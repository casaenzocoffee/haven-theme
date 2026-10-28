<?php
/* Ficha de habitación: datos, comodidades, galería con visor y otras habitaciones. */
get_header();
$r    = hv_data();
$r['url'] = get_permalink();
$name = $r['name'] ?? get_the_title();
$cat  = $r['cat'] ?? '';
$imgs = array_values( array_filter( (array) ( $r['imgs'] ?? array() ) ) );
$num  = function ( $s ) { return preg_match( '/(\d+)/', (string) $s, $m ) ? $m[1] : ''; };
$specs = '';
if ( ! empty( $r['m2'] ) )   $specs .= '<div><b data-n="' . ea( $num( $r['m2'] ) ) . '" data-u=" m²">' . e( $r['m2'] ) . '</b><span>' . e( t( 'Área' ) ) . '</span></div>';
if ( ! empty( $r['per'] ) )  $specs .= '<div><b data-n="' . ea( $num( $r['per'] ) ) . '">' . e( $num( $r['per'] ) ) . '</b><span>' . e( t( 'Personas' ) ) . '</span></div>';
if ( ! empty( $r['beds'] ) ) $specs .= '<div><b>' . e( implode( ' · ', (array) $r['beds'] ) ) . '</b><span>' . e( t( 'Camas' ) ) . '</span></div>';
if ( ! empty( $r['dup'] ) )  $specs .= '<div><b>' . e( $r['dup'] ) . '</b><span>' . e( t( 'Formato' ) ) . '</span></div>';
$title = e( $name );
foreach ( array( 'Suite ', 'Habitación ' ) as $w ) if ( 0 === strpos( $title, $w ) ) { $title = $w . '<em>' . substr( $title, strlen( $w ) ) . '</em>'; break; }
if ( hv_en() && preg_match( '#^(.+) (Room|Suite)( \d+)?$#u', $name ) ) { $title = preg_replace( '#^(.+?) (Room|Suite)(.*)$#u', '$1 <em>$2$3</em>', e( $name ) ); }
$desc0 = $r['desc'][0] ?? '';
$sub   = mb_strlen( $desc0 ) > 150 ? mb_substr( mb_substr( $desc0, 0, 150 ), 0, mb_strrpos( mb_substr( $desc0, 0, 150 ), ' ' ) ) . '…' : $desc0;
echo hv_hero( $title, $sub, ( $r['cover'] ?? '' ) ?: ( $imgs[0] ?? '' ), hv_crumbs_rooms( $name ), ( $r['kicker'] ?? '' ) ?: $cat, array_filter( array( $r['m2'] ?? '', $r['per'] ?? '' ) ) );
$others = array();
foreach ( hv_rooms() as $o ) if ( (int) $o['id'] !== hv_current_id() && ( $o['f'] ?? '' ) === ( $r['f'] ?? '' ) ) $others[] = $o;
if ( ! $others ) foreach ( hv_rooms() as $o ) if ( (int) $o['id'] !== hv_current_id() ) $others[] = $o;
$others = array_slice( $others, 0, 3 );
$slug = $r['slug_es'] ?? get_post_field( 'post_name' );
?>
<section class="ed" id="c"><div class="wrap">
  <div class="ed-grid">
    <div data-reveal><p class="eyebrow"><?php echo e( $cat ); ?></p><h2><?php echo e( $name ); ?></h2><?php foreach ( (array) ( $r['desc'] ?? array() ) as $p ) echo '<p>' . e( $p ) . '</p>'; ?>
      <div style="margin-top:34px;display:flex;gap:10px;flex-wrap:wrap"><?php echo hv_btn( t( 'Reservar ahora' ), '#reservar', 'gold', 'reservar_' . $slug ), hv_btn( 'WhatsApp', hv_wa( t( 'Hola, quiero información sobre ' ) . $name ), 'ghost', 'whatsapp_' . $slug, true ); ?></div></div>
    <div class="arch-wrap"><?php echo hv_stamp(); ?><div class="arch"><?php echo hv_img( $imgs[1] ?? ( $imgs[0] ?? '' ), array( 'data-speed' => '.08' ) ); ?></div><?php if ( isset( $imgs[2] ) ) echo '<div class="arch-float">' . hv_img( $imgs[2] ) . '</div>'; ?></div>
  </div>
  <div class="rd-specs" data-reveal><?php echo $specs; ?></div>
</div></section>
<section class="sec cream"><div class="wrap">
  <div class="sec-head" data-reveal><div><p class="eyebrow"><?php echo e( t( 'Comodidades' ) ); ?></p><h2 style="margin-top:20px"><?php echo wp_kses_post( t( 'Todo lo que <em style="color:#8a6f33">necesitas</em>' ) ); ?></h2></div><?php foreach ( (array) ( $r['serv'] ?? array() ) as $p ) echo '<p>' . e( $p ) . '</p>'; ?></div>
  <ul class="amen amen-cream" data-reveal><?php foreach ( (array) ( $r['amen'] ?? array() ) as $a ) echo '<li>' . hv_icon( $a ) . e( $a ) . '</li>'; ?></ul>
  <div style="margin-top:34px"><?php echo hv_btn( t( 'Conoce nuestros servicios' ), hv_url( 'servicios' ), 'ghost' ); ?></div>
</div></section>
<section class="sec"><div class="wrap">
  <div class="sec-head" data-reveal><div><p class="eyebrow"><?php echo e( t( 'Galería · ' ) . count( $imgs ) . ' ' . t( 'fotos' ) ); ?></p><h2 style="margin-top:20px"><?php echo wp_kses_post( false !== stripos( $name, 'suite' ) ? t( 'Recorre la <em>suite</em>' ) : t( 'Recorre la <em>habitación</em>' ) ); ?></h2></div><p><?php echo e( t( 'Toca cualquier foto para verla en grande.' ) ); ?></p></div>
  <div class="mosaic"><?php foreach ( $imgs as $i => $u ) echo '<a href="' . esc_url( hv_img_url( $u ) ) . '">' . hv_img( $u, array( 'alt' => $name . ' — ' . t( 'foto' ) . ' ' . ( $i + 1 ) ) ) . '</a>'; ?></div>
</div></section>
<section class="sec" style="padding-top:0"><div class="wrap">
  <div class="sec-head" data-reveal><div><p class="eyebrow"><?php echo e( t( 'También te puede gustar' ) ); ?></p><h2 style="margin-top:20px"><?php echo wp_kses_post( t( 'Otros <em>espacios</em>' ) ); ?></h2></div><?php echo hv_btn( t( 'Ver todas' ), hv_url( 'habitaciones' ), 'ghost' ); ?></div>
  <div class="cards"><?php foreach ( $others as $o ) echo hv_room_card( $o ); ?></div>
</div></section>
<?php
echo hv_bookbar( $name . ' ' . t( '· reserva directa' ) );
get_footer();
