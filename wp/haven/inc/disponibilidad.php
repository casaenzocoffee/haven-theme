<?php
/**
 * Calendario de disponibilidad en vivo (pedido del gerente, 11 nov 2026).
 *
 * De dónde sale: el mismo motor de reservas de Roiback (havenmedellin.backhotelite.com) que usa la web para reservar.
 * Por cada noche se pregunta al motor qué tipos de habitación están libres (1 noche, 1 adulto, para no ocultar ninguna),
 * y se guarda una copia en la opción hv_dispo: { upd, names{codigo:nombre}, days{AAAA-MM-DD:{t:hora, c:{codigo:cupo}}} }.
 * La copia se renueva poco a poco con WP-Cron (cada 5 min, 20 noches por vez, primero las cercanas): las próximas
 * 2 semanas quedan al día cada ~5 min y las 120 noches en menos de una hora. El cron real de cPanel (hv-cron-haven, 11 nov) ejecuta WP-Cron cada 5 minutos.
 *
 * Lo que ve el visitante: uploads/hv-dispo/<clave>.json (copia estática, rápida) o, de respaldo,
 * /wp-json/haven/v1/disponibilidad?room=<clave de la habitación> → { upd, today, days{fecha:cupo} }.
 * No se muestran precios del motor (los precios "desde" siguen en inc/precios.php).
 */
defined( 'ABSPATH' ) || exit;

const HV_DISPO_DAYS = 120;
const HV_DISPO_HOST = 'https://havenmedellin.backhotelite.com';

/** Clave de la habitación en el sitio → código del tipo de habitación en Roiback. */
function hv_dispo_map() {
	return apply_filters( 'hv_dispo_map', array(
		'room-habitacion-consort'      => 'consort',
		'room-habitacion-duke'         => 'duke',
		'room-habitacion-earl'         => 'earl',
		'room-habitacion-marques'      => 'marquess',
		'room-habitacion-marques-plus' => 'marquessjr',
		'room-habitacion-lord'         => 'lord',
		'room-habitacion-ambassador-jr'=> '~Ambassador Jr', // código aún no visto: se busca por nombre
		'room-habitacion-viscount'     => 'viscount',
		'room-habitacion-viscount-803' => 'visc',
		'room-habitacion-ambassador'   => 'ambassador',
		'room-suite-excelsior-604'     => 'excelsior',
		'room-suite-excelsior-804'     => 'excel',
		'room-suite-excelsior-401'     => 'exc',
		'room-suite-imperial-402'      => 'impe',
		'room-suite-emperor-901'       => 'emperor',
		'room-suite-emperor-902'       => 'empe',
		'room-suite-presidential'      => 'presidencial',
	) );
}

function hv_dispo_tz() { return new DateTimeZone( 'America/Bogota' ); }

/** Código del hotel en Roiback: está en la propia página del motor. Se guarda y se renueva cada semana. */
function hv_dispo_hotelcode( $force = false ) {
	$c = get_option( 'hv_roiback_code', array() );
	if ( ! $force && ! empty( $c['v'] ) && ( time() - (int) ( $c['t'] ?? 0 ) ) < WEEK_IN_SECONDS ) return $c['v'];
	$d = ( new DateTime( 'now', hv_dispo_tz() ) )->modify( '+30 days' )->format( 'Y-m-d' );
	$e = ( new DateTime( $d ) )->modify( '+1 day' )->format( 'Y-m-d' );
	$r = wp_remote_get( HV_DISPO_HOST . "/es/bookcore/availability/havenmedellin/$d/$e/", array( 'timeout' => 15 ) );
	if ( ! is_wp_error( $r ) && preg_match( '#["\']hotelCode["\']\s*:\s*["\']([A-Za-z0-9_-]{4,40})["\']#', wp_remote_retrieve_body( $r ), $m ) ) {
		update_option( 'hv_roiback_code', array( 'v' => $m[1], 't' => time() ), false );
		return $m[1];
	}
	return $c['v'] ?? '';
}

