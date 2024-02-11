<?php

/*
 * Plugin Name:       Xpay Payment Gateway
 * Plugin URI:        #
 * Description:       Xpay Payment Gateway
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Shah Jalal
 * Author URI:        #
 * License:           GPL v2 or later
 * Text Domain:       xpay
 * Domain Path:       /languages
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 */

defined( 'ABSPATH' ) || exit( 'Direct Access Not Allowed' );

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

add_action( 'plugins_loaded', 'xpay_payment_gateway_init', 11 );
function xpay_payment_gateway_init() {

    if ( class_exists( 'WC_Payment_Gateway' ) ) {

        class Xpay_Payment_Gateway extends WC_Payment_Gateway {

            public function __construct() {

                $this->id                 = 'xpay';
                $this->icon               = '';
                $this->has_fields         = false;
                $this->method_title       = __( 'Xpay Gateway', 'xpay' );
                $this->method_description = __( 'Description of Xpay payment gateway', 'xpay' );

                $this->supports = array(
                    'products',
                );

                // Method with all the options fields
                $this->init_form_fields();

                // Load the settings.
                $this->init_settings();

                $this->title           = $this->get_option( 'woocommerce_xpay_title' );
                $this->description     = $this->get_option( 'woocommerce_xpay_description' );
                $this->enabled         = $this->get_option( 'woocommerce_xpay_enabled' );
                $this->testmode        = 'yes' === $this->get_option( 'woocommerce_xpay_testmode' );
                $this->private_key     = $this->testmode ? $this->get_option( 'woocommerce_xpay_test_private_key' ) : $this->get_option( 'woocommerce_xpay_private_key' );
                $this->publishable_key = $this->testmode ? $this->get_option( 'woocommerce_xpay_test_publishable_key' ) : $this->get_option( 'woocommerce_xpay_publishable_key' );

                // This action hook saves the settings
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            }

            public function init_form_fields() {

                $this->form_fields = array(
                    'enabled'              => array(
                        'title'       => __( 'Enable/Disable', 'xpay' ),
                        'label'       => __( 'Enable Xpay', 'xpay' ),
                        'type'        => 'checkbox',
                        'description' => '',
                        'default'     => 'no',
                    ),
                    'title'                => array(
                        'title'       => __( 'Title', 'xpay' ),
                        'type'        => 'text',
                        'description' => __( 'This controls the title which the user sees during checkout.', 'xpay' ),
                        'default'     => __( 'Credit Card', 'xpay' ),
                        'desc_tip'    => true,
                    ),
                    'description'          => array(
                        'title'       => __( 'Description', 'xpay' ),
                        'type'        => 'textarea',
                        'description' => __( 'This controls the description which the user sees during checkout.', 'xpay' ),
                        'default'     => __( 'Pay with your credit card via our super-cool payment gateway.', 'xpay' ),
                    ),
                    'testmode'             => array(
                        'title'       => __( 'Test mode', 'xpay' ),
                        'label'       => __( 'Test Mode', 'xpay' ),
                        'type'        => 'checkbox',
                        'description' => __( 'Place the payment gateway in test mode using test API keys.', 'xpay' ),
                        'default'     => 'no',
                        'desc_tip'    => true,
                    ),
                    'test_publishable_key' => array(
                        'title' => __( 'Test Publishable Key', 'xpay' ),
                        'type'  => 'text',
                    ),
                    'test_private_key'     => array(
                        'title' => __( 'Test Private Key', 'xpay' ),
                        'type'  => 'password',
                    ),
                    'publishable_key'      => array(
                        'title' => __( 'Live Publishable Key', 'xpay' ),
                        'type'  => 'text',
                    ),
                    'private_key'          => array(
                        'title' => __( 'Live Private Key', 'xpay' ),
                        'type'  => 'password',
                    ),
                );

            }

        }
    }

}

add_filter( 'woocommerce_payment_gateways', 'xpay_payment_gateway_class_add' );
function xpay_payment_gateway_class_add( $gateways ) {

    $gateways[] = 'Xpay_Payment_Gateway';
    return $gateways;
}
