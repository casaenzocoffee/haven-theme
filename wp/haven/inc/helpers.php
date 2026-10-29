<?php
/* Utilidades: idioma, textos, datos de cada página, enlaces e imágenes. */
defined( 'ABSPATH' ) || exit;

/** Idioma actual (es|en). */
function hv_lang() {
	static $l = null;
	if ( null !== $l ) return $l;
	$l = function_exists( 'pll_current_language' ) ? ( pll_current_language( 'slug' ) ?: 'es' ) : 'es';
	if ( 'es' !== $l && 'en' !== $l ) $l = 'es';
	return $l;
}
function hv_en() { return 'en' === hv_lang(); }

/** Traduce un texto de interfaz escrito en español. */
function t( $es ) {
	static $d = null;
	if ( ! hv_en() ) return $es;
	if ( null === $d ) $d = include HAVEN_DIR . '/inc/i18n-en.php';
	return $d[ $es ] ?? $es;
}
function e( $s ) { return esc_html( (string) $s ); }
function ea( $s ) { return esc_attr( (string) $s ); }

/** ID de la página que se está viendo (en la portada, la página de inicio del idioma aunque WordPress no la haya cargado como página). */
function hv_current_id() {
	$id = (int) get_queried_object_id();
	if ( ! $id && is_front_page() ) $id = hv_page_id( 'home' );
	return $id;
}

/** Datos estructurados de una página (meta _hv_data, JSON). */
function hv_data( $post_id = null ) {
	$post_id = $post_id ?: hv_current_id();
	static $cache = array();
	if ( isset( $cache[ $post_id ] ) ) return $cache[ $post_id ];
	$raw = get_post_meta( $post_id, '_hv_data', true );
	$d   = $raw ? json_decode( $raw, true ) : array();
	return $cache[ $post_id ] = is_array( $d ) ? $d : array();
}
function hv_key( $post_id = null ) {
	return (string) get_post_meta( $post_id ?: hv_current_id(), '_hv_key', true );
}

/** Mapa idioma → clave → ID de todas las páginas/entradas con clave Haven (una sola consulta, en caché hasta que se guarde algo). */
function hv_key_map() {
	static $m = null;
	if ( null !== $m ) return $m;
	$m = get_transient( 'hv_keymap' );
	if ( is_array( $m ) ) return $m;
	$m   = array();
	$ids = get_posts( array( 'post_type' => array( 'page', 'post' ), 'post_status' => 'publish', 'posts_per_page' => 500, 'fields' => 'ids', 'meta_key' => '_hv_key', 'no_found_rows' => true, 'lang' => '', 'orderby' => 'ID', 'order' => 'ASC' ) );
	update_meta_cache( 'post', $ids );
	foreach ( $ids as $id ) {
		$k = get_post_meta( $id, '_hv_key', true );
		$l = function_exists( 'pll_get_post_language' ) ? ( pll_get_post_language( $id ) ?: 'es' ) : 'es';
		if ( $k && ! isset( $m[ $l ][ $k ] ) ) $m[ $l ][ $k ] = (int) $id;
	}
	set_transient( 'hv_keymap', $m, DAY_IN_SECONDS );
	return $m;
}
foreach ( array( 'save_post', 'deleted_post', 'trashed_post', 'untrashed_post', 'updated_post_meta', 'added_post_meta', 'deleted_post_meta', 'pll_save_post', 'update_option_permalink_structure', 'update_option_polylang' ) as $hv_h ) {
	add_action( $hv_h, function () { delete_transient( 'hv_keymap' ); delete_transient( 'hv_urlmap' ); } );
}

/** Página (ID) con una clave Haven en el idioma actual (o el indicado). */
function hv_page_id( $key, $lang = null ) {
	$lang = $lang ?: hv_lang();
	if ( 'home' === $key ) {
		$front = (int) get_option( 'page_on_front' );
		if ( $front && function_exists( 'pll_get_post' ) ) $front = (int) pll_get_post( $front, $lang ) ?: $front;
		if ( $front ) return $front;
	}
	return hv_key_map()[ $lang ][ $key ] ?? 0;
}

