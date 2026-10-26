/* ---------- Funciones que no dependen de librerías ---------- */
(function(){
  const BOOK='http://havenmedellin.backhotelite.com/es/bookcore/availability/havenmedellin/';
  const iso=d=>d.toISOString().slice(0,10);
  const fin=document.getElementById('f-in'),fout=document.getElementById('f-out');
  const t=new Date();t.setHours(12);const a=new Date(t);a.setDate(a.getDate()+1);const b=new Date(t);b.setDate(b.getDate()+3);
  window.dataLayer=window.dataLayer||[];
  if(fin&&fout){
  fin.min=iso(t);fin.value=iso(a);fout.min=iso(a);fout.value=iso(b);
  fin.addEventListener('change',()=>{const d=new Date(fin.value+'T12:00');d.setDate(d.getDate()+1);fout.min=iso(d);if(fout.value<=fin.value)fout.value=iso(d);});
  window.dataLayer=window.dataLayer||[];
  document.getElementById('reservar').addEventListener('submit',e=>{
    e.preventDefault();
    if(fout.value<=fin.value){fout.focus();return;}
    dataLayer.push({event:'haven_buscar_fechas',checkin:fin.value,checkout:fout.value});
    window.open(BOOK+fin.value+'/'+fout.value+'/','_blank','noopener');
  });
  }
  document.addEventListener('click',e=>{const el=e.target.closest('[data-track]');if(el)dataLayer.push({event:'haven_click',accion:el.dataset.track});});

  // Galerías de suites
  document.querySelectorAll('.suite').forEach(s=>{
    const main=s.querySelector('.main');
    s.querySelectorAll('.thumbs button').forEach(btn=>btn.addEventListener('click',()=>{
      s.querySelectorAll('.thumbs button').forEach(x=>x.setAttribute('aria-pressed','false'));
      btn.setAttribute('aria-pressed','true');main.classList.add('swap');
      const img=new Image();img.onload=()=>{setTimeout(()=>{main.src=btn.dataset.src;main.classList.remove('swap');},350)};img.src=btn.dataset.src;
    }));
  });

  // Lista de acomodación
  const items=[...document.querySelectorAll('.room-list li')],imgs=[...document.querySelectorAll('.room-stage img')],cap=document.querySelector('.cap-name');
  const setRoom=li=>{items.forEach(x=>x.classList.toggle('active',x===li));if(li.classList.contains('cur'))return;items.forEach(x=>x.classList.toggle('cur',x===li));imgs.forEach((im,i)=>im.classList.toggle('on',i==li.dataset.img));cap.textContent=li.querySelector('.name').textContent;const cn=document.querySelector('.room-stage .count b');if(cn)cn.textContent=String(+li.dataset.img+1).padStart(2,'0');};
  // Escritorio: un clic elige la categoría y muestra su foto; el segundo clic (o el botón Ver) entra
  items.forEach(li=>{const a=li.querySelector('a');a.addEventListener('click',e=>{if(!matchMedia('(min-width: 821px)').matches)return;if(li.dataset.picked!=='1'){e.preventDefault();items.forEach(x=>x.dataset.picked='');li.dataset.picked='1';setRoom(li);}});a.addEventListener('focus',()=>{if(a.matches(':focus-visible'))setRoom(li);});});
  const vb=document.querySelector('.room-stage .stage-go');
  if(vb){const upd=()=>{const li=items.find(x=>x.classList.contains('active'))||items[0];vb.href=li.querySelector('a').getAttribute('href');vb.querySelector('b').textContent='Ver '+li.querySelector('.name').textContent;};items.forEach(li=>li.querySelector('a').addEventListener('click',()=>setTimeout(upd,0)));upd();}
  window.__setRoom=setRoom;
  const stage=document.querySelector('.room-stage');
  if(stage&&matchMedia('(hover:hover)').matches){document.querySelector('.room-switch').addEventListener('pointermove',e=>{const r=stage.getBoundingClientRect();stage.style.setProperty('--px',((e.clientX-r.left)/r.width-.5)*-22+'px');stage.style.setProperty('--py',((e.clientY-r.top)/r.height-.5)*-22+'px');});}

  // Tarjetas de experiencias: inclinación 3D + brillo dorado
  if(matchMedia('(hover:hover)').matches&&!document.body.classList.contains('reduce')){
    document.querySelectorAll('.exp-card').forEach(c=>{
      c.addEventListener('pointermove',e=>{const r=c.getBoundingClientRect();const x=(e.clientX-r.left)/r.width,y=(e.clientY-r.top)/r.height;
        c.style.transform=`rotateY(${(x-.5)*14}deg) rotateX(${(.5-y)*12}deg) translateZ(18px)`;c.style.setProperty('--mx',x*100+'%');c.style.setProperty('--my',y*100+'%');
        const im=c.querySelector('img');im.style.transform=`scale(1.12) translate(${(x-.5)*-16}px,${(y-.5)*-16}px)`;});
      c.addEventListener('pointerleave',()=>{c.style.transform='';c.querySelector('img').style.transform='';});
    });
  }

  // Experiencias con video (Higgsfield): se cargan solo cuando la tarjeta entra en pantalla
  if(!document.body.classList.contains('reduce')&&!(navigator.connection&&navigator.connection.saveData)&&'IntersectionObserver' in window){
    const vio=new IntersectionObserver(en=>en.forEach(x=>{const c=x.target;let v=c.querySelector('video');
      if(x.isIntersecting){if(!v){v=document.createElement('video');v.muted=true;v.loop=true;v.playsInline=true;v.preload='auto';v.setAttribute('aria-hidden','true');v.src=c.dataset.video;v.addEventListener('playing',()=>v.classList.add('on'),{once:true});c.insertBefore(v,c.firstChild.nextSibling);}v.play().catch(()=>{});}
      else if(v)v.pause();}),{rootMargin:'200px 0px'});
    document.querySelectorAll('.exp-card[data-video]').forEach(c=>{if(c.dataset.video)vio.observe(c);});
  }

  // Logo del footer: oro líquido con olas que suben con el scroll y reaccionan al cursor
  (function(){const wm=document.querySelector('.wordmark');if(!wm)return;
    const fr=wm.querySelector('.wm-front'),bk=wm.querySelector('.wm-back'),gl=wm.querySelector('.wm-glint'),svg=wm.querySelector('svg');
    const red=document.body.classList.contains('reduce');let on=false,raf=0,lvl=.15,target=.15,amp=10,ampT=10,mx=-1,t0=performance.now();
    const wave=(base,a,k,sp,ph,t)=>{let d='M0 300 L0 '+base.toFixed(1);for(let x=0;x<=1200;x+=24){const near=mx<0?0:Math.exp(-Math.pow((x-mx)/160,2));const y=base+Math.sin(x*k+t*sp+ph)*a+Math.sin(x*k*2.3-t*sp*1.4)*a*.35-near*a*2.2*Math.sin(t*6+x*.02);d+=' L'+x+' '+y.toFixed(1);}return d+' L1200 300 Z';};
    const tick=now=>{raf=on?requestAnimationFrame(tick):0;const t=(now-t0)/1000;
      const r=wm.getBoundingClientRect(),vis=1-Math.min(1,Math.max(0,(r.top-innerHeight*.35)/(innerHeight*.65)));
      target=red?.85:.12+vis*.62+Math.sin(t*.5)*.08+(mx>=0?.12:0);lvl+=(target-lvl)*.04;amp+=(ampT-amp)*.06;
      const base=300-lvl*260;
      fr.setAttribute('d',wave(base,amp,.011,1.6,0,t));bk.setAttribute('d',wave(base-8,amp*1.2,.008,-1.1,2,t));
      gl.setAttribute('x',String(((t*260)%2200)-500));};
    const io=new IntersectionObserver(en=>{on=en[0].isIntersecting;if(on&&!raf)raf=requestAnimationFrame(tick);});io.observe(wm);
    svg.addEventListener('pointermove',e=>{const r=svg.getBoundingClientRect();mx=(e.clientX-r.left)/r.width*1200;ampT=18;});
    svg.addEventListener('pointerleave',()=>{mx=-1;ampT=10;});
    if(red){fr.setAttribute('d',wave(80,0,0,0,0,0));}
  })();

  // Mapa: se activa con un clic (antes queda en tonos del hotel y no atrapa el scroll)
  const mfr=document.querySelector('.map-frame');
  if(mfr){mfr.addEventListener('click',()=>{mfr.classList.add('live');(window.dataLayer=window.dataLayer||[]).push({event:'haven_click',accion:'mapa_activar'});},{once:true});}

  // Mapa: se carga solo cuando está cerca de la pantalla
  const mf=document.querySelector('.map-frame'),mi=mf&&mf.querySelector('iframe');
  if(mi){const load=()=>{if(mi.src)return;mi.addEventListener('load',()=>mf.classList.add('loaded'),{once:true});mi.src=mi.dataset.src;};
    if('IntersectionObserver' in window){const io=new IntersectionObserver(en=>{if(en.some(x=>x.isIntersecting)){load();io.disconnect();}},{rootMargin:'600px 0px'});io.observe(mf);}else load();}

  // Tarjetas Serena Rewards con inclinación 3D
  document.querySelectorAll('.tier').forEach(c=>{
    c.addEventListener('pointermove',e=>{const r=c.getBoundingClientRect();const x=(e.clientX-r.left)/r.width,y=(e.clientY-r.top)/r.height;
      c.style.transform=`rotateY(${(x-.5)*18}deg) rotateX(${(.5-y)*14}deg) translateZ(10px)`;c.style.setProperty('--mx',x*100+'%');c.style.setProperty('--my',y*100+'%');});
    c.addEventListener('pointerleave',()=>{c.style.transform='';});
  });

  // Header + menú móvil
  const hdr=document.querySelector('.site-header');
  addEventListener('scroll',()=>hdr.classList.toggle('scrolled',scrollY>60),{passive:true});
  const dr=document.getElementById('drawer'),mb=document.querySelector('.menu-btn');
  mb.addEventListener('click',()=>{dr.hidden=false;mb.setAttribute('aria-expanded','true');dr.querySelector('.close').focus();});
  dr.querySelector('.close').addEventListener('click',()=>{dr.hidden=true;mb.setAttribute('aria-expanded','false');mb.focus();});
  dr.addEventListener('click',e=>{if(e.target.tagName==='A')dr.hidden=true;});
})();

