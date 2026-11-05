<?php
/* Swing Lounge solo de 8 a. m. a 6 p. m. (sin cócteles de noche) y brunch esporádico como evento programado (gerente, 5 nov 2026).
   Uso: wp eval-file swing-brunch.php [aplicar]. Copia en ~/hv-backups/swing-brunch-antes-*.json y _hv_data_bak. */
$apply = in_array( 'aplicar', (array) ( $args ?? array() ), true );
$T = array(
	'es' => array(
		'swing' => array( 'Ubicado en el vestíbulo de Haven, Swing Lounge es nuestro café: el lugar para empezar el día con café de especialidad de Antioquia, preparado por nuestros baristas.',
			'Abre todos los días de 8:00 a. m. a 6:00 p. m. Pase por un espresso antes de salir a recorrer la ciudad, quédese a trabajar un rato o haga una pausa en la tarde antes de volver a Provenza.',
			'Consulte las bebidas y los acompañamientos en la carta digital de Swing Lounge.' ),
		'brunch_t' => 'Brunch en el rooftop',
		'brunch_p' => array( 'De vez en cuando organizamos un brunch en el 360° Rooftop como evento programado, con sabores locales e internacionales. No tiene fecha fija: escríbanos por WhatsApp para saber cuándo es el próximo.' ),
		'brunch_b' => 'Preguntar por el próximo brunch',
		'sub_from' => array( 'y brunch dominical' ), 'sub_to' => 'y eventos programados',
		'desc_from' => array( 'brunch dominical' ), 'desc_to' => 'brunch y eventos programados',
	),
	'en' => array(
		'swing' => array( 'Located in the Haven lobby, Swing Lounge is our café: the place to start the day with specialty coffee from Antioquia, prepared by our baristas.',
			'Open every day from 8:00 a.m. to 6:00 p.m. Stop by for an espresso before heading out to explore the city, stay a while to work, or take an afternoon break before going back to Provenza.',
			'See the drinks and bites on the Swing Lounge digital menu.' ),
		'brunch_t' => 'Rooftop brunch',
		'brunch_p' => array( 'Now and then we host a brunch on the 360° Rooftop as a scheduled event, with local and international flavors. There is no fixed date: message us on WhatsApp to find out when the next one is.' ),
		'brunch_b' => 'Ask about the next brunch',
		'sub_from' => array( 'and Sunday brunch' ), 'sub_to' => 'and scheduled events',
		'desc_from' => array( 'Sunday brunch' ), 'desc_to' => 'brunch and scheduled events',
	),
);
$bak = array();
foreach ( array( 'swing', 'eventos' ) as $key ) {
	foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '', 'meta_key' => '_hv_key', 'meta_value' => $key ) ) as $id ) {
		$l = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $id ) : 'es';
		if ( empty( $T[ $l ] ) ) continue;
		$t = $T[ $l ];
		$raw = get_post_meta( $id, '_hv_data', true );
		$d = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
		$ch = array();
		foreach ( (array) ( $d['sections'] ?? array() ) as $i => $s ) {
			$p0 = (string) ( $s['paras'][0] ?? '' );
			if ( 'swing' === $key && preg_match( '/^(Ubicado en nuestro vestíbulo|Located in our lobby)/u', $p0 ) ) {
				$d['sections'][ $i ]['paras'] = $t['swing'];
				$d['sections'][ $i ]['title'] = 'Swing Lounge';
				$ch[] = "sección $i: texto de Swing";
			}
			if ( 'swing' === $key ) {
				foreach ( (array) ( $s['btns'] ?? array() ) as $j => $b ) if ( false !== strpos( (string) ( $b['href'] ?? '' ), 'Swing Bar' ) ) { $d['sections'][ $i ]['btns'][ $j ]['href'] = str_replace( 'Swing Bar', 'Swing Lounge', $b['href'] ); $ch[] = "sección $i: botón WhatsApp"; }
			}
			if ( 'eventos' === $key && preg_match( '/brunch/i', (string) ( $s['title'] ?? '' ) ) ) {
				$d['sections'][ $i ]['title'] = $t['brunch_t'];
				$d['sections'][ $i ]['paras'] = $t['brunch_p'];
				foreach ( (array) ( $s['btns'] ?? array() ) as $j => $b ) if ( preg_match( '/brunch/i', (string) ( $b['t'] ?? '' ) ) ) $d['sections'][ $i ]['btns'][ $j ]['t'] = $t['brunch_b'];
				$ch[] = "sección $i: brunch esporádico";
			}
		}
		if ( 'eventos' === $key && ! empty( $d['sub'] ) ) { $n = str_replace( $t['sub_from'], $t['sub_to'], $d['sub'] ); if ( $n !== $d['sub'] ) { $d['sub'] = $n; $ch[] = 'subtítulo'; } }
		$md = get_post_meta( $id, '_yoast_wpseo_metadesc', true );
		$md2 = 'eventos' === $key ? str_ireplace( $t['desc_from'], $t['desc_to'], $md ) : $md;
		WP_CLI::log( "#$id $key [$l]: " . ( $ch ? implode( '; ', $ch ) : 'sin cambios' ) . ( $md2 !== $md ? '; meta descripción' : '' ) );
		if ( ! $apply || ( ! $ch && $md2 === $md ) ) continue;
		$bak[ $id ] = array( '_hv_data' => $raw, '_yoast_wpseo_metadesc' => $md );
		update_post_meta( $id, '_hv_data_bak', is_array( $raw ) ? $raw : wp_slash( $raw ) );
		update_post_meta( $id, '_hv_data', wp_slash( wp_json_encode( $d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
		if ( $md2 !== $md ) update_post_meta( $id, '_yoast_wpseo_metadesc', wp_slash( $md2 ) );
	}
}
if ( $apply ) { $f = getenv( 'HOME' ) . '/hv-backups/swing-brunch-antes-' . gmdate( 'Ymd-His' ) . '.json'; file_put_contents( $f, wp_json_encode( $bak, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) ); WP_CLI::success( count( $bak ) . " páginas. Copia: $f" ); }
