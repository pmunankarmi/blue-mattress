<?php
/**
 * City-based Saudi delivery rates.
 *
 * @package BlueMattress
 */

defined( 'ABSPATH' ) || exit;

/** Normalize a checkout city without changing Arabic characters. */
function blue_normalize_shipping_city( string $city ): string {
	$city = trim( wp_strip_all_tags( $city ) );
	$city = function_exists( 'mb_strtolower' ) ? mb_strtolower( $city, 'UTF-8' ) : strtolower( $city );
	return (string) preg_replace( '/[\s\p{Pd}_]+/u', ' ', $city );
}

/** Check whether the supplied city receives complimentary delivery. */
function blue_is_free_shipping_city( string $city ): bool {
	$city = blue_normalize_shipping_city( $city );
	if ( ! $city ) {
		return false;
	}

	$city_names = array(
		'jeddah',
		'jedda',
		'jidda',
		'jiddah',
		'جدة',
		'جده',
		'makkah',
		'makkah al mukarramah',
		'mecca',
		'مكة',
		'مكه',
		'مكة المكرمة',
	);

	foreach ( $city_names as $city_name ) {
		if ( str_contains( $city, $city_name ) ) {
			return true;
		}
	}

	return false;
}

/** Create a taxable SAR 150 fallback rate using the customer's shipping tax rates. */
function blue_saudi_flat_shipping_rate(): WC_Shipping_Rate {
	$cost  = 150.0;
	$taxes = array();
	if ( function_exists( 'wc_tax_enabled' ) && wc_tax_enabled() && class_exists( 'WC_Tax' ) ) {
		$taxes = WC_Tax::calc_shipping_tax( $cost, WC_Tax::get_shipping_tax_rates() );
	}

	return new WC_Shipping_Rate(
		'blue_saudi_flat_rate',
		blue_text( 'Flat rate', 'سعر ثابت' ),
		$cost,
		$taxes,
		'flat_rate'
	);
}

add_filter(
	'woocommerce_package_rates',
	function ( array $rates, array $package ): array {
		$destination = is_array( $package['destination'] ?? null ) ? $package['destination'] : array();
		$country     = strtoupper( (string) ( $destination['country'] ?? '' ) );
		if ( 'SA' !== $country ) {
			return $rates;
		}

		$city = (string) ( $destination['city'] ?? '' );
		if ( blue_is_free_shipping_city( $city ) ) {
			$free_rate = new WC_Shipping_Rate(
				'blue_jeddah_makkah_free',
				blue_text( 'Free shipping', 'شحن مجاني' ),
				0,
				array(),
				'free_shipping'
			);
			return array( $free_rate->get_id() => $free_rate );
		}

		$flat_rates = array_filter(
			$rates,
			static fn( $rate ): bool => $rate instanceof WC_Shipping_Rate && 'flat_rate' === $rate->get_method_id()
		);
		if ( ! $flat_rates ) {
			$flat_rate = blue_saudi_flat_shipping_rate();
			return array( $flat_rate->get_id() => $flat_rate );
		}

		foreach ( $flat_rates as $rate ) {
			$rate->set_cost( 150.0 );
		}
		return $flat_rates;
	},
	100,
	2
);
