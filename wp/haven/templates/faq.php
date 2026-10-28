<?php
/* Preguntas frecuentes: acordeón (el esquema FAQPage lo agrega inc/seo.php). */
get_header();
$d = hv_data();
$h = hv_hero_data( $d, array( 'title' => t( 'Preguntas <em>frecuentes</em>' ), 'sub' => t( 'Toda la información para disfrutar al máximo de tu estancia en El Poblado.' ), 'img' => '/wp-content/uploads/2026/06/preguntas-frecuentes.webp', 'eyebrow' => 'PQRS' ) );
echo hv_hero( $h['title'], $h['sub'], $h['img'], array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( t( 'Preguntas frecuentes' ), '' ) ), $h['eyebrow'] );
?>
<section class="sec" id="c"><div class="wrap" style="max-width:1000px"><div class="faq" data-reveal>
<?php foreach ( (array) ( $d['items'] ?? array() ) as $q ) {
	if ( empty( $q['q'] ) ) continue;
	echo '<details><summary>' . e( $q['q'] ) . '<i aria-hidden="true">+</i></summary><div class="a">';
	foreach ( (array) ( $q['a'] ?? array() ) as $p ) if ( is_string( $p ) && '' !== trim( $p ) ) echo '<p>' . e( $p ) . '</p>';
	echo '</div></details>';
} ?>
</div><div style="margin-top:50px;display:flex;gap:10px;flex-wrap:wrap"><?php echo hv_btn( t( 'Escribir por WhatsApp' ), hv_wa( t( 'Hola, tengo una pregunta' ) ), 'gold', 'faq_whatsapp', true ), hv_btn( t( 'Escribir al correo' ), 'mailto:frontdesk@serenahotelsco.com', 'ghost', 'faq_correo' ); ?></div></div></section>
<?php get_footer();