/** Pregunta al motor por una noche. Devuelve array( codigo => cupo ) (vacío = nada libre) o null si falló. */
function hv_dispo_fetch_night( $date, &$names = null ) {
	$code = hv_dispo_hotelcode();
	if ( ! $code ) return null;
	$out = ( new DateTime( $date ) )->modify( '+1 day' )->format( 'Y-m-d' );
	$q = http_build_query( array( 'adults' => 1, 'babies' => 0, 'checkin' => $date, 'checkout' => $out, 'children-ages' => '', 'cp' => '',
		'hotelCode' => $code, 'isDesktop' => 'true', 'ninos' => 0, 'lang' => 'es' ) );
	$r = wp_remote_get( HV_DISPO_HOST . '/es/bookcore/ajax/desktop/availability/rooms/?' . $q, array( 'timeout' => 15, 'headers' => array( 'Accept' => 'application/json' ) ) );
	if ( is_wp_error( $r ) || 200 !== (int) wp_remote_retrieve_response_code( $r ) ) return null;
	$j = json_decode( wp_remote_retrieve_body( $r ), true );
	if ( ! is_array( $j ) || ! isset( $j['roomData'] ) ) return null;
	$c = array();
	foreach ( (array) $j['roomData'] as $x ) {
		$p = $x['parsedRoomData'] ?? array();
		if ( empty( $p['codigo'] ) ) continue;
		$c[ $p['codigo'] ] = max( 0, (int) ( $p['cupo'] ?? 0 ) );
		if ( is_array( $names ) && ! empty( $p['nombre'] ) ) $names[ $p['codigo'] ] = $p['nombre'];
	}
	return $c;
}

/** Renueva las noches más viejas de la copia (máximo $n por vez, y nunca más de ~40 s). */
function hv_dispo_refresh( $n = 12 ) {
	$tz   = hv_dispo_tz();
	$st   = get_option( 'hv_dispo', array() );
	$days = (array) ( $st['days'] ?? array() );
	$names = (array) ( $st['names'] ?? array() );
	$today = new DateTime( 'today', $tz );
	$want = array();
	// Prioridad (11 nov): las fechas cercanas cuentan como "más viejas" para renovarse primero.
	// Con 20 noches cada 5 min: próximas 2 semanas al día cada ~5 min, el mes siguiente cada ≤30 min, el resto cada ≤50 min.
	for ( $i = 0; $i < HV_DISPO_DAYS; $i++ ) {
		$d = ( clone $today )->modify( "+$i day" )->format( 'Y-m-d' );
		$want[ $d ] = (int) ( $days[ $d ]['t'] ?? 0 ) - ( $i < 14 ? 2700 : ( $i < 45 ? 1200 : 0 ) );
	}
	asort( $want ); // primero las que nunca se han leído o llevan más tiempo
	$t0 = microtime( true ); $done = 0; $fail = 0;
	foreach ( array_slice( array_keys( $want ), 0, $n ) as $d ) {
		if ( microtime( true ) - $t0 > 40 ) break;
		$c = hv_dispo_fetch_night( $d, $names );
		if ( null === $c ) { $fail++; if ( $fail >= 3 ) break; continue; }
		$days[ $d ] = array( 't' => time(), 'c' => $c );
		$done++;
	}
	foreach ( array_keys( $days ) as $d ) if ( ! isset( $want[ $d ] ) ) unset( $days[ $d ] ); // fuera de la ventana
	ksort( $days );
	update_option( 'hv_dispo', array( 'upd' => time(), 'names' => $names, 'days' => $days ), false );
	hv_dispo_write_files();
	return array( 'leidas' => $done, 'fallas' => $fail );
}

/** Datos que ve el visitante para una habitación: { upd, today, days{fecha:cupo} }. */
function hv_dispo_payload( $key, $st = null ) {
	$st    = null === $st ? get_option( 'hv_dispo', array() ) : $st;
	$code  = hv_dispo_code_for( $key, $st['names'] ?? array() );
	$today = ( new DateTime( 'today', hv_dispo_tz() ) )->format( 'Y-m-d' );
	$days  = array();
	foreach ( (array) ( $st['days'] ?? array() ) as $d => $x ) {
		if ( $d < $today ) continue;
		$days[ $d ] = $code ? (int) ( $x['c'][ $code ] ?? 0 ) : 0;
	}
	// name: nombre del tipo de habitación en Roiback; el calendario lo pasa al motor (hv_room) para mostrarla de primera (etiqueta de GTM-TVFGX8W).
	return array( 'upd' => (int) ( $st['upd'] ?? 0 ), 'today' => $today, 'name' => $code ? (string) ( ( $st['names'] ?? array() )[ $code ] ?? '' ) : '', 'days' => $days );
}

