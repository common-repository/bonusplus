<?php
/**
 * Exporter class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Entities;

use WC_Product;
use WP_Term;

/**
 * Exporter class
 *
 * @package Onepix\BonusPlus
 * @since 1.0.0
 */
abstract class BPExporter {
	/**
	 * Get categories for export to BonusPlus
	 *
	 * @see https://bonusplus.pro/api/Help/ResourceModel?modelName=ProductTiny
	 *
	 * @return array
	 */
	public static function get_categories(): array {
		$categories_to_export = array();

		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		if ( $categories && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $cat ) {
				$category_to_export = array(
					'id' => $cat->term_id,
					'n'  => $cat->name,
					'g'  => true,
				);

				if ( 0 !== $cat->parent ) {
					$categories_to_export['pid'] = $cat->parent;
				}

				$categories_to_export[] = $category_to_export;
			}
		}

		return $categories_to_export;
	}

	/**
	 * Get products for export to BonusPlus
	 *
	 * @see https://bonusplus.pro/api/Help/ResourceModel?modelName=ProductTiny
	 *
	 * @return array
	 */
	public static function get_products(): array {
		$products_to_export = array();

		/**
		 * Products to export
		 *
		 * @var WC_Product[] $products
		 */
		$products = wc_get_products(
			array(
				'status' => 'publish',
				'limit'  => - 1,
				'type'   => array( 'simple', 'variable' ),
			)
		);

		foreach ( $products as $product ) {
			$categories_for_product = self::get_child_categories_for_product( $product );

			if ( empty( $categories_for_product ) ) {
				continue;
			}

			$category_id = reset( $categories_for_product )->term_id;

			if ( $product->is_type( 'simple' ) ) {
				$products_to_export[] = array(
					'id'  => $product->get_id(),
					'pid' => $category_id,
					'n'   => $product->get_name(),
					'g'   => false,
					'a'   => $product->get_sku(),
				);
			} else {
				foreach ( $product->get_available_variations() as $variation ) {
					$variation = wc_get_product( $variation['variation_id'] );

					$products_to_export[] = array(
						'id'  => $variation->get_id(),
						'pid' => $category_id,
						'n'   => sprintf( '%s (%s)', $product->get_name(), $variation->get_id() ),
						'g'   => false,
						'a'   => $variation->get_sku(),
					);
				}
			}
		}

		return $products_to_export;
	}

	/**
	 * Return child categories for product
	 *
	 * @see https://wordpress.stackexchange.com/a/55921
	 *
	 * @param mixed $product post object or post ID of the product.
	 *
	 * @return WP_Term[]
	 */
	private static function get_child_categories_for_product( $product ): array {
		$product = wc_get_product( $product );

		// Get all terms associated with post in woocommerce's taxonomy 'product_cat'.
		$terms = get_the_terms( $product->get_id(), 'product_cat' ) ?: array();

		// If error with get_the_terms return empty array.
		if ( is_wp_error( $terms ) ) {
			return array();
		}

		// Get an array of their IDs.
		$term_ids = wp_list_pluck( $terms, 'term_id' );

		// Get array of parents - 0 is not a parent.
		$parents = array_filter( wp_list_pluck( $terms, 'parent' ) );

		// Get array of IDs of terms which are not parents.
		$term_ids_not_parents = array_diff( $term_ids, $parents );

		// Get corresponding term objects.
		return array_intersect_key( $terms, $term_ids_not_parents );
	}
}
