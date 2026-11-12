<?php
/* Pie: redes, ubicación con mapa, llamado a reservar, enlaces, HAVEN animado y barra del celular. */
defined( 'ABSPATH' ) || exit;
hv_render_footer();
/* Black Week 2026 (13 nov–3 dic; 35 % ya aplicado por Roiback en la tarifa no reembolsable, sin código desde el 12 nov): banner bajo la portada y pop-up, solo en la página de inicio (ES y EN).
   Se muestran solos en esas fechas; para verlos antes se abre el inicio con ?vista-previa. Este bloque se puede borrar después del 3 de diciembre. */
$hv_bw    = is_front_page() && wp_date( 'Y-m-d' ) <= '2026-12-03';
$hv_bw_en = function_exists( 'pll_current_language' ) && 'en' === pll_current_language();
if ( $hv_bw && ! $hv_bw_en ) : ?>
<!-- Haven · Black Week ES: banner bajo la portada + pop-up. Visible solo del 13 de noviembre al 3 de diciembre de 2026 (vista previa: ?vista-previa) -->
<!-- Haven · Banner Black Week para la página de inicio (ES). Se ve solo del 13 de noviembre al 3 de diciembre; para revisarlo antes, abre la página con ?vista-previa al final de la URL -->
<style>
.hvt-bw[hidden],.hvt-pop[hidden]{display:none!important}
.hvt-bw{padding:clamp(28px,4vw,56px) 0}
.hvt-bw-card{display:grid;grid-template-columns:1.25fr 1fr;align-items:center;gap:clamp(24px,4vw,64px);padding:clamp(28px,4.5vw,64px);border-radius:var(--r,28px);background:var(--espresso,#2E241E);box-shadow:inset 0 0 0 1px var(--line)}
.hvt-bw h2,.hvt-pop h2{font-size:clamp(40px,5vw,76px);line-height:.98;margin:18px 0 0}
.hvt-bw-text{color:var(--muted);font-size:17px;line-height:1.6;margin:18px 0 0;max-width:40ch}
.hvt-bw-side{display:flex;flex-direction:column;align-items:flex-start;gap:18px;justify-self:end}
.hvt-bw-code{display:inline-block;font:600 13px/1 var(--sans);letter-spacing:.2em;text-transform:uppercase;color:var(--champagne);padding:14px 18px;border:1px dashed var(--gold);border-radius:14px}
.hvt-pop{position:fixed;inset:0;z-index:99999;display:grid;place-items:center;padding:20px;background:rgba(25,19,17,.72);-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px);opacity:0;transition:opacity .35s}
.hvt-pop.is-open{opacity:1}
.hvt-pop-card{position:relative;width:min(780px,100%);max-height:calc(100svh - 40px);overflow:auto;display:grid;grid-template-columns:.9fr 1.1fr;border-radius:28px;background:var(--ink,#191311);color:var(--fg,#FDF5E5);box-shadow:0 40px 80px -30px rgba(0,0,0,.8),inset 0 0 0 1px var(--line);transform:translateY(16px);transition:transform .45s var(--ease)}
.hvt-pop.is-open .hvt-pop-card{transform:none}
.hvt-pop-media{background:var(--espresso,#2E241E);border-radius:999px 999px 0 0;margin:18px 0 18px 18px;overflow:hidden}
.hvt-pop-media img{width:100%;height:100%;object-fit:cover;display:block}
.hvt-pop-body{padding:clamp(28px,4vw,48px)}
.hvt-pop h2{font-size:clamp(36px,4.2vw,56px)}
.hvt-pop .hvt-bw-text{font-size:16px}
.hvt-pop .hvt-bw-code{margin-top:22px}
.hvt-pop .btn{margin-top:22px}
.hvt-pop-close{position:absolute;top:14px;right:14px;z-index:2;width:42px;height:42px;border-radius:50%;border:0;background:rgba(25,19,17,.7);color:var(--cream,#FDF5E5);display:grid;place-items:center;cursor:pointer;box-shadow:inset 0 0 0 1px var(--line)}
.hvt-pop-close:hover,.hvt-pop-later:hover{color:var(--gold)}
.hvt-pop-later{display:block;margin-top:16px;padding:8px 0;background:none;border:0;color:var(--muted);font:500 13px var(--sans);text-decoration:underline;text-underline-offset:4px;cursor:pointer}
.hvt-pop-close:focus-visible,.hvt-pop-later:focus-visible,.hvt-pop .btn:focus-visible,.hvt-bw .btn:focus-visible{outline:2px solid var(--champagne);outline-offset:3px}
@media (max-width:820px){.hvt-bw-card{grid-template-columns:1fr}.hvt-bw-side{justify-self:start}}
@media (max-width:640px){.hvt-pop-card{grid-template-columns:1fr}.hvt-pop-media{height:170px;margin:14px 14px 0;border-radius:999px 999px 18px 18px}.hvt-pop-body{padding:24px 24px 28px}.hvt-pop .btn{width:100%;justify-content:space-between}}
@media (prefers-reduced-motion:reduce){.hvt-pop,.hvt-pop-card{transition:none}}
</style>
<section class="hvt-bw" id="hvt-banner-bw" data-desde="2026-11-12" data-hasta="2026-12-03" hidden><div class="wrap"><div class="hvt-bw-card">
<div><p class="eyebrow">Black Week · 13 de noviembre al 3 de diciembre</p><h2>35% en tu <em>estadía</em></h2><p class="hvt-bw-text">El descuento ya viene aplicado en la tarifa no reembolsable al reservar en nuestra web. No necesitas código.</p></div>
<div class="hvt-bw-side"><a class="btn btn-gold" href="#reservar" data-track="banner_blackweek">Reservar ahora <span class="arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8"/></svg></span></a></div>
</div></div></section>
<script>(function(){var s=document.getElementById("hvt-banner-bw");if(!s)return;var prev=/vista-previa/.test(location.search)||window.HVT_PREVIEW===true;var hoy;try{hoy=new Intl.DateTimeFormat('en-CA',{timeZone:'America/Bogota',year:'numeric',month:'2-digit',day:'2-digit'}).format(new Date());}catch(e){var d=new Date();hoy=d.getFullYear()+'-'+('0'+(d.getMonth()+1)).slice(-2)+'-'+('0'+d.getDate()).slice(-2);}if(prev||(hoy>=s.getAttribute("data-desde")&&hoy<=s.getAttribute("data-hasta")))s.hidden=false;})();</script>
<!-- Haven · Pop-up Black Week (ES). Sale solo del 13 de noviembre al 3 de diciembre, a los 5 segundos, una vez por visita; para revisarlo antes, abre la página con ?vista-previa al final de la URL -->
<div class="hvt-pop" id="hvt-popup-bw" role="dialog" aria-modal="true" aria-labelledby="hvt-pop-title" data-desde="2026-11-12" data-hasta="2026-12-03" hidden><div class="hvt-pop-card">
<button type="button" class="hvt-pop-close" aria-label="Cerrar"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
<div class="hvt-pop-media"><img src="https://havenmedellin.com/wp-content/uploads/2026/06/presidential-suite-room-jacuzzi-lounge-area-1-768x512.webp" alt="Suite Presidencial de Haven con jacuzzi y sala" loading="lazy" decoding="async"></div>
<div class="hvt-pop-body"><p class="eyebrow">Black Week · 13 de noviembre al 3 de diciembre</p><h2 id="hvt-pop-title">35% en tu <em>estadía</em></h2><p class="hvt-bw-text">El descuento ya viene aplicado en la tarifa no reembolsable al reservar en nuestra web. No necesitas código.</p><div><a class="btn btn-gold" href="#reservar" data-track="popup_blackweek">Reservar ahora <span class="arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8"/></svg></span></a></div><button type="button" class="hvt-pop-later">Ahora no</button></div>
</div></div>
<script>(function(){var pop=document.getElementById("hvt-popup-bw");if(!pop)return;var prev=/vista-previa/.test(location.search)||window.HVT_PREVIEW===true;var hoy;try{hoy=new Intl.DateTimeFormat('en-CA',{timeZone:'America/Bogota',year:'numeric',month:'2-digit',day:'2-digit'}).format(new Date());}catch(e){var d=new Date();hoy=d.getFullYear()+'-'+('0'+(d.getMonth()+1)).slice(-2)+'-'+('0'+d.getDate()).slice(-2);}if(!prev&&(hoy<pop.getAttribute("data-desde")||hoy>pop.getAttribute("data-hasta")))return;var KEY="hvt-popup-bw-2026";try{if(!prev&&sessionStorage.getItem(KEY))return;}catch(e){}document.body.appendChild(pop);var last;function foc(){return pop.querySelectorAll("a[href],button");}function onKey(e){if(e.key==="Escape"){cerrar();return;}if(e.key==="Tab"){var f=foc(),a=f[0],z=f[f.length-1];if(e.shiftKey&&document.activeElement===a){e.preventDefault();z.focus();}else if(!e.shiftKey&&document.activeElement===z){e.preventDefault();a.focus();}}}function abrir(){last=document.activeElement;pop.hidden=false;requestAnimationFrame(function(){pop.classList.add("is-open");});pop.querySelector(".hvt-pop-close").focus();document.addEventListener("keydown",onKey);try{sessionStorage.setItem(KEY,"1");}catch(e){}}function cerrar(){pop.classList.remove("is-open");document.removeEventListener("keydown",onKey);setTimeout(function(){pop.hidden=true;},320);if(last&&last.focus)last.focus();}pop.addEventListener("click",function(e){if(e.target===pop)cerrar();});pop.querySelectorAll(".hvt-pop-close,.hvt-pop-later").forEach(function(x){x.addEventListener("click",cerrar);});window.addEventListener("click",function(e){if(e.target.closest&&e.target.closest('#hvt-popup-bw a[href="#reservar"]'))cerrar();},true);setTimeout(abrir,prev?600:5000);})();</script>
<script>(function(){var b=document.getElementById("hvt-banner-bw"),h=document.querySelector("main section.hero");if(b&&h&&h.parentNode)h.parentNode.insertBefore(b,h.nextSibling);else if(b)b.parentNode.removeChild(b);})();</script>
<?php elseif ( $hv_bw ) : ?>
<!-- Haven · Black Week EN: banner bajo la portada + pop-up. Visible solo del 13 de noviembre al 3 de diciembre de 2026 (vista previa: ?vista-previa) -->
<!-- Haven · Banner Black Week para la página de inicio (EN). Se ve solo del 13 de noviembre al 3 de diciembre; para revisarlo antes, abre la página con ?vista-previa al final de la URL -->
<style>
.hvt-bw[hidden],.hvt-pop[hidden]{display:none!important}
.hvt-bw{padding:clamp(28px,4vw,56px) 0}
.hvt-bw-card{display:grid;grid-template-columns:1.25fr 1fr;align-items:center;gap:clamp(24px,4vw,64px);padding:clamp(28px,4.5vw,64px);border-radius:var(--r,28px);background:var(--espresso,#2E241E);box-shadow:inset 0 0 0 1px var(--line)}
.hvt-bw h2,.hvt-pop h2{font-size:clamp(40px,5vw,76px);line-height:.98;margin:18px 0 0}
.hvt-bw-text{color:var(--muted);font-size:17px;line-height:1.6;margin:18px 0 0;max-width:40ch}
.hvt-bw-side{display:flex;flex-direction:column;align-items:flex-start;gap:18px;justify-self:end}
.hvt-bw-code{display:inline-block;font:600 13px/1 var(--sans);letter-spacing:.2em;text-transform:uppercase;color:var(--champagne);padding:14px 18px;border:1px dashed var(--gold);border-radius:14px}
.hvt-pop{position:fixed;inset:0;z-index:99999;display:grid;place-items:center;padding:20px;background:rgba(25,19,17,.72);-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px);opacity:0;transition:opacity .35s}
.hvt-pop.is-open{opacity:1}
.hvt-pop-card{position:relative;width:min(780px,100%);max-height:calc(100svh - 40px);overflow:auto;display:grid;grid-template-columns:.9fr 1.1fr;border-radius:28px;background:var(--ink,#191311);color:var(--fg,#FDF5E5);box-shadow:0 40px 80px -30px rgba(0,0,0,.8),inset 0 0 0 1px var(--line);transform:translateY(16px);transition:transform .45s var(--ease)}
.hvt-pop.is-open .hvt-pop-card{transform:none}
.hvt-pop-media{background:var(--espresso,#2E241E);border-radius:999px 999px 0 0;margin:18px 0 18px 18px;overflow:hidden}
.hvt-pop-media img{width:100%;height:100%;object-fit:cover;display:block}
.hvt-pop-body{padding:clamp(28px,4vw,48px)}
.hvt-pop h2{font-size:clamp(36px,4.2vw,56px)}
.hvt-pop .hvt-bw-text{font-size:16px}
.hvt-pop .hvt-bw-code{margin-top:22px}
.hvt-pop .btn{margin-top:22px}
.hvt-pop-close{position:absolute;top:14px;right:14px;z-index:2;width:42px;height:42px;border-radius:50%;border:0;background:rgba(25,19,17,.7);color:var(--cream,#FDF5E5);display:grid;place-items:center;cursor:pointer;box-shadow:inset 0 0 0 1px var(--line)}
.hvt-pop-close:hover,.hvt-pop-later:hover{color:var(--gold)}
.hvt-pop-later{display:block;margin-top:16px;padding:8px 0;background:none;border:0;color:var(--muted);font:500 13px var(--sans);text-decoration:underline;text-underline-offset:4px;cursor:pointer}
.hvt-pop-close:focus-visible,.hvt-pop-later:focus-visible,.hvt-pop .btn:focus-visible,.hvt-bw .btn:focus-visible{outline:2px solid var(--champagne);outline-offset:3px}
@media (max-width:820px){.hvt-bw-card{grid-template-columns:1fr}.hvt-bw-side{justify-self:start}}
@media (max-width:640px){.hvt-pop-card{grid-template-columns:1fr}.hvt-pop-media{height:170px;margin:14px 14px 0;border-radius:999px 999px 18px 18px}.hvt-pop-body{padding:24px 24px 28px}.hvt-pop .btn{width:100%;justify-content:space-between}}
@media (prefers-reduced-motion:reduce){.hvt-pop,.hvt-pop-card{transition:none}}
</style>
<section class="hvt-bw" id="hvt-banner-bw" data-desde="2026-11-12" data-hasta="2026-12-03" hidden><div class="wrap"><div class="hvt-bw-card">
<div><p class="eyebrow">Black Week · November 13 – December 3</p><h2>35% off your <em>stay</em></h2><p class="hvt-bw-text">The discount is already applied to the non-refundable rate when you book on our website. No code needed.</p></div>
<div class="hvt-bw-side"><a class="btn btn-gold" href="#reservar" data-track="banner_blackweek">Book now <span class="arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8"/></svg></span></a></div>
</div></div></section>
<script>(function(){var s=document.getElementById("hvt-banner-bw");if(!s)return;var prev=/vista-previa/.test(location.search)||window.HVT_PREVIEW===true;var hoy;try{hoy=new Intl.DateTimeFormat('en-CA',{timeZone:'America/Bogota',year:'numeric',month:'2-digit',day:'2-digit'}).format(new Date());}catch(e){var d=new Date();hoy=d.getFullYear()+'-'+('0'+(d.getMonth()+1)).slice(-2)+'-'+('0'+d.getDate()).slice(-2);}if(prev||(hoy>=s.getAttribute("data-desde")&&hoy<=s.getAttribute("data-hasta")))s.hidden=false;})();</script>
<!-- Haven · Pop-up Black Week (EN). Sale solo del 13 de noviembre al 3 de diciembre, a los 5 segundos, una vez por visita; para revisarlo antes, abre la página con ?vista-previa al final de la URL -->
<div class="hvt-pop" id="hvt-popup-bw" role="dialog" aria-modal="true" aria-labelledby="hvt-pop-title" data-desde="2026-11-12" data-hasta="2026-12-03" hidden><div class="hvt-pop-card">
<button type="button" class="hvt-pop-close" aria-label="Close"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
<div class="hvt-pop-media"><img src="https://havenmedellin.com/wp-content/uploads/2026/06/presidential-suite-room-jacuzzi-lounge-area-1-768x512.webp" alt="Presidential Suite at Haven, with jacuzzi and lounge" loading="lazy" decoding="async"></div>
<div class="hvt-pop-body"><p class="eyebrow">Black Week · November 13 – December 3</p><h2 id="hvt-pop-title">35% off your <em>stay</em></h2><p class="hvt-bw-text">The discount is already applied to the non-refundable rate when you book on our website. No code needed.</p><div><a class="btn btn-gold" href="#reservar" data-track="popup_blackweek">Book now <span class="arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8"/></svg></span></a></div><button type="button" class="hvt-pop-later">Not now</button></div>
</div></div>
<script>(function(){var pop=document.getElementById("hvt-popup-bw");if(!pop)return;var prev=/vista-previa/.test(location.search)||window.HVT_PREVIEW===true;var hoy;try{hoy=new Intl.DateTimeFormat('en-CA',{timeZone:'America/Bogota',year:'numeric',month:'2-digit',day:'2-digit'}).format(new Date());}catch(e){var d=new Date();hoy=d.getFullYear()+'-'+('0'+(d.getMonth()+1)).slice(-2)+'-'+('0'+d.getDate()).slice(-2);}if(!prev&&(hoy<pop.getAttribute("data-desde")||hoy>pop.getAttribute("data-hasta")))return;var KEY="hvt-popup-bw-2026";try{if(!prev&&sessionStorage.getItem(KEY))return;}catch(e){}document.body.appendChild(pop);var last;function foc(){return pop.querySelectorAll("a[href],button");}function onKey(e){if(e.key==="Escape"){cerrar();return;}if(e.key==="Tab"){var f=foc(),a=f[0],z=f[f.length-1];if(e.shiftKey&&document.activeElement===a){e.preventDefault();z.focus();}else if(!e.shiftKey&&document.activeElement===z){e.preventDefault();a.focus();}}}function abrir(){last=document.activeElement;pop.hidden=false;requestAnimationFrame(function(){pop.classList.add("is-open");});pop.querySelector(".hvt-pop-close").focus();document.addEventListener("keydown",onKey);try{sessionStorage.setItem(KEY,"1");}catch(e){}}function cerrar(){pop.classList.remove("is-open");document.removeEventListener("keydown",onKey);setTimeout(function(){pop.hidden=true;},320);if(last&&last.focus)last.focus();}pop.addEventListener("click",function(e){if(e.target===pop)cerrar();});pop.querySelectorAll(".hvt-pop-close,.hvt-pop-later").forEach(function(x){x.addEventListener("click",cerrar);});window.addEventListener("click",function(e){if(e.target.closest&&e.target.closest('#hvt-popup-bw a[href="#reservar"]'))cerrar();},true);setTimeout(abrir,prev?600:5000);})();</script>
<script>(function(){var b=document.getElementById("hvt-banner-bw"),h=document.querySelector("main section.hero");if(b&&h&&h.parentNode)h.parentNode.insertBefore(b,h.nextSibling);else if(b)b.parentNode.removeChild(b);})();</script>
<?php endif;
wp_footer(); ?>
</body>
</html>
