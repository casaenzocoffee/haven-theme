<?php
/* Piezas del diseño (portadas de sección, bloques editoriales, galerías, tarjetas…), igual que el prototipo. */
defined( 'ABSPATH' ) || exit;

const HV_ARROW = '<span class="arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>';
const HV_OUT   = '<span class="arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17 17 7M9 7h8v8"/></svg></span>';
const HV_IG    = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.4" cy="6.6" r="1" fill="currentColor" stroke="none"/></svg>';
const HV_TT    = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.6 3c.4 2.1 1.7 3.6 3.9 3.8v3.1c-1.4 0-2.7-.4-3.9-1.1v6.4c0 3.3-2.6 5.8-5.9 5.8S4.8 18.5 4.8 15.2s2.7-5.9 6-5.8c.3 0 .6 0 .9.1v3.2a2.8 2.8 0 0 0-3.8 2.6 2.8 2.8 0 0 0 5.6 0V3h3.1Z"/></svg>';
const HV_FB    = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.6h2.6l.4-3h-3V8.5c0-.9.3-1.5 1.5-1.5h1.6V4.3c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4V10.4H7.8v3h2.6V21h3.1Z"/></svg>';

function hv_stamp() {
	static $n = 0; $n++;
	return '<svg class="arch-stamp" viewBox="0 0 120 120" aria-hidden="true"><defs><path id="cp' . $n . '" d="M60 60m-46 0a46 46 0 1 1 92 0a46 46 0 1 1-92 0"/></defs><text font-family="DM Sans" font-size="10.5" letter-spacing="4.6" fill="currentColor"><textPath href="#cp' . $n . '">HAVEN · A DESIGN HOTEL · MEDELLÍN · </textPath></text><circle cx="60" cy="60" r="4" fill="currentColor"/></svg>';
}

function hv_btn( $text, $href, $kind = 'gold', $track = '', $ext = false ) {
	$t = $track ? ' data-track="' . ea( $track ) . '"' : '';
	$x = $ext ? ' target="_blank" rel="noopener"' : '';
	$href = hv_href( $href );
	return '<a class="btn btn-' . ea( $kind ) . '" href="' . esc_url( $href ) . '"' . $t . $x . '>' . e( $text ) . ' ' . ( $ext ? HV_OUT : HV_ARROW ) . '</a>';
}

/** Portada de sección. $crumbs: [[texto, url|''], …] */
function hv_hero( $title_html, $sub, $img, $crumbs, $eyebrow = '', $tags = array() ) {
	$cr = array();
	foreach ( $crumbs as $c ) $cr[] = $c[1] ? '<a href="' . esc_url( $c[1] ) . '">' . e( $c[0] ) . '</a>' : '<span>' . e( $c[0] ) . '</span>';
	$tg = '';
	foreach ( (array) $tags as $x ) if ( $x ) $tg .= '<span class="chip">' . e( $x ) . '</span>';
	ob_start(); ?>
<section class="ph">
  <div class="ph-media"><?php echo hv_img( $img, array( 'fetchpriority' => 'high', 'sizes' => '(max-width: 820px) 256px, 100vw' ) ); ?></div>
  <div class="wrap">
    <nav class="crumbs" aria-label="<?php echo ea( t( 'Ruta' ) ); ?>"><?php echo implode( ' <span aria-hidden="true">/</span> ', $cr ); ?></nav>
    <?php if ( $eyebrow ) echo '<p class="eyebrow">' . e( $eyebrow ) . '</p>'; ?>
    <h1 class="split"><?php echo wp_kses_post( $title_html ); ?></h1>
    <div class="ph-row"><p class="ph-sub"><?php echo e( $sub ); ?></p><div class="ph-tags"><?php echo $tg; ?></div><a class="ph-cue" href="#c" aria-label="<?php echo ea( t( 'Bajar' ) ); ?>"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 5v14M6 13l6 6 6-6"/></svg></a></div>
  </div>
</section>
<?php return ob_get_clean();
}

function hv_crumbs_rooms( $last ) {
	return array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( t( 'Habitaciones' ), hv_url( 'habitaciones' ) ), array( $last, '' ) );
}

