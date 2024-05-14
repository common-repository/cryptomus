<?php
/**
 * Plugin Name: Cryptomus
 * Plugin URI: https://doc.cryptomus.com
 * Description: Cryptomus allows you to accept cryptocurrency payments worldwide without any restrictions. To start accepting payments all you need to do is register on the platform and issue an API key.
 * Version: 1.3.2
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

function cryptomus_add_endpoint() {
	add_rewrite_endpoint('cryptomus-pay', EP_ROOT);
	flush_rewrite_rules(false);
}
add_action('init', 'cryptomus_add_endpoint');

function cryptomus_template_include($template) {
	global $wp_query;
	if (isset($wp_query->query_vars['cryptomus-pay'])) {
		$gateway = new Cryptomus\Woocommerce\Gateway();

		if ($gateway->h2h !== "yes") {
			wp_redirect(home_url());
			exit;
		}

		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
		$step_id = isset($_GET['step_id']) ? intval($_GET['step_id']) : 1;
		$order = wc_get_order($order_id);
		$success_url = $gateway->get_return_url($order);
		$return_url = str_replace('/order-received/', '/order-pay/', $success_url);
		$return_url .= '&pay_for_order=true';

		$params = [
			'order_id' => $order_id,
			'step_id' => $step_id,
			'return_url' => $return_url,
			'success_url' => $success_url,
			'theme' => $gateway->theme,
		];

		switch ($step_id) {
			case 1:
				$params['currencies'] = $gateway->request_currencies();
				$params['unique_coins'] = array_unique(array_column($params['currencies'], 'currency'));
				sort($params['unique_coins']);
				$params['order_amount'] = $order->get_total();
				$params['order_currency'] = $order->get_currency();
				break;

			case 2:
				$network = $_GET['network'] ?? null;
				$to_currency = $_GET['to_currency'] ?? null;
				$params['payment'] = $gateway->create_h2h_payment($order_id, $network, $to_currency);
				$params['network'] = $network;
				$params['to_currency'] = $to_currency;
				break;

			default:
				break;
		}

		set_query_var('params', $params);
		$new_template = plugin_dir_path(__FILE__) . 'templates/form_' . $step_id . '.php';
		if (file_exists($new_template)) {
			return $new_template;
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

function cryptomus_webhook_callback($request) {
	$gateway = new Cryptomus\Woocommerce\Gateway();
	$params = $request->get_params();

	if (empty($params['uuid']) || empty($params['order_id'])) {
		return ['success' => false];
	}

	$result = $gateway->payment->info(['uuid' => $params['uuid']]);
	if (empty($result['payment_status'])) {
		return ['success' => false];
	}

	$order_id = $params['order_id'];
	$order = wc_get_order($order_id);
	$all_downloadable_and_virtual = all_items_downloadable_and_virtual($order);

	$order->set_status(PaymentStatus::convertToWoocommerceStatus($result['payment_status'], $all_downloadable_and_virtual));
	$order->save();

	if (PaymentStatus::isNeedReturnStocks($result['payment_status'], $all_downloadable_and_virtual)) {
		wc_increase_stock_levels($order);
	}

	return ['success' => true];
}
add_action('rest_api_init', function () {
	$gateway = new Cryptomus\Woocommerce\Gateway();
	register_rest_route('cryptomus-webhook', $gateway->merchant_uuid, array(
		'methods' => 'POST',
		'permission_callback' => '__return_true',
		'callback' => 'cryptomus_webhook_callback',
	));
});

function check_payment_status(WP_REST_Request $request) {
	$order_id = $request->get_param('order_id');
	if (empty($order_id)) {
		return new WP_REST_Response(['error' => 'Order ID is required'], 400);
	}
	$gateway = new Cryptomus\Woocommerce\Gateway();
	$result = $gateway->payment->info(['order_id' => $order_id]);
	$order = wc_get_order(end(explode('_', $order_id)));
	$all_downloadable_and_virtual = all_items_downloadable_and_virtual($order);

	$order->set_status(PaymentStatus::convertToWoocommerceStatus($result['payment_status'], $all_downloadable_and_virtual));
	$order->save();

	if (PaymentStatus::isNeedReturnStocks($result['payment_status'], $all_downloadable_and_virtual)) {
		wc_increase_stock_levels($order);
	}

	return new WP_REST_Response($result, 200);
}
add_action('rest_api_init', function () {
	register_rest_route('cryptomus-pay', '/check-status', array(
		'methods' => 'POST',
		'callback' => 'check_payment_status',
		'permission_callback' => '__return_true',
	));
});

function plugin_enqueue_admin_scripts($hook) {
	if (isset($_GET['page']) && $_GET['page'] === 'wc-settings' && isset($_GET['tab']) && $_GET['tab'] === 'checkout' && isset($_GET['section']) && $_GET['section'] === 'cryptomus') {
		wp_enqueue_script('plugin-admin-script', plugins_url('js/plugin-admin.js', __FILE__), array('jquery'), null, true);
	}
}
add_action('admin_enqueue_scripts', 'plugin_enqueue_admin_scripts');

function all_items_downloadable_and_virtual($order) {
	$items = $order->get_items();
	foreach ($items as $item) {
		$product = $item->get_product();
		if (!($product->is_virtual() && $product->is_downloadable())) {
			return false;
		}
	}
	return true;
}