/* ---------- Video del hero: se carga después de la página ---------- */
addEventListener('load',()=>{const v=document.querySelector('.hero-video');if(v&&v.dataset.srcM&&!matchMedia('(min-width: 900px)').matches){v.dataset.src=v.dataset.srcM;}if(!v||(!matchMedia('(min-width: 900px)').matches&&!v.dataset.srcM)||(navigator.connection&&navigator.connection.saveData))return;
  setTimeout(()=>{v.src=v.dataset.src;v.addEventListener('playing',()=>v.classList.add('playing'),{once:true});v.play().catch(()=>{});},600);});

/* ---------- Animaciones (GSAP + Lenis), se cargan diferidas ---------- */
addEventListener('DOMContentLoaded',()=>{
  if(!window.gsap){document.body.classList.add('reduce');return;}
  if(document.body.classList.contains('reduce'))return;
  gsap.registerPlugin(ScrollTrigger);
  const lenis=window.Lenis?new Lenis({lerp:.1}):null;
  if(lenis){lenis.on('scroll',ScrollTrigger.update);gsap.ticker.add(t=>lenis.raf(t*1000));gsap.ticker.lagSmoothing(0);
    document.querySelectorAll('a[href^="#"]').forEach(a=>a.addEventListener('click',e=>{const id=a.getAttribute('href');if(id.length>1&&document.querySelector(id)){e.preventDefault();lenis.scrollTo(id,{offset:-140});}}));}

  // Barra de progreso
  gsap.to('.progress',{scaleX:1,ease:'none',scrollTrigger:{start:0,end:'max',scrub:true}});

  // Título letra a letra con giro 3D
  const h1=document.querySelector('h1.split')||document.createElement('h1');
  const walk=(node)=>{[...node.childNodes].forEach(n=>{
    if(n.nodeType===3){const frag=document.createDocumentFragment();n.textContent.split(/(\s+)/).forEach(w=>{
      if(!w)return; if(/^\s+$/.test(w)){frag.append(' ');return;}
      const ws=document.createElement('span');ws.className='w';[...w].forEach(c=>{const s=document.createElement('span');s.className='ch';s.textContent=c;ws.append(s);});frag.append(ws);});
      n.replaceWith(frag);} else if(n.nodeType===1) walk(n);});};
  h1.setAttribute('aria-label',h1.textContent.replace(/\s+/g,' '));walk(h1);
  h1.querySelectorAll('.w').forEach(w=>w.setAttribute('aria-hidden','true'));
  document.querySelectorAll('.split:not(h1)').forEach(t=>{t.setAttribute('aria-label',t.textContent.replace(/\s+/g,' '));walk(t);t.querySelectorAll('.w').forEach(w=>w.setAttribute('aria-hidden','true'));gsap.from(t.querySelectorAll('.ch'),{rotateX:-95,yPercent:50,opacity:0,duration:1.1,ease:'expo.out',stagger:.02,scrollTrigger:{trigger:t,start:'top 85%'}});});
  gsap.from(h1.querySelectorAll('.ch'),{rotateX:-95,yPercent:40,opacity:0,duration:1.1,ease:'expo.out',stagger:.022,delay:.1});
  gsap.from('.hero-sub, .hero .facts, .hero .eyebrow',{y:24,opacity:0,duration:1,ease:'power3.out',stagger:.1,delay:.5});
  gsap.from('.booking',{y:40,opacity:0,duration:1.1,ease:'power3.out',delay:.8});

  // ¿Por qué Haven?: título letra a letra, chips que flotan, cinta que responde al scroll, íconos que se dibujan
  const wt=document.querySelector('.why-title');
  if(wt){wt.setAttribute('aria-label',wt.textContent.replace(/\s+/g,' '));const em=wt.querySelector('.shine'),emT=em.textContent;walk(wt);em.textContent=emT;em.setAttribute('aria-hidden','true');em.style.display='inline-block';wt.querySelectorAll('.w').forEach(w=>w.setAttribute('aria-hidden','true'));
    const tl=gsap.timeline({scrollTrigger:{trigger:wt,start:'top 82%'}});tl.from(wt.querySelectorAll('.ch'),{rotateX:-100,yPercent:60,opacity:0,ease:'expo.out',duration:1.2,stagger:.03}).from(em,{rotateX:-80,y:50,opacity:0,filter:'blur(10px)',duration:1.4,ease:'expo.out',transformOrigin:'50% 100%'},'-=.8');
    gsap.from('.gets li',{scale:.6,opacity:0,y:30,duration:.9,ease:'back.out(1.8)',stagger:.08,scrollTrigger:{trigger:'.gets',start:'top 88%'}});
    const tr=document.querySelector('.mq-track');let x=0,dir=-1,boost=0;
    ScrollTrigger.create({trigger:'.why',start:'top bottom',end:'bottom top',onUpdate:st=>{dir=st.direction>0?-1:1;boost=Math.min(Math.abs(st.getVelocity())/120,18);}});
    gsap.ticker.add(()=>{const half=tr.scrollWidth/2;x+=dir*(0.6+boost);boost*=.92;if(x<=-half)x+=half;if(x>0)x-=half;tr.style.transform=`translate3d(${x}px,0,0) skewX(${dir*-boost*.6}deg)`;});
    document.querySelectorAll('.gets li').forEach(li=>{li.addEventListener('pointermove',e=>{const r=li.getBoundingClientRect();li.style.transform=`translate(${(e.clientX-r.left-r.width/2)*.25}px,${(e.clientY-r.top-r.height/2)*.35}px)`;});li.addEventListener('pointerleave',()=>{li.style.transform='';});});
  }

  // Marco del hero que se abre a pantalla completa
  if(matchMedia('(min-width: 821px)').matches){
    gsap.fromTo('.hero-media',{clipPath:'inset(0% 6% 0% 6% round 28px)'},{clipPath:'inset(0% 0% 0% 0% round 0px)',ease:'none',scrollTrigger:{trigger:'.hero-media',start:'top 70%',end:'top 5%',scrub:true}});
  }
  gsap.to('.hero-media img, .hero-media video',{yPercent:12,ease:'none',scrollTrigger:{trigger:'.hero-media',start:'top bottom',end:'bottom top',scrub:true}});

  // Manifiesto palabra por palabra
  document.querySelectorAll('.reveal-text').forEach(el=>{
    const txt=el.textContent.trim();el.setAttribute('aria-label',txt);
    el.innerHTML=txt.split(' ').map(w=>`<span class="wd" aria-hidden="true">${w}</span>`).join(' ');
    gsap.to(el.querySelectorAll('.wd'),{color:'#191311',stagger:.1,ease:'none',scrollTrigger:{trigger:el,start:'top 80%',end:'bottom 45%',scrub:true}});
  });
  document.querySelectorAll('.floaters img').forEach(im=>{
    gsap.fromTo(im,{y:()=>parseFloat(im.dataset.speed)*-260,rotate:()=>parseFloat(im.dataset.speed)*-12},{y:()=>parseFloat(im.dataset.speed)*260,rotate:()=>parseFloat(im.dataset.speed)*12,ease:'none',scrollTrigger:{trigger:'.manifesto',start:'top bottom',end:'bottom top',scrub:true}});
  });

  // Lista de acomodación activada por scroll
  // (la lista ya no cambia sola con el scroll: se elige con clic)

  // Suites apiladas: la anterior se aleja
  if(matchMedia('(min-width: 821px)').matches){
    const suites=gsap.utils.toArray('.suite');
    suites.forEach((s,i)=>{if(i===suites.length-1)return;
      gsap.to(s,{scale:.92,filter:'brightness(.55)',ease:'none',scrollTrigger:{trigger:suites[i+1],start:'top 85%',end:'top 15%',scrub:true}});});
  }

  // Túnel 3D: fotos que vienen desde la profundidad
  const flies=matchMedia('(min-width: 821px)').matches?gsap.utils.toArray('.fly'):[];
  const W=()=>innerWidth,H=()=>innerHeight;
  const seeds=flies.map((f,i)=>{const ang=i*2.399963;const rad=.32+(i%3)*.09;return{x:Math.cos(ang)*rad,y:Math.sin(ang)*rad*.9,z:-i*520};});
  const depth=flies.length*520;
  const place=p=>{flies.forEach((f,i)=>{const s=seeds[i];let z=s.z+p*(depth+900);
      const o=z>520?Math.max(0,1-(z-520)/280):Math.min(1,(z+depth)/(depth*.55));
      f.style.transform=`translate3d(${s.x*W()}px,${s.y*H()}px,${z}px)`;f.style.opacity=Math.max(0,Math.min(1,o));f.style.visibility=z>820?'hidden':'visible';});};
  place(0);
  ScrollTrigger.create({trigger:'.tunnel',start:'top top',end:'bottom bottom',scrub:true,onUpdate:st=>place(st.progress)});
  gsap.fromTo('.tunnel-title',{scale:.85,opacity:.3},{scale:1,opacity:1,ease:'none',scrollTrigger:{trigger:'.tunnel',start:'top 60%',end:'top top',scrub:true}});

  // Tarjetas de experiencias
  gsap.from('.exp-card',{y:140,rotateX:38,rotateY:i=>(i-1.5)*-10,opacity:0,transformOrigin:'50% 100%',duration:1.4,ease:'expo.out',stagger:.12,clearProps:'transform',scrollTrigger:{trigger:'.exp-grid',start:'top 85%'}});
  gsap.utils.toArray('.exp-card').forEach(c=>gsap.fromTo(c,{clipPath:'inset(14% 8% 0% 8% round 22px)'},{clipPath:'inset(0% 0% 0% 0% round 22px)',ease:'none',scrollTrigger:{trigger:c,start:'top 100%',end:'top 55%',scrub:true}}));

  // Fotos de suites y acomodación: se revelan con cortina y zoom al hacer scroll
  gsap.utils.toArray('.suite-media').forEach(m=>{
    gsap.fromTo(m,{clipPath:'inset(0% 0% 100% 0%)'},{clipPath:'inset(0% 0% 0% 0%)',duration:1.3,ease:'expo.inOut',scrollTrigger:{trigger:m,start:'top 85%'}});
    gsap.fromTo(m.querySelector('.main'),{scale:1.35},{scale:1,duration:1.8,ease:'expo.out',scrollTrigger:{trigger:m,start:'top 85%'}});
  });
  gsap.from('.room-stage',{clipPath:'inset(30% 20% 30% 20% round 40px)',duration:1.6,ease:'expo.out',scrollTrigger:{trigger:'.room-switch',start:'top 75%'}});
  gsap.from('.room-list li',{x:-60,opacity:0,duration:1,ease:'power3.out',stagger:.09,scrollTrigger:{trigger:'.room-list',start:'top 80%'}});
  gsap.from('.map-frame',{clipPath:'inset(20% 20% 20% 20% round 60px)',scale:.9,duration:1.6,ease:'expo.out',scrollTrigger:{trigger:'.location',start:'top 75%'}});
  gsap.from('.loc-copy > *',{y:30,opacity:0,stagger:.08,duration:.9,ease:'power3.out',scrollTrigger:{trigger:'.location',start:'top 75%'}});

  // Contadores
  document.querySelectorAll('[data-count]').forEach(el=>{
    const end=parseFloat(el.dataset.count),dec=+el.dataset.dec||0,sup=el.querySelector('sup');const o={v:0};
    ScrollTrigger.create({trigger:el,start:'top 90%',once:true,onEnter:()=>gsap.to(o,{v:end,duration:1.8,ease:'power2.out',onUpdate:()=>{el.firstChild.nodeValue=o.v.toLocaleString('es-CO',{minimumFractionDigits:dec,maximumFractionDigits:dec});}})});
    if(!el.firstChild||el.firstChild.nodeType!==3)el.prepend(document.createTextNode(''));
  });

  // Tarjetas de membresía entrando
  gsap.from('.tier-wrap',{y:90,rotateX:25,opacity:0,duration:1.2,ease:'power3.out',stagger:.12,scrollTrigger:{trigger:'.tiers',start:'top 85%'}});
  gsap.fromTo('.wordmark',{yPercent:30,opacity:.2},{yPercent:0,opacity:1,ease:'none',scrollTrigger:{trigger:'.wordmark',start:'top bottom',end:'bottom bottom',scrub:true}});
});

