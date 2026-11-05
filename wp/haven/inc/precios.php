<?php
/**
 * Precios "desde" por noche (gerente, 5 nov 2026). Se guardan en USD, como en Roiback.
 * En español se muestran en pesos (USD × TRM guardada, redondeado al millar); en inglés, en dólares.
 * Están en opciones de WordPress (no en el tema), para cambiarlos sin publicar:
 *   wp option update hv_precios '{"room-suite-presidential":585,"economic-consort":80}' --format=json
 *   wp option update hv_trm 3221.19
 * Claves: clave de la habitación (room-…); claves sin ficha (p. ej. economic-consort) solo cuentan para el rango del hotel.
 * Si no hay precio, no se muestra nada (nunca se inventa).
 */
defined( 'ABSPATH' ) || exit;

function hv_precios() {
	static $p = null;
	if ( null === $p ) $p = array_filter( array_map( 'floatval', (array) get_option( 'hv_precios', array() ) ) );
	return $p;
}
function hv_trm() { return (float) get_option( 'hv_trm', 0 ); }

/** Precio "desde" en USD de una habitación (0 si no hay). */
function hv_precio_usd( $key ) { $p = hv_precios(); return (float) ( $p[ $key ] ?? 0 ); }

/** Moneda y valor para el idioma: ES → COP redondeado al millar; EN → USD. Devuelve array( moneda, valor ) o null. */
function hv_precio_local( $usd, $en = null ) {
	$en = null === $en ? hv_en() : $en;
	if ( $usd <= 0 ) return null;
	// Precios cerrados, siempre hacia arriba (el "desde" nunca queda por debajo de la tarifa real):
	// USD al múltiplo de 5; COP a la decena de mil, y desde un millón al múltiplo de 50.000.
	if ( $en || hv_trm() <= 0 ) return array( 'USD', ceil( $usd / 5 ) * 5 );
	$cop = $usd * hv_trm();
	$step = $cop >= 1000000 ? 50000 : 10000;
	return array( 'COP', ceil( $cop / $step ) * $step );
}

/** Compatibilidad: valor en la moneda del idioma. */
function hv_precio_desde( $key, $f = '' ) { $l = hv_precio_local( hv_precio_usd( $key ) ); return $l ? $l[1] : 0; }

function hv_precio_fmt( $cur, $v, $en = null ) {
	$en = null === $en ? hv_en() : $en;
	return $cur . ' ' . number_format( $v, 0, $en ? '.' : ',', $en ? ',' : '.' );
}

/** "Desde COP 290.000" / "From USD 90". */
function hv_precio_txt( $v_or_key, $en = null ) {
	$en = null === $en ? hv_en() : $en;
	$l = hv_precio_local( is_string( $v_or_key ) ? hv_precio_usd( $v_or_key ) : $v_or_key, $en );
	return $l ? ( $en ? 'From ' : 'Desde ' ) . hv_precio_fmt( $l[0], $l[1], $en ) : '';
}

/** Rango del hotel en la moneda del idioma. */
function hv_precio_rango() {
	$v = hv_precios();
	if ( ! $v ) return '';
	$a = hv_precio_local( min( $v ) ); $b = hv_precio_local( max( $v ) );
	return hv_precio_fmt( $a[0], $a[1] ) . ( $b[1] > $a[1] ? ' – ' . number_format( $b[1], 0, hv_en() ? '.' : ',', hv_en() ? ',' : '.' ) : '' );
}

/* Schema Hotel: rango real en vez de "$$$". */
add_filter( 'hv_hotel_schema', function ( $s ) {
	$r = hv_precio_rango();
	if ( $r ) $s['priceRange'] = $r;
	return $s;
}, 20 );

/* Ficha de habitación: oferta con precio por noche para Google. */
add_action( 'wp_head', function () {
	if ( 'room' !== hv_template_key() ) return;
	$d = hv_data();
	$l = hv_precio_local( hv_precio_usd( hv_key() ) );
	if ( ! $l ) return;
	hv_ld( array(
		'@context' => 'https://schema.org', '@type' => 'Offer', 'url' => get_permalink(),
		'name' => ( hv_en() ? 'Nightly rate from · ' : 'Tarifa por noche desde · ' ) . ( $d['name'] ?? get_the_title() ),
		'itemOffered' => array( '@type' => 'HotelRoom', 'name' => $d['name'] ?? get_the_title(), 'url' => get_permalink() ),
		'priceSpecification' => array( '@type' => 'UnitPriceSpecification', 'price' => $l[1], 'priceCurrency' => $l[0], 'unitCode' => 'DAY', 'minPrice' => $l[1] ),
		'price' => $l[1], 'priceCurrency' => $l[0],
		'offeredBy' => array( '@type' => 'Hotel', '@id' => home_url( '/#hotel' ), 'name' => 'Haven, A Design Hotel' ),
	) );
}, 8 );
