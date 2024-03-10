<?php
/**
 * Template: Product Hero.
 *
 * This template can be overridden by copying it to yourtheme/orderable/hero.php
 *
 * HOWEVER, on occasion Orderable will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @package Orderable/Templates
 *
 * @var WC_Product_Variable $product Product.
 * @var array               $args Array of args for the shortcode.
 */

defined( 'ABSPATH' ) || exit;

if ( ! $args['images'] ) {
	return;
}
?>

<div class="orderable-product__hero">
	<?php do_action( 'orderable_before_product_hero', $product, $args ); ?>

	<?php
	echo wp_kses_post(
		$product->get_image(
			/**
			 * Allow product hero image size to be updated.
			 *
			 * @param WC_Product $product Product.
			 * @param array      $args    Argumentss passed to the Layout shortcode.
			 */
			apply_filters( 'orderable_product_hero_image_size', 'woocommerce_thumbnail', $product, $args ),
			array(
				'class' => 'orderable-product__image',
			)
		)
	);
	?>

	<?php do_action( 'orderable_after_product_hero', $product, $args ); ?>
</div>
