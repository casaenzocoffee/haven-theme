<?php
/*
 * Panel "Contenido Haven": edita el contenido estructurado de cada página (meta _hv_data) sin tocar código,
 * y los textos e imágenes de la portada (meta _hv_home). El formulario se arma solo a partir de los datos:
 * textos, párrafos, listas, bloques repetibles e imágenes de la biblioteca de medios.
 */
defined( 'ABSPATH' ) || exit;

/** Nombres legibles de los campos. */
function hv_field_label( $k ) {
	$L = array(
		'h1' => 'Título principal (usa <em>…</em> para la parte en cursiva)', 'title_html' => 'Título (usa <em>…</em> para la cursiva)', 'title' => 'Título', 'sub' => 'Subtítulo',
		'img' => 'Imagen', 'imgs' => 'Imágenes', 'cover' => 'Imagen de portada', 'first_img' => 'Imagen del primer bloque', 'crumb' => 'Nombre en la ruta (migas)',
		'gal_title' => 'Título de la galería', 'wa' => 'Mensaje de WhatsApp', 'sections' => 'Bloques', 'eyebrow' => 'Antetítulo', 'paras' => 'Párrafos', 'btns' => 'Botones',
		't' => 'Texto', 'href' => 'Enlace (URL, page:clave o theme:ruta)', 'ext' => 'Abrir en otra pestaña', 'lists' => 'Listas', 'intro' => 'Introducción', 'items' => 'Elementos',
		'q' => 'Pregunta', 'a' => 'Respuesta (párrafos)', 'tiers' => 'Niveles', 'name' => 'Nombre', 'cls' => 'Estilo de tarjeta (ts, t3, tdia, t4)', 'nights' => 'Noches',
		'note' => 'Nota', 'prose' => 'Texto (HTML)', 'casa_enzo' => 'Mostrar sección Casa Enzo Coffee', 'cat' => 'Categoría', 'f' => 'Filtro (penthouse, presidencial, suites, habitaciones)',
		'kicker' => 'Antetítulo', 'm2' => 'Área', 'per' => 'Personas', 'beds' => 'Camas', 'dup' => 'Dúplex', 'amen' => 'Comodidades', 'desc' => 'Descripción', 'serv' => 'Servicios',
		'slug_es' => 'Slug (español)', 'slug' => 'Slug', 'date' => 'Fecha visible', 'ex' => 'Resumen', 'published' => 'Fecha de publicación', 'modified' => 'Fecha de modificación',
		'hero' => 'Encabezado', 'tags' => 'Etiquetas', 'menu_url' => 'Menú digital (URL)', 'table_url' => 'Reservar mesa (URL)', 'form_shortcode' => 'Shortcode de formulario (opcional)',
	);
	return $L[ $k ] ?? ucfirst( str_replace( '_', ' ', (string) $k ) );
}
function hv_is_img_value( $k, $v ) {
	return is_string( $v ) && ( in_array( $k, array( 'img', 'cover', 'first_img', 'og', 'intro_img' ), true ) || preg_match( '#^(/wp-content/uploads/|theme:media/|https?://[^ ]+\.(webp|jpe?g|png|avif)$)#i', $v ) );
}

