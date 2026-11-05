<?php
/**
 * Políticas de Haven unificadas con Viktory (5 nov 2026): aplica politicas-viktory.json a las páginas
 * terminos-y-condiciones y politica-de-visitantes (ES y EN) en la base de datos.
 * Uso: wp eval-file politicas-viktory.php [aplicar]   (sin "aplicar" solo muestra qué encontraría)
 * Antes de cambiar guarda copia JSON en ~/hv-backups/ y el contenido anterior en _hv_data_bak.
 */
$apply = in_array( 'aplicar', (array) ( $args ?? array() ), true );
$C = json_decode( file_get_contents( __DIR__ . '/' . ( getenv( 'HV_POL' ) ?: 'politicas-viktory.json' ) ), true );
$backup = array(); $total = 0; $fallas = 0;
foreach ( array( 'terminos-y-condiciones', 'politica-de-visitantes' ) as $key ) {
	$ids = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '', 'meta_key' => '_hv_key', 'meta_value' => $key ) );
	foreach ( $ids as $id ) {
		$lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $id ) : 'es';
		if ( empty( $C[ $lang ][ $key ] ) ) { WP_CLI::log( "#$id $key [$lang]: sin cambios definidos" ); continue; }
		$raw = get_post_meta( $id, '_hv_data', true );
		$d = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
		if ( ! is_array( $d ) || ! isset( $d['prose'] ) ) { WP_CLI::warning( "#$id sin prose" ); $fallas++; continue; }
		$s = $d['prose'];
		foreach ( $C[ $lang ][ $key ] as $p ) {
			list( $a, $b ) = $p;
			$n = substr_count( $s, $a );
			if ( 1 === $n ) { $s = str_replace( $a, $b, $s ); $total++; $st = 'OK'; }
			elseif ( false !== strpos( $s, $b ) ) $st = 'ya estaba';
			else { $st = "NO ENCONTRADO (x$n)"; $fallas++; }
			WP_CLI::log( "#$id $key [$lang] $st: " . mb_substr( wp_strip_all_tags( $a ), 0, 70 ) );
		}
		if ( $s !== $d['prose'] ) {
			$backup[ $id ] = array( 'key' => $key, 'lang' => $lang, '_hv_data' => $raw );
			if ( $apply ) {
				$d['prose'] = $s;
				update_post_meta( $id, '_hv_data_bak', $raw );
				update_post_meta( $id, '_hv_data', is_array( $raw ) ? $d : wp_slash( wp_json_encode( $d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
			}
		}
	}
}
if ( $apply && $backup ) {
	$f = getenv( 'HOME' ) . '/hv-backups/politicas-antes-' . gmdate( 'Ymd-His' ) . '.json';
	@mkdir( dirname( $f ), 0755, true );
	file_put_contents( $f, wp_json_encode( $backup, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
	WP_CLI::success( "Aplicado: $total cambios en " . count( $backup ) . " páginas, $fallas sin encontrar. Copia: $f" );
} else {
	WP_CLI::log( "Prueba: $total cambios posibles, $fallas sin encontrar" . ( $apply ? '' : ' (agrega "aplicar" para guardar)' ) );
}
