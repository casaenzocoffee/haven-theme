<?php
/*
 * Importador del contenido Haven (idempotente y sin borrar nada):
 *  - Busca cada página/entrada existente por su slug real y su idioma (import/slugs.json).
 *  - Le pone la clave Haven (_hv_key), el orden (_hv_order) y el contenido estructurado (_hv_data) desde import/bundle-{es,en}.json.
 *  - No cambia títulos, contenido, plantilla ni datos de Elementor. Guarda copia de lo que reemplaza (_hv_data_bak, _hv_yoast_bak).
 *  - Si una página falta, la reporta; con --create (o el botón "crear faltantes") la crea publicada.
 * Uso: wp haven import [--dry-run] [--create] [--force] [--seo]   ·   Herramientas → Haven en el panel.
 */
defined( 'ABSPATH' ) || exit;

function hv_import_bundle( $lang ) {
	$f = HAVEN_DIR . '/import/bundle-' . $lang . '.json';
	return file_exists( $f ) ? json_decode( file_get_contents( $f ), true ) : null;
}

/** Idioma de una entrada ('' si Polylang no está o no tiene idioma). */
function hv_post_lang( $id ) {
	return function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $id ) : '';
}

/**
 * Busca la página/entrada de una clave en un idioma: primero por su clave Haven (para que repetir la importación no duplique),
 * luego por slug comprobando el idioma a mano (funciona con slugs compartidos entre idiomas y sin Polylang).
 */
function hv_find_post( $slug, $lang, $type = 'page', $key = '' ) {
	$st = array( 'publish', 'draft', 'private', 'pending', 'future' );
	$ok = function ( $id ) use ( $lang ) {
		$l = hv_post_lang( $id );
		return function_exists( 'pll_get_post_language' ) ? ( $l === $lang || ( '' === $l && 'es' === $lang ) ) : 'es' === $lang;
	};
	if ( $key ) {
		$ids = get_posts( array( 'post_type' => $type, 'post_status' => $st, 'posts_per_page' => 20, 'fields' => 'ids', 'no_found_rows' => true, 'lang' => '', 'meta_key' => '_hv_key', 'meta_value' => $key ) );
		foreach ( $ids as $id ) if ( $ok( $id ) ) return (int) $id;
	}
	if ( '' === $slug ) {
		$front = (int) get_option( 'page_on_front' );
		if ( ! $front ) return 0;
		if ( function_exists( 'pll_get_post' ) ) { $t = (int) pll_get_post( $front, $lang ); return $t ?: ( $ok( $front ) ? $front : 0 ); }
		return 'es' === $lang ? $front : 0;
	}
	$ids = get_posts( array( 'name' => $slug, 'post_type' => $type, 'post_status' => $st, 'posts_per_page' => 20, 'fields' => 'ids', 'no_found_rows' => true, 'lang' => '' ) );
	foreach ( $ids as $id ) if ( $ok( $id ) ) return (int) $id;
	return 0;
}

