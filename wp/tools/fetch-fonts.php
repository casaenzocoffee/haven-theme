<?php
/*
 * Descarga las fuentes del sitio (Playfair Display + DM Sans) desde Google Fonts y las deja en wp-content/uploads/hv-fonts/
 * para servirlas desde el mismo dominio (sin conexiones extra a Google). El tema las usa si existe hv-fonts/fonts.css.
 * Uso (solo por consola): php fetch-fonts.php /ruta/a/wp-content/uploads
 */
if ( PHP_SAPI !== 'cli' ) exit( "Solo por consola\n" );
$up  = rtrim( $argv[1] ?? '', '/' );
$dir = $up . '/hv-fonts';
if ( ! is_dir( $up ) ) exit( "No existe $up\n" );
if ( ! is_dir( $dir ) ) mkdir( $dir, 0755, true );
$url = 'https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap';
$ua  = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36';
$get = function ( $u ) use ( $ua ) { $c = curl_init( $u ); curl_setopt_array( $c, array( CURLOPT_RETURNTRANSFER => true, CURLOPT_USERAGENT => $ua, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 30 ) ); $r = curl_exec( $c ); $code = curl_getinfo( $c, CURLINFO_HTTP_CODE ); curl_close( $c ); return 200 === $code ? $r : false; };
$css = $get( $url );
if ( ! $css ) exit( "No se pudo leer la hoja de Google Fonts\n" );
$preload = array();
$css = preg_replace_callback( '#(/\*\s*([a-z-]+)\s*\*/\s*)?@font-face\s*\{([^}]*)\}#', function ( $m ) use ( $get, $dir, &$preload ) {
	$block = $m[3]; $subset = $m[2] ?? '';
	if ( ! preg_match( '#url\((https://[^)]+\.woff2)\)#', $block, $u ) ) return $m[0];
	preg_match( '#font-family:\s*\'([^\']+)\'#', $block, $fam ); preg_match( '#font-style:\s*(\w+)#', $block, $st ); preg_match( '#font-weight:\s*([\d ]+)#', $block, $w );
	// Un archivo por URL (las fuentes variables comparten archivo entre pesos).
	$name = strtolower( preg_replace( '#[^a-z0-9]+#i', '-', ( $fam[1] ?? 'f' ) . '-' . ( $st[1] ?? '' ) . '-' . ( $subset ?: 'x' ) . '-' . substr( md5( $u[1] ), 0, 6 ) ) ) . '.woff2';
	if ( ! file_exists( "$dir/$name" ) ) { $b = $get( $u[1] ); if ( ! $b ) return $m[0]; file_put_contents( "$dir/$name", $b ); }
	if ( 'latin' === $subset && ( ( 'normal' === ( $st[1] ?? '' ) && false !== strpos( $fam[1] ?? '', 'Playfair' ) && '400' === trim( $w[1] ?? '' ) ) || false !== strpos( $fam[1] ?? '', 'DM Sans' ) ) ) $preload[ $name ] = 1;
	return ( $m[1] ?? '' ) . '@font-face{' . str_replace( $u[1], 'FONTDIR/' . $name, $block ) . '}';
}, $css );
file_put_contents( "$dir/fonts.css", $css );
file_put_contents( "$dir/preload.json", json_encode( array_keys( $preload ) ) );
echo count( glob( "$dir/*.woff2" ) ) . " archivos de fuente; precarga: " . implode( ', ', array_keys( $preload ) ) . "\n";
