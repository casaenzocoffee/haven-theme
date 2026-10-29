<?php
/* Gastronomía en el 360° Rooftop & Restaurante: platos, carta en línea y reserva de mesa. */
get_header();
$d    = hv_data();
$h    = hv_hero_data( $d, array( 'title' => t( '<em>Gastronomía</em>' ), 'sub' => t( 'Cocina local e internacional con vista a la ciudad, en el 360° Rooftop del piso 11.' ), 'img' => '/wp-content/uploads/2026/07/burrata-pizza.webp', 'eyebrow' => 'Haven · 360° Rooftop' ) );
$secs = (array) ( $d['sections'] ?? array() );
$imgs = array();
foreach ( $secs as $s ) $imgs = array_merge( $imgs, (array) ( $s['imgs'] ?? array() ) );
$imgs = array_values( array_filter( $imgs ) );
$dishes = '';
$ps = array();
foreach ( $secs as $s ) {
	if ( empty( $s['paras'] ) ) continue;
	$dishes .= '<div class="dish"><h3>' . e( $s['title'] ?? '' ) . '</h3><span class="tag">' . e( $s['eyebrow'] ?? '' ) . '</span><p>' . e( $s['paras'][0] ) . '</p></div>';
	if ( count( $ps ) < 2 ) $ps[] = $s['paras'][0];
}
$menu  = ( $d['menu_url'] ?? '' ) ?: hv_menu_360();
$table = $d['table_url'] ?? 'https://fiweex.com/reservas_portal_bienvenida/aIJzgQ%3D%3D';
echo hv_hero( $h['title'], $h['sub'], $h['img'], array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( t( 'Gastronomía' ), '' ) ), $h['eyebrow'] );
echo hv_editorial( t( 'Platos recomendados' ), t( 'Sabores que <em>inspiran</em>' ), $ps, $imgs[0] ?? '', $imgs[1] ?? null, false, false, hv_btn( t( 'Ver menú digital' ), $menu, 'gold', 'menu_digital', true ) . hv_btn( t( 'Reservar mesa' ), $table, 'ghost', 'reservar_mesa', true ) );
?>
<section class="sec cream"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow"><?php echo e( t( 'La carta' ) ); ?></p><h2 style="margin-top:20px"><?php echo wp_kses_post( t( 'Lo más <em style="color:#8a6f33">pedido</em>' ) ); ?></h2></div><p><?php echo e( t( 'Una selección de la carta del 360° Rooftop. El menú completo está en línea.' ) ); ?></p></div><div class="menu-cols" data-reveal><?php echo $dishes; ?></div><div style="margin-top:40px;display:flex;gap:10px;flex-wrap:wrap"><?php echo hv_btn( t( 'Ver platos recomendados' ), hv_menu_360(), 'gold', 'platos_recomendados', true ), hv_btn( t( 'Ver platos fuertes' ), hv_menu_360(), 'ghost', 'platos_fuertes', true ); ?></div></div></section>
<?php
echo hv_hgallery( t( 'Galería' ), t( 'Para <em>compartir</em>' ), array_merge( $imgs, array( '/wp-content/uploads/2026/06/hotel-haven-rooftop-360-gastronomy-2.webp', '/wp-content/uploads/2026/06/hotel-haven-rooftop-360-gastronomy-cocktails-1.webp', '/wp-content/uploads/2026/06/hotel-haven-rooftop-360-gastronomy-cocktails-2.webp' ) ) );
echo hv_bigq( '360° Rooftop', t( 'Déjese llevar por un viaje culinario inolvidable mientras disfruta de las vistas.' ) );
get_footer();
