<?php
/* Diagnóstico SEO (solo lectura). Uso: wp eval-file seo-diag.php */
foreach ( array( 'habitacion-duke-206', 'duke-room-206', 'presidencial', 'suite-presidential', 'presidential', 'deal10', 'penthouse', 'eventos', 'events', 'rooftop', '360-rooftop' ) as $slug ) {
	$ps = get_posts( array( 'name' => $slug, 'post_type' => array( 'page', 'post' ), 'post_status' => 'any', 'numberposts' => 5, 'lang' => '' ) );
	foreach ( $ps as $p ) {
		$l = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $p->ID ) : '';
		$tr = function_exists( 'pll_get_post_translations' ) ? wp_json_encode( pll_get_post_translations( $p->ID ) ) : '';
		echo "$slug #{$p->ID} [$l] {$p->post_status} key=" . get_post_meta( $p->ID, '_hv_key', true ) . ' tpl=' . get_post_meta( $p->ID, '_wp_page_template', true ) . ' url=' . get_permalink( $p ) . " tr=$tr title=" . $p->post_title . "\n";
	}
}
$o = get_option( 'wpseo_titles' );
foreach ( array( 'noindex-author-wpseo', 'disable-author', 'noindex-tax-category', 'noindex-author-noposts-wpseo', 'company_name', 'website_name', 'og_frontpage_title' ) as $k ) echo "wpseo_titles[$k]=" . wp_json_encode( $o[ $k ] ?? null ) . "\n";
$s = get_option( 'wpseo_social' ); echo 'og enabled=' . wp_json_encode( $s['opengraph'] ?? null ) . "\n";
echo 'yoast ver=' . ( defined( 'WPSEO_VERSION' ) ? WPSEO_VERSION : '-' ) . "\n";
foreach ( get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false, 'lang' => '' ) ) as $t ) echo "cat {$t->term_id} {$t->slug} n={$t->count}\n";
foreach ( get_users( array( 'fields' => array( 'ID', 'user_login', 'user_nicename', 'display_name' ) ) ) as $u ) echo "user {$u->ID} {$u->user_login} {$u->user_nicename} {$u->display_name} posts=" . count_user_posts( $u->ID ) . "\n";
echo 'tagline=' . get_bloginfo( 'description' ) . "\n";
if ( function_exists( 'pll_translate_string' ) ) echo 'tagline en=' . pll_translate_string( get_bloginfo( 'description' ), 'en' ) . "\n";
