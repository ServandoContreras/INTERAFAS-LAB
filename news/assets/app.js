(() => {
  const cards=[...document.querySelectorAll('[data-news-id]')];
  const seenKey='pulso_seen_news_ids_v3';
  const readKey='pulso_read_news_ids_v1';
  let seen=[], read=[];
  try{seen=JSON.parse(localStorage.getItem(seenKey)||'[]')}catch(e){seen=[]}
  try{read=JSON.parse(localStorage.getItem(readKey)||'[]')}catch(e){read=[]}
  const current=cards.map(x=>String(x.dataset.newsId));
  cards.forEach(el=>{
    const id=String(el.dataset.newsId||'');
    const related=el.dataset.interafas==='1';
    if(read.includes(id)) el.classList.add('is-read-story');
    if(related && localStorage.getItem(seenKey) && (el.dataset.newsNew==='1' || !seen.includes(id))){
      el.classList.add('is-new-story');
      const b=document.createElement('span'); b.className='new-story-badge'; b.textContent='NUEVA';
      const target=el.querySelector('.kicker,.card-time,time,h2,h3')||el; target.before(b);
    }
    const a=el.querySelector('a[href*="/nota.php"]');
    if(a) a.addEventListener('click',()=>{
      let r=[];try{r=JSON.parse(localStorage.getItem(readKey)||'[]')}catch(e){}
      if(!r.includes(id)) r.push(id);
      localStorage.setItem(readKey,JSON.stringify(r.slice(-500)));
    });
  });
  if(!localStorage.getItem(seenKey)) localStorage.setItem(seenKey,JSON.stringify(current));
  else setTimeout(()=>localStorage.setItem(seenKey,JSON.stringify([...new Set([...seen,...current])].slice(-500))),1500);
})();


if(window.PULSO_LIVE){
  let snap=null;
  async function poll(){
    try{
      const r=await fetch('/api/state.php?ts='+Date.now(),{cache:'no-store'}), d=await r.json();
      const n=[d.phase,d.count,d.event_id,d.last_event].join(':');
      if(snap!==null&&n!==snap){
        const t=document.createElement('div');
        t.innerHTML='<b>ÚLTIMA HORA</b><span>Hay nueva información confirmada. Actualizando cobertura…</span>';
        t.className='live-toast';document.body.appendChild(t);
        setTimeout(()=>location.reload(),450);
        return;
      }
      snap=n;
    }catch(e){}
  }
  poll();setInterval(poll,1000);
}
