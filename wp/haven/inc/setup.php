<?php
/* Soportes del tema, estilos y scripts, limpieza para velocidad e integraciones (WP Rocket, Joinchat). */
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
} );

/** ¿La página actual la dibuja el tema Haven? (todas las públicas excepto admin/feeds). */
function hv_is_haven_view() {
	return ! is_admin() && ! is_feed() && ! wp_doing_ajax() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST );
}

/** Librerías (GSAP, Lenis, three.js) servidas desde el propio dominio si están en uploads/hv-vendor/; si no, desde el CDN. */
function hv_vendor( $rel, $cdn ) {
	static $up = null;
	if ( null === $up ) $up = wp_upload_dir();
	$f = $up['basedir'] . '/hv-vendor/' . $rel;
	if ( ! file_exists( $f ) ) return $cdn;
	return set_url_scheme( $up['baseurl'] . '/hv-vendor/' . $rel ) . '?v=' . substr( md5( (string) filesize( $f ) ), 0, 6 );
}
function hv_vendor_local() { $up = wp_upload_dir(); return file_exists( $up['basedir'] . '/hv-vendor/gsap.min.js' ); }

add_action( 'wp_enqueue_scripts', function () {
	$ver = null;
	wp_enqueue_style( 'haven', hv_asset( 'haven.css' ), array(), $ver );
	wp_enqueue_script( 'hv-gsap', hv_vendor( 'gsap.min.js', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js' ), array(), null, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_enqueue_script( 'hv-gsap-st', hv_vendor( 'ScrollTrigger.min.js', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js' ), array( 'hv-gsap' ), null, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_enqueue_script( 'hv-lenis', hv_vendor( 'lenis.min.js', 'https://cdn.jsdelivr.net/npm/lenis@1.1.13/dist/lenis.min.js' ), array(), null, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_enqueue_script( 'haven', hv_asset( 'haven.js' ), array( 'hv-gsap', 'hv-gsap-st', 'hv-lenis' ), null, array( 'strategy' => 'defer', 'in_footer' => true ) );
	if ( is_front_page() && function_exists( 'wp_enqueue_script_module' ) ) {
		// Sin dependencias registradas: el mapa de importación lo imprime el tema en el <head>, antes de las precargas (si no, el navegador lo ignora).
		wp_enqueue_script_module( 'haven-clock', hv_asset( 'clock3d.js' ), array(), null );
	}
	// Bloques de Gutenberg y estilos globales: el tema no los usa en sus plantillas.
	if ( hv_template_key() ) {
		foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles' ) as $h ) wp_dequeue_style( $h );
	}
}, 20 );

/** Quita los recursos de constructores visuales (Elementor, Slider Revolution, Unlimited Elements) en las páginas del tema. */
/** ¿Página hecha con Elementor y sin clave Haven? (se deja que Elementor la dibuje con sus estilos). */
function hv_is_builder_page( $id = null ) {
	$id = $id ?: hv_current_id();
	return $id && is_page( $id ) && ! hv_key( $id ) && 'builder' === get_post_meta( $id, '_elementor_edit_mode', true );
}

function hv_strip_builder_assets() {
	if ( ! hv_template_key() || hv_is_builder_page() ) return;
	$pat = '#/plugins/(elementor|elementor-pro|revslider|unlimited-elements[^/]*|skyboot-[^/]*|haven-guia[^/]*|connect-polylang-elementor)/#'; // la guía la sirve el tema
	global $wp_styles, $wp_scripts;
	foreach ( array( $wp_styles, $wp_scripts ) as $reg ) {
		if ( ! $reg ) continue;
		foreach ( (array) $reg->queue as $h ) {
			$src = isset( $reg->registered[ $h ] ) ? (string) $reg->registered[ $h ]->src : '';
			if ( preg_match( $pat, $src ) || false !== strpos( $src, '/uploads/elementor/' ) || preg_match( '#^(elementor|e-|swiper|font-awesome|elementor-gf|eael|uc_|rs-|revmin|tp-tools|cpel-|base-(desktop|tablet|mobile)$|(global|local)-\d+-frontend)#', $h ) ) {
				if ( $reg === $wp_styles ) wp_dequeue_style( $h ); else wp_dequeue_script( $h );
			}
		}
	}
}
add_action( 'wp_print_styles', 'hv_strip_builder_assets', 100 );
add_action( 'wp_print_scripts', 'hv_strip_builder_assets', 100 );
add_action( 'wp_print_footer_scripts', 'hv_strip_builder_assets', 1 );

// Emojis y extras del encabezado que no aportan.
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	// Estilos globales de bloques: el tema no los usa (se dejan en las páginas hechas con Elementor/bloques sin clave Haven).
	add_action( 'wp', function () {
		if ( hv_template_key() && ! hv_is_builder_page() && ! ( 'page' === hv_template_key() || 'post' === hv_template_key() && ! hv_key() ) ) {
			remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
			remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
			remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
		}
	} );
} );

