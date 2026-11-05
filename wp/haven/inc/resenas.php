<?php
/**
 * Reseñas de Google en la portada (5 nov 2026, elegidas por el gerente): [haven_resenas].
 * Son reseñas reales de 5 estrellas de la ficha "Haven, A Design Hotel" en Google Maps, citadas en su idioma original
 * (fragmentos marcados con …), con nombre corto del autor y enlace a la ficha de Google para verlas todas o dejar una.
 * Para cambiarlas: editar HV_RESENAS. La calificación y el total se ajustan con hv_resenas_total().
 */
defined( 'ABSPATH' ) || exit;

const HV_GOOGLE_PLACE = 'ChIJdYV0FpopRI4RyykZ15R9GWw';

function hv_resenas_lista() {
	return array(
		array( 'n' => 'Luis C.', 'f' => '2026-07', 'l' => 'en', 't' => "Over the span of four years, I've returned to Haven Hotel time and time again — and every single visit has reinforced why this is my go-to property in Medellín. That kind of loyalty doesn't happen by accident; it's earned." ),
		array( 'n' => 'Juan M.', 'f' => '2026-08', 'l' => 'es', 't' => 'La atención es impecable y de primer nivel, el personal es de los mejores que he visto y el lugar y la comida los igualan, 1000% recomendado.' ),
		array( 'n' => 'Fabio G.', 'f' => '2026-03', 'l' => 'en', 't' => 'A true gem in the heart of El Poblado. Haven Hotel is the perfect choice for those looking for design, comfort, and attention to detail… Everyone is extremely kind, helpful, and welcoming, making you feel at home from the very first moment.' ),
		array( 'n' => 'Tanya R.', 'f' => '2026-04', 'l' => 'es', 't' => 'Es una buenísima opción para visitar y hospedarse, está céntrico para visitar y conocer Medellín, la atención que da el personal 1000/10, a nosotros nos atendió Camilo y es excepcional.' ),
		array( 'n' => 'Myriam L.', 'f' => '2026-03', 'l' => 'en', 't' => 'We had a wonderful stay at Hotel Haven A Design in Medellín! From the start, the staff made us feel welcome, especially David, who went above and beyond… Breakfast was excellent, with a great variety of options and really good coffee.' ),
	);
}

/** Calificación y número de reseñas en Google (revisado el 5 nov 2026). */
function hv_resenas_total() { return array( 'r' => '4.8', 'n' => 2105 ); }

function hv_resenas_url( $write = false ) {
	return 'https://search.google.com/local/' . ( $write ? 'writereview' : 'reviews' ) . '?placeid=' . HV_GOOGLE_PLACE;
}

add_shortcode( 'haven_resenas', function () {
	$en  = 'en' === hv_lang();
	$tot = hv_resenas_total();
	$mes = $en ? array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ) : array( 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic' );
	$g   = '<svg class="rv-g" viewBox="0 0 48 48" width="18" height="18" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>';
	$h   = '<div class="rv" data-reveal>';
	foreach ( hv_resenas_lista() as $r ) {
		list( $y, $m ) = array_map( 'intval', explode( '-', $r['f'] ) );
		$h .= '<a class="rv-card" href="' . esc_url( hv_resenas_url() ) . '" target="_blank" rel="noopener" lang="' . esc_attr( $r['l'] ) . '">'
			. '<span class="rv-top"><span class="stars" aria-label="' . esc_attr( $en ? '5 stars' : '5 estrellas' ) . '">★★★★★</span>' . $g . '</span>'
			. '<blockquote>“' . esc_html( $r['t'] ) . '”</blockquote>'
			. '<span class="rv-by"><i aria-hidden="true">' . esc_html( mb_substr( $r['n'], 0, 1 ) ) . '</i><span><b>' . esc_html( $r['n'] ) . '</b>' . esc_html( ( $en ? 'Google review · ' : 'Reseña en Google · ' ) . $mes[ $m - 1 ] . ' ' . $y ) . '</span></span>'
			. '</a>';
	}
	$h .= '</div><div class="rv-cta">'
		. '<a class="btn btn-gold" href="' . esc_url( hv_resenas_url() ) . '" target="_blank" rel="noopener">' . $g . esc_html( sprintf( $en ? 'See all %s reviews on Google' : 'Ver las %s reseñas en Google', number_format( $tot['n'], 0, '', $en ? ',' : '.' ) ) ) . '</a>'
		. '<a class="link" href="' . esc_url( hv_resenas_url( true ) ) . '" target="_blank" rel="noopener">' . esc_html( $en ? 'Leave a review' : 'Dejar una reseña' ) . '</a>'
		. '</div>';
	return $h;
} );
