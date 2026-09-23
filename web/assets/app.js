(() => {
  const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const toggle=document.querySelector('.nav-toggle'), nav=document.querySelector('.primary-nav');
  if(toggle&&nav) toggle.addEventListener('click',()=>{const open=nav.classList.toggle('is-open');toggle.setAttribute('aria-expanded',open?'true':'false')});
  document.querySelectorAll('.nav-group-button').forEach(button=>button.addEventListener('click',e=>{if(window.innerWidth>1080)return;e.preventDefault();button.closest('.nav-group').classList.toggle('mobile-open')}));

  const header=document.querySelector('.site-header');
  const updateHeader=()=>{ if(header) header.classList.toggle('is-scrolled',window.scrollY>24); };
  updateHeader(); window.addEventListener('scroll',updateHeader,{passive:true});

  const calc=document.getElementById('waterCalculator');
  if(calc){calc.addEventListener('submit',e=>{e.preventDefault();const p=Math.max(1,+document.getElementById('people').value||1), shower=Math.max(0,+document.getElementById('shower').value||0), washer=Math.max(0,+document.getElementById('washer').value||0), garden=Math.max(0,+document.getElementById('garden').value||0);const daily=(shower*9)+(washer*60/7/p)+(garden*12/7/p)+55;const monthly=daily*p*30/1000;document.getElementById('calcMonthly').textContent=monthly.toFixed(1)+' m³/mes';document.getElementById('calcDaily').textContent=Math.round(daily)+' L/hab/día';let text='Consumo eficiente. Mantén tus hábitos.';if(daily>130)text='Nivel moderado. Revisa duchas, fugas y hábitos de riego.';if(daily>180)text='Consumo alto. Identifica oportunidades de reducción.';document.getElementById('calcText').textContent=text;});}

  // Scroll reveal aplicado automáticamente a bloques visuales.
  const revealSelectors=['.section-heading','.stat-card','.visual-card','.news-card','.culture-card','.support-feature','.split-feature','.promo-grid','.director-card','.constancia-card','.trans-grid>a','.social-panel','.channel-grid>div','.social-preview-main','.social-preview-comments>div','.short-video-card'];
  const reveal=[...document.querySelectorAll(revealSelectors.join(','))];
  reveal.forEach((el,i)=>{el.classList.add('reveal-item'); if(i%4) el.classList.add('reveal-delay-'+Math.min(i%4,3));});
  if(!reduceMotion && 'IntersectionObserver' in window){
    document.body.classList.add('motion-ready');
    const io=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('is-visible');io.unobserve(entry.target)}}),{threshold:.09,rootMargin:'0px 0px -35px'});
    reveal.forEach(el=>io.observe(el));
    document.querySelectorAll('.mini-bars').forEach(el=>io.observe(el));
  } else { reveal.forEach(el=>el.classList.add('is-visible')); document.querySelectorAll('.mini-bars').forEach(el=>el.classList.add('is-visible')); }

  // Contadores numéricos: conserva prefijos/sufijos y decimales simples.
  if(!reduceMotion && 'IntersectionObserver' in window){
    const numberEls=[...document.querySelectorAll('.stat-card b,.portal-kpis b,.territory-stats b,.social-preview-number b,.counter-value')];
    const countIO=new IntersectionObserver(entries=>entries.forEach(entry=>{
      if(!entry.isIntersecting)return;
      const el=entry.target, raw=el.textContent.trim();
      const match=raw.match(/^([^0-9-]*)(-?[0-9][0-9,]*(?:\.[0-9]+)?)(.*)$/);
      if(!match){countIO.unobserve(el);return;}
      const prefix=match[1], number=Number(match[2].replace(/,/g,'')), suffix=match[3]; if(!Number.isFinite(number)){countIO.unobserve(el);return;}
      const decimals=(match[2].split('.')[1]||'').length, duration=760, start=performance.now();
      const draw=now=>{const t=Math.min(1,(now-start)/duration), eased=1-Math.pow(1-t,3), value=number*eased;const formatted=decimals?value.toLocaleString('en-US',{minimumFractionDigits:decimals,maximumFractionDigits:decimals}):Math.round(value).toLocaleString('en-US');el.textContent=prefix+formatted+suffix;if(t<1)requestAnimationFrame(draw);}; requestAnimationFrame(draw); countIO.unobserve(el);
    }),{threshold:.45}); numberEls.forEach(el=>countIO.observe(el));
  }

  // Filtro temporal del feed social.
  const filters=[], posts=document.querySelectorAll('.social-post');
  const applySocialRange=(range)=>{
    posts.forEach(post=>{
      const age=Number(post.dataset.age||0);
      const show = range==='all' || (range==='today'&&age===0) || (range==='yesterday'&&age===1) || (range==='week'&&age<=7) || (range==='month'&&age<=30);
      post.classList.toggle('is-hidden',!show);
      if(show){ post.classList.add('is-visible'); post.style.removeProperty('display'); post.style.opacity='1'; post.style.transform='none'; }
    });
  };
  filters.forEach(btn=>btn.addEventListener('click',()=>{
    filters.forEach(b=>b.classList.remove('is-active')); btn.classList.add('is-active');
    applySocialRange(btn.dataset.range);
  }));
  if(filters.length) applySocialRange(document.querySelector('.social-filter.is-active')?.dataset.range || 'all');


  // Reproducción discreta de videos verticales al entrar en pantalla.
  const shortVideos=[...document.querySelectorAll('.short-video-card video')];
  if(!reduceMotion && 'IntersectionObserver' in window){
    const videoIO=new IntersectionObserver(entries=>entries.forEach(entry=>{
      const v=entry.target;
      if(entry.isIntersecting && entry.intersectionRatio>.65){ v.play().catch(()=>{}); }
      else { v.pause(); }
    }),{threshold:[0,.65,1]});
    shortVideos.forEach(v=>videoIO.observe(v));
  }

  // Pulso visual periódico.
  if(!reduceMotion){
    const preview=document.querySelector('.social-preview-main');
    if(preview)setInterval(()=>{preview.classList.add('pulse-once');setTimeout(()=>preview.classList.remove('pulse-once'),650)},7000);
  }
})();