/** Dibuja un nodo del árbol de datos (recursivo). */
function hv_field_node( $k, $v, $depth = 0 ) {
	$lab = is_int( $k ) ? '' : '<label class="hvf-l">' . esc_html( hv_field_label( $k ) ) . '</label>';
	$key = is_int( $k ) ? '' : ' data-k="' . esc_attr( $k ) . '"';
	if ( is_bool( $v ) ) return '<div class="hvf-n" data-t="bool"' . $key . '><label><input type="checkbox"' . checked( $v, true, false ) . '> ' . esc_html( hv_field_label( $k ) ) . '</label></div>';
	if ( is_int( $v ) || is_float( $v ) ) return '<div class="hvf-n" data-t="num"' . $key . '>' . $lab . '<input type="number" step="any" value="' . esc_attr( $v ) . '"></div>';
	if ( is_null( $v ) || is_string( $v ) ) {
		$v = (string) $v;
		if ( hv_is_img_value( is_int( $k ) ? 'img' : $k, $v ) || ( is_int( $k ) && '' === $v && false ) ) {
			$src = $v ? hv_img_url( $v, 'thumbnail' ) : '';
			return '<div class="hvf-n hvf-img" data-t="str"' . $key . '>' . $lab . '<div class="hvf-imgrow"><img src="' . esc_url( $src ) . '" alt=""><input type="text" value="' . esc_attr( $v ) . '"><button type="button" class="button hvf-pick">Elegir imagen</button></div></div>';
		}
		$long = mb_strlen( $v ) > 90 || false !== strpos( $v, "\n" ) || in_array( $k, array( 'prose', 'intro', 'note' ), true );
		$rows = 'prose' === $k ? 18 : max( 3, min( 10, (int) ceil( mb_strlen( $v ) / 110 ) ) );
		return '<div class="hvf-n" data-t="str"' . $key . '>' . $lab . ( $long ? '<textarea rows="' . $rows . '">' . esc_textarea( $v ) . '</textarea>' : '<input type="text" value="' . esc_attr( $v ) . '">' ) . '</div>';
	}
	if ( is_array( $v ) ) {
		$is_list = array_keys( $v ) === range( 0, count( $v ) - 1 ) || ! $v;
		if ( $is_list ) {
			$out = '<fieldset class="hvf-n hvf-list" data-t="list"' . $key . '><legend>' . esc_html( is_int( $k ) ? 'Lista' : hv_field_label( $k ) ) . ' <small>(' . count( $v ) . ')</small></legend><div class="hvf-items">';
			foreach ( $v as $i => $x ) $out .= '<div class="hvf-item"><div class="hvf-tools"><button type="button" class="button-link hvf-up" title="Subir">↑</button><button type="button" class="button-link hvf-down" title="Bajar">↓</button><button type="button" class="button-link hvf-del" title="Quitar">✕</button></div>' . hv_field_node( $i, $x, $depth + 1 ) . '</div>';
			// Plantilla para agregar: copia vacía del primer elemento (o un texto).
			$proto = $v ? hv_field_blank( $v[0] ) : '';
			$out .= '</div><template>' . '<div class="hvf-item"><div class="hvf-tools"><button type="button" class="button-link hvf-up">↑</button><button type="button" class="button-link hvf-down">↓</button><button type="button" class="button-link hvf-del">✕</button></div>' . hv_field_node( 0, $proto, $depth + 1 ) . '</div></template><button type="button" class="button hvf-add">+ Agregar</button></fieldset>';
			return $out;
		}
		$out = '<fieldset class="hvf-n hvf-obj" data-t="obj"' . $key . '>' . ( is_int( $k ) ? '' : '<legend>' . esc_html( hv_field_label( $k ) ) . '</legend>' );
		foreach ( $v as $kk => $x ) $out .= hv_field_node( (string) $kk, $x, $depth + 1 );
		return $out . '</fieldset>';
	}
	return '';
}
function hv_field_blank( $v ) {
	if ( is_bool( $v ) ) return false;
	if ( is_int( $v ) || is_float( $v ) ) return 0;
	if ( is_array( $v ) ) {
		$is_list = array_keys( $v ) === range( 0, count( $v ) - 1 );
		if ( $is_list ) return array();
		$o = array();
		foreach ( $v as $k => $x ) $o[ $k ] = hv_field_blank( $x );
		return $o;
	}
	return '';
}

add_action( 'add_meta_boxes', function ( $type, $post ) {
	if ( ! in_array( $type, array( 'page', 'post' ), true ) ) return;
	$key = get_post_meta( $post->ID, '_hv_key', true );
	if ( ! $key ) return;
	add_meta_box( 'hv-content', 'Contenido Haven · ' . esc_html( $key ), 'hv_meta_box', $type, 'normal', 'high' );
}, 10, 2 );

