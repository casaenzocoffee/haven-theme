<?php
/* Autor del blog: "Concierge de Haven" en vez de "admin" (5 nov 2026, pedido del gerente). Uso: wp eval-file blog-autor.php [aplicar] */
$apply = in_array( 'aplicar', (array) ( $args ?? array() ), true );
$ids = array_unique( array_map( 'intval', wp_list_pluck( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1, 'lang' => '' ) ), 'post_author' ) ) );
$es = 'El equipo de concierge de Haven, A Design Hotel, en El Poblado, Medellín. Estamos a 2 minutos de Provenza y cada día recomendamos a nuestros huéspedes restaurantes, cafés, rooftops y planes de la ciudad que conocemos de primera mano.';
$en = 'The concierge team at Haven, A Design Hotel in El Poblado, Medellín. Two minutes from Provenza, we recommend restaurants, cafés, rooftops and city plans we know first-hand to our guests every day.';
foreach ( $ids as $uid ) {
	$u = get_userdata( $uid );
	WP_CLI::log( "#$uid {$u->user_login}: '{$u->display_name}' -> 'Concierge de Haven'" );
	if ( ! $apply ) continue;
	$bak = getenv( 'HOME' ) . '/hv-backups/autor-' . $uid . '-antes-' . gmdate( 'Ymd-His' ) . '.json';
	file_put_contents( $bak, wp_json_encode( array( 'display_name' => $u->display_name, 'nickname' => $u->nickname, 'first_name' => $u->first_name, 'last_name' => $u->last_name, 'description' => $u->description, 'description_en' => get_user_meta( $uid, 'description_en', true ) ), JSON_UNESCAPED_UNICODE ) );
	wp_update_user( array( 'ID' => $uid, 'display_name' => 'Concierge de Haven', 'nickname' => 'Concierge de Haven', 'first_name' => 'Concierge', 'last_name' => 'de Haven', 'description' => $es ) );
	update_user_meta( $uid, 'description_en', $en ); // Polylang: biografía en inglés
	WP_CLI::success( "Listo. Copia: $bak" );
}
