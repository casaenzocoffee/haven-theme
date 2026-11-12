<?php
/*
 * Reservar (clave 'reservar'): /es/reservar/ y /en/book/. Pensada para el enlace del perfil de Instagram (11 nov 2026, pedido del gerente):
 * calendario en vivo del hotel completo (precio más bajo por noche), selector de huéspedes y, al elegir fechas,
 * las habitaciones libres esas noches con su total (IVA incluido) y un botón que abre el motor de Roiback con esa habitación de primera.
 * Datos: wp-content/uploads/hv-dispo/_hotel.json (inc/disponibilidad.php). Calendario: assets/hv-cal.js (modo _hotel).
 */
get_header();
$en    = hv_en();
$rooms = hv_rooms();
$list  = array();
$caps  = array();
foreach ( $rooms as $r ) {
	$k = hv_key( $r['id'] );
	if ( ! function_exists( 'hv_dispo_map' ) || ! isset( hv_dispo_map()[ $k ] ) ) continue;
	$cap = (int) preg_replace( '/\D/', '', (string) ( $r['per'] ?? '' ) ) ?: 2;
	$caps[ $k ] = $cap;
	$list[] = array(
		'k'   => $k,
		'n'   => $r['name'],
		'u'   => $r['url'],
		'i'   => ( $r['cover'] ?? '' ) ?: ( $r['imgs'][0] ?? '' ),
		'm2'  => $r['m2'] ?? '',
		'per' => $r['per'] ?? '',
		'cap' => $cap,
	);
}
$wa = hv_wa( $en ? 'Hello, I would like to book a room at Haven' : 'Hola, quiero reservar una habitación en Haven' );
?>
<section class="sec rs" id="c"><div class="wrap rs-wrap">
  <div class="rs-head">
    <p class="eyebrow"><?php echo e( $en ? 'Direct booking · Haven Medellín' : 'Reserva directa · Haven Medellín' ); ?></p>
    <h1 class="rs-title"><?php echo $en ? 'Choose your <em>dates</em>' : 'Elija sus <em>fechas</em>'; ?></h1>
    <p class="rs-sub"><?php echo e( $en ? 'Live availability and prices for our 17 rooms and suites in El Poblado. Breakfast included.' : 'Disponibilidad y precios en vivo de nuestras 17 habitaciones y suites en El Poblado. Desayuno incluido.' ); ?></p>
  </div>
  <div class="rs-guests" role="group" aria-label="<?php echo ea( $en ? 'Guests' : 'Huéspedes' ); ?>">
    <span><?php echo e( $en ? 'Guests' : 'Huéspedes' ); ?></span>
    <button type="button" class="rs-g" data-d="-1" aria-label="<?php echo ea( $en ? 'Fewer guests' : 'Menos huéspedes' ); ?>">−</button>
    <output id="rs-gn" aria-live="polite">2</output>
    <button type="button" class="rs-g" data-d="1" aria-label="<?php echo ea( $en ? 'More guests' : 'Más huéspedes' ); ?>">+</button>
  </div>
  <div class="hv-cal" data-room="_hotel" data-guests="2" data-caps="<?php echo ea( wp_json_encode( $caps ) ); ?>" data-api="<?php echo esc_url( rest_url( 'haven/v1/disponibilidad' ) ); ?>" data-json="<?php echo esc_url( function_exists( 'hv_dispo_json_base' ) ? hv_dispo_json_base() : '' ); ?>" data-lang="<?php echo $en ? 'en' : 'es'; ?>">
    <p class="hv-cal-load"><?php echo e( $en ? 'Loading live availability…' : 'Cargando disponibilidad en vivo…' ); ?></p>
  </div>
  <script>/* velocidad: los datos del hotel empiezan a bajar ya, sin esperar a hv-cal.js */try{window.__hvHotelP=fetch(<?php echo wp_json_encode( esc_url_raw( ( function_exists( 'hv_dispo_json_base' ) ? hv_dispo_json_base() : '' ) . '_hotel.json' ) ); ?>+'?v='+Math.floor(Date.now()/3e5),{credentials:'omit'}).then(function(r){if(!r.ok)throw 0;return r.json();});window.__hvHotelP.catch(function(){window.__hvHotelP=null;});}catch(e){}</script>
  <div class="rs-res" id="rs-res" aria-live="polite"></div>
  <p class="rs-help"><?php echo e( $en ? 'Questions or a group booking?' : '¿Dudas o reserva para un grupo?' ); ?> <a href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-track="reservar_whatsapp"><?php echo e( $en ? 'Message us on WhatsApp' : 'Escríbanos por WhatsApp' ); ?></a></p>
