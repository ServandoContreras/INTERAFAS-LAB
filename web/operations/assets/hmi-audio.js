(()=>{
'use strict';

const AudioCtx=window.AudioContext||window.webkitAudioContext;
if(!AudioCtx)return;

const ALERT_CHANNEL='interafas-v20-alarm';
const bc=('BroadcastChannel' in window)?new BroadcastChannel(ALERT_CHANNEL):null;

const SPOKEN_ALERTS=[
  {
    lang:'es-MX',
    text:'Alerta. Falla crítica de control.',
    fallback:'Alerta. Falla critica de control.'
  },
  {
    lang:'ru-RU',
    text:'Тревога. Критический сбой управления.',
    fallback:'Trevoga. Kriticheskiy sboy upravleniya.'
  },
  {
    lang:'en-US',
    text:'Warning. Critical control failure.',
    fallback:'Warning. Critical control failure.'
  },
  {
    lang:'fr-FR',
    text:'Alerte. Défaillance critique du contrôle.',
    fallback:'Alerte. Defaillance critique du controle.'
  },
  {
    lang:'de-DE',
    text:'Warnung. Kritischer Steuerungsausfall.',
    fallback:'Warnung. Kritischer Steuerungsausfall.'
  },
  {
    lang:'ar-SA',
    text:'تحذير. عطل حرج في نظام التحكم.',
    fallback:'Tahdheer. Atal harij fi nizam al tahakkum.'
  },
  {
    lang:'zh-CN',
    text:'警报。控制系统严重故障。',
    fallback:'Jing bao. Kong zhi xi tong yan zhong gu zhang.'
  },
  {
    lang:'ja-JP',
    text:'警報。制御システムに重大な障害。',
    fallback:'Keihou. Seigyo shisutemu ni juudai na shougai.'
  }
];

let ctx=null;
let master=null;
let compressor=null;
let armed=false;
let active=false;
let stage=-1;
let pulseTimer=null;
let speechAdvanceTimer=null;
let speechWatchdog=null;
let voiceIndex=0;
let cachedVoices=[];

function refreshVoices(){
  if(!('speechSynthesis' in window))return [];
  try{
    const voices=window.speechSynthesis.getVoices()||[];
    if(voices.length)cachedVoices=voices;
  }catch(e){}
  return cachedVoices;
}

if('speechSynthesis' in window){
  refreshVoices();
  window.speechSynthesis.addEventListener?.('voiceschanged',refreshVoices);
}

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

  tone(880+lift,t,.14,.12);
  tone(660+lift,t+.19,.16,.13);
  tone(880+lift,t+.40,.18,.12);
}

function languageVoice(lang){
  const voices=refreshVoices();
  const normalized=lang.toLowerCase();

  const exact=voices.find(v=>String(v.lang||'').toLowerCase()===normalized);
  if(exact)return exact;

  const base=normalized.split('-')[0];
  return voices.find(v=>String(v.lang||'').toLowerCase().split('-')[0]===base)||null;
}

function defaultVoice(){
  const voices=refreshVoices();
  return voices.find(v=>v.default)||voices.find(v=>/^es/i.test(String(v.lang||'')))||voices.find(v=>/^en/i.test(String(v.lang||'')))||voices[0]||null;
}

function clearSpeechTimers(){
  if(speechAdvanceTimer){
    clearTimeout(speechAdvanceTimer);
    speechAdvanceTimer=null;
  }
  if(speechWatchdog){
    clearTimeout(speechWatchdog);
    speechWatchdog=null;
  }
}

function scheduleNextSpeech(delay=180){
  clearSpeechTimers();
  if(!active)return;

  speechAdvanceTimer=setTimeout(()=>{
    speechAdvanceTimer=null;
    speakNext();
  },delay);
}

function speakNext(){
  if(!active||!('speechSynthesis' in window))return;

  const item=SPOKEN_ALERTS[voiceIndex%SPOKEN_ALERTS.length];
  voiceIndex++;

  try{
    const nativeVoice=languageVoice(item.lang);
    const fallbackVoice=defaultVoice();
    const useNative=!!nativeVoice;

    const u=new SpeechSynthesisUtterance(useNative?item.text:item.fallback);
    u.rate=useNative?1.03:1.00;
    u.pitch=.92;
    u.volume=1;

    if(useNative){
      u.lang=item.lang;
      u.voice=nativeVoice;
    }else if(fallbackVoice){
      u.lang=String(fallbackVoice.lang||'en-US');
      u.voice=fallbackVoice;
    }else{
      u.lang='en-US';
    }

    let finished=false;
    const advance=()=>{
      if(finished)return;
      finished=true;
      clearSpeechTimers();
      if(active)scheduleNextSpeech(180);
    };

    u.onend=advance;
    u.onerror=advance;

    // Some browser/voice combinations fail to emit onend/onerror.
    speechWatchdog=setTimeout(()=>{
      try{window.speechSynthesis.cancel()}catch(e){}
      advance();
    },3600);

    window.speechSynthesis.speak(u);
  }catch(e){
    scheduleNextSpeech(180);
  }
}

function clearTimers(){
  if(pulseTimer){
    clearInterval(pulseTimer);
    pulseTimer=null;
  }
  clearSpeechTimers();
}

function beginAlarm(){
  if(!armed||!active)return;

  clearTimers();
  voiceIndex=0;

  try{
    window.speechSynthesis?.cancel();
    window.speechSynthesis?.resume();
  }catch(e){}

  attentionBurst();

  pulseTimer=setInterval(()=>{
    if(active)attentionBurst();
  },1550);

  setTimeout(()=>{
    if(active)speakNext();
  },220);
}

async function arm(){
  try{
    ensureContext();
    await ctx.resume();
    armed=ctx.state==='running';
    refreshVoices();

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

  try{
    window.speechSynthesis?.cancel();
  }catch(e){}
}

function broadcastStage(nextStage,isActive=true){
  try{
    bc?.postMessage({
      type:'stage',
      stage:Number(nextStage)||0,
      active:!!isActive
    });
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
  get state(){return ctx?ctx.state:'not-created'},
  get voices(){
    return refreshVoices().map(v=>({
      name:v.name,
      lang:v.lang,
      default:!!v.default
    }));
  }
};
})();