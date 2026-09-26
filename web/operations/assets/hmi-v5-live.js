(()=>{
'use strict';

const stationMeta = window.INTERAFAS_STATIONS || [];
const live = {
  stationIndex: 0,
  tick: 0,
  logs: [],
  tags: {},
  history: {},
  alarms: new Map(),
  stats: {},
  incidentActive: false,
  incidentData: null,
  incidentStage: -1
};

const COLORS={ok:'ok',warn:'warn',alarm:'alarm',off:'off',service:'service'};
const now=()=>new Date();
const clamp=(v,min,max)=>Math.max(min,Math.min(max,v));
const fmtTime=d=>d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit'});

function makeTag(value,min,max,warnLow,warnHigh,unit,step,decimals=1){
  return {value,min,max,warnLow,warnHigh,unit,step,decimals};
}
function buildTags(){
  const s=stationMeta[live.stationIndex]||{};
  const code=(s.id||'EST-SL-01').split('-')[1]||'SL';
  live.tags={
    FLOW:makeTag(Number(s.supply||1130),Math.max(200,Number(s.supply||1130)*.86),Number(s.supply||1130)*1.12,Number(s.supply||1130)*.91,Number(s.supply||1130)*1.07,'L/s',Math.max(2,Number(s.supply||1130)*.006),0),
    PRESS:makeTag(Number(s.pressure||4.3),3.55,4.85,3.85,4.55,'bar',.035,2),
    LEVEL:makeTag(Number(s.reserve||80),52,97,65,91,'%',.32,1),
    MOTOR_A:makeTag(78,0,100,0,93,'%',1.4,0),
    MOTOR_B:makeTag(0,0,100,0,93,'%',1.0,0),
    CURRENT_A:makeTag(142,0,190,0,172,'A',1.8,0),
    CURRENT_B:makeTag(0,0,190,0,172,'A',1.2,0),
    CHLORINE:makeTag(.72,.35,1.25,.52,1.02,'mg/L',.012,2),
    TURBIDITY:makeTag(.34,.08,1.4,0,0.75,'NTU',.018,2),
    TEMP:makeTag(26.8,16,48,0,39,'°C',.09,1),
    LATENCY:makeTag(code==='SL'?48:27,12,110,0,55,'ms',2.6,0),
    V201:{value:1,type:'bool',unit:''},
    V202:{value:1,type:'bool',unit:''},
    P101:{value:1,type:'bool',unit:''},
    P102:{value:0,type:'bool',unit:''},
    MODE:{value:'AUTO',type:'state',unit:''}
  };
  Object.keys(live.tags).forEach(k=>{ if(!live.history[k]) live.history[k]=[]; });
}

function stateFor(t){
  if(!t)return COLORS.off;
  if(t.type==='bool') return t.value ? COLORS.ok : COLORS.off;
  if(t.type==='state') return t.value==='AUTO'?COLORS.ok:(t.value==='SERVICE'?COLORS.service:COLORS.warn);
  if(Number.isFinite(t.min) && (t.value<=t.min+1e-9 || t.value>=t.max-1e-9)) return COLORS.alarm;
  if(Number.isFinite(t.warnLow) && t.warnLow>t.min && t.value<t.warnLow) return COLORS.warn;
  if(Number.isFinite(t.warnHigh) && t.value>t.warnHigh) return COLORS.warn;
  return COLORS.ok;
}

function driftTag(t, bias=0){
  if(!t || t.type)return;
  const center=(t.min+t.max)/2;
  const restoring=(center-t.value)*0.015;
  const noise=(Math.random()-.5)*2*t.step;
  t.value=clamp(t.value+noise+restoring+bias,t.min,t.max);
}

function pushHistory(){
  Object.entries(live.tags).forEach(([k,t])=>{
    if(t.type)return;
    const h=live.history[k]||(live.history[k]=[]);
    h.push(t.value);
    if(h.length>240)h.shift();
  });
}

function pushLog(severity,source,message){
  const item={ts:now(),severity,source,message};
  live.logs.unshift(item);
  live.logs=live.logs.slice(0,120);
  renderLogs();
}

function maybeEvent(){
  const r=Math.random();
  if(r<.07) pushLog('INFO','HIST-01','Historian snapshot committed for metropolitan process tags.');
  else if(r<.105) pushLog('INFO','OT-AUTO-01','Demand-balancing cycle completed; no operator action required.');
  else if(r<.135) pushLog('WARN','RTU-SL-02','Communication latency exceeded the warning envelope.');
  else if(r<.16) pushLog('INFO','BRS-01','Recovery catalog heartbeat verified.');
  else if(r<.18) pushLog('INFO','EWS-01','Engineering baseline checksum observed.');
}

function detectAlarms(){
  Object.entries(live.tags).forEach(([name,t])=>{
    if(t.type)return;
    const state=stateFor(t);
    const prev=live.alarms.get(name)||'ok';
    if(state!==prev){
      const s=stationMeta[live.stationIndex]||{};
      if(state==='warn') pushLog('WARN',name,(s.id||'STATION')+' '+name+' entered warning range: '+formatTag(t));
      if(state==='alarm') pushLog('ALARM',name,(s.id||'STATION')+' '+name+' exceeded process limit: '+formatTag(t));
      if(prev!=='ok' && state==='ok') pushLog('INFO',name,(s.id||'STATION')+' '+name+' returned to normal operating range.');
      live.alarms.set(name,state);
    }
  });
}

function formatTag(t){
  if(!t)return '—';
  if(t.type==='bool')return t.value?'RUN/OPEN':'STOP/CLOSED';
  if(t.type==='state')return t.value;
  return Number(t.value).toFixed(t.decimals??1)+(t.unit?' '+t.unit:'');
}

function setStateClass(el,state){
  if(!el)return;
  el.classList.remove('state-ok','state-warn','state-alarm','state-off','state-service');
  el.classList.add('state-'+state);
}

function renderTags(){
  document.querySelectorAll('[data-live-tag]').forEach(el=>{
    const key=el.dataset.liveTag;
    const t=live.tags[key];
    if(!t)return;
    const text=el.dataset.liveFormat==='state' ? (t.type==='bool'?(t.value?(el.dataset.on||'RUN'):(el.dataset.off||'STOP')):formatTag(t)) : formatTag(t);
    if(el.textContent!==text){
      el.textContent=text;
      el.classList.remove('tag-pulse'); void el.offsetWidth; el.classList.add('tag-pulse');
    }
    const st=stateFor(t);
    setStateClass(el,st);
    const host=el.closest('.process-status-strip>div,.instrument-list>div');
    const pilot=host?.querySelector('.pilot');
    if(pilot){pilot.classList.remove('pilot-ok','pilot-warn','pilot-alarm','pilot-off','pilot-service');pilot.classList.add('pilot-'+st);}
  });
  document.querySelectorAll('[data-level-fill]').forEach(el=>{
    const t=live.tags[el.dataset.levelFill];
    if(t) el.style.height=clamp(t.value,0,100)+'%';
  });
  document.querySelectorAll('[data-pipe-state]').forEach(el=>{
    const key=el.dataset.pipeState;
    const t=live.tags[key];
    el.classList.toggle('flow-active',!!t?.value);
    el.classList.toggle('flow-off',!t?.value);
  });
  document.querySelectorAll('[data-pump-state]').forEach(el=>{
    const t=live.tags[el.dataset.pumpState];
    el.classList.toggle('running',!!t?.value);
    el.classList.toggle('stopped',!t?.value);
  });
  document.querySelectorAll('[data-valve-state]').forEach(el=>{
    const t=live.tags[el.dataset.valveState];
    el.classList.toggle('open',!!t?.value);
    el.classList.toggle('closed',!t?.value);
  });
}

function recalcStats(){
  const station=stationMeta[live.stationIndex]||{};
  const stationFlow=live.tags.FLOW?.value||0;
  const baseTotal=stationMeta.reduce((a,s)=>a+Number(s.supply||0),0)||8380;
  const baseStation=Number(station.supply||stationFlow)||stationFlow||1;
  const ratio=stationFlow/baseStation;
  const totalSupply=baseTotal*ratio;
  const demand=totalSupply*(.932+Math.sin(live.tick/23)*.006);
  const reserve=stationMeta.reduce((a,s)=>a+Number(s.reserve||0),0)/(stationMeta.length||1) + (live.tags.LEVEL.value-Number(station.reserve||live.tags.LEVEL.value))/(stationMeta.length||1);
  const pressure=(stationMeta.reduce((a,s)=>a+Number(s.pressure||0),0)/(stationMeta.length||1))+(live.tags.PRESS.value-Number(station.pressure||live.tags.PRESS.value))/(stationMeta.length||1);
  const active=Array.from(live.alarms.values()).filter(x=>x==='warn'||x==='alarm').length+6;
  const stats={supply:totalSupply,demand,reserve,pressure,active,energy:16.1+(totalSupply/baseTotal)*.75,latency:live.tags.LATENCY.value,availability:99.74+Math.sin(live.tick/37)*.08,runningPumps:31+(live.tags.P102.value?1:0)};
  live.stats=stats;
  document.querySelectorAll('[data-live-stat]').forEach(el=>{
    const key=el.dataset.liveStat;
    let value=stats[key];
    if(value===undefined)return;
    const decimals=Number(el.dataset.decimals||0);
    const suffix=el.dataset.suffix||'';
    const prefix=el.dataset.prefix||'';
    const text=prefix+Number(value).toFixed(decimals)+suffix;
    if(el.textContent!==text){el.textContent=text;el.classList.remove('tag-pulse');void el.offsetWidth;el.classList.add('tag-pulse');}
  });
  const ts=document.querySelectorAll('[data-live-updated]');
  ts.forEach(el=>el.textContent=fmtTime(now()));
}

function renderLogs(){
  const body=document.getElementById('live-log-body');
  if(body){
    body.innerHTML=live.logs.slice(0,30).map(l=>'<tr class="log-'+l.severity.toLowerCase()+'"><td>'+fmtTime(l.ts)+'</td><td><span class="sev-badge sev-'+l.severity.toLowerCase()+'">'+l.severity+'</span></td><td>'+l.source+'</td><td>'+l.message+'</td></tr>').join('');
  }
  const ticker=document.getElementById('live-event-ticker');
  if(ticker && live.logs[0]){
    ticker.innerHTML='<span class="sev-badge sev-'+live.logs[0].severity.toLowerCase()+'">'+live.logs[0].severity+'</span><b>'+live.logs[0].source+'</b><span>'+live.logs[0].message+'</span><time>'+fmtTime(live.logs[0].ts)+'</time>';
  }
}

function setCascadeClass(stage){
  document.body.classList.remove('cascade-active','cascade-stage-0','cascade-stage-1','cascade-stage-2','cascade-stage-3','cascade-stage-4','cascade-stage-5');
  if(stage<0)return;
  document.body.classList.add('cascade-active','cascade-stage-'+stage);
}

function renderIncidentStats(data){
  const active=Number(data.alarm_count||0);
  const availability=Number(data.availability??99.82);
  const critical=Number(data.stations_critical||0);

  live.stats.active=active;
  live.stats.availability=availability;

  document.querySelectorAll('[data-live-stat="active"]').forEach(el=>el.textContent=String(active));
  document.querySelectorAll('[data-live-stat="availability"]').forEach(el=>el.textContent=availability.toFixed(2));
  document.querySelectorAll('[data-live-updated]').forEach(el=>el.textContent=fmtTime(now()));

  const count=document.getElementById('cascade-alarm-count');
  const av=document.getElementById('cascade-availability');
  if(count)count.textContent=active.toLocaleString();
  if(av)av.textContent=availability.toFixed(2)+'%';

  const unack=Math.floor(active*.96);
  const criticalAlarms=Math.floor(active*Math.min(.68,.08+(Math.max(0,live.incidentStage)*.12)));
  const high=Math.max(1,Math.floor(active*.21));
  const medium=Math.max(3,Math.floor(active*.09));

  const setText=(id,value)=>{const el=document.getElementById(id);if(el)el.textContent=Number(value).toLocaleString();};
  setText('cascade-unack',unack);
  setText('cascade-critical-count',criticalAlarms);
  setText('cascade-filter-all',active);
  setText('cascade-filter-critical',criticalAlarms);
  setText('cascade-filter-high',high);
  setText('cascade-filter-medium',medium);
  setText('cascade-filter-unack',unack);

  document.querySelectorAll('[data-cascade-critical]').forEach(el=>el.textContent=String(critical));

  const body=document.getElementById('cascade-alarm-body');
  if(body && live.incidentData){
    const names=Array.isArray(live.incidentData.alarms)?live.incidentData.alarms:[];
    const stage=Number(live.incidentData.incident?.stage||0);
    const rows=names.slice(0,8).map((name,index)=>
      '<tr class="cascade-alarm-row">'+
        '<td>'+fmtTime(now())+'</td>'+
        '<td class="alarm-text">CRITICAL</td>'+
        '<td>METROPOLITAN</td>'+
        '<td>'+(['RTU-GW-07','PLC-SL-01','P-SL-101','V-SL-201'][index%4])+'</td>'+
        '<td>'+String(name).replaceAll('_',' ')+' · cascade stage '+stage+'/5</td>'+
        '<td>UNACK</td>'+
      '</tr>'
    ).join('');
    const existing=body.querySelectorAll('.cascade-alarm-row');
    existing.forEach(row=>row.remove());
    body.insertAdjacentHTML('afterbegin',rows);
  }
}

function applyTelemetry(data){
  const incident=data?.incident||{};
  const active=!!incident.active && String(incident.profile||'').toUpperCase()==='CASCADE';

  if(!active){
    if(live.incidentActive){
      live.incidentActive=false;
      live.incidentData=null;
      live.incidentStage=-1;
      setCascadeClass(-1);
      const banner=document.getElementById('cascade-banner');
      if(banner)banner.hidden=true;
      if(window.INTERAFAS_AUDIO)window.INTERAFAS_AUDIO.setStage(0,false);
    }
    return;
  }

  const stage=clamp(Number(incident.stage||0),0,5);
  live.incidentActive=true;
  if(window.INTERAFAS_AUDIO)window.INTERAFAS_AUDIO.setStage(stage,true);
  live.incidentData=data;
  setCascadeClass(stage);

  const banner=document.getElementById('cascade-banner');
  if(banner)banner.hidden=false;
  const stageLabel=document.getElementById('cascade-stage-label');
  if(stageLabel)stageLabel.textContent=String(incident.stage_label||'CASCADE');

  if(live.tags.FLOW)live.tags.FLOW.value=Number(data.flow||0);
  if(live.tags.PRESS)live.tags.PRESS.value=Number(data.pressure||0);
  if(live.tags.LEVEL)live.tags.LEVEL.value=Number(data.tank||0);
  if(live.tags.P101)live.tags.P101.value=String(data.p101||'').toUpperCase()==='ON'?1:0;
  if(live.tags.P102)live.tags.P102.value=String(data.p102||'').toUpperCase()==='ON'?1:0;
  if(live.tags.V201)live.tags.V201.value=String(data.v201||'').toUpperCase()==='CLOSED'?0:1;
  if(live.tags.MODE)live.tags.MODE.value=stage>=4?'EMERGENCY':'CASCADE';

  if(stage>=1){
    if(live.tags.MOTOR_A)live.tags.MOTOR_A.value=96+stage;
    if(live.tags.CURRENT_A)live.tags.CURRENT_A.value=174+stage*4;
    if(live.tags.LATENCY)live.tags.LATENCY.value=68+stage*11;
  }
  if(stage>=2){
    if(live.tags.CHLORINE)live.tags.CHLORINE.value=Math.max(.15,.48-stage*.05);
    if(live.tags.TURBIDITY)live.tags.TURBIDITY.value=.82+stage*.14;
    if(live.tags.TEMP)live.tags.TEMP.value=39+stage*1.7;
  }

  renderTags();

  if(stage!==live.incidentStage){
    live.incidentStage=stage;
    const sev=stage>=3?'ALARM':'WARN';
    pushLog(
      sev,
      'RTU-GW-07',
      'Firmware cascade stage '+stage+'/5 · '+String(incident.stage_label||'CASCADE')+
      ' · '+Number(data.alarm_count||0).toLocaleString()+' active alarms.'
    );
  }

  const flag=document.getElementById('vuln20-title-flag');
  if(flag && data.final_flag){
    flag.textContent='· '+String(data.final_flag);
    flag.hidden=false;
  }

  renderIncidentStats(data);
  pushHistory();
}

function hydraulicTick(){
  live.tick++;
  if(live.incidentActive){renderTags();pushHistory();return;}
  driftTag(live.tags.FLOW, Math.sin(live.tick/13)*.45);
  driftTag(live.tags.PRESS, Math.sin(live.tick/17)*.003);
  driftTag(live.tags.LEVEL, -.015 + Math.sin(live.tick/60)*.01);
  driftTag(live.tags.MOTOR_A, Math.sin(live.tick/9)*.15);
  driftTag(live.tags.CURRENT_A, Math.sin(live.tick/8)*.2);
  if(live.tick%47===0 && Math.random()<.28) live.tags.P102.value=live.tags.P102.value?0:1;
  pushHistory();
  detectAlarms();
  renderTags();
}
function qualityTick(){
  if(live.incidentActive){renderTags();return;}
  driftTag(live.tags.CHLORINE);
  driftTag(live.tags.TURBIDITY);
  driftTag(live.tags.TEMP);
  pushHistory();
  detectAlarms();
  renderTags();
}
function commsTick(){
  if(live.incidentActive){renderTags();return;}
  driftTag(live.tags.LATENCY, Math.sin(live.tick/11)*.4);
  detectAlarms();
  renderTags();
}
function statsTick(){
  if(live.incidentActive){renderIncidentStats(live.incidentData||{});}else{recalcStats();}
  if(document.querySelector('[data-view="trends"].active') && window.INTERAFAS_HMI?.renderTrends){
    window.INTERAFAS_HMI.renderTrends();
  }
}
function eventTick(){ if(!live.incidentActive)maybeEvent(); }
function jitterLoop(fn,min,max){
  const run=()=>{
    fn();
    window.setTimeout(run,Math.round(min+Math.random()*(max-min)));
  };
  window.setTimeout(run,Math.round(min+Math.random()*(max-min)));
}

function setStation(index){
  live.stationIndex=Number(index)||0;
  buildTags();
  const s=stationMeta[live.stationIndex]||{};
  document.querySelectorAll('[data-current-station]').forEach(el=>el.textContent=s.id||'—');
  document.querySelectorAll('[data-current-city]').forEach(el=>el.textContent=s.city||'—');
  document.querySelectorAll('[data-current-controller]').forEach(el=>el.textContent=s.controller||'—');
  document.querySelectorAll('[data-current-gateway]').forEach(el=>el.textContent=s.gateway||'—');
  document.querySelectorAll('[data-current-function]').forEach(el=>el.textContent=s.function||'—');
  pushLog('INFO',s.id||'STATION','Live process view selected: '+(s.name||s.city||'station')+'.');
  renderTags();recalcStats();
}

function seedLogs(){
  const seeds=[
    ['INFO','HMI-OPS-01','Live metropolitan telemetry session initialized.'],
    ['INFO','HIST-01','Historian ingestion stream synchronized.'],
    ['WARN','RTU-SL-02','Communication latency operating near warning threshold.'],
    ['INFO','PLC-CP-03','Reservoir auxiliary control heartbeat received.'],
    ['INFO','QCS-SO-01','Water quality sample accepted.'],
    ['INFO','OT-AUTO-01','Maintenance scheduler heartbeat completed.']
  ];
  seeds.forEach((x,i)=>live.logs.push({ts:new Date(Date.now()-i*37000),severity:x[0],source:x[1],message:x[2]}));
  renderLogs();
}

window.INTERAFAS_LIVE={setStation,pushLog,applyTelemetry,get tags(){return live.tags;},get history(){return live.history;},get stats(){return live.stats;}};
document.addEventListener('DOMContentLoaded',()=>{
  buildTags();
  seedLogs();
  renderTags();recalcStats();
  jitterLoop(hydraulicTick,720,1180);
  jitterLoop(qualityTick,1450,2450);
  jitterLoop(commsTick,2100,3900);
  jitterLoop(statsTick,1100,1850);
  jitterLoop(eventTick,2600,5200);
});
})();