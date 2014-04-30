<?php

/**
 * Scripts
 *
 * @package     Sell Media
 * @subpackage  Functions/Install
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Scripts
 *
 * Enqueues all necessary scripts in the WP Admin to run Sell Media
 *
 * @since 1.8.5
 * @return void
 */
function sell_media_scripts( $hook ) {

    $settings = sell_media_get_plugin_options();

    // enqueue 
    wp_enqueue_script( 'sell_media', SELL_MEDIA_PLUGIN_URL . 'js/sell_media.js', array( 'jquery' ), SELL_MEDIA_VERSION );
    wp_enqueue_script( 'sellMediaCart', SELL_MEDIA_PLUGIN_URL . 'js/sell_media_cart.js', array( 'jquery' ), SELL_MEDIA_VERSION );
    wp_enqueue_style( 'sell_media', SELL_MEDIA_PLUGIN_URL . 'css/sell_media.css', null, SELL_MEDIA_VERSION );
    wp_enqueue_style( 'sell_media-widgets-style', SELL_MEDIA_PLUGIN_URL . 'css/sell_media_widgets.css', null, SELL_MEDIA_VERSION );

    // register
    wp_register_script( 'sell_media-chosen', SELL_MEDIA_PLUGIN_URL . 'js/chosen.jquery.min.js', array( 'jquery' ), SELL_MEDIA_VERSION );
    wp_register_style( 'sell_media-chosen', SELL_MEDIA_PLUGIN_URL . 'css/chosen.css', null, SELL_MEDIA_VERSION );

    if ( isset( $settings->style ) && '' != $settings->style ) {
        wp_enqueue_style( 'sell-media-style', SELL_MEDIA_PLUGIN_URL . 'css/sell_media-' . $settings->style . '.css' );
    } else {
        wp_enqueue_style( 'sell-media-style', SELL_MEDIA_PLUGIN_URL . 'css/sell_media-light.css' );
    }

    $settings = sell_media_get_plugin_options();

    wp_localize_script( 'sell_media', 'sell_media', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'pluginurl' => SELL_MEDIA_PLUGIN_URL . 'sell-media.php',
        'site_name' => get_bloginfo( 'name' ),
        'site_url' => site_url(),
        'checkout_url' => empty( $settings->checkout_page ) ? null : get_permalink( $settings->checkout_page ),
        'currency_symbol' => $settings->currency,
        'dashboard_page' => get_permalink( $settings->dashboard_page ),
        'error' => array(
            'email_exists' => __('Sorry that email already exists or is invalid', 'sell_media')
            ),
        'sandbox' => ( $settings->test_mode == 1 ) ? true : false,
        'paypal_email' => ( empty( $settings->paypal_email ) ) ? null : $settings->paypal_email,
        // set this in stripe extension? and make use testing or live key
        'stripe_public_key' => ( empty( $settings->stripe_test_publishable_key ) ) ? null : $settings->stripe_test_publishable_key,
        'thanks_page' => get_permalink( $settings->thanks_page ),
        'listener_url' => site_url( '?sell_media-listener=IPN' ),
        'added_to_cart' => sprintf(
            "%s! <a href='" . get_permalink( $settings->checkout_page ) . "' class='cart'>%s</a>!",
            __( 'Added', 'sell_media' ),
            __( 'Checkout now','sell_media' ) ),
        'cart_labels' => array(
            'name' => __( 'Name', 'sell_media' ),
            'size' => __( 'Size', 'sell_media' ),
            'license' => __( 'License', 'sell_media' ),
            'price' => __( 'Price', 'sell_media' ),
            'qty' => __( 'Qty', 'sell_media' ),
            'sub_total' => __( 'Subtotal', 'sell_media' )
            ),
        'cart_style' => apply_filters( 'sell_media_cart_style', 'table' ),
        'tax' => ( empty( $settings->tax ) ) ? 0 : $settings->tax_rate,
        'shipping' => apply_filters( 'sell_media_shipping', 0 ), // should PayPal force buyers add address
        'cart_error' => __( 'There was an error loading the cart data. Please contact the site owner.', 'sell_media' ),
        'checkout_text' => __( 'Checkout Now', 'sell_media' ),
        'checkout_wait_text' => __( 'Please wait...', 'sell_media' )
    ) );
    
    do_action( 'sell_media_scripts_hook' );
}
add_action( 'wp_enqueue_scripts', 'sell_media_scripts' );