<?php
/* Blog: listado de historias de Medellín. */
get_header();
$pid = is_home() ? (int) get_option( 'page_for_posts' ) : hv_current_id();
$d   = $pid ? hv_data( $pid ) : array();
$h   = hv_hero_data( $d, array( 'title' => '<em>Blog</em>', 'sub' => t( 'Guías, planes y sabores para vivir Medellín como un local.' ), 'img' => '/wp-content/uploads/2026/06/hotel-haven-rooftop-360-4.webp' ) );
echo hv_hero( $h['title'], $h['sub'], $h['img'], array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( 'Blog', '' ) ), $h['eyebrow'] );
?>
<section class="sec" id="c"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow">Medellín</p><h2 style="margin-top:20px"><?php echo wp_kses_post( t( 'Historias de la <em>ciudad</em>' ) ); ?></h2></div><p><?php echo e( $d['intro'] ?? '' ); ?></p></div><div class="cards"><?php foreach ( hv_posts() as $p ) echo hv_post_card( $p ); ?></div></div></section>
<?php get_footer();
