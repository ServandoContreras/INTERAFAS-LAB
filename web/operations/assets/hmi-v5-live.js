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

const DANGER_MESSAGES=[
  ['PELIGRO','ES-MX','FALLO CRÍTICO DEL SISTEMA DE CONTROL'],
  ['ALERTA','ES-ES','FALLO CRÍTICO DE CONTROL'],
  ['PELIGRO','ES-AR','FALLA CRÍTICA DEL SISTEMA'],
  ['DANGER','EN-US','CRITICAL CONTROL FAILURE'],
  ['WARNING','EN-GB','CRITICAL CONTROL FAILURE'],
  ['DANGER','FR-FR','DÉFAILLANCE CRITIQUE DU CONTRÔLE'],
  ['DANGER','FR-CA','DÉFAILLANCE CRITIQUE DU CONTRÔLE'],
  ['PERIGO','PT-BR','FALHA CRÍTICA DE CONTROLE'],
  ['PERIGO','PT-PT','FALHA CRÍTICA DE CONTROLO'],
  ['GEFAHR','DE','KRITISCHER STEUERUNGSFEHLER'],
  ['PERICOLO','IT','GUASTO CRITICO DEL CONTROLLO'],
  ['GEVAAR','NL','KRITIEKE BESTURINGSFOUT'],
  ['ОПАСНОСТЬ','RU','КРИТИЧЕСКИЙ СБОЙ УПРАВЛЕНИЯ'],
  ['НЕБЕЗПЕКА','UK','КРИТИЧНИЙ ЗБІЙ КЕРУВАННЯ'],
  ['ОПАСНОСТ','BG','КРИТИЧНА ПОВРЕДА В УПРАВЛЕНИЕТО'],
  ['ОПАСНОСТ','MK','КРИТИЧЕН ДЕФЕКТ НА КОНТРОЛАТА'],
  ['OPASNOST','SR-LATN','KRITIČNI KVAR UPRAVLJANJA'],
  ['ОПАСНОСТ','SR-CYRL','КРИТИЧНИ КВАР УПРАВЉАЊА'],
  ['OPASNOST','HR','KRITIČNI KVAR UPRAVLJANJA'],
  ['OPASNOST','BS','KRITIČNI KVAR UPRAVLJANJA'],
  ['NEVARNOST','SL','KRITIČNA NAPAKA KRMILJENJA'],
  ['NIEBEZPIECZEŃSTWO','PL','KRYTYCZNA AWARIA STEROWANIA'],
  ['NEBEZPEČÍ','CS','KRITICKÁ PORUCHA ŘÍZENÍ'],
  ['NEBEZPEČENSTVO','SK','KRITICKÉ ZLYHANIE RIADENIA'],
  ['VESZÉLY','HU','KRITIKUS VEZÉRLÉSI HIBA'],
  ['PERICOL','RO','DEFECȚIUNE CRITICĂ DE CONTROL'],
  ['ΚΙΝΔΥΝΟΣ','EL','ΚΡΙΣΙΜΗ ΒΛΑΒΗ ΕΛΕΓΧΟΥ'],
  ['TEHLİKE','TR','KRİTİK KONTROL ARIZASI'],
  ['FARA','SV','KRITISKT STYRFEL'],
  ['FARE','NO','KRITISK KONTROLLFEIL'],
  ['FARE','DA','KRITISK STYRINGSFEJL'],
  ['VAARA','FI','KRIITTINEN OHJAUSVIKA'],
  ['OHT','ET','KRIITILINE JUHTIMISRIKE'],
  ['BRIESMAS','LV','KRITISKA VADĪBAS KĻŪME'],
  ['PAVOJUS','LT','KRITINIS VALDYMO GEDIMAS'],
  ['PERILL','CA','FALLADA CRÍTICA DE CONTROL'],
  ['PERIGO','GL','FALLO CRÍTICO DE CONTROL'],
  ['ARRISKUA','EU','KONTROL-SISTEMAREN HUTSEGITE KRITIKOA'],
  ['PERYGL','CY','METHIANT RHEOLI CRITIGOL'],
  ['CONTÚIRT','GA','TEIP CHRITICIÚIL RIALAITHE'],
  ['خطر','AR','عطل حرج في نظام التحكم'],
  ['خطر','FA','خرابی بحرانی سامانه کنترل'],
  ['סכנה','HE','כשל קריטי במערכת הבקרה'],
  ['خطرہ','UR','کنٹرول سسٹم میں سنگین خرابی'],
  ['खतरा','HI','नियंत्रण प्रणाली में गंभीर विफलता'],
  ['বিপদ','BN','নিয়ন্ত্রণ ব্যবস্থায় গুরুতর ত্রুটি'],
  ['ਖ਼ਤਰਾ','PA','ਕੰਟਰੋਲ ਸਿਸਟਮ ਵਿੱਚ ਗੰਭੀਰ ਖਰਾਬੀ'],
  ['જોખમ','GU','નિયંત્રણ પ્રણાલીમાં ગંભીર ખામી'],
  ['धोका','MR','नियंत्रण प्रणालीमध्ये गंभीर बिघाड'],
  ['அபாயம்','TA','கட்டுப்பாட்டு அமைப்பில் கடுமையான கோளாறு'],
  ['ప్రమాదం','TE','నియంత్రణ వ్యవస్థలో తీవ్రమైన వైఫల్యం'],
  ['ಅಪಾಯ','KN','ನಿಯಂತ್ರಣ ವ್ಯವಸ್ಥೆಯಲ್ಲಿ ಗಂಭೀರ ವೈಫಲ್ಯ'],
  ['അപകടം','ML','നിയന്ത്രണ സംവിധാനത്തിൽ ഗുരുതര തകരാർ'],
  ['खतरा','NE','नियन्त्रण प्रणालीमा गम्भीर विफलता'],
  ['අනතුර','SI','පාලන පද්ධතියේ බරපතල දෝෂයක්'],
  ['危险','ZH-CN','检测到关键控制故障'],
  ['危險','ZH-TW','偵測到關鍵控制故障'],
  ['危險','YUE-HK','偵測到嚴重控制故障'],
  ['危険','JA','重大な制御異常を検出'],
  ['위험','KO','중대한 제어 장애 감지'],
  ['NGUY HIỂM','VI','PHÁT HIỆN LỖI ĐIỀU KHIỂN NGHIÊM TRỌNG'],
  ['อันตราย','TH','ตรวจพบความล้มเหลวของระบบควบคุมขั้นวิกฤต'],
  ['BAHAYA','ID','KEGAGALAN KONTROL KRITIS TERDETEKSI'],
  ['BAHAYA','MS','KEGAGALAN KAWALAN KRITIK DIKESAN'],
  ['PANGANIB','FIL','KRITIKAL NA PAGKABIGO NG KONTROL'],
  ['HATARI','SW','HITILAFU KUBWA YA UDHIBITI'],
  ['GEVAAR','AF','KRITIEKE BEHEERFOUT'],
  ['INGOZI','ZU','UKWEHLULEKA OKUBUCAYI KOKULAWULA'],
  ['INGOZI','XH','UKUSILELA OKUBALULEKILEYO KOLAWULO'],
  ['አደጋ','AM','የቁጥጥር ስርዓት ከባድ ብልሽት'],
  ['HADARI','HA','MATSANANCIN GAZAWAR SARRAFAWA'],
  ['EWU','YO','IKUNA PATAKI NINU ETO IṢAKOSO'],
  ['DANĜERO','EO','KRITIKA REGADA FIASKO'],
  ['PERICULUM','LA','DEFECTUS MODERATIONIS GRAVIS']
]
let dangerToastTimer=null;
let dangerToastIndex=0;
let dangerToastStage=-1;
let dangerNotifyHost=null;
let dangerNotifyStack=null;

