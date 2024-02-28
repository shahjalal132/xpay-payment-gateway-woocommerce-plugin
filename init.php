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
 */

/**
 * Ensure that the WordPress environment is loaded and ABSPATH is defined,
 * or exit with a message indicating that direct access is not allowed.
 */
defined( 'ABSPATH' ) || exit( 'Direct Access Not Allowed' );

// Define plugin path
if ( !defined( 'XPAY_PLUGIN_PATH' ) ) {
    define( 'XPAY_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Define plugin url
if ( !defined( 'XPAY_PLUGIN_URI' ) ) {
    define( 'XPAY_PLUGIN_URI', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
}

/**
 * Load the plugin textdomain for localization.
 */
add_action( 'plugins_loaded', 'xpay_load_textdomain' );
function xpay_load_textdomain() {
    load_plugin_textdomain( 'xpay', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Check if WooCommerce plugin is active, if not, stop further execution.
 */
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

/**
 * Initialize the Xpay payment gateway.
 */
add_action( 'plugins_loaded', 'xpay_payment_gateway_init', 11 );
function xpay_payment_gateway_init() {

    if ( class_exists( 'WC_Payment_Gateway' ) ) {

        /**
         * Xpay Payment Gateway class extending WooCommerce's payment gateway class.
         */
        class Xpay_Payment_Gateway extends WC_Payment_Gateway {

            /**
             * Constructor function to set up the gateway.
             */
            public function __construct() {

                // Gateway ID, icon, and method title.
                $this->id                 = 'xpay';
                $this->icon               = XPAY_PLUGIN_URI . '/assets/images/atm-card.png';
                $this->has_fields         = false;
                $this->method_title       = __( 'Xpay Gateway', 'xpay' );
                $this->method_description = __( 'Description of Xpay payment gateway', 'xpay' );

                // Supported features.
                $this->supports = array( 'products' );

                // Initialize form fields.
                $this->init_form_fields();

                // Load the settings.
                $this->init_settings();

                // Get settings values.
                $this->title           = $this->get_option( 'woocommerce_xpay_title' );
                $this->description     = $this->get_option( 'woocommerce_xpay_description' );
                $this->enabled         = $this->get_option( 'woocommerce_xpay_enabled' );
                $this->testmode        = 'yes' === $this->get_option( 'woocommerce_xpay_testmode' );
                $this->private_key     = $this->testmode ? $this->get_option( 'woocommerce_xpay_test_private_key' ) : $this->get_option( 'woocommerce_xpay_private_key' );
                $this->publishable_key = $this->testmode ? $this->get_option( 'woocommerce_xpay_test_publishable_key' ) : $this->get_option( 'woocommerce_xpay_publishable_key' );

                // Save settings on update.
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            }

            /**
             * Initialize form fields for the gateway settings.
             */
            public function init_form_fields() {

                $this->form_fields = array(
                    'enabled'              => array(
                        'title'       => __( 'Enable/Disable', 'xpay' ),
                        'label'       => __( 'Enable Xpay', 'xpay' ),
                        'type'        => 'checkbox',
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
                        'default'     => '',
                        'desc_tip'    => true,
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
                        'title'       => __( 'Test Publishable Key', 'xpay' ),
                        'type'        => 'text',
                        'description' => __( 'Enter your test publishable key here.', 'xpay' ),
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                    'test_private_key'     => array(
                        'title' => __( 'Test Private Key', 'xpay' ),
                        'type'  => 'password',
                    ),
                    'publishable_key'      => array(
                        'title'       => __( 'Live Publishable Key', 'xpay' ),
                        'type'        => 'text',
                        'description' => __( 'Enter your live publishable key here.', 'xpay' ),
                        'default'     => '',
                        'desc_tip'    => true,
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

/**
 * Add the Xpay Payment Gateway class to WooCommerce payment gateways.
 */
add_filter( 'woocommerce_payment_gateways', 'xpay_payment_gateway_class_add' );
function xpay_payment_gateway_class_add( $gateways ) {
    $gateways[] = 'Xpay_Payment_Gateway';
    return $gateways;
}
