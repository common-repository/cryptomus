<?php

namespace Cryptomus\Woocommerce;

use Cryptomus\Api\Client;
use WC_Payment_Gateway;

final class Gateway extends WC_Payment_Gateway
{
    /**
     * @var string
     */
    public $id = 'cryptomus';
    /**
     * @var bool
     */
    public $has_fields = true;
    /**
     * @var string
     */
    public $title = 'Pay with Cryptomus';
    /**
     * @var string
     */
    public $method_title = 'Cryptomus';
    /**
     * @var string
     */
    public $method_description = "";
    /**
     * @var \Cryptomus\Api\Payment
     */
    public $payment;
    /**
     * @var string
     */
    public $merchant_uuid;
    /**
     * @var int|string
     */
    public $subtract;
    /**
     * @var string
     */
    private $payment_key;
    /**
     * @var string
     */
    public $lifetime;
    public $accepted_networks;
    public $accepted_currencies;
    public $h2h;
    public $theme;

    public function __construct()
    {
        $this->title = $this->get_option('method_title') ?: $this->title;
        $this->method_description = "
        <img src='https://cryptomus.com/_next/image?url=https%3A%2F%2Fstorage.cryptomus.com%2Fyq8VJoFkIrkziDcOhTmxrgPIa53NPNBSph8PzTQc.webp&w=1920&q=75' style='max-width: 500px; height: auto;'>
        <p>To start using Cryptomus, you need to follow a few simple steps.</p>

        <p>1) Create an account on our website <a href='http://cryptomus.com/?utm_source=wordpress&utm_medium=plugin-description'>cryptomus.com</a> and complete the verification process.</p>
        <p>2) Once you have completed the verification, you will be issued a personal merchant UUID and API key.</p>
        <p>3) In the settings, enter the merchant UUID and API key.</p>
        <p>Congratulations! Now you can accept cryptocurrency payments using Cryptomus.</p>
        <p>Detailed instructions on creating a merchant account can be found <a href='https://cryptomus.com/blog/how-to-accept-crypto-on-your-wordpress-website-with-woocommerce-payment-plugin/?utm_source=wordpress&utm_medium=plugin-description'>here</a>.</p>
        <p>Have questions? We are always happy to answer on <a href='https://t.me/cryptomussupport'>Telegram</a>. Our support operates 24 hours a day.</p>
        ";
        $this->description = $this->get_option('description');
        $this->form_fields = $this->adminFields();
        $this->init_settings();

        $this->payment_key = $this->get_option('payment_key');
        $this->merchant_uuid = $this->get_option('merchant_uuid');
        $path = str_replace(ABSPATH, '', __DIR__) . "/images/logo_light.svg";
        $this->icon = esc_url(get_option('cryptomus_method_image')) ?? site_url($path);
        $this->subtract = $this->get_option('subtract') ?? 0;
        $this->payment = Client::payment($this->payment_key, $this->merchant_uuid);
        $this->lifetime = $this->get_option('lifetime') ?? 2;
        $this->theme = $this->get_option('theme') ?? 'light';
        $this->h2h = $this->get_option('h2h');
        $this->accepted_networks = $this->get_option('accepted_networks');
        $this->accepted_currencies = $this->get_option('accepted_currencies');
        add_action("woocommerce_update_options_payment_gateways_{$this->id}", array($this, 'process_admin_options'));
    }

    /**
     * @return array
     */
    public function adminFields()
    {
        return [
            'enabled' => [
                'title' => __('Enabled'),
                'type' => 'checkbox',
                'default' => 'no',
                'desc_tip' => true
            ],
            'payment_key' => [
                'title' => '
                Payment API-key
                <p><font size="1">You can find the API key in the settings of your personal account. Read more <a href="https://cryptomus.com/blog/how-to-accept-crypto-on-your-wordpress-website-with-woocommerce-payment-plugin/?utm_source=wordpress&utm_medium=plugin-description">here</a>.</font></p>
                ',
                'type' => 'text'
            ],
            'merchant_uuid' => [
                'title' => 'Merchant UUID
                <p><font size="1">You can find the Merchant UUID in the settings of your personal account. Read more <a href="https://cryptomus.com/blog/how-to-accept-crypto-on-your-wordpress-website-with-woocommerce-payment-plugin/?utm_source=wordpress&utm_medium=plugin-description">here</a>.</font></p>
                ',
                'type' => 'text'
            ],
            'method_title' => [
                'title' => 'Method title
                <p><font size="1">Payment method name. For example "Cryptomus".</font></p>
                ',
                'type' => 'text',
                'default' => 'Pay with Cryptomus'
            ],
            'description' => [
                'title' => 'Method description
                <p><font size="1">
                Description that will be located next to the Cryptomus payment method. For example "Pay with cryptocurrency." </font></p>
                ',
                'type' => 'text',
                'default' => 'Crypto payment system'
            ],
            'method_image' => [
                'title' => 'Method Image
                <p><font size="1">The image that will be located next to the payment method. Ready images can be taken <a href="https://cryptomus.com/brand-guideline/?utm_source=wordpress&utm_medium=plugin-description">here</a>.</font></p>
                ',
                'type' => 'file',
                'desc_tip' => true,
                'description' => 'Upload an image for the payment method',
            ],
            'subtract' => [
                'title' => 'How much commission does the client pay (0-100%)
                <p><font size="1">Percentage of the payment commission charged to the client
                If you have a rate of 1%, then if you create an invoice for 100 USDT with subtract = 100 (the client pays 100"%" commission), the client will have to pay 101 USDT.</font></p>
                ',
                'type' => 'number',
                'default' => 0,
                'custom_attributes' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ],
            'lifetime' => [
                'title' => 'Invoice Lifetime',
                'type' => 'select',
                'options' => [
                    '1' => '1 Hour',
                    '2' => '2 Hours',
                    '3' => '3 Hours',
                    '4' => '4 Hours',
                    '5' => '5 Hours',
                    '6' => '6 Hours',
                    '7' => '7 Hours',
                    '8' => '8 Hours',
                    '9' => '9 Hours',
                    '10' => '10 Hours',
                    '11' => '11 Hours',
                    '12' => '12 Hours',
                ],
                'default' => '2',
            ],
            'h2h' => [
                'title' => __('Host-to-Host'),
                'type' => 'checkbox',
                'default' => 'no'
            ],
            'theme' => [
                'title' => 'Theme',
                'type' => 'select',
                'options' => [
                    'light' => 'Light',
                    'dark' => 'Dark',
                ]
            ],
            'accepted_networks' => [
                'title' => 'Accepted Networks',
                'type' => 'multiselect',
                'options' => [
                    'ETH' => 'Ethereum (ETH)',
                    'ARBITRUM' => 'Arbitrum (ARBITRUM)',
                    'TRON' => 'Tron (TRON)',
                    'DOGE' => 'Dogecoin (DOGE)',
                    'DASH' => 'Dash (DASH)',
                    'AVALANCHE' => 'Avalanche (AVALANCHE)',
                    'SOL' => 'Solana (SOL)',
                    'BTC' => 'Bitcoin (BTC)',
                    'XMR' => 'Monero (XMR)',
                    'TON' => 'TON (TON)',
                    'POLYGON' => 'Polygon (POLYGON)',
                    'BCH' => 'Bitcoin Cash (BCH)',
                    'LTC' => 'Litecoin (LTC)',
                    'BSC' => 'Binance Smart Chain (BSC)',
                ],
                'default' => [
                    'ETH',
                    'ARBITRUM',
                    'TRON',
                    'DOGE',
                    'DASH',
                    'AVALANCHE',
                    'SOL',
                    'BTC',
                    'XMR',
                    'TON',
                    'POLYGON',
                    'BCH',
                    'LTC',
                    'BSC',
                ], // Выбраны все сети по умолчанию
                'class' => 'wc-enhanced-select', // Добавим класс для стилизации
            ],
            'accepted_currencies' => [
                'title' => 'Accepted Currencies',
                'type' => 'multiselect',
                'options' => [
                    'DOGE' => 'Dogecoin (DOGE)',
                    'SHIB' => 'Shiba Inu (SHIB)',
                    'CGPT' => 'ChatGPT Token (CGPT)',
                    'MATIC' => 'Polygon (MATIC)',
                    'BCH' => 'Bitcoin Cash (BCH)',
                    'DAI' => 'Dai (DAI)',
                    'VERSE' => 'Verse (VERSE)',
                    'SOL' => 'Solana (SOL)',
                    'BUSD' => 'Binance USD (BUSD)',
                    'LTC' => 'Litecoin (LTC)',
                    'ETH' => 'Ethereum (ETH)',
                    'BNB' => 'Binance Coin (BNB)',
                    'TRX' => 'TRON (TRX)',
                    'USDC' => 'USD Coin (USDC)',
                    'AVAX' => 'Avalanche (AVAX)',
                    'DASH' => 'Dash (DASH)',
                    'XMR' => 'Monero (XMR)',
                    'BTC' => 'Bitcoin (BTC)',
                    'USDT' => 'Tether (USDT)',
                    'TON' => 'TON (TON)',
                ],
                'default' => [
                    'DOGE',
                    'SHIB',
                    'CGPT',
                    'MATIC',
                    'BCH',
                    'DAI',
                    'VERSE',
                    'SOL',
                    'BUSD',
                    'LTC',
                    'ETH',
                    'BNB',
                    'TRX',
                    'USDC',
                    'AVAX',
                    'DASH',
                    'XMR',
                    'BTC',
                    'USDT',
                    'TON',
                ], // Выбраны все валюты по умолчанию
                'class' => 'wc-enhanced-select', // Добавляем класс для стилизации
            ],
        ];
    }

    /**
     * @param $order_id
     * @return array
     */
    public function process_payment($order_id) {
        if ($this->h2h == "yes") {
            $order = wc_get_order($order_id);
            $order->update_status(PaymentStatus::WC_STATUS_PENDING);
            $order->save();
            wc_reduce_stock_levels($order_id);
            WC()->cart->empty_cart();
            return ['result' => 'success', 'redirect' => home_url('/cryptomus-pay?order_id='.$order_id.'&step_id=1')];
        } else {
            $order = wc_get_order($order_id);
            $order->update_status(PaymentStatus::WC_STATUS_PENDING);
            $order->save();
            wc_reduce_stock_levels($order_id);
            WC()->cart->empty_cart();
            try {
                $success_url = $this->get_return_url($order);
                $return_url = str_replace('/order-received/', '/order-pay/', $success_url);
                $return_url .= '&pay_for_order=true';
                $payment = $this->payment->create([
                    'amount' => $order->get_total(),
                    'currency' => $order->get_currency(),
                    'order_id' => (string)$order_id,
                    'url_return' => $return_url,
                    'url_success' => $success_url,
                    'url_callback' => get_site_url(null, "wp-json/cryptomus-webhook/$this->merchant_uuid"),
                    'is_payment_multiple' => true,
                    'lifetime' => (int)$this->lifetime * 3600,
                    'subtract' => $this->subtract,
                ]);
                return ['result' => 'success', 'redirect' => $payment['url']];
            } catch (\Exception $e) {
                $order->update_status(PaymentStatus::WC_STATUS_FAIL);
                wc_increase_stock_levels($order);
                $order->save();
            }
            return ['result' => 'success', 'redirect' => $this->get_return_url($order)];
        }
    }

    public function create_h2h_payment($order_id, $network, $to_currency) {
        $order = wc_get_order($order_id);
        try {
            $success_url = $this->get_return_url($order);
            $return_url = str_replace('/order-received/', '/order-pay/', $success_url);
            $return_url .= '&pay_for_order=true';

            $payment = $this->payment->create([
                'amount' => $order->get_total(),
                'currency' => $order->get_currency(),
                'order_id' => 'woo_h2h_'.$network.'_'.$to_currency.'_'.(string)$order_id,
                'url_return' => $return_url,
                'url_success' => $success_url,
                'url_callback' => get_site_url(null, "wp-json/cryptomus-webhook/$this->merchant_uuid"),
                'is_payment_multiple' => true,
                'lifetime' => (int)$this->lifetime * 3600,
                'subtract' => $this->subtract,
                'network' => $network,
                'to_currency' => $to_currency
            ]);

            return $payment;
        } catch (\Exception $e) {
            $order->update_status(PaymentStatus::WC_STATUS_FAIL);
            wc_increase_stock_levels($order);
            $order->save();
            return false;
        }
    }

    public function request_currencies()
    {
        $all_currencies = $this->payment->services();
        $filtered_currencies = [];
        foreach ($all_currencies as $currency) {
            if ($currency['is_available'] && in_array($currency['network'], $this->accepted_networks) && in_array($currency['currency'], $this->accepted_currencies)) {
                array_push($filtered_currencies, $currency);
            }
        }
        return $filtered_currencies;
    }

    public function process_admin_options()
    {
        parent::process_admin_options();

        $uploaded_image = isset($_FILES['woocommerce_cryptomus_method_image']) ? $_FILES['woocommerce_cryptomus_method_image'] : null;

        if ($uploaded_image && isset($uploaded_image['tmp_name']) && !empty($uploaded_image['tmp_name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $upload_overrides = array('test_form' => false);
            $movefile = wp_handle_upload($uploaded_image, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $image_url = $movefile['url'];
                update_option('cryptomus_method_image', $image_url);
            }
        }
    }

    public function admin_options()
    {
        $image_url = get_option('cryptomus_method_image');

        echo '<h2>' . esc_html($this->method_title) . '</h2>';
        echo '<div>' . $this->method_description . '</div>';

        if (!empty($image_url)) {
            echo '<h3>' . __('Image Preview', 'woocommerce') . '</h3>';
            echo '<img src="' . esc_url($image_url) . '" alt="Method Image" style="max-width: 200px; height: auto;" /><br />';
        }

        echo '<table class="form-table">';
        // Render other settings fields here...
        $this->generate_settings_html();
        echo '</table>';
    }
}