/** URL de una página por clave (p. ej. 'habitaciones', 'room-habitacion-duke', 'home'). */
function hv_url( $key, $lang = null ) {
	static $urls = null;
	$lang = $lang ?: hv_lang();
	if ( 'home' === $key ) return function_exists( 'pll_home_url' ) ? pll_home_url( $lang ) : home_url( '/' );
	if ( null === $urls ) $urls = get_transient( 'hv_urlmap' ) ?: array();
	if ( isset( $urls[ $lang ][ $key ] ) ) return $urls[ $lang ][ $key ];
	static $primed = false;
	if ( ! $primed ) { // carga de una vez todas las páginas Haven (y sus idiomas) para no hacer una consulta por enlace
		$primed = true;
		$all = array();
		foreach ( hv_key_map() as $ids ) $all = array_merge( $all, array_values( $ids ) );
		if ( $all ) _prime_post_caches( $all, true, true );
	}
	$id = hv_page_id( $key, $lang );
	$u  = $id ? get_permalink( $id ) : ( function_exists( 'pll_home_url' ) ? pll_home_url( $lang ) : home_url( '/' ) );
	if ( $id ) { $urls[ $lang ][ $key ] = $u; set_transient( 'hv_urlmap', $urls, DAY_IN_SECONDS ); }
	return $u;
}

/** Resuelve enlaces guardados en el contenido: page:clave, theme:ruta o URL normal. */
function hv_href( $h ) {
	$h = (string) $h;
	if ( 0 === strpos( $h, 'page:' ) ) {
		$k = substr( $h, 5 );
		$alias = array( 'habitacion/' => 'room-', 'blog/' => 'post-' );
		foreach ( $alias as $a => $p ) if ( 0 === strpos( $k, $a ) ) $k = $p . substr( $k, strlen( $a ) );
		return hv_url( $k );
	}
	if ( 0 === strpos( $h, 'theme:' ) ) return HAVEN_URI . '/assets/' . substr( $h, 6 );
	return $h;
}

/** URL pública de un archivo del tema (con versión para la caché). */
function hv_asset( $path ) {
	$f = HAVEN_DIR . '/assets/' . $path;
	$v = file_exists( $f ) ? substr( md5( filemtime( $f ) . filesize( $f ) ), 0, 8 ) : HAVEN_VER;
	return HAVEN_URI . '/assets/' . $path . '?v=' . $v;
}

/** ID de adjunto a partir de una URL de /wp-content/uploads (incluidas variantes -WxH). Caché en la opción hv_att_map, guardada una vez al final. */
function hv_attachment_id( $url ) {
	static $map = null;
	if ( is_numeric( $url ) ) return (int) $url;
	if ( ! $url || ! is_string( $url ) || false === strpos( $url, '/wp-content/uploads/' ) ) return 0;
	if ( null === $map ) $map = get_option( 'hv_att_map', array() ) ?: array();
	$base = preg_replace( '#-\d+x\d+(\.\w+)$#', '$1', strtok( $url, '?' ) );
	$base = preg_replace( '#^https?://[^/]+#', '', $base );
	if ( isset( $map[ $base ] ) ) return (int) $map[ $base ];
	$id = attachment_url_to_postid( home_url( $base ) );
	if ( ! $id ) $id = attachment_url_to_postid( preg_replace( '#(\.\w+)$#', '-scaled$1', home_url( $base ) ) );
	$map[ $base ] = (int) $id;
	$GLOBALS['hv_att_map_dirty'] = $map;
	return (int) $id;
}
add_action( 'shutdown', function () {
	if ( ! empty( $GLOBALS['hv_att_map_dirty'] ) ) update_option( 'hv_att_map', $GLOBALS['hv_att_map_dirty'], false );
} );
// Si cambia la biblioteca de medios, se recalcula la caché.
foreach ( array( 'add_attachment', 'delete_attachment', 'edit_attachment' ) as $hv_h ) add_action( $hv_h, function () { delete_option( 'hv_att_map' ); } );

/**
 * Imagen con srcset nativo de WordPress cuando el archivo está en la biblioteca.
 * $src: ID de adjunto, URL de uploads o theme:ruta. $a: atributos extra (alt, class, loading, fetchpriority, sizes, data-*).
 */
