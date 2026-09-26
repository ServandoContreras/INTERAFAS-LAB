(()=>{
'use strict';

const AudioCtx=window.AudioContext||window.webkitAudioContext;
if(!AudioCtx)return;

const ALERT_CHANNEL='interafas-v20-alarm';
const bc=('BroadcastChannel' in window)?new BroadcastChannel(ALERT_CHANNEL):null;

const SPOKEN_ALERTS=[
  {lang:'es-MX',text:'Alerta. Falla crítica en el sistema de control. Estado de emergencia.'},
  {lang:'ru-RU',text:'Тревога. Критический сбой системы управления. Аварийное состояние.'},
  {lang:'en-US',text:'Warning. Critical control system failure. Emergency condition.'},
  {lang:'fr-FR',text:'Alerte. Défaillance critique du système de contrôle. État d’urgence.'},
  {lang:'de-DE',text:'Warnung. Kritischer Ausfall des Steuerungssystems. Notfallzustand.'},
  {lang:'ar-SA',text:'تحذير. عطل حرج في نظام التحكم. حالة طوارئ.'},
  {lang:'zh-CN',text:'警报。控制系统发生严重故障。进入紧急状态。'},
  {lang:'ja-JP',text:'警報。制御システムに重大な障害が発生しました。緊急状態です。'}
];

let ctx=null;
let master=null;
let compressor=null;
let armed=false;
let active=false;
let stage=-1;
let pulseTimer=null;
let speechTimer=null;
let voiceIndex=0;

function ensureContext(){
  if(ctx)return ctx;

  ctx=new AudioCtx();

  master=ctx.createGain();
  master.gain.value=.46;

  compressor=ctx.createDynamicsCompressor();
  compressor.threshold.value=-13;
  compressor.knee.value=8;
  compressor.ratio.value=5;
  compressor.attack.value=.003;
  compressor.release.value=.14;

  master.connect(compressor);
  compressor.connect(ctx.destination);
  return ctx;
}

function envelope(at,duration,peak){
  const g=ctx.createGain();
  g.gain.setValueAtTime(.0001,at);
  g.gain.linearRampToValueAtTime(peak,at+.008);
  g.gain.setValueAtTime(peak,Math.max(at+.008,at+duration-.045));
  g.gain.exponentialRampToValueAtTime(.0001,at+duration);
  g.connect(master);
  return g;
}

function tone(freq,at,duration=.14,peak=.13){
  if(!armed||!active)return;

  const osc=ctx.createOscillator();
  const filter=ctx.createBiquadFilter();
  const gain=envelope(at,duration,peak);

  osc.type='square';
  osc.frequency.setValueAtTime(freq,at);
  filter.type='bandpass';
  filter.frequency.value=freq;
  filter.Q.value=.65;

  osc.connect(filter);
  filter.connect(gain);
  osc.start(at);
  osc.stop(at+duration+.02);
}

function attentionBurst(){
  if(!armed||!active)return;

  const t=ctx.currentTime+.003;
  const lift=Math.max(0,Math.min(5,stage))*8;

  // Dry public-warning attention burst, deliberately short so speech remains intelligible.
  tone(880+lift,t,.14,.12);
  tone(660+lift,t+.19,.16,.13);
  tone(880+lift,t+.40,.18,.12);
}

function availableVoice(lang){
  if(!('speechSynthesis' in window))return null;
  const voices=window.speechSynthesis.getVoices();
  const exact=voices.find(v=>String(v.lang||'').toLowerCase()===lang.toLowerCase());
  if(exact)return exact;

  const base=lang.split('-')[0].toLowerCase();
  return voices.find(v=>String(v.lang||'').toLowerCase().startsWith(base))||null;
}

function speakNext(){
  if(!active||!('speechSynthesis' in window))return;

  const item=SPOKEN_ALERTS[voiceIndex%SPOKEN_ALERTS.length];
  voiceIndex++;

  try{
    const u=new SpeechSynthesisUtterance(item.text);
    u.lang=item.lang;
    u.rate=.88;
    u.pitch=.92;
    u.volume=1;

    const voice=availableVoice(item.lang);
    if(voice)u.voice=voice;

    window.speechSynthesis.speak(u);
  }catch(e){}
}

function clearTimers(){
  if(pulseTimer){
    clearInterval(pulseTimer);
    pulseTimer=null;
  }
  if(speechTimer){
    clearInterval(speechTimer);
    speechTimer=null;
  }
}

function beginAlarm(){
  if(!armed||!active)return;

  clearTimers();

  attentionBurst();
  setTimeout(()=>{ if(active) speakNext(); },240);

  pulseTimer=setInterval(()=>{
    if(active)attentionBurst();
  },1550);

  speechTimer=setInterval(()=>{
    if(!active)return;
    attentionBurst();
    setTimeout(()=>{ if(active) speakNext(); },240);
  },4700);
}

async function arm(){
  try{
    ensureContext();
    await ctx.resume();
    armed=ctx.state==='running';
    if(armed&&active)beginAlarm();
    return armed;
  }catch(e){
    armed=false;
    return false;
  }
}

function setStage(nextStage,isActive=true){
  const wasActive=active;
  stage=Math.max(0,Math.min(5,Number(nextStage)||0));
  active=!!isActive;

  if(!active){
    stop();
    return;
  }

  if(!wasActive&&armed)beginAlarm();
}

function stop(){
  active=false;
  stage=-1;
  clearTimers();
  try{window.speechSynthesis?.cancel()}catch(e){}
}

function broadcastStage(nextStage,isActive=true){
  try{
    bc?.postMessage({type:'stage',stage:Number(nextStage)||0,active:!!isActive});
  }catch(e){}
}

if(bc){
  bc.addEventListener('message',event=>{
    const msg=event.data||{};
    if(msg.type==='stage'){
      setStage(Number(msg.stage)||0,!!msg.active);
    }else if(msg.type==='stop'){
      stop();
    }
  });
}

window.INTERAFAS_AUDIO={
  arm,
  setStage,
  stop,
  broadcastStage,
  get armed(){return armed},
  get state(){return ctx?ctx.state:'not-created'}
};
})();