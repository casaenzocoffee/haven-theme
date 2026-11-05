<?php
/**
 * Contenido SEO del 5 nov 2026 (auditoría): páginas por intención, páginas de la guía, fichas de habitación y blog.
 * Uso: wp eval-file seo-content.php [aplicar]     (sin "aplicar" = prueba: muestra qué haría y no cambia nada)
 * Solo toca las páginas de este trabajo. Antes de cambiar guarda todo lo anterior en ~/hv-backups/seo-content-antes-*.json
 * y el contenido Haven anterior de cada página en _hv_data_bak.
 */
$apply = in_array( 'aplicar', (array) ( $args ?? array() ), true );
$dir   = __DIR__ . '/seo-content';
$load  = function ( $f ) use ( $dir ) { $p = "$dir/$f"; return file_exists( $p ) ? json_decode( file_get_contents( $p ), true ) : array(); };
$today = gmdate( 'Y-m-d' );
$bak   = array();
$log   = function ( $s ) { WP_CLI::log( $s ); };
$lang_of = function ( $id ) { return function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $id ) : 'es'; };
$find = function ( $key, $lang, $type = 'page' ) use ( $lang_of ) {
	foreach ( get_posts( array( 'post_type' => $type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '', 'meta_key' => '_hv_key', 'meta_value' => $key ) ) as $id ) if ( $lang_of( $id ) === $lang ) return (int) $id;
	return 0;
};
$get_data = function ( $id ) { $r = get_post_meta( $id, '_hv_data', true ); return array( $r, is_array( $r ) ? $r : (array) json_decode( (string) $r, true ) ); };
$put_data = function ( $id, $d, $raw ) {
	if ( $raw ) update_post_meta( $id, '_hv_data_bak', is_array( $raw ) ? $raw : wp_slash( $raw ) );
	update_post_meta( $id, '_hv_data', wp_slash( wp_json_encode( $d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
};
$yoast = function ( $id, $t, $desc ) {
	if ( $t ) update_post_meta( $id, '_yoast_wpseo_title', wp_slash( $t ) );
	if ( $desc ) update_post_meta( $id, '_yoast_wpseo_metadesc', wp_slash( $desc ) );
};
$remember = function ( $id ) use ( &$bak ) {
	if ( isset( $bak[ $id ] ) ) return;
	$p = get_post( $id );
	$bak[ $id ] = array( 'title' => $p->post_title, 'excerpt' => $p->post_excerpt, 'content' => $p->post_content, '_hv_data' => get_post_meta( $id, '_hv_data', true ),
		'_yoast_wpseo_title' => get_post_meta( $id, '_yoast_wpseo_title', true ), '_yoast_wpseo_metadesc' => get_post_meta( $id, '_yoast_wpseo_metadesc', true ) );
};
$h2ids = function ( $html ) {
	return preg_replace_callback( '#<h2(?![^>]*\bid=)([^>]*)>(.*?)</h2>#s', function ( $m ) { return '<h2' . $m[1] . ' id="' . esc_attr( sanitize_title( wp_strip_all_tags( $m[2] ) ) ) . '">' . $m[2] . '</h2>'; }, $html );
};

/* 1. Páginas por intención y de la guía. Primero las que no tienen padre (el padre debe existir antes). */
$pages = array_merge( (array) $load( 'landings.json' ), (array) $load( 'guide-places.json' ), (array) $load( 'guide-days.json' ) );
$pairs = array();
foreach ( $pages as $key => $pg ) {
	foreach ( array( 'es', 'en' ) as $lang ) {
		$c = $pg[ $lang ] ?? null;
		if ( ! $c || empty( $pg['slug'][ $lang ] ) ) { $log( "! $key [$lang]: sin contenido o slug" ); continue; }
		$id = $find( $key, $lang );
		$parent_id = 0;
		if ( ! empty( $pg['parent'] ) ) {
			$parent_id = (int) $find( $pg['parent'], $lang );
			if ( ! $parent_id ) { $log( "! $key [$lang]: no existe la página padre {$pg['parent']}" ); continue; }
		}
		$new = array( 'h1' => $c['h1'], 'sub' => $c['sub'] ?? '', 'crumb' => $c['crumb'] ?? $c['title'], 'parent' => $pg['parent'] ?? '', 'updated' => $today, 'blocks' => $c['blocks'] );
		if ( ! empty( $c['img'] ) ) $new['img'] = $c['img'];
		if ( ! $id ) {
			$log( "+ $key [$lang]: crear /{$pg['slug'][$lang]}/" . ( $parent_id ? " (hija de #$parent_id)" : '' ) . " · {$c['seo_title']}" );
			if ( ! $apply ) continue;
			$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $pg['slug'][ $lang ], 'post_title' => $c['title'], 'post_parent' => $parent_id, 'post_content' => '' ), true );
			if ( is_wp_error( $id ) ) { WP_CLI::warning( "$key [$lang]: " . $id->get_error_message() ); continue; }
			if ( function_exists( 'pll_set_post_language' ) ) pll_set_post_language( $id, $lang );
			update_post_meta( $id, '_hv_key', $key );
			$bak[ $id ] = array( 'creada' => true );
			$raw = '';
			$data = $new;
		} else {
			$log( "~ $key [$lang]: actualizar #$id · {$c['seo_title']}" );
			if ( ! $apply ) { $pairs[ $key ][ $lang ] = $id; continue; }
			$remember( $id );
			list( $raw, $old ) = $get_data( $id );
			$data = array_merge( $old, $new ); // conserva los campos anteriores (p. ej. cat/f de Penthouse y Suites de lujo)
		}
		$put_data( $id, $data, $raw );
		$yoast( $id, $c['seo_title'] ?? '', $c['seo_desc'] ?? '' );
		$pairs[ $key ][ $lang ] = $id;
	}
}
if ( $apply && function_exists( 'pll_save_post_translations' ) ) {
	foreach ( $pairs as $key => $p ) {
		if ( empty( $p['es'] ) || empty( $p['en'] ) ) continue;
		if ( (int) pll_get_post( $p['es'], 'en' ) === (int) $p['en'] ) continue;
		if ( pll_get_post( $p['es'], 'en' ) || pll_get_post( $p['en'], 'es' ) ) { $log( "! $key: ya tenía otra traducción enlazada (no se tocó)" ); continue; }
		pll_save_post_translations( array( 'es' => $p['es'], 'en' => $p['en'] ) );
	}
}

/* 2. Fichas de habitación: solo la descripción y la meta descripción. */
foreach ( (array) $load( 'rooms.json' ) as $key => $rc ) {
	foreach ( array( 'es', 'en' ) as $lang ) {
		if ( empty( $rc[ $lang ]['desc'] ) ) continue;
		$id = $find( $key, $lang );
		if ( ! $id ) { $log( "! $key [$lang]: no existe" ); continue; }
		$w = str_word_count( wp_strip_all_tags( implode( ' ', $rc[ $lang ]['desc'] ) ), 0, 'áéíóúñüÁÉÍÓÚÑÜ' );
		$log( "~ $key [$lang] #$id: descripción nueva ($w palabras)" );
		if ( ! $apply ) continue;
		$remember( $id );
		list( $raw, $d ) = $get_data( $id );
		$d['desc'] = array_values( $rc[ $lang ]['desc'] );
		$put_data( $id, $d, $raw );
		$yoast( $id, '', $rc[ $lang ]['seo_desc'] ?? '' );
	}
}

/* 3. Blog: artículos existentes (título, extracto, texto) y nuevos. */
$blog = (array) $load( 'blog.json' );
foreach ( (array) ( $blog['existing'] ?? array() ) as $key => $bc ) {
	foreach ( array( 'es', 'en' ) as $lang ) {
		if ( empty( $bc[ $lang ]['prose'] ) ) continue;
		$id = $find( $key, $lang, 'post' );
		if ( ! $id ) { $log( "! $key [$lang]: artículo no encontrado" ); continue; }
		$log( "~ $key [$lang] #$id: {$bc[$lang]['title']}" );
		if ( ! $apply ) continue;
		$remember( $id );
		list( $raw, $d ) = $get_data( $id );
		$d['title'] = $bc[ $lang ]['title']; $d['ex'] = $bc[ $lang ]['ex']; $d['prose'] = $h2ids( $bc[ $lang ]['prose'] );
		$put_data( $id, $d, $raw );
		wp_update_post( array( 'ID' => $id, 'post_title' => $bc[ $lang ]['title'], 'post_excerpt' => $bc[ $lang ]['ex'] ) );
		$yoast( $id, $bc[ $lang ]['seo_title'] ?? '', $bc[ $lang ]['seo_desc'] ?? '' );
	}
}
$author = (int) ( get_posts( array( 'post_type' => 'post', 'numberposts' => 1, 'lang' => '' ) )[0]->post_author ?? 1 );
$newpairs = array();
foreach ( (array) ( $blog['new'] ?? array() ) as $np ) {
	foreach ( array( 'es', 'en' ) as $lang ) {
		$c = $np[ $lang ] ?? null; $slug = 'es' === $lang ? $np['slug_es'] : $np['slug'];
		if ( ! $c ) continue;
		$id = $find( $np['key'], $lang, 'post' );
		$log( ( $id ? "~ ya existe #$id " : '+ crear artículo ' ) . "{$np['key']} [$lang] /$slug/ · {$c['title']}" );
		if ( ! $apply || $id ) { if ( $id ) $newpairs[ $np['key'] ][ $lang ] = $id; continue; }
		// Misma categoría que los artículos existentes de ese idioma.
		$sib = get_posts( array( 'post_type' => 'post', 'numberposts' => 1, 'lang' => $lang, 'post_status' => 'publish' ) );
		$cats = $sib ? wp_get_post_categories( $sib[0]->ID ) : array();
		$id = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $c['title'], 'post_excerpt' => $c['ex'], 'post_content' => '', 'post_author' => $author, 'post_category' => $cats ), true );
		if ( is_wp_error( $id ) ) { WP_CLI::warning( $id->get_error_message() ); continue; }
		if ( function_exists( 'pll_set_post_language' ) ) pll_set_post_language( $id, $lang );
		update_post_meta( $id, '_hv_key', $np['key'] );
		$date = 'en' === $lang ? date_i18n( 'F j, Y' ) : date_i18n( 'j \d\e F \d\e Y' );
		$put_data( $id, array( 'title' => $c['title'], 'ex' => $c['ex'], 'img' => $np['img'], 'prose' => $h2ids( $c['prose'] ), 'date' => $date ), '' );
		$yoast( $id, $c['seo_title'] ?? '', $c['seo_desc'] ?? '' );
		$bak[ $id ] = array( 'creado' => true );
		$newpairs[ $np['key'] ][ $lang ] = $id;
	}
}
if ( $apply && function_exists( 'pll_save_post_translations' ) ) foreach ( $newpairs as $p ) if ( ! empty( $p['es'] ) && ! empty( $p['en'] ) && ! pll_get_post( $p['es'], 'en' ) ) pll_save_post_translations( array( 'es' => $p['es'], 'en' => $p['en'] ) );

if ( $apply ) {
	delete_transient( 'hv_urlmap' );
	$f = getenv( 'HOME' ) . '/hv-backups/seo-content-antes-' . gmdate( 'Ymd-His' ) . '.json';
	@mkdir( dirname( $f ), 0755, true );
	file_put_contents( $f, wp_json_encode( $bak, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
	WP_CLI::success( count( $bak ) . " páginas/artículos tocados. Copia de lo anterior: $f" );
} else {
	WP_CLI::log( 'PRUEBA: no se cambió nada. Agrega "aplicar" para guardar.' );
}
