<?php
/* Qué plantilla dibuja cada página (según su clave Haven), sin tocar la plantilla original de WordPress/Elementor. */
defined( 'ABSPATH' ) || exit;

function hv_template_map() {
	return array(
		'home' => 'front-page', 'habitaciones' => 'rooms', 'penthouse' => 'category', 'presidencial' => 'category', 'suites-de-lujo' => 'category', // penthouse y suites-de-lujo pasan a 'landing' cuando su contenido tiene bloques (ver abajo)
		'rooftop' => 'experience', 'swing' => 'experience', 'wellness' => 'experience', 'eventos' => 'experience',
		'gastronomia' => 'gastronomy', 'servicios' => 'services', 'contacto' => 'contact', 'preguntas-frecuentes' => 'faq', 'serena-rewards' => 'rewards',
		'politica-de-visitantes' => 'legal', 'terminos-y-condiciones' => 'legal', 'blogs' => 'blogs', 'guia-medellin' => 'guide', 'que-suite-elegir' => 'guide', 'reservar' => 'reservar',
	) + array_fill_keys( hv_landing_keys(), 'landing' );
}

/** Clave de plantilla de la vista actual: home, rooms, room, experience, post, page, 404… ('' si no es una vista del tema). */
function hv_template_key() {
	static $k = null;
	if ( null !== $k ) return $k;
	if ( is_admin() || ! did_action( 'wp' ) ) return '';
	// Vista previa del editor de Elementor: se deja que Elementor dibuje la página para que su editor cargue.
	if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['elementor_library'] ) ) return $k = '';
	if ( is_front_page() ) return $k = 'home';
	if ( is_404() ) return $k = '404';
	if ( is_singular( 'post' ) ) return $k = 'post';
	if ( is_home() ) return $k = 'blogs';
	if ( is_page() ) {
		$key = hv_key();
		if ( 0 === strpos( $key, 'room-' ) ) return $k = 'room';
		$map = hv_template_map();
		if ( isset( $map[ $key ] ) ) {
			$t = 'guia-medellin' === $key ? 'guia' : ( 'que-suite-elegir' === $key ? 'quiz' : $map[ $key ] );
			// Penthouse y Suites de lujo: con el contenido nuevo (bloques) usan la plantilla de página por intención; sin él, la de categoría de siempre.
			if ( 'category' === $t && ! empty( hv_data()['blocks'] ) ) $t = 'landing';
			return $k = $t;
		}
		return $k = 'page';
	}
	return $k = 'page';
}

add_filter( 'template_include', function ( $tpl ) {
	$k = hv_template_key();
	if ( '' === $k ) return $tpl;
	$file = array( 'home' => 'front-page', 'room' => 'room', 'post' => 'post', 'blogs' => 'blogs', '404' => '404', 'guia' => 'guide', 'quiz' => 'guide', 'page' => 'page' )[ $k ] ?? $k;
	if ( is_page() ) {
		$key = hv_key();
		$map = hv_template_map();
		if ( isset( $map[ $key ] ) && 'landing' !== $k ) $file = $map[ $key ];
	}
	$path = HAVEN_DIR . '/templates/' . $file . '.php';
	return ( $file && file_exists( $path ) ) ? $path : $tpl;
}, 99 );

/** Elementor no debe reemplazar las plantillas del tema en las páginas con clave Haven. */
add_filter( 'elementor/frontend/builder_content_data', function ( $data, $post_id ) {
	return ( hv_key( $post_id ) && ! is_admin() && ! ( isset( $_GET['elementor-preview'] ) ) ) ? array() : $data;
}, 10, 2 );
