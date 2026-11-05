<?php
/*
 * Páginas por intención de búsqueda y páginas de la guía (auditoría SEO, 5 nov 2026).
 * Datos en _hv_data: h1, sub, img, crumb, eyebrow, parent (clave), updated, blocks[].
 * Bloques: text, rooms, places, days, faq, links, cta. Los datos estructurados (FAQPage, ItemList, TouristTrip) los agrega inc/seo-fix.php.
 */
get_header();
$d      = hv_data();
$en     = hv_en();
$blocks = (array) ( $d['blocks'] ?? array() );
$crumb  = $d['crumb'] ?? get_the_title();
$parent = $d['parent'] ?? '';
$crumbs = array( array( t( 'Inicio' ), hv_url( 'home' ) ) );
if ( $parent ) $crumbs[] = array( $en ? 'Medellín guide' : 'Guía de Medellín', hv_url( $parent ) );
$crumbs[] = array( $crumb, '' );

// Primera imagen disponible para la portada si la página no trae una.
$img = $d['img'] ?? '';
if ( ! $img ) foreach ( $blocks as $b ) { if ( ! empty( $b['img'] ) ) { $img = $b['img']; break; } }
if ( ! $img ) $img = '/wp-content/uploads/2026/06/hotel-haven-medellin.webp';
echo hv_hero( $d['h1'] ?? e( get_the_title() ), $d['sub'] ?? '', $img, $crumbs, $d['eyebrow'] ?? ( $parent ? ( $en ? 'Medellín from Haven' : 'Medellín desde Haven' ) : 'Haven · El Poblado' ) );

// Fecha de actualización y autor (confianza y E-E-A-T), visibles bajo la portada.
$upd = $d['updated'] ?? get_the_modified_date( 'Y-m-d' );
$ts  = strtotime( $upd );
$fmt = hv_fecha( $ts, $en );
echo '<div class="lp-meta wrap"><span>' . e( $en ? 'By the Haven concierge' : 'Por el Concierge de Haven' ) . '</span><span aria-hidden="true">·</span><time datetime="' . ea( date( 'Y-m-d', $ts ) ) . '">' . e( ( $en ? 'Updated ' : 'Actualizado el ' ) . $fmt ) . '</time></div>';

hv_render_blocks( $blocks, array( 'crumb' => $crumb ) );
echo hv_bookbar();
get_footer();