function ensureDangerStack(){
  if(dangerNotifyStack?.isConnected)return dangerNotifyStack;

  dangerNotifyHost=document.createElement('div');
  dangerNotifyHost.id='cascade-notify-portal';
  dangerNotifyHost.style.setProperty('all','initial','important');
  dangerNotifyHost.style.setProperty('position','fixed','important');
  dangerNotifyHost.style.setProperty('top','86px','important');
  dangerNotifyHost.style.setProperty('right','18px','important');
  dangerNotifyHost.style.setProperty('width','360px','important');
  dangerNotifyHost.style.setProperty('max-width','calc(100vw - 36px)','important');
  dangerNotifyHost.style.setProperty('height','246px','important');
  dangerNotifyHost.style.setProperty('z-index','2147483647','important');
  dangerNotifyHost.style.setProperty('pointer-events','none','important');
  dangerNotifyHost.style.setProperty('overflow','visible','important');

  const shadow=dangerNotifyHost.attachShadow({mode:'open'});
  shadow.innerHTML=`
    <style>
      :host{all:initial}
      #stack{
        position:relative;
        width:100%;
        height:246px;
        overflow:visible;
        pointer-events:none;
        font-family:Inter,Segoe UI,Arial,sans-serif;
      }
      .toast{
        position:absolute;
        top:0;
        right:0;
        width:360px;
        max-width:100%;
        box-sizing:border-box;
        display:grid;
        grid-template-columns:38px minmax(0,1fr);
        gap:10px;
        align-items:center;
        min-height:66px;
        padding:10px 12px 10px 10px;
        border:1px solid #ff6068;
        border-left:4px solid #ff2633;
        border-radius:7px;
        background:linear-gradient(90deg,rgba(165,10,18,.98),rgba(77,5,10,.98));
        color:#fff;
        box-shadow:0 12px 32px rgba(0,0,0,.50),0 0 16px rgba(255,31,45,.22);
        opacity:0;
        transform:translate3d(112%,calc(var(--slot,0) * 78px),0) scale(.985);
        transition:transform .24s cubic-bezier(.2,.8,.2,1),opacity .20s ease;
        pointer-events:none;
      }
      .toast.show{
        opacity:1;
        transform:translate3d(0,calc(var(--slot,0) * 78px),0) scale(1);
      }
      .toast.leaving{
        opacity:0;
        transform:translate3d(116%,calc(var(--slot,0) * 78px),0) scale(.98);
      }
      .icon{
        width:32px;
        height:32px;
        display:grid;
        place-items:center;
        border:2px solid #fff;
        border-radius:50%;
        font:900 21px/1 ui-monospace,SFMono-Regular,Consolas,monospace;
      }
      .copy{min-width:0}
      .head{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:8px;
        margin-bottom:4px;
      }
      .head strong{
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
        font-size:12px;
        letter-spacing:.07em;
      }
      .head span{
        flex:0 0 auto;
        color:#ffc9cc;
        font:800 8px ui-monospace,SFMono-Regular,Consolas,monospace;
        letter-spacing:.04em;
      }
      p{
        margin:0;
        color:#ffe7e9;
        font-size:10px;
        line-height:1.3;
        overflow:hidden;
        display:-webkit-box;
        -webkit-line-clamp:2;
        -webkit-box-orient:vertical;
      }
    </style>
    <div id="stack" aria-live="assertive" aria-atomic="false"></div>
  `;

  dangerNotifyStack=shadow.getElementById('stack');
  document.documentElement.appendChild(dangerNotifyHost);
  return dangerNotifyStack;
}

