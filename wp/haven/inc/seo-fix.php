<?php
/**
 * Ajustes de la auditoría SEO del 5 nov 2026 (semana 1).
 * - Redirecciones 301: Duke 206 (plantilla vieja) -> Habitación Duke; Presidencial (categoría con una sola suite) -> Suite Presidencial.
 * - Sin indexar: archivos de autor (redirigen al blog), categoría "sin categoría" y la oferta DEAL10; fuera del mapa del sitio.
 * - Open Graph: título = título SEO de la página (no "Inicio"), idioma es_CO / en_US, nombre del sitio.
 * - Datos estructurados: Hotel completo (precio, estrellas, horarios, habitaciones, 360° y Swing Lounge),
 *   Restaurant en la página del 360° y Event (fiesta del primer viernes) en Eventos.
 * - Texto alternativo de las fotos de la portada que no tenían (ES/EN).
 */
defined( 'ABSPATH' ) || exit;

/** Páginas viejas -> clave Haven de destino. */
function hv_seo_redirects() {
	return array(
		'habitacion-duke-206' => 'room-habitacion-duke',
		'duke-room-206'       => 'room-habitacion-duke',
		'presidencial'        => 'room-suite-presidential',
		'presidential'        => 'room-suite-presidential',
	);
}
function hv_seo_noindex_slugs() { return array( 'deal10' ); }

/** IDs de páginas por slug (cualquier idioma), con caché de un día. */
function hv_seo_ids( $slugs ) {
	$k = 'hv_seo_ids_' . md5( implode( ',', $slugs ) );
	$ids = get_transient( $k );
	if ( false === $ids ) {
		$ids = array();
		foreach ( $slugs as $s ) foreach ( get_posts( array( 'name' => $s, 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => 5, 'fields' => 'ids', 'lang' => '' ) ) as $id ) $ids[] = (int) $id;
		set_transient( $k, $ids, DAY_IN_SECONDS );
	}
	return $ids;
}

add_action( 'template_redirect', function () {
	if ( is_author() ) { wp_safe_redirect( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ), 301 ); exit; }
	if ( ! is_page() ) return;
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	$map = hv_seo_redirects();
	if ( isset( $map[ $slug ] ) ) {
		$lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( get_queried_object_id() ) : null;
		$to = hv_url( $map[ $slug ], $lang ?: null );
		if ( $to && untrailingslashit( $to ) !== untrailingslashit( get_permalink( get_queried_object_id() ) ) ) { wp_safe_redirect( $to, 301 ); exit; }
	}
}, 1 );

/** Los enlaces internos van directo al destino (sin pasar por la redirección). */
add_filter( 'hv_url_key', function ( $key ) { return 'presidencial' === $key ? 'room-suite-presidential' : $key; } );

/* ---------- Indexación ---------- */
add_filter( 'wpseo_robots', function ( $r ) {
	if ( is_author() || is_category( array( 'sin-categoria-es', 'sin-categoria', 'uncategorized' ) ) ) return 'noindex, follow';
	if ( is_page() && in_array( get_post_field( 'post_name', get_queried_object_id() ), hv_seo_noindex_slugs(), true ) ) return 'noindex, follow';
	return $r;
} );
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function ( $ids ) {
	return array_merge( (array) $ids, hv_seo_ids( array_merge( array_keys( hv_seo_redirects() ), hv_seo_noindex_slugs() ) ) );
} );
add_filter( 'wpseo_sitemap_exclude_author', '__return_empty_array' );
add_filter( 'wpseo_exclude_from_sitemap_by_term_ids', function ( $ids ) {
	foreach ( array( 'sin-categoria-es', 'sin-categoria', 'uncategorized' ) as $s ) { $t = get_term_by( 'slug', $s, 'category' ); if ( $t ) $ids[] = (int) $t->term_id; }
	return $ids;
} );

/* ---------- Open Graph ---------- */
function hv_seo_title_now() {
	if ( function_exists( 'YoastSEO' ) ) { try { $t = YoastSEO()->meta->for_current_page()->title; if ( $t ) return $t; } catch ( \Throwable $e ) {} }
	return wp_get_document_title();
}
foreach ( array( 'wpseo_opengraph_title', 'wpseo_twitter_title' ) as $f ) {
	add_filter( $f, function ( $t ) {
		$seo = hv_seo_title_now();
		// Yoast deja el título interno de la página ("Inicio", "Presidencial"…) cuando no hay uno social: se usa el título SEO.
		return ( ! $t || ( $seo && mb_strlen( $t ) < 30 && false === strpos( $t, '|' ) ) ) ? $seo : $t;
	} );
}
add_filter( 'wpseo_og_locale', function () { return hv_en() ? 'en_US' : 'es_CO'; } );
add_filter( 'wpseo_opengraph_site_name', function () { return 'Haven, A Design Hotel'; } );

