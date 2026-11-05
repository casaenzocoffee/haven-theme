<?php
/**
 * Swing Coffee Bar -> Swing Lounge (5 nov 2026, pedido del gerente).
 * Uso: wp eval-file swing-lounge.php [aplicar]
 * Sin "aplicar" solo muestra lo que cambiaría. Antes de cambiar guarda una copia JSON en ~/hv-backups/.
 */
$apply = in_array( 'aplicar', (array) ( $args ?? array() ), true );
$T = array(
	'es' => array(
		'h1'    => 'Swing <em>Lounge</em>',
		'sub'   => 'Uno de los mejores cafés de Antioquia, servido en el vestíbulo de Haven.',
		'crumb' => 'Swing Lounge',
		'wa'    => 'Hola, quiero reservar en el Swing Lounge',
		'title' => 'Swing Lounge: uno de los mejores cafés de Antioquia | Haven Medellín',
		'desc'  => 'Swing Lounge, en el vestíbulo de Haven en El Poblado: café de especialidad de Antioquia preparado por nuestros baristas, para empezar el día o hacer una pausa en Provenza.',
	),
	'en' => array(
		'h1'    => 'Swing <em>Lounge</em>',
		'sub'   => "One of Antioquia's finest coffees, served in the Haven lobby.",
		'crumb' => 'Swing Lounge',
		'wa'    => 'Hello, I would like to book at Swing Lounge',
		'title' => "Swing Lounge: one of Antioquia's finest coffees | Haven Medellín",
		'desc'  => 'Swing Lounge, in the Haven lobby in El Poblado: specialty coffee from Antioquia prepared by our baristas, to start your day or take a break in Provenza.',
	),
);
$ids = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '', 'meta_key' => '_hv_key', 'meta_value' => 'swing' ) );
$backup = array();
foreach ( $ids as $id ) {
	$lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $id ) : 'es';
	if ( ! isset( $T[ $lang ] ) ) { WP_CLI::log( "#$id idioma $lang: se omite" ); continue; }
	$t = $T[ $lang ];
	$raw = get_post_meta( $id, '_hv_data', true );
	$d = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
	if ( ! is_array( $d ) ) { WP_CLI::warning( "#$id sin _hv_data legible" ); continue; }
	$backup[ $id ] = array( 'lang' => $lang, '_hv_data' => $raw, '_yoast_wpseo_title' => get_post_meta( $id, '_yoast_wpseo_title', true ), '_yoast_wpseo_metadesc' => get_post_meta( $id, '_yoast_wpseo_metadesc', true ), '_hv_seo' => get_post_meta( $id, '_hv_seo', true ) );
	foreach ( array( 'h1', 'sub', 'crumb', 'wa' ) as $k ) {
		WP_CLI::log( "#$id [$lang] $k: " . ( $d[ $k ] ?? '(vacío)' ) . '  ->  ' . $t[ $k ] );
		$d[ $k ] = $t[ $k ];
	}
	WP_CLI::log( "#$id [$lang] yoast title: " . $backup[ $id ]['_yoast_wpseo_title'] . '  ->  ' . $t['title'] );
	WP_CLI::log( "#$id [$lang] yoast desc: " . $backup[ $id ]['_yoast_wpseo_metadesc'] . '  ->  ' . $t['desc'] );
	if ( $apply ) {
		update_post_meta( $id, '_hv_data_bak', $raw );
		update_post_meta( $id, '_hv_data', is_array( $raw ) ? $d : wp_slash( wp_json_encode( $d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
		update_post_meta( $id, '_yoast_wpseo_title', $t['title'] );
		update_post_meta( $id, '_yoast_wpseo_metadesc', $t['desc'] );
		$seo = json_decode( (string) $backup[ $id ]['_hv_seo'], true );
		if ( is_array( $seo ) ) { $seo['title'] = $t['title']; $seo['desc'] = $t['desc']; update_post_meta( $id, '_hv_seo', wp_slash( wp_json_encode( $seo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) ); }
	}
}
if ( $apply && $backup ) {
	$f = getenv( 'HOME' ) . '/hv-backups/swing-lounge-antes-' . gmdate( 'Ymd-His' ) . '.json';
	@mkdir( dirname( $f ), 0755, true );
	file_put_contents( $f, wp_json_encode( $backup, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
	WP_CLI::success( 'Aplicado en ' . count( $backup ) . " páginas. Copia: $f" );
} else {
	WP_CLI::log( 'Páginas encontradas: ' . count( $ids ) . ( $apply ? '' : ' (solo prueba; agrega "aplicar" para guardar)' ) );
}