function layoutDangerToasts(){
  if(!dangerNotifyStack)return;
  [...dangerNotifyStack.children].forEach((node,index)=>{
    node.style.setProperty('--slot',String(index));
  });
}

function removeDangerToast(toast){
  if(!toast?.isConnected)return;
  toast.classList.remove('show');
  toast.classList.add('leaving');
  setTimeout(()=>{
    toast.remove();
    layoutDangerToasts();
  },250);
}

function pushDangerToast(stage){
  const stack=ensureDangerStack();
  const item=DANGER_MESSAGES[dangerToastIndex%DANGER_MESSAGES.length];
  dangerToastIndex++;

  const toast=document.createElement('div');
  toast.className='toast';
  toast.style.setProperty('--slot','0');
  toast.innerHTML=
    '<div class="icon">!</div>'+
    '<div class="copy">'+
      '<div class="head"><strong>'+item[0]+'</strong><span>'+item[1]+' · STAGE '+stage+'/5</span></div>'+
      '<p>'+item[2]+'</p>'+
    '</div>';

  stack.prepend(toast);

  while(stack.children.length>3){
    stack.lastElementChild?.remove();
  }

  layoutDangerToasts();
  requestAnimationFrame(()=>requestAnimationFrame(()=>toast.classList.add('show')));
  setTimeout(()=>removeDangerToast(toast),2200);
}

