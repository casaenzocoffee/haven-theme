<?php
/* Servicios del hotel: tarjetas con solicitud por WhatsApp. */
get_header();
$d     = hv_data();
$secs  = (array) ( $d['sections'] ?? array() );
$intro = '';
foreach ( $secs as $s ) if ( ! empty( $s['paras'] ) ) { $intro = $s['paras'][0]; break; }
$h = hv_hero_data( $d, array( 'title' => t( 'Nuestros <em>servicios</em>' ), 'sub' => hv_excerpt( $intro, 150 ), 'img' => '/wp-content/uploads/2026/06/servicios-hotel-haven.webp' ) );
echo hv_hero( $h['title'], $h['sub'], $h['img'], array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( t( 'Servicios' ), '' ) ), $h['eyebrow'] );
?>
<section class="sec" id="c"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow"><?php echo e( t( 'A tu medida' ) ); ?></p><h2 style="margin-top:20px"><?php echo wp_kses_post( t( 'Todo en un <em>solo lugar</em>' ) ); ?></h2></div><p><?php echo e( $intro ); ?></p></div><div class="cards">
<?php foreach ( $secs as $s ) {
	if ( empty( $s['imgs'] ) || empty( $s['title'] ) || in_array( $s['title'], array( 'Servicios', 'Services' ), true ) ) continue;
	echo '<article class="card"><div class="card-media">' . hv_img( $s['imgs'][0] ) . '</div><div class="card-body"><h3>' . e( $s['title'] ) . '</h3><p>' . e( $s['paras'][0] ?? ( $s['eyebrow'] ?? '' ) ) . '</p><div class="card-actions">' . hv_btn( t( 'Solicitar' ), hv_wa( t( 'Hola, quiero información sobre ' ) . $s['title'] ), 'ghost', 'servicio', true ) . '</div></div></article>';
} ?>
</div></div></section>
<?php get_footer();