function hv_img( $src, $a = array() ) {
	$a = array_merge( array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ), $a );
	if ( isset( $a['fetchpriority'] ) && 'high' === $a['fetchpriority'] ) { $a['loading'] = 'eager'; }
	if ( empty( $a['sizes'] ) ) $a['sizes'] = '(max-width: 820px) 256px, 48vw';
	$id = hv_attachment_id( $src );
	if ( $id ) {
		$html = wp_get_attachment_image( $id, 'full', false, $a );
		if ( $html ) return $html;
	}
	$url = is_string( $src ) ? hv_href( $src ) : '';
	unset( $a['sizes'] );
	$attr = '';
	foreach ( $a as $k => $v ) { if ( false === $v || null === $v ) continue; $attr .= ' ' . $k . '="' . esc_attr( $v ) . '"'; }
	return '<img src="' . esc_url( $url ) . '"' . $attr . '>';
}
/** Solo la URL (para fondos, enlaces de galería, etc.). */
function hv_img_url( $src, $size = 'full' ) {
	$id = hv_attachment_id( $src );
	if ( $id ) { $u = wp_get_attachment_image_url( $id, $size ); if ( $u ) return $u; }
	return hv_href( is_string( $src ) ? $src : '' );
}

/** Nombres de marca en el texto visible: 360° Rooftop & Restaurante/Restaurant y Swing Coffee Bar. */
function hv_brand( $html ) {
	$rest = hv_en() ? 'Restaurant' : 'Restaurante';
	$html = preg_replace( '#Swing <em>Bar</em>#', 'Swing <em>Coffee Bar</em>', $html );
	$html = preg_replace( '#360°? <em>Rooftop</em>|Rooftop <em>360°?</em>#u', '360° Rooftop <em>&amp; ' . $rest . '</em>', $html );
	$html = preg_replace( '#(<em>&amp; )Restaurante?(</em>)#u', '${1}' . $rest . '$2', $html );
	$parts = preg_split( '#(<script\b.*?</script>|<style\b.*?</style>|<[^>]+>)#s', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	$fix = function ( $t ) use ( $rest ) {
		$t = preg_replace( '#Rooftop 360°?#u', '360° Rooftop', $t );
		$t = preg_replace( '#360° Rooftop (&amp;|&) Restaurante?\b#u', '360° Rooftop &amp; ' . $rest, $t );
		$t = preg_replace( '#360° Rooftop(?! &| <em>&)(?!\s+$)#u', '360° Rooftop &amp; ' . $rest, $t );
		$t = preg_replace( '#\bSwing Bar\b#', 'Swing Coffee Bar', $t );
		$t = preg_replace( '#\bSwing\b(?! Coffee| Bar| Menu)(?!\s+$)#', 'Swing Coffee Bar', $t );
		return $t;
	};
	foreach ( $parts as $i => $p ) {
		if ( '' === $p ) continue;
		if ( 0 === strpos( $p, '<script' ) || 0 === strpos( $p, '<style' ) ) continue;
		if ( '<' === $p[0] ) {
			$parts[ $i ] = preg_replace_callback( '#\b(alt|title|aria-label|content)="([^"]*)"#', function ( $m ) use ( $fix ) {
					if ( 'content' === $m[1] && preg_match( '#^(https?:|/|width=|\d+$)#', $m[2] ) ) return $m[0];
					return $m[1] . '="' . $fix( $m[2] ) . '"';
				}, $p );
		} else {
			$parts[ $i ] = $fix( $p );
		}
	}
	return implode( '', $parts );
}

/** Habitaciones del idioma actual, en el orden del sitio. */
function hv_rooms( $lang = null ) {
	$lang = $lang ?: hv_lang();
	static $cache = array();
	if ( isset( $cache[ $lang ] ) ) return $cache[ $lang ];
	$args = array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 60, 'meta_key' => '_hv_order', 'orderby' => 'meta_value_num', 'order' => 'ASC',
		'meta_query' => array( array( 'key' => '_hv_key', 'value' => 'room-', 'compare' => 'LIKE' ) ), 'no_found_rows' => true, 'suppress_filters' => false );
	if ( function_exists( 'pll_current_language' ) ) $args['lang'] = $lang;
	$out = array();
	foreach ( get_posts( $args ) as $p ) {
		$d = hv_data( $p->ID );
		if ( ! $d ) continue;
		$d['id']  = $p->ID;
		$d['url'] = get_permalink( $p );
		$out[]    = $d;
	}
	return $cache[ $lang ] = $out;
}

/** Enlace de WhatsApp de reservas. */
/** Carta digital del 360° Rooftop (subdominio propio). Se puede cambiar con el filtro hv_menu_360 o con menu_url en la página. */
function hv_menu_360() { return apply_filters( 'hv_menu_360', 'https://carta.havenmedellin.com/' ); }