/** Bloque editorial con foto en arco. */
function hv_editorial( $eyebrow, $title_html, $paras, $img, $img2 = null, $flip = false, $cream = false, $cta = '', $id = 'c' ) {
	$ps = '';
	foreach ( (array) $paras as $p ) $ps .= '<p>' . e( $p ) . '</p>';
	$fl = $img2 ? '<div class="arch-float">' . hv_img( $img2 ) . '</div>' : '';
	ob_start(); ?>
<section class="ed<?php echo $cream ? ' cream' : ''; ?>"<?php echo $id ? ' id="' . ea( $id ) . '"' : ''; ?>>
  <div class="wrap ed-grid<?php echo $flip ? ' flip' : ''; ?>">
    <div data-reveal><p class="eyebrow"><?php echo e( $eyebrow ); ?></p><h2><?php echo wp_kses_post( $title_html ); ?></h2><?php echo $ps; ?><?php if ( $cta ) echo '<div style="margin-top:34px;display:flex;gap:10px;flex-wrap:wrap">' . $cta . '</div>'; ?></div>
    <div class="arch-wrap"><?php echo hv_stamp(); ?><div class="arch"><?php echo hv_img( $img, array( 'data-speed' => '.08' ) ); ?></div><?php echo $fl; ?></div>
  </div>
</section>
<?php return ob_get_clean();
}

/** Galería horizontal anclada. */
function hv_hgallery( $eyebrow, $title_html, $imgs ) {
	$imgs = array_values( array_filter( (array) $imgs ) );
	if ( ! $imgs ) return '';
	$items = '';
	foreach ( $imgs as $u ) $items .= '<figure class="hgal-item">' . hv_img( $u, array( 'sizes' => '(max-width: 820px) 256px, 40vw' ) ) . '</figure>';
	return '<section class="hgal"><div class="hgal-pin"><div class="wrap hgal-head"><div><p class="eyebrow">' . e( $eyebrow ) . '</p><h2 style="margin-top:18px">' . wp_kses_post( $title_html ) . '</h2></div><p class="hgal-count"><b>01</b>/ ' . sprintf( '%02d', count( $imgs ) ) . '</p></div><div class="hgal-track">' . $items . '</div></div></section>';
}

function hv_bigq( $eyebrow, $text, $cream = false ) {
	return '<section class="bigq' . ( $cream ? ' cream' : '' ) . '"><div class="wrap"><p class="eyebrow">' . e( $eyebrow ) . '</p><p>' . e( $text ) . '</p></div></section>';
}

function hv_bookbar( $label = null ) {
	$label = $label ?? t( 'Reserva directa · mejor tarifa' );
	return '<div class="bookbar" aria-hidden="false"><span>' . e( $label ) . '</span><a class="btn btn-gold" href="#reservar" data-track="reservar_barra">' . e( t( 'Reservar' ) ) . ' ' . HV_ARROW . '</a></div>';
}

function hv_icon( $txt ) {
	$I = array(
		'aire' => '<path d="M3 8h13a3 3 0 1 0-3-3M3 12h17a3 3 0 1 1-3 3M3 16h9"/>',
		'tv' => '<rect x="3" y="5" width="18" height="12" rx="2"/><path d="M8 21h8"/>',
		'baño' => '<path d="M4 12h16v3a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5zM6 12V6a2 2 0 0 1 4 0"/>',
		'jacuzzi' => '<path d="M3 12h18v3a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5v-3ZM6 12V6a2 2 0 0 1 4 0M14 8c0-1 1-1 1-2s-1-1-1-2M18 8c0-1 1-1 1-2s-1-1-1-2"/>',
		'cocina' => '<path d="M4 10h16v10H4zM8 10V5h8v5M10 15h4"/>',
		'sala' => '<path d="M3 18v-6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v6M3 15h18M6 9V6h12v3"/>',
		'bar' => '<path d="M8 21h8M12 17v4M7 4h10l-1 7a4 4 0 0 1-8 0L7 4Z"/>',
		'vista' => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
		'balc' => '<path d="M4 20V10l8-6 8 6v10M4 14h16M8 14v6M12 14v6M16 14v6"/>',
		'comedor' => '<path d="M5 3v8a2 2 0 0 0 4 0V3M7 11v10M17 3c-2 2-2 6 0 8v10"/>',
		'default' => '<path d="M12 3l2.6 5.6L20 9.5l-4 4 1 5.5-5-2.7-5 2.7 1-5.5-4-4 5.4-.9z"/>',
	);
	$tl = mb_strtolower( $txt );
	$k  = 'default';
	foreach ( array( 'air' => 'aire', 'bath' => 'baño', 'shower' => 'baño', 'kitchen' => 'cocina', 'living' => 'sala', 'lounge' => 'sala', 'view' => 'vista', 'dining' => 'comedor', 'aire' => 'aire', 'tv' => 'tv', 'jacuzzi' => 'jacuzzi', 'baño' => 'baño', 'ducha' => 'baño', 'cocina' => 'cocina', 'sala' => 'sala', 'estar' => 'sala', 'bar' => 'bar', 'vista' => 'vista', 'balc' => 'balc', 'comedor' => 'comedor', 'relaj' => 'jacuzzi' ) as $n => $v ) {
		if ( false !== strpos( $tl, $n ) ) { $k = $v; break; }
	}
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">' . $I[ $k ] . '</svg>';
}

