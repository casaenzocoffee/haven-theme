<?php
/* Todas las habitaciones: filtros por categoría y tarjetas. */
get_header();
$d     = hv_data();
$h     = hv_hero_data( $d, array( 'title' => t( 'Todas las <em>habitaciones</em>' ), 'sub' => t( 'Diecisiete espacios decorados y amoblados de forma individual, del penthouse dúplex a la habitación esencial.' ), 'img' => '/wp-content/uploads/2026/06/presidential-suite-room-jacuzzi-lounge-area-1.webp' ) );
$tags  = $d['hero']['tags'] ?? array( t( '17 habitaciones' ), 'Jacuzzi', t( 'Desayuno incluido' ) );
$rooms = hv_rooms();
$gal   = array();
foreach ( $rooms as $r ) if ( ! empty( $r['imgs'][1] ) ) $gal[] = $r['imgs'][1];
echo hv_hero( $h['title'], $h['sub'], $h['img'], array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( t( 'Habitaciones' ), '' ) ), $h['eyebrow'], $tags );
$F = array( 'penthouse' => t( 'Penthouse' ), 'presidencial' => t( 'Presidencial' ), 'suites' => t( 'Suites de lujo' ), 'habitaciones' => t( 'Habitaciones' ), 'all' => t( 'Ver todas' ) );
?>
<section class="sec" id="c"><div class="wrap">
  <div class="sec-head" data-reveal><div><p class="eyebrow"><?php echo e( $d['eyebrow'] ?? t( 'Descanso con estilo' ) ); ?></p><h2 style="margin-top:20px"><?php echo wp_kses_post( $d['heading'] ?? t( 'Elige tu <em>habitación</em>' ) ); ?></h2></div><p><?php echo e( $d['intro'] ?? '' ); ?></p></div>
  <div class="filters" data-target="#rooms" role="group" aria-label="<?php echo ea( t( 'Filtrar' ) ); ?>">
    <?php $i = 0; foreach ( $F as $k => $label ) { echo '<button type="button" data-f="' . ea( $k ) . '" aria-pressed="' . ( 0 === $i ? 'true' : 'false' ) . '">' . e( $label ) . '</button>'; $i++; } ?>
  </div>
  <div class="cards" id="rooms">
<?php foreach ( $rooms as $r ) echo hv_room_card( $r, 'penthouse' !== ( $r['f'] ?? '' ) ); ?>
  </div>
</div></section>
<?php
echo hv_bigq( 'Haven · A Design Hotel', $d['quote'] ?? t( 'Cada espacio está decorado y amoblado de forma individual.' ) );
echo hv_hgallery( t( 'Galería' ), $d['gal_title'] ?? t( 'Detalles que <em>se sienten</em>' ), array_slice( $gal, 0, 10 ) );
echo hv_bookbar();
get_footer();
