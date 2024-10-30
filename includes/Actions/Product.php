<?php
/**
 * Product actions class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Actions;

use Onepix\BonusPlus\Entities\BPCustomer;
use Onepix\BonusPlus\Entities\BPProduct;
use Onepix\BonusPlus\Tools\Features;
use WC_Product;
use WP_Post;

defined( 'ABSPATH' ) || exit();

/**
 * Class Product
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Product {
	/**
	 * Product constructor.
	 */
	public function __construct() {
		if ( Features::discounts_enabled() ) {
			add_filter( 'woocommerce_product_is_on_sale', array( $this, 'all_products_are_on_sale' ), 1000, 1 );
			add_filter( 'woocommerce_get_price_html', array( $this, 'add_bp_discount_to_product_price_html' ), 1000, 2 );
			add_filter( 'woocommerce_sale_flash', array( $this, 'change_sale_flash' ), 1000, 3 );
		}
	}

	/**
	 * Set all products on discount if user logged in
	 *
	 * @param bool $on_sale default value.
	 *
	 * @return bool
	 */
	public function all_products_are_on_sale( bool $on_sale ): bool {
		return BPProduct::can_add_bp_discount() ?: $on_sale;
	}

	/**
	 * Add price with BonusPlus discount to product price html
	 *
	 * @param string     $price default price html.
	 * @param WC_Product $product current product.
	 *
	 * @return string
	 */
	public function add_bp_discount_to_product_price_html( string $price, WC_Product $product ): string {
		if ( BPProduct::can_add_bp_discount( $product ) ) {
			$discount_percent = ( new BPCustomer() )->get_discount_percent();
			$discount_coef    = ( new BPCustomer() )->get_discount_coef();

			if ( $product->is_type( 'simple' ) && $product->get_price() !== '' ) {
				if ( Features::double_discount_enabled() && $product->get_sale_price() ) {
					$price = wc_format_sale_price(
						wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ),
						wc_get_price_to_display( $product, array( 'price' => $product->get_sale_price() - $product->get_regular_price() * $discount_percent / 100 ) )
					);
				} else {
					$price = wc_format_sale_price(
						wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ),
						wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() * $discount_coef ) )
					);
				}

				$price .= $product->get_price_suffix();
			} elseif ( $product->is_type( 'variable' ) ) {
				$prices = $product->get_variation_prices( true );

				if ( Features::double_discount_enabled() ) {
					$min_price = current( $prices['price'] ) - current( $prices['regular_price'] ) * $discount_percent / 100;
					$max_price = end( $prices['price'] ) - end( $prices['regular_price'] ) * $discount_percent / 100;
				} else {
					$min_price = current( $prices['price'] ) * $discount_coef;
					$max_price = end( $prices['price'] ) * $discount_coef;
				}

				$min_reg_price = current( $prices['regular_price'] );
				$max_reg_price = end( $prices['regular_price'] );

				if ( $min_price !== $max_price ) {
					$price = wc_format_price_range( $min_price, $max_price );
				} elseif ( $product->is_on_sale() && $min_reg_price === $max_reg_price ) {
					$price = wc_format_sale_price( wc_price( $max_reg_price ), wc_price( $min_price ) );
				} else {
					$price = wc_price( $min_price );
				}

				$price = apply_filters( 'woocommerce_variable_price_html', $price . $product->get_price_suffix(), $product );
			}
		}

		return $price;
	}

	/**
	 * Replace default sale flash with BP sale flash
	 *
	 * @param  string     $html default sale flash.
	 * @param  WP_Post    $post current post.
	 * @param  WC_Product $product current product.
	 *
	 * @return string
	 */
	public function change_sale_flash( string $html, WP_Post $post, WC_Product $product ): string {
		if ( BPProduct::can_add_bp_discount( $product ) ) {
			$discount = ( new BPCustomer() )->get_discount_percent();

			$bp_flash_html = '<span class="onsale onsale-bonusPlus">';

			$bp_flash_html .= sprintf(
				bonus_plus()->get_option( 'discount_flash_text' ) ?: '',
				$discount . '%'
			);

			$bp_flash_html .= '</span>';

			if ( Features::double_discount_enabled() ) {
				$html .= $bp_flash_html;
			} else {
				$html = $bp_flash_html;
			}
		}

		return $html;
	}
}