function hv_meta_box( $post ) {
	wp_nonce_field( 'hv_save_' . $post->ID, 'hv_nonce' );
	$key = get_post_meta( $post->ID, '_hv_key', true );
	echo '<p class="description">Este contenido es el que muestra el diseño Haven en esta página. Los cambios se ven al <strong>Actualizar</strong>. El editor de bloques/Elementor de abajo no se usa en esta página.</p>';
	if ( 'home' === $key ) { hv_home_box( $post ); return; }
	if ( 0 === strpos( $key, 'room-' ) ) {
		echo '<p><label class="hvf-l" for="hv_order">Orden en el listado de habitaciones</label><input type="number" id="hv_order" name="hv_order" min="1" style="width:90px" value="' . esc_attr( get_post_meta( $post->ID, '_hv_order', true ) ) . '"></p>';
	}
	$raw  = (string) get_post_meta( $post->ID, '_hv_data', true );
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) $data = array();
	echo '<div class="hvf-root" data-t="obj">';
	foreach ( $data as $k => $v ) echo hv_field_node( (string) $k, $v );
	echo '</div><input type="hidden" name="hv_json" class="hvf-out" value="">';
	echo '<details class="hvf-raw"><summary>Avanzado: editar como JSON</summary><p class="description">Si escribes aquí, este JSON reemplaza al formulario de arriba.</p><textarea name="hv_json_raw" rows="12" style="width:100%;font-family:monospace"></textarea><p><button type="button" class="button hvf-load-raw">Cargar JSON actual</button></p></details>';
	echo '<script type="application/json" class="hvf-orig">' . wp_json_encode( $data, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE ) . '</script>';
}

/** Portada: textos e imágenes con sus valores por defecto, agrupados por sección. */
function hv_home_box( $post ) {
	$lang = function_exists( 'pll_get_post_language' ) ? ( pll_get_post_language( $post->ID ) ?: 'es' ) : 'es';
	$def  = hv_home_defaults()[ $lang ] ?? array( 't' => array(), 'img' => array() );
	$ov   = json_decode( (string) get_post_meta( $post->ID, '_hv_home', true ), true ) ?: array();
	$tpl  = (string) file_get_contents( HAVEN_DIR . '/partials/home.html' );
	// Sección de cada marcador (id o clase de la <section> más cercana).
	$where = array();
	$cur   = 'Inicio';
	foreach ( preg_split( '#(<(?:section|header|footer|div class="[^"]*(?:hero|intro)[^"]*")[^>]*>|\{\{(?:t|ta|img):\d+\}\})#', $tpl, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY ) as $p ) {
		if ( preg_match( '#^<(section|header|footer|div)#', $p ) ) {
			if ( preg_match( '#\bid="([^"]+)"#', $p, $m ) ) $cur = $m[1];
			elseif ( preg_match( '#\bclass="([^" ]+)#', $p, $m ) ) $cur = $m[1];
		} elseif ( preg_match( '#^\{\{(t|ta|img):(\d+)\}\}$#', $p, $m ) ) {
			$where[ ( 'img' === $m[1] ? 'img' : 't' ) . $m[2] ] = $where[ ( 'img' === $m[1] ? 'img' : 't' ) . $m[2] ] ?? $cur;
		}
	}
	echo '<p class="description">Deja un campo igual al texto original para usar el valor por defecto. Idioma: <strong>' . esc_html( strtoupper( $lang ) ) . '</strong>.</p><div class="hvf-home">';
	$last = '';
	foreach ( $def['t'] as $i => $d ) {
		$sec = $where[ 't' . $i ] ?? 'otros';
		if ( $sec !== $last ) { echo '<h3 class="hvf-sec">' . esc_html( $sec ) . '</h3>'; $last = $sec; }
		$v = $ov['t'][ $i ] ?? $d;
		$long = mb_strlen( $d ) > 90;
		echo '<div class="hvf-n"><label class="hvf-l">#' . (int) $i . '</label>' . ( $long ? '<textarea name="hv_home[t][' . (int) $i . ']" rows="3">' . esc_textarea( $v ) . '</textarea>' : '<input type="text" name="hv_home[t][' . (int) $i . ']" value="' . esc_attr( $v ) . '">' ) . '</div>';
	}
	echo '<h3 class="hvf-sec">Imágenes</h3>';
	foreach ( $def['img'] as $i => $d ) {
		$v = $ov['img'][ $i ] ?? $d;
		echo '<div class="hvf-n hvf-img"><label class="hvf-l">#' . (int) $i . ' · ' . esc_html( $where[ 'img' . $i ] ?? '' ) . '</label><div class="hvf-imgrow"><img src="' . esc_url( hv_img_url( $v, 'thumbnail' ) ) . '" alt=""><input type="text" name="hv_home[img][' . (int) $i . ']" value="' . esc_attr( $v ) . '"><button type="button" class="button hvf-pick">Elegir imagen</button></div></div>';
	}
	echo '</div>';
}