function hv_wa( $text = '' ) { return 'https://wa.me/573212097033?text=' . rawurlencode( $text ); }

/** URL de esta misma página en español e inglés (Polylang); si no hay traducción, la portada de ese idioma. */
function hv_lang_links() {
	$out = array( 'es' => home_url( '/es/' ), 'en' => home_url( '/en/' ) );
	if ( function_exists( 'pll_the_languages' ) ) {
		$raw = pll_the_languages( array( 'raw' => 1, 'hide_if_no_translation' => 0, 'hide_if_empty' => 0 ) );
		foreach ( (array) $raw as $slug => $l ) {
			if ( ! isset( $out[ $slug ] ) ) continue;
			$out[ $slug ] = ( ! empty( $l['no_translation'] ) || is_front_page() ) ? pll_home_url( $slug ) : $l['url'];
		}
	}
	return $out;
}
function hv_logo_url() {
	$id = get_theme_mod( 'custom_logo' );
	if ( $id ) { $u = wp_get_attachment_image_url( $id, 'medium' ); if ( $u ) return $u; }
	return home_url( '/wp-content/uploads/2026/06/logo-haven.webp' );
}

/**
 * Encabezado de una página: cada campo se toma de $d['hero'][campo], luego del campo plano del contenido, luego del valor por defecto.
 * Campos: title (o h1/title_html), sub, img, eyebrow.
 */
function hv_hero_data( $d, $def = array() ) {
	$h   = (array) ( $d['hero'] ?? array() );
	$pick = function ( $keys, $fallback ) use ( $h, $d ) {
		foreach ( (array) $keys as $k ) if ( ! empty( $h[ $k ] ) ) return $h[ $k ];
		foreach ( (array) $keys as $k ) if ( ! empty( $d[ $k ] ) && is_string( $d[ $k ] ) ) return $d[ $k ];
		return $fallback;
	};
	return array(
		'title'   => $pick( array( 'title', 'h1', 'title_html' ), $def['title'] ?? e( get_the_title() ) ),
		'sub'     => $pick( array( 'sub' ), $def['sub'] ?? '' ),
		'img'     => $pick( array( 'img' ), $def['img'] ?? '' ),
		'eyebrow' => $pick( array( 'eyebrow' ), $def['eyebrow'] ?? 'Haven · Medellín' ),
	);
}
/** Recorta un texto a ~$n caracteres sin cortar palabras. */
function hv_excerpt( $s, $n = 150 ) {
	$s = (string) $s;
	if ( mb_strlen( $s ) <= $n ) return $s;
	$c = mb_substr( $s, 0, $n );
	$p = mb_strrpos( $c, ' ' );
	return ( false !== $p ? mb_substr( $c, 0, $p ) : $c ) . '…';
}

/** Entradas del blog del idioma actual con los datos de su tarjeta (título, fecha, resumen, imagen, URL). */
function hv_posts( $exclude = 0 ) {
	$args = array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 12, 'no_found_rows' => true, 'suppress_filters' => false );
	if ( function_exists( 'pll_current_language' ) ) $args['lang'] = hv_lang();
	$out = array();
	foreach ( get_posts( $args ) as $p ) {
		if ( (int) $exclude === $p->ID ) continue;
		$d   = hv_data( $p->ID );
		$img = $d['img'] ?? ( get_post_thumbnail_id( $p ) ?: '' );
		$ex  = $d['ex'] ?? hv_excerpt( wp_strip_all_tags( get_the_excerpt( $p ) ), 90 );
		$out[] = array(
			'id' => $p->ID, 'url' => get_permalink( $p ), 'title' => $d['title'] ?? get_the_title( $p ),
			'date' => $d['date'] ?? date_i18n( 'F j, Y', strtotime( $p->post_date ) ), 'ex' => rtrim( $ex, '…' ), 'img' => $img,
		);
	}
	return $out;
}

