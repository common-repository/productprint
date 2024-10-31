<?php 

/***
 * Plug-in: ProductPrint
 * Version: 2.0.4
 * File: html-print.php
 * Purpose: Generate the html page for printing
 * 
 ****/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( isset( $_GET['task'] ) && $_GET['task'] == 'productprint' ) {

check_admin_referer($_REQUEST['pid']); /*** the pid was encoded in the nonce, see if they match ***/

// Find out what product we have to deal with
$product = wc_get_product($_REQUEST['pid']);

// do we have a variation? If so deal with it
$isVariation = ( isset( $_REQUEST['variation_id'] ) && $_REQUEST['variation_id'] > 0 );
if ( $isVariation ) {

    $var_id=$_REQUEST['variation_id'];

    //$wp_variations = $product->get_available_variations($_REQUEST['pid']);
    $wp_variations = $product->get_available_variations();
    foreach($wp_variations as $key1=>$value1)
    {
        if($var_id==$value1['variation_id'])
        {
            $variation_array=$value1;
        }
    }
}


$sampleheader = SC_PRODUCTPRINT_PLUGIN_URL . '/assets/default-header.jpg';
$samplefooter = SC_PRODUCTPRINT_PLUGIN_URL . '/assets/default-footer.jpg';
$sampleprintericon = SC_PRODUCTPRINT_PLUGIN_URL . '/assets/default-printer-icon.png';
//Here are the default variables to the Plugin if there aren't any settings found in the database
	$def = array(		'featured_image' => 1, 
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
						'button_position' => '4', 
						'button_legend' => 'Print',
						'button_marginleft' => '0px',
						'button_marginright' => '0px',
						'button_margintop' => '0px',
						'button_marginbottom' => '0px',
						'sku' => '',
						'printer_icon' => $sampleprintericon,
						);
				


//gets the options set in Admin page
$ops = get_option('productprint_ops'); 

//the array_merge checks if there are any missing keys (settings) from $ops, our custom options in the backend
$ops = array_merge($def, $ops);

//now we are going to output the HTML to the print page

?>



<!DOCTYPE html>

<html>

<head>

<title>Print <?php print $product->get_title(); ?></title>

<style>

body, #container {
	font-family: <?php print $ops['font_family']?>;
}

p {
	font-size: <?php print $ops['font_size']?>;
}


#thumbnails img {

<?php

//this gets the width to the Gallery.

	if(isset($ops['gallery_img_width']) && $ops['gallery_img_width'] != "")
		echo "width: " . $ops['gallery_img_width'] . "; ";

?>
	height: "auto";

}

</style>

<link rel="stylesheet" href="<?php print plugins_url( 'css/productprint.css', __FILE__ ) ?>" />

<script src="<?php print home_url('/wp-includes/js/jquery/jquery.js'); ?>"></script>

<script>


jQuery(function($)

{
		window.print(); //standard Javascript print function

});

</script>

</head>

<body>

<div id="container" style="display:block;">

    <div id="productprint-main">

	    <h1 id="productprint-title"><?php print $product->get_title(); ?></h1>

	    <div id="productprint-images">

		    <?php if( $ops['featured_image'] == 1 ) include("inc/featured-image.php");
            ?>

		<?php if($ops['price'] == 1 ): /** Gets the price of the product **/ ?>
			<div id="productprint-price">
				<h3>
				<?php if ( $isVariation ) {
				    echo $variation_array['price_html'];
                    }
				else {
				    echo $product->get_price_html(); 
				    }
				?>
				</h3>
			</div>

		<?php endif; ?>


		<?php if($ops['sku'] == 1 ): /** Gets the sku of the product **/ ?>
			<div id="productprint-sku">
				<?php 
    			 if ( $isVariation ) {
                    _e('SKU: ', 'woocommerce');
				    echo $variation_array['sku'];
                    }
				else {
				    _e('SKU: ', 'woocommerce');
				    echo $product->get_sku(); 
			        }
				?>
			</div>

		<?php endif; ?>


	    <?php if($ops['stock_level'] == 1 ): /** gets the product's availability **/ ?>

			<div id="productprint-stock">
				<?php 
    			 if ( $isVariation ) {
                    $qty = $variation_array['max_qty'];
                    if ( $qty > 0 ) {
                        echo $qty;
                        _e(' in stock', 'productprint');
                        }
                    else
                        _e('Out of stock', 'productprint');
    			    }
				else {
				    $availability = $product->get_availability();
				    if ( $availability['availability'] )
					    echo apply_filters( 'woocommerce_stock_html', '<p class="ppp-stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>', $availability['availability'] );
				    }
				?>
			</div>

		<?php endif; ?>


		<?php if($ops['short_description'] == 1 ): /** gets the product's short description **/ ?>


			<div id="productprint-short-description">
            <?php
                $content = $product->get_short_description();
			    echo apply_filters( 'woocommerce_short_description', $content );
            ?>
			</div>

		<?php endif; ?>

		<?php if($ops['product_description'] == 1 ): /** gets the product's full description **/ ?>

			<div id="productprint-description">

			<?php
				$heading = apply_filters( 'woocommerce_product_description_heading', __( 'Product Description', 'woocommerce' ) ); 
			?>

			<h2><?php echo $heading; ?></h2>

            <p><?php
                $content = $product->get_description();
			    echo apply_filters( 'woocommerce_description', $content );
            ?></p>
			</div>

		<?php endif; ?>

		<?php if($ops['product_attributes'] == 1 ): ?>

			<?php $attributes = $product->get_attributes(); ?>

			<?php if(count($attributes)): ?>

				<div id="additional-info">
					<?php
						$heading = apply_filters( 'woocommerce_product_additional_information_heading', __( 'Additional Information', 'woocommerce' ) );
					?>

					<?php if ( $heading ): ?>
						<h2><?php echo $heading; ?></h2>
					<?php endif; ?>

					<p><?php wc_display_product_attributes($product); ?></p>

				</div>

			<?php endif; ?>

		<?php endif; ?>

		<?php if($ops['gallery'] == 1 ): /** checks if the gallery image option is set **/ ?>
			
			<div id="thumbnails">
				
			<?php include("inc/gallery-images.php"); ?>

			</div>

		<?php endif; ?>

		<?php if($ops['reviews'] == 1 ): ?>
			
			<div id="productprint-reviews">
	
				<h2><?php _e('Reviews', 'woocommerce'); ?></h2>

				<?php /*** star rating and review count ***/

				$count   = $product->get_review_count();
				$average = $product->get_average_rating();

				if ( $count > 0 ) : ?>
		            <h2 class="productprint-reviews-title"><?php
				    /* translators: 1: reviews count 2: product name */
				    printf( esc_html( _n( '%1$s review for %2$s. Average rating: %3$s out of 5', '%1$s reviews for %2$s. Average rating: %3$s out of 5', $count, 'woocommerce' ) ), esc_html( $count ), $product->get_title(), $average );
		            ?></h2>

		            <ol class ="productprint-reviews-list">
		                <?php
                            $args = array ('post_id' => $product->get_id());
                            $comments = get_comments( $args );
                            wp_list_comments( array( 'callback' => 'woocommerce_comments' ), $comments);
                        ?>
		            </ol>
	            <?php else : ?>
		            <p class="productprint-noreviews"><?php _e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
		        <?php endif; ?>
			</div><!-- end id="productprint-reviews" -->

		<?php endif; ?>

	</div>

    </div><!-- end id="productprint-main" -->

</div><!-- end id="container" -->

</body>

</html>

<?php	/*** the else part of the nonce test. ***/
} 