/** Tarjeta de habitación. $r: datos de la habitación (hv_rooms). */
function hv_room_card( $r, $hidden = false ) {
	$chips = '';
	foreach ( array_merge( array( $r['m2'] ?? '', $r['per'] ?? '' ), (array) ( $r['beds'] ?? array() ), array( $r['dup'] ?? '' ) ) as $x ) if ( $x ) $chips .= '<span class="chip">' . e( $x ) . '</span>';
	$imgs = (array) ( $r['imgs'] ?? array() );
	$im1  = ( $r['cover'] ?? '' ) ?: ( $imgs[0] ?? '' );
	$im2  = $im1;
	foreach ( $imgs as $u ) if ( $u !== $im1 ) { $im2 = $u; break; }
	$desc = $r['desc'][0] ?? '';
	if ( mb_strlen( $desc ) > 150 ) { $desc = mb_substr( $desc, 0, 150 ); $desc = mb_substr( $desc, 0, mb_strrpos( $desc, ' ' ) ) . '…'; }
	$url  = $r['url'] ?? hv_url( $r['key'] );
	$slug = $r['slug_es'] ?? '';
	ob_start(); ?>
<article class="card<?php echo $hidden ? ' hide' : ''; ?>" data-cat="<?php echo ea( $r['f'] ?? '' ); ?>">
  <a class="card-media" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo ea( $r['name'] ); ?>"><?php echo hv_img( $im1, array( 'alt' => $r['name'] ) ), hv_img( $im2 ); ?><span class="card-tag"><?php echo e( $r['cat'] ?? '' ); ?></span></a>
  <div class="card-body"><h3><?php echo e( $r['name'] ); ?></h3><div class="chips"><?php echo $chips; ?></div><p><?php echo e( $desc ); ?></p>
  <div class="card-actions"><a class="btn btn-ghost" href="<?php echo esc_url( $url ); ?>"><?php echo e( t( 'Ver' ) ), ' ', HV_OUT; ?></a><a class="btn btn-gold" href="#reservar" data-track="reservar_<?php echo ea( $slug ); ?>"><?php echo e( t( 'Reservar' ) ), ' ', HV_ARROW; ?></a></div></div>
</article>
<?php return ob_get_clean();
}

