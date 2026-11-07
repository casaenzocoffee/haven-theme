
/* HV-TEMP-0711 (temporal: se quita después de usarlo) */
add_action( 'wp_ajax_hv_seo_0711', function () {
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['n'] ?? '', 'wp_rest' ) ) wp_send_json_error( 'no', 403 );
	$in = json_decode( wp_unslash( $_POST['d'] ?? '' ), true );
	if ( ! is_array( $in ) ) wp_send_json_error( 'json', 400 );
	$apply = ! empty( $in['apply'] ); $log = array(); $bak = get_option( 'hv_bak_0711', array() );
	$lang_of = function ( $id ) { return function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $id ) : 'es'; };
	$keep = function ( $id ) use ( &$bak ) {
		if ( isset( $bak[ $id ] ) ) return;
		$bak[ $id ] = array( 't' => get_post_meta( $id, '_yoast_wpseo_title', true ), 'd' => get_post_meta( $id, '_yoast_wpseo_metadesc', true ), 'seo' => get_post_meta( $id, '_hv_seo', true ), 'data' => get_post_meta( $id, '_hv_data', true ) );
	};
	foreach ( (array) ( $in['seo'] ?? array() ) as $path => $td ) {
		if ( in_array( $path, array( '/es/', '/en/' ), true ) ) { $f = (int) get_option( 'page_on_front' ); $id = function_exists( 'pll_get_post' ) ? (int) pll_get_post( $f, trim( $path, '/' ) ) : $f; }
		else $id = url_to_postid( home_url( $path ) );
		if ( ! $id ) { $log[] = "! $path sin id"; continue; }
		list( $t, $d ) = $td;
		$log[] = "#$id $path | " . ( $t ? 'T: ' . get_post_meta( $id, '_yoast_wpseo_title', true ) . ' -> ' . $t : '' ) . ( $d ? ' | D' : '' );
		if ( ! $apply ) continue;
		$keep( $id );
		if ( $t ) update_post_meta( $id, '_yoast_wpseo_title', wp_slash( $t ) );
		if ( $d ) update_post_meta( $id, '_yoast_wpseo_metadesc', wp_slash( $d ) );
		$s = json_decode( (string) get_post_meta( $id, '_hv_seo', true ), true );
		if ( is_array( $s ) ) { if ( $t ) $s['title'] = $t; if ( $d ) $s['description'] = $d; update_post_meta( $id, '_hv_seo', wp_slash( wp_json_encode( $s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) ); }
		clean_post_cache( $id ); if ( function_exists( 'rocket_clean_post' ) ) rocket_clean_post( $id );
	}
	foreach ( (array) ( $in['rooms'] ?? array() ) as $key => $pl ) {
		foreach ( $pl as $lang => $para ) {
			$id = 0;
			foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '', 'meta_key' => '_hv_key', 'meta_value' => $key ) ) as $p ) if ( $lang_of( $p ) === $lang ) $id = (int) $p;
			if ( ! $id ) { $log[] = "! $key [$lang] sin id"; continue; }
			$raw = get_post_meta( $id, '_hv_data', true ); $d = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
			if ( ! is_array( $d ) || empty( $d['desc'] ) ) { $log[] = "! $key [$lang] #$id sin desc"; continue; }
			if ( in_array( $para, (array) $d['desc'], true ) ) { $log[] = "= $key [$lang] #$id ya estaba"; continue; }
			$log[] = "+ $key [$lang] #$id: " . count( (array) $d['desc'] ) . ' párrafos -> ' . ( count( (array) $d['desc'] ) + 1 );
			if ( ! $apply ) continue;
			$keep( $id );
			$d['desc'][] = $para;
			update_post_meta( $id, '_hv_data_bak', is_array( $raw ) ? $raw : wp_slash( $raw ) );
			update_post_meta( $id, '_hv_data', wp_slash( wp_json_encode( $d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
			clean_post_cache( $id ); if ( function_exists( 'rocket_clean_post' ) ) rocket_clean_post( $id );
		}
	}
	foreach ( (array) ( $in['noindex_cat'] ?? array() ) as $slug ) {
		$tm = get_option( 'wpseo_taxonomy_meta', array() );
		foreach ( get_terms( array( 'taxonomy' => 'category', 'slug' => $slug, 'hide_empty' => false, 'lang' => '' ) ) as $term ) {
			$log[] = "noindex categoría {$term->slug} #{$term->term_id} (" . ( $tm['category'][ $term->term_id ]['wpseo_noindex'] ?? 'default' ) . ')';
			if ( ! $apply ) continue;
			$bak[ 'term' . $term->term_id ] = $tm['category'][ $term->term_id ] ?? array();
			$tm['category'][ $term->term_id ]['wpseo_noindex'] = 'noindex';
		}
		if ( $apply ) update_option( 'wpseo_taxonomy_meta', $tm );
	}
	if ( $apply ) { update_option( 'hv_bak_0711', $bak, false ); if ( function_exists( 'rocket_clean_domain' ) ) rocket_clean_domain(); }
	wp_send_json_success( $log );
} );
/* /HV-TEMP-0711 */
