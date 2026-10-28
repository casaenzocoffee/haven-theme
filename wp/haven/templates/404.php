<?php
/* Página no encontrada. */
status_header( 404 );
get_header();
echo hv_hero( t( 'Página no <em>encontrada</em>' ), t( 'Esta página no existe o cambió de dirección.' ), '/wp-content/uploads/2026/06/hotel-haven-medellin.webp', array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( '404', '' ) ), 'Haven · Medellín' );
?>
<section class="sec" id="c"><div class="wrap"><div style="display:flex;gap:10px;flex-wrap:wrap"><?php echo hv_btn( t( 'Volver al inicio' ), hv_url( 'home' ), 'gold', '404_inicio' ), hv_btn( t( 'Ver habitaciones' ), hv_url( 'habitaciones' ), 'ghost', '404_habitaciones' ), hv_btn( t( 'Contacto' ), hv_url( 'contacto' ), 'ghost', '404_contacto' ); ?></div></div></section>
<?php get_footer();