/** Guarda una meta JSON (con barras protegidas para que WordPress no las quite). */
function hv_put_json( $id, $key, $data ) {
	update_post_meta( $id, $key, wp_slash( wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
}

/**
 * Ejecuta la importación. $o: dry (bool), create (bool), force (bool), seo (bool).
 * Devuelve filas [idioma, clave, slug, id, acción].
 */
function hv_run_import( $o = array() ) {
	$o     = array_merge( array( 'dry' => false, 'create' => false, 'force' => false, 'seo' => false ), $o );
	$slugs = json_decode( (string) @file_get_contents( HAVEN_DIR . '/import/slugs.json' ), true ) ?: array();
	$rows  = array();
	$pairs = array();
	foreach ( array( 'es', 'en' ) as $lang ) {
		$b = hv_import_bundle( $lang );
		if ( ! $b ) { $rows[] = array( $lang, '-', '-', 0, 'sin bundle' ); continue; }
		$items = array();
		foreach ( $slugs as $key => $s ) {
			if ( 0 === strpos( $key, 'room-' ) || 0 === strpos( $key, 'post-' ) ) continue;
			$items[ $key ] = array( 'type' => 'page', 'data' => $b['pages'][ $key ] ?? array() );
		}
		foreach ( $b['rooms'] as $i => $r ) $items[ $r['key'] ] = array( 'type' => 'page', 'data' => $r, 'order' => $i + 1, 'title' => $r['name'] );
		foreach ( $b['posts'] as $p ) { $pd = $p; $items[ $p['key'] ] = array( 'type' => 'post', 'data' => $pd, 'title' => $p['title'] ); }

		foreach ( $items as $key => $it ) {
			$slug = $slugs[ $key ][ $lang ] ?? null;
			if ( null === $slug ) { $rows[] = array( $lang, $key, '-', 0, 'sin slug' ); continue; }
			$id  = hv_find_post( $slug, $lang, $it['type'], $key );
			// Algunos sitios tienen un slug distinto (p. ej. suite-presidential en vez de suite-presidencial): se prueban los alternativos del contenido.
			foreach ( array_unique( array_filter( array( $it['data']['slug_es'] ?? '', $it['data']['slug'] ?? '' ) ) ) as $alt ) {
				if ( $id || $alt === $slug ) continue;
				$id = hv_find_post( $alt, $lang, $it['type'] );
				if ( $id ) $slug = $alt;
			}
			$act = array();
			if ( ! $id ) {
				if ( ! $o['create'] || '' === $slug ) { $rows[] = array( $lang, $key, $slug, 0, 'FALTA' ); continue; }
				if ( $o['dry'] ) { $rows[] = array( $lang, $key, $slug, 0, 'se crearía' ); continue; }
				$id = wp_insert_post( array( 'post_type' => $it['type'], 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $it['title'] ?? $b['titles'][ $key ] ?? wp_strip_all_tags( $it['data']['title_html'] ?? ( $it['data']['h1'] ?? ucfirst( str_replace( '-', ' ', $slug ) ) ) ), 'post_content' => '' ), true );
				if ( is_wp_error( $id ) ) { $rows[] = array( $lang, $key, $slug, 0, 'error: ' . $id->get_error_message() ); continue; }
				if ( function_exists( 'pll_set_post_language' ) ) pll_set_post_language( $id, $lang );
				if ( ! empty( $it['data']['published'] ) ) wp_update_post( array( 'ID' => $id, 'post_date' => $it['data']['published'] ) );
				$act[] = 'creada';
			}
			$pairs[ $key ][ $lang ] = $id;
			if ( $o['dry'] ) { $rows[] = array( $lang, $key, $slug, $id, 'ok (prueba)' ); continue; }

			if ( get_post_meta( $id, '_hv_key', true ) !== $key ) { update_post_meta( $id, '_hv_key', $key ); $act[] = 'clave'; }
			if ( isset( $it['order'] ) ) update_post_meta( $id, '_hv_order', (int) $it['order'] );
			$data = $it['data'];
			unset( $data['key'] );
			if ( $data && 'home' !== $key ) {
				$old = get_post_meta( $id, '_hv_data', true );
				if ( ! $old || $o['force'] ) {
					if ( $old ) update_post_meta( $id, '_hv_data_bak', wp_slash( $old ) );
					hv_put_json( $id, '_hv_data', $data );
					$act[] = $old ? 'contenido (reemplazado, copia en _hv_data_bak)' : 'contenido';
				} else {
					$act[] = 'contenido ya existía (sin cambios)';
				}
			}
			$meta = $b['meta'][ $key ] ?? null;
			if ( $meta ) {
				hv_put_json( $id, '_hv_seo', $meta );
				if ( $o['seo'] ) {
					$yt = get_post_meta( $id, '_yoast_wpseo_title', true );
					$yd = get_post_meta( $id, '_yoast_wpseo_metadesc', true );
					if ( ( $yt || $yd ) && ! get_post_meta( $id, '_hv_yoast_bak', true ) ) hv_put_json( $id, '_hv_yoast_bak', array( 'title' => $yt, 'desc' => $yd ) );
					update_post_meta( $id, '_yoast_wpseo_title', wp_slash( $meta['title'] ) );
					update_post_meta( $id, '_yoast_wpseo_metadesc', wp_slash( $meta['desc'] ) );
					$act[] = 'yoast';
				}
			}
			$rows[] = array( $lang, $key, $slug, $id, implode( ', ', $act ) ?: 'sin cambios' );
		}
	}
	// Traducciones de Polylang (solo enlaza pares que aún no estén enlazados).
	if ( ! $o['dry'] && function_exists( 'pll_save_post_translations' ) ) {
		foreach ( $pairs as $key => $p ) {
			if ( empty( $p['es'] ) || empty( $p['en'] ) ) continue;
			if ( (int) pll_get_post( $p['es'], 'en' ) === (int) $p['en'] ) continue;
			if ( pll_get_post( $p['es'], 'en' ) || pll_get_post( $p['en'], 'es' ) ) { $rows[] = array( '-', $key, '-', 0, 'traducción distinta ya enlazada (no se tocó)' ); continue; }
			pll_save_post_translations( array( 'es' => $p['es'], 'en' => $p['en'] ) );
			$rows[] = array( '-', $key, '-', 0, 'traducciones enlazadas' );
		}
	}
	delete_option( 'hv_att_map' );
	return $rows;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'haven import', function ( $args, $assoc ) {
		$rows = hv_run_import( array( 'dry' => isset( $assoc['dry-run'] ), 'create' => isset( $assoc['create'] ), 'force' => isset( $assoc['force'] ), 'seo' => isset( $assoc['seo'] ) ) );
		WP_CLI\Utils\format_items( 'table', array_map( function ( $r ) { return array_combine( array( 'lang', 'key', 'slug', 'id', 'action' ), $r ); }, $rows ), array( 'lang', 'key', 'slug', 'id', 'action' ) );
	} );
}

/* Herramientas → Haven: botón para importar (prueba / real) y ver el resultado. */
add_action( 'admin_menu', function () {
	add_management_page( 'Haven', 'Haven (contenido)', 'manage_options', 'haven-import', 'hv_import_screen' );
} );
function hv_import_screen() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$rows = null;
	if ( isset( $_POST['hv_import'] ) && check_admin_referer( 'hv_import' ) ) {
		$rows = hv_run_import( array( 'dry' => ! empty( $_POST['dry'] ), 'create' => ! empty( $_POST['create'] ), 'force' => ! empty( $_POST['force'] ), 'seo' => ! empty( $_POST['seo'] ) ) );
	}
	echo '<div class="wrap"><h1>Haven · contenido</h1><p>Enlaza las páginas existentes con el diseño Haven y carga su contenido. No borra ni cambia títulos, textos ni Elementor. Haz primero una prueba.</p><form method="post">';
	wp_nonce_field( 'hv_import' );
	echo '<p><label><input type="checkbox" name="dry" value="1" checked> Solo prueba (no guarda nada)</label><br><label><input type="checkbox" name="create" value="1"> Crear páginas que falten</label><br><label><input type="checkbox" name="force" value="1"> Reemplazar contenido Haven ya cargado (guarda copia)</label><br><label><input type="checkbox" name="seo" value="1"> Escribir títulos y descripciones en Yoast (guarda copia)</label></p>';
	submit_button( 'Ejecutar', 'primary', 'hv_import' );
	echo '</form>';
	if ( $rows ) {
		echo '<table class="widefat striped"><thead><tr><th>Idioma</th><th>Clave</th><th>Slug</th><th>ID</th><th>Acción</th></tr></thead><tbody>';
		foreach ( $rows as $r ) echo '<tr><td>' . esc_html( $r[0] ) . '</td><td>' . esc_html( $r[1] ) . '</td><td>' . esc_html( $r[2] ) . '</td><td>' . ( $r[3] ? '<a href="' . esc_url( get_permalink( $r[3] ) ) . '" target="_blank">' . (int) $r[3] . '</a>' : '' ) . '</td><td>' . esc_html( $r[4] ) . '</td></tr>';
		echo '</tbody></table>';
	}
	echo '</div>';
}
