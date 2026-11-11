/* Calendario de disponibilidad en vivo (Roiback) — inc/disponibilidad.php da los datos.
   Se usa en la ficha de cada habitación (.hv-cal) y en la ventana que abre "Disponibilidad" en las tarjetas. */
(function(){
  const EN=(document.documentElement.lang||'').slice(0,2)==='en';
  const L=EN?{t:'Live availability',s:'Pick your check-in and check-out dates',upd:'Updated',ago:'min ago',now:'just now',av:'Available',no:'Not available',
      ci:'Check-in',co:'Check-out',book:'Book these dates',n:['night','nights'],err:'We could not load availability right now. You can still check dates in the booking engine.',
      eng:'Open booking engine',clear:'Clear',pick:'Now choose your check-out date',blocked:'Some nights in that range are not available',m:['January','February','March','April','May','June','July','August','September','October','November','December'],w:['Mo','Tu','We','Th','Fr','Sa','Su'],x:'Close'}
    :{t:'Disponibilidad en vivo',s:'Elija su fecha de llegada y de salida',upd:'Actualizado hace',ago:'min',now:'hace un momento',av:'Disponible',no:'No disponible',
      ci:'Llegada',co:'Salida',book:'Reservar estas fechas',n:['noche','noches'],err:'No pudimos cargar la disponibilidad en este momento. Puede consultar las fechas en el motor de reservas.',
      eng:'Abrir motor de reservas',clear:'Borrar',pick:'Ahora elija su fecha de salida',blocked:'Algunas noches de ese rango no están disponibles',m:['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'],w:['Lu','Ma','Mi','Ju','Vi','Sá','Do'],x:'Cerrar'};
  const BOOK='https://havenmedellin.backhotelite.com/'+(EN?'en':'es')+'/bookcore/availability/havenmedellin/';
  const pad=n=>(n<10?'0':'')+n, ymd=d=>d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate());
  const parse=s=>{const[a,b,c]=s.split('-').map(Number);return new Date(a,b-1,c,12);};
  const add=(s,n)=>{const d=parse(s);d.setDate(d.getDate()+n);return ymd(d);};
  const fmt=s=>{const d=parse(s);return d.getDate()+' '+L.m[d.getMonth()].slice(0,3);};
  const cache={};
  const get=u=>fetch(u,{credentials:'omit'}).then(r=>{if(!r.ok)throw 0;return r.json();});
  const rest=(api,room)=>get(api+(api.includes('?')?'&':'?')+'room='+encodeURIComponent(room));
  // Primero la copia estática (uploads/hv-dispo, se renueva cada 5 min); si falla, la ruta REST de WordPress.
  const load=(api,room,json)=>cache[room]||(cache[room]=json?get(json+encodeURIComponent(room)+'.json?v='+Math.floor(Date.now()/3e5)).then(j=>{if(!j||!j.days)throw 0;return j;}).catch(()=>rest(api,room)):rest(api,room));
  const track=a=>{(window.dataLayer=window.dataLayer||[]).push({event:'haven_click',accion:a});};

  function mount(el){
    if(el.dataset.ready)return;el.dataset.ready='1';
    const room=el.dataset.room;
    load(el.dataset.api,room,el.dataset.json).then(j=>render(el,j)).catch(()=>{el.innerHTML='<p class="hv-cal-err">'+L.err+'</p><a class="btn btn-ghost" href="'+BOOK+'" target="_blank" rel="noopener">'+L.eng+'</a>';});
  }

  function render(el,j){
    const days=j.days||{}, keys=Object.keys(days).sort();
    if(!keys.length)throw 0;
    const first=parse(j.today||keys[0]), last=keys[keys.length-1];
    let view=new Date(first.getFullYear(),first.getMonth(),1,12), ci=null, co=null, msg='';
    const months=()=>matchMedia('(max-width:700px)').matches?1:2;
    const avail=s=>(days[s]||0)>0;
    const rangeOk=(a,b)=>{for(let d=a;d<b;d=add(d,1))if(!avail(d))return false;return true;};
    const mins=Math.max(0,Math.round((Date.now()/1000-(j.upd||0))/60));
    el.innerHTML='<div class="hv-cal-head"><div><p class="eyebrow">'+L.t+'</p><p class="hv-cal-sub">'+L.s+'</p></div>'
      +'<div class="hv-cal-nav"><button type="button" class="hv-cal-prev" aria-label="‹">‹</button><button type="button" class="hv-cal-next" aria-label="›">›</button></div></div>'
      +'<div class="hv-cal-months"></div>'
      +'<div class="hv-cal-foot"><div class="hv-cal-legend"><span><i class="ok"></i>'+L.av+'</span><span><i class="no"></i>'+L.no+'</span><span class="hv-cal-upd">'+(j.upd?(mins<2?L.upd.replace(/ hace$/,'')+' '+L.now:L.upd+' '+mins+' '+L.ago):'')+'</span></div>'
      +'<div class="hv-cal-sel" aria-live="polite"></div></div>';
    const box=el.querySelector('.hv-cal-months'), sel=el.querySelector('.hv-cal-sel');
    const draw=()=>{
      let h='';const n=months();
      for(let k=0;k<n;k++){
        const m=new Date(view.getFullYear(),view.getMonth()+k,1,12);
        h+='<div class="hv-cal-m"><p class="hv-cal-title">'+L.m[m.getMonth()]+' '+m.getFullYear()+'</p><div class="hv-cal-grid">';
        L.w.forEach(w=>h+='<span class="hv-cal-w">'+w+'</span>');
        const off=(m.getDay()+6)%7;for(let i=0;i<off;i++)h+='<span></span>';
        const dim=new Date(m.getFullYear(),m.getMonth()+1,0).getDate();
        for(let d=1;d<=dim;d++){
          const s=ymd(new Date(m.getFullYear(),m.getMonth(),d,12));
          const known=s in days, ok=avail(s), past=s<keys[0];
          // La salida puede caer en una noche ocupada (ese día se va): se permite si las noches previas están libres.
          const canOut=ci&&!co&&s>ci&&rangeOk(ci,s);
          let c='hv-cal-d';
          if(past||(!known&&!canOut))c+=' off';else if(!ok&&!canOut)c+=' no';else c+=' ok';
          if(s===ci)c+=' ci';if(s===co)c+=' co';if(ci&&co&&s>ci&&s<co)c+=' in';
          const dis=c.includes(' off')||(c.includes(' no'));
          h+='<button type="button" class="'+c+'" data-d="'+s+'"'+(dis?' disabled':'')+' aria-label="'+d+' '+L.m[m.getMonth()]+(ok?' · '+L.av:' · '+L.no)+'">'+d+'</button>';
        }
        h+='</div></div>';
      }
      box.innerHTML=h;
      const lastM=parse(last);
      el.querySelector('.hv-cal-prev').disabled=view.getFullYear()===first.getFullYear()&&view.getMonth()<=first.getMonth();
      el.querySelector('.hv-cal-next').disabled=new Date(view.getFullYear(),view.getMonth()+n,1,12)>lastM;
      if(ci&&co){const nn=Math.round((parse(co)-parse(ci))/864e5);
        sel.innerHTML='<span><b>'+fmt(ci)+' → '+fmt(co)+'</b> · '+nn+' '+L.n[nn===1?0:1]+'</span><button type="button" class="hv-cal-clear">'+L.clear+'</button><a class="btn btn-gold hv-cal-book" href="'+BOOK+ci+'/'+co+'/'+(j.name?'?hv_room='+encodeURIComponent(j.name):'')+'" data-track="calendario_'+el.dataset.room+'">'+L.book+' <span class="arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></a>';}
      else sel.innerHTML='<span>'+(msg||(ci?L.pick:''))+'</span>'+(ci?'<button type="button" class="hv-cal-clear">'+L.clear+'</button>':'');
    };
    el.addEventListener('click',e=>{
      const b=e.target.closest('button,a');if(!b||!el.contains(b))return;
      if(b.classList.contains('hv-cal-prev')){view=new Date(view.getFullYear(),view.getMonth()-1,1,12);draw();return;}
      if(b.classList.contains('hv-cal-next')){view=new Date(view.getFullYear(),view.getMonth()+1,1,12);draw();return;}
      if(b.classList.contains('hv-cal-clear')){ci=co=null;msg='';draw();return;}
      if(b.classList.contains('hv-cal-book')){track('calendario_reservar_'+el.dataset.room);
        if(typeof window.hvBook==='function'){e.preventDefault();window.hvBook(b.href,ci,co,'calendario_'+el.dataset.room);}return;}
      const s=b.dataset.d;if(!s)return;msg='';
      if(!ci||co||s<=ci){ if(avail(s)){ci=s;co=null;track('calendario_llegada');} }
      else if(rangeOk(ci,s)){co=s;}
      else{msg=L.blocked;}
      draw();
    });
    addEventListener('resize',()=>draw(),{passive:true});
    draw();
  }

  // Ficha: se carga cuando el calendario se acerca a la pantalla.
  const io='IntersectionObserver' in window?new IntersectionObserver(es=>es.forEach(x=>{if(x.isIntersecting){io.unobserve(x.target);mount(x.target);}}),{rootMargin:'300px'}):null;
  const scan=()=>document.querySelectorAll('.hv-cal:not([data-ready])').forEach(el=>{if(el.closest('dialog'))return;io?io.observe(el):mount(el);});
  scan();

  // Tarjetas: botón "Disponibilidad" abre una ventana con el calendario de esa habitación.
  let dlg=null;
  document.addEventListener('click',e=>{
    const b=e.target.closest('.card-cal');if(!b)return;e.preventDefault();
    if(!dlg){dlg=document.createElement('dialog');dlg.className='hv-cal-dlg';document.body.appendChild(dlg);
      dlg.addEventListener('click',ev=>{if(ev.target===dlg||ev.target.closest('.hv-cal-x'))dlg.close();});
      dlg.addEventListener('close',()=>{document.documentElement.classList.remove('hvb-open');try{window.__hvLenis&&window.__hvLenis.start()}catch(_){}});}
    dlg.innerHTML='<button type="button" class="hv-cal-x" aria-label="'+L.x+'">✕</button><h2 class="hv-cal-name">'+(b.dataset.name||'')+'</h2><div class="hv-cal" data-room="'+b.dataset.room+'" data-api="'+b.dataset.api+'" data-json="'+(b.dataset.json||'')+'"></div>';
    track('calendario_abrir_'+b.dataset.room);
    try{window.__hvLenis&&window.__hvLenis.stop()}catch(_){}
    document.documentElement.classList.add('hvb-open');
    dlg.showModal?dlg.showModal():dlg.setAttribute('open','');
    mount(dlg.querySelector('.hv-cal'));
  });
})();