/* Copia estática por habitación en uploads/hv-dispo/<clave>.json (11 nov): el servidor la entrega sin arrancar WordPress
   (una consulta a /wp-json tarda ~2,6 s en este hosting; el archivo, milisegundos). La ruta REST queda como respaldo. */
function hv_dispo_dir() { $u = wp_upload_dir( null, false ); return array( trailingslashit( $u['basedir'] ) . 'hv-dispo/', trailingslashit( $u['baseurl'] ) . 'hv-dispo/' ); }
function hv_dispo_write_files() {
	list( $dir ) = hv_dispo_dir();
	if ( ! wp_mkdir_p( $dir ) ) return;
	$st = get_option( 'hv_dispo', array() );
	foreach ( array_keys( hv_dispo_map() ) as $key ) {
		$tmp = $dir . $key . '.json.tmp';
		if ( false !== file_put_contents( $tmp, wp_json_encode( hv_dispo_payload( $key, $st ) ) ) ) rename( $tmp, $dir . $key . '.json' );
	}
}
function hv_dispo_json_base() { list( $dir, $url ) = hv_dispo_dir(); return is_dir( $dir ) ? set_url_scheme( $url ) : ''; }

/* WP-Cron: cada 5 minutos. */
add_filter( 'cron_schedules', function ( $s ) { $s['hv_5min'] = array( 'interval' => 300, 'display' => 'Cada 5 minutos (Haven)' ); return $s; } );
add_action( 'init', function () { if ( ! wp_next_scheduled( 'hv_dispo_cron' ) ) wp_schedule_event( time() + 60, 'hv_5min', 'hv_dispo_cron' ); } );
add_action( 'hv_dispo_cron', function () { hv_dispo_refresh( 20 ); } );

/** Código de Roiback para una clave del sitio (resuelve los que se buscan por nombre). */
function hv_dispo_code_for( $key, $names ) {
	$m = hv_dispo_map();
	$c = $m[ $key ] ?? '';
	if ( 0 === strpos( $c, '~' ) ) {
		$want = strtolower( substr( $c, 1 ) );
		$c = '';
		foreach ( (array) $names as $code => $nm ) if ( false !== strpos( strtolower( $nm ), $want ) ) { $c = $code; break; }
	}
	return $c;
}

/* Datos para el calendario. */
add_action( 'rest_api_init', function () {
	register_rest_route( 'haven/v1', '/disponibilidad', array(
		'methods' => 'GET', 'permission_callback' => '__return_true',
		'args' => array( 'room' => array( 'required' => true, 'sanitize_callback' => 'sanitize_key' ) ),
		'callback' => function ( $req ) {
			$key = $req['room'];
			if ( ! isset( hv_dispo_map()[ $key ] ) ) return new WP_Error( 'hv_room', 'Habitación desconocida', array( 'status' => 404 ) );
			$st = get_option( 'hv_dispo', array() );
			$code = hv_dispo_code_for( $key, $st['names'] ?? array() );
			$today = ( new DateTime( 'today', hv_dispo_tz() ) )->format( 'Y-m-d' );
			$days = array();
			foreach ( (array) ( $st['days'] ?? array() ) as $d => $x ) {
				if ( $d < $today ) continue;
				$days[ $d ] = $code ? (int) ( $x['c'][ $code ] ?? 0 ) : 0;
			}
			$res = rest_ensure_response( array( 'upd' => (int) ( $st['upd'] ?? 0 ), 'today' => $today, 'days' => $days ) );
			$res->header( 'Cache-Control', 'public, max-age=300' );
			return $res;
		},
	) );
} );

/** Bloque del calendario (ficha de habitación y ventana de las tarjetas). */
function hv_dispo_block( $key, $name = '' ) {
	if ( ! isset( hv_dispo_map()[ $key ] ) ) return '';
	$en = hv_en();
	return '<div class="hv-cal" data-room="' . ea( $key ) . '" data-name="' . ea( $name ) . '" data-api="' . esc_url( rest_url( 'haven/v1/disponibilidad' ) ) . '" data-json="' . esc_url( hv_dispo_json_base() ) . '" data-lang="' . ( $en ? 'en' : 'es' ) . '">'
		. '<p class="hv-cal-load">' . e( $en ? 'Loading live availability…' : 'Cargando disponibilidad en vivo…' ) . '</p></div>';
}
