/* ---------- Reloj 3D: hora real de Medellín ---------- */
const stage=document.querySelector('.clock-stage'),cv=document.getElementById('clock3d'),timeEl=document.getElementById('mde-time');
const mde=()=>{const d=new Date(Date.now()-5*3600e3);return{h:d.getUTCHours(),m:d.getUTCMinutes(),s:d.getUTCSeconds(),ms:d.getUTCMilliseconds()}};
const fmt=()=>{const t=mde();return String(t.h).padStart(2,'0')+':'+String(t.m).padStart(2,'0')};
const syncFallback=()=>{const t=mde();stage.style.setProperty('--hr',((t.h%12)+t.m/60)*30+'deg');stage.style.setProperty('--mn',t.m*6+'deg');timeEl.textContent=fmt();};
syncFallback();setInterval(syncFallback,15000);
const reduce=false; // animaciones siempre activas (decisión de marca)

async function start(){
  // Sin tarjeta gráfica (3D dibujado por software) el reloj 3D congela la página: se queda el reloj plano y ni se descarga three.js.
  if(window.__hvSoftGL===true){window.__introEnd&&window.__introEnd();return;}
  if(window.__hvSoftGL!==false&&!/[?&]hv3d=1/.test(location.search)){let soft=true;try{const g=document.createElement('canvas').getContext('webgl',{failIfMajorPerformanceCaveat:true});if(g){const x=g.getExtension('WEBGL_debug_renderer_info'),r=String(x?g.getParameter(x.UNMASKED_RENDERER_WEBGL):g.getParameter(g.RENDERER));soft=/swiftshader|llvmpipe|softpipe|software|basic render/i.test(r);const l=g.getExtension('WEBGL_lose_context');l&&l.loseContext();}}catch(e){}
    if(soft){window.__introEnd&&window.__introEnd();return;}}
  let THREE,RoomEnvironment;
  try{THREE=await import('three');({RoomEnvironment}=await import('three/addons/environments/RoomEnvironment.js'));}catch(e){console.warn('3D no disponible',e);window.__introEnd&&window.__introEnd();return;}
  let renderer;
  try{renderer=new THREE.WebGLRenderer({canvas:cv,antialias:innerWidth>820,alpha:true,powerPreference:'high-performance',});}catch(e){window.__introEnd&&window.__introEnd();return;}
  const small=innerWidth<821,SM=small;
  renderer.setPixelRatio(Math.min(devicePixelRatio||1,small?1.25:1.75));
  renderer.outputColorSpace=THREE.SRGBColorSpace;renderer.toneMapping=THREE.ACESFilmicToneMapping;renderer.toneMappingExposure=1.1;
  const scene=new THREE.Scene();
  const pm=new THREE.PMREMGenerator(renderer);scene.environment=pm.fromScene(new RoomEnvironment(),0.04).texture;
  const cam=new THREE.PerspectiveCamera(30,1,0.1,100);cam.position.set(0,0,10);
  const gold=new THREE.MeshStandardMaterial({color:0xCDB371,metalness:1,roughness:.2});
  const champ=new THREE.MeshStandardMaterial({color:0xF7DFAE,metalness:1,roughness:.28});
  const holder=new THREE.Group();scene.add(holder);const root=new THREE.Group();holder.add(root);
  const clock=new THREE.Group();root.add(clock);
  const seg=small?64:160;
  clock.add(new THREE.Mesh(new THREE.TorusGeometry(1.6,0.12,48,seg),gold));
  const inner=new THREE.Mesh(new THREE.TorusGeometry(1.44,0.022,16,seg),champ);inner.position.z=0.04;clock.add(inner);
  const cs=new THREE.Mesh(new THREE.CylinderGeometry(1.62,1.5,0.34,seg),new THREE.MeshStandardMaterial({color:0x2E241E,metalness:.7,roughness:.32}));cs.rotation.x=Math.PI/2;cs.position.z=-0.18;clock.add(cs);

  await document.fonts.ready.catch(()=>{});
  // Esfera con textura propia
  const dc=document.createElement('canvas');dc.width=dc.height=1024;const x=dc.getContext('2d');
  const g=x.createRadialGradient(512,430,40,512,512,520);g.addColorStop(0,'#46382d');g.addColorStop(1,'#191311');x.fillStyle=g;x.fillRect(0,0,1024,1024);
  x.strokeStyle='rgba(205,179,113,.07)';for(let r=40;r<500;r+=9){x.lineWidth=r%45===4?2:1;x.beginPath();x.arc(512,512,r,0,Math.PI*2);x.stroke();}
  x.textAlign='center';x.fillStyle='#CDB371';
  x.font='500 112px "Playfair Display", Georgia, serif';x.fillText('HAVEN',512,370);
  x.font='500 30px "DM Sans", Arial, sans-serif';try{x.letterSpacing='12px'}catch(e){};x.fillStyle='#F7DFAE';x.fillText('A DESIGN HOTEL',518,420);
  x.font='600 34px "DM Sans", Arial, sans-serif';try{x.letterSpacing='16px'}catch(e){};x.fillStyle='#CDB371';x.fillText('MEDELLÍN',520,700);
  x.font='400 22px "DM Sans", Arial, sans-serif';try{x.letterSpacing='6px'}catch(e){};x.fillStyle='rgba(247,223,174,.6)';x.fillText('6°12′ N · 75°34′ O',515,745);
  try{x.letterSpacing='0px'}catch(e){};x.font='600 104px "Playfair Display", Georgia, serif';x.textBaseline='middle';x.fillStyle='#F7DFAE';x.shadowColor='rgba(0,0,0,.55)';x.shadowBlur=14;
  [['12',512,148],['3',880,516],['6',512,884],['9',144,516]].forEach(([n,px,py])=>x.fillText(n,px,py));x.shadowBlur=0;x.textBaseline='alphabetic';
  const dt=new THREE.CanvasTexture(dc);dt.colorSpace=THREE.SRGBColorSpace;dt.anisotropy=8;
  const dial=new THREE.Mesh(new THREE.CircleGeometry(1.43,seg),new THREE.MeshStandardMaterial({map:dt,metalness:.3,roughness:.55}));dial.position.z=0.01;clock.add(dial);
  for(let i=0;i<12;i++){if(i%3===0)continue;const big=false,a=i/12*Math.PI*2,r=1.2;const m=new THREE.Mesh(new THREE.BoxGeometry(big?0.075:0.04,big?0.27:0.16,0.05),gold);m.position.set(Math.sin(a)*r,Math.cos(a)*r,0.05);m.rotation.z=-a;clock.add(m);}
  const hand=(w,l,mat,z)=>{const p=new THREE.Group();const geo=new THREE.BoxGeometry(w,l,0.035);geo.translate(0,l/2-0.14,0);p.add(new THREE.Mesh(geo,mat));p.position.z=z;clock.add(p);return p;};
  const white=new THREE.MeshStandardMaterial({color:0xFFFFFF,metalness:.25,roughness:.3,emissive:0xFDF5E5,emissiveIntensity:.18});
  const hH=hand(0.085,0.82,white,0.09),hM=hand(0.055,1.18,white,0.12),hS=hand(0.02,1.32,gold,0.15);
  const cap=new THREE.Mesh(new THREE.CylinderGeometry(0.075,0.075,0.07,32),gold);cap.rotation.x=Math.PI/2;cap.position.z=0.18;clock.add(cap);

  // Anillo de letras
  const tc=document.createElement('canvas');tc.width=small?2048:4096;tc.height=128;const tx=tc.getContext('2d');
  tx.font='600 62px "DM Sans", Arial, sans-serif';try{tx.letterSpacing='22px'}catch(e){};tx.textBaseline='middle';tx.fillStyle='#F7DFAE';
  const phrase='MEDELLÍN  ·  EL POBLADO  ·  HAVEN  ·  ';const pw=tx.measureText(phrase).width;const n=Math.max(1,Math.floor(tc.width/pw));
  for(let i=0;i<n;i++)tx.fillText(phrase,i*tc.width/n,66);
  const rt=new THREE.CanvasTexture(tc);rt.colorSpace=THREE.SRGBColorSpace;rt.wrapS=THREE.RepeatWrapping;rt.anisotropy=8;
  const bandMat=new THREE.MeshBasicMaterial({map:rt,transparent:true,side:THREE.DoubleSide,depthWrite:false,opacity:0});
  const band=new THREE.Mesh(new THREE.CylinderGeometry(2.3,2.3,0.27,seg,1,true),bandMat);
  const bandG=new THREE.Group();bandG.add(band);bandG.rotation.set(1.2,0,0.32);root.add(bandG);

  // Partículas doradas
  const N=small?50:170,pos=new Float32Array(N*3);for(let i=0;i<N;i++){const r=2.4+Math.random()*2.4,t=Math.random()*Math.PI*2,u=Math.acos(2*Math.random()-1);pos[i*3]=r*Math.sin(u)*Math.cos(t);pos[i*3+1]=r*Math.sin(u)*Math.sin(t);pos[i*3+2]=r*Math.cos(u)-1;}
  const pg=new THREE.BufferGeometry();pg.setAttribute('position',new THREE.BufferAttribute(pos,3));
  const dust=new THREE.Points(pg,new THREE.PointsMaterial({color:0xF7DFAE,size:small?0.03:0.025,transparent:true,opacity:0,depthWrite:false}));root.add(dust);

  // Granos dorados que vuelan desde todas partes y arman el reloj (apertura)
  const TAU0=Math.PI*2;
  const sc=document.createElement('canvas');sc.width=sc.height=64;const sx=sc.getContext('2d');const sgr=sx.createRadialGradient(32,32,0,32,32,32);sgr.addColorStop(0,'rgba(255,246,220,1)');sgr.addColorStop(.35,'rgba(232,205,140,.9)');sgr.addColorStop(1,'rgba(205,179,113,0)');sx.fillStyle=sgr;sx.fillRect(0,0,64,64);
  const G=reduce?0:(small?380:1600),gFrom=new Float32Array(G*3),gTo=new Float32Array(G*3),gPos=new Float32Array(G*3),gDel=new Float32Array(G);
  for(let i=0;i<G;i++){const k=Math.random();let x,y,z;
    if(k<.45){const a=Math.random()*TAU0,b=Math.random()*TAU0,R=1.6+.12*Math.cos(b);x=R*Math.cos(a);y=R*Math.sin(a);z=.12*Math.sin(b);}
    else if(k<.85){const r=Math.sqrt(Math.random())*1.45,a=Math.random()*TAU0;x=r*Math.cos(a);y=r*Math.sin(a);z=.03;}
    else{const m=Math.floor(Math.random()*12)*TAU0/12,r=1.22+Math.random()*.16;x=r*Math.sin(m)+(Math.random()-.5)*.05;y=r*Math.cos(m)+(Math.random()-.5)*.05;z=.06;}
    gTo.set([x,y,z],i*3);
    const R2=5+Math.random()*7,t=Math.random()*TAU0,u=Math.acos(2*Math.random()-1);gFrom.set([R2*Math.sin(u)*Math.cos(t),R2*Math.sin(u)*Math.sin(t),R2*Math.cos(u)*.6+2],i*3);
    gDel[i]=Math.random()*.35+(Math.hypot(x,y)/1.75)*.12;}
  gPos.set(gFrom);
  const gg=new THREE.BufferGeometry();gg.setAttribute('position',new THREE.BufferAttribute(gPos,3));
  const gMat=new THREE.PointsMaterial({map:new THREE.CanvasTexture(sc),size:small?0.07:0.055,transparent:true,depthWrite:false,blending:THREE.AdditiveBlending,opacity:1});
  const grains=new THREE.Points(gg,gMat);const gGroup=new THREE.Group();gGroup.scale.setScalar(0.92);gGroup.add(grains);holder.add(gGroup);
  const gDir=new Float32Array(G*3);for(let i=0;i<G;i++){const x=gTo[i*3],y=gTo[i*3+1],L=Math.hypot(x,y)||1;gDir[i*3]=x/L*(.6+Math.random())+(Math.random()-.5)*.9;gDir[i*3+1]=y/L*(.6+Math.random())+(Math.random()-.5)*.9+.25;gDir[i*3+2]=(Math.random()-.2)*1.6;}
  const introEl=document.getElementById('intro');const big=!!(introEl&&introEl.isConnected&&G);
  const cntEl=big?introEl.querySelector('.intro-count b'):stage.querySelector('.clock-count b');if(G&&!big)stage.classList.add('counting');if(big)cv.classList.add('top');
  let tExit=0,I=big?1:0;const DUR=SM?1700:2600;

  scene.add(new THREE.AmbientLight(0xffffff,.25));
  const key=new THREE.DirectionalLight(0xfff1d6,2.4);key.position.set(3,4,6);scene.add(key);
  const rim=new THREE.DirectionalLight(0xCDB371,1.6);rim.position.set(-5,-2,-3);scene.add(rim);

  // Lienzo fijo a pantalla completa: el reloj sale del hero y viaja en 3D con el scroll
  let VW=innerWidth,VH=innerHeight;
  const size=()=>{VW=innerWidth;VH=innerHeight;renderer.setSize(VW,VH,true);cam.aspect=VW/VH;cam.updateProjectionMatrix();};
  size();addEventListener('resize',size);
  const UPP=()=>2*10*Math.tan(15*Math.PI/180)/VH; // unidades de mundo por píxel en z=0
  let anchor=null;const measure=()=>{const r=stage.getBoundingClientRect();anchor={cx:r.left+r.width/2,cy:r.top+r.height/2+scrollY,w:r.width};};
  measure();addEventListener('resize',measure);
  const mouse={x:0,y:0,tx:0,ty:0};
  addEventListener('pointermove',e=>{mouse.tx=(e.clientX/innerWidth-.5)*2;mouse.ty=(e.clientY/innerHeight-.5)*2;},{passive:true});
  const TAU=Math.PI*2,ease=p=>1-Math.pow(1-p,4),cl=v=>v<0?0:v>1?1:v,sm=v=>v*v*(3-2*v),lerp=(a,b,t)=>a+(b-a)*t;let lastCnt=-1;
  let t0=0,last=performance.now(),idle=false,el=0;
  const frame=now=>{
    requestAnimationFrame(frame);
    if(document.hidden||!innerWidth){last=now;return;}
    if(VW!==innerWidth||VH!==innerHeight){size();measure();}
    if(!t0)t0=now;const raw=Math.max(0,now-last),d=Math.min(0.05,raw/1000);last=now;el+=Math.min(raw,50); // el: tiempo de animación sin saltos aunque el teléfono se trabe
    if(big&&window.__introSkip&&!tExit){el=Math.max(el,DUR);}
    const P=reduce?1:Math.min(1,el/(big?DUR:3400));window.__introTakeover=1;if(big&&!introEl.classList.contains('go'))introEl.classList.add('go');
    if(big){if(P>.74)introEl.classList.add('brand');
      if(P>=1&&!tExit){tExit=now+(SM?450:1200);}
      if(!tExit&&(!introEl.isConnected||introEl.classList.contains('out')))tExit=now; // si la apertura terminó por tiempo, el reloj igual vuela a su sitio
      if(tExit&&now>=tExit&&introEl.isConnected&&!introEl.classList.contains('out'))window.__introEnd&&window.__introEnd();
      I=tExit?1-ease(cl((now-tExit)/(SM?700:1100))):1;
      if(I<=0&&cv.classList.contains('top'))cv.classList.remove('top');}
    const p=cl((P-.5)/.5),e=ease(p);
    // Recorrido con scroll: S 0→1 a lo largo de ~1.7 pantallas
    const S=reduce?0:cl(scrollY/(VH*(VW<821?.75:.6)));
    if(P>=1&&S>=1){if(!idle){idle=true;cv.classList.add('gone');}return;}
    if(idle){idle=false;cv.classList.remove('gone');}
    mouse.x+=(mouse.tx-mouse.x)*0.05;mouse.y+=(mouse.ty-mouse.y)*0.05;
    const u=UPP(),k=(anchor.w*1.28/5.359)*u;   // escala para que el reloj llene su espacio en el hero
    const sx=(anchor.cx-VW/2)*u,sy=-(anchor.cy-VH/2)*u; // posición inicial (scroll 0)
    const A=sm(cl(S/.45)),B=cl((S-.42)/.38),C=cl((S-.78)/.22);
    const small=VW<821,endScale=k*(small?1.05:1.35);
    const kc=Math.min(VH*.4,VW*.62)*u/3.44,cy=VH*.09*u; // tamaño y posición en la apertura (centro de pantalla)
    // En celular el reloj acompaña la página (no tapa el título): gira en 3D y se desarma al salir por arriba
    const px=sx, py=sy+scrollY*u*(small?.65:1)+B*0.4; // acompaña la página: nunca tapa el video del banner
    holder.position.set(lerp(px,0,I),lerp(py,cy,I)+Math.sin(now/1500)*0.06,0);
    holder.scale.setScalar(lerp(k*(1+A*.12),kc,I));
    holder.rotation.y=A*TAU+mouse.x*0.32+Math.sin(now/3200)*0.06+B*0.8;
    holder.rotation.x=Math.sin(A*Math.PI)*0.55+mouse.y*0.22-B*0.3;
    holder.rotation.z=Math.sin(A*Math.PI)*-0.18;
    // apertura (granos que arman el reloj) y explosión con scroll
    if(G){
      if(P<1){const gp=Math.min(1,P/.62);
        for(let i=0;i<G;i++){let q=(gp-gDel[i])/(1-.47);q=q<0?0:q>1?1:q;q=1-Math.pow(1-q,3);const j=i*3,sw=(1-q)*1.4;
          gPos[j]=gFrom[j]+(gTo[j]-gFrom[j])*q+Math.sin(now/400+i)*sw*.08;gPos[j+1]=gFrom[j+1]+(gTo[j+1]-gFrom[j+1])*q+Math.cos(now/500+i)*sw*.08;gPos[j+2]=gFrom[j+2]+(gTo[j+2]-gFrom[j+2])*q;}
        gg.attributes.position.needsUpdate=true;
        gMat.opacity=Math.max(P<.6?1:1-(P-.6)/.3,B>0?Math.min(1,B*4):0);
        gGroup.rotation.y=(1-Math.min(1,P/.62))*-1.6;gGroup.rotation.x=(1-Math.min(1,P/.62))*.8;
        const c=Math.min(100,Math.round(ease(Math.min(1,P/.9))*100));if(c!==lastCnt){lastCnt=c;cntEl.textContent=String(c).padStart(2,'0');}
        if(P>.95&&!big)stage.classList.remove('counting');
      }else{
        gGroup.rotation.set(0,0,0);
        if(B>0){const eb=1-Math.pow(1-B,2),dr=eb*3.2+C*2.2;
          for(let i=0;i<G;i++){const j=i*3,w=Math.sin(now/700+i)*.05*eb;
            gPos[j]=gTo[j]+gDir[j]*dr+w;gPos[j+1]=gTo[j+1]+gDir[j+1]*dr+C*1.5+w;gPos[j+2]=gTo[j+2]+gDir[j+2]*dr;}
          gg.attributes.position.needsUpdate=true;}
        gMat.opacity=B>0?Math.min(1,B*5)*(1-C):0;
      }
      gGroup.visible=gMat.opacity>0.01;
    }
    // el reloj sólido aparece al final de la apertura y se disuelve cuando explota
    const vis=(G?(p>0?1:0):1)*(1-cl(B*3.5));
    root.visible=vis>0.01;
    root.scale.setScalar(G?(0.0001+(0.92+0.08*e)*vis):(0.3+0.7*e));
    root.rotation.y=(1-e)*(G?0:-2.6);root.rotation.x=(1-e)*(G?0:1.0);
    const t=mde(),sec=(t.s+t.ms/1000)/60,min=(t.m+sec)/60,hr=((t.h%12)+min)/12;
    hS.rotation.z=-TAU*(sec+(1-e)*6+A*3);hM.rotation.z=-TAU*(min+(1-e)*3+A*1);hH.rotation.z=-TAU*(hr+(1-e)*1+A*.25);
    bandMat.opacity=Math.min(1,e*1.3)*(1-A*.6);band.rotation.y+=d*(0.18+(1-e)*2.5+A*1.5);
    dust.material.opacity=0.7*e*(1-B);dust.rotation.y+=d*0.03;
    renderer.render(scene,cam);
    if(!cv.classList.contains('ready')){cv.classList.add('ready');stage.classList.add('ready');}
  };
  // Arranque sólido en un dispositivo nuevo: compila los shaders sin bloquear (si el navegador lo permite),
  // y espera unos cuadros estables antes de empezar a contar el tiempo de la apertura.
  try{if(renderer.compileAsync)await renderer.compileAsync(scene,cam);else renderer.compile(scene,cam);renderer.render(scene,cam);}catch(e){try{renderer.compile(scene,cam);}catch(_){}}
  await new Promise(res=>{let ok=0,prev=performance.now();const t0w=prev;const w=n=>{const dt=n-prev;prev=n;ok=dt<24?ok+1:0;if(ok>=4||n-t0w>(SM?300:700))res();else requestAnimationFrame(w);};requestAnimationFrame(w);});
  last=performance.now();
  requestAnimationFrame(frame);
}
// Con apertura: arranca justo después del primer cuadro pintado (pedir la tarjeta gráfica puede trabar la pantalla un momento).
const afterPaint=()=>requestAnimationFrame(()=>setTimeout(start,0));
const go=()=>document.getElementById('intro')?afterPaint():(('requestIdleCallback' in window)?requestIdleCallback(start,{timeout:600}):setTimeout(start,150));
if(document.getElementById('intro'))go();else if(document.readyState==='complete')go();else addEventListener('load',go);
