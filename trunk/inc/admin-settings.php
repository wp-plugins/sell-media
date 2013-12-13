<?php

/*
 * The main class, holds everything our Settings does,
 * initialized right after declaration
 */
class SellMediaSettings {

    /*
     * For easier overriding we declared the keys
     * here as well as our tabs array which is populated
     * when registering settings
     */
    private $general_settings_key = 'sell_media_general_settings';
    private $payment_settings_key = 'sell_media_payment_settings';
    private $email_settings_key = 'sell_media_email_settings';
    private $misc_settings_key = 'sell_media_misc_settings';
    private $plugin_options_key = 'sell_media_plugin_options';
    private $size_settings_key = 'sell_media_size_settings';
    private $plugin_post_type_key = 'sell_media_item';
    private $plugin_settings_tabs = array();

    /*
     * Fired during plugins_loaded (very very early),
     * so don't miss-use this, only actions and filters,
     * current ones speak for themselves.
     *
     * @todo remove $option_tabs array and use a dynamic
     * array (once we dismantel this settings god glass)
     */
    function __construct() {
        add_action( 'init', array( &$this, 'load_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_general_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_size_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_payment_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_email_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_misc_settings' ) );

        add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );


        $option_tabs = array(
            'sell_media_misc_settings',
            'sell_media_size_settings',
            'sell_media_general_settings',
            'sell_media_payment_settings',
            'sell_media_email_settings'
        );

