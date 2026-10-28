<?php
/* Guía Medellín (mapa + plan + quiz) y "¿Qué suite va contigo?" (quiz): mismas herramientas del prototipo, servidas por el tema. */
get_header();
$key  = hv_key();
$quiz = ( 'que-suite-elegir' === $key );
$d    = hv_data();
$def  = $quiz
	? array( 'title' => t( '¿Qué suite va <em>contigo</em>?' ), 'sub' => t( 'Cinco preguntas para encontrar el espacio de Haven hecho para tu viaje.' ), 'img' => '/wp-content/uploads/2026/06/presidential-suite-room-jacuzzi-lounge-area-1.webp', 'eyebrow' => t( 'Quiz · 30 segundos' ) )
	: array( 'title' => t( 'Medellín <em>desde Haven</em>' ), 'sub' => t( 'Todo lo que queda a pie desde El Poblado, un plan día por día hecho a tu medida y la suite que va contigo. Mueve, toca y arma tu viaje.' ), 'img' => 'theme:media/medellin-montanas.webp', 'eyebrow' => t( 'Guía interactiva' ) );
$h    = hv_hero_data( $d, $def );
$tags = $quiz ? array( t( '5 preguntas' ), t( '17 espacios' ) ) : array( t( '88 lugares' ), t( 'A pie' ), t( 'Plan a medida' ) );
echo hv_hero( $h['title'], $h['sub'], $h['img'], array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( $quiz ? t( '¿Qué suite va contigo?' ) : t( 'Guía Medellín' ), '' ) ), $h['eyebrow'], $d['hero']['tags'] ?? $tags );
echo '<div id="c" class="hg-host hg-host--' . ( $quiz ? 'quiz' : 'guia' ) . '">' . hv_guia_html( $quiz ? 'quiz' : 'guia' ) . '</div>';
get_footer();
