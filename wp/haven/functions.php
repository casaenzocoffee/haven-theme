<?php
/**
 * Haven, A Design Hotel — tema propio.
 * Diseño del prototipo (havenmedellin.com/prototipo-haven) llevado a WordPress:
 * contenido editable por página (campos "Contenido Haven"), Polylang ES/EN, Yoast SEO y GTM4WP.
 */
defined( 'ABSPATH' ) || exit;

define( 'HAVEN_VER', '1.0.0' );
define( 'HAVEN_DIR', get_template_directory() );
define( 'HAVEN_URI', get_template_directory_uri() );

require HAVEN_DIR . '/inc/helpers.php';
require HAVEN_DIR . '/inc/components.php';
require HAVEN_DIR . '/inc/partials.php';
require HAVEN_DIR . '/inc/setup.php';
require HAVEN_DIR . '/inc/router.php';
require HAVEN_DIR . '/inc/seo.php';
require HAVEN_DIR . '/inc/fields.php';
require HAVEN_DIR . '/inc/importer.php';
