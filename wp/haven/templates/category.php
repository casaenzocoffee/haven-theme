<?php
/* Categoría (Penthouse, Presidencial, Suites de lujo): editorial + tarjetas + galería. */
get_header();
$d     = hv_data();
$f     = $d['f'] ?? '';
$cat   = $d['cat'] ?? get_the_title();
$rooms = array_values( array_filter( hv_rooms(), function ( $r ) use ( $f ) { return ( $r['f'] ?? '' ) === $f; } ) );
$gal   = array();
foreach ( $rooms as $r ) $gal = array_merge( $gal, array_slice( (array) $r['imgs'], 0, 4 ) );
$intro = $d['intro'] ?? '';
$h     = hv_hero_data( $d, array( 'title' => $d['title_html'] ?? e( $cat ), 'sub' => hv_excerpt( $intro, 160 ), 'img' => $d['img'] ?? '' ) );
echo hv_hero( $h['title'], $h['sub'], $h['img'], hv_crumbs_rooms( $cat ), $h['eyebrow'] );
echo hv_editorial( $cat, $d['title_html'] ?? e( $cat ), array( $intro ), $gal[0] ?? $h['img'], $gal[2] ?? null, false, true );
?>
<section class="sec"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow"><?php echo e( $cat ); ?></p><h2 style="margin-top:20px"><?php echo wp_kses_post( t( 'Elige tu <em>espacio</em>' ) ); ?></h2></div></div><div class="cards"><?php foreach ( $rooms as $r ) echo hv_room_card( $r ); ?></div></div></section>
<?php
echo hv_hgallery( t( 'Galería' ), e( $cat ) . ' <em>' . e( t( 'en detalle' ) ) . '</em>', array_slice( $gal, 0, 10 ) );
echo hv_bookbar();
get_footer();
