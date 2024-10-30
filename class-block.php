<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Cryptomus_Gateway_Blocks extends AbstractPaymentMethodType
{
    private $gateway;
    protected $name = 'cryptomus';

    public function initialize()
    {
        $this->settings = get_option("woocommerce_{$this->name}_settings", []);
        $gateways = WC()->payment_gateways->payment_gateways();
        $this->gateway = $gateways[$this->name];
    }

    public function is_active()
    {
        return !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'];

    }

    public function get_payment_method_script_handles()
    {
        wp_register_script(
            'cryptomus-gateway-blocks-integration',
            plugin_dir_url(__FILE__) . 'checkout.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );

        return ['cryptomus-gateway-blocks-integration'];
    }

    public function get_payment_method_data()
    {
        return [
            'title' => $this->get_setting('method_title'),
            'description' => $this->get_setting('description'),
            'supports' => array_filter($this->gateway->supports, [$this->gateway, 'supports']),
            'icon' => plugin_dir_url( __DIR__ ) . $this->name . "/src/images/logo_light.svg",
        ];
    }
}
