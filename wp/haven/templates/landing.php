<?php
/*
 * Páginas por intención de búsqueda y páginas de la guía (auditoría SEO, 5 nov 2026).
 * Datos en _hv_data: h1, sub, img, crumb, eyebrow, parent (clave), updated, blocks[].
 * Bloques: text, rooms, places, days, faq, links, cta. Los datos estructurados (FAQPage, ItemList, TouristTrip) los agrega inc/seo-fix.php.
 */
get_header();
$d      = hv_data();
$en     = hv_en();
$blocks = (array) ( $d['blocks'] ?? array() );
$crumb  = $d['crumb'] ?? get_the_title();
$parent = $d['parent'] ?? '';
$crumbs = array( array( t( 'Inicio' ), hv_url( 'home' ) ) );
if ( $parent ) $crumbs[] = array( $en ? 'Medellín guide' : 'Guía de Medellín', hv_url( $parent ) );
$crumbs[] = array( $crumb, '' );

// Primera imagen disponible para la portada si la página no trae una.
$img = $d['img'] ?? '';
if ( ! $img ) foreach ( $blocks as $b ) { if ( ! empty( $b['img'] ) ) { $img = $b['img']; break; } }
if ( ! $img ) $img = '/wp-content/uploads/2026/06/hotel-haven-medellin.webp';
echo hv_hero( $d['h1'] ?? e( get_the_title() ), $d['sub'] ?? '', $img, $crumbs, $d['eyebrow'] ?? ( $parent ? ( $en ? 'Medellín from Haven' : 'Medellín desde Haven' ) : 'Haven · El Poblado' ) );

// Fecha de actualización y autor (confianza y E-E-A-T), visibles bajo la portada.
$upd = $d['updated'] ?? get_the_modified_date( 'Y-m-d' );
$ts  = strtotime( $upd );
$fmt = $en ? date( 'F j, Y', $ts ) : date_i18n( 'j \d\e F \d\e Y', $ts );
echo '<div class="lp-meta wrap"><span>' . e( $en ? 'By the Haven concierge' : 'Por el Concierge de Haven' ) . '</span><span aria-hidden="true">·</span><time datetime="' . ea( date( 'Y-m-d', $ts ) ) . '">' . e( ( $en ? 'Updated ' : 'Actualizado el ' ) . $fmt ) . '</time></div>';

$places = array();
foreach ( (array) ( hv_guia_data()['lugares'] ?? array() ) as $p ) $places[ $p['id'] ] = $p;
$rooms_by_key = array();
foreach ( hv_rooms() as $r ) $rooms_by_key[ hv_key( $r['id'] ) ] = $r;

$n = 0;
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
echo hv_bookbar();
get_footer();
