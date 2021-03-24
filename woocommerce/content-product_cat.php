<?php
/**
 * The template for displaying product category thumbnails within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product_cat.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $woocommerce_loop;
$artfurniture_opt = get_option( 'artfurniture_opt' );
$colwidth = 3;
$artfurniture_shopclass="";
if($artfurniture_shopclass=='shop-fullwidth') {
	if(isset($artfurniture_opt)){
		$woocommerce_loop['columns'] = $artfurniture_opt['product_per_row_fw'];
		if($woocommerce_loop['columns'] > 0){
			$colwidth = round(12/$woocommerce_loop['columns']);
		}
	}
	$col_classes = ' home-category text-center col-12 col-sm-6 col-md-4 col-lg-'.$colwidth ;
} else {
	if(isset($artfurniture_opt)){
		$woocommerce_loop['columns'] = $artfurniture_opt['product_per_row'];
		if($woocommerce_loop['columns'] > 0){
			$colwidth = round(12/$woocommerce_loop['columns']);
		}
	}
	$col_classes = ' home-category text-center col-12 col-md-4 col-lg-'.$colwidth ;
}
?>
<div <?php wc_product_cat_class($col_classes); ?>>
	<?php do_action( 'woocommerce_before_subcategory', $category ); ?>
	<a href="<?php echo get_term_link( $category->slug, 'product_cat' ); ?>">
		<?php
			/**
			 * woocommerce_before_subcategory_title hook
			 *
			 * @hooked woocommerce_subcategory_thumbnail - 10
			 */
			do_action( 'woocommerce_before_subcategory_title', $category );
		?>
		
		<?php
			/**
			 * woocommerce_after_subcategory_title hook
			 */
			do_action( 'woocommerce_after_subcategory_title', $category );
		?>
	</a>
	<?php do_action( 'woocommerce_after_subcategory', $category ); ?>
	<h4>
			<?php
				echo ''.$category->name;
				if ( $category->count > 0 )
					// echo apply_filters( 'woocommerce_subcategory_count_html', ' <mark class="count">(' . $category->count . ')</mark>', $category );
			?>
		</h4>
</div>
