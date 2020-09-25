<?php

/*
Plugin Name:        Extra Details On Shop Page
Plugin URI:
Description:        You can add extra details for each product on shop page of woocommerce.
Version:            1.0
Author:             Mostafa Rouzbahani
Author URI:
License:            GPL-2.0+
License URI:        http://www.gnu.org/licenses/gpl-2.0.txt
*/

/**
 * If this file is called directly, abort.
 */
	if ( ! defined( 'WPINC' ) ) {
		die;
	}


/**
 * Activate the plugin.
 */
	function EDSP_activate() {

		if(!get_option('EDSP_separator_format')){
			add_option('EDSP_separator_format', '<br>');
		}

	}
	register_activation_hook( __FILE__, 'EDSP_activate' );

/**
 * Deactivation hook.
 */
	function EDSP_deactivate() {

	}
	register_deactivation_hook( __FILE__, 'EDSP_deactivate' );

/**
 * Editing woocommerce shop page.
 */

	function EDSP_display_details_after_price(){
		global $product;
		$cnt = 0;
		$EDSP_attributes_options = get_option('EDSP_attributes');
		$EDSP_separator_format_options = get_option('EDSP_separator_format');
		$EDSP_class_options = get_option('EDSP_classes');

		if(!empty($EDSP_attributes_options)){
			echo '<div class="product_detail inner_product_header"><div class="inner_product_header_table"><div class="inner_product_header_cell"><p class="woocommerce-loop-product__title"><span class="';
			if(!empty($EDSP_class_options)){echo ' '. esc_attr($EDSP_class_options);}
            echo'">';
			foreach($EDSP_attributes_options as $EDSP_attributes_option){
			    if(!empty( $product->get_attribute( $EDSP_attributes_option ))){
			        if( $cnt != 0 ) echo ' '.$EDSP_separator_format_options.' ';
				    echo esc_html(ucwords( $EDSP_attributes_option )).': '.esc_html( $product->get_attribute( $EDSP_attributes_option ) );
                    $cnt ++;
                }
            }
			echo '</span></p></div></div></div>';
        }

	}

	add_action( 'woocommerce_after_shop_loop_item', 'EDSP_display_details_after_price', 9 );

