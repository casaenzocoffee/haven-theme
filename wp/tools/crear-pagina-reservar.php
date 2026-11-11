<?php
/* Crea (si faltan) las páginas Reservar: /es/reservar/ y /en/book/, clave Haven 'reservar', enlazadas en Polylang.
   Idempotente: si ya existen (por clave o slug), no las duplica. Uso: wp eval-file crear-pagina-reservar.php */
$want = array(
	'es' => array( 'title' => 'Reservar en Haven', 'slug' => 'reservar' ),
	'en' => array( 'title' => 'Book at Haven', 'slug' => 'book' ),
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
	$ids[ $lang ] = $id;
}
if ( function_exists( 'pll_save_post_translations' ) && count( $ids ) === 2 ) pll_save_post_translations( $ids );
delete_transient( 'hv_keymap' );
foreach ( $ids as $lang => $id ) echo "$lang: " . get_permalink( $id ) . "\n";