/** Nombre viejo del Swing en descripciones que salen de la base de datos (meta y datos estructurados de Yoast). */
function hv_seo_brand( $s ) { return is_string( $s ) ? preg_replace( '/\bSwing (?:Coffee )?Bar\b/', 'Swing Lounge', $s ) : $s; }
foreach ( array( 'wpseo_metadesc', 'wpseo_opengraph_desc', 'wpseo_twitter_description', 'wpseo_title', 'wpseo_opengraph_title' ) as $f ) add_filter( $f, 'hv_seo_brand', 99 );
add_filter( 'wpseo_schema_webpage', function ( $d ) { if ( isset( $d['description'] ) ) $d['description'] = hv_seo_brand( $d['description'] ); if ( isset( $d['name'] ) ) $d['name'] = hv_seo_brand( $d['name'] ); return $d; } );
add_filter( 'wpseo_schema_website', function ( $d ) { $d['name'] = 'Haven, A Design Hotel'; if ( isset( $d['description'] ) && in_array( trim( $d['description'] ), array( 'Hotel', '' ), true ) ) $d['description'] = hv_en() ? 'Design hotel in El Poblado, Medellín' : 'Hotel de diseño en El Poblado, Medellín'; return $d; } );

/* ---------- Datos estructurados ---------- */
function hv_seo_rooftop_schema() {
	$h = function ( $d, $o, $c ) { return array( '@type' => 'OpeningHoursSpecification', 'dayOfWeek' => $d, 'opens' => $o, 'closes' => $c ); };
	return array(
		'@type' => 'Restaurant', '@id' => home_url( '/#rooftop' ), 'name' => '360 Rooftop Bar & Restaurante', 'alternateName' => '360° Rooftop Haven',
		'url' => hv_url( 'rooftop' ), 'hasMenu' => 'https://havenmedellin.com/360-rooftop-carta/', 'telephone' => '+57 312 6273647',
		'servesCuisine' => array( 'Internacional', 'Colombiana', 'Cócteles' ), 'priceRange' => '$$',
		'image' => home_url( '/wp-content/uploads/2026/06/piscina-rooftop.webp' ),
		'address' => array( '@type' => 'PostalAddress', 'streetAddress' => 'Calle 10A #36-29, piso 11', 'addressLocality' => 'Medellín', 'addressRegion' => 'Antioquia', 'addressCountry' => 'CO' ),
		'openingHoursSpecification' => array( $h( array( 'Monday', 'Tuesday', 'Wednesday' ), '07:00', '23:00' ), $h( 'Thursday', '07:00', '00:00' ), $h( 'Friday', '07:00', '02:00' ), $h( 'Saturday', '08:00', '02:00' ), $h( 'Sunday', '08:00', '00:00' ) ),
		'amenityFeature' => array( array( '@type' => 'LocationFeatureSpecification', 'name' => hv_en() ? 'Rooftop pool' : 'Piscina en la terraza', 'value' => true ) ),
		'acceptsReservations' => 'True',
	);
}
function hv_seo_swing_schema() {
	return array(
		'@type' => 'CafeOrCoffeeShop', '@id' => home_url( '/#swing' ), 'name' => 'Swing Lounge', 'alternateName' => 'Swing Coffee by Haven', 'url' => hv_url( 'swing' ),
		'hasMenu' => 'https://havenmedellin.com/swing-coffee-bar-carta/', 'servesCuisine' => array( hv_en() ? 'Antioquian specialty coffee' : 'Café de especialidad de Antioquia' ),
		'openingHoursSpecification' => array( array( '@type' => 'OpeningHoursSpecification', 'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ), 'opens' => '08:00', 'closes' => '18:00' ) ),
	);
}
add_filter( 'hv_hotel_schema', function ( $s ) {
	$en = hv_en();
	$s['description'] = $en ? 'Design hotel in El Poblado, Medellín: 37 rooms and suites (many with jacuzzi), 360° Rooftop & Restaurant with pool, Swing Lounge and wellness, 2 minutes from Provenza.'
		: 'Hotel de diseño en El Poblado, Medellín: 37 habitaciones y suites (muchas con jacuzzi), 360° Rooftop & Restaurante con piscina, Swing Lounge y wellness, a 2 minutos de Provenza.';
	$s['priceRange']    = '$$$';
	$s['starRating']    = array( '@type' => 'Rating', 'ratingValue' => '4' );
	$s['checkinTime']   = '15:00';
	$s['checkoutTime']  = '11:00';
	$s['numberOfRooms'] = 37;
	$s['petsAllowed']   = true;
	$s['currenciesAccepted'] = 'COP, USD';
	$s['containsPlace'] = array( hv_seo_rooftop_schema(), hv_seo_swing_schema() );
	return $s;
} );
add_action( 'wp_head', function () {
	$k = hv_key();
	if ( 'rooftop' === $k ) hv_ld( array( '@context' => 'https://schema.org' ) + hv_seo_rooftop_schema() );
	if ( 'swing' === $k ) hv_ld( array( '@context' => 'https://schema.org' ) + hv_seo_swing_schema() );
	if ( 'eventos' === $k ) {
		// Fiesta del primer viernes de cada mes (página Eventos): las dos próximas fechas.
		$ev = array();
		$tz = new DateTimeZone( 'America/Bogota' );
		$today = ( new DateTime( 'now', $tz ) )->format( 'Y-m-d' );
		for ( $i = 0; $i < 3 && count( $ev ) < 2; $i++ ) {
			$m = new DateTime( 'first day of this month', $tz ); $m->modify( "+$i month" );
			$d = new DateTime( 'first friday of ' . $m->format( 'F Y' ), $tz );
			if ( $d->format( 'Y-m-d' ) < $today ) continue;
			$ev[] = array(
				'@context' => 'https://schema.org', '@type' => 'Event',
				'name' => hv_en() ? 'First-Friday party at 360° Rooftop' : 'Fiesta del primer viernes en el 360° Rooftop',
				'description' => hv_en() ? 'Live DJ, music and a night with the best view of Medellín, every first Friday of the month at Haven’s 360° Rooftop.' : 'DJ en vivo, música y una noche con la mejor vista de Medellín, el primer viernes de cada mes en el 360° Rooftop de Haven.',
				'startDate' => $d->format( 'Y-m-d' ), 'eventStatus' => 'https://schema.org/EventScheduled', 'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
				'image' => array( home_url( '/wp-content/uploads/2026/06/fiesta-1.webp' ) ),
				'location' => array( '@type' => 'Place', 'name' => '360° Rooftop · Haven, A Design Hotel', 'address' => array( '@type' => 'PostalAddress', 'streetAddress' => 'Calle 10A #36-29, piso 11', 'addressLocality' => 'Medellín', 'addressRegion' => 'Antioquia', 'addressCountry' => 'CO' ) ),
				'organizer' => array( '@type' => 'Organization', 'name' => 'Haven, A Design Hotel', 'url' => hv_url( 'home' ) ),
			);
		}
		foreach ( $ev as $e ) hv_ld( $e );
	}
}, 6 );

/* ---------- Datos estructurados Event completos (aviso de Search Console, 11 nov 2026) ----------
 * Google pedía endDate, performer y offers.validFrom en los eventos. Se completan sobre el HTML final,
 * así cubre tanto los eventos del tema como los JSON-LD escritos dentro del contenido (página de temporada). */
function hv_seo_event_fix( array $e, $valid_from ) {
	$is_ev = isset( $e['@type'] ) && in_array( 'Event', (array) $e['@type'], true );
	if ( $is_ev ) {
		if ( empty( $e['endDate'] ) && ! empty( $e['startDate'] ) ) {
			$sd = (string) $e['startDate'];
			if ( preg_match( '/T\d/', $sd ) ) {
				try { $d = new DateTime( $sd ); $d->modify( '+4 hours' ); $e['endDate'] = $d->format( 'Y-m-d\TH:i:sP' ); } catch ( Exception $x ) { $e['endDate'] = $sd; }
			} else {
				$e['endDate'] = $sd;
			}
		}
		if ( empty( $e['performer'] ) ) {
			$e['performer'] = ! empty( $e['organizer'] ) ? $e['organizer'] : array( '@type' => 'Organization', 'name' => 'Haven, A Design Hotel', 'url' => home_url( '/' ) );
		}
		if ( ! empty( $e['offers'] ) && is_array( $e['offers'] ) ) {
			if ( isset( $e['offers']['@type'] ) ) {
				if ( empty( $e['offers']['validFrom'] ) ) $e['offers']['validFrom'] = $valid_from;
			} else {
				foreach ( $e['offers'] as $i => $o ) if ( is_array( $o ) && empty( $o['validFrom'] ) ) $e['offers'][ $i ]['validFrom'] = $valid_from;
			}
		}
	}
	foreach ( array( '@graph', 'subEvent' ) as $k ) {
		if ( ! empty( $e[ $k ] ) && is_array( $e[ $k ] ) ) foreach ( $e[ $k ] as $i => $x ) if ( is_array( $x ) ) $e[ $k ][ $i ] = hv_seo_event_fix( $x, $valid_from );
	}
	return $e;
}
function hv_seo_fix_events( $html ) {
	if ( false === strpos( $html, '"Event"' ) ) return $html;
	$id = get_queried_object_id();
	$valid_from = $id ? get_the_date( 'Y-m-d\TH:i:sP', $id ) : '';
	if ( ! $valid_from ) $valid_from = '2026-11-01T00:00:00-05:00';
	return preg_replace_callback( '#(<script[^>]*application/ld\+json[^>]*>)(.*?)(</script>)#s', function ( $m ) use ( $valid_from ) {
		if ( false === strpos( $m[2], '"Event"' ) ) return $m[0];
		$d = json_decode( $m[2], true );
		if ( ! is_array( $d ) ) return $m[0];
		$d = isset( $d[0] ) ? array_map( function ( $x ) use ( $valid_from ) { return is_array( $x ) ? hv_seo_event_fix( $x, $valid_from ) : $x; }, $d ) : hv_seo_event_fix( $d, $valid_from );
		return $m[1] . wp_json_encode( $d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ) . $m[3];
	}, $html );
}

/* ---------- Texto alternativo de la portada ---------- */
function hv_seo_alts() {
	$a = array(
		'hotel-haven-medellin' => array( 'Edificio de Haven, A Design Hotel en El Poblado, Medellín', 'Haven, A Design Hotel building in El Poblado, Medellín' ),
		'hotel-haven-rooftop-360-with-friends' => array( 'Amigos en el 360° Rooftop de Haven con vista a Medellín', 'Friends at Haven’s 360° Rooftop overlooking Medellín' ),
		'hotel-haven-rooftop-360-gastronomy-cocktails' => array( 'Cócteles y platos del 360° Rooftop & Restaurante de Haven', 'Cocktails and dishes at Haven’s 360° Rooftop & Restaurant' ),
		'presidential-suite-room-bed' => array( 'Cama de la Suite Presidencial de Haven, El Poblado', 'Presidential Suite bed at Haven, El Poblado' ),
		'presidential-suite-room-jacuzzi-lounge-area' => array( 'Suite Presidencial de Haven con jacuzzi y sala, El Poblado', 'Haven Presidential Suite with jacuzzi and lounge, El Poblado' ),
		'presidential-suite-room-balcony' => array( 'Balcón de la Suite Presidencial con vista a El Poblado', 'Presidential Suite balcony overlooking El Poblado' ),
		'emperor-phenthouse-901-room-jacuzzi' => array( 'Jacuzzi del penthouse Emperor 901 de Haven en Medellín', 'Jacuzzi in Haven’s Emperor 901 penthouse, Medellín' ),
		'emperor-penthouse-901-room' => array( 'Penthouse Emperor 901 de Haven, El Poblado, Medellín', 'Emperor 901 penthouse at Haven, El Poblado, Medellín' ),
		'excelsior-suite-room-401-jacuzzi-lounge-area' => array( 'Suite Excelsior 401 de Haven con jacuzzi y sala', 'Haven Excelsior Suite 401 with jacuzzi and lounge' ),
		'imperial-suite-402-room-jacuzzi-lounge-area' => array( 'Suite Imperial 402 de Haven con jacuzzi', 'Haven Imperial Suite 402 with jacuzzi' ),
		'viscount-suite-803-room-jacuzzi-balcony' => array( 'Habitación Viscount 803 con jacuzzi en el balcón, Haven Medellín', 'Viscount 803 room with balcony jacuzzi, Haven Medellín' ),
		'marquess-room-bed' => array( 'Habitación Marqués de Haven, hotel de diseño en El Poblado', 'Marques room at Haven, design hotel in El Poblado' ),
		'swing-bar' => array( 'Swing Lounge, café de especialidad en el vestíbulo de Haven', 'Swing Lounge, specialty coffee in the Haven lobby' ),
		'aromas-coffee' => array( 'Café de especialidad de Antioquia en Swing Lounge', 'Antioquian specialty coffee at Swing Lounge' ),
		'vida-nocturna' => array( 'Noche con DJ en el 360° Rooftop de Haven', 'DJ night at Haven’s 360° Rooftop' ),
		'piscina-rooftop' => array( 'Piscina del 360° Rooftop de Haven con vista a Medellín', 'Haven 360° Rooftop pool overlooking Medellín' ),
		'burrata-pizza' => array( 'Pizza de burrata del 360° Rooftop & Restaurante', 'Burrata pizza at 360° Rooftop & Restaurant' ),
		'masaje-relajante' => array( 'Masaje relajante en el wellness de Haven', 'Relaxing massage at Haven wellness' ),
		'fiesta' => array( 'Fiesta en el 360° Rooftop de Haven, El Poblado', 'Party at Haven’s 360° Rooftop, El Poblado' ),
		'hotel-haven-rooftop-360-gastronomy' => array( 'Platos del 360° Rooftop & Restaurante de Haven', 'Dishes at Haven’s 360° Rooftop & Restaurant' ),
		'hotel-haven-rooftop' => array( '360° Rooftop de Haven en El Poblado, Medellín', 'Haven’s 360° Rooftop in El Poblado, Medellín' ),
		'la-culpable-pizza' => array( 'Pizza La Culpable del 360° Rooftop & Restaurante', 'La Culpable pizza at 360° Rooftop & Restaurant' ),
		'salmon-ricotta' => array( 'Salmón con ricotta y espinaca del 360° Rooftop & Restaurante', 'Salmon with ricotta and spinach at 360° Rooftop & Restaurant' ),
		'mini-albondigas' => array( 'Mini albóndigas del 360° Rooftop & Restaurante', 'Mini meatballs at 360° Rooftop & Restaurant' ),
		'brunch-dominical' => array( 'Brunch en el 360° Rooftop de Haven', 'Brunch at Haven’s 360° Rooftop' ),
		'hidratacion-facial' => array( 'Hidratación facial en el wellness de Haven', 'Facial hydration at Haven wellness' ),
		'terapia-de-sueros' => array( 'Sueroterapia en el wellness de Haven', 'IV therapy at Haven wellness' ),
		'suero-terapia' => array( 'Sueroterapia en el wellness de Haven', 'IV therapy at Haven wellness' ),
		'swing-cafe' => array( 'Café en Swing Lounge, vestíbulo de Haven', 'Coffee at Swing Lounge, Haven lobby' ),
		'presidential-suite-room' => array( 'Suite Presidencial de Haven, El Poblado', 'Haven Presidential Suite, El Poblado' ),
		'imperial-suite-402' => array( 'Suite Imperial 402 de Haven', 'Haven Imperial Suite 402' ),
		'emperor-phenthouse-90' => array( 'Penthouse Emperor de Haven en Medellín', 'Haven Emperor penthouse in Medellín' ),
		'excelsior-suite' => array( 'Suite Excelsior de Haven', 'Haven Excelsior Suite' ),
		'viscount' => array( 'Habitación Viscount de Haven', 'Haven Viscount room' ),
	);
	return $a;
}
function hv_seo_fill_alts( $html ) {
	$en = hv_en() ? 1 : 0;
	$map = hv_seo_alts();
	return preg_replace_callback( '#<img\b[^>]*\balt=""[^>]*>#', function ( $m ) use ( $map, $en ) {
		if ( ! preg_match( '#\bsrc="[^"]*/([^/"]+?)(?:-\d+x\d+)?\.(?:webp|jpe?g|png)"#i', $m[0], $s ) ) return $m[0];
		$stem = preg_replace( '/-\d+$/', '', strtolower( $s[1] ) );
		foreach ( $map as $k => $v ) if ( 0 === strpos( $stem, $k ) ) return str_replace( 'alt=""', 'alt="' . esc_attr( $v[ $en ] ) . '"', $m[0] );
		// Sin descripción propia: nombre de la página + hotel (mejor que vacío para Google Imágenes y lectores de pantalla).
		static $page = null;
		if ( null === $page ) $page = trim( wp_strip_all_tags( (string) ( is_singular() ? get_the_title( get_queried_object_id() ) : '' ) ) );
		return $page ? str_replace( 'alt=""', 'alt="' . esc_attr( $page . ( $en ? ' · Haven, El Poblado, Medellín' : ' · Haven, El Poblado, Medellín' ) ) . '"', $m[0] ) : $m[0];
	}, $html );
}

/* ---------- Páginas por intención y páginas de la guía (plantilla landing) ---------- */
function hv_landing_keys() {
	return array( 'cerca-parque-lleras', 'escapada-romantica', 'dia-de-sol', 'celebraciones',
		'guia-a-pie', 'guia-provenza', 'guia-restaurantes-provenza', 'guia-restaurantes-poblado', 'guia-bares-lleras', 'guia-cafes', 'guia-rooftops', 'guia-el-poblado-a-pie',
		'guia-2-dias', 'guia-3-dias', 'guia-4-dias', 'guia-donde-hospedarse' );
}

/** Datos de la guía (lugares, eventos) del idioma actual. */
function hv_guia_data( $lang = null ) {
	static $c = array();
	$lang = $lang ?: hv_lang();
	if ( ! isset( $c[ $lang ] ) ) {
		$f = HAVEN_DIR . '/guia/data_' . $lang . '.json';
		$c[ $lang ] = file_exists( $f ) ? (array) json_decode( file_get_contents( $f ), true ) : array();
	}
	return $c[ $lang ];
}

add_action( 'wp_head', function () {
	$d = hv_data();
	// Landings: sus bloques. Otras páginas Haven: los bloques extra de "more" (Swing, Wellness, Gastronomía, Servicios, quiz).
	if ( 'landing' === hv_template_key() ) $list = (array) ( $d['blocks'] ?? array() );
	elseif ( ! empty( $d['more'] ) && is_array( $d['more'] ) ) $list = $d['more'];
	else return;
	$url = get_permalink();
	$places = array();
	foreach ( (array) ( hv_guia_data()['lugares'] ?? array() ) as $p ) $places[ $p['id'] ] = $p;
	foreach ( $list as $b ) {
		$t = $b['type'] ?? '';
		if ( 'faq' === $t ) {
			$qs = array();
			foreach ( (array) ( $b['items'] ?? array() ) as $q ) if ( ! empty( $q['q'] ) ) $qs[] = array( '@type' => 'Question', 'name' => $q['q'], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $q['a'] ?? '' ) ) );
			if ( $qs ) hv_ld( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $qs ) );
		}
		if ( 'places' === $t ) {
			$items = array(); $i = 0;
			foreach ( (array) ( $b['ids'] ?? array() ) as $id ) {
				if ( empty( $places[ $id ] ) ) continue;
				$p = $places[ $id ];
				$pl = array( '@type' => 'Place', 'name' => $p['n'], 'address' => trim( ( $p['dir'] ?? '' ) . ', Medellín' ), 'description' => $p['desc'] ?? '' );
				if ( isset( $p['lat'], $p['lng'] ) ) $pl['geo'] = array( '@type' => 'GeoCoordinates', 'latitude' => $p['lat'], 'longitude' => $p['lng'] );
				$items[] = array( '@type' => 'ListItem', 'position' => ++$i, 'item' => $pl );
			}
			if ( $items ) hv_ld( array( '@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => wp_strip_all_tags( $b['title'] ?? get_the_title() ), 'itemListElement' => $items ) );
		}
		if ( 'days' === $t ) {
			$parts = array();
			foreach ( (array) ( $b['days'] ?? array() ) as $day ) $parts[] = array( '@type' => 'TouristTrip', 'name' => $day['t'] ?? '', 'description' => wp_strip_all_tags( implode( ' ', array_map( function ( $x ) { return ( $x['h'] ?? '' ) . ': ' . ( $x['p'] ?? '' ); }, (array) ( $day['items'] ?? array() ) ) ) ) );
			hv_ld( array( '@context' => 'https://schema.org', '@type' => 'TouristTrip', 'name' => wp_strip_all_tags( $d['h1'] ?? get_the_title() ), 'url' => $url, 'touristType' => hv_en() ? 'Visitors to Medellín' : 'Visitantes de Medellín', 'itinerary' => array( '@type' => 'ItemList', 'itemListElement' => array_map( function ( $x, $i ) { return array( '@type' => 'ListItem', 'position' => $i + 1, 'item' => $x ); }, $parts, array_keys( $parts ) ) ), 'provider' => array( '@type' => 'Hotel', '@id' => home_url( '/#hotel' ), 'name' => 'Haven, A Design Hotel' ) ) );
		}
	}
}, 7 );

/** Índice de páginas (tarjetas de enlace) por claves; solo las que existen en el idioma actual. */
function hv_links_index( $eyebrow, $title_html, $keys, $intro = '' ) {
	$h = '';
	foreach ( $keys as $k ) {
		$id = hv_page_id( $k );
		if ( ! $id || 'publish' !== get_post_status( $id ) ) continue;
		$ld = hv_data( $id );
		$label = wp_strip_all_tags( $ld['crumb'] ?? '' ) ?: get_the_title( $id );
		$h .= '<li><a href="' . esc_url( get_permalink( $id ) ) . '"><span>' . e( $label ) . '</span>' . HV_ARROW . '</a></li>';
	}
	if ( ! $h ) return '';
	return '<section class="sec lp-alt"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow">' . e( $eyebrow ) . '</p><h2 style="margin-top:20px">' . wp_kses_post( $title_html ) . '</h2></div>' . ( $intro ? '<p>' . e( $intro ) . '</p>' : '' ) . '</div><ul class="lp-links" data-reveal>' . $h . '</ul></div></section>';
}

/** Fecha legible en el idioma de la página, sin depender del idioma del servidor. */
function hv_fecha( $ts, $en = null ) {
	$en = null === $en ? hv_en() : $en;
	$m = (int) date( 'n', $ts );
	if ( $en ) return date( 'F j, Y', $ts );
	$meses = array( 1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre' );
	return date( 'j', $ts ) . ' de ' . $meses[ $m ] . ' de ' . date( 'Y', $ts );
}

/**
 * Bloques editoriales (text, rooms, places, days, faq, links, cta). Los usa la plantilla landing y,
 * con la clave "more" de _hv_data, también Swing, Wellness, Gastronomía, Servicios y el quiz (5 nov).
 * $o: crumb (antetítulo por defecto), n (índice inicial, decide la alternancia y el ancla #c).
 */
function hv_render_blocks( $blocks, $o = array() ) {
	$en    = hv_en();
	$crumb = $o['crumb'] ?? get_the_title();
	$places = array();
	foreach ( (array) ( hv_guia_data()['lugares'] ?? array() ) as $p ) $places[ $p['id'] ] = $p;
	$rooms_by_key = array();
	foreach ( hv_rooms() as $r ) $rooms_by_key[ hv_key( $r['id'] ) ] = $r;

	$n = (int) ( $o['n'] ?? 0 );
	foreach ( $blocks as $b ) {
		$type = $b['type'] ?? 'text';
		$head = function ( $b, $p = '' ) {
			$h = '<div class="sec-head" data-reveal><div>';
			if ( ! empty( $b['eyebrow'] ) ) $h .= '<p class="eyebrow">' . e( $b['eyebrow'] ) . '</p>';
			if ( ! empty( $b['title'] ) ) $h .= '<h2 style="margin-top:20px">' . wp_kses_post( $b['title'] ) . '</h2>';
			$h .= '</div>' . ( $p ? '<p>' . e( $p ) . '</p>' : '' ) . '</div>';
			return $h;
		};
		switch ( $type ) {
			case 'text':
				$sub = '';
				foreach ( (array) ( $b['sub'] ?? array() ) as $s ) {
					$sub .= '<h3>' . e( $s['h3'] ?? '' ) . '</h3>';
					foreach ( (array) ( $s['paras'] ?? array() ) as $p ) $sub .= '<p>' . wp_kses_post( $p ) . '</p>';
				}
				if ( ! empty( $b['img'] ) && ! $sub ) {
					echo hv_editorial( $b['eyebrow'] ?? $crumb, $b['title'] ?? '', (array) ( $b['paras'] ?? array() ), $b['img'], $b['img2'] ?? null, 1 === $n % 2, 1 === $n % 2, '', 0 === $n ? 'c' : '' );
				} else {
					echo '<section class="sec' . ( 1 === $n % 2 ? ' lp-alt' : '' ) . '"' . ( 0 === $n ? ' id="c"' : '' ) . '><div class="wrap lp-text"><article class="prose" data-reveal>';
					if ( ! empty( $b['eyebrow'] ) ) echo '<p class="eyebrow">' . e( $b['eyebrow'] ) . '</p>';
					if ( ! empty( $b['title'] ) ) echo '<h2>' . wp_kses_post( $b['title'] ) . '</h2>';
					foreach ( (array) ( $b['paras'] ?? array() ) as $p ) echo '<p>' . wp_kses_post( $p ) . '</p>';
					echo $sub;
					if ( ! empty( $b['img'] ) ) echo '<figure>' . hv_img( $b['img'], array( 'alt' => wp_strip_all_tags( $b['title'] ?? $crumb ) ) ) . '</figure>';
					echo '</article></div></section>';
				}
				break;
			case 'rooms':
				$cards = '';
				foreach ( (array) ( $b['keys'] ?? array() ) as $k ) if ( isset( $rooms_by_key[ $k ] ) ) $cards .= hv_room_card( $rooms_by_key[ $k ] );
				if ( $cards ) echo '<section class="sec"' . ( 0 === $n ? ' id="c"' : '' ) . '><div class="wrap">' . $head( $b, $b['intro'] ?? '' ) . '<div class="cards">' . $cards . '</div></div></section>';
				break;
			case 'places':
				$cards = '';
				foreach ( (array) ( $b['ids'] ?? array() ) as $id ) {
					if ( empty( $places[ $id ] ) ) continue;
					$p = $places[ $id ];
					$walk = (int) ( $p['min'] ?? 0 );
					$dist = ( $walk && $walk <= 30 ) ? sprintf( $en ? '%d min walk' : '%d min a pie', $walk ) : ( ! empty( $p['carro'] ) ? sprintf( $en ? '%d min by car' : '%d min en carro', (int) $p['carro'] ) : '' );
					$price = ! empty( $p['precio'] ) ? str_repeat( '$', (int) $p['precio'] ) : '';
					$map = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $p['n'] . ', ' . ( $p['dir'] ?? 'Medellín' ) );
					$cards .= '<li class="lp-place"><div class="lp-place-top"><h3>' . e( $p['n'] ) . '</h3>' . ( $dist ? '<span class="lp-walk">' . e( $dist ) . '</span>' : '' ) . '</div>'
						. '<p class="lp-zone">' . e( trim( ( $p['zona'] ?? '' ) . ( $price ? ' · ' . $price : '' ) ) ) . '</p>'
						. '<p>' . e( $p['desc'] ?? '' ) . '</p>'
						. ( ! empty( $p['tip'] ) ? '<p class="lp-tip"><b>' . e( $en ? 'Our tip: ' : 'Nuestro consejo: ' ) . '</b>' . e( $p['tip'] ) . '</p>' : '' )
						. '<a class="lp-map" href="' . esc_url( $map ) . '" target="_blank" rel="noopener">' . e( $en ? 'Open in Google Maps' : 'Abrir en Google Maps' ) . ' ' . HV_OUT . '</a></li>';
				}
				if ( $cards ) echo '<section class="sec lp-alt"' . ( 0 === $n ? ' id="c"' : '' ) . '><div class="wrap">' . $head( $b, $b['intro'] ?? '' ) . '<ol class="lp-places" data-reveal>' . $cards . '</ol></div></section>';
				break;
			case 'days':
				$h = '';
				foreach ( (array) ( $b['days'] ?? array() ) as $day ) {
					$h .= '<div class="lp-day"><h3>' . e( $day['t'] ?? '' ) . '</h3><dl>';
					foreach ( (array) ( $day['items'] ?? array() ) as $it ) $h .= '<dt>' . e( $it['h'] ?? '' ) . '</dt><dd>' . wp_kses_post( $it['p'] ?? '' ) . '</dd>';
					$h .= '</dl></div>';
				}
				echo '<section class="sec"' . ( 0 === $n ? ' id="c"' : '' ) . '><div class="wrap">' . $head( $b ) . '<div class="lp-days" data-reveal>' . $h . '</div></div></section>';
				break;
			case 'faq':
				$h = '';
				foreach ( (array) ( $b['items'] ?? array() ) as $q ) {
					if ( empty( $q['q'] ) ) continue;
					$h .= '<details><summary>' . e( $q['q'] ) . '<i aria-hidden="true">+</i></summary><div class="a"><p>' . wp_kses_post( $q['a'] ?? '' ) . '</p></div></details>';
				}
				echo '<section class="sec"><div class="wrap" style="max-width:1000px">' . $head( array( 'eyebrow' => $en ? 'FAQ' : 'Preguntas', 'title' => $b['title'] ?? ( $en ? 'Frequently asked <em>questions</em>' : 'Preguntas <em>frecuentes</em>' ) ) ) . '<div class="faq" data-reveal>' . $h . '</div></div></section>';
				break;
			case 'links':
				$h = '';
				foreach ( (array) ( $b['keys'] ?? array() ) as $k ) {
					$id = hv_page_id( $k );
					if ( ! $id || (int) $id === (int) hv_current_id() ) continue;
					$ld = hv_data( $id );
					$label = wp_strip_all_tags( $ld['crumb'] ?? '' ) ?: get_the_title( $id );
					$h .= '<li><a href="' . esc_url( get_permalink( $id ) ) . '"><span>' . e( $label ) . '</span>' . HV_ARROW . '</a></li>';
				}
				if ( $h ) echo '<section class="sec lp-alt"><div class="wrap">' . $head( $b ) . '<ul class="lp-links" data-reveal>' . $h . '</ul></div></section>';
				break;
			case 'cta':
				echo '<section class="sec lp-cta"><div class="wrap" data-reveal><h2>' . wp_kses_post( $b['title'] ?? '' ) . '</h2><p>' . e( $b['text'] ?? '' ) . '</p><div class="lp-cta-btns">'
					. hv_btn( t( 'Reservar ahora' ), '#reservar', 'gold', 'reservar_' . hv_key() )
					. hv_btn( 'WhatsApp', hv_wa( $b['wa'] ?? ( $en ? 'Hello, I would like information' : 'Hola, quiero información' ) ), 'ghost', 'whatsapp_' . hv_key(), true )
					. '</div></div></section>';
				break;
		}
		$n++;
	}
	return $n;
}

/* hreflang x-default en todas las páginas (Polylang solo lo pone en la portada): apunta a la versión en español. */
add_filter( 'pll_rel_hreflang_attributes', function ( $h ) {
	if ( is_array( $h ) && ! isset( $h['x-default'] ) && ! empty( $h['es'] ) ) $h['x-default'] = $h['es'];
	return $h;
} );

/* Portada: "Planes en Haven", tarjetas hacia las páginas por intención y la guía ({{sc:haven_planes}} en partials/home.html). */
add_shortcode( 'haven_planes', function () {
	$en = hv_en();
	return hv_links_index(
		$en ? 'Plans at Haven' : 'Planes en Haven',
		$en ? 'Choose your <em>plan</em>' : 'Elija su <em>plan</em>',
		array( 'suites-de-lujo', 'escapada-romantica', 'penthouse', 'cerca-parque-lleras', 'dia-de-sol', 'celebraciones', 'guia-a-pie', 'guia-3-dias' ),
		$en ? 'Stays and plans for every kind of trip, two minutes from Provenza.' : 'Estadías y planes para cada tipo de viaje, a dos minutos de Provenza.'
	);
} );

