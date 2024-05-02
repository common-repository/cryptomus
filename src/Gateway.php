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
    private $logo_theme;

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
        $this->logo_theme = $this->get_option('logo_theme') ?: 'light';

        $path = str_replace(ABSPATH, '', __DIR__) . "/images/logo_$this->logo_theme.svg";
        $this->icon = esc_url(get_option('cryptomus_method_image')) ?: site_url($path);
        $this->subtract = $this->get_option('subtract') ?: 0;
        $this->payment = Client::payment($this->payment_key, $this->merchant_uuid);

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
            /*'logo_theme' => [
                'title' => 'Logo Theme',
                'type' => 'select',
                'options' => [
                    'light' => 'Light',
                    'dark' => 'Dark',
                ]
            ],*/
            'subtract' => [
                'title' => 'How much commission does the client pay (0-100%)
                <p><font size="1">Percentage of the payment commission charged to the client
                If you have a rate of 1%, then if you create an invoice for 100 USDT with subtract = 100 (the client pays 100"%" commission), the client will have to pay 101 USDT.</font></p>
                ',
                'type' => 'number',
                'default' => 0,
            ],
        ];
    }

    /**
     * @param $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);
        $order->update_status(PaymentStatus::WC_STATUS_PENDING);
        $order->save();

        wc_reduce_stock_levels($order_id);
        WC()->cart->empty_cart();

        return ['result' => 'success', 'redirect' => home_url('/cryptomus-pay?order_id='.$order_id)];
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
                update_option('cryptomus_method_image', $image_url); // Replace 'cryptomus_method_image' with your preferred option name
            }
        }
    }

    public function admin_options()
    {
        $image_url = get_option('cryptomus_method_image'); // Replace with your option name

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
