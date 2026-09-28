<?php
$pageTitle='SYSTEM';
require_once __DIR__.'/includes/scenario.php';
if (!all_flags_obtained()) { http_response_code(404); echo '404'; exit; }
lab_sensitive_page('system.php','Pestaña creada por el atacante','critical');
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>// SYSTEM //</title><style>
html,body{margin:0;min-height:100%;background:#020202;color:#d8ffd8;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}body{overflow-x:hidden;background:radial-gradient(circle at 50% 22%,rgba(0,255,102,.12),transparent 28%),linear-gradient(#020202,#050805)}body:before{content:"";position:fixed;inset:0;pointer-events:none;background:repeating-linear-gradient(0deg,rgba(255,255,255,.025) 0 1px,transparent 1px 4px);mix-blend-mode:screen}.wrap{max-width:1080px;margin:auto;padding:42px 24px 70px}.top{display:flex;justify-content:space-between;gap:20px;color:#61ff93;font-size:12px}.badge{border:1px solid #31ff70;padding:7px 10px;box-shadow:0 0 22px rgba(49,255,112,.18)}h1{font-size:clamp(60px,11vw,150px);line-height:.8;margin:80px 0 28px;color:white;letter-spacing:-.09em;text-shadow:4px 0 #ff1744,-4px 0 #00e5ff;animation:g 2.7s infinite steps(2)}@keyframes g{50%{transform:translateX(2px);text-shadow:-5px 0 #ff1744,5px 0 #00e5ff}}.lead{font-size:clamp(22px,3vw,38px);max-width:900px;line-height:1.18}.lead strong{color:#ff315e}.term{margin-top:52px;border:1px solid #143c20;background:rgba(0,15,5,.74);box-shadow:0 0 60px rgba(0,255,90,.08);padding:24px}.term p{margin:9px 0}.prompt{color:#52ff82}.danger{color:#ff315e}.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:28px}.grid div{border:1px solid #183922;padding:14px}.grid b{font-size:26px;color:#fff}.footer{margin-top:52px;color:#6f8d76;font-size:12px;border-top:1px solid #183922;padding-top:18px}.cursor{animation:b .75s step-end infinite}@keyframes b{50%{opacity:0}}@media(max-width:700px){.grid{grid-template-columns:repeat(2,1fr)}}
</style></head><body><main class="wrap"><div class="top"><span class="badge">ROOT SESSION :: ACTIVE</span><span>INTERAFAS / UNAUTHORIZED TAB / 20 OF 20</span></div><h1>OWNED.</h1><p class="lead"><strong>Ustedes llamaron “transformación digital” a conectar puertas que nunca aprendieron a cerrar.</strong><br>Veinte fallas. Veinte oportunidades. Una ciudad entera confiando en una ilusión de control.</p><div class="term"><p><span class="prompt">root@interafas:~#</span> whoami</p><p>COLECTIVO UMBRAL</p><p><span class="prompt">root@interafas:~#</span> cat /etc/reality</p><p>El gobierno no perdió una página. Perdió la certeza de que sabía qué estaba protegiendo.</p><p><span class="prompt">root@interafas:~#</span> echo $MESSAGE</p><p class="danger">NO FUE MAGIA. FUE DEUDA TÉCNICA, CONTROLES ROTOS Y CONFIANZA MAL PUESTA.</p><p><span class="prompt">root@interafas:~#</span> _<span class="cursor">█</span></p><div class="grid"><div><small>FLAGS</small><b>20/20</b></div><div><small>IT</small><b>BREACHED</b></div><div><small>OT</small><b>REACHED</b></div><div><small>TRUST</small><b>0%</b></div></div></div><div class="footer">Escenario ficticio y confinado al laboratorio académico INTERAFAS. Esta pestaña forma parte de la simulación.</div></main>
<script>
(()=>{
  const SNAP_KEY='INTERAFAS_DEFACEMENT_SNAPSHOT_V1';

  async function captureSystemTab(){
    const topLevel=window.top===window;

    try{
      if(!topLevel && sessionStorage.getItem(SNAP_KEY)==='1')return;
    }catch(_){}

    // Allow fonts/layout and the glitch treatment to paint before freezing the frame.
    await new Promise(resolve=>setTimeout(resolve,420));

    const width=Math.min(1440,Math.max(1024,window.innerWidth||1280));
    const height=Math.min(1100,Math.max(720,document.documentElement.scrollHeight||0,window.innerHeight||0));

    const body=document.body.cloneNode(true);
    body.querySelectorAll('script').forEach(node=>node.remove());
    body.setAttribute('xmlns','http://www.w3.org/1999/xhtml');
    body.style.width=width+'px';
    body.style.height=height+'px';
    body.style.overflow='hidden';
    body.style.margin='0';

    const style=document.createElement('style');
    const pageCss=[...document.querySelectorAll('style')].map(s=>s.textContent||'').join('\n');
    style.textContent=pageCss+
      '\n*{animation-play-state:paused!important;}'+
      '\nh1{transform:none!important;}'+
      '\nhtml,body{width:'+width+'px!important;height:'+height+'px!important;overflow:hidden!important;}';
    body.prepend(style);

    const serialized=new XMLSerializer().serializeToString(body);
    const svg=
      '<svg xmlns="http://www.w3.org/2000/svg" width="'+width+'" height="'+height+'">'+
      '<foreignObject x="0" y="0" width="100%" height="100%">'+serialized+'</foreignObject>'+
      '</svg>';

    const blob=new Blob([svg],{type:'image/svg+xml;charset=utf-8'});
    const url=URL.createObjectURL(blob);

    try{
      const image=await new Promise((resolve,reject)=>{
        const img=new Image();
        img.onload=()=>resolve(img);
        img.onerror=()=>reject(new Error('render-failed'));
        img.src=url;
      });

      const canvas=document.createElement('canvas');
      canvas.width=width;
      canvas.height=height;

      const ctx=canvas.getContext('2d');
      ctx.fillStyle='#020202';
      ctx.fillRect(0,0,width,height);
      ctx.drawImage(image,0,0,width,height);

      const payload={
        image:canvas.toDataURL('image/jpeg',.9),
        captured_at:new Date().toISOString(),
        viewport_width:window.innerWidth||width,
        viewport_height:window.innerHeight||height
      };

      const response=await fetch('/defacement-snapshot.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        credentials:'same-origin',
        cache:'no-store',
        body:JSON.stringify(payload)
      });

      const result=await response.json();
      if(response.ok&&result.ok){
        try{sessionStorage.setItem(SNAP_KEY,'1')}catch(_){}
      }
    }catch(_){
      // The screenshot is evidence enrichment only; never break the SYSTEM page.
    }finally{
      URL.revokeObjectURL(url);
    }
  }

  if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',captureSystemTab,{once:true});
  }else{
    captureSystemTab();
  }
})();
</script>
</body></html>