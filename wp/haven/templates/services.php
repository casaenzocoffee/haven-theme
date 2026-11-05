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
<?php
// Cada servicio: nombre (antetítulo), descripción (título) y foto. En los datos migrados de Elementor la foto de cada
// servicio quedó en la sección anterior, así que se toma de ahí (corrige fotos corridas; 5 nov 2026).
// Menús: foto real del hotel y botón a su carta. Resto: símbolo propio de Haven (arco + ícono + sello) en vez de foto de banco.
$prev = null;
foreach ( $secs as $s ) {
	$img_prev = $prev['imgs'][0] ?? '';
	$prev = $s;
	$name = trim( (string) ( $s['eyebrow'] ?? '' ) );
	$desc = trim( (string) ( $s['title'] ?? '' ) );
	if ( '' === $name || '' === $desc || in_array( $desc, array( 'Servicios', 'Services' ), true ) || in_array( $name, array( 'Nuestros', 'Our' ), true ) ) continue;
	$kind = hv_service_kind( $name );
	$media = ( in_array( $kind, array( 'm360', 'mdesayuno', 'mswing' ), true ) && $img_prev )
		? hv_img( $img_prev, array( 'alt' => $name . ' · Haven, El Poblado' ) )
		: hv_service_icon( $kind );
	$btn = 'm360' === $kind ? hv_btn( hv_en() ? 'See the menu' : 'Ver la carta', hv_menu_360(), 'gold', 'servicio_carta_360', true )
		: ( 'mswing' === $kind ? hv_btn( hv_en() ? 'See the menu' : 'Ver la carta', hv_menu_swing(), 'gold', 'servicio_carta_swing', true )
		: hv_btn( t( 'Solicitar' ), hv_wa( t( 'Hola, quiero información sobre ' ) . $name ), 'ghost', 'servicio_' . $kind, true ) );
	echo '<article class="card sv-card"><div class="card-media' . ( 0 === strpos( $media, '<div' ) ? ' sv-media' : '' ) . '">' . $media . '</div><div class="card-body"><p class="eyebrow">' . e( hv_en() ? 'Service' : 'Servicio' ) . '</p><h3>' . e( $name ) . '</h3><p>' . e( $desc ) . '</p><div class="card-actions">' . $btn . '</div></div></article>';
} ?>
</div></div></section>
<?php get_footer();
