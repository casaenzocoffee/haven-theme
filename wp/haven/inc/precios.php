<?php
/**
 * Precios "desde" por noche (gerente, 5 nov 2026).
 * Se guardan en la opción `hv_precios` (no en el tema), para cambiarlos sin publicar:
 *   wp option update hv_precios '{"room-suite-presidential":1500000,"f:habitaciones":250000}' --format=json
 * Claves: clave de la habitación (room-…) o categoría con prefijo "f:" (penthouse, presidencial, suites, habitaciones).
 * Si no hay precio, no se muestra nada (nunca se inventa).
 */
defined( 'ABSPATH' ) || exit;

function hv_precios() {
	static $p = null;
	if ( null === $p ) $p = (array) get_option( 'hv_precios', array() );
	return $p;
}

/** Precio "desde" (COP, entero) de una habitación: por su clave o por su categoría. 0 si no hay. */
function hv_precio_desde( $key, $f = '' ) {
	$p = hv_precios();
	if ( ! empty( $p[ $key ] ) ) return (int) $p[ $key ];
	if ( $f && ! empty( $p[ 'f:' . $f ] ) ) return (int) $p[ 'f:' . $f ];
	return 0;
}

/** "Desde COP 350.000" / "From COP 350,000". */
function hv_precio_txt( $cop, $en = null ) {
	$en = null === $en ? hv_en() : $en;
	return ( $en ? 'From COP ' : 'Desde COP ' ) . number_format( (int) $cop, 0, '', $en ? ',' : '.' );
}

/** Rango del hotel para el schema Hotel (mín. – máx. de los precios cargados). */
function hv_precio_rango() {
	$v = array_filter( array_map( 'intval', hv_precios() ) );
	if ( ! $v ) return '';
	$f = function ( $n ) { return number_format( $n, 0, '', '.' ); };
	return 'COP ' . $f( min( $v ) ) . ( max( $v ) > min( $v ) ? ' – ' . $f( max( $v ) ) : '' );
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
	$cop = hv_precio_desde( hv_key(), $d['f'] ?? '' );
	if ( ! $cop ) return;
	hv_ld( array(
		'@context' => 'https://schema.org', '@type' => 'Offer', 'url' => get_permalink(),
		'name' => ( hv_en() ? 'Nightly rate from · ' : 'Tarifa por noche desde · ' ) . ( $d['name'] ?? get_the_title() ),
		'itemOffered' => array( '@type' => 'HotelRoom', 'name' => $d['name'] ?? get_the_title(), 'url' => get_permalink() ),
		'priceSpecification' => array( '@type' => 'UnitPriceSpecification', 'price' => $cop, 'priceCurrency' => 'COP', 'unitCode' => 'DAY', 'minPrice' => $cop ),
		'price' => $cop, 'priceCurrency' => 'COP',
		'offeredBy' => array( '@type' => 'Hotel', '@id' => home_url( '/#hotel' ), 'name' => 'Haven, A Design Hotel' ),
	) );
}, 8 );