/* ---------- Guía "Medellín desde Haven" (mapa, plan y quiz) ---------- */
/** Módulos de JS que usa cada bloque de la guía. */
function hv_guia_mods( $block ) {
	return array( 'guia' => array( 'mapa', 'plan', 'quiz' ), 'mapa' => array( 'mapa' ), 'quiz' => array( 'quiz' ) )[ $block ] ?? array();
}
/** Enlaces internos guardados como {{R}}habitacion/slug/ → URL de esa habitación en el idioma actual. */
function hv_guia_links( $s ) {
	return preg_replace_callback( '#\{\{R\}\}([a-z0-9/-]*)#', function ( $m ) {
		$p = trim( $m[1], '/' );
		if ( '' === $p ) return hv_url( 'home' );
		if ( 0 === strpos( $p, 'habitacion/' ) ) return hv_url( 'room-' . substr( $p, 11 ) );
		return hv_url( $p );
	}, $s );
}
/** HTML de un bloque de la guía; deja anotados los módulos para que el pie cargue sus datos y scripts. */
function hv_guia_html( $block ) {
	$f = HAVEN_DIR . '/guia/' . $block . '_' . hv_lang() . '.html';
	if ( ! file_exists( $f ) ) return '';
	$GLOBALS['hv_guia_mods'] = array_unique( array_merge( $GLOBALS['hv_guia_mods'] ?? array(), hv_guia_mods( $block ) ) );
	$h = str_replace( 'class="hg-map__stage"', 'class="hg-map__stage" data-lenis-prevent', file_get_contents( $f ) );
	if ( 'guia' !== $block ) $h = preg_replace( '/(hg-eyebrow">)(?:Capítulo|Chapter) \d+ · /u', '$1', $h );
	return hv_guia_links( $h );
}
/** Shortcode [haven_guia bloque="guia|mapa|quiz"] para usar la guía en cualquier página o entrada. */
add_shortcode( 'haven_guia', function ( $a ) {
	$a = shortcode_atts( array( 'bloque' => 'guia' ), $a );
	return '<div class="hg-host hg-host--' . esc_attr( $a['bloque'] ) . '">' . hv_guia_html( $a['bloque'] ) . '</div>';
} );
/** Estilos y scripts de la guía solo donde se usa. */
add_action( 'wp_enqueue_scripts', function () {
	if ( in_array( hv_template_key(), array( 'guia', 'quiz', 'home' ), true ) || ( is_singular() && has_shortcode( (string) get_post_field( 'post_content', hv_current_id() ), 'haven_guia' ) ) ) {
		wp_enqueue_style( 'haven-theme-guia', hv_asset( 'haven-guia.css' ), array( 'haven' ), null );
	}
}, 30 );
add_action( 'wp_footer', function () {
	$mods = $GLOBALS['hv_guia_mods'] ?? array();
	if ( ! $mods ) return;
	$f = HAVEN_DIR . '/guia/data_' . hv_lang() . '.json';
	$d = file_exists( $f ) ? json_decode( file_get_contents( $f ), true ) : array();
	if ( ! in_array( 'mapa', $mods, true ) && ! in_array( 'plan', $mods, true ) ) $d['lugares'] = array();
	if ( ! in_array( 'quiz', $mods, true ) && ! empty( $d['suites'] ) ) {
		$d['suites'] = array_map( function ( $x ) { return array_intersect_key( $x, array_flip( array( 'id', 'n', 'url', 'img', 'cat' ) ) ); }, $d['suites'] );
	}
	$json = wp_json_encode( $d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP );
	$json = hv_guia_links( $json );
	// Portada: el mapa está muy abajo, así que los datos van en un archivo aparte (se pide al acercarse). Ahorra ~110 KB del HTML.
	$src = '';
	if ( 'home' === hv_template_key() ) {
		$up  = wp_upload_dir();
		$dir = $up['basedir'] . '/hv-guia';
		$fn  = 'guia-' . hv_lang() . '-' . substr( md5( $json ), 0, 10 ) . '.json';
		if ( ! file_exists( "$dir/$fn" ) ) { wp_mkdir_p( $dir ); @file_put_contents( "$dir/$fn", $json ); }
		if ( file_exists( "$dir/$fn" ) ) $src = set_url_scheme( $up['baseurl'] . '/hv-guia/' . $fn );
	}
	if ( $src ) echo '<script type="application/json" id="haven-guia-data" data-src="' . esc_url( $src ) . '"></script>' . "\n";
	else echo '<script type="application/json" id="haven-guia-data">' . $json . "</script>\n";
	echo '<script src="' . esc_url( hv_asset( 'hg-core.js' ) ) . '" defer></script>' . "\n";
	foreach ( $mods as $m ) echo '<script src="' . esc_url( hv_asset( 'hg-' . $m . '.js' ) ) . '" defer></script>' . "\n";
}, 20 );
