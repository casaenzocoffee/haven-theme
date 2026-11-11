<?php
/* Encabezado: barra cápsula, menú, idiomas y botón de reserva. */
defined( 'ABSPATH' ) || exit;
$nav = array(
	array( 'home', t( 'Inicio' ) ), array( 'habitaciones', t( 'Habitaciones' ) ), array( 'rooftop', '360° Rooftop' ), array( 'swing', 'Swing' ),
	array( 'gastronomia', t( 'Gastronomía' ) ), array( 'wellness', 'Wellness' ), array( 'eventos', t( 'Eventos' ) ), array( 'guia-medellin', t( 'Guía' ) ), array( 'contacto', t( 'Contacto' ) ),
);
$cur_id = hv_current_id();
$langs  = hv_lang_links();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
<style id="hv-nav-dd">
.nav-dd{position:relative;display:flex}
.nav-dd>a{display:inline-flex;align-items:center;gap:5px}
.nav-dd .dd-chev{opacity:.7;transition:transform .25s}
.nav-dd:hover .dd-chev,.nav-dd:focus-within .dd-chev{transform:rotate(180deg)}
.nav-sub{position:absolute;top:100%;left:50%;min-width:230px;margin-top:14px;padding:8px;border-radius:22px;background:rgba(25,19,17,.95);box-shadow:inset 0 0 0 1px rgba(205,179,113,.22),0 18px 40px rgba(0,0,0,.35);display:flex;flex-direction:column;gap:2px;opacity:0;visibility:hidden;transform:translate(-50%,-6px);transition:opacity .2s,transform .2s,visibility .2s;z-index:60}
.nav-sub::before{content:"";position:absolute;left:0;right:0;top:-18px;height:18px}
.nav-dd:hover .nav-sub,.nav-dd:focus-within .nav-sub{opacity:1;visibility:visible;transform:translate(-50%,0)}
.nav .nav-sub a{padding:13px 16px;border-radius:14px;text-align:left}
.nav .nav-sub a:hover{background:rgba(205,179,113,.08);color:var(--fg)}
.nav .nav-sub a[aria-current="page"]::after{display:none}
.drawer a.sub{font:400 17px/1.4 var(--sans);color:var(--champagne);letter-spacing:.02em;margin:-2px 0 8px 2px}
.drawer a.sub::before{content:"— ";color:var(--gold)}
@media (prefers-reduced-motion:reduce){.nav-sub,.nav-dd .dd-chev{transition:none}}
</style>
</head>
<body <?php body_class(); ?> data-hv="<?php echo ea( hv_template_key() . ( hv_key() ? ':' . hv_key() : '' ) ); ?>">
<?php wp_body_open(); ?>
<?php if ( function_exists( 'hv_front_intro' ) && is_front_page() ) hv_front_intro(); ?>
<script>document.body.classList.replace('no-js','js');</script>
<div class="progress" aria-hidden="true"></div>

<header class="site-header" id="top">
  <div class="wrap">
    <div class="capsule">
      <a class="logo" href="<?php echo esc_url( hv_url( 'home' ) ); ?>" aria-label="Haven, A Design Hotel — <?php echo ea( t( 'Inicio' ) ); ?>"><img src="<?php echo esc_url( hv_logo_url() ); ?>" alt="Haven, A Design Hotel" width="150" height="56"></a>
      <nav class="nav" aria-label="<?php echo ea( t( 'Principal' ) ); ?>">
        <?php foreach ( $nav as $n ) {
			$id = hv_page_id( $n[0] );
			$cur = ( $id && $id === $cur_id ) ? ' aria-current="page"' : '';
			if ( 'eventos' === $n[0] ) {
				$tid  = hv_en() ? 6057 : 6054;
				$tcur = ( (int) $cur_id === $tid ) ? ' aria-current="page"' : '';
				$chev = '<svg class="dd-chev" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
				echo '<div class="nav-dd"><a href="' . esc_url( hv_url( $n[0] ) ) . '"' . ( $cur ? $cur : $tcur ) . ' aria-haspopup="true">' . e( $n[1] ) . $chev . '</a><div class="nav-sub"><a href="' . esc_url( hv_url( $n[0] ) ) . '"' . $cur . '>' . e( $n[1] ) . '</a><a href="' . esc_url( get_permalink( $tid ) ) . '"' . $tcur . '>' . e( hv_en() ? 'Holiday season' : 'Eventos de temporada' ) . '</a></div></div>';
				continue;
			}
			echo '<a href="' . esc_url( hv_url( $n[0] ) ) . '"' . $cur . '>' . e( $n[1] ) . '</a>';
		} ?>
      </nav>
      <div class="lang"><a href="<?php echo esc_url( $langs['es'] ); ?>" hreflang="es" lang="es"<?php echo hv_en() ? '' : ' aria-current="true"'; ?>>ES</a><span aria-hidden="true">/</span><a href="<?php echo esc_url( $langs['en'] ); ?>" hreflang="en" lang="en"<?php echo hv_en() ? ' aria-current="true"' : ''; ?>>EN</a></div>
      <a class="btn btn-gold" href="#reservar" data-track="reservar_header"><?php echo e( hv_en() ? 'Book now' : 'Reservar' ), ' ', HV_ARROW; ?></a>
      <button class="menu-btn" type="button" aria-label="<?php echo ea( t( 'Abrir menú' ) ); ?>" aria-expanded="false" aria-controls="drawer"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 8h16M4 16h16"/></svg></button>
    </div>
  </div>
</header>

<div class="drawer" id="drawer" hidden>
  <button class="close" type="button" aria-label="<?php echo ea( t( 'Cerrar menú' ) ); ?>">✕</button>
  <?php foreach ( array_slice( $nav, 1 ) as $n ) { echo '<a class="big" href="' . esc_url( hv_url( $n[0] ) ) . '">' . e( 'guia-medellin' === $n[0] ? t( 'Guía Medellín' ) : $n[1] ) . '</a>'; if ( 'eventos' === $n[0] ) echo '<a class="sub" href="' . esc_url( get_permalink( hv_en() ? 6057 : 6054 ) ) . '">' . e( hv_en() ? 'Holiday season' : 'Eventos de temporada' ) . '</a>'; } ?>
  <p style="margin-top:24px;color:var(--muted)"><?php echo hv_en() ? '<a href="' . esc_url( $langs['es'] ) . '" hreflang="es" lang="es">Español</a>' : '<a href="' . esc_url( $langs['en'] ) . '" hreflang="en" lang="en">English</a>'; ?></p>
</div>