        if ( ! empty( $_POST['option_page'] ) && in_array( $_POST['option_page'], $option_tabs ) ){
            do_action( 'sell_media_settings_init_hook' );
        }
    }

    /*
     * Loads both the settings from
     * the database into their respective arrays. Uses
     * array_merge to merge with default values if they're
     * missing.
     */
    function load_settings() {
        $this->general_settings = (array) get_option( $this->general_settings_key );
        $this->payment_settings = (array) get_option( $this->payment_settings_key );
        $this->email_settings = (array) get_option( $this->email_settings_key );
        $this->size_settings = (array) get_option( $this->size_settings_key );

        // Merge with defaults
        $this->general_settings = array_merge( array(
            'test_mode' => false,
            'checkout_page' => '',
            'thanks_page' => '',
            'dashboard_page' => '',
            'login_page' => '',
            'customer_notification' => '',
            'style' => '',
            'plugin_credit' => '',
            'post_type_slug' => 'items',
            'order_by' => '',
            'terms_and_conditions' => '',
            'disable_search' => '',
            'hide_original_price' => '',
            'show_collections' => 1,
            'show_licenses' => 1
        ), $this->general_settings );

        $this->payment_settings = array_merge( array(
            'default_gateway' => 'paypal',
            'paypal_email' => '',
            'currency' => 'USD',
            'paypal_additional_test_email' => ''
        ), $this->payment_settings );

        $user = get_user_by('email', get_option('admin_email') );
        if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ){
            $from_name = $user->first_name . ' ' . $user->last_name;
        } else {
            $from_name = null;
        }
        $msg = "Hi {first_name} {last_name},\nThanks for purchasing from my site. Here are your download links:\n{download_links}\nThanks!";

        $this->email_settings = array_merge( array(
            'from_name' => $from_name,
            'from_email' => get_option('admin_email'),
            'success_email_subject' => 'Your Purchase',
            'success_email_body' => $msg
        ), $this->email_settings );

        $this->size_settings = array_merge( array(
            'small_size_height' => '600',
            'small_size_width' => '800',
            'small_size_price' => '1',
            'medium_size_height' => '1200',
            'medium_size_width' => '1600',
            'medium_size_price' => '2',
            'large_size_height' => '1800',
            'large_size_width' => '2400',
            'large_size_price' => '3',
            'default_price' => '10',
            'default_price_group' => '',
        ), $this->size_settings );

        do_action( 'sell_media_load_settings_hook' );
    }

    /*
     * Registers the general settings via the Settings API,
     * appends the setting to the tabs array of the object.
     */
    function register_general_settings() {
        $this->plugin_settings_tabs[$this->general_settings_key] = 'General';

        register_setting( $this->general_settings_key, $this->general_settings_key, array( &$this, 'register_settings_validate') );
        add_settings_section( 'section_general', 'General Settings', array( &$this, 'section_general_desc' ), $this->general_settings_key );

        $settings['section_general']['fields'] = array(
            array(
                'id' => 'test_mode',
                'label' => __('Test Mode','sell_media'),
                'function' => array( &$this, 'field_general_test_mode' ),
                'key' => $this->general_settings_key
            ),
            array(
                'id' => 'checkout_page',
                'label' => __('Checkout Page','sell_media'),
                'function' => array( &$this, 'field_general_checkout_page' ),
                'key' => $this->general_settings_key
                ),
            array(
                'id' => 'thanks_page',
                'label' => __('Thanks Page','sell_media'),
                'function' => array( &$this, 'field_general_thanks_page' ),
                'key' => $this->general_settings_key
                ),
            array(
                'id' => 'dashboard_page',
                'label' => __('Dashboard Page','sell_media'),
                'function' => array( &$this, 'field_general_dashboard_page' ),
                'key' => $this->general_settings_key
                ),
            array(
                'id' => 'login_page',
                'label' => __('Login Page','sell_media'),
                'function' => array( &$this, 'field_general_login_page' ),
                'key' => $this->general_settings_key
                ),
            array(
                'id' => 'customer_notification',
                'label' => __('Customer Notification','sell_media'),
                'function' => array( &$this, 'field_general_customer_notification' ),
                'key' => $this->general_settings_key
                ),
            array(
                'id' => 'style',
                'label' => __('Style','sell_media'),
                'function' => array( &$this, 'field_general_style' ),
                'key' => $this->general_settings_key
                ),
            array(
                'id' => 'plugin_credit',
                'label' => __('Plugin Credit','sell_media'),
                'function' => array( &$this, 'field_general_plugin_credit' ),
                'key' => $this->general_settings_key
                ),
            array(
                'id' => 'post_type_slug',
                'label' => __('Post Type Slug','sell_media'),
                'function' => array( &$this, 'field_post_type_slug' ),
                'key' => $this->general_settings_key
                ),
            array(
                'id' => 'order_by',
                'label' => __('Order By','sell_media'),
                'function' => array( &$this, 'field_order_by' ),
                'key' => $this->general_settings_key
                ),
            array(
                'id' => 'terms_and_conditions',
                'label' => __('Terms and Conditions','sell_media'),
                'function' => array( &$this, 'field_terms_and_conditions' ),
                'key' => $this->general_settings_key
                ),
            array(
                'id' => 'disable_search',
                'label' => __('Disable Sell Media Search','sell_media'),
                'function' => array( &$this, 'field_disable_search' ),
                'key' => $this->general_settings_key
                ),
            array(
                'id' => 'hide_original_price',
                'label' => __('Hide Original Price','sell_media'),
                'function' => array( &$this, 'field_hide_original_price' ),
                'key' => $this->general_settings_key
                ),
            array(
                'id' => 'columns_to_show',
                'label' => __('Columns to Show','sell_media'),
                'function' => array( &$this, 'field_columns_to_show' ),
                'key' => $this->general_settings_key
                )
            );

        foreach( $settings as $k => $v ){
            foreach( $v['fields'] as $field ){
                add_settings_field(
                    $field['id'],
                    $field['label'],
                    $field['function'],
                    $field['key'],
                    $k
                );
            }
        }


        do_action( 'sell_media_general_settings_hook' );

    }

    /*
     * Registers the advanced settings and appends the
     * key to the plugin settings tabs array.
     */
    function register_payment_settings() {
        $this->plugin_settings_tabs[$this->payment_settings_key] = 'Payment';

        register_setting( $this->payment_settings_key, $this->payment_settings_key, array( &$this, 'register_settings_validate') );
        add_settings_section( 'section_payment', 'Payment Settings', array( &$this, 'section_payment_desc' ), $this->payment_settings_key );

        add_settings_field( 'default_gateway', __('Default Payment','sell_media'), array( &$this, 'field_payment_default_gateway' ), $this->payment_settings_key, 'section_payment' );

        add_settings_field( 'paypal_email', 'Paypal Email Address', array( &$this, 'field_payment_paypal_email' ), $this->payment_settings_key, 'section_payment' );
        add_settings_field( 'currency', 'Currency', array( &$this, 'field_payment_currency' ), $this->payment_settings_key, 'section_payment' );
        add_settings_field( 'paypal_additional_test_email', 'Paypal Additional Test Emails', array( &$this, 'field_payment_additional_email' ), $this->payment_settings_key, 'section_payment' );

        do_action( 'sell_media_payment_settings_hook' );

    }

    /*
     * Registers the email settings and appends the
     * key to the plugin settings tabs array.
     */
    function register_email_settings() {
        $this->plugin_settings_tabs[$this->email_settings_key] = 'Email';

        register_setting( $this->email_settings_key, $this->email_settings_key, array( &$this, 'register_settings_validate') );
        add_settings_section( 'section_email', 'Email Settings', array( &$this, 'section_email_desc' ), $this->email_settings_key );

        add_settings_field( 'from_name', 'From Name', array( &$this, 'field_email_from_name' ), $this->email_settings_key, 'section_email' );
        add_settings_field( 'from_email', 'From Email', array( &$this, 'field_email_from_email' ), $this->email_settings_key, 'section_email' );
        add_settings_field( 'success_email_subject', 'Email Subject', array( &$this, 'field_email_success_email_subject' ), $this->email_settings_key, 'section_email' );
        add_settings_field( 'success_email_body', 'Email Body', array( &$this, 'field_email_success_email_body' ), $this->email_settings_key, 'section_email' );

        do_action( 'sell_media_email_settings_hook' );

    }

    /*
     * Registers the size settings and appends the
     * key to the plugin settings tabs array.
     */
    function register_size_settings() {
        $this->plugin_settings_tabs[$this->size_settings_key] = 'Size & Price';

        register_setting( $this->size_settings_key, $this->size_settings_key, array( &$this, 'register_settings_validate') );

        add_settings_section( 'section_default_download_price', 'Original Download Price', array( &$this, 'section_size_desc' ), $this->size_settings_key );
        add_settings_field( 'default_price', 'Original Price', array( &$this, 'field_payment_default_price' ), $this->size_settings_key, 'section_default_download_price' );

        add_settings_section( 'section_default_price_group', 'Default Price Group', array( &$this, 'section_size_desc' ), $this->size_settings_key );
        add_settings_field( 'default_price_group', 'Select Default Price Group', array( &$this, 'field_payment_default_price_group' ), $this->size_settings_key, 'section_default_price_group' );

        add_settings_section( 'section_size', 'Image Size Settings', array( &$this, 'section_size_desc' ), $this->size_settings_key );
        add_settings_field( 'price_group', 'Price Groups', array( &$this, 'field_price_group' ), $this->size_settings_key, 'section_size' );

        add_settings_section( 'section_size_hook', '', array( &$this, 'section_size_hook' ), $this->size_settings_key );

    }


    /**
     * Print the tabs, table and form
     *
     * @author Zane M. Kolnik
     * @since 1.5.1
     */
    function field_price_group(){
        $price_group = New SellMediaNavStyleUI();
        $price_group->taxonomy = 'price-group';
        $price_group->setting_ui();
    }



    /*
     * Registers the misc settings and appends the
     * key to the plugin settings tabs array.
     */
    function register_misc_settings() {
        $this->plugin_settings_tabs[$this->misc_settings_key] = 'Misc';

        register_setting( $this->misc_settings_key, $this->misc_settings_key, array( &$this, 'register_settings_validate') );
        add_settings_section( 'section_misc', 'Misc Settings', array( &$this, 'section_misc_desc' ), $this->misc_settings_key );

    }

    /**
     * Validation callback
     * @since 1.0.9
     * @author Zane Matthew
     */
    function register_settings_validate( $fields ){

        $valid_inputs = array();

        if ( ! empty( $fields ) ){
            foreach( $fields as $field => $value ){

                switch( $field ){

                    /**
                     * Ensure that only integers are saved.
                     */
                    case 'small_size_width' :
                    case 'small_size_height' :
                    case 'medium_size_width' :
                    case 'medium_size_height' :
                    case 'large_size_width' :
                    case 'large_size_height' :
                        $value = (int)$value;
                        if ( $value == get_option('medium_size_w') || $value == get_option('large_size_w') ){
                            $value = ( $value + 1 );
                        }
                        break;

                    /**
                     * Ensure that float is saved, i.e. 10.55 vs. 10.55the
                     */
                    case 'small_size_price' :
                    case 'medium_size_price' :
                    case 'large_size_price' :
                    case 'default_price' :
                        $value = sprintf( "%0.2f", floatval( $value ) );
                        break;

                    /**
                     * Ensure that only valid email address is saved
                     */
                    case 'paypal_email' :
                    case 'from_email' :
                        if ( ! is_email( $value ) )
                            $value = null;
                        break;

                    case 'post_type_slug' :
                        $general = get_option('sell_media_general_settings');
                        if ( isset( $fields['post_type_slug'] ) && $fields['post_type_slug'] != $general['post_type_slug'] ){
                            flush_rewrite_rules();
                        }
                        break;
                }
                $valid_inputs[ $field ] = wp_filter_nohtml_kses( $value );
            }
        }
        return $valid_inputs;
    }

    /*
     * The following methods provide descriptions
     * for their respective sections, used as callbacks
     * with add_settings_section
     */
    function section_general_desc() { echo ''; }
    function section_payment_desc() { echo ''; }
    function section_email_desc() { echo ''; }
    function section_size_desc() { echo ''; }

    function section_size_hook() {
        do_action( 'sell_media_size_settings_hook' );
    }

    function section_misc_desc() {
        printf( '%s <a href="' . sell_media_plugin_data( $field='AuthorURI' ) . '/downloads/category/extensions/" class="button secondary" target="_blank">%s</a>',
            __( 'Settings for Extensions are shown below.', 'sell_media' ),
            __( 'Download Extensions for Sell Media' )
             );
        do_action( 'sell_media_misc_settings_hook' );
    }

    /*
     * General Option field callback, renders a
     * text input, note the name and value.
     */
    function field_general_test_mode() {
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[test_mode]" id="<?php echo $this->general_settings_key; ?>[test_mode]">
            <option value="0" <?php selected( $this->general_settings['test_mode'], 0 ); ?>><?php _e( 'No', 'sell_media' ); ?></option>
            <option value="1" <?php selected( $this->general_settings['test_mode'], 1 ); ?>><?php _e( 'Yes', 'sell_media' ); ?></option>
        </select>
        <span class="desc"><?php printf(__('To accept real payments, select No. To fully use test mode, you must have %1$s.'), '<a href="https://developer.paypal.com/" target="_blank">Paypal sandbox (test) account</a>' ); ?></span>

        <?php
    }

    /*
     * Checkout Page Option field callback
     */
    function field_general_checkout_page() {
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[checkout_page]" id="<?php echo $this->general_settings_key; ?>[checkout_page]">
            <?php $this->build_field_pages_select( 'checkout_page' ); ?>
        </select>
        <span class="desc"><?php _e( 'What page contains the <code>[sell_media_checkout]</code> shortcode? This shortcode generates the checkout cart.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Thanks Page Option field callback
     */
    function field_general_thanks_page() {
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[thanks_page]" id="<?php echo $this->general_settings_key; ?>[thanks_page]">
            <?php $this->build_field_pages_select( 'thanks_page' ); ?>
        </select>
        <span class="desc"><?php _e( 'What page contains the <code>[sell_media_thanks]</code> shortcode?', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Dashboard Page Option field callback
     */
    function field_general_dashboard_page() {
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[dashboard_page]" id="<?php echo $this->general_settings_key; ?>[dashboard_page]">
            <?php $this->build_field_pages_select( 'dashboard_page' ); ?>
        </select>
        <span class="desc"><?php _e( 'Where is your customer Dashboard page? This page will contain the <code>[sell_media_download_list]</code> shortcode.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Login Page Option field callback
     */
    function field_general_login_page() {
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[login_page]" id="<?php echo $this->general_settings_key; ?>[login_page]">
            <?php $this->build_field_pages_select( 'login_page' ); ?>
        </select>
        <span class="desc"><?php _e( 'Where is your customer login page? This page will contain the <code>[sell_media_login_form]</code> shortcode.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Customer Notification field callback
     */
    function field_general_customer_notification(){
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[customer_notification]" id="<?php echo $this->general_settings_key; ?>[customer_notification]">
            <option value="0" <?php selected( $this->general_settings['customer_notification'], 0 ); ?>><?php _e( 'No', 'sell_media' ); ?></option>
            <option value="1" <?php selected( $this->general_settings['customer_notification'], 1 ); ?>><?php _e( 'Yes', 'sell_media' ); ?></option>
        </select>
        <span class="desc"><?php _e( 'Notify the customer of their site registration.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Plugin Style
     */
    function field_general_style(){
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[style]" id="<?php echo $this->general_settings_key; ?>[style]">
            <option value="light" <?php selected( $this->general_settings['style'], 'light' ); ?>><?php _e( 'Light', 'sell_media' ); ?></option>
            <option value="dark" <?php selected( $this->general_settings['style'], 'dark' ); ?>><?php _e( 'Dark', 'sell_media' ); ?></option>
        </select>
        <span class="desc"><?php _e( 'Choose the style of your theme. Sell Media will load styles to match your theme.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Plugin Credit field callback
     */
    function field_general_plugin_credit(){
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[plugin_credit]" id="<?php echo $this->general_settings_key; ?>[plugin_credit]">
            <option value="0" <?php selected( $this->general_settings['plugin_credit'], 0 ); ?>><?php _e( 'No', 'sell_media' ); ?></option>
            <option value="1" <?php selected( $this->general_settings['plugin_credit'], 1 ); ?>><?php _e( 'Yes', 'sell_media' ); ?></option>
        </select>
        <span class="desc"><?php _e( 'Let your site visitors know you are using the Sell Media plugin?', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Post Type Slug field callback
     */
    function field_post_type_slug(){
        ?>
        <input type="text" name="<?php echo $this->general_settings_key; ?>[post_type_slug]" id="<?php echo $this->general_settings_key; ?>[post_type_slug]" value="<?php echo wp_filter_nohtml_kses( $this->general_settings['post_type_slug'] ); ?>" />
        <span class="desc"><?php _e( 'You can change the post type slug to: &quot;photos&quot; or &quot;downloads&quot;. The default slug is &quot;items&quot;', 'sell_media' ); ?></span>
        <?php
    }

    function field_order_by(){
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[order_by]" id="<?php echo $this->general_settings_key; ?>[order_by]">
            <option value="date-desc" <?php selected( $this->general_settings['order_by'], 'date-desc' ); ?>><?php _e( 'Date (Desc)', 'sell_media' ); ?></option>
            <option value="date-asc" <?php selected( $this->general_settings['order_by'], 'date-asc' ); ?>><?php _e( 'Date (ASC)', 'sell_media' ); ?></option>
            <option value="title-desc" <?php selected( $this->general_settings['order_by'], 'title-desc' ); ?>><?php _e( 'Item Title (Desc)', 'sell_media' ); ?></option>
            <option value="title-asc" <?php selected( $this->general_settings['order_by'], 'title-asc' ); ?>><?php _e( 'Item Title (ASC)', 'sell_media' ); ?></option>
        </select>
        <span class="desc"><?php _e( 'Choose the order of items for the archive pages.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Post Type terms and conditions field callback
     */
    function field_terms_and_conditions(){
        ?>
         <textarea name="<?php echo $this->general_settings_key; ?>[terms_and_conditions]" id="<?php echo $this->general_settings_key; ?>[terms_and_conditions]" style="width:50%;height:150px;" placeholder="<?php _e( 'Terms and Conditions', 'sell_media' ); ?>"><?php echo stripslashes_deep( wp_filter_nohtml_kses( $this->general_settings['terms_and_conditions'] ) ); ?></textarea>
        <p class="desc"><?php _e( 'These "Terms and Conditions" will show up on the checkout page. Users must agree to these terms before completing their purchase.', 'sell_media' ); ?></p>
        <?php
    }


    /*
     * Columns to show field callback
     */
    function field_columns_to_show(){
        $fields = array(
            'show_collection' => array(
                'label' => 'Collections'
                ),
            'show_license' => array(
                'label' => 'Licenses'
                ),
            'show_keywords' => array(
                'label' => 'Keywords'
                ),
            'show_creators' => array(
                'label' => 'Creators'
                )
            );
        ?>
        <?php foreach( $fields as $k => $v ) : ?>
            <input type="checkbox" value="1" name="<?php echo $this->general_settings_key; ?>[<?php echo $k; ?>] ?>" id="<?php echo $this->general_settings_key; ?>[<?php echo $k; ?>] ?>"
            <?php if( isset( $this->general_settings[ $k ] )  ) checked( $this->general_settings[ $k ], 1 ); ?>>
            <label for="<?php echo $this->general_settings_key; ?>[<?php echo $k; ?>] ?>" class="desc"><?php echo $v['label']; ?></label>
            <br />
        <?php endforeach; ?>
        <p class="desc"><?php _e( 'Select the columns to show on the admin page "All Items" page', 'sell_media' ); ?></p>
        <?php
    }


    /*
     * Disable search
     */
    function field_disable_search(){
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[disable_search]" id="<?php echo $this->general_settings_key; ?>[disable_search]">
            <option value="no" <?php selected( $this->general_settings['disable_search'], 'no' ); ?>><?php _e( 'No', 'sell_media' ); ?></option>
            <option value="yes" <?php selected( $this->general_settings['disable_search'], 'yes' ); ?>><?php _e( 'Yes', 'sell_media' ); ?></option>
        </select>
        <span class="desc"><?php _e( 'Set this to "no" if you do not want to use the built in Sell Media search.', 'sell_media' ); ?></span>
        <?php
    }


    /*
     * Hide original price
     */
    function field_hide_original_price(){
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[hide_original_price]" id="<?php echo $this->general_settings_key; ?>[hide_original_price]">
            <option value="no" <?php selected( $this->general_settings['hide_original_price'], 'no' ); ?>><?php _e( 'No', 'sell_media' ); ?></option>
            <option value="yes" <?php selected( $this->general_settings['hide_original_price'], 'yes' ); ?>><?php _e( 'Yes', 'sell_media' ); ?></option>
        </select>
        <span class="desc"><?php _e( 'You can also hide the original price by editing each individual item.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Paypal Email Option field callback
     */
    function field_payment_paypal_email() {
        ?>
        <input type="text" name="<?php echo $this->payment_settings_key; ?>[paypal_email]" value="<?php echo wp_filter_nohtml_kses( $this->payment_settings['paypal_email'] ); ?>" />
        <p class="desc"><?php printf( __('The email address used to collect Paypal payments. %1$s: You must setup IPN Notifications in Paypal to process transactions. %2$s. Here is the listener URL you need to add in Paypal: %3$s'), '<strong>'.__('IMPORTANT', 'sell_media').'</strong>', '<a href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_admin_IPNSetup#id089EG030E5Z" target="_blank">Read Paypal instructions</a>', '<code>' . site_url( '?sell_media-listener=IPN' ) . '</code>'); ?></p>
        <?php
    }

    /*
     * Currency Option field callback
     */
    function field_payment_currency() {
        ?>
        <select name="<?php echo $this->payment_settings_key; ?>[currency]" id="<?php echo $this->payment_settings_key; ?>[currency]">
            <option value="USD" <?php selected( $this->payment_settings['currency'], 'USD' ); ?>>US Dollars ($)</option>
            <option value="EUR" <?php selected( $this->payment_settings['currency'], 'EUR' ); ?>>Euros (€)</option>
            <option value="GBP" <?php selected( $this->payment_settings['currency'], 'GBP' ); ?>>Pounds Sterling (£)</option>
            <option value="AUD" <?php selected( $this->payment_settings['currency'], 'AUD' ); ?>>Australian Dollars ($)</option>
            <option value="BRL" <?php selected( $this->payment_settings['currency'], 'BRL' ); ?>>Brazilian Real ($)</option>
            <option value="CAD" <?php selected( $this->payment_settings['currency'], 'CAD' ); ?>>Canadian Dollars ($)</option>
            <option value="CZK" <?php selected( $this->payment_settings['currency'], 'CZK' ); ?>>Czech Koruna (Kč)</option>
            <option value="DKK" <?php selected( $this->payment_settings['currency'], 'DKK' ); ?>>Danish Krone</option>
            <option value="HKD" <?php selected( $this->payment_settings['currency'], 'HKD' ); ?>>Hong Kong Dollar ($)</option>
            <option value="HUF" <?php selected( $this->payment_settings['currency'], 'HUF' ); ?>>Hungarian Forint</option>
            <option value="ILS" <?php selected( $this->payment_settings['currency'], 'ILS' ); ?>>Israeli Shekel</option>
            <option value="JPY" <?php selected( $this->payment_settings['currency'], 'JPY' ); ?>>Japanese Yen (¥)</option>
            <option value="MYR" <?php selected( $this->payment_settings['currency'], 'MYR' ); ?>>Malaysian Ringgits</option>
            <option value="MXN" <?php selected( $this->payment_settings['currency'], 'MXN' ); ?>>Mexican Peso ($)</option>
            <option value="NZD" <?php selected( $this->payment_settings['currency'], 'NZD' ); ?>>New Zealand Dollar ($)</option>
            <option value="NOK" <?php selected( $this->payment_settings['currency'], 'NOK' ); ?>>Norwegian Krone</option>
            <option value="PHP" <?php selected( $this->payment_settings['currency'], 'PHP' ); ?>>Philippine Pesos</option>
            <option value="PLN" <?php selected( $this->payment_settings['currency'], 'PLN' ); ?>>Polish Zloty</option>
            <option value="SGD" <?php selected( $this->payment_settings['currency'], 'SGD' ); ?>>Singapore Dollar ($)</option>
            <option value="SEK" <?php selected( $this->payment_settings['currency'], 'SEK' ); ?>>Swedish Krona</option>
            <option value="CHF" <?php selected( $this->payment_settings['currency'], 'CHF' ); ?>>Swiss Franc</option>
            <option value="TWD" <?php selected( $this->payment_settings['currency'], 'TWD' ); ?>>Taiwan New Dollars</option>
            <option value="THB" <?php selected( $this->payment_settings['currency'], 'THB' ); ?>>Thai Baht</option>
            <option value="TRY" <?php selected( $this->payment_settings['currency'], 'TRY' ); ?>>Turkish Lira (TL)</option>
            <option value="ZAR" <?php selected( $this->payment_settings['currency'], 'ZAR' ); ?>>South African rand (R)</option>
        </select>
        <span class="desc"><?php _e( 'The currency in which you accept payment.', 'sell_media' ); ?></span>

        <?php
    }


    /*
     * Paypal additional test emails
     */
    function field_payment_additional_email(){
        ?>
        <input type="text" class="regular-text" name="<?php echo $this->payment_settings_key; ?>[paypal_additional_test_email]" value="<?php echo wp_filter_nohtml_kses( $this->payment_settings['paypal_additional_test_email'] ); ?>" />
        <div class="desc"><?php _e('This is useful when debugging Paypal. Enter a comma separeted list of emails, and when a purchase is made the same email that is sent to the buyer will be sent to the recipients in the above list.', 'sell_media' ); ?></div>
        <?php
    }


    /*
     * Default gateway
     */
    function field_payment_default_gateway(){
        $gateways = array(
            array(
                'id' => 'paypal',
                'name' => __('Paypal','sell_media')
                )
            );

        $gateways = apply_filters('sell_media_payment_gateway', $gateways); ?>
        <select name="<?php echo $this->payment_settings_key; ?>[default_gateway]" value="<?php echo $this->payment_settings['default_gateway']; ?>" id="sell_media_price_group_select">
            <?php foreach( $gateways as $gateway ) : ?>
                <option value="<?php echo $gateway['id']; ?>" <?php selected( $this->payment_settings['default_gateway'], $gateway['id'] ); ?>><?php echo $gateway['name']; ?></option>
            <?php endforeach; ?>
        </select>
    <?php }


    /*
     * Default Price Option field callback
     */
    function field_payment_default_price() {
        ?>
        <span class="description"><?php echo sell_media_get_currency_symbol(); ?></span>
        <input type="number" step="0.01" class="small-text" min="0" name="<?php echo $this->size_settings_key; ?>[default_price]" value="<?php echo wp_filter_nohtml_kses( sprintf("%0.2f", $this->size_settings['default_price']) ); ?>" />
        <span class="desc"><?php _e( 'The default price of new items and bulk uploads. You can set unique prices by editing each individual item.', 'sell_media' ); ?></span>
        <?php
    }

     /*
     * Default Price Group Option field callback
     */
    function field_payment_default_price_group() {
        ?>
        <select name="<?php echo $this->size_settings_key; ?>[default_price_group]" value="<?php echo $this->size_settings['default_price_group']; ?>" id="sell_media_price_group_select">
            <option value="" data-price="0"><?php _e( 'None', 'sell_media' ); ?></option>
            <?php foreach( get_terms('price-group',array('hide_empty'=>false, 'parent'=>0)) as $term ) : ?>
                <option value="<?php echo $term->term_id; ?>" <?php selected( $this->size_settings['default_price_group'], $term->term_id ); ?>><?php echo $term->name; ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /*
     * From Name Option field callback
     */
    function field_email_from_name() {
        ?>
        <input type="text" name="<?php echo $this->email_settings_key; ?>[from_name]" value="<?php echo wp_filter_nohtml_kses( $this->email_settings['from_name'] ); ?>" />
        <span class="desc"><?php _e( 'The name associated with all outgoing email.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * From Email Option field callback
     */
    function field_email_from_email() {
        ?>
        <input type="text" name="<?php echo $this->email_settings_key; ?>[from_email]" value="<?php echo wp_filter_nohtml_kses( $this->email_settings['from_email'] ); ?>" />
        <span class="desc"><?php _e( 'The email address used for all outgoing email.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Success Email Subject Option field callback
     */
    function field_email_success_email_subject() {
        ?>
        <input type="text" name="<?php echo $this->email_settings_key; ?>[success_email_subject]" value="<?php echo wp_filter_nohtml_kses( $this->email_settings['success_email_subject'] ); ?>" />
        <span class="desc"><?php _e( 'The email subject on successful purchase emails.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Success Email Body Option field callback
     */
    function field_email_success_email_body() {
        ?>
        <textarea name="<?php echo $this->email_settings_key; ?>[success_email_body]" id="<?php echo $this->email_settings_key; ?>[success_email_body]" style="width:50%;height:150px;"><?php echo wp_filter_nohtml_kses( $this->email_settings['success_email_body'] ); ?></textarea>
        <p class="desc"><?php _e( 'This e-mail message is sent to your customers in case of successful and cleared payment. You can use the following keywords: {first_name}, {last_name}, {payer_email}, {download_links}. Be sure to include the {download_links} tag, otherwise your buyers won\'t receive their download purchases.', 'sell_media' ); ?></p>
         <?php
    }


    /*
     * Helper for building select options for Pages
     */
    function build_field_pages_select( $option ) {
        $pages = get_pages();
        foreach ( $pages as $page ) { ?>
            <option value="<?php echo $page->ID; ?>" <?php selected( $this->general_settings[''. $option .''], $page->ID ); ?>><?php echo $page->post_title; ?></option>
        <?php }
    }

    /*
     * Called during admin_menu, adds an options
     * page under plugin, rendered
     * using the plugin_options_page method.
     */
    function add_admin_menus() {
        add_submenu_page( 'edit.php?post_type=sell_media_item', __('Settings', 'sell_media'), __('Settings', 'sell_media'),  'manage_options', $this->plugin_options_key, array( &$this, 'plugin_options_page' ) );
    }

    /*
     * Plugin Options page rendering goes here, checks
     * for active tab and replaces key with the related
     * settings key. Uses the plugin_options_tabs method
     * to render the tabs.
     */
    function plugin_options_page() {
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
        ?>
        <div class="wrap">
            <?php $this->plugin_options_tabs(); ?>
            <form method="post" action="options.php" enctype="multipart/form-data">
                <?php settings_fields( $tab ); ?>
                <?php do_settings_sections( $tab ); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /*
     * Renders our tabs in the plugin options page,
     * walks through the object's tabs array and prints
     * them one by one. Provides the heading for the
     * plugin_options_page method.
     */
    function plugin_options_tabs() {
        $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;

        screen_icon( 'options-general' );
        echo '<h2 class="nav-tab-wrapper">';
        foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?post_type=' . $this->plugin_post_type_key . '&page=' . $this->plugin_options_key . '&tab=' . $tab_key . '&term_parent=new_term">' . $tab_caption . '</a>';
        }
        echo '</h2>';
    }

};

// Initialize the plugin
add_action( 'plugins_loaded', create_function( '', '$sell_media_settings = new SellMediaSettings;' ) );