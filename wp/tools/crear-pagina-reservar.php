<?php
/* Crea (si faltan) las páginas Reservar: /es/reservar/ y /en/book/, clave Haven 'reservar', enlazadas en Polylang.
   Idempotente: si ya existen (por clave o slug), no las duplica. Uso: wp eval-file crear-pagina-reservar.php */
$want = array(
	'es' => array( 'title' => 'Reservar en Haven', 'slug' => 'reservar',
		'seo_t' => 'Reservar en Haven Medellín: disponibilidad y precios en vivo',
		'seo_d' => 'Consulte en vivo la disponibilidad y los precios de las 17 habitaciones y suites de Haven en El Poblado, Medellín, y reserve directo. Desayuno incluido.' ),
	'en' => array( 'title' => 'Book at Haven', 'slug' => 'book',
		'seo_t' => 'Book Haven Medellín: live availability and prices',
		'seo_d' => 'Check live availability and prices for the 17 rooms and suites at Haven in El Poblado, Medellín, and book direct. Breakfast included.' ),
);
$ids = array();
foreach ( $want as $lang => $w ) {
	$id = function_exists( 'hv_find_post' ) ? (int) hv_find_post( $w['slug'], $lang, 'page', 'reservar' ) : 0;
	if ( ! $id ) {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $w['title'], 'post_name' => $w['slug'], 'post_content' => '' ), true );
		if ( is_wp_error( $id ) ) { echo "ERROR $lang: " . $id->get_error_message() . "\n"; continue; }
		echo "creada $lang id=$id\n";
	} else {
		echo "ya existe $lang id=$id\n";
	}
	if ( function_exists( 'pll_set_post_language' ) ) pll_set_post_language( $id, $lang );
	update_post_meta( $id, '_hv_key', 'reservar' );
	// Título y descripción para Google (Yoast y, sin Yoast, la meta del tema _hv_seo). 11 nov.
	update_post_meta( $id, '_yoast_wpseo_title', $w['seo_t'] );
	update_post_meta( $id, '_yoast_wpseo_metadesc', $w['seo_d'] );
	update_post_meta( $id, '_hv_seo', wp_json_encode( array( 'title' => $w['seo_t'], 'desc' => $w['seo_d'] ), JSON_UNESCAPED_UNICODE ) );
	wp_update_post( array( 'ID' => $id ) ); // dispara save_post: Yoast reconstruye su índice con el título y la descripción
	$ids[ $lang ] = $id;
}
if ( function_exists( 'pll_save_post_translations' ) && count( $ids ) === 2 ) pll_save_post_translations( $ids );
delete_transient( 'hv_keymap' );
if ( class_exists( 'WPSEO_Options' ) ) { foreach ( $ids as $id ) { clean_post_cache( $id ); } }
foreach ( $ids as $lang => $id ) echo "$lang: " . get_permalink( $id ) . "\n";
