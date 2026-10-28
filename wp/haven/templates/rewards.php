<?php
/* Serena Rewards: programa de fidelización con sus cuatro niveles (Silver, Golden, Diamond, Black). */
get_header();
$d     = hv_data();
$h     = hv_hero_data( $d, array( 'title' => 'Serena <em>Rewards</em>', 'sub' => t( 'Programa de fidelización de Serena Hotels: más noches, más beneficios.' ), 'img' => '/wp-content/uploads/2026/06/serena.webp', 'eyebrow' => t( 'Programa de fidelización' ) ) );
$tiers = (array) ( $d['tiers'] ?? array() );
echo hv_hero( $h['title'], $h['sub'], $h['img'], array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( 'Serena Rewards', '' ) ), $h['eyebrow'] );
echo hv_editorial( 'Serena Hotels', t( 'Elegancia <em>sin límites</em>' ), array( $d['intro'] ?? '' ), $d['intro_img'] ?? '/wp-content/uploads/2026/06/serena.webp', null, false, true,
	hv_btn( t( 'Quiero registrarme' ), hv_wa( t( 'Hola, quiero registrarme en Serena Rewards' ) ), 'gold', 'rewards_registro', true ) );
?>
<section class="sec"><div class="wrap"><div class="sec-head" data-reveal><div><p class="eyebrow"><?php echo e( t( 'Cuatro niveles' ) ); ?></p><h2 style="margin-top:20px"><?php echo wp_kses_post( t( 'Elige tu <em>plan</em>' ) ); ?></h2></div><p><?php echo e( t( 'Los beneficios crecen con las noches que pasas con nosotros cada año.' ) ); ?></p></div><div class="plans">
<?php foreach ( $tiers as $i => $x ) {
	$items = '';
	foreach ( (array) ( $x['items'] ?? array() ) as $it ) $items .= '<li>' . e( $it ) . '</li>';
	$cls = preg_replace( '/[^a-z0-9]/', '', (string) ( $x['cls'] ?? 'ts' ) );
	printf( '<div class="plan%1$s"><div class="plan-card"><div class="tier %2$s" tabindex="0"><div class="row"><span class="brand">SERENA</span><span class="chipcard"></span></div><div><div class="tname">%3$s</div></div><div class="row"><span>Rewards</span><span>Plan %4$02d</span></div></div></div><div class="plan-body" data-reveal><p class="eyebrow">Plan %3$s</p><h3>%5$s</h3><ul class="plan-list">%6$s</ul></div></div>',
		$i % 2 ? ' flip' : '', $cls, e( $x['name'] ?? '' ), $i + 1, e( $x['nights'] ?? '' ), $items );
} ?>
</div><?php if ( ! empty( $d['note'] ) ) echo '<p class="plan-note">' . e( $d['note'] ) . '</p>'; ?></div></section>
<?php
echo hv_bigq( 'Serena Rewards', t( 'Permítanos conocerle mejor.' ) );
get_footer();
