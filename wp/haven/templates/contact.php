<?php
/* Contacto: ubicación, recepción, reservas y formulario. */
get_header();
$d = hv_data();
$h = hv_hero_data( $d, array( 'title' => t( '<em>Contacto</em>' ), 'sub' => t( 'Estamos aquí para resolver cualquier detalle, antes, durante o después de tu estancia.' ), 'img' => '/wp-content/uploads/2026/06/hotel-haven-medellin.webp' ) );
$ic = function ( $p ) { return '<span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">' . $p . '</svg></span>'; };
echo hv_hero( $h['title'], $h['sub'], $h['img'], array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( t( 'Contacto' ), '' ) ), $h['eyebrow'] );
?>
<section class="sec" id="c"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow"><?php echo e( t( 'Hablemos' ) ); ?></p><h2 style="margin-top:20px"><?php echo wp_kses_post( t( 'Estamos para <em>ayudarte</em>' ) ); ?></h2></div><p><?php echo e( t( '¿Tienes alguna duda o queja? ¡Estamos aquí para escucharte!' ) ); ?></p></div>
<div class="contact-grid" data-reveal>
  <div class="cbox"><?php echo $ic( '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/>' ); ?><h3><?php echo e( t( 'Ubicación' ) ); ?></h3><p>Calle 10A #36-29<br>El Poblado, Medellín, Colombia</p><a href="https://www.google.com/maps/dir/?api=1&amp;destination=Haven+Medell%C3%ADn+Calle+10A+36-29" target="_blank" rel="noopener" data-track="como_llegar_contacto"><?php echo e( t( 'Cómo llegar ↗︎' ) ); ?></a></div>
  <div class="cbox"><?php echo $ic( '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>' ); ?><h3><?php echo e( t( 'Recepción' ) ); ?></h3><p><a href="tel:+573223064585" data-track="llamar_recepcion">(+57) 322 306 4585</a><br><a href="tel:+576046047327">(+57) (4) 604 7327</a><br><a href="mailto:frontdesk@serenahotelsco.com">frontdesk@serenahotelsco.com</a></p></div>
  <div class="cbox"><?php echo $ic( '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>' ); ?><h3><?php echo e( t( 'Reservas' ) ); ?></h3><p><a href="tel:+573212097033" data-track="llamar_reservas">(+57) 321 209 7033</a><br><a href="mailto:booking@serenahotelsco.com">booking@serenahotelsco.com</a></p><a href="<?php echo esc_url( hv_wa( t( 'Hola, quiero hacer una reserva' ) ) ); ?>" target="_blank" rel="noopener" data-track="whatsapp_contacto"><?php echo e( t( 'Escribir por WhatsApp' ) ); ?> ↗︎</a></div>
</div></div></section>
<section class="ed cream"><div class="wrap ed-grid"><div><p class="eyebrow"><?php echo e( t( 'Formulario' ) ); ?></p><h2><?php echo e( t( 'Escríbenos' ) ); ?></h2><p><?php echo e( t( 'Te respondemos en el menor tiempo posible.' ) ); ?></p>
<?php if ( ! empty( $d['form_shortcode'] ) && shortcode_exists( trim( strtok( trim( $d['form_shortcode'], '[]' ), ' ' ) ) ) ) { echo do_shortcode( $d['form_shortcode'] ); } else { ?>
<form class="cform" action="mailto:frontdesk@serenahotelsco.com" method="post" enctype="text/plain" data-reveal>
  <label><?php echo e( t( 'Nombre' ) ); ?><input name="nombre" type="text" placeholder="<?php echo ea( t( 'Coloque su nombre completo' ) ); ?>" required autocomplete="name"></label>
  <label><?php echo e( t( 'Celular' ) ); ?><input name="celular" type="tel" placeholder="<?php echo ea( t( 'Coloque su número de celular' ) ); ?>" autocomplete="tel"></label>
  <label>E-mail<input name="email" type="email" placeholder="<?php echo ea( t( 'Correo electrónico' ) ); ?>" required autocomplete="email"></label>
  <label><?php echo e( t( 'Mensaje' ) ); ?><textarea name="mensaje" rows="4" placeholder="<?php echo ea( t( 'Redacte un mensaje' ) ); ?>"></textarea></label>
  <button class="btn btn-gold" type="submit" data-track="contacto_enviar"><?php echo e( t( 'Enviar solicitud' ) ), ' ', HV_ARROW; ?></button>
</form>
<?php } ?>
</div><div class="arch-wrap"><?php echo hv_stamp(); ?><div class="arch"><?php echo hv_img( '/wp-content/uploads/2026/06/medellin-haven-a-design-hotel.webp', array( 'data-speed' => '.08' ) ); ?></div></div></div></section>
<?php get_footer();
