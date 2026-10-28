<?php
/*
 * SEO: Yoast se encarga de títulos, descripciones, Open Graph, canonical y su grafo (BreadcrumbList, Article…);
 * Polylang publica los hreflang. El tema agrega solo lo que Yoast no sabe: Hotel, HotelRoom y FAQPage.
 * Si Yoast no está activo, el tema pone título, descripción, canonical y Open Graph básicos (meta _hv_seo).
 */
defined( 'ABSPATH' ) || exit;

function hv_yoast() { return defined( 'WPSEO_VERSION' ); }

function hv_hotel_schema() {
	$amen = hv_en() ? array( 'Rooftop with pool', 'Bar', 'Spa and wellness', 'Suites with jacuzzi' ) : array( 'Rooftop con piscina', 'Bar', 'Spa y wellness', 'Suites con jacuzzi' );
	return array(
		'@context' => 'https://schema.org', '@type' => 'Hotel', '@id' => home_url( '/#hotel' ), 'name' => 'Haven, A Design Hotel', 'url' => hv_url( 'home' ),
		'image' => home_url( '/wp-content/uploads/2026/06/hotel-haven-medellin.webp' ), 'telephone' => '+57 321 209 7033', 'email' => 'booking@serenahotelsco.com',
		'address' => array( '@type' => 'PostalAddress', 'streetAddress' => 'Calle 10A #36-29', 'addressLocality' => 'Medellín', 'addressRegion' => 'Antioquia', 'postalCode' => '050021', 'addressCountry' => 'CO' ),
		'geo' => array( '@type' => 'GeoCoordinates', 'latitude' => 6.2096216, 'longitude' => -75.5660737 ),
		'hasMap' => 'https://www.google.com/maps/search/?api=1&query=Haven+Medell%C3%ADn+Calle+10A+36-29+El+Poblado',
		'amenityFeature' => array_map( function ( $a ) { return array( '@type' => 'LocationFeatureSpecification', 'name' => $a, 'value' => true ); }, $amen ),
		'sameAs' => array( 'https://www.instagram.com/haven_medellin', 'https://www.facebook.com/HavenaDesignHotel', 'https://www.tiktok.com/@haven_medellin' ),
	);
}

function hv_room_schema( $r, $url ) {
	$num = function ( $x ) { return preg_match( '/(\d+)/', (string) $x, $m ) ? (int) $m[1] : null; };
	$imgs = array();
	foreach ( array_slice( (array) ( $r['imgs'] ?? array() ), 0, 6 ) as $u ) $imgs[] = hv_img_url( $u );
	$s = array(
		'@context' => 'https://schema.org', '@type' => 'HotelRoom', 'name' => $r['name'] ?? '', 'url' => $url,
		'description' => mb_substr( (string) ( $r['desc'][0] ?? '' ), 0, 300 ), 'image' => $imgs,
		'occupancy' => array( '@type' => 'QuantitativeValue', 'maxValue' => $num( $r['per'] ?? '' ) ?: 2, 'unitCode' => 'C62' ),
		'amenityFeature' => array_map( function ( $a ) { return array( '@type' => 'LocationFeatureSpecification', 'name' => $a, 'value' => true ); }, array_slice( (array) ( $r['amen'] ?? array() ), 0, 15 ) ),
		'containedInPlace' => array( '@type' => 'Hotel', '@id' => home_url( '/#hotel' ), 'name' => 'Haven, A Design Hotel', 'url' => hv_url( 'home' ) ),
	);
	if ( $num( $r['m2'] ?? '' ) ) $s['floorSize'] = array( '@type' => 'QuantitativeValue', 'value' => $num( $r['m2'] ), 'unitCode' => 'MTK' );
	if ( ! empty( $r['beds'] ) ) $s['bed'] = array_map( function ( $b ) { return array( '@type' => 'BedDetails', 'typeOfBed' => $b ); }, (array) $r['beds'] );
	return $s;
}

function hv_ld( $data ) {
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ) . "</script>\n";
}

add_action( 'wp_head', function () {
	$k = hv_template_key();
	if ( 'home' === $k ) hv_ld( hv_hotel_schema() );
	if ( 'room' === $k ) hv_ld( hv_room_schema( hv_data(), get_permalink() ) );
	if ( 'faq' === ( hv_template_map()[ hv_key() ] ?? '' ) ) {
		$qs = array();
		foreach ( (array) ( hv_data()['items'] ?? array() ) as $q ) {
			if ( empty( $q['q'] ) ) continue;
			$qs[] = array( '@type' => 'Question', 'name' => $q['q'], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => implode( ' ', array_filter( (array) ( $q['a'] ?? array() ), 'is_string' ) ) ) );
		}
		if ( $qs ) hv_ld( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $qs ) );
	}
	if ( hv_yoast() ) return;
	// --- Sin Yoast: descripción, canonical y Open Graph básicos.
	$id   = hv_current_id();
	$seo  = $id ? (array) json_decode( (string) get_post_meta( $id, '_hv_seo', true ), true ) : array();
	$desc = $seo['desc'] ?? ( is_singular() && has_excerpt( $id ) ? get_the_excerpt( $id ) : get_bloginfo( 'description' ) );
	$url  = is_singular() || is_front_page() ? ( $id ? get_permalink( $id ) : home_url( '/' ) ) : '';
	echo '<meta name="description" content="' . ea( $desc ) . '">' . "\n";
	echo '<meta property="og:type" content="' . ( is_singular( 'post' ) ? 'article' : 'website' ) . '"><meta property="og:site_name" content="Haven, A Design Hotel"><meta property="og:locale" content="' . ( hv_en() ? 'en_US' : 'es_CO' ) . '">' . "\n";
	echo '<meta property="og:title" content="' . ea( wp_get_document_title() ) . '"><meta property="og:description" content="' . ea( $desc ) . '">' . "\n";
	if ( $url ) echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	$og = $seo['og'] ?? ( hv_data()['hero']['img'] ?? ( hv_data()['img'] ?? '/wp-content/uploads/2026/06/hotel-haven-medellin.webp' ) );
	$ogu = hv_img_url( $og );
	if ( 0 === strpos( $ogu, '/' ) ) $ogu = home_url( $ogu );
	echo '<meta property="og:image" content="' . esc_url( $ogu ) . '"><meta name="twitter:card" content="summary_large_image">' . "\n";
}, 5 );

/** Sin Yoast: título de la meta _hv_seo. */
add_filter( 'pre_get_document_title', function ( $t ) {
	if ( hv_yoast() || is_admin() ) return $t;
	$id = hv_current_id();
	if ( ! $id ) return $t;
	$seo = (array) json_decode( (string) get_post_meta( $id, '_hv_seo', true ), true );
	return $seo['title'] ?? $t;
}, 20 );

/** Yoast: imagen OG de la página cuando no se definió una en Yoast. */
add_filter( 'wpseo_opengraph_image', function ( $img ) {
	if ( $img ) return $img;
	$d = hv_data();
	$u = $d['hero']['img'] ?? ( $d['img'] ?? ( $d['cover'] ?? '' ) );
	if ( ! $u ) return $img;
	$u = hv_img_url( $u );
	return 0 === strpos( $u, '/' ) ? home_url( $u ) : $u;
} );
