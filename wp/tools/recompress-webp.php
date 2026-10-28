<?php
/*
 * Recomprime las fotos WebP pesadas de la biblioteca de medios (mismo tamaño en píxeles, misma URL).
 * Guarda el original en ~/hv-backups/uploads-orig/<ruta> y solo reemplaza si el nuevo pesa al menos 15 % menos.
 * Uso (solo por consola): php recompress-webp.php /ruta/a/wp-content/uploads [calidad=80] [minKB=250] [--dry]
 */
if ( PHP_SAPI !== 'cli' ) exit( "Solo por consola\n" );
$root = rtrim( $argv[1] ?? '', '/' );
$q    = (int) ( $argv[2] ?? 80 );
$min  = (int) ( $argv[3] ?? 250 ) * 1024;
$dry  = in_array( '--dry', $argv, true );
if ( ! is_dir( $root ) || ! function_exists( 'imagewebp' ) ) exit( "Carpeta o GD/WebP no disponible\n" );
$bk   = getenv( 'HOME' ) . '/hv-backups/uploads-orig';
$it   = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
$n = $saved = $before = $after = 0;
foreach ( $it as $f ) {
	$p = $f->getPathname();
	if ( strtolower( $f->getExtension() ) !== 'webp' || $f->getSize() < $min ) continue;
	$im = @imagecreatefromwebp( $p );
	if ( ! $im ) { echo "no se pudo leer: $p\n"; continue; }
	imagepalettetotruecolor( $im ); imagealphablending( $im, false ); imagesavealpha( $im, true );
	$tmp = $p . '.hvtmp';
	imagewebp( $im, $tmp, $q ); imagedestroy( $im );
	$old = $f->getSize(); $new = filesize( $tmp );
	if ( $new > 0 && $new < $old * 0.85 ) {
		$rel = substr( $p, strlen( $root ) + 1 );
		if ( ! $dry ) {
			$dst = $bk . '/' . $rel;
			if ( ! is_dir( dirname( $dst ) ) ) mkdir( dirname( $dst ), 0755, true );
			if ( ! file_exists( $dst ) ) copy( $p, $dst );
			rename( $tmp, $p );
		} else { unlink( $tmp ); }
		$n++; $before += $old; $after += $new;
		printf( "%4dKB -> %4dKB  %s\n", $old / 1024, $new / 1024, $rel );
	} else { unlink( $tmp ); }
}
printf( "\n%d fotos %s: %.1f MB -> %.1f MB\n", $n, $dry ? 'se recomprimirían' : 'recomprimidas', $before / 1048576, $after / 1048576 );