// v0.3.5 — feedback interactivo, spotlight y superficies clicables.
(() => {
  const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const hoverTargets = document.querySelectorAll([
    '.stat-card','.visual-card','.service-card','.culture-card','.news-card','.press-card','.press-feature',
    '.director-card','.constancia-card','.chart-card','.social-post','.channel-grid>div','.social-preview-comments>div',
    '.trans-grid>a','.infra-grid>div','.dependency-grid>div','.principle-grid>div','.org-root','.org-branches>div',
    '.mini-municipal-stats>div','.support-metrics>div','.mini-grid>div','.map-frame','.short-video-card','.data-row'
  ].join(','));

  hoverTargets.forEach(el => {
    el.classList.add('hover-surface');
    if (!reduceMotion) {
      el.addEventListener('pointermove', e => {
        const r = el.getBoundingClientRect();
        el.style.setProperty('--mx', `${e.clientX-r.left}px`);
        el.style.setProperty('--my', `${e.clientY-r.top}px`);
      }, {passive:true});
    }
  });

  // Si una tarjeta tiene un único destino real, toda su superficie responde al clic.
  const cardSelectors = ['.service-card','.news-card','.press-card','.press-feature','.director-card','.chart-card','.culture-card','.social-preview-comments>div'];
  document.querySelectorAll(cardSelectors.join(',')).forEach(card => {
    if (card.matches('a[href]')) { card.classList.add('is-clickable'); return; }
    const links = [...card.querySelectorAll('a[href]')].filter(a => {
      const href=(a.getAttribute('href')||'').trim();
      return href && href !== '#';
    });
    if (links.length !== 1) return;
    const link = links[0];
    card.classList.add('is-clickable');
    card.tabIndex = 0;
    card.setAttribute('role','link');
    const go = () => {
      if (link.target === '_blank') window.open(link.href, '_blank', 'noopener');
      else window.location.href = link.href;
    };
    card.addEventListener('click', e => {
      if (e.target.closest('a,button,input,select,textarea,label,video')) return;
      go();
    });
    card.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); go(); }
    });
  });

  document.querySelectorAll('a.visual-card,a.constancia-card,.trans-grid>a,.action-strip-grid>a').forEach(el => el.classList.add('is-clickable','hover-surface'));
})();

// v0.3.7 — selección de pasarela y formateo básico de pago.
(() => {
  const tabs=[...document.querySelectorAll('.gateway-tab')];
  if(!tabs.length) return;
  const method=document.getElementById('payment-method');
  const card=document.getElementById('card-fields');
  const spei=document.getElementById('spei-fields');
  const direct=document.getElementById('direct-fields');
  const choose=(value)=>{
    tabs.forEach(t=>t.classList.toggle('active',t.dataset.method===value));
    if(method) method.value=value;
    if(card) card.classList.toggle('is-hidden',value!=='Tarjeta');
    if(spei) spei.classList.toggle('is-hidden',value!=='SPEI');
    if(direct) direct.classList.toggle('is-hidden',value!=='Domiciliación');
  };
  tabs.forEach(t=>t.addEventListener('click',()=>choose(t.dataset.method)));
  const cn=document.getElementById('card-number'), last=document.getElementById('last4');
  if(cn){cn.addEventListener('input',()=>{const d=cn.value.replace(/\D/g,'').slice(0,16);cn.value=d.replace(/(.{4})/g,'$1 ').trim();if(last)last.value=d.slice(-4);});}
})();

// v0.3.9 — botón global para volver al inicio.
(() => {
  const btn=document.querySelector('.back-to-top');
  if(!btn) return;
  const update=()=>btn.classList.toggle('is-visible',window.scrollY>520);
  update(); window.addEventListener('scroll',update,{passive:true});
  btn.addEventListener('click',()=>window.scrollTo({top:0,behavior:(window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches)?'auto':'smooth'}));
})();


// Live scenario polling
if (window.INTERAFAS_SCENARIO_POLL) {
  let scenarioSnapshot = null;
  const pollScenario = async () => {
    try {
      const r = await fetch('/api/scenario-state.php', {cache:'no-store'});
      const data = await r.json();
      const snap = `${data.phase}:${data.social_visible}:${data.event_id||0}:${data.updated_at||''}`;
      if (scenarioSnapshot !== null && snap !== scenarioSnapshot) location.reload();
      scenarioSnapshot = snap;
    } catch(e) {}
  };
  pollScenario();
  setInterval(pollScenario, 1000);
}