</div></section>
<script type="application/json" id="rs-rooms"><?php echo wp_json_encode( $list ); ?></script>
<script>
(function(){
  var EN=<?php echo $en ? 'true' : 'false'; ?>, R=JSON.parse(document.getElementById('rs-rooms').textContent), box=document.getElementById('rs-res'), gn=document.getElementById('rs-gn'), g=2, last=null;
  var T=EN?{pick:'Pick your check-in and check-out dates to see the rooms available.',none:'No room fits those dates for that many guests. Try other dates or write to us on WhatsApp.',av:'rooms available for your dates',one:'room available for your dates',n:['night','nights'],tax:'VAT incl.',book:'Book',see:'See room',from:'Total',ask:'See price in the booking engine'}
    :{pick:'Elija llegada y salida para ver las habitaciones disponibles.',none:'Ninguna habitación está libre esas fechas para esa cantidad de huéspedes. Pruebe otras fechas o escríbanos por WhatsApp.',av:'habitaciones disponibles para sus fechas',one:'habitación disponible para sus fechas',n:['noche','noches'],tax:'IVA incluido',book:'Reservar',see:'Ver habitación',from:'Total',ask:'Ver precio en el motor'};
  var BOOK='https://havenmedellin.backhotelite.com/'+(EN?'en':'es')+'/bookcore/availability/havenmedellin/';
  var money=function(p){return 'COP '+Math.round(p).toLocaleString(EN?'en-US':'es-CO');};
  var add=function(d,n){var x=new Date(d+'T12:00:00');x.setDate(x.getDate()+n);return x.getFullYear()+'-'+('0'+(x.getMonth()+1)).slice(-2)+'-'+('0'+x.getDate()).slice(-2);};
  var esc=function(s){return String(s).replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];});};
  function show(r){
    last=r;
    if(!r){box.innerHTML='<p class="rs-hint">'+T.pick+'</p>';return;}
    var H=window.hvHotel;if(!H)return;
    var nights=[];for(var d=r.ci;d<r.co;d=add(d,1))nights.push(d);
    var out=[];
    R.forEach(function(x){
      if(x.cap<g)return;var tot=0,ok=true,priced=true;
      nights.forEach(function(d){var day=H.days[d]||{};if(!(x.k in day)){ok=false;return;}if(!day[x.k])priced=false;tot+=day[x.k];});
      if(ok)out.push({x:x,t:priced?tot:0});
    });
    out.sort(function(a,b){return (a.t||9e15)-(b.t||9e15);});
    if(!out.length){box.innerHTML='<p class="rs-hint">'+T.none+'</p>';return;}
    var nn=nights.length;
    box.innerHTML='<p class="rs-count"><b>'+out.length+'</b> '+(out.length===1?T.one:T.av)+'</p>'+out.map(function(o){var x=o.x;
      var url=BOOK+r.ci+'/'+r.co+'/'+g+'/0/?hv_room='+encodeURIComponent((H.names||{})[x.k]||'');
      return '<article class="rs-room"><a class="rs-img" href="'+esc(x.u)+'" data-track="reservar_ver_'+esc(x.k)+'"><img src="'+esc(x.i)+'" alt="'+esc(x.n)+'" loading="lazy" decoding="async"></a>'
        +'<div class="rs-info"><h2>'+esc(x.n)+'</h2><p class="rs-meta">'+esc([x.m2,x.per].filter(Boolean).join(' · '))+'</p>'
        +(o.t?'<p class="rs-price"><b>'+money(o.t)+'</b> <span>'+nn+' '+T.n[nn===1?0:1]+' · '+T.tax+'</span></p>':'<p class="rs-price"><span>'+T.ask+'</span></p>')
        +'<div class="rs-act"><a class="btn btn-gold rs-book" href="'+esc(url)+'" data-k="'+esc(x.k)+'">'+T.book+' <span class="arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></a>'
        +'<a class="rs-see" href="'+esc(x.u)+'">'+T.see+'</a></div></div></article>';}).join('');
  }
  box.addEventListener('click',function(e){var a=e.target.closest('.rs-book');if(!a||!last)return;
    (window.dataLayer=window.dataLayer||[]).push({event:'haven_click',accion:'reservar_'+a.dataset.k});
    if(typeof window.hvBook==='function'){e.preventDefault();window.hvBook(a.href,last.ci,last.co,'pagina_reservar_'+a.dataset.k);}});
  document.addEventListener('hvcal:range',function(e){show(e.detail);});
  document.querySelectorAll('.rs-g').forEach(function(b){b.addEventListener('click',function(){
    var n=Math.min(8,Math.max(1,g+(+b.dataset.d)));if(n===g)return;g=n;gn.textContent=g;
    document.dispatchEvent(new CustomEvent('hvcal:guests',{detail:g}));});});
  show(null);
})();
</script>
<?php
get_footer();