/** Encabezado: conexiones previas, fuentes sin bloquear y precarga de la foto principal. */
add_action( 'wp_head', function () {
	$fonts = 'https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap';
	echo '<meta name="theme-color" content="#191311">' . "\n";
	if ( ! hv_vendor_local() ) echo '<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>' . "\n" . '<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>' . "\n";
	$local = WP_CONTENT_DIR . '/uploads/hv-fonts/fonts.css';
	if ( file_exists( $local ) ) {
		// Fuentes servidas desde el propio dominio (tools/fetch-fonts.php): sin conexiones a Google y con precarga de las principales.
		$base = content_url( 'uploads/hv-fonts' );
		foreach ( (array) json_decode( (string) @file_get_contents( WP_CONTENT_DIR . '/uploads/hv-fonts/preload.json' ), true ) as $pf ) {
			echo '<link rel="preload" as="font" type="font/woff2" href="' . esc_url( $base . '/' . $pf ) . '" crossorigin>' . "\n";
		}
		// Portada: el título lleva una palabra en cursiva; se precarga esa fuente para que no cambie de tamaño al llegar.
		if ( is_front_page() ) {
			foreach ( (array) glob( WP_CONTENT_DIR . '/uploads/hv-fonts/playfair-display-italic-latin-*.woff2' ) as $it ) {
				if ( false !== strpos( basename( $it ), 'latin-ext' ) ) continue;
				echo '<link rel="preload" as="font" type="font/woff2" href="' . esc_url( $base . '/' . basename( $it ) ) . '" crossorigin>' . "\n";
			}
		}
		echo '<style id="hv-fonts">' . str_replace( 'FONTDIR', esc_url( $base ), (string) file_get_contents( $local ) ) . '</style>' . "\n";
	} else {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
		echo '<link rel="preload" as="style" href="' . esc_url( $fonts ) . '">' . "\n" . '<link rel="stylesheet" href="' . esc_url( $fonts ) . '" media="print" onload="this.media=\'all\'"><noscript><link rel="stylesheet" href="' . esc_url( $fonts ) . '"></noscript>' . "\n";
	}
	if ( is_front_page() ) {
		echo '<link rel="preload" as="image" href="' . esc_url( hv_asset( 'media/haven-hero.webp' ) ) . '" media="(min-width: 900px)" fetchpriority="high">' . "\n";
		echo '<link rel="preload" as="image" href="' . esc_url( hv_asset( 'media/haven-hero-m.webp' ) ) . '" media="(max-width: 899px)" fetchpriority="high">' . "\n";
		if ( ! hv_lite() ) {
		// El reloj 3D de la apertura. Primero el mapa de importación (tiene que ir antes de cualquier módulo o precarga) y luego las precargas.
		$three = hv_vendor( 'three/three.module.min.js', 'https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.min.js' );
		$room  = hv_vendor( 'three/addons/environments/RoomEnvironment.js', 'https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/environments/RoomEnvironment.js' );
		$addons = preg_replace( '#environments/RoomEnvironment\.js.*$#', '', $room );
		// El mapa debe apuntar a la misma URL exacta que la precarga (con ?v=) para que el navegador reutilice la descarga.
		echo '<script type="importmap">' . wp_json_encode( array( 'imports' => array( 'three' => $three, 'three/addons/' => $addons, 'three/addons/environments/RoomEnvironment.js' => $room ) ), JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
		// Precarga del 3D solo en pantallas grandes: en celular compite con lo primero que se ve; ahí se pide justo después del primer cuadro.
		echo '<script>if(innerWidth>820){' . wp_json_encode( array( $three, $room ), JSON_UNESCAPED_SLASHES ) . '.forEach(function(u){var l=document.createElement("link");l.rel="modulepreload";l.href=u;l.crossOrigin="anonymous";document.head.appendChild(l);});}</script>' . "\n";
		}
	}
}, 2 );
add_action( 'wp_head', function () { echo '<script>document.documentElement.classList.add("hv-js");</script>' . "\n"; }, 1 );

/** Clase del cuerpo según la plantilla (page-home, page-room, page-guia…), como en el prototipo. */
add_filter( 'body_class', function ( $c ) {
	$k = hv_template_key();
	if ( $k ) $c[] = 'page-' . $k;
	$c[] = 'no-js';
	return $c;
} );

/** Nombres de marca en todo el HTML del tema. */
add_action( 'template_redirect', function () {
	if ( hv_is_haven_view() && hv_template_key() ) ob_start( 'hv_brand' );
}, 1 );

/** WP Rocket: la apertura del reloj y las animaciones no deben esperar a la interacción del usuario. */
add_filter( 'rocket_delay_js_exclusions', function ( $ex ) {
	return array_merge( (array) $ex, array( 'haven.js', 'clock3d.js', 'gsap', 'ScrollTrigger', 'lenis', 'three', 'hv-intro', '__introEnd', 'haven-guia', 'hg-' ) );
} );
add_filter( 'rocket_exclude_defer_js', function ( $ex ) { return array_merge( (array) $ex, array( 'clock3d.js' ) ); } );

/** Joinchat (WhatsApp): por encima de la barra inferior del celular. */
add_action( 'wp_head', function () {
	echo '<style>@media (max-width:820px){.joinchat{--bottom:96px!important;bottom:96px!important}}body.admin-bar .site-header{top:calc(46px + env(safe-area-inset-top,0px))}@media (min-width:783px){body.admin-bar .site-header{top:46px}}</style>' . "\n";
}, 50 );

/** Tamaños de imagen razonables para el diseño. */
add_filter( 'wp_calculate_image_sizes', function ( $sizes ) { return $sizes; } );
add_filter( 'big_image_size_threshold', function () { return 2400; } );

/**
 * Quita los ganchos de un plugin en una acción (por la ruta del archivo que los define).
 * Sirve también para funciones anónimas, que no se pueden quitar con remove_action().
 */
function hv_remove_hooks_from( $hook, $path_part ) {
	global $wp_filter;
	if ( empty( $wp_filter[ $hook ] ) ) return;
	foreach ( $wp_filter[ $hook ]->callbacks as $prio => $cbs ) {
		foreach ( $cbs as $id => $cb ) {
			$fn = $cb['function'];
			try {
				if ( $fn instanceof Closure ) $r = new ReflectionFunction( $fn );
				elseif ( is_array( $fn ) ) $r = new ReflectionMethod( is_object( $fn[0] ) ? get_class( $fn[0] ) : $fn[0], $fn[1] );
				elseif ( is_string( $fn ) && function_exists( $fn ) ) $r = new ReflectionFunction( $fn );
				else continue;
			} catch ( ReflectionException $e ) { continue; }
			if ( false !== strpos( str_replace( '\\', '/', (string) $r->getFileName() ), $path_part ) ) {
				$wp_filter[ $hook ]->remove_filter( $hook, $fn, $prio );
			}
		}
	}
}

/**
 * En las páginas con diseño Haven, Elementor Pro no debe poner su encabezado, pie, ventanas emergentes (popups)
 * ni sus fragmentos de código: el tema trae los suyos. Las páginas hechas con Elementor (sin clave Haven) no se tocan.
 */
function hv_unhook_elementor_pro() {
	if ( ! hv_template_key() || hv_is_builder_page() ) return;
	foreach ( array( 'get_header', 'get_footer' ) as $h ) hv_remove_hooks_from( $h, '/elementor-pro/' );
	foreach ( array( 'wp_head', 'wp_body_open', 'wp_footer', 'wp_print_footer_scripts' ) as $h ) {
		// Scripts sueltos de constructores que estas páginas no usan (Slider Revolution, carga diferida de Elementor, Unlimited Elements).
		hv_remove_hooks_from( $h, '/plugins/revslider/' );
		hv_remove_hooks_from( $h, '/plugins/elementor/' );
		hv_remove_hooks_from( $h, '/plugins/unlimited-elements' );
		hv_remove_hooks_from( $h, '/elementor-pro/modules/custom-code/' );
		hv_remove_hooks_from( $h, '/elementor-pro/modules/popup/' );
		hv_remove_hooks_from( $h, '/elementor-pro/modules/theme-builder/' );
	}
	add_filter( 'elementor/theme/need_override_location', '__return_false' );
}
// Elementor Pro engancha su encabezado/pie al elegir la plantilla, así que se quita en ambos momentos.
add_action( 'template_redirect', 'hv_unhook_elementor_pro', 5 );
add_filter( 'template_include', function ( $t ) { hv_unhook_elementor_pro(); return $t; }, 100 );
// Slider Revolution y otros enganchan sus scripts al cargar los recursos, así que se repite después de eso.
add_action( 'wp_enqueue_scripts', 'hv_unhook_elementor_pro', 9999 );

/** Limpia de la dirección los parámetros _gl/_ga de Google (antes era un fragmento de Elementor). */
add_action( 'wp_footer', function () {
	if ( ! hv_template_key() || hv_is_builder_page() ) return;
	echo "<script>addEventListener('load',function(){setTimeout(function(){try{var u=new URL(location.href),c=0;['_gl','_ga'].forEach(function(p){if(u.searchParams.has(p)){u.searchParams.delete(p);c=1}});if(c)history.replaceState(history.state,'',u.pathname+u.search+u.hash)}catch(e){}},2000)});</script>\n";
}, 99 );

/**
 * WP Rocket: en las páginas Haven se mantiene la caché de página, pero no sus optimizaciones de CSS/JS
 * (quitar CSS "sin usar" borraría estilos que el tema activa con JavaScript, y retrasar JS frenaría la apertura).
 * El tema ya entrega su CSS y JS livianos y diferidos.
 */
add_action( 'template_redirect', function () {
	if ( hv_template_key() && ! hv_is_builder_page() && ! defined( 'DONOTROCKETOPTIMIZE' ) ) define( 'DONOTROCKETOPTIMIZE', true );
}, 1 );

/** jQuery solo lo usa el píxel (PixelYourSite, que ya va diferido): en las páginas Haven se carga diferido para no bloquear la primera pintura. */
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	if ( in_array( $handle, array( 'jquery-core', 'jquery-migrate' ), true ) && ! is_admin() && hv_template_key() && ! hv_is_builder_page() && false === strpos( $tag, ' defer' ) ) {
		$tag = str_replace( '<script ', '<script defer ', $tag );
	}
	return $tag;
}, 10, 2 );

/**
 * Celulares: versión liviana de la portada, sin la apertura ni el reloj 3D (en un celular promedio la apertura 3D tardaba unos 9 s).
 * WP Rocket guarda una caché aparte para celulares, así que esto no se mezcla con la versión de escritorio.
 */
function hv_lite() {
	static $l = null;
	if ( null === $l ) $l = (bool) apply_filters( 'hv_lite', false );
	return $l;
}
add_filter( 'body_class', function ( $c ) { if ( hv_lite() ) $c[] = 'hv-lite'; return $c; } );
