<?php
/**
 * Preguntas frecuentes cortas en la portada (5 nov 2026, pedido del gerente, al estilo de Viktory).
 * Shortcode [haven_faq_portada]; en partials/home.html va al final, después de "¿Por qué Haven?".
 * Solo datos ya publicados en el sitio (políticas vigentes, fichas y páginas por intención). Para cambiar: editar hv_faq_portada().
 * El esquema FAQPage de la portada lo agrega este mismo archivo.
 */
defined( 'ABSPATH' ) || exit;

function hv_faq_portada( $en = null ) {
	$en = null === $en ? hv_en() : $en;
	if ( $en ) return array(
		array( 'Where is Haven?', 'At Calle 10A #36-29, in El Poblado, Medellín. Provenza and Parque Lleras are a two-minute walk away.' ),
		array( 'Which rooms have a jacuzzi?', 'Twelve of them: the Lord, Viscount, Viscount 803, Ambassador and Ambassador Jr rooms; the Excelsior 401, 604 and 804 and Imperial 402 suites; the Emperor 901 and 902 penthouse suites and the Presidential Suite. The Viscount 803 and the Excelsior 804 have it outdoors, on their private balcony.' ),
		array( 'Is there a pool?', 'Yes. The pool is heated and sits on the 11th floor, at the 360° Rooftop & Restaurant, with a 360° view of the city. Guests have access during their stay.' ),
		array( 'Which suite works for a group?', 'The Emperor 901 and 902 penthouse suites and the Presidential Suite each have four bedrooms and sleep up to eight. The Excelsior suites sleep four and the Imperial 402 sleeps six.' ),
		array( 'What are the check-in and check-out times?', 'Check-in is from 3:00 p.m. and check-out is until 11:00 a.m. Early check-in is subject to availability.' ),
		array( 'Are pets allowed?', 'Yes, pets are welcome at no extra charge. Any damage they cause to the property is the guest\'s responsibility.' ),
		array( 'What is included in my stay?', 'Buffet breakfast, Wi-Fi, daily housekeeping and access to the heated rooftop pool.' ),
		array( 'Can I smoke at the hotel?', 'Only in the pool area on the 11th floor, at the 360° Rooftop & Restaurant. Smoking anywhere else carries a USD 100 fine.' ),
		array( 'Do you offer airport transfers?', 'Yes. The drive from José María Córdova airport takes about 40 minutes, depending on traffic, and the front desk can arrange your transfer.' ),
		array( 'Where should I book?', 'Right here, with the "Book now" button, or by WhatsApp if you would rather talk to us first.' ),
	);
	return array(
		array( '¿Dónde queda Haven?', 'En la Calle 10A #36-29, en El Poblado, Medellín. Provenza y el Parque Lleras quedan a dos minutos a pie.' ),
		array( '¿Qué habitaciones tienen jacuzzi?', 'Doce: las habitaciones Lord, Viscount, Viscount 803, Ambassador y Ambassador Jr; las suites Excelsior 401, 604 y 804 e Imperial 402; las Emperor 901 y 902 del penthouse y la Suite Presidencial. La Viscount 803 y la Excelsior 804 lo tienen al aire libre, en su balcón privado.' ),
		array( '¿Hay piscina?', 'Sí. Es climatizada y está en el piso 11, en el 360° Rooftop & Restaurante, con vista de 360° de la ciudad. Los huéspedes tienen acceso durante su estadía.' ),
		array( '¿Qué suite sirve para un grupo?', 'Las Emperor 901 y 902 del penthouse y la Suite Presidencial tienen cuatro habitaciones cada una y reciben hasta ocho personas. Las suites Excelsior reciben cuatro y la Imperial 402, seis.' ),
		array( '¿A qué hora es el check-in y el check-out?', 'El check-in es desde las 3:00 p. m. y el check-out hasta las 11:00 a. m. El early check-in está sujeto a disponibilidad.' ),
		array( '¿Aceptan mascotas?', 'Sí, las mascotas son bienvenidas sin costo adicional. Cualquier daño que causen a la propiedad es responsabilidad del huésped.' ),
		array( '¿Qué incluye la estadía?', 'Desayuno bufé, wifi, limpieza diaria y acceso a la piscina climatizada del rooftop.' ),
		array( '¿Se puede fumar en el hotel?', 'Solo en la zona de la piscina del piso 11, en el 360° Rooftop & Restaurante. Fumar en cualquier otra zona tiene una multa de USD 100.' ),
		array( '¿Tienen traslado desde el aeropuerto?', 'Sí. El trayecto desde el aeropuerto José María Córdova toma unos 40 minutos según el tráfico, y la recepción coordina su traslado.' ),
		array( '¿Dónde conviene reservar?', 'Aquí mismo, con el botón "Reservar ahora", o por WhatsApp si prefiere hablar antes con nosotros.' ),
	);
}

add_shortcode( 'haven_faq_portada', function () {
	$en = hv_en();
	$h  = '';
	foreach ( hv_faq_portada( $en ) as $q ) {
		$h .= '<details><summary>' . esc_html( $q[0] ) . '<i aria-hidden="true">+</i></summary><div class="a"><p>' . esc_html( $q[1] ) . '</p></div></details>';
	}
	return '<section class="sec fq-home" aria-labelledby="fq-title"><div class="wrap" style="max-width:1000px">'
		. '<div class="sec-head" data-reveal><div><p class="eyebrow">' . esc_html( $en ? 'FAQ' : 'Preguntas' ) . '</p><h2 id="fq-title" style="margin-top:20px">' . ( $en ? 'Frequently asked <em>questions</em>' : 'Preguntas <em>frecuentes</em>' ) . '</h2></div></div>'
		. '<div class="faq" data-reveal>' . $h . '</div>'
		. '<div class="fq-more" data-reveal>'
		. hv_btn( $en ? 'Ask us on WhatsApp' : 'Preguntar por WhatsApp', hv_wa( $en ? 'Hello, I have a question' : 'Hola, tengo una pregunta' ), 'gold', 'faq_home_whatsapp', true )
		. hv_btn( $en ? 'See all questions' : 'Ver todas las preguntas', hv_url( 'preguntas-frecuentes' ), 'ghost', 'faq_home_todas' )
		. '</div></div></section>';
} );

/* Esquema FAQPage de la portada (mismas preguntas que se ven). */
add_action( 'wp_head', function () {
	if ( 'home' !== hv_template_key() ) return;
	$items = array();
	foreach ( hv_faq_portada() as $q ) $items[] = array( '@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $q[1] ) );
	hv_ld( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $items ) );
}, 9 );
