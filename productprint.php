<?php
/**
 * Plugin Name: ProductPrint
 * Author URI: http://togethernet.ltd.uk
 * Plugin URI: http://productprint.com
 * Description: WooCommerce extension to create printer-friendly product pages with tailored headers and footers.
 * Version: 2.0.4
 * Author: Togethernet
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Tags: WooCommerce, print, product, pdf
 * Requires at least: 3.5
 * 
 * Text Domain productprint
 */


// Prevent direct file access
if( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

defined('SB_DS') or define('SB_DS', DIRECTORY_SEPARATOR); //this checks for the separator on the hosting environment. So "/" for linux "\" for windows
define('SC_productprint_PLUGIN_DIR', dirname(__FILE__)); //the directory to the plugin on the web server
define('SC_productprint_PLUGIN_URL', WP_PLUGIN_URL . '/' . basename(SC_productprint_PLUGIN_DIR)); //the url directory of the plugin on the browser

function pp_validate_options( $input ) {
	// Sanitize textarea input (strip html tags, and escape characters)
	$input['header_image'] = wp_filter_nohtml_kses($input['header_image']);
	$input['footer_image'] = wp_filter_nohtml_kses($input['footer_image']);
	$input['button_legend'] = wp_filter_nohtml_kses($input['button_legend']);
	$input['printer_icon'] = wp_filter_nohtml_kses($input['printer_icon']);
	return $input;
}

function pp_init() { /*** tell WordPress about the new set of options ***/
	register_setting( 'pp_plugin_options', 'productprint_ops', 'pp_validate_options' );
}
add_action('admin_init', 'pp_init' );


class SC_productprint { //the main plugin class, main logic is here for admin page

	public static $textdomain = 'productprint';
	
	private $settings_page_handle = 'productprint_options_handle';

 	public function __construct () {

		// if this is not the admin page, or the user is not an admin, go to the redirect handler

		if(!is_admin()) add_action('template_redirect', array($this, 'action_template_redirect'));

		add_action('init', array($this,'productprint_localize') );  //makes sure that the localization is run at the beginning

		add_action('admin_menu', array($this, 'add_menu_entry')); //adds the menu on the admin page for the application

		add_action('admin_print_scripts', array($this, 'productprint_addjavascriptfiles')); //loads plugins scripts
		
		$option_name = 'productprint_ops' ; // Initialise the database variables

		if ( get_option( $option_name ) !== false ) {
    	// The option already exists, so we just take a copy.
			$options = get_option('productprint_ops'); //gets the variables stored in the plugin options
		} else {
    	// The options haven't been added yet. We'll add them.
    		$deprecated = null;
    		$autoload = 'no';
			//default setting for the plugin, normally used after first install as a default
			$sampleprintericon = SC_productprint_PLUGIN_URL . '/assets/default-printer-icon.png';
			$options = array(
						'featured_image' => 1, 
						'gallery' => 1, 
						'product_description' => 1,
						'price'=> 1, 
						'product_attributes'=> 1, 
						'short_description' => 1, 
						'stock_level' => 0, 
						'reviews' => 0,
						'img_position' => 'right',
						'img_width' => '50%',
						'img_marginleft' => '20px',
						'img_marginright' => '0px',
						'img_margintop' => '0px',
						'img_marginbottom' => '20px',
						'show_border' => '1',
						'gallery_img_width' =>'15%',
						'gallery_border' => '1',
						'font_family' => 'Arial',
						'font_size' => '16px',
						'button_position' => '3', 
						'button_legend' => 'Print',
						'button_marginleft' => '0px',
						'button_marginright' => '0px',
						'button_margintop' => '0px',
						'button_marginbottom' => '0px',
						'sku' => '',
						'printer_icon' => $sampleprintericon,
						);
    		add_option( $option_name, $options, $deprecated, $autoload );
		}
		
		switch($options['button_position']) { // Choose where to display the button on the single product page
		case 1:
		add_action('woocommerce_before_single_product', array($this, 'productprint_button')); break;
		case 2:
		add_action('woocommerce_before_single_product_summary', array($this, 'productprint_button')); break;
		case 3:
		add_action('woocommerce_single_product_summary', array($this, 'productprint_button'), 7); break;
		case 4:
		add_action('woocommerce_before_add_to_cart_form', array($this, 'productprint_button'), 12); break;
		case 5:
		add_action('woocommerce_product_meta_end', array($this, 'productprint_button'), 25); break;
		case 6:
		add_action('woocommerce_after_single_product_summary', array($this, 'productprint_button'), 35); break;
		case 7:
		add_action('woocommerce_after_single_product', array($this, 'productprint_button')); break;

		}; // end Switch statement
 	} // end construct

    public function productprint_addjavascriptfiles() {
	    // add the scripts to the admin page
	    // the javascript files to open up the media manager
	    wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_script( 'productprint-script', plugins_url( '/js/admin.js', __FILE__ ) );
    }

    public function productprint_localize() {
    	// Localization
    	load_plugin_textdomain('productprint', false, dirname(plugin_basename(__FILE__)). "/languages" );
    }

	public function add_menu_entry() {
		// add the page to WordPress and call the function that is responsible of displaying the plugin settings
		add_submenu_page( 'options-general.php', 'ProductPrint', 'ProductPrint', 'manage_options', $this->settings_page_handle, array( $this, 'productprint2_settings' ) );
	}

    public function productprint_settings() {
    	//default setting for the plugin, normally used after first install as a default
    	$sampleprintericon = SC_productprint_PLUGIN_URL . '/assets/default-printer-icon.png';
    	$def = array(	'featured_image' => '0', 
						'gallery' => '0', 
						'product_description' => '0', 
						'price' => '0', 
						'product_attributes' => '0', 
						'short_description' => '0', 
						'stock_level' => '0', 
						'reviews' => '0',
						'img_position' => 'right',
						'img_width' => '50%',
						'img_marginleft' => '20px',
						'img_marginright' => '0px',
						'img_margintop' => '0px',
						'img_marginbottom' => '20px',
						'show_border' => '0',
						'gallery_img_width' =>'15%',
						'gallery_border' => '0',
						'font_family' => 'Arial',
						'font_size' => '16px',
						'button_position' => '3', 
						'button_legend' => 'Print',
						'button_marginleft' => '0px',
						'button_marginright' => '0px',
						'button_margintop' => '0px',
						'button_marginbottom' => '0px',
						'sku' => '',
						'printer_icon' => $sampleprintericon,
						);
    }

	public function productprint2_settings() { // Admin page settings - set up the two tabs

	    $tab = 'tab1';
        if ( isset( $_REQUEST[ 'tab' ] ) )
            switch ( $_REQUEST[ 'tab' ] ) {
		    case 'tab2':
			    $tab = 'tab2'; break;	
		    default:
			    $tab = 'tab1'; break;
        	}
		?>

		<div class="wrap">
		    <h2><img style="margin-right:15px; vertical-align: middle" src="<?php print (SC_productprint_PLUGIN_URL . '/assets/icon.png') ?>"><?php _e('ProductPrint Settings', 'productprint'); ?></h2>
			<div id="icon-options-general" class="icon32"></div>
			<h2 class="nav-tab-wrapper">
				<a href="<?php echo admin_url( 'options-general.php?page=' . $this->settings_page_handle ) ?>" class="nav-tab <?php echo ( $tab == 'tab1' ) ? 'nav-tab-active' : '' ?>">
					<?php _e('Options', 'productprint'); ?>
				</a>
				<a href="<?php echo admin_url( 'options-general.php?page=' . $this->settings_page_handle . '&tab=tab2' ) ?>" class="nav-tab <?php echo ( $tab == 'tab2' ) ? 'nav-tab-active' : '' ?>">
					<?php _e('Go Pro', 'productprint'); ?>
				</a>
			</h2>
			<div class="metabox-holder">
			<?php
				switch ( $tab ) {
				    case 'tab2':
					    $this->settings_page_tab2(); break;
				    default:
				        $this->settings_page_tab1(); break;
				}
			?>
			</div> <!-- .metabox-holder -->
		</div> <!-- .wrap -->
		<?php
	}



	private function settings_page_tab1() { // tab 1 is for the plugin options
        // define the KEYS to available image positions.
        $img_positions = array( 'left', 'none', 'right' );

        //the font for the text page, 
        $fonts = array('Arial', 'Calibri', 'Courier', 'Garamond', 'Georgia', 'Helvetica', 'Minion', 'Monospace', 'Palatino', 'Sans-serif', 'Serif', 'Times', 'Times New Roman', 'Verdana');

		?>
		<div id="post-body1">
			<div id="post-body-content1">
				<div class="postbox">
					<div class="inside">
			            <form action="options.php" method="post">
			                <?php
				                settings_fields('pp_plugin_options');
				                $options = get_option('productprint_ops');
				                //gets the variables stored in the plugin options

				                /*** $options should be set assuming user has visited the admin pages once ***/
				                /* $options = array_merge($def, $options); */
			                ?>		
			                <h3><?php _e('Print button', 'productprint'); ?></h3>
				            <table>
					            <th>
					            <tr>
					            <td style="width: 250px;"></td>
					            <td style="width: 250px;"></td>
					            </tr>
					            </th>
					            <tr><td><label><?php _e('Position on the page', 'productprint'); ?></label></td><td>
						
						        <select name="productprint_ops[button_position]" id="button_position">	
						
						            <option value="1" <?php selected($options['button_position'], 1); ?>> <?php _e('Before product', 'productprint'); ?></option>		
						            <option value="2" <?php selected($options['button_position'], 2); ?>> <?php _e('Before product summary', 'productprint'); ?></option>		
						            <option value="3" <?php selected($options['button_position'], 3); ?>> <?php _e('Product summary', 'productprint'); ?></option>		
						            <option value="4" <?php selected($options['button_position'], 4); ?>> <?php _e('Before add to cart form', 'productprint'); ?></option>		
						            <option value="5" <?php selected($options['button_position'], 5); ?>> <?php _e('After product meta', 'productprint'); ?></option>	
						            <option value="6" <?php selected($options['button_position'], 6); ?>> <?php _e('After product summary', 'productprint'); ?></option>		
						            <option value="7" <?php selected($options['button_position'], 7); ?>> <?php _e('After product', 'productprint'); ?></option>
		
						        </select>
						        </td></tr>
								
					            <tr><td><span><?php _e('Label for the button', 'productprint'); ?></span></td><td>						
					                <input id="button_legend" type="text" size="20" name="productprint_ops[button_legend]" value="<?php print sanitize_text_field($options['button_legend']) ?>" /></td></tr>		
					                <span> <?php _e('Append either px or % to the button margin values.', 'productprint'); ?> </span>                					
                				<tr><td><span><?php _e('Left margin', 'productprint'); ?></span></td><td><input type="text" name="productprint_ops[button_marginleft]" value="<?php print $options['button_marginleft'] ?>" /></td></tr>		
					            <tr><td><span><?php _e('Right margin', 'productprint'); ?></span></td><td><input type="text" name="productprint_ops[button_marginright]" value="<?php print $options['button_marginright'] ?>" /></td></tr>		
					            <tr><td><span><?php _e('Top margin', 'productprint'); ?></span></td><td><input type="text" name="productprint_ops[button_margintop]" value="<?php print $options['button_margintop'] ?>" /></td></tr>		
					            <tr><td><span><?php _e('Bottom margin', 'productprint'); ?></span></td><td><input type="text" name="productprint_ops[button_marginbottom]" value="<?php print $options['button_marginbottom'] ?>" /></td></tr>		
				            </table>
				            <p><label><?php _e('Select a printer icon from the media library, or upload one. A good size is around 20x20 px.', 'productprint'); ?></label></p>
				            <div>
                                <input type="text" name="productprint_ops[printer_icon]" id="image_url" class="regular-text" value="<?php echo esc_url_raw($options['printer_icon'])?>" >
                                <input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Select Icon" >
                                <p><?php _e ('Current selection: ', 'productprint'); ?><img src="<?php echo esc_url($options['printer_icon'])?>"></p>
                            </div>
				            <?php submit_button(); ?>		
				            <hr />
				            <p>
							
				            <h3><?php _e('Select the elements to be printed', 'productprint'); ?></h3>

				            <table>
				                <th>
				                <tr>
				                <td style="width: 250px;"></td>
				                <td style="width: 250px;"></td>
				                </tr>
				                </th>
				                <tr>
				                <td>
					            <tr><td><span><?php _e('Show short description?', 'productprint'); ?></span></td><td><select name="productprint_ops[short_description]"><option value='1'><?php _e('Yes', 'productprint'); ?></option><option value='0' 
						        <?php if (isset($options['short_description']) && $options['short_description'] == 0)
							        _e ('Selected=', 'productprint'); ?> ?>
						        <?php _e('No', 'productprint'); ?></option></select></td></tr>
								
					            <tr><td><span><?php _e('Show description?', 'productprint'); ?></span></td><td><select name="productprint_ops[product_description]"><option value='1'><?php _e('Yes', 'productprint'); ?></option><option value='0' 
							    <?php if (isset($options['product_description']) && $options['product_description'] == 0)
    							    _e ('Selected=', 'productprint'); ?> ?>
							    <?php _e('No', 'productprint'); ?></option></select></td></tr>
								
					            <tr><td><span><?php _e('Show price?', 'productprint'); ?></span></td><td><select name="productprint_ops[price]"><option value='1'><?php _e('Yes', 'productprint'); ?></option><option value='0' 
							    <?php if (isset($options['price']) && $options['price'] == 0)
							        _e ('Selected=', 'productprint'); ?> ?>
							    <?php _e('No', 'productprint'); ?></option></select></td></tr>
														
					            <tr><td><span><?php _e('Show SKU?', 'productprint'); ?></span></td><td><select name="productprint_ops[sku]"><option value='1'><?php _e('Yes', 'productprint'); ?></option><option value='0' 
							    <?php if (isset($options['sku']) && $options['sku'] == 0)
    							    _e ('Selected=', 'productprint'); ?> ?>
							    <?php _e('No', 'productprint'); ?></option></select></td></tr>
							
					            <tr><td><span><?php _e('Show attributes?', 'productprint'); ?></span></td><td><select name="productprint_ops[product_attributes]"><option value='1'><?php _e('Yes', 'productprint'); ?></option><option value='0' 
							    <?php if (isset($options['product_attributes']) && $options['product_attributes'] == 0)
							        _e ('Selected=', 'productprint'); ?> ?>
							    <?php _e('No', 'productprint'); ?></option></select></td></tr>
								
					            <tr><td><span><?php _e('Show stock level?', 'productprint'); ?></span></td><td><select name="productprint_ops[stock_level]"><option value='1'><?php _e('Yes', 'productprint'); ?></option><option value='0' 
							    <?php if (isset($options['stock_level']) && $options['stock_level'] == 0)
    						    	_e ('Selected=', 'productprint'); ?> ?>
							    <?php _e('No', 'productprint'); ?></option></select></td></tr>
								
					            <tr><td><span><?php _e('Show reviews?', 'productprint'); ?></span></td><td><select name="productprint_ops[reviews]"><option value='1'><?php _e('Yes', 'productprint'); ?></option><option value='0' 
							    <?php if (isset($options['reviews']) && $options['reviews'] == 0)
							        _e ('Selected=', 'productprint'); ?> ?>
							    <?php _e('No', 'productprint'); ?></option></select></td></tr>

				            </table>				

				            <?php submit_button(); ?>				
				            <hr />

				            <!-- featured image settings -->
		
					        <p>
					        <h3><?php _e('Featured Image Settings', 'productprint'); ?></h3>
					        <table>
					            <th>
					            <tr>
						        <td style="width: 250px;"></td>
						        <td style="width: 250px;"></td>
					            </tr>
					            </th>
					            <tr><td><span><?php _e('Show featured image?', 'productprint'); ?></span></td><td><select name="productprint_ops[featured_image]"><option value='1'><?php _e('Yes', 'productprint'); ?></option><option value='0' 
						        <?php if (isset($options['featured_image']) && $options['featured_image'] == 0)
    						        _e ('Selected=', 'productprint'); ?> ?>
						        <?php _e('No', 'productprint'); ?></option></select>
					            </td></tr>
					            <tr>
					            <td>
					            <label><?php _e('Position', 'productprint'); ?></label>
					            </td>
					            <td>
					            <select name="productprint_ops[img_position]">
					                <option value="">-- <?php _e('Position', 'productprint'); ?> --</option>
					                <?php foreach($img_positions as $position): ?>
					                    <option value="<?php print $position; ?>" <?php print ($options['img_position'] == $position) ? 'selected="selected"' : ''; ?>>
					    	            <?php print $position; ?>
					                </option>
					                <?php endforeach; ?>
					            </select>
					            </td>
					            </tr>
		
				                <!-- featured image width and margin settings -->
		
					            <span> <?php _e('Append either px or % to the width. The height will scale in proportion.', 'productprint'); ?> </span>
					            <tr><td><span><?php _e('Width', 'productprint'); ?></span></td><td><input type="text" name="productprint_ops[img_width]" value="<?php print $options['img_width'] ?>" /></td></tr>
					            <span> <?php _e('Append either px or % to the margin values.', 'productprint'); ?> </span>
					            <tr><td><span><?php _e('Left margin', 'productprint'); ?></span></td><td><input type="text" name="productprint_ops[img_marginleft]" value="<?php print $options['img_marginleft'] ?>" /></td></tr>
					            <tr><td><span><?php _e('Right margin', 'productprint'); ?></span></td><td><input type="text" name="productprint_ops[img_marginright]" value="<?php print $options['img_marginright'] ?>" /></td></tr>		
					            <tr><td><span><?php _e('Top margin', 'productprint'); ?></span></td><td><input type="text" name="productprint_ops[img_margintop]" value="<?php print $options['img_margintop'] ?>" /></td></tr>		
					            <tr><td><span><?php _e('Bottom margin', 'productprint'); ?></span></td><td><input type="text" name="productprint_ops[img_marginbottom]" value="<?php print $options['img_marginbottom'] ?>" /></td></tr>		
					            <tr><td><span><?php _e('Show border?', 'productprint'); ?></span></td><td><select name="productprint_ops[show_border]"><option value='1'><?php _e('Yes', 'productprint'); ?></option><option value='0' 
					            <?php if (isset($options['show_border']) && $options['show_border'] == 0)
						            _e ('Selected=', 'productprint'); ?> ?>
    				            <?php _e('No', 'productprint'); ?></option></select>
    				            </td></tr>
				            </table>
		
				            <?php submit_button(); ?>		
				            <hr />
				
				            <!-- Gallery Options for the plugin -->
		
				            <p>
				            <h3><?php _e('Gallery Options', 'productprint'); ?></h3>
				            <span> <?php _e('Append either px or % to the width. The height will scale in proportion.', 'productprint'); ?> </span>
				            <table>
    					        <th>
    					        <tr>
    						    <td style="width: 250px;"></td>
    						    <td style="width: 250px;"></td>
    					        </tr>
    					        </th>
    				            <tr><td><span><?php _e('Show gallery?', 'productprint'); ?></span></td><td><select name="productprint_ops[gallery]"><option value='1'><?php _e('Yes', 'productprint'); ?></option><option value='0' 
    					        <?php if (isset($options['gallery']) && $options['gallery'] == 0)
    						        _e ('Selected=', 'productprint'); ?> ?>
    					        <?php _e('No', 'productprint'); ?></option></select></td></tr>
    								
    				            <tr><td><span><?php _e('Gallery Image Width', 'productprint'); ?></span></td><td><input type="text" name="productprint_ops[gallery_img_width]" value="<?php print $options['gallery_img_width'] ?>" /></td></tr>
    		
    				            <tr><td><span><?php _e('Show borders?', 'productprint'); ?></span></td><td><select name="productprint_ops[gallery_border]"><option value='1'><?php _e('Yes', 'productprint'); ?></option><option value='0' 
    					        <?php if (isset($options['gallery_border']) && $options['gallery_border'] == 0)
    						        _e ('Selected=', 'productprint'); ?> ?>
    					        <?php _e('No', 'productprint'); ?></option></select></td></tr>
    				        </table>

    				        <?php submit_button(); ?>		
    				        <hr />
		
    				        <!-- plugins font settings -->
		
    				        <p>
		
    				        <h3><?php _e('Printer font', 'productprint'); ?></h3>
		
    				        <table>
    					        <th>
    					        <tr>
    					        <td style="width: 250px;"></td>
    					        <td style="width: 250px;"></td>
    					        </tr>
    					        </th>
    					        <tr><td><label><?php _e('Font Family', 'productprint'); ?></label></td><td>						
    					        <select name="productprint_ops[font_family]">		
    						        <option value="">-- <?php _e('font family', 'productprint'); ?> --</option>		
    						        <?php foreach($fonts as $font): ?>		
    						            <option value="<?php print $font; ?>" <?php print ($options['font_family'] == $font) ? 'selected="selected"' : ''; ?>>		
    							        <?php print $font; ?>		
    						            </option>		
    						        <?php endforeach; ?>		
    					        </select>							
    					        </td></tr>
								
    					        <tr><td><span><?php _e('Font Size', 'productprint'); ?></span></td><td><input type="text" name="productprint_ops[font_size]" value="<?php print $options['font_size'] ?>" /></td></tr>
		
    				        </table>

    				        <?php submit_button(); ?>
    		                <hr />
    				    </form>
				    </div> <!-- .inside -->
				</div> <!-- .postbox -->
			</div> <!-- #post-body-content -->
		</div> <!-- #post-body -->	
		<?php
	}

	private function settings_page_tab2() { // tab2 is the upgrade tab
		?>
		<div id="post-body">
			<div id="post-body-content">
				<div class="postbox">
					<div class="inside">
						<iframe src="https://productprintpro.com/upgrade.html"></iframe>					
    				</div> <!-- .inside -->
				</div> <!-- .postbox -->
			</div> <!-- #post-body-content -->
		</div> <!-- #post-body -->	
		<?php
	}

	public function productprint_button() {
		global $product; //gets the global class Product for WooCommerce so we can get the product's details

		$link = home_url('/index.php?task=productprint&pid='.$product->get_id()); //sets the URL for the post page
		$nonced_url = wp_nonce_url($link, $product->get_id()); // adds a nonce to the URL

		$ops = get_option('productprint_ops');

		//this produces the print link on the products page
		//Gets width, margin and position properties for the button

		$bml = ($ops['button_marginleft']) ?   $ops['button_marginleft'] : '0px';
		$bmr = ($ops['button_marginright']) ?  $ops['button_marginright'] : '0px';
		$bmt = ($ops['button_margintop']) ?    $ops['button_margintop'] : '0px';
		$bmb = ($ops['button_marginbottom']) ? $ops['button_marginbottom'] : '0px';

		?><a href="<?php print $nonced_url; ?>"	id="print_button_id" 
				                                target="_blank" 
				                                rel="nofollow" 
				                                class="button print-button"
				                                style="margin-left: <?php print $bml; ?>; margin-right: <?php print $bmr; ?>; margin-top: <?php print $bmt; ?>; margin-bottom: <?php print $bmb; ?>;">
				                                <img src="<?php print ($ops['printer_icon']) ?>"><?php print (sanitize_text_field($ops['button_legend'])) ?>
				                                </a>

        <script language="JavaScript">
    		jQuery( 'document' ).ready( function( $ ) { // add variation id onto print button link
        		if ( jQuery( "input[name='variation_id']" ) ) {
    		        jQuery( "input[name = 'variation_id']" ).change( function() {
        			    variationnn_id = jQuery( "input[name='variation_id']" ).val();
        			    cur_href = document.getElementById( "print_button_id" ).href; 
        			    cur_href2 = cur_href.split( '&variation_id' );
        			    cur_href = cur_href2[0];
        			    document.getElementById( "print_button_id" ).href=cur_href+"&variation_id="+variationnn_id;
        			    return false;
    		        });
    		    }
		    });
	    </script>	
		<?php
	}

	public function action_template_redirect() {
		if( isset($_REQUEST['task']) && $_REQUEST['task'] == 'productprint' && isset($_REQUEST['pid']) && $_REQUEST['pid'] ) {
			$retrieved_nonce = $_REQUEST['_wpnonce'];
			if (!wp_verify_nonce($retrieved_nonce, $_REQUEST['pid'] ) )
			die( 'Failed security check' );

			require_once SC_productprint_PLUGIN_DIR . SB_DS . 'html-print.php';
			die();
		}
	}
		
}

$sc_productprint = new SC_productprint(); //this simply calls the class meaning the construct method is run

