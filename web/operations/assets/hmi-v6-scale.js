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


function processTemplates(){
  return {
    'Saint Louis':
      '<div class="city-process city-process-sl">'+
      '<div class="cp-section sl-storage"><span>RAW WATER STORAGE</span></div>'+
      '<div class="cp-section sl-lift"><span>HIGH-LIFT PUMPING</span></div>'+
      '<div class="cp-section sl-treatment"><span>TREATMENT / REGULATION</span></div>'+
      '<div class="cp-section sl-distribution"><span>METROPOLITAN DISTRIBUTION</span></div>'+
      '<div class="cp-tank sl-tank-a eq-click" data-equipment="TQ-SL-01"><b>TQ-SL-01</b><span>Primary reservoir</span><div class="cp-level"><i data-level-fill="LEVEL"></i></div><strong data-live-tag="LEVEL">—</strong></div>'+
      '<div class="cp-tank sl-tank-b eq-click" data-equipment="TQ-SL-02"><b>TQ-SL-02</b><span>Secondary reservoir</span><div class="cp-level"><i data-level-fill="LEVEL"></i></div><strong data-live-tag="LEVEL">—</strong></div>'+
      '<div class="cp-pump sl-p1 running eq-click" data-pump-state="P101" data-equipment="P-SL-101"><i></i><b>P-SL-101</b><span>HIGH-LIFT A</span><strong data-live-tag="P101" data-live-format="state" data-on="RUN" data-off="STOP">RUN</strong></div>'+
      '<div class="cp-pump sl-p2 running eq-click" data-pump-state="P101" data-equipment="P-SL-102"><i></i><b>P-SL-102</b><span>HIGH-LIFT B</span><strong>RUN</strong></div>'+
      '<div class="cp-pump sl-p3 running eq-click" data-pump-state="P101" data-equipment="P-SL-103"><i></i><b>P-SL-103</b><span>HIGH-LIFT C</span><strong>RUN</strong></div>'+
      '<div class="cp-pump sl-p4 eq-click" data-pump-state="P102" data-equipment="P-SL-104"><i></i><b>P-SL-104</b><span>STANDBY D</span><strong data-live-tag="P102" data-live-format="state" data-on="RUN" data-off="STBY">STBY</strong></div>'+
      '<div class="sl-suction-header eq-click" data-equipment="HDR-SL-SUC"><b>SUCTION MANIFOLD</b><span>2.18 bar</span></div>'+
      '<div class="sl-discharge-header eq-click" data-equipment="HDR-SL-DIS"><b>DISCHARGE MANIFOLD</b><span data-live-tag="PRESS">—</span></div>'+
      '<div class="sl-filter-bank eq-click" data-equipment="FLT-SL-01"><b>FILTER BANK</b><div><span>F-101</span><i class="ok"></i><small>ΔP 0.18</small></div><div><span>F-102</span><i class="ok"></i><small>ΔP 0.20</small></div><div><span>F-103</span><i class="warn"></i><small>ΔP 0.65</small></div></div>'+
      '<div class="cp-skid sl-qcs eq-click" data-equipment="QCS-SL-01"><b>QCS-SL-01</b><span>Final water quality</span><div><small>CL₂</small><strong data-live-tag="CHLORINE">—</strong></div><div><small>NTU</small><strong data-live-tag="TURBIDITY">—</strong></div></div>'+
      '<div class="sl-chem eq-click" data-equipment="CHEM-SL-01"><b>NaOCl</b><span>DOSING</span><strong>AUTO</strong></div>'+
      '<div class="cp-header sl-header eq-click" data-equipment="HDR-SL-01"><b>HDR-SL-01</b><span>Main metropolitan header</span><strong data-live-tag="PRESS">—</strong></div>'+
      '<div class="cp-meter sl-flow"><b>FT-SL-101</b><strong data-live-tag="FLOW">—</strong></div>'+
      '<div class="cp-zone sl-zone-a"><b>ZONE A</b><span>Central / commercial</span><small>FT-SL-501 · 1.28 m³/s</small><em>PT-SL-501 · 4.8 bar</em></div>'+
      '<div class="cp-zone sl-zone-b"><b>ZONE B</b><span>Residential / university</span><small>FT-SL-502 · 1.15 m³/s</small><em>PT-SL-502 · 4.6 bar</em></div>'+
      '<div class="cp-zone sl-zone-c"><b>ZONE C</b><span>Industrial / airport</span><small>FT-SL-503 · 0.93 m³/s</small><em>PT-SL-503 · 4.4 bar</em></div>'+
      '<div class="scada-tag sl-tag-flow"><small>FIT-SL-101 · RAW WATER</small><b data-live-tag="FLOW">—</b><em>VALID · AUTO</em></div>'+
      '<div class="scada-tag sl-tag-p1"><small>PT-SL-201 · SUCTION</small><b>2.18 bar</b><em>NORMAL</em></div>'+
      '<div class="scada-tag sl-tag-q"><small>AIT-SL-301 · FINAL QUALITY</small><b data-live-tag="TURBIDITY">—</b><em>QUALITY OK</em></div>'+
      '<div class="scada-status-bank sl-status"><div><i></i><span>PLC-SL-01</span><b>RUN</b></div><div><i></i><span>RTU-SL-01</span><b>ONLINE</b></div><div><i></i><span>QCS-SL-01</span><b>VALID</b></div><div><i class="warn"></i><span>P-SL-104</span><b>STBY</b></div></div>'+
      '<div class="scada-valve sl-v-a open"><i></i><span>XV-SL-501</span></div><div class="scada-valve sl-v-b open"><i></i><span>XV-SL-502</span></div><div class="scada-valve sl-v-c open"><i></i><span>XV-SL-503</span></div>'+
      '<div class="scada-valve sl-v-suc-a open"><i></i><span>XV-SL-101</span></div><div class="scada-valve sl-v-suc-b open"><i></i><span>XV-SL-102</span></div>'+
      '<div class="sl-check sl-cv1"><i></i><span>CV-201</span></div><div class="sl-check sl-cv2"><i></i><span>CV-202</span></div><div class="sl-check sl-cv3"><i></i><span>CV-203</span></div><div class="sl-check sl-cv4"><i></i><span>CV-204</span></div>'+
      '<div class="sl-isolation sl-iso-in"><i></i><span>XV-SL-110</span></div><div class="sl-isolation sl-iso-out"><i></i><span>XV-SL-410</span></div>'+
      '<div class="sl-bypass"><b>MAINTENANCE BYPASS</b><span>XV-SL-420 · CLOSED</span></div>'+
      '<div class="sl-main-header-label"><small>METROPOLITAN HEADER</small><b>HDR-SL-01</b><strong data-live-tag="PRESS">—</strong></div>'+
      '<div class="scada-motor-state sl-mcc">MCC-SL-01 · BUS HEALTHY · AUTO · 3 RUN / 1 STBY</div>'+
      '<div class="cp-route sl-r1 active"></div><div class="cp-route sl-r2 active"></div><div class="cp-route sl-r3 active"></div><div class="cp-route sl-r4 active"></div><div class="cp-route sl-r5 active"></div><div class="cp-route sl-r6 active"></div><div class="cp-route sl-branch-a active"></div><div class="cp-route sl-branch-b active"></div><div class="cp-route sl-branch-c active"></div>'+
      '</div>',

    'Soledade':
      '<div class="city-process city-process-so">'+
      '<div class="cp-section so-intake"><span>INTAKE / BOOSTER</span></div>'+
      '<div class="cp-section so-control"><span>PRESSURE CONTROL</span></div>'+
      '<div class="cp-section so-storage"><span>BREAK-PRESSURE STORAGE</span></div>'+
      '<div class="cp-section so-service"><span>EAST / SOUTH SERVICE</span></div>'+
      '<div class="cp-source so-source"><b>INTAKE SO-01</b><span>Eastern feeder</span><strong data-live-tag="FLOW">—</strong></div>'+
      '<div class="cp-pump so-p1 running eq-click" data-pump-state="P101" data-equipment="P-SO-101"><i></i><b>P-SO-101</b><span>BOOSTER A</span><strong data-live-tag="P101" data-live-format="state" data-on="RUN" data-off="STOP">RUN</strong></div>'+
      '<div class="cp-pump so-p2 running eq-click" data-pump-state="P101" data-equipment="P-SO-102"><i></i><b>P-SO-102</b><span>BOOSTER B</span><strong>RUN</strong></div>'+
      '<div class="cp-pump so-p3 eq-click" data-pump-state="P102" data-equipment="P-SO-103"><i></i><b>P-SO-103</b><span>STANDBY C</span><strong data-live-tag="P102" data-live-format="state" data-on="RUN" data-off="STBY">STBY</strong></div>'+
      '<div class="cp-control so-prv eq-click" data-equipment="PRV-SO-01"><b>PRV-SO-01</b><span>Pressure reducing station</span><strong data-live-tag="PRESS">—</strong></div>'+
      '<div class="cp-tank so-break eq-click" data-equipment="BRK-SO-01"><b>BRK-SO-01</b><span>Break-pressure tank</span><div class="cp-level"><i data-level-fill="LEVEL"></i></div><strong data-live-tag="LEVEL">—</strong></div>'+
      '<div class="cp-skid so-qcs eq-click" data-equipment="QCS-SO-01"><b>QCS-SO-01</b><span>Distribution quality</span><div><small>CL₂</small><strong data-live-tag="CHLORINE">—</strong></div><div><small>NTU</small><strong data-live-tag="TURBIDITY">—</strong></div></div>'+
      '<div class="cp-zone so-zone-n"><b>NORTH / INDUSTRIAL</b><span>Primary service sector</span></div>'+
      '<div class="cp-zone so-zone-s"><b>SOUTH / RESIDENTIAL</b><span>Secondary service sector</span></div>'+
      '<div class="scada-tag so-tag-flow"><small>FIT-SO-101 · INTAKE</small><b data-live-tag="FLOW">—</b><em>VALID</em></div>'+
      '<div class="scada-tag so-tag-up"><small>PT-SO-201 · PRV UPSTREAM</small><b>8.50 bar</b><em>HIGH SIDE</em></div>'+
      '<div class="scada-tag so-tag-down"><small>PT-SO-202 · DOWNSTREAM</small><b data-live-tag="PRESS">—</b><em>CONTROLLED</em></div>'+
      '<div class="scada-status-bank so-status"><div><i></i><span>PLC-SO-01</span><b>RUN</b></div><div><i></i><span>RTU-SO-01</span><b>ONLINE</b></div><div><i></i><span>PRV-SO-01</span><b>AUTO</b></div><div><i class="warn"></i><span>P-SO-103</span><b>LOCAL</b></div></div>'+
      '<div class="scada-valve so-v-n open"><i></i><span>XV-SO-401</span></div><div class="scada-valve so-v-s open"><i></i><span>XV-SO-402</span></div>'+
      '<div class="scada-motor-state so-mcc">MCC-SO-01 · 2 RUN / 1 LOCAL</div>'+
      '<div class="cp-route so-r1 active"></div><div class="cp-route so-r2 active"></div><div class="cp-route so-r3 active"></div><div class="cp-route so-up active"></div><div class="cp-route so-down active"></div>'+
      '</div>',

    'Cerro de San Pablo':
      '<div class="city-process city-process-cp">'+
      '<div class="cp-section cp-storage"><span>ELEVATED STORAGE</span></div>'+
      '<div class="cp-section cp-booster"><span>HILLSIDE BOOSTER</span></div>'+
      '<div class="cp-section cp-disinfection"><span>DISINFECTION</span></div>'+
      '<div class="cp-section cp-highzone"><span>HIGH-ZONE FEED</span></div>'+
      '<div class="cp-tower eq-click" data-equipment="TQ-CP-02"><div class="tower-bowl"><i data-level-fill="LEVEL"></i></div><div class="tower-legs"></div><b>TQ-CP-02</b><span>Elevated balance tank</span><strong data-live-tag="LEVEL">—</strong></div>'+
      '<div class="cp-pump cp-p1 running eq-click" data-pump-state="P101" data-equipment="P-CP-101"><i></i><b>P-CP-101</b><span>BOOSTER DUTY</span><strong data-live-tag="P101" data-live-format="state" data-on="RUN" data-off="STOP">RUN</strong></div>'+
      '<div class="cp-pump cp-p2 eq-click" data-pump-state="P102" data-equipment="P-CP-102"><i></i><b>P-CP-102</b><span>BOOSTER STANDBY</span><strong data-live-tag="P102" data-live-format="state" data-on="RUN" data-off="STBY">STBY</strong></div>'+
      '<div class="cp-skid cp-chlor eq-click" data-equipment="CL-CP-01"><b>CL-CP-01</b><span>Compact chlorination</span><div><small>CL₂</small><strong data-live-tag="CHLORINE">—</strong></div></div>'+
      '<div class="cp-meter cp-pressure"><b>PT-CP-201</b><strong data-live-tag="PRESS">—</strong></div>'+
      '<div class="cp-zone cp-zone-main"><b>HIGH ZONE</b><span>Single municipal feed</span><strong data-live-tag="FLOW">—</strong></div>'+
      '<div class="cp-overflow"><b>OVERFLOW / DRAIN</b><span>Gravity safety path</span></div>'+
      '<div class="cp-route cp-r1 active"></div><div class="cp-route cp-r2 active"></div><div class="cp-route cp-r3 active"></div><div class="cp-route cp-gravity"></div>'+
      '</div>'
  };
}

function renderCityProcess(city){
  const scene=document.querySelector('.view[data-view="process"] .pid-scene');
  if(!scene)return;
  const templates=processTemplates();
  const p=cityProfiles[city]||cityProfiles['Saint Louis'];
  state.city=cityProfiles[city]?city:'Saint Louis';
  scene.className='pid-scene '+p.cls;
  scene.dataset.city=p.code;
  scene.innerHTML=templates[state.city];

  const title=document.querySelector('.pid-titlebar strong');
  const subtitle=document.querySelector('.pid-titlebar span');
  if(title)title.textContent=p.label;
  if(subtitle)subtitle.textContent=p.scale+' PROCESS · '+p.assets+' observed assets · '+p.pumps+' pump trains · '+p.branches+' distribution branches';

  const idx=(window.INTERAFAS_STATIONS||[]).findIndex(s=>s.id===currentStation());
  if(idx>=0) window.INTERAFAS_LIVE?.setStation?.(idx);
}

function overviewDefaults(city){
  if(city==='Saint Louis')return {FLOW:'3.42 m³/s',PRESS:'5.20 bar',LEVEL:'68 %',CHLORINE:'1.05 mg/L',TURBIDITY:'0.34 NTU',P101:'RUN',P102:'STBY'};
  if(city==='Soledade')return {FLOW:'1.28 m³/s',PRESS:'4.80 bar',LEVEL:'62 %',CHLORINE:'1.02 mg/L',TURBIDITY:'0.32 NTU',P101:'RUN',P102:'LOCAL'};
  return {FLOW:'2,380 L/s',PRESS:'4.40 bar',LEVEL:'78 %',CHLORINE:'0.72 mg/L',TURBIDITY:'0.28 NTU',P101:'RUN',P102:'STBY'};
}
function renderProcessOverviews(){
  const templates=processTemplates();
  document.querySelectorAll('[data-process-overview]').forEach(scene=>{
    const city=scene.dataset.processOverview;
    const p=cityProfiles[city]||cityProfiles['Saint Louis'];
    scene.className='pid-scene overview-process-scene '+p.cls;
    scene.dataset.city=p.code;
    scene.innerHTML=templates[city]||templates['Saint Louis'];

    const vals=overviewDefaults(city);
    scene.querySelectorAll('[data-live-tag]').forEach(el=>{
      const key=el.dataset.liveTag;
      if(vals[key]!==undefined)el.textContent=vals[key];
      el.removeAttribute('data-live-tag');
      el.removeAttribute('data-live-format');
      el.removeAttribute('data-on');
      el.removeAttribute('data-off');
    });
    scene.querySelectorAll('[data-level-fill]').forEach(el=>{
      const n=parseFloat(vals.LEVEL)||65;
      el.style.height=Math.max(8,Math.min(94,n))+'%';
      el.removeAttribute('data-level-fill');
    });
    scene.querySelectorAll('[data-pump-state],[data-pipe-state]').forEach(el=>{
      el.removeAttribute('data-pump-state');
      el.removeAttribute('data-pipe-state');
    });
    scene.querySelectorAll('.eq-click').forEach(el=>{
      el.classList.remove('eq-click');
      el.removeAttribute('data-equipment');
    });
  });
}

