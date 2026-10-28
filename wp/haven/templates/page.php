<?php
/* Página genérica (sin clave Haven): encabezado del tema + contenido del editor (Gutenberg o Elementor). */
get_header();
$id = hv_current_id();
$d  = hv_data( $id );
if ( hv_is_builder_page( $id ) ) {
	echo '<main class="hv-builder" style="padding-top:110px">';
	while ( have_posts() ) { the_post(); the_content(); }
	echo '</main>';
} else {
	$img = $d['hero']['img'] ?? ( get_post_thumbnail_id( $id ) ?: '/wp-content/uploads/2026/06/hotel-haven-medellin.webp' );
	$h   = hv_hero_data( $d, array( 'img' => $img, 'sub' => has_excerpt( $id ) ? get_the_excerpt( $id ) : '' ) );
	echo hv_hero( $h['title'], $h['sub'], $h['img'], array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( get_the_title( $id ), '' ) ), $h['eyebrow'] );
	echo '<section class="sec" id="c"><div class="wrap" style="max-width:900px"><article class="prose" data-reveal>';
	while ( have_posts() ) { the_post(); the_content(); }
	echo '</article></div></section>';
}
get_footer();
