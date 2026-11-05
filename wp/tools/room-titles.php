<?php
/* Títulos SEO de las fichas (room-titles.json). Uso: wp eval-file room-titles.php [aplicar]. Copia en ~/hv-backups/. */
$apply = in_array( 'aplicar', (array) ( $args ?? array() ), true );
$T = json_decode( file_get_contents( __DIR__ . '/room-titles.json' ), true ); $bak = array(); $n = 0;
foreach ( $T as $key => $tt ) {
	foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '', 'meta_key' => '_hv_key', 'meta_value' => $key ) ) as $id ) {
		$l = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $id ) : 'es';
		if ( empty( $tt[ $l ] ) ) continue;
		$old = get_post_meta( $id, '_yoast_wpseo_title', true );
		$bak[ $id ] = array( 'key' => $key, 'lang' => $l, '_yoast_wpseo_title' => $old, '_hv_seo' => get_post_meta( $id, '_hv_seo', true ) );
		WP_CLI::log( "#$id $key [$l]: $old  ->  {$tt[$l]}" ); $n++;
		if ( $apply ) {
			update_post_meta( $id, '_yoast_wpseo_title', $tt[ $l ] );
			$seo = json_decode( (string) $bak[ $id ]['_hv_seo'], true );
			if ( is_array( $seo ) ) { $seo['title'] = $tt[ $l ]; update_post_meta( $id, '_hv_seo', wp_slash( wp_json_encode( $seo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) ); }
			wp_update_post( array( 'ID' => $id ) );
		}
	}
}
if ( $apply ) { $f = getenv( 'HOME' ) . '/hv-backups/titulos-fichas-antes-' . gmdate( 'Ymd-His' ) . '.json'; file_put_contents( $f, wp_json_encode( $bak, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) ); WP_CLI::success( "$n títulos. Copia: $f" ); }
else WP_CLI::log( "$n títulos (prueba)" );
