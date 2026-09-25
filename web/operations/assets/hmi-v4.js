(()=>{
'use strict';

const state={
  activeView:'overview',
  stationIndex:0,
  range:'1h',
  drawer:null
};

const palette={
  grid:'rgba(74,112,130,.22)',
  text:'#7f9aaa',
  line1:'#4cb2d4',
  line2:'#72d4aa',
  line3:'#e6bd62',
  danger:'#e47b72',
  fill1:'rgba(76,178,212,.15)',
  fill2:'rgba(114,212,170,.10)'
};

const ranges={
  '15m':{points:30,label:'15 min'},
  '1h':{points:60,label:'1 h'},
  '8h':{points:64,label:'8 h'},
  '24h':{points:72,label:'24 h'},
  '7d':{points:84,label:'7 d'}
};

function seededSeries(n,base,amp,phase=0,noise=.12){
  const out=[];
  for(let i=0;i<n;i++){
    const wave=Math.sin((i/n)*Math.PI*2+phase)*amp;
    const wave2=Math.sin((i/n)*Math.PI*5+phase*.7)*amp*.23;
    const jitter=Math.sin(i*12.9898+phase*78.233)*amp*noise;
    out.push(base+wave+wave2+jitter);
  }
  return out;
}

function labels(n,range){
  const step=Math.max(1,Math.floor(n/6));
  return Array.from({length:n},(_,i)=>{
    if(i%step!==0 && i!==n-1) return '';
    if(range==='7d') return ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'][Math.min(6,Math.floor(i/(n/7)))];
    if(range==='24h') return String(Math.floor(i/(n/24))).padStart(2,'0')+':00';
    if(range==='8h') return '-'+Math.round((n-1-i)*(8/(n-1)))+'h';
    if(range==='15m') return '-'+Math.round((n-1-i)*(15/(n-1)))+'m';
    return '-'+Math.round((n-1-i)*(60/(n-1)))+'m';
  });
}

function niceBounds(seriesList){
  const all=seriesList.flat();
  let min=Math.min(...all),max=Math.max(...all);
  const span=Math.max(.1,max-min);
  min-=span*.18; max+=span*.18;
  return {min,max};
}

function drawChart(canvas,series,opts={}){
  if(!canvas)return;
  const rect=canvas.getBoundingClientRect();
  const dpr=Math.min(2,window.devicePixelRatio||1);
  canvas.width=Math.max(300,Math.floor(rect.width*dpr));
  canvas.height=Math.max(190,Math.floor((opts.height||260)*dpr));
  const ctx=canvas.getContext('2d');
  ctx.scale(dpr,dpr);
  const w=canvas.width/dpr,h=canvas.height/dpr;
  const pad={l:52,r:18,t:18,b:34};
  const cw=w-pad.l-pad.r,ch=h-pad.t-pad.b;
  const bounds=niceBounds(series.map(s=>s.values));
  const yMin=opts.yMin??bounds.min,yMax=opts.yMax??bounds.max;
  ctx.clearRect(0,0,w,h);
  ctx.font='10px Segoe UI,Arial';
  ctx.textBaseline='middle';

  for(let i=0;i<=4;i++){
    const y=pad.t+ch*(i/4);
    ctx.strokeStyle=palette.grid;ctx.lineWidth=1;
    ctx.beginPath();ctx.moveTo(pad.l,y);ctx.lineTo(w-pad.r,y);ctx.stroke();
    const v=yMax-(yMax-yMin)*(i/4);
    ctx.fillStyle=palette.text;ctx.textAlign='right';
    ctx.fillText((opts.formatY?opts.formatY(v):v.toFixed(opts.decimals??0)),pad.l-8,y);
  }

  const ls=opts.labels||[];
  const step=Math.max(1,Math.floor(ls.length/6));
  ctx.textAlign='center';ctx.fillStyle=palette.text;
  ls.forEach((label,i)=>{if(label){const x=pad.l+cw*(i/(ls.length-1||1));ctx.fillText(label,x,h-15);}});

  series.forEach((s,si)=>{
    const color=s.color||[palette.line1,palette.line2,palette.line3,palette.danger][si%4];
    const vals=s.values;
    ctx.beginPath();
    vals.forEach((v,i)=>{
      const x=pad.l+cw*(i/(vals.length-1||1));
      const y=pad.t+ch*(1-(v-yMin)/(yMax-yMin||1));
      if(i===0)ctx.moveTo(x,y);else ctx.lineTo(x,y);
    });
    ctx.strokeStyle=color;ctx.lineWidth=2.2;ctx.stroke();
    if(s.fill){
      const lastX=pad.l+cw;
      ctx.lineTo(lastX,pad.t+ch);ctx.lineTo(pad.l,pad.t+ch);ctx.closePath();
      ctx.fillStyle=s.fill;ctx.fill();
    }
  });

  canvas._chartMeta={series,opts,pad,cw,ch,yMin,yMax,w,h};
}

function enableChartTooltip(canvas){
  if(!canvas || canvas.dataset.tooltipBound)return;
  canvas.dataset.tooltipBound='1';
  let tip=document.querySelector('.chart-tooltip');
  if(!tip){tip=document.createElement('div');tip.className='chart-tooltip';document.body.appendChild(tip);}
  canvas.addEventListener('mousemove',e=>{
    const m=canvas._chartMeta;if(!m)return;
    const r=canvas.getBoundingClientRect();
    const x=e.clientX-r.left;
    const norm=Math.max(0,Math.min(1,(x-m.pad.l)/m.cw));
    const n=m.series[0].values.length;
    const idx=Math.round(norm*(n-1));
    tip.innerHTML=m.series.map(s=>'<div><i style="background:'+ (s.color||palette.line1) +'"></i><b>'+s.name+'</b><span>'+formatValue(s.values[idx],s.unit)+'</span></div>').join('');
    tip.style.left=(e.clientX+14)+'px';tip.style.top=(e.clientY+14)+'px';tip.classList.add('show');
  });
  canvas.addEventListener('mouseleave',()=>tip.classList.remove('show'));
}

function formatValue(v,unit=''){return (Math.abs(v)>=100?v.toFixed(0):v.toFixed(1))+(unit?' '+unit:'');}

function renderTrends(range=state.range){
  state.range=range;
  const n=ranges[range].points, labs=labels(n,range);
  const configs=[
    ['chart-supply',[
      {name:'Supply',unit:'L/s',values:seededSeries(n,8380,310,.2),color:palette.line1,fill:palette.fill1},
      {name:'Demand',unit:'L/s',values:seededSeries(n,7836,280,.8),color:palette.line2}
    ],{labels:labs,formatY:v=>Math.round(v/100)*100+'',decimals:0}],
    ['chart-pressure',[
      {name:'Cerro',unit:'bar',values:seededSeries(n,4.10,.14,.1),color:palette.line1},
      {name:'Saint Louis',unit:'bar',values:seededSeries(n,4.32,.16,.8),color:palette.line2},
      {name:'Soledade',unit:'bar',values:seededSeries(n,4.21,.13,1.5),color:palette.line3}
    ],{labels:labs,decimals:1,formatY:v=>v.toFixed(1)}],
    ['chart-reservoir',[
      {name:'Cerro',unit:'%',values:seededSeries(n,78,.9,.5),color:palette.line1},
      {name:'Saint Louis',unit:'%',values:seededSeries(n,80,.8,1.1),color:palette.line2},
      {name:'Soledade',unit:'%',values:seededSeries(n,80,.7,1.9),color:palette.line3}
    ],{labels:labs,formatY:v=>Math.round(v)+'%'}],
    ['chart-energy',[
      {name:'Energy',unit:'MW',values:seededSeries(n,16.7,1.15,.4),color:palette.line1,fill:palette.fill1}
    ],{labels:labs,decimals:1,formatY:v=>v.toFixed(1)}],
    ['chart-latency',[
      {name:'Median',unit:'ms',values:seededSeries(n,28,4,.3),color:palette.line2},
      {name:'RTU-SL-02',unit:'ms',values:seededSeries(n,43,11,1.2).map((v,i)=>i>n*.62&&i<n*.72?v+35:v),color:palette.line3}
    ],{labels:labs,formatY:v=>Math.round(v)}],
    ['chart-quality',[
      {name:'Turbidity',unit:'NTU',values:seededSeries(n,.34,.055,.6),color:palette.line1},
      {name:'Residual chlorine',unit:'mg/L',values:seededSeries(n,.72,.06,1.6),color:palette.line2}
    ],{labels:labs,decimals:2,formatY:v=>v.toFixed(2)}]
  ];
  configs.forEach(([id,series,opts])=>{const c=document.getElementById(id);drawChart(c,series,opts);enableChartTooltip(c);});
  document.querySelectorAll('[data-trend-range]').forEach(b=>b.classList.toggle('active',b.dataset.trendRange===range));
}

function showView(name){
  state.activeView=name;
  document.querySelectorAll('.nav-item[data-view-target]').forEach(x=>x.classList.toggle('active',x.dataset.viewTarget===name));
  document.querySelectorAll('.view').forEach(v=>{
    const active=v.dataset.view===name;
    v.classList.toggle('active',active);
    if(active){v.classList.remove('view-enter');void v.offsetWidth;v.classList.add('view-enter');}
  });
  history.replaceState(null,'','#'+name);
  if(name==='trends')requestAnimationFrame(()=>renderTrends(state.range));
}

function openDrawer(title,subtitle,html){
  let drawer=document.getElementById('detail-drawer');
  if(!drawer){
    drawer=document.createElement('aside');drawer.id='detail-drawer';drawer.className='detail-drawer';
    drawer.innerHTML='<div class="drawer-head"><div><span id="drawer-sub"></span><h3 id="drawer-title"></h3></div><button class="drawer-close" aria-label="Cerrar">×</button></div><div class="drawer-body" id="drawer-body"></div>';
    document.body.appendChild(drawer);
    drawer.querySelector('.drawer-close').addEventListener('click',()=>drawer.classList.remove('open'));
  }
  drawer.querySelector('#drawer-title').textContent=title;
  drawer.querySelector('#drawer-sub').textContent=subtitle||'';
  drawer.querySelector('#drawer-body').innerHTML=html;
  drawer.classList.add('open');
}

function bindInteractions(){
  document.querySelectorAll('[data-view-target]').forEach(btn=>btn.addEventListener('click',()=>showView(btn.dataset.viewTarget)));
  document.querySelectorAll('[data-view-jump]').forEach(btn=>btn.addEventListener('click',()=>showView(btn.dataset.viewJump)));
  document.querySelectorAll('[data-trend-range]').forEach(btn=>btn.addEventListener('click',()=>renderTrends(btn.dataset.trendRange)));

  document.querySelectorAll('.metric').forEach(metric=>metric.addEventListener('click',()=>{
    const label=metric.querySelector('label')?.textContent||'Indicator';
    const value=metric.querySelector('strong')?.textContent||'—';
    const sub=metric.querySelector('.sub')?.textContent||'Metropolitan operating metric';
    openDrawer(label,'LIVE METRIC','<div class="drawer-kpi">'+value+'</div><p>'+sub+'</p><div class="drawer-chart"><canvas id="drawer-spark"></canvas></div><div class="drawer-meta"><span>Source</span><b>HIST-01 / HMI-OPS-01</b><span>Refresh</span><b>5 s</b><span>Scope</span><b>Metropolitan</b></div>');
    requestAnimationFrame(()=>{const c=document.getElementById('drawer-spark');drawChart(c,[{name:label,values:seededSeries(36,100,9,.5),color:palette.line1,fill:palette.fill1}],{labels:labels(36,'1h')});});
  }));

  document.querySelectorAll('.event-row,.event-timeline>div,.data-table tbody tr').forEach(row=>row.addEventListener('click',()=>{
    const txt=row.innerText.split('\n').filter(Boolean);
    openDrawer(txt[0]||'Operational record','EVENT DETAIL','<div class="drawer-record">'+txt.map((x,i)=>'<p><span>'+(['Record','Context','Value','State','Detail'][i]||'Field')+'</span><b>'+x+'</b></p>').join('')+'</div><div class="drawer-meta"><span>Correlation</span><b>HIST-01</b><span>Audit</span><b>Standard</b><span>Visibility</span><b>Operational</b></div>');
  }));

  document.querySelectorAll('.node').forEach(n=>{n.tabIndex=0;n.classList.add('interactive-node');n.addEventListener('click',()=>{
    const title=n.querySelector('strong')?.textContent||'OT Asset';
    openDrawer(title,'NETWORK ASSET','<p>'+n.textContent.replace(title,'').trim()+'</p><div class="drawer-meta"><span>Zone</span><b>OT operational network</b><span>Status</span><b class="ok-text">ONLINE</b><span>Observed by</span><b>HMI-OPS-01</b></div>');
  });});

  document.querySelectorAll('.job-card').forEach(card=>card.addEventListener('click',()=>{
    openDrawer(card.querySelector('h3')?.textContent||'Maintenance job','AUTOMATION JOB','<p>'+card.querySelector('.job-meta')?.textContent+'</p><div class="drawer-meta"><span>Engine</span><b>OT-AUTO-01</b><span>State</span><b>'+card.querySelector('.job-state')?.textContent+'</b><span>Audit profile</span><b>Standard</b></div>');
  }));

  document.addEventListener('keydown',e=>{if(e.key==='Escape')document.getElementById('detail-drawer')?.classList.remove('open');});
}

function animateLiveValues(){
  setInterval(()=>{
    document.querySelectorAll('[data-live-pulse]').forEach(el=>{
      const base=Number(el.dataset.livePulse);if(!Number.isFinite(base))return;
      const amp=Number(el.dataset.liveAmp||1);
      el.textContent=(base+(Math.random()-.5)*amp).toFixed(Number(el.dataset.liveDecimals||0));
      el.classList.add('value-flash');setTimeout(()=>el.classList.remove('value-flash'),350);
    });
  },4200);
}

window.INTERAFAS_HMI={showView,openDrawer,renderTrends};
document.addEventListener('DOMContentLoaded',()=>{
  bindInteractions();
  animateLiveValues();
  const hash=location.hash.replace('#','');
  if(hash&&document.querySelector('[data-view="'+hash+'"]'))showView(hash);
  renderTrends('1h');
});
})();