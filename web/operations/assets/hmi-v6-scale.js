(()=>{
'use strict';

/* INTERAFAS HMI V6
 * High-volume live simulation, dynamic statistics and municipality-specific P&ID layouts.
 * Historical counters are virtualized: only the newest rows are rendered.
 */
const state={
  tick:0,
  alarmTotal:198420,
  eventTotal:797860,
  alarmRows:[],
  eventRows:[],
  city:'Saint Louis',
  ready:false
};

const cityProfiles={
  'Saint Louis':{
    cls:'city-sl',code:'SL',label:'METROPOLITAN PRIMARY WORKS',
    zones:['RAW WATER / STORAGE','HIGH-LIFT PUMPING','TREATMENT / REGULATION','3-ZONE DISTRIBUTION'],
    extras:[
      ['sl-res-2','TQ-SL-02','Secondary Reservoir','tank'],
      ['sl-pump-3','P-SL-103','High-Lift Pump C','pump'],
      ['sl-pump-4','P-SL-104','High-Lift Pump D','pump'],
      ['sl-valve-3','V-SL-203','Zone C Valve','valve'],
      ['sl-zone-3','ZONE C','Industrial / airport corridor','zone']
    ],
    assets:42,pumps:4,branches:3,scale:'LARGE'
  },
  'Soledade':{
    cls:'city-so',code:'SO',label:'EASTERN DISTRIBUTION WORKS',
    zones:['STORAGE','PRIMARY PUMPING','QUALITY / PRESSURE','2-ZONE DISTRIBUTION'],
    extras:[
      ['so-pump-3','P-SO-103','Booster Pump C','pump'],
      ['so-break','BRK-SO-01','Pressure Break Tank','tank']
    ],
    assets:27,pumps:3,branches:2,scale:'MEDIUM'
  },
  'Cerro de San Pablo':{
    cls:'city-cp',code:'CP',label:'HILLSIDE BOOSTER STATION',
    zones:['ELEVATED STORAGE','BOOSTER SET','DISINFECTION','HIGH-ZONE FEED'],
    extras:[
      ['cp-tower','TQ-CP-02','Elevated Balance Tank','tank']
    ],
    assets:16,pumps:2,branches:1,scale:'COMPACT'
  }
};

const alarmTemplates=[
  ['HIGH','Reservoir low-level operating margin','LT'],
  ['MEDIUM','Header pressure deviation','PT'],
  ['MEDIUM','RTU communications latency threshold','RTU'],
  ['LOW','Standby pump availability test delayed','P'],
  ['LOW','Flow variance above adaptive baseline','FT'],
  ['INFO','Quality analyzer calibration heartbeat','AIT'],
  ['INFO','PLC scan-cycle diagnostic recorded','PLC'],
  ['INFO','Valve travel verification completed','V']
];
const eventTemplates=[
  ['INFO','Historian batch committed','HIST-01'],
  ['INFO','PLC scan cycle synchronized','PLC'],
  ['INFO','Demand balancing calculation completed','OT-AUTO-01'],
  ['INFO','Operator display refresh completed','HMI-OPS-01'],
  ['WARN','RTU latency variance observed','RTU'],
  ['INFO','Quality sample accepted','QCS'],
  ['INFO','Pump runtime accumulator updated','P'],
  ['INFO','Valve state telemetry archived','V'],
  ['INFO','Engineering checksum observed','EWS-01'],
  ['INFO','Recovery catalog heartbeat verified','BRS-01']
];

const fmt=n=>Math.round(n).toLocaleString('en-US');
const time=()=>new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit'});
const pick=a=>a[Math.floor(Math.random()*a.length)];
const currentStation=()=>document.querySelector('[data-current-station]')?.textContent?.trim()||'EST-SL-01';

function ensureLiveBadges(){
  const alarmView=document.querySelector('.view[data-view="alarms"]');
  if(alarmView){
    const tools=alarmView.querySelector('.page-tools');
    if(tools && !tools.querySelector('[data-v6-alarm-total]')){
      tools.innerHTML='<span class="tool-chip live-volume"><i class="live-dot"></i><b data-v6-alarm-total>'+fmt(state.alarmTotal)+'</b> RECORDS</span><span class="tool-chip"><b data-v6-alarm-rate>0</b>/MIN INGEST</span><span class="tool-chip"><b data-v6-alarm-active>7</b> ACTIVE</span>';
    }
    const strip=alarmView.querySelector('.alarm-filter-strip');
    if(strip) strip.innerHTML='<span>ALL <b data-v6-alarm-total>'+fmt(state.alarmTotal)+'</b></span><span>HIGH <b data-v6-high>1</b></span><span>MEDIUM <b data-v6-med>3</b></span><span>LOW <b data-v6-low>3</b></span><span>INFO <b data-v6-info>'+fmt(state.alarmTotal-7)+'</b></span><span>STREAMING</span>';
    const tbody=alarmView.querySelector('tbody');
    if(tbody){tbody.id='v6-alarm-body';state.alarmRows=[...tbody.querySelectorAll('tr')].slice(0,20).map(tr=>tr.outerHTML);}
  }

  const eventView=document.querySelector('.view[data-view="events"]');
  if(eventView){
    const tools=eventView.querySelector('.page-tools');
    if(tools) tools.innerHTML='<span class="tool-chip live-volume"><i class="live-dot"></i><b data-v6-event-total>'+fmt(state.eventTotal)+'</b> EVENTS</span><span class="tool-chip"><b data-v6-event-rate>0</b>/MIN</span><span class="tool-chip">LIVE SESSION</span>';
  }

  const statsView=document.querySelector('.view[data-view="statistics"]');
  if(statsView && !statsView.querySelector('.v6-volume-strip')){
    const strip=document.createElement('div');
    strip.className='v6-volume-strip';
    strip.innerHTML='<div><span>ALARM HISTORY</span><b data-v6-alarm-total>'+fmt(state.alarmTotal)+'</b><small>virtualized records</small></div><div><span>EVENT HISTORY</span><b data-v6-event-total>'+fmt(state.eventTotal)+'</b><small>virtualized records</small></div><div><span>EVENT INGEST</span><b data-v6-event-rate>0</b><small>records / min</small></div><div><span>ALARM INGEST</span><b data-v6-alarm-rate>0</b><small>records / min</small></div>';
    const liveStrip=statsView.querySelector('.live-statistics-strip');
    liveStrip?.insertAdjacentElement('afterend',strip);
  }
}

function makeAlarmRow(){
  const [priority,message,kind]=pick(alarmTemplates);
  const station=currentStation();
  const city=document.querySelector('[data-current-city]')?.textContent?.trim()||state.city;
  const code=(station.match(/EST-([A-Z]{2})/)||[])[1]||'SL';
  const src=kind==='RTU'?('RTU-'+code+'-'+(1+Math.floor(Math.random()*5))):(kind+'-'+code+'-'+String(101+Math.floor(Math.random()*105)));
  const status=Math.random()<.7?'UNACK':(Math.random()<.6?'ACK':'CLEARED');
  const cls=priority==='HIGH'||priority==='MEDIUM'?'warn-text':'';
  return '<tr class="v6-row-enter"><td>'+time()+'</td><td class="'+cls+'">'+priority+'</td><td>'+city+'</td><td>'+src+'</td><td>'+message+'</td><td>'+status+'</td></tr>';
}

function pushAlarm(){
  const count=1+Math.floor(Math.random()*3);
  state.alarmTotal+=count;
  state.alarmRows.unshift(makeAlarmRow());
  state.alarmRows=state.alarmRows.slice(0,60);
  const body=document.getElementById('v6-alarm-body');
  if(body) body.innerHTML=state.alarmRows.join('');
  updateVolumeCounters(count,0);
}

function pushEvent(){
  const burst=2+Math.floor(Math.random()*7);
  state.eventTotal+=burst;
  const [sev,msg,sourceBase]=pick(eventTemplates);
  const code=(currentStation().match(/EST-([A-Z]{2})/)||[])[1]||'SL';
  const source=['PLC','RTU','P','V','QCS'].includes(sourceBase)?sourceBase+'-'+code+'-'+String(1+Math.floor(Math.random()*9)).padStart(2,'0'):sourceBase;
  if(window.INTERAFAS_LIVE?.pushLog) window.INTERAFAS_LIVE.pushLog(sev,source,msg+' · batch +'+burst);
  updateVolumeCounters(0,burst);
}

function updateVolumeCounters(alarmBurst=0,eventBurst=0){
  document.querySelectorAll('[data-v6-alarm-total]').forEach(n=>n.textContent=fmt(state.alarmTotal));
  document.querySelectorAll('[data-v6-event-total]').forEach(n=>n.textContent=fmt(state.eventTotal));
  document.querySelectorAll('[data-v6-info]').forEach(n=>n.textContent=fmt(Math.max(0,state.alarmTotal-7)));
  const alarmRate=Math.round(68+Math.sin(state.tick/8)*13+alarmBurst*5);
  const eventRate=Math.round(430+Math.sin(state.tick/6)*75+eventBurst*8);
  document.querySelectorAll('[data-v6-alarm-rate]').forEach(n=>n.textContent=fmt(alarmRate));
  document.querySelectorAll('[data-v6-event-rate]').forEach(n=>n.textContent=fmt(eventRate));
}

function ensureProcessExtras(){
  const scene=document.querySelector('.pid-scene');
  if(!scene || scene.dataset.v6Ready)return;
  scene.dataset.v6Ready='1';
  const extras=document.createElement('div');
  extras.className='v6-process-extras';
  extras.innerHTML=
    '<div class="v6-extra v6-tank sl-res-2"><b>TQ-SL-02</b><span>Secondary Reservoir</span><i></i><strong data-live-tag="LEVEL">—</strong></div>'+
    '<div class="v6-extra v6-pump sl-pump-3" data-pump-state="P101"><i></i><b>P-SL-103</b><span>High-Lift C</span></div>'+
    '<div class="v6-extra v6-pump sl-pump-4" data-pump-state="P102"><i></i><b>P-SL-104</b><span>High-Lift D</span></div>'+
    '<div class="v6-extra v6-valve sl-valve-3" data-valve-state="V201"><i></i><b>V-SL-203</b><span>ZONE C</span></div>'+
    '<div class="v6-extra v6-zone sl-zone-3"><b>ZONE C</b><span>Industrial / airport</span><strong data-live-tag="FLOW">—</strong></div>'+
    '<div class="v6-extra v6-pump so-pump-3" data-pump-state="P101"><i></i><b>P-SO-103</b><span>Booster C</span></div>'+
    '<div class="v6-extra v6-tank so-break"><b>BRK-SO-01</b><span>Pressure Break</span><i></i><strong data-live-tag="LEVEL">—</strong></div>'+
    '<div class="v6-extra v6-tank cp-tower"><b>TQ-CP-02</b><span>Elevated Balance</span><i></i><strong data-live-tag="LEVEL">—</strong></div>'+
    '<div class="v6-flow-pulse pulse-a"></div><div class="v6-flow-pulse pulse-b"></div><div class="v6-flow-pulse pulse-c"></div>'+
    '<div class="v6-scanline"></div>';
  scene.appendChild(extras);
}

function applyCityProfile(city){
  state.city=cityProfiles[city]?city:'Saint Louis';
  const p=cityProfiles[state.city];
  const scene=document.querySelector('.pid-scene');
  if(!scene)return;
  scene.classList.remove('city-sl','city-so','city-cp');
  scene.classList.add(p.cls);
  scene.dataset.city=p.code;
  const zones=[...scene.querySelectorAll('.pid-zone')];
  zones.forEach((z,i)=>{if(p.zones[i])z.textContent=p.zones[i];});
  const title=document.querySelector('.pid-titlebar strong');
  if(title) title.textContent=p.label;
  const subtitle=document.querySelector('.pid-titlebar span');
  if(subtitle) subtitle.textContent=p.scale+' PROCESS · '+p.assets+' observed assets · '+p.pumps+' pump trains · '+p.branches+' distribution branches';

  // City-specific naming makes the schematic read as a different plant, not a reused template.
  const code=p.code;
  const replacements=[
    ['.reservoir-vessel .equipment-label b','TQ-'+code+'-01'],
    ['.pump-a .equipment-label b','P-'+code+'-101'],
    ['.pump-b .equipment-label b','P-'+code+'-102'],
    ['.quality-skid .equipment-label b','QCS-'+code+'-01'],
    ['.pressure-header .equipment-label b','HDR-'+code+'-01'],
    ['.valve-1 span','V-'+code+'-201'],
    ['.valve-2 span','V-'+code+'-202']
  ];
  replacements.forEach(([sel,val])=>{const el=scene.querySelector(sel);if(el)el.textContent=val;});

  const distA=scene.querySelector('.dist-a span'), distB=scene.querySelector('.dist-b span');
  if(state.city==='Saint Louis'){if(distA)distA.textContent='Central / commercial grid';if(distB)distB.textContent='Residential / university grid';}
  if(state.city==='Soledade'){if(distA)distA.textContent='North / industrial distribution';if(distB)distB.textContent='South / residential distribution';}
  if(state.city==='Cerro de San Pablo'){if(distA)distA.textContent='High-zone municipal feed';if(distB)distB.textContent='Reserve bypass';}}

function observeStationChanges(){
  const city=document.querySelector('[data-current-city]');
  if(!city)return;
  const sync=()=>applyCityProfile(city.textContent.trim());
  new MutationObserver(sync).observe(city,{childList:true,characterData:true,subtree:true});
  sync();
}

function dynamicStatistics(){
  const view=document.querySelector('.view[data-view="statistics"]');
  if(!view)return;

  // Animate legacy bars and heat cells.
  view.querySelectorAll('.horizontal-bars i').forEach((bar,i)=>{
    const v=28+((state.tick*3+i*17)%64);
    bar.style.width=v+'%';
  });
  view.querySelectorAll('.vertical-bars i').forEach((bar,i)=>{
    const v=48+Math.round((Math.sin((state.tick+i*4)/7)+1)*22);
    bar.style.height=v+'%';
    const value=bar.parentElement?.querySelector('b');
    if(value)value.textContent=(3.8+v/18).toFixed(1);
  });
  view.querySelectorAll('.capacity-list i b').forEach((bar,i)=>{
    const v=55+Math.round((Math.sin((state.tick+i*3)/9)+1)*16);
    bar.style.width=v+'%';
    const em=bar.closest('div')?.querySelector('em');if(em)em.textContent=v+'%';
  });
  const heat=[...view.querySelectorAll('.heatmap i')];
  if(heat.length && state.tick%2===0){
    heat.forEach((cell,i)=>{cell.className='h'+Math.max(0,Math.min(4,Math.round((Math.sin((state.tick+i)/4)+1)*2)));});
  }

  // Replace the fixed production SVG path with a moving live waveform.
  const svg=view.querySelector('.area-chart svg');
  if(svg){
    const line=svg.querySelector('.area-line'),fill=svg.querySelector('.area-fill');
    const pts=[];
    for(let i=0;i<=30;i++){
      const x=i*30;
      const y=118-Math.sin((i+state.tick*.7)/3.3)*26-Math.sin((i+state.tick)/7)*18+(Math.random()-.5)*8;
      pts.push([x,Math.max(38,Math.min(188,y))]);
    }
    const d='M'+pts.map(p=>p[0].toFixed(0)+' '+p[1].toFixed(0)).join(' L');
    if(line)line.setAttribute('d',d);
    if(fill)fill.setAttribute('d',d+' L900 220 L0 220 Z');
  }

  const donut=view.querySelector('.donut');
  if(donut){
    const high=3+(state.tick%4),med=14+(state.tick%7),low=20+(state.tick%9);
    donut.style.background='conic-gradient(#e47b72 0 '+high+'%,#e6bd62 '+high+'% '+(high+med)+'%,#4cb2d4 '+(high+med)+'% '+(high+med+low)+'%,#72d4aa '+(high+med+low)+'% 100%)';
  }
}

function animateTelemetry(){
  const scene=document.querySelector('.pid-scene');
  if(!scene)return;
  scene.style.setProperty('--v6-flow-speed',(0.7+Math.abs(Math.sin(state.tick/9))*.7).toFixed(2)+'s');
  scene.style.setProperty('--v6-pulse-offset',String((state.tick*17)%100)+'%');
}

function scheduleLoop(fn,min,max){
  const run=()=>{
    fn();
    window.setTimeout(run,Math.round(min+Math.random()*(max-min)));
  };
  window.setTimeout(run,Math.round(min+Math.random()*(max-min)));
}
function animationPulse(){
  state.tick++;
  animateTelemetry();
}
function volumePulse(){ updateVolumeCounters(); }
function statisticsPulse(){ dynamicStatistics(); }

document.addEventListener('DOMContentLoaded',()=>{
  ensureLiveBadges();
  ensureProcessExtras();
  const initialIndex=(window.INTERAFAS_STATIONS||[]).findIndex(s=>s.id===currentStation());
  if(initialIndex>=0) window.INTERAFAS_LIVE?.setStation?.(initialIndex);
  observeStationChanges();
  updateVolumeCounters();
  dynamicStatistics();
  state.ready=true;

  scheduleLoop(animationPulse,650,1050);
  scheduleLoop(pushEvent,1350,2600);
  scheduleLoop(pushAlarm,3600,6800);
  scheduleLoop(volumePulse,1900,3300);
  scheduleLoop(statisticsPulse,2400,4700);
});

window.INTERAFAS_V6={
  get state(){return state;},
  applyCityProfile,
  pushAlarm,
  pushEvent
};
})();