/* Acomodación en celular: copia la foto de cada categoría dentro de su tarjeta */
(function(){if(matchMedia('(min-width: 821px)').matches)return;const imgs=[...document.querySelectorAll('.room-stage img')];
  document.querySelectorAll('.room-list li').forEach(li=>{const im=imgs[+li.dataset.img];if(!im)return;const w=document.createElement('span');w.className='ri-img';const c=im.cloneNode();c.className='';c.alt='';c.loading='lazy';w.append(c);li.querySelector('a').prepend(w);});})();

/* Túnel en celular: las fotos pasan a una fila deslizable */
(function(){const st=document.querySelector('.tunnel-stage');if(!st||matchMedia('(min-width: 821px)').matches)return;
  const row=document.createElement('div');row.className='tunnel-row';st.querySelectorAll('.fly').forEach(f=>row.append(f));st.append(row);
  const h=document.createElement('p');h.className='tunnel-hint';h.textContent='Desliza para ver más →';st.append(h);})();

/* ===================== Páginas internas ===================== */
(function(){
  const BOOK='http://havenmedellin.backhotelite.com/es/bookcore/availability/havenmedellin/';
  const iso=d=>d.toISOString().slice(0,10);
  // "Reservar" fuera de la portada abre el motor con fechas sugeridas
  if(!document.getElementById('reservar')){
    document.addEventListener('click',e=>{const a=e.target.closest('a[href="#reservar"]');if(!a)return;e.preventDefault();
      const t=new Date();t.setHours(12);t.setDate(t.getDate()+1);const o=new Date(t);o.setDate(o.getDate()+2);
      (window.dataLayer=window.dataLayer||[]).push({event:'haven_click',accion:'abrir_motor_'+(document.body.className.match(/page-(\S+)/)||[,''])[1]});
      window.open(BOOK+iso(t)+'/'+iso(o)+'/','_blank','noopener');});
  }
  // Barra flotante de reserva
  const bb=document.querySelector('.bookbar');
  if(bb){const ph=document.querySelector('.ph');addEventListener('scroll',()=>{bb.classList.toggle('on',scrollY>(ph?ph.offsetHeight*.8:500)&&(innerHeight+scrollY)<document.body.scrollHeight-500);},{passive:true});}
  // Filtros de tarjetas
  document.querySelectorAll('.filters').forEach(f=>{const grid=document.querySelector(f.dataset.target);
    f.addEventListener('click',e=>{const b=e.target.closest('button');if(!b)return;f.querySelectorAll('button').forEach(x=>x.setAttribute('aria-pressed',x===b));
      const v=b.dataset.f;let k=0;grid.querySelectorAll('.card').forEach(c=>{const h=v!=='all'&&!(c.dataset.cat||'').split(' ').includes(v);c.classList.toggle('hide',h);c.classList.remove('show');if(!h){c.style.transform='';c.style.opacity='';c.style.animationDelay=(k++*60)+'ms';void c.offsetWidth;c.classList.add('show');}});
      (window.dataLayer=window.dataLayer||[]).push({event:'haven_click',accion:'filtro_'+v});const top=f.getBoundingClientRect().top+scrollY-90;if(scrollY>top)scrollTo({top,behavior:'smooth'});
      if(window.ScrollTrigger)ScrollTrigger.refresh();});});
  // Tarjetas con inclinación + brillo
  if(matchMedia('(hover:hover)').matches&&!document.body.classList.contains('reduce')){
    document.querySelectorAll('.card,[data-tilt]').forEach(c=>{c.addEventListener('pointermove',e=>{const r=c.getBoundingClientRect(),x=(e.clientX-r.left)/r.width,y=(e.clientY-r.top)/r.height;
      c.style.transform=`rotateY(${(x-.5)*8}deg) rotateX(${(.5-y)*7}deg) translateY(-4px)`;c.style.setProperty('--mx',x*100+'%');c.style.setProperty('--my',y*100+'%');});
      c.addEventListener('pointerleave',()=>{c.style.transform='';});});
    // Botones magnéticos
    document.querySelectorAll('.btn').forEach(b=>{b.addEventListener('pointermove',e=>{const r=b.getBoundingClientRect();b.style.transform=`translate(${(e.clientX-r.left-r.width/2)*.18}px,${(e.clientY-r.top-r.height/2)*.3}px)`;});b.addEventListener('pointerleave',()=>{b.style.transform='';});});
  }
  // Lightbox
  const ms=[...document.querySelectorAll('.mosaic a,[data-lb]')];
  if(ms.length){const lb=document.createElement('div');lb.className='lb';lb.setAttribute('role','dialog');lb.setAttribute('aria-modal','true');lb.setAttribute('aria-label','Galería');
    lb.innerHTML='<img alt=""><button class="x" aria-label="Cerrar">✕</button><button class="pv" aria-label="Anterior">←</button><button class="nx" aria-label="Siguiente">→</button>';document.body.append(lb);
    let i=0;const im=lb.querySelector('img');const show=n=>{i=(n+ms.length)%ms.length;im.src=ms[i].getAttribute('href');im.alt=(ms[i].querySelector('img')||{}).alt||'';lb.classList.add('on');};
    ms.forEach((a,n)=>a.addEventListener('click',e=>{e.preventDefault();show(n);}));
    lb.querySelector('.x').onclick=()=>lb.classList.remove('on');lb.querySelector('.pv').onclick=()=>show(i-1);lb.querySelector('.nx').onclick=()=>show(i+1);
    lb.addEventListener('click',e=>{if(e.target===lb)lb.classList.remove('on');});
    addEventListener('keydown',e=>{if(!lb.classList.contains('on'))return;if(e.key==='Escape')lb.classList.remove('on');if(e.key==='ArrowRight')show(i+1);if(e.key==='ArrowLeft')show(i-1);});}
})();

