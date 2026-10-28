<?php
/* Páginas legales (Política de visitantes, Términos y condiciones): texto con índice lateral. */
get_header();
$d     = hv_data();
$h     = hv_hero_data( $d, array( 'img' => '/wp-content/uploads/2026/06/hotel-haven-medellin.webp' ) );
$prose = (string) ( $d['prose'] ?? '' );
if ( '' === trim( $prose ) ) $prose = apply_filters( 'the_content', get_post_field( 'post_content', hv_current_id() ) );
preg_match_all( '#<h2 id="([^"]+)">(.*?)</h2>#s', $prose, $m, PREG_SET_ORDER );
echo hv_hero( $h['title'], $h['sub'], $h['img'], array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( $d['crumb'] ?? get_the_title(), '' ) ), $h['eyebrow'] );
?>
<section class="sec" id="c"><div class="wrap doc-grid">
<?php if ( count( $m ) > 2 ) {
	echo '<aside class="toc"><p class="eyebrow">' . e( t( 'Contenido' ) ) . '</p><ol>';
	foreach ( $m as $x ) echo '<li><a href="#' . ea( $x[1] ) . '">' . wp_strip_all_tags( $x[2] ) . '</a></li>';
	echo '</ol></aside>';
} else { echo '<div></div>'; } ?>
<article class="prose" data-reveal><?php echo wp_kses_post( $prose ); ?></article></div></section>
<?php get_footer();