function toastIntervalFor(stage){
  return [1250,1120,1000,900,820,740][Math.max(0,Math.min(5,stage))];
}

function stopDangerNotifications(){
  if(dangerToastTimer){
    clearTimeout(dangerToastTimer);
    dangerToastTimer=null;
  }
  dangerToastStage=-1;

  if(dangerNotifyStack)dangerNotifyStack.replaceChildren();
  if(dangerNotifyHost){
    dangerNotifyHost.remove();
    dangerNotifyHost=null;
    dangerNotifyStack=null;
  }
}

function startDangerNotifications(stage){
  const normalized=Math.max(0,Math.min(5,Number(stage)||0));
  if(dangerToastTimer && dangerToastStage===normalized)return;

  if(dangerToastTimer){
    clearTimeout(dangerToastTimer);
    dangerToastTimer=null;
  }

  dangerToastStage=normalized;

  const run=()=>{
    if(!live.incidentActive)return;
    pushDangerToast(dangerToastStage);
    dangerToastTimer=setTimeout(run,toastIntervalFor(dangerToastStage));
  };

  run();
}

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

let vuln20SnapshotInFlight=false;

function markSnapshotComplete(){
  sessionStorage.setItem('INTERAFAS_V20_SNAPSHOT_SENT','1');
  pushLog(
    'INFO',
    'EVIDENCE-01',
    'Personalized HMI snapshot preserved and released to Pulso Metropolitano.'
  );
}

function evidenceIdentity(data){
  return {
    name:String(data?.auditor?.name||'Auditor'),
    matricula:String(data?.auditor?.matricula||'—')
  };
}

function drawEvidenceStamp(ctx,width,height,data){
  const identity=evidenceIdentity(data);
  const capturedAt=new Intl.DateTimeFormat('es-MX',{
    year:'numeric',month:'2-digit',day:'2-digit',
    hour:'2-digit',minute:'2-digit',second:'2-digit',
    hour12:false
  }).format(new Date());

  const pad=Math.max(14,Math.round(width*.012));
  const boxH=Math.max(92,Math.round(height*.105));
  const boxW=Math.min(width-pad*2,Math.max(610,Math.round(width*.66)));
  const x=pad;
  const y=height-boxH-pad;

  ctx.save();
  ctx.fillStyle='rgba(7,12,18,.90)';
  ctx.strokeStyle='rgba(255,77,84,.95)';
  ctx.lineWidth=Math.max(2,Math.round(width/1100));
  ctx.fillRect(x,y,boxW,boxH);
  ctx.strokeRect(x,y,boxW,boxH);

  const titleSize=Math.max(17,Math.round(width/88));
  const textSize=Math.max(13,Math.round(width/118));

  ctx.fillStyle='#ff666d';
  ctx.font='800 '+titleSize+'px Arial, sans-serif';
  ctx.fillText('INTERAFAS · EVIDENCIA OPERACIONAL · VULN-20',x+16,y+27);

  ctx.fillStyle='#ffffff';
  ctx.font='700 '+textSize+'px Arial, sans-serif';
  ctx.fillText('AUDITOR: '+identity.name,x+16,y+52);
  ctx.fillText('MATRÍCULA: '+identity.matricula,x+16,y+75);

  ctx.fillStyle='#c9d3da';
  ctx.textAlign='right';
  ctx.fillText('CATASTROPHIC STATE',x+boxW-16,y+52);
  ctx.fillText(capturedAt,x+boxW-16,y+75);
  ctx.restore();
}

function replaceCanvasCopies(sourceRoot,cloneRoot){
  const sourceCanvases=[...sourceRoot.querySelectorAll('canvas')];
  const cloneCanvases=[...cloneRoot.querySelectorAll('canvas')];

  cloneCanvases.forEach((copy,index)=>{
    const original=sourceCanvases[index];
    if(!original)return;

    try{
      const img=document.createElement('img');
      img.src=original.toDataURL('image/png');
      img.width=original.clientWidth||original.width;
      img.height=original.clientHeight||original.height;
      img.style.width=(original.clientWidth||original.width)+'px';
      img.style.height=(original.clientHeight||original.height)+'px';
      img.style.display='block';
      copy.replaceWith(img);
    }catch(e){
      copy.remove();
    }
  });
}

function sanitizeSnapshotClone(clone){
  clone.querySelectorAll('script,iframe,video,audio').forEach(node=>node.remove());

  // Never publish the challenge flag inside the news snapshot.
  const flag=clone.querySelector('#vuln20-title-flag');
  if(flag){
    flag.textContent='· CRITICAL INCIDENT';
    flag.removeAttribute('hidden');
  }

  // External images are not required for the SCADA evidence and can taint canvas export.
  clone.querySelectorAll('img').forEach(img=>{
    if(!String(img.src||'').startsWith('data:')){
      const ph=document.createElement('div');
      ph.style.width=(img.clientWidth||160)+'px';
      ph.style.height=(img.clientHeight||90)+'px';
      ph.style.background='#101c24';
      ph.style.border='1px solid #39434a';
      img.replaceWith(ph);
    }
  });
}

async function renderViewportSnapshot(data){
  const width=Math.max(1024,window.innerWidth||1280);
  const height=Math.max(620,Math.min(window.innerHeight||720,900));

  const bodyClone=document.body.cloneNode(true);
  replaceCanvasCopies(document.body,bodyClone);
  sanitizeSnapshotClone(bodyClone);

  const cssResponse=await fetch('/operations/assets/hmi.css?v=snapshot',{cache:'no-store'});
  const cssText=await cssResponse.text();

  bodyClone.setAttribute('xmlns','http://www.w3.org/1999/xhtml');
  bodyClone.style.width=width+'px';
  bodyClone.style.height=height+'px';
  bodyClone.style.overflow='hidden';
  bodyClone.style.margin='0';
  bodyClone.style.background='#260606';

  const style=document.createElement('style');
  style.textContent=cssText+
    '\nhtml,body{width:'+width+'px!important;height:'+height+'px!important;overflow:hidden!important;margin:0!important;}'+
    '\n.cascade-notify-stack{top:88px!important;}';
  bodyClone.prepend(style);

  const serialized=new XMLSerializer().serializeToString(bodyClone);
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
      img.onerror=()=>reject(new Error('snapshot-render-failed'));
      img.src=url;
    });

    const maxWidth=1440;
    const scale=Math.min(1,maxWidth/width);
    const outWidth=Math.round(width*scale);
    const outHeight=Math.round(height*scale);

    const canvas=document.createElement('canvas');
    canvas.width=outWidth;
    canvas.height=outHeight;

    const ctx=canvas.getContext('2d');
    ctx.fillStyle='#260606';
    ctx.fillRect(0,0,outWidth,outHeight);
    ctx.drawImage(image,0,0,outWidth,outHeight);
    drawEvidenceStamp(ctx,outWidth,outHeight,data);

    return {
      image:canvas.toDataURL('image/jpeg',.88),
      width:outWidth,
      height:outHeight,
      captured_at:new Date().toISOString(),
      stage:Number(data?.incident?.stage||5),
      stage_label:String(data?.incident?.stage_label||'CATASTROPHIC STATE'),
      source:'hmi'
    };
  }finally{
    URL.revokeObjectURL(url);
  }
}