function applyCityProfile(city){
  renderCityProcess(city);
}

function observeStationChanges(){
  const city=document.querySelector('[data-current-city]');
  if(!city)return;
  let last='';
  const sync=()=>{
    const next=city.textContent.trim();
    if(!next || next===last)return;
    last=next;
    applyCityProfile(next);
  };
  new MutationObserver(sync).observe(city,{childList:true,characterData:true,subtree:true});
  sync();
}

document.addEventListener('click',e=>{
  const el=e.target.closest('.pid-scene .eq-click');
  if(!el)return;
  const name=el.dataset.equipment||'Process asset';
  window.INTERAFAS_HMI?.openDrawer?.(name,'LIVE PROCESS ASSET',
    '<div class="drawer-kpi">'+name+'</div><p>Live asset context for '+state.city+'.</p><div class="drawer-meta"><span>Station</span><b>'+currentStation()+'</b><span>Municipality</span><b>'+state.city+'</b><span>State</span><b class="ok-text">ONLINE</b><span>Telemetry</span><b>LIVE</b></div>');
});

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
  const scene=document.querySelector('.view[data-view="process"] .pid-scene');
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
  const initialIndex=(window.INTERAFAS_STATIONS||[]).findIndex(s=>s.id===currentStation());
  if(initialIndex>=0) window.INTERAFAS_LIVE?.setStation?.(initialIndex);
  observeStationChanges();
  renderProcessOverviews();
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