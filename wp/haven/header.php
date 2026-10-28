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
  <?php foreach ( array_slice( $nav, 1 ) as $n ) echo '<a class="big" href="' . esc_url( hv_url( $n[0] ) ) . '">' . e( 'guia-medellin' === $n[0] ? t( 'Guía Medellín' ) : $n[1] ) . '</a>'; ?>
  <p style="margin-top:24px;color:var(--muted)"><?php echo hv_en() ? '<a href="' . esc_url( $langs['es'] ) . '" hreflang="es" lang="es">Español</a>' : '<a href="' . esc_url( $langs['en'] ) . '" hreflang="en" lang="en">English</a>'; ?></p>
</div>
