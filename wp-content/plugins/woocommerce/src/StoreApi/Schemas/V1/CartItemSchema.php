<?php
namespace Automattic\WooCommerce\StoreApi\Schemas\V1;

use Automattic\WooCommerce\StoreApi\Utilities\ProductItemTrait;
use Automattic\WooCommerce\StoreApi\Utilities\QuantityLimits;

/**
 * CartItemSchema class.
 */
class CartItemSchema extends ItemSchema {
	use ProductItemTrait;

	/**
	 * The schema item name.
	 *
	 * @var string
	 */
	protected $title = 'cart_item';

	/**
	 * The schema item identifier.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'cart-item';

	/**
	 * Convert a WooCommerce cart item to an object suitable for the response.
	 *
	 * @param array $cart_item Cart item array.
	 * @return array
	 */
	public function get_item_response( $cart_item ) {
		$product = $cart_item['data'];
		// mzl mod nbd preview
		if( isset( $cart_item['nbd_item_meta_ds'] ) ){
			if( isset( $cart_item['nbd_item_meta_ds']['nbd'] ) ){
				$path = NBDESIGNER_CUSTOMER_DIR;
				$path = str_replace('/www/wwwroot/', 'https://', $path);
				$parsedUrl = parse_url($path);
				$path = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '.com' . (isset($parsedUrl['path']) ?
					$parsedUrl['path'] : '');
				$path .= '/' . $cart_item['nbd_item_meta_ds']['nbd'] .
						'/' . 'preview' . '/'. 'frame_0.png';
						// echo '<div> get_item_response nbd_item_meta_ds path: ' . $path . '</div>';
				$cart_item['fpd_data']['fpd_product_thumbnail'] = $path;
			}
			
		}
		// if( isset( $cart_item['nbo_meta'] ) ){
        //     $builder_folder = $cart_item['nbo_meta']['nbdpb'];
		// 	echo 'get_item_response builder_folder ' . $builder_folder;
        //     $path           = NBDESIGNER_CUSTOMER_DIR . '/' . $builder_folder;
		// 	echo 'get_item_response path ' . $path;
		// }
		 
// 'https://www.dujiayoupin.com/wp-content/uploads/2024/09/braceletA.png';//todo mzl 4.13
		// do_action( 'woocommerce_before_single_product_summary' );
		/**
		 * Filter the product permalink.
		 *
		 * This is a hook taken from the legacy cart/mini-cart templates that allows the permalink to be changed for a
		 * product. This is specific to the cart endpoint.
		 *
		 * @since 9.9.0
		 *
		 * @param string $product_permalink Product permalink.
		 * @param array  $cart_item         Cart item array.
		 * @param string $cart_item_key     Cart item key.
		 */
		$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->get_permalink(), $cart_item, $cart_item['key'] );
		
		$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $product->get_image(), $cart_item, $cart_item['key'] );
		// $fpd_data = $cart_item['fpd_data'];
		//$fpd_data['fpd_product']
		$attachment_ids = array_merge( [ $product->get_image_id() ], $product->get_gallery_image_ids() );
		$images = array_values( array_filter( array_map( [ $this->image_attachment_schema, 'get_item_response' ], $attachment_ids ) ) );
		// echo 'CartItemSchema get_item_response';
		// echo implode( ' ', $attachment_ids );
		// echo implode( ' ', $cart_item['fpd_data'] );
		// mzl mod thumb
		// echo $attachment_ids[0];
		//$thumbnail = null;
		// $fpd_data = null;
		// if ( !is_null($cart_item) && isset($cart_item['data']) != null ) {
			// $fpd_data = $cart_item['fpd_data'];
			// echo $fpd_data['fpd_product_thumbnail'];
		// }
		// echo '------------------------------------';
		$width = 900;
		$height = 600;
		// echo '<div>aa </div>';
		// do_action( 'woocommerce_before_single_product' );

		// echo $thumbnail;
		// do_action( 'woocommerce_before_single_product_summary' );
		// echo '</div>';
		// $imageSrc = $thumbnail;
		// $product_meta = get_post_meta($post_id);
		// echo wp_get_attachment_image( $product_meta['_thumbnail_id'][0], 'full' );
		//$image = wp_get_attachment_image_src( get_post_thumbnail_id( $cart_item['product_id'] ), 'full' );//$cart_item['product_id'] single-post-thumbnail
		
		//$htmlImage = "<img src=\"$image[0]\" width=\"$width\" height=\"$height\" alt=\"Image\">";
		//echo $htmlImage;
		// echo json_encode(wp_get_attachment_url( $cart_item['product_id'] ));
		
		// foreach ($attachment_ids as $attachment_id) {
		// 	echo $attachment_id;
		// 	$html = wc_get_gallery_image_html(530, true);
		// 	echo '
		// 	<div class="woocommerce-product-gallery woocommerce-product-gallery--with-images woocommerce-product-gallery--columns-4 images" data-columns="4" style="opacity: 1; transition: opacity 0.25s ease-in-out;"><a href="#" class="woocommerce-product-gallery__trigger">' . '
		// 	<div class="woocommerce-product-gallery__wrapper">' . 
		// 	apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $attachment_id )
		// 	. '
		// 	</div>
		// 	</div>
		// 	';
		// }
		foreach ($images as $value) {
			// echo json_encode($value);
			if ( $thumbnail ) {
				$value->thumbnail = $thumbnail;//$cart_item['data'];//$cart_item['key'];
				$value->width = 600;
				$value->height = 450;
			}
		}
		//echo wc_get_template( 'single-product/product-image.php' );

    // 获取基础响应数据
    	$response =  [
			'key'                  => $cart_item['key'],
			'id'                   => $product->get_id(),
			'type'                 => $product->get_type(),
			'quantity'             => wc_stock_amount( $cart_item['quantity'] ),
			'quantity_limits'      => (object) ( new QuantityLimits() )->get_cart_item_quantity_limits( $cart_item ),
			'name'                 => $this->prepare_html_response( $product->get_title() ),
			'short_description'    => $this->prepare_html_response( wc_format_content( wp_kses_post( $product->get_short_description() ) ) ),
			'description'          => $this->prepare_html_response( wc_format_content( wp_kses_post( $product->get_description() ) ) ),
			'sku'                  => $this->prepare_html_response( $product->get_sku() ),
			'low_stock_remaining'  => $this->get_low_stock_remaining( $product ),
			'backorders_allowed'   => (bool) $product->backorders_allowed(),
			'show_backorder_badge' => (bool) $product->backorders_require_notification() && $product->is_on_backorder( $cart_item['quantity'] ),
			'sold_individually'    => $product->is_sold_individually(),
			'permalink'            => $product_permalink,
			'images'               => $images,
			'variation'            => $this->format_variation_data( $cart_item['variation'], $product ),
			'item_data'            => $this->get_item_data( $cart_item ),
			'prices'               => (object) $this->prepare_product_price_response( $product, get_option( 'woocommerce_tax_display_cart' ) ),
			'totals'               => (object) $this->prepare_currency_response(
				[
					'line_subtotal'     => $this->prepare_money_response( $cart_item['line_subtotal'], wc_get_price_decimals() ),
					'line_subtotal_tax' => $this->prepare_money_response( $cart_item['line_subtotal_tax'], wc_get_price_decimals() ),
					'line_total'        => $this->prepare_money_response( $cart_item['line_total'], wc_get_price_decimals() ),
					'line_total_tax'    => $this->prepare_money_response( $cart_item['line_tax'], wc_get_price_decimals() ),
				]
			),
			'catalog_visibility'   => $product->get_catalog_visibility(),
			self::EXTENDING_KEY    => $this->get_extended_data( self::IDENTIFIER, $cart_item ),
		];
		// 添加定制化预览图片
		if (isset($cart_item['nbo_meta']) && isset($cart_item['nbo_meta']['cart_image'])) {
			$response['custom_image'] = [
				'src' => $cart_item['nbo_meta']['cart_image'],
				'alt' => __('Custom design preview', 'web-to-print-online-designer'),
			];
		}
		return $response;
	}

	/**
	 * Format cart item data removing any HTML tag.
	 *
	 * @param array $cart_item Cart item array.
	 * @return array
	 */
	protected function get_item_data( $cart_item ) {
		/**
		 * Filters cart item data.
		 *
		 * Filters the variation option name for custom option slugs.
		 *
		 * @since 4.3.0
		 *
		 * @internal Matches filter name in WooCommerce core.
		 *
		 * @param array $item_data Cart item data. Empty by default.
		 * @param array $cart_item Cart item array.
		 * @return array
		 */
		$item_data       = apply_filters( 'woocommerce_get_item_data', array(), $cart_item );
		$clean_item_data = [];
		foreach ( $item_data as $data ) {
			// We will check each piece of data in the item data element to ensure it is scalar. Extensions could add arrays
			// to this, which would cause a fatal in wp_strip_all_tags. If it is not scalar, we will return an empty array,
			// which will be filtered out in get_item_data (after this function has run).
			foreach ( $data as $data_value ) {
				if ( ! is_scalar( $data_value ) ) {
					continue 2;
				}
			}
			$clean_item_data[] = $this->format_item_data_element( $data );
		}
		return $clean_item_data;
	}

	/**
	 * Remove HTML tags from cart item data and set the `hidden` property to `__experimental_woocommerce_blocks_hidden`.
	 *
	 * @param array $item_data_element Individual element of a cart item data.
	 * @return array
	 */
	protected function format_item_data_element( $item_data_element ) {
		if ( array_key_exists( '__experimental_woocommerce_blocks_hidden', $item_data_element ) ) {
			$item_data_element['hidden'] = $item_data_element['__experimental_woocommerce_blocks_hidden'];
		}
		return array_map( 'wp_strip_all_tags', $item_data_element );
	}
}
