<?php
/* Experiencias (360° Rooftop & Restaurante, Swing Coffee Bar, Wellness, Eventos): bloques editoriales con fotos en arco. */
get_header();
$d     = hv_data();
$h     = hv_hero_data( $d );
$key   = hv_key();
$secs  = (array) ( $d['sections'] ?? array() );
$crumb = $d['crumb'] ?? get_the_title();
$all   = array();
foreach ( $secs as $s ) $all = array_merge( $all, (array) ( $s['imgs'] ?? array() ) );
$all = array_values( array_filter( $all ) );
echo hv_hero( $h['title'], $h['sub'], $h['img'], array( array( t( 'Inicio' ), hv_url( 'home' ) ), array( $crumb, '' ) ), $h['eyebrow'] );
$n = 0;
foreach ( $secs as $s ) {
	if ( empty( $s['paras'] ) ) continue;
	$ims = array_values( array_filter( (array) ( $s['imgs'] ?? array() ) ) );
	if ( ! $ims && $all ) $ims = array_slice( array_slice( $all, $n % count( $all ) ), 0, 2 );
	$cta  = '';
	$seen = array();
	foreach ( array_slice( (array) ( $s['btns'] ?? array() ), 0, 3 ) as $b ) {
		$bt = trim( (string) ( $b['t'] ?? '' ) );
		if ( '' === $bt || isset( $seen[ mb_strtolower( $bt ) ] ) ) continue;
		$seen[ mb_strtolower( $bt ) ] = 1;
		if ( mb_strtolower( $bt ) === $bt ) $bt = mb_strtoupper( mb_substr( $bt, 0, 1 ) ) . mb_substr( $bt, 1 );
		$cta .= hv_btn( $bt, $b['href'] ?? '#', $cta ? 'ghost' : 'gold', $key . '_' . $n, ! empty( $b['ext'] ) );
	}
	$main = ( 0 === $n && ! empty( $d['first_img'] ) ) ? $d['first_img'] : ( $ims[0] ?? $h['img'] );
	echo hv_editorial( ( $s['eyebrow'] ?? '' ) ?: $crumb, hv_title_em( $s['title'] ?? '' ), $s['paras'], $main, $ims[1] ?? null, 1 === $n % 2, 1 === $n % 2, $cta, 0 === $n ? 'c' : '' );
	if ( 0 === $n && ! empty( $d['casa_enzo'] ) ) echo hv_casa_enzo();
	if ( 0 === $n && count( $all ) >= 5 ) echo hv_hgallery( t( 'Galería' ), $d['gal_title'] ?? '', array_slice( $all, 0, 10 ) );
	$n++;
}
get_footer();
