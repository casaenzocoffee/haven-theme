
/* hv temporal 19-nov: título "Menú 360 Rooftop" en la carta estática. Se quita después de usarlo. */
add_action( 'wp_ajax_hv_carta_1911', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'no' );
	$f = ABSPATH . '360-rooftop-carta/index.html';
	if ( ! file_exists( $f ) ) wp_send_json_error( array( 'nofile' => $f ) );
	$s = file_get_contents( $f );
	$map = array(
		'Carta 360 Rooftop Bar &amp; Restaurante · Medellín' => 'Menú 360 Rooftop Medellín: carta, cócteles y precios | Haven',
		'Carta del 360 Rooftop Bar & Restaurante en el piso 11 de Haven, en Provenza, El Poblado (Medellín): desayunos, platos fuertes, hamburguesas, pizzas, postres, cócteles, vinos y más.' => 'Menú oficial del 360 Rooftop Medellín, piso 11 de Haven en Provenza: desayunos, platos fuertes, hamburguesas, pizzas, cócteles, vinos y precios actualizados.',
	);
	$n = 0;
	foreach ( $map as $a => $b ) { $c = substr_count( $s, $a ); $n += $c; $s = str_replace( $a, $b, $s ); }
	if ( empty( $_POST['go'] ) ) wp_send_json_success( array( 'file' => $f, 'size' => filesize( $f ), 'matches' => $n ) );
	if ( $n ) { copy( $f, $f . '.bak-20261119' ); file_put_contents( $f, $s ); }
	wp_send_json_success( array( 'replaced' => $n, 'size' => filesize( $f ) ) );
} );
