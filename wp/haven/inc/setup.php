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

add_action( 'wp_enqueue_scripts', function () {
	$ver = null;
	wp_enqueue_style( 'haven', hv_asset( 'haven.css' ), array(), $ver );
	wp_enqueue_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), null, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_enqueue_script( 'gsap-st', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js', array( 'gsap' ), null, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_enqueue_script( 'lenis', 'https://cdn.jsdelivr.net/npm/lenis@1.1.13/dist/lenis.min.js', array(), null, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_enqueue_script( 'haven', hv_asset( 'haven.js' ), array( 'gsap', 'gsap-st', 'lenis' ), null, array( 'strategy' => 'defer', 'in_footer' => true ) );
	if ( is_front_page() && function_exists( 'wp_enqueue_script_module' ) ) {
		wp_register_script_module( 'three', 'https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.min.js', array(), null );
		wp_register_script_module( 'three/addons/environments/RoomEnvironment.js', 'https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/environments/RoomEnvironment.js', array( 'three' ), null );
		wp_enqueue_script_module( 'haven-clock', hv_asset( 'clock3d.js' ), array( array( 'id' => 'three', 'import' => 'dynamic' ), array( 'id' => 'three/addons/environments/RoomEnvironment.js', 'import' => 'dynamic' ) ), null );
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
	$pat = '#/plugins/(elementor|elementor-pro|revslider|unlimited-elements[^/]*|skyboot-[^/]*|haven-guia[^/]*)/#'; // la guía la sirve el tema
	global $wp_styles, $wp_scripts;
	foreach ( array( $wp_styles, $wp_scripts ) as $reg ) {
		if ( ! $reg ) continue;
		foreach ( (array) $reg->queue as $h ) {
			$src = isset( $reg->registered[ $h ] ) ? (string) $reg->registered[ $h ]->src : '';
			if ( preg_match( $pat, $src ) || preg_match( '#^(elementor|e-|swiper|font-awesome|elementor-gf|eael|uc_|rs-|revmin|tp-tools)#', $h ) ) {
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
	echo '<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>' . "\n" . '<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>' . "\n";
	echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	echo '<link rel="preload" as="style" href="' . esc_url( $fonts ) . '">' . "\n" . '<link rel="stylesheet" href="' . esc_url( $fonts ) . '" media="print" onload="this.media=\'all\'"><noscript><link rel="stylesheet" href="' . esc_url( $fonts ) . '"></noscript>' . "\n";
	if ( is_front_page() ) {
		echo '<link rel="preload" as="image" href="' . esc_url( hv_asset( 'media/haven-hero.webp' ) ) . '" media="(min-width: 900px)" fetchpriority="high">' . "\n";
		echo '<link rel="preload" as="image" href="' . esc_url( hv_asset( 'media/haven-hero-m.webp' ) ) . '" media="(max-width: 899px)" fetchpriority="high">' . "\n";
		// El reloj 3D de la apertura: se descarga en paralelo para que arranque sin pausa.
		echo '<link rel="modulepreload" href="https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.min.js" crossorigin>' . "\n";
		echo '<link rel="modulepreload" href="https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/environments/RoomEnvironment.js" crossorigin>' . "\n";
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
	echo '<style>@media (max-width:820px){.joinchat{--bottom:96px!important;bottom:96px!important}}</style>' . "\n";
}, 50 );

/** Tamaños de imagen razonables para el diseño. */
add_filter( 'wp_calculate_image_sizes', function ( $sizes ) { return $sizes; } );
add_filter( 'big_image_size_threshold', function () { return 2400; } );
