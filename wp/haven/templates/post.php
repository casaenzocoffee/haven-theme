<?php
/* Artículo del blog: texto con índice, botones de reserva y más historias. */
get_header();
$id    = hv_current_id();
$d     = hv_data( $id );
$title = $d['title'] ?? get_the_title( $id );
$prose = (string) ( $d['prose'] ?? '' );
if ( '' === trim( $prose ) ) {
	$prose = apply_filters( 'the_content', get_post_field( 'post_content', $id ) );
	// Índice: da id a los h2 que no lo tengan.
	$prose = preg_replace_callback( '#<h2(?![^>]*\bid=)([^>]*)>(.*?)</h2>#s', function ( $m ) { return '<h2' . $m[1] . ' id="' . ea( sanitize_title( wp_strip_all_tags( $m[2] ) ) ) . '">' . $m[2] . '</h2>'; }, $prose );
}
preg_match_all( '#<h2[^>]*\bid="([^"]+)"[^>]*>(.*?)</h2>#s', $prose, $m, PREG_SET_ORDER );
$ex    = $d['ex'] ?? hv_excerpt( wp_strip_all_tags( get_the_excerpt( $id ) ), 90 );
$img   = $d['img'] ?? ( get_post_thumbnail_id( $id ) ?: '' );
$date  = $d['date'] ?? get_the_date( '', $id );
$short = mb_strlen( $title ) > 40 ? mb_substr( $title, 0, 40 ) . '…' : $title;
echo hv_hero( e( $title ), rtrim( $ex, '…' ) . '…', $img, array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( 'Blog', hv_url( 'blogs' ) ), array( $short, '' ) ), $date );
?>
<section class="sec" id="c"><div class="wrap doc-grid"><aside class="toc"><p class="eyebrow"><?php echo e( t( 'En este artículo' ) ); ?></p><ol><?php foreach ( $m as $x ) echo '<li><a href="#' . ea( $x[1] ) . '">' . e( wp_strip_all_tags( $x[2] ) ) . '</a></li>'; ?></ol></aside>
<article class="prose" data-reveal><?php echo wp_kses_post( $prose ); ?><div style="margin-top:50px;display:flex;gap:10px;flex-wrap:wrap"><?php echo hv_btn( t( 'Reservar en Haven' ), '#reservar', 'gold', 'blog_reservar' ), hv_btn( t( 'Volver al blog' ), hv_url( 'blogs' ), 'ghost' ); ?></div></article></div></section>
<?php $others = hv_posts( $id ); if ( $others ) { ?>
<section class="sec" style="padding-top:0"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow"><?php echo e( t( 'Sigue leyendo' ) ); ?></p><h2 style="margin-top:20px"><?php echo wp_kses_post( t( 'Más <em>historias</em>' ) ); ?></h2></div></div><div class="cards"><?php foreach ( $others as $p ) echo hv_post_card( $p ); ?></div></div></section>
<?php }
echo hv_bookbar();
get_footer();