/** Sección Casa Enzo Coffee (café insignia). */
function hv_casa_enzo() {
	$en  = hv_en();
	$url = $en ? 'https://casaenzocoffee.com/en' : 'https://casaenzocoffee.com';
	$I   = 'https://casaenzocoffee.com/cdn/shop/';
	$t   = $en ? array(
		'eb' => 'Our signature coffee', 'h' => 'Casa Enzo <em>Coffee</em>',
		'p1' => 'Colombian specialty coffee, grown by Colombian coffee farmers and roasted in Medellín. Our first lot, Primera Luz, is a Caturra hand-picked at 1,699 meters in the mountains of Santa Elena, above the city: an artisanal micro-lot with notes of green apple, yellow fruits and sugarcane.',
		'p2' => 'It is the coffee we serve at Haven, and you can take it home: a bag of Casa Enzo is a gift that carries the taste of origin.',
		'facts' => array( array( 'Origin', 'Santa Elena, Medellín' ), array( 'Altitude', '1,699 m' ), array( 'Variety', 'Caturra · light honey' ), array( 'Lot', 'Artisanal micro-lot' ) ),
		'where' => 'Enjoy it at', 'places' => array( 'Swing Coffee Bar', 'Our breakfast', 'Every room', 'To take home as a gift' ),
		'cta' => 'See more', 'ig' => 'Follow us', 'alt' => array( 'Casa Enzo Coffee bag, Primera Luz', 'Pouring coffee over a Chemex', 'Roasted coffee, green coffee and coffee cherries' ),
	) : array(
		'eb' => 'Nuestro café insignia', 'h' => 'Casa Enzo <em>Coffee</em>',
		'p1' => 'Café de especialidad colombiano, cultivado por caficultores colombianos y tostado en Medellín. Su primer lote, Primera Luz, es un Caturra cosechado a mano a 1.699 metros en las montañas de Santa Elena, sobre la ciudad: un microlote artesanal con notas de manzana verde, frutos amarillos y caña de azúcar.',
		'p2' => 'Es el café que servimos en Haven, y también puedes llevártelo: una bolsa de Casa Enzo es un regalo con el sabor del origen.',
		'facts' => array( array( 'Origen', 'Santa Elena, Medellín' ), array( 'Altura', '1.699 msnm' ), array( 'Variedad', 'Caturra · honey claro' ), array( 'Lote', 'Microlote artesanal' ) ),
		'where' => 'Lo encuentras en', 'places' => array( 'Swing Coffee Bar', 'Nuestro desayuno', 'Todas las habitaciones', 'Para llevar de regalo' ),
		'cta' => 'Ver más', 'ig' => 'Síguenos', 'alt' => array( 'Bolsa de Casa Enzo Coffee, Primera Luz', 'Café filtrado en Chemex', 'Café tostado, café verde y cerezas de café' ),
	);
	$imgs = array( $I . 'files/WhatsApp_Image_2026-10-21_at_10_16_43_AM.jpg?v=1792596874&width=1000', $I . 'articles/hf_20261024_235910_6ef4ebc1-6fc7-4975-97b4-9f50157d0c07.png?v=1792886467&width=700', $I . 'articles/hf_20261024_235944_e3420dd7-0d73-444f-81ac-9a9f38e6d61e.png?v=1792886474&width=700' );
	ob_start(); ?>
<section class="ce" aria-labelledby="ce-title">
  <div class="wrap ce-grid">
    <div class="ce-media">
      <figure class="ce-main"><img src="<?php echo esc_url( $imgs[0] ); ?>" alt="<?php echo ea( $t['alt'][0] ); ?>" loading="lazy" decoding="async" width="1000" height="1000"></figure>
      <figure class="ce-a"><img src="<?php echo esc_url( $imgs[1] ); ?>" alt="<?php echo ea( $t['alt'][1] ); ?>" loading="lazy" decoding="async" width="700" height="700"></figure>
      <figure class="ce-b"><img src="<?php echo esc_url( $imgs[2] ); ?>" alt="<?php echo ea( $t['alt'][2] ); ?>" loading="lazy" decoding="async" width="700" height="700"></figure>
    </div>
    <div class="ce-copy" data-reveal>
      <p class="eyebrow"><?php echo e( $t['eb'] ); ?></p>
      <h2 id="ce-title"><?php echo $t['h']; ?></h2>
      <p><?php echo e( $t['p1'] ); ?></p>
      <p><?php echo e( $t['p2'] ); ?></p>
      <div class="ce-facts"><?php foreach ( $t['facts'] as $f ) echo '<div><span>' . e( $f[0] ) . '</span><b>' . e( $f[1] ) . '</b></div>'; ?></div>
      <p class="ce-where"><?php echo e( $t['where'] ); ?></p>
      <ul class="ce-places"><?php foreach ( $t['places'] as $p ) echo '<li>' . e( $p ) . '</li>'; ?></ul>
      <div class="ce-ctas"><?php echo hv_btn( $t['cta'], $url, 'gold', 'casa_enzo_ver_mas', true ); ?><a class="btn btn-ghost ce-ig" href="https://www.instagram.com/casaenzocoffee" target="_blank" rel="noopener" data-track="casa_enzo_instagram"><?php echo HV_IG, e( $t['ig'] ); ?> <b>@casaenzocoffee</b></a></div>
    </div>
  </div>
</section>
<?php return ob_get_clean();
}

/** Primera palabra normal y la última en cursiva, como en los títulos del prototipo. */
function hv_title_em( $title ) {
	$w = explode( ' ', e( $title ) );
	if ( count( $w ) > 1 ) { $last = array_pop( $w ); return implode( ' ', $w ) . ' <em>' . $last . '</em>'; }
	return e( $title );
}

/** Tarjeta de una entrada del blog. */
function hv_post_card( $p ) {
	$u = esc_url( $p['url'] );
	return '<article class="card post-card"><a class="card-media" href="' . $u . '">' . hv_img( $p['img'] ) . hv_img( $p['img'] ) . '<span class="card-tag">' . e( $p['date'] ) . '</span></a>'
		. '<div class="card-body"><h3>' . e( $p['title'] ) . '</h3><p>' . e( $p['ex'] ) . '…</p><div class="card-actions"><a class="btn btn-ghost" href="' . $u . '">' . e( t( 'Leer artículo' ) ) . ' ' . HV_ARROW . '</a></div></div></article>';
}
