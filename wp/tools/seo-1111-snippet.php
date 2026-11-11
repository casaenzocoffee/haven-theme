
/* HV-TEMP-1111 (temporal: se quita después de usarlo) */
add_action( 'wp_ajax_hv_seo_1111', function () {
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['n'] ?? '', 'wp_rest' ) ) wp_send_json_error( 'no', 403 );
	$ops = json_decode( wp_unslash( $_POST['d'] ?? '' ), true );
	if ( ! is_array( $ops ) ) wp_send_json_error( 'json', 400 );
	$bak = get_option( 'hv_bak_1111', array() ); $out = array();
	$find = function ( $o ) {
		if ( ! empty( $o['path'] ) ) return url_to_postid( home_url( $o['path'] ) );
		foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '', 'meta_key' => '_hv_key', 'meta_value' => $o['key'] ) ) as $p )
			if ( ! function_exists( 'pll_get_post_language' ) || pll_get_post_language( $p ) === $o['lang'] ) return (int) $p;
		return 0;
	};
	foreach ( $ops as $o ) {
		$id = $find( $o );
		if ( ! $id ) { $out[] = array( 'err' => 'sin id', 'o' => $o['key'] ?? $o['path'] ); continue; }
		$raw = get_post_meta( $id, '_hv_data', true ); $d = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
		if ( 'get' === $o['op'] ) { $out[] = array( 'id' => $id, 'url' => get_permalink( $id ), 't' => get_post_meta( $id, '_yoast_wpseo_title', true ), 'd' => get_post_meta( $id, '_yoast_wpseo_metadesc', true ), 'data' => $d ); continue; }
		if ( empty( $o['apply'] ) ) { $out[] = array( 'id' => $id, 'dry' => $o['op'] ); continue; }
		if ( ! isset( $bak[ $id ] ) ) $bak[ $id ] = array( 't' => get_post_meta( $id, '_yoast_wpseo_title', true ), 'd' => get_post_meta( $id, '_yoast_wpseo_metadesc', true ), 'data' => $raw );
		if ( 'yoast' === $o['op'] ) {
			if ( ! empty( $o['t'] ) ) update_post_meta( $id, '_yoast_wpseo_title', wp_slash( $o['t'] ) );
			if ( ! empty( $o['d'] ) ) update_post_meta( $id, '_yoast_wpseo_metadesc', wp_slash( $o['d'] ) );
		} elseif ( 'more' === $o['op'] || 'insert' === $o['op'] ) {
			if ( ! is_array( $d ) || ! $d ) { $out[] = array( 'id' => $id, 'err' => 'sin _hv_data' ); continue; }
			if ( 'more' === $o['op'] ) $d['more'] = $o['blocks'];
			else {
				$titles = array_map( function ( $b ) { return $b['title'] ?? ''; }, (array) ( $d['blocks'] ?? array() ) );
				if ( in_array( $o['blocks'][0]['title'], $titles, true ) ) { $out[] = array( 'id' => $id, 'skip' => 'ya estaba' ); continue; }
				array_splice( $d['blocks'], (int) $o['at'], 0, $o['blocks'] );
			}
			update_post_meta( $id, '_hv_data_bak', is_array( $raw ) ? $raw : wp_slash( $raw ) );
			update_post_meta( $id, '_hv_data', wp_slash( wp_json_encode( $d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
		}
		clean_post_cache( $id ); if ( function_exists( 'rocket_clean_post' ) ) rocket_clean_post( $id );
		$out[] = array( 'id' => $id, 'ok' => $o['op'] );
	}
	update_option( 'hv_bak_1111', $bak, false );
	wp_send_json_success( $out );
} );
/* /HV-TEMP-1111 */
