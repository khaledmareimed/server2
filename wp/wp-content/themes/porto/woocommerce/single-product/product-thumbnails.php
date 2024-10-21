<?php
/**
 * Single Product Thumbnails
 *
 * @version     3.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $woocommerce, $product, $porto_product_layout, $porto_settings;

if ( 'extended' == $porto_product_layout || 'sticky_info' == $porto_product_layout || 'sticky_both_info' == $porto_product_layout || 'grid' == $porto_product_layout ) {
	return;
}

$attachment_ids     = $product->get_gallery_image_ids();
$thumbnails_classes = '';
if ( 'full_width' === $porto_product_layout || 'centered_vertical_zoom' === $porto_product_layout ) {
	$thumbnails_classes = 'product-thumbnails-inner';
} elseif ( 'transparent' === $porto_product_layout ) {
	$thumbnails_classes = 'product-thumbs-vertical-slider';
} else {
	$thumbnails_classes = 'product-thumbs-slider owl-carousel';
}

$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
$thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );

?>
<div class="product-thumbnails thumbnails">
	<?php
	$html = '<div class="' . esc_attr( $thumbnails_classes ) . ( 'product-thumbs-slider owl-carousel' == $thumbnails_classes ? ' has-ccols ccols-' . intval( $porto_settings['product-thumbs-count'] ) : '' ) . '">';

	$attachment_id = method_exists( $product, 'get_image_id' ) ? $product->get_image_id() : get_post_thumbnail_id();
	$has_main_img  = false;

	if ( $attachment_id ) {

		if ( $attachment_ids ) {
			$attachment_ids = array_merge( array( $attachment_id ), $attachment_ids );
		} else {
			$attachment_ids = array( $attachment_id );
		}
		$has_main_img = true;

	} else {

		$image_thumb_link = wc_placeholder_img_src();
		$html            .= apply_filters( 'woocommerce_single_product_image_thumbnail_html', '<div class="img-thumbnail"><div class="inner"><img class="woocommerce-main-thumb wp-post-image" alt="' . esc_html__( 'Awaiting product image', 'woocommerce' ) . '" src="' . esc_url( $image_thumb_link ) . '" /></div></div>', false, $post->ID, '' ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
	}

	if ( $attachment_ids ) {
		foreach ( $attachment_ids as $index => $attachment_id ) {

			$param_args = array(
				'title' => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
				'class' => $has_main_img && 0 === $index ? 'woocommerce-main-thumb wp-post-image' : '',
			);
			$caption = _wp_specialchars( get_post_field( 'post_excerpt', $attachment_id ), ENT_QUOTES, 'UTF-8', true );
			if ( $caption ) {
				$param_args['data-caption'] = $caption;
			}

			$image = wp_get_attachment_image(
				$attachment_id,
				$thumbnail_size,
				false,
				apply_filters(
					'woocommerce_gallery_image_html_attachment_image_params',
					$param_args,
					$attachment_id,
					$thumbnail_size,
					false
				)
			);

			if ( empty( $image ) ) {
				continue;
			}

			$html .= apply_filters( 'woocommerce_single_product_image_thumbnail_html', '<div class="img-thumbnail">' . $image . '</div>', $attachment_id, $post->ID, '' ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

		}
	}
	$html .= apply_filters( 'porto_single_product_after_thumbnails', '' );

	$html .= '</div>';

	echo porto_filter_output( $html );

	?>
</div>
