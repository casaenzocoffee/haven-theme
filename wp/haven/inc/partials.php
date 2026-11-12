<?php
/* Plantillas HTML de la portada y del pie, con marcadores {{…}} que se completan al vuelo. */
defined( 'ABSPATH' ) || exit;

function hv_home_defaults() {
	static $d = null;
	if ( null === $d ) $d = json_decode( (string) file_get_contents( HAVEN_DIR . '/partials/home-defaults.json' ), true );
	return $d;
}

/** Completa marcadores. $ov: textos/imágenes editados (portada). */
function hv_fill( $tpl, $ov = array(), $lang = null ) {
	$lang = $lang ?: hv_lang();
	$def  = hv_home_defaults()[ $lang ] ?? array( 't' => array(), 'img' => array() );
	return preg_replace_callback( '#\{\{(t|ta|img|url|asset|sc|room):([^}]+)\}\}#', function ( $m ) use ( $ov, $def ) {
		switch ( $m[1] ) {
			case 't':
			case 'ta':
				$n = (int) $m[2];
				$v = isset( $ov['t'][ $n ] ) && '' !== trim( (string) $ov['t'][ $n ] ) ? $ov['t'][ $n ] : ( $def['t'][ $n ] ?? '' );
				return 'ta' === $m[1] ? esc_attr( $v ) : esc_html( $v );
			case 'img':
				$n = (int) $m[2];
				$v = ! empty( $ov['img'][ $n ] ) ? $ov['img'][ $n ] : ( $def['img'][ $n ] ?? '' );
				return esc_url( hv_img_url( $v ) );
			case 'url':
				return esc_url( hv_url( $m[2] ) );
			case 'asset':
				return esc_url( hv_asset( $m[2] ) );
			case 'room':
				return hv_home_room_btns( $m[2] );
			case 'sc':
				// Bloques de la guía: los sirve el tema (mismo código del prototipo).
				$g = array( 'haven_mapa' => 'mapa', 'haven_guia' => 'guia', 'haven_quiz' => 'quiz' );
				if ( isset( $g[ $m[2] ] ) ) return hv_guia_html( $g[ $m[2] ] );
				return shortcode_exists( $m[2] ) ? do_shortcode( '[' . $m[2] . ']' ) : '';
		}
		return '';
	}, $tpl );
}

/**
 * Portada, "Nuestras favoritas" (12 nov 2026, pedido del gerente): mismos dos botones que las tarjetas de
 * /todas-las-habitaciones/: "Conocer la suite" y "Ver disponibilidad en vivo" (abre el calendario en vivo de esa suite).
 * Uso en partials/home.html: {{room:room-suite-presidential}}. Si la habitación no está en el calendario, deja "Reservar".
 */
function hv_home_room_btns( $key ) {
	$key  = sanitize_key( $key );
	$name = '';
	foreach ( hv_rooms() as $r ) {
		if ( isset( $r['id'] ) && hv_key( $r['id'] ) === $key ) { $name = $r['name']; break; }
	}
	$en    = hv_en();
	$url   = hv_url( $key );
	$slug  = str_replace( 'room-', '', $key );
	$suite = false !== stripos( $name ?: $key, 'suite' );
	$ver   = $en ? ( $suite ? 'Explore the suite' : 'Explore the room' ) : ( $suite ? 'Conocer la suite' : 'Conocer la habitación' );
	$ghost = '<a class="btn btn-ghost" href="' . esc_url( $url ) . '" data-track="conocer_' . ea( $slug ) . '">' . e( $ver ) . ' ' . HV_ARROW . '</a>';
	$cal   = function_exists( 'hv_dispo_map' ) && isset( hv_dispo_map()[ $key ] ) && $name
		? '<button type="button" class="btn btn-gold card-cal-btn" data-room="' . ea( $key ) . '" data-name="' . ea( $name ) . '" data-api="' . esc_url( rest_url( 'haven/v1/disponibilidad' ) ) . '" data-json="' . esc_url( function_exists( 'hv_dispo_json_base' ) ? hv_dispo_json_base() : '' ) . '" data-track="disponibilidad_' . ea( $slug ) . '">' . e( $en ? 'Live availability' : 'Ver disponibilidad en vivo' ) . ' ' . HV_ARROW . '</button>'
		: '<a class="btn btn-gold" href="#reservar" data-track="reservar_' . ea( $slug ) . '">' . e( t( 'Reservar' ) ) . ' ' . HV_ARROW . '</a>';
	return $ghost . $cal;
}

function hv_front_intro() {
	if ( hv_lite() ) { echo '<script>window.__hvAfterIntro=function(f){f()};</script>'; return; }
	echo file_get_contents( HAVEN_DIR . '/partials/intro.html' ); // apertura del reloj (solo la primera visita de la sesión)
}

function hv_render_home() {
	$ov = json_decode( (string) get_post_meta( hv_current_id(), '_hv_home', true ), true ) ?: array();
	echo hv_seo_fill_alts( hv_responsive( hv_fill( file_get_contents( HAVEN_DIR . '/partials/home.html' ), $ov ) ) );
}

function hv_render_footer() {
	echo hv_responsive( hv_fill( file_get_contents( HAVEN_DIR . '/partials/footer-' . hv_lang() . '.html' ) ) );
}

/** Agrega srcset/sizes a las <img> de la biblioteca de medios que no los traen (portada y pie), como en el prototipo. */
function hv_responsive( $html ) {
	return preg_replace_callback( '#<img\b[^>]*>#', function ( $m ) {
		$tag = $m[0];
		if ( false !== strpos( $tag, 'srcset=' ) || false !== strpos( $tag, 'class="poster"' ) ) return $tag;
		if ( ! preg_match( '#\bsrc="([^"]*/wp-content/uploads/[^"]+)"#', $tag, $s ) ) return $tag;
		$id = hv_attachment_id( html_entity_decode( $s[1] ) );
		if ( ! $id ) return $tag;
		$set = wp_get_attachment_image_srcset( $id, 'full' );
		if ( ! $set ) return $tag;
		$sizes = false !== strpos( $tag, 'fetchpriority="high"' ) ? '(max-width: 820px) 256px, 100vw' : '(max-width: 820px) 256px, 48vw';
		$tag = str_replace( $s[0], $s[0] . ' srcset="' . esc_attr( $set ) . '" sizes="' . $sizes . '"', $tag );
		if ( false === strpos( $tag, 'decoding=' ) ) $tag = str_replace( '<img ', '<img decoding="async" ', $tag );
		return $tag;
	}, $html );
}