add_action( 'save_post', function ( $id, $post ) {
	if ( wp_is_post_revision( $id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) return;
	if ( empty( $_POST['hv_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hv_nonce'] ) ), 'hv_save_' . $id ) ) return;
	if ( ! current_user_can( 'edit_post', $id ) ) return;
	$can_html = current_user_can( 'unfiltered_html' );
	$clean = function ( $v ) use ( &$clean, $can_html ) {
		if ( is_array( $v ) ) { $o = array(); foreach ( $v as $k => $x ) $o[ $k ] = $clean( $x ); return $o; }
		if ( is_string( $v ) ) return $can_html ? $v : wp_kses_post( $v );
		return $v;
	};
	// Portada.
	if ( isset( $_POST['hv_home'] ) && is_array( $_POST['hv_home'] ) ) {
		$lang = function_exists( 'pll_get_post_language' ) ? ( pll_get_post_language( $id ) ?: 'es' ) : 'es';
		$def  = hv_home_defaults()[ $lang ] ?? array( 't' => array(), 'img' => array() );
		$in   = wp_unslash( $_POST['hv_home'] );
		$ov   = array( 't' => array(), 'img' => array() );
		foreach ( array( 't', 'img' ) as $g ) foreach ( (array) ( $in[ $g ] ?? array() ) as $i => $v ) {
			$v = trim( (string) $v );
			if ( '' !== $v && $v !== ( $def[ $g ][ (int) $i ] ?? null ) ) $ov[ $g ][ (int) $i ] = 'img' === $g ? esc_url_raw( $v ) : $clean( $v );
		}
		if ( $ov['t'] || $ov['img'] ) update_post_meta( $id, '_hv_home', wp_slash( wp_json_encode( $ov, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
		else delete_post_meta( $id, '_hv_home' );
		return;
	}
	if ( isset( $_POST['hv_order'] ) && '' !== $_POST['hv_order'] ) update_post_meta( $id, '_hv_order', absint( $_POST['hv_order'] ) );
	$raw  = isset( $_POST['hv_json_raw'] ) ? trim( (string) wp_unslash( $_POST['hv_json_raw'] ) ) : '';
	$json = '' !== $raw ? $raw : ( isset( $_POST['hv_json'] ) ? (string) wp_unslash( $_POST['hv_json'] ) : '' );
	if ( '' === $json ) return; // sin JavaScript: no se toca nada.
	$data = json_decode( $json, true );
	if ( ! is_array( $data ) ) return;
	$old = get_post_meta( $id, '_hv_data', true );
	if ( $old ) update_post_meta( $id, '_hv_data_bak', wp_slash( $old ) );
	update_post_meta( $id, '_hv_data', wp_slash( wp_json_encode( $clean( $data ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
}, 10, 2 );

/* Estilos y JavaScript del panel (solo en la pantalla de edición). */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) return;
	wp_enqueue_media();
	wp_add_inline_style( 'wp-admin', '
#hv-content .hvf-n{margin:0 0 12px}#hv-content .hvf-l{display:block;font-weight:600;margin:0 0 4px}
#hv-content input[type=text],#hv-content textarea{width:100%}#hv-content fieldset{border:1px solid #dcdcde;border-radius:6px;padding:10px 14px 4px;margin:0 0 14px;background:#fff}
#hv-content fieldset fieldset{background:#f6f7f7}#hv-content legend{font-weight:600;padding:0 6px}
#hv-content .hvf-item{position:relative;border-left:3px solid #b8955a;padding:6px 0 2px 12px;margin:0 0 10px}
#hv-content .hvf-tools{position:absolute;right:0;top:0;display:flex;gap:6px;z-index:2}#hv-content .hvf-tools button{font-size:14px;text-decoration:none}
#hv-content .hvf-imgrow{display:flex;gap:8px;align-items:center}#hv-content .hvf-imgrow img{width:64px;height:48px;object-fit:cover;border-radius:4px;background:#eee;flex:none}
#hv-content .hvf-sec{margin:22px 0 8px;padding-top:10px;border-top:1px solid #dcdcde;text-transform:capitalize}' );
	wp_add_inline_script( 'jquery-core', <<<'JS'
jQuery(function($){
  var box=$('#hv-content'); if(!box.length) return;
  function val(n){
    var t=n.attr('data-t');
    if(t==='bool') return n.find('> label > input').prop('checked');
    if(t==='num'){var x=parseFloat(n.find('> input').val());return isNaN(x)?0:x;}
    if(t==='str') return n.find('input[type=text],textarea').first().val();
    if(t==='list'){var a=[];n.find('> .hvf-items > .hvf-item').each(function(){var c=$(this).children('.hvf-n').first();if(c.length)a.push(val(c));});return a;}
    var o={};n.children('.hvf-n[data-k]').each(function(){o[$(this).attr('data-k')]=val($(this));});return o;
  }
  function out(){var r=box.find('.hvf-root');if(r.length) box.find('.hvf-out').val(JSON.stringify(val(r)));}
  $('form#post').on('submit',out);
  // Editor de bloques: guarda las metas por el formulario clásico; actualizamos el campo en cada cambio.
  box.on('input change click','input,textarea,button',function(){setTimeout(out,0);}); out();
  box.on('click','.hvf-add',function(){var fs=$(this).closest('fieldset');var tpl=fs.children('template')[0];fs.children('.hvf-items').append($(tpl.innerHTML));fs.find('> legend small').text('('+fs.find('> .hvf-items > .hvf-item').length+')');out();});
  box.on('click','.hvf-del',function(){if(confirm('¿Quitar este elemento?')){$(this).closest('.hvf-item').remove();out();}});
  box.on('click','.hvf-up',function(){var i=$(this).closest('.hvf-item');i.prev('.hvf-item').before(i);out();});
  box.on('click','.hvf-down',function(){var i=$(this).closest('.hvf-item');i.next('.hvf-item').after(i);out();});
  box.on('click','.hvf-pick',function(e){e.preventDefault();var row=$(this).closest('.hvf-imgrow');var f=wp.media({title:'Elegir imagen',multiple:false,library:{type:'image'}});
    f.on('select',function(){var a=f.state().get('selection').first().toJSON();var u=a.url.replace(/^https?:\/\/[^\/]+/,'');row.find('input').val(u).trigger('change');row.find('img').attr('src',(a.sizes&&a.sizes.thumbnail?a.sizes.thumbnail.url:a.url));out();});f.open();});
  box.on('click','.hvf-load-raw',function(){box.find('textarea[name=hv_json_raw]').val(JSON.stringify(val(box.find('.hvf-root')),null,2));});
});
JS
	);
} );

/** Páginas con diseño Haven: editor clásico (el contenido real está en el panel "Contenido Haven"). */
add_filter( 'use_block_editor_for_post', function ( $use, $post ) {
	return ( $post && get_post_meta( $post->ID, '_hv_key', true ) ) ? false : $use;
}, 10, 2 );