addEventListener('DOMContentLoaded',()=>{
  if(!window.gsap||document.body.classList.contains('reduce'))return;
  // Hero de página: la foto se recoge en un marco redondeado y hace zoom suave
  const ph=document.querySelector('.ph-media');
  if(ph){gsap.fromTo(ph.firstElementChild,{scale:1.18},{scale:1,duration:2.2,ease:'expo.out'});
    gsap.to(ph,{clipPath:'inset(6% 4% 0% 4% round 32px)',ease:'none',scrollTrigger:{trigger:'.ph',start:'top top',end:'bottom top',scrub:true}});
    gsap.to(ph.firstElementChild,{yPercent:-10,ease:'none',scrollTrigger:{trigger:'.ph',start:'top top',end:'bottom top',scrub:true}});
    gsap.from('.ph .crumbs,.ph .eyebrow,.ph-row>*',{y:30,opacity:0,duration:1,ease:'power3.out',stagger:.1,delay:.5});}
  // Aparición escalonada genérica
  document.querySelectorAll('[data-reveal]').forEach(g=>gsap.from(g.children,{y:50,opacity:0,duration:1.05,ease:'power3.out',stagger:.09,scrollTrigger:{trigger:g,start:'top 86%'}}));
  // Fotos con cortina + zoom
  document.querySelectorAll('.img-reveal').forEach(m=>{const im=m.querySelector('img,video');
    gsap.fromTo(m,{clipPath:'inset(100% 0% 0% 0%)'},{clipPath:'inset(0% 0% 0% 0%)',duration:1.4,ease:'expo.inOut',scrollTrigger:{trigger:m,start:'top 88%'}});
    if(im)gsap.fromTo(im,{scale:1.35},{scale:1,duration:2,ease:'expo.out',scrollTrigger:{trigger:m,start:'top 88%'}});});
  // Parallax
  document.querySelectorAll('[data-speed]').forEach(el=>{if(el.closest('.floaters'))return;const s=parseFloat(el.dataset.speed)||.15;
    gsap.fromTo(el,{yPercent:-s*100},{yPercent:s*100,ease:'none',scrollTrigger:{trigger:el.parentElement,start:'top bottom',end:'bottom top',scrub:true}});});
  // Galería horizontal fijada
  if(matchMedia('(min-width: 821px)').matches)document.querySelectorAll('.hg').forEach(h=>{const tr=h.querySelector('.hg-track'),pin=h.querySelector('.hg-pin'),cnt=h.querySelector('.hg-count b'),items=[...tr.children];
    const dist=()=>tr.scrollWidth-innerWidth+80;
    gsap.to(tr,{x:()=>-dist(),ease:'none',scrollTrigger:{trigger:h,start:'top top',end:()=>'+='+dist(),pin:pin,scrub:1,invalidateOnRefresh:true,anticipatePin:1,
      onUpdate:st=>{items.forEach((it,k)=>{const r=it.getBoundingClientRect();it.querySelector('img').style.setProperty('--px',((r.left+r.width/2)/innerWidth-.5)*-60+'px');});
        if(cnt)cnt.textContent=String(Math.min(items.length,1+Math.round(st.progress*(items.length-1)))).padStart(2,'0');}}});});
  // Tarjetas entrando en 3D
  document.querySelectorAll('.cards').forEach(g=>gsap.from(g.querySelectorAll('.card'),{y:110,rotateX:24,opacity:0,transformOrigin:'50% 100%',duration:1.3,ease:'expo.out',stagger:.08,clearProps:'transform',scrollTrigger:{trigger:g,start:'top 85%'}}));
  // Arco: se abre desde abajo
  document.querySelectorAll('.arch').forEach(a=>gsap.fromTo(a,{clipPath:'inset(40% 10% 0% 10% round 999px 999px 28px 28px)'},{clipPath:'inset(0% 0% 0% 0% round 999px 999px 28px 28px)',ease:'none',scrollTrigger:{trigger:a,start:'top 95%',end:'top 35%',scrub:true}}));
  document.querySelectorAll('.arch-float').forEach(a=>gsap.from(a,{scale:.4,rotate:-25,opacity:0,duration:1.4,ease:'back.out(1.6)',scrollTrigger:{trigger:a,start:'top 90%'}}));
  // Frase grande palabra a palabra (dorado)
  document.querySelectorAll('.bigq p:not(.eyebrow)').forEach(el=>{const txt=el.textContent.trim();el.setAttribute('aria-label',txt);el.innerHTML=txt.split(' ').map(w=>`<span class="wd" aria-hidden="true" style="opacity:.15;display:inline-block">${w}</span>`).join(' ');
    gsap.to(el.querySelectorAll('.wd'),{opacity:1,stagger:.08,ease:'none',scrollTrigger:{trigger:el,start:'top 80%',end:'bottom 50%',scrub:true}});});
  // Specs con contador
  document.querySelectorAll('.rd-specs b[data-n]').forEach(b=>{const end=+b.dataset.n,o={v:0};ScrollTrigger.create({trigger:b,start:'top 92%',once:true,onEnter:()=>gsap.to(o,{v:end,duration:1.6,ease:'power2.out',onUpdate:()=>b.textContent=Math.round(o.v)+(b.dataset.u||'')})});});
});