/**
 * Admin page
 */

    /**
     * custom option and settings
     */
    function EDSP_settings_init() {

	    // Register a new setting for "EDSP" page.
	    register_setting( 'EDSP', 'EDSP_options' );

        // Register a new section in the "EDSP" page.
        add_settings_section(
            'EDSP_section_developers',
            __( 'You can change the EDSP options in this page.', 'EDSP' ),
            '',
            'EDSP'
        );

        // Register a new field in the "EDSP_section_developers" section, inside the "EDSP" page.
        $fields = [
            [
                'id'        =>  'EDSP_attributes',
                'title'     =>  __( 'Select the attributes', 'EDSP' ),
                'callback'  =>  'EDSP_section_developers_fields_cb',
                'page'      =>  'EDSP',
                'section'   =>  'EDSP_section_developers',
                'array'     =>  [ 'label_for'         => 'EDSP_attributes_field[]' ],
            ],
	        [
		        'id'        =>  'EDSP_separator_format',
		        'title'     =>  __( 'Separator Format', 'EDSP' ),
		        'callback'  =>  'EDSP_section_developers_fields_cb',
		        'page'      =>  'EDSP',
		        'section'   =>  'EDSP_section_developers',
		        'array'     =>  [ 'label_for'         => 'EDSP_separator_format_field' ],
	        ],
	        [
		        'id'        =>  'EDSP_css_class',
		        'title'     =>  __( 'CSS Class', 'EDSP' ),
		        'callback'  =>  'EDSP_section_developers_fields_cb',
		        'page'      =>  'EDSP',
		        'section'   =>  'EDSP_section_developers',
		        'array'     =>  [ 'label_for'         => 'EDSP_css_class_field' ],
	        ],

        ];


        foreach ( $fields as $field ){
	        add_settings_field(
		        $field['id'],
                $field['title'],
                $field['callback'],
                $field['page'],
                $field['section'],
		        $field['array']
	        );
        }
    }

    /**
     * Register our EDSP_settings_init to the admin_init action hook.
     */
    add_action( 'admin_init', 'EDSP_settings_init' );


    /**
     * Custom option and settings:
     *  - callback functions
     */


    /**
     * EDSP attributes field callback function.
     *
     * WordPress has magic interaction with the following keys: label_for, class.
     * - the "label_for" key value is used for the "for" attribute of the <label>.
     * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
     * Note: you can add custom key value pairs to be used inside your callbacks.
     *
     * @param array $args
     */
    function EDSP_section_developers_fields_cb( $args ) {
        switch ( $args['label_for'] ){

            case 'EDSP_attributes_field[]':
                // global attributes
	            $global_attributes = wc_get_attribute_taxonomy_labels();

	            //product attributes
	            $product_attributes = EDSP_product_attributes();

	            //merging global and product attributes
	            $attr_tax = array_unique (array_merge ($global_attributes, $product_attributes));

	            //option tag
		        $EDSP_attributes_options = get_option('EDSP_attributes');
		        $arr = '';
	            foreach( $attr_tax as $tax ) {
		            $arr = $arr .  '<option value="'.$tax.'"';
		            if(!empty($EDSP_attributes_options)){
			            if(in_array($tax, $EDSP_attributes_options)){ $arr = $arr . ' selected ';}
		            }
		            $arr = $arr . '>'.$tax.'</option>';
	            }

	            //frontend
		        ?>
                <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
                        name="<?php echo esc_attr( $args['label_for'] ); ?>" multiple>
                    <?php echo wp_kses($arr, ['option' => ['selected' => [], 'value' => []]]); ?>
                </select>
                <p class="description">
			        <?php esc_html_e( 'Use Ctrl key to select multiple attributes which you want to show on the shop page.', 'EDSP' ); ?>
                </p>
		        <?php
                break;

            case 'EDSP_separator_format_field';
	            $EDSP_separator_format_options = get_option('EDSP_separator_format');
                ?>
                <fieldset>
                    <label><input type="radio" name="<?php echo esc_attr( $args['label_for'] ); ?>" value="<br>" <?php if( $EDSP_separator_format_options == '<br>'){ echo esc_html('checked=checked'); } ?>> New line</label><br>
                    <label><input type="radio" name="<?php echo esc_attr( $args['label_for'] ); ?>" value="|" <?php if( $EDSP_separator_format_options == '|'){ echo esc_html('checked=checked'); } ?> > Vertical line, |</label>
                </fieldset>

                <?php
                break;

	        case 'EDSP_css_class_field';
		        $EDSP_class_options = get_option('EDSP_classes');
		        ?>
                <input name="<?php echo esc_attr( $args['label_for'] ); ?>"  type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" aria-describedby="<?php echo esc_attr( $args['label_for'] ); ?>-description" value="<?php if(!empty($EDSP_class_options)){ echo esc_attr($EDSP_class_options);} ?>"  class="regular-text code">
                <p class="description">
                    <?php esc_html_e('Enter the css classes to apply. Separate them by space (example: dark large)', 'EDSP') ?>
                </p>

		        <?php
		        break;
        }

    }

    /**
     * Get each products attributes.
     */
    function EDSP_product_attributes() {
        $product_attributes = [];
        $loop               = new WP_Query( [ 'post_type' => 'product' ] );
        while ( $loop->have_posts() ) : $loop->the_post();
            global $product;
            $attributes = $product->get_attributes();
            foreach ( $attributes as $attribute ) {
                $product_attributes[ wc_attribute_label( $attribute->get_name() ) ] = wc_attribute_label( $attribute->get_name() );
            }
        endwhile;
        wp_reset_query();
        return $product_attributes;
    }

    /**
     * Add the top level menu page.
     */
	function EDSP_admin_page(){
		add_menu_page(
		        'Extra info on woocommerce shop page',
                'EDSP',
                'manage_options',
                'EDSP_admin_menu',
                'EDSP_admin_page_scripts',
                '',
                200
        );
	}

    /**
     * Register our EDSP_admin_page to the admin_menu action hook.
     */
	add_action('admin_menu', 'EDSP_admin_page');


    /**
     * Top level menu callback function
     */
	function EDSP_admin_page_scripts(){

		// check if the user have submitted the settings and security check.
		if ( isset( $_POST['submit_EDSP_options'] )
             && current_user_can( 'manage_options' )
		     &&  isset( $_POST['_wpnonce'] )
		     &&  wp_verify_nonce( $_POST['_wpnonce'], 'EDSP-options' )
        ) {

			// POST request validation or sanitize
		    if ( isset( $_POST['EDSP_attributes_field'] ) || empty( $_POST['EDSP_attributes_field'] )){

				if ( !empty ($_POST['EDSP_attributes_field']) ){
				    //common attributes between form POST and global&&product attributes.
					$validate_attributes_field = array_intersect( $_POST['EDSP_attributes_field'], array_unique (array_merge (wc_get_attribute_taxonomy_labels(), EDSP_product_attributes()))
				);
					update_option('EDSP_attributes', $validate_attributes_field);
                }
				else{
					update_option('EDSP_attributes', '');
                }

			}

			if ( isset( $_POST['EDSP_separator_format_field'] )){

				$EDSP_separator_format_field_valid_values = ['<br>','|',];
				if( in_array( $_POST['EDSP_separator_format_field'], $EDSP_separator_format_field_valid_values ) ) {
					update_option('EDSP_separator_format', $_POST['EDSP_separator_format_field']);
				}
			}

			if (isset($_POST['EDSP_css_class_field'])){
				update_option('EDSP_classes', sanitize_text_field($_POST['EDSP_css_class_field']));
			}

			// add settings saved message
			add_settings_error(
			        'EDSP_messages',
                    'EDSP_admin_submit_message',
                    __( 'Settings Saved', 'EDSP' ),
                    'success'
            );
		}

		// show error/update messages
		settings_errors( 'EDSP_messages' );

		// admin page frontend
        if ( class_exists( 'woocommerce' ) )
            {
            ?>
            <div class="wrap">
                <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
                <form action="" method="post">
                    <?php
                    // output security fields for the registered setting "EDSP"
                    settings_fields( 'EDSP' );
                    // output setting sections and their fields
                    // (sections are registered for "EDSP", each field is registered to a specific section)
                    do_settings_sections( 'EDSP' );
                    // output save settings button
                    submit_button( 'Save Settings', 'primary', 'submit_EDSP_options' );
                    ?>
                </form>
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="position: fixed; bottom: 0; right: 0;">
                    <input type="hidden" name="cmd" value="_s-xclick" />
                    <input type="hidden" name="hosted_button_id" value="L8DTYUT7FTHR4" />
                    <input type="image" src="https://www.paypalobjects.com/en_US/AT/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
                    <img alt="" border="0" src="https://www.paypal.com/en_AT/i/scr/pixel.gif" width="1" height="1" />
                </form>

            </div>
            <?php
            }

        else
            {
            ?>
            <div class="wrap">
                <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
                <h2><?php esc_html_e('Please first activate Woocommerce.', 'EDSP') ?> </h2>
            </div>
            <?php
            }


	}