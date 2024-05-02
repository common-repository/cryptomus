<?php
/**
 * Plugin Name: Cryptomus
 * Plugin URI: https://doc.cryptomus.com
 * Description: Cryptomus allows you to accept cryptocurrency payments worldwide without any restrictions. To start accepting payments all you need to do is register on the platform and issue an API key.
 * Version: 1.2.6
 * Author: Cryptomus.com
 * Author URI: https://app.cryptomus.com/
 * Developer: Cryptomus
 * Developer https://app.cryptomus.com/
 *
 * @package WooCommerce\Admin
 */

use Cryptomus\Woocommerce\PaymentStatus;

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	return;
}

add_filter('woocommerce_payment_gateways', function ($plugins) {
	return array_merge([\Cryptomus\Woocommerce\Gateway::class], $plugins);
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
	$url = admin_url('admin.php?page=wc-settings&tab=checkout&section=cryptomus');
	return array_merge(['<a href="' . $url . '">' . __('Configure') . '</a>'], $links);
});

add_action('plugins_loaded', function () {
	return new \Cryptomus\Woocommerce\Gateway();
});

add_filter('wc_order_statuses', function ($statuses) {
	$statuses['wc-wrong-amount'] = __('Wrong amount');
	return $statuses;
});

// Функция для регистрации нового endpoint
function cryptomus_add_endpoint() {
	add_rewrite_endpoint('cryptomus-pay', EP_ROOT);
	flush_rewrite_rules(false);  // Используйте это только для отладки!
}

// Добавление функции регистрации endpoint к хуку init
add_action('init', 'cryptomus_add_endpoint');

function cryptomus_template_include($request) {
	global $wp_query;
	$gateway = new Cryptomus\Woocommerce\Gateway();

	if (isset($wp_query->query_vars['cryptomus-pay'])) {
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
		$step_id = isset($_GET['step_id']) ? intval($_GET['step_id']) : 1;
		$order = wc_get_order($order_id);

		if ($step_id == 1) {
			$currencies = $gateway->request_currencies();
			$unique_networks = [];

			foreach ($currencies as $currency) {
				if ($currency['is_available']) {
					$unique_networks[$currency['network']] = $currency['network'];
				}
			}

			$params = [
				'order_id' => $order_id,
				'unique_networks' => array_keys($unique_networks),
				'currencies' => $currencies,
				'order_amount' => $order->get_total(),
				'order_currency' => $order->get_currency(),
			];

			set_query_var('params', $params);

			$new_template = plugin_dir_path(__FILE__) . 'templates/form1.php';
			if (file_exists($new_template)) {
				return $new_template;
			}

		} else if ($step_id == 2) {
			$network = $_GET['network'] ?? null;
			$to_currency = $_GET['to_currency'] ?? null;
			$payment = $gateway->create_h2h_payment($order_id, $network, $to_currency);
			$params = [
				'order_id' => $order_id,
				'payment' => $payment,
				'network' => $network,
				'to_currency' => $to_currency,
			];
			set_query_var('params', $params);
			$new_template = plugin_dir_path(__FILE__) . 'templates/form2.php';
			if (file_exists($new_template)) {
				return $new_template;
			}
		}
	}

	return $template;
}

add_filter('template_include', 'cryptomus_template_include');


function cryptomus_query_vars($vars) {
	$vars[] = 'cryptomus-pay';
	return $vars;
}
add_filter('query_vars', 'cryptomus_query_vars');


add_action('rest_api_init', function () {
	$gateway = new Cryptomus\Woocommerce\Gateway();
	register_rest_route('cryptomus-webhook', $gateway->merchant_uuid, array(
		'methods' => 'POST',
		'permission_callback' => function() {
			return true;
		},
		'callback' => function ($request) use ($gateway) {
			$params = $request->get_params();
			if (empty($params['uuid']) || empty($params['order_id'])) {
				return ['success' => false];
			}

			$result = $gateway->payment->info(['uuid' => $params['uuid']]);
			if (empty($result['payment_status'])) {
				return ['success' => false];
			}

			$order = wc_get_order($params['order_id']);

			$items = $order->get_items();
			$all_downloadable_or_virtual = true;
			foreach ($items as $item) {
				$product = $item->get_product();
				if (!($product->is_virtual() || $product->is_downloadable())) {
					$all_downloadable_or_virtual = false;
					break;
				}
			}

			$order->set_status(PaymentStatus::convertToWoocommerceStatus($result['payment_status'], $all_downloadable_or_virtual));
			$order->save();

			if (PaymentStatus::isNeedReturnStocks($result['payment_status'])) {
				wc_increase_stock_levels($order);
			}

			return ['success' => true];
		}
	));
});