function fallbackOperationalSnapshot(data){
  const canvas=document.createElement('canvas');
  canvas.width=1280;
  canvas.height=720;
  const ctx=canvas.getContext('2d');

  ctx.fillStyle='#200406';
  ctx.fillRect(0,0,1280,720);
  ctx.fillStyle='#3f090d';
  ctx.fillRect(0,0,1280,88);

  ctx.fillStyle='#fff';
  ctx.font='700 31px Arial';
  ctx.fillText('INTERAFAS · CRITICAL INCIDENT',42,54);

  ctx.fillStyle='#ff676f';
  ctx.font='800 52px Arial';
  ctx.fillText(String(data?.incident?.stage_label||'CATASTROPHIC STATE'),42,160);

  const lines=[
    ['ACTIVE ALARMS',Number(data?.alarm_count||0).toLocaleString()],
    ['AVAILABILITY',String(data?.availability??'—')+'%'],
    ['PRESSURE',String(data?.pressure??'—')+' bar'],
    ['FLOW',String(data?.flow??'—')+' L/s'],
    ['CRITICAL STATIONS',String(data?.stations_critical??'—')],
    ['QUALITY',String(data?.quality??'—')]
  ];

  lines.forEach((item,index)=>{
    const y=238+index*66;
    ctx.fillStyle='#ff9ba0';
    ctx.font='700 18px Arial';
    ctx.fillText(item[0],48,y);
    ctx.fillStyle='#fff';
    ctx.font='800 30px Arial';
    ctx.fillText(item[1],390,y);
  });

  ctx.fillStyle='#ff3742';
  ctx.fillRect(0,674,1280,46);
  ctx.fillStyle='#fff';
  ctx.font='800 17px Arial';
  ctx.fillText('OPERATIONAL SNAPSHOT · RTU-GW-07 · AUTOMATED EVIDENCE CAPTURE',42,704);

  drawEvidenceStamp(ctx,1280,720,data);

  return {
    image:canvas.toDataURL('image/jpeg',.9),
    width:1280,
    height:720,
    captured_at:new Date().toISOString(),
    stage:Number(data?.incident?.stage||5),
    stage_label:String(data?.incident?.stage_label||'CATASTROPHIC STATE'),
    source:'hmi'
  };
}

async function publishFinalHmiSnapshot(data){
  if(vuln20SnapshotInFlight)return;
  if(sessionStorage.getItem('INTERAFAS_V20_SNAPSHOT_SENT')==='1')return;

  vuln20SnapshotInFlight=true;

  try{
    // Give the stage-5 visual state and at least one floating notification time to paint.
    await new Promise(resolve=>setTimeout(resolve,520));

    let payload;
    try{
      payload=await renderViewportSnapshot(data);
    }catch(e){
      payload=fallbackOperationalSnapshot(data);
    }

    const response=await fetch('/operations/snapshot.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      credentials:'same-origin',
      cache:'no-store',
      body:JSON.stringify(payload)
    });

    const result=await response.json();

    if(response.ok&&result.ok){
      markSnapshotComplete();
    }else{
      throw new Error(result.error||'snapshot-publication-failed');
    }
  }catch(e){
    pushLog('WARN','EVIDENCE-01','Automatic operational snapshot could not be published.');
  }finally{
    vuln20SnapshotInFlight=false;
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
      stopDangerNotifications();
      sessionStorage.removeItem('INTERAFAS_V20_SNAPSHOT_SENT');
      if(window.INTERAFAS_AUDIO){
        window.INTERAFAS_AUDIO.broadcastStage?.(0,false);
        if(window.name!=='INTERAFAS_HMI_MONITOR')window.INTERAFAS_AUDIO.setStage(0,false);
      }
    }
    return;
  }

  const stage=clamp(Number(incident.stage||0),0,5);

  // A new active cascade must be allowed to publish fresh evidence even if
  // the previous run ended while this HMI window was closed.
  if(stage<5){
    sessionStorage.removeItem('INTERAFAS_V20_SNAPSHOT_SENT');
  }

  live.incidentActive=true;
  startDangerNotifications(stage);
  if(window.INTERAFAS_AUDIO){
    window.INTERAFAS_AUDIO.broadcastStage?.(stage,true);

    // The monitor window is visual-only. The firmware tab keeps the
    // user-gesture-authorized AudioContext alive in the background.
    if(window.name!=='INTERAFAS_HMI_MONITOR'){
      if(window.INTERAFAS_AUDIO.armed){
        window.INTERAFAS_AUDIO.setStage(stage,true);
      }else{
        window.INTERAFAS_AUDIO.arm().then(()=>{
          window.INTERAFAS_AUDIO?.setStage(stage,true);
        });
      }
    }
  }
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

  if(stage>=5 && data.final_flag){
    publishFinalHmiSnapshot(data);
  }

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