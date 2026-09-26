(()=>{
'use strict';

const AudioCtx=window.AudioContext||window.webkitAudioContext;
if(!AudioCtx)return;

let ctx=null;
let master=null;
let compressor=null;
let armed=false;
let active=false;
let stage=-1;
let timer=null;
let token=0;
let lastSpeechAt=0;

function ensureContext(){
  if(ctx)return ctx;

  ctx=new AudioCtx();

  master=ctx.createGain();
  master.gain.value=.52;

  compressor=ctx.createDynamicsCompressor();
  compressor.threshold.value=-14;
  compressor.knee.value=10;
  compressor.ratio.value=4;
  compressor.attack.value=.004;
  compressor.release.value=.18;

  master.connect(compressor);
  compressor.connect(ctx.destination);
  return ctx;
}

async function arm(){
  try{
    ensureContext();
    await ctx.resume();
    armed=ctx.state==='running';
    if(armed&&active)start(true);
    return armed;
  }catch(e){
    armed=false;
    return false;
  }
}

function envelope(at,duration,peak){
  const g=ctx.createGain();
  g.gain.setValueAtTime(.0001,at);
  g.gain.linearRampToValueAtTime(peak,at+.012);
  g.gain.setValueAtTime(peak,Math.max(at+.012,at+duration-.06));
  g.gain.exponentialRampToValueAtTime(.0001,at+duration);
  g.connect(master);
  return g;
}

function tone(freq,at,duration=.18,peak=.13,type='triangle'){
  if(!armed||!active)return;
  const osc=ctx.createOscillator();
  const filter=ctx.createBiquadFilter();
  const gain=envelope(at,duration,peak);

  osc.type=type;
  osc.frequency.setValueAtTime(freq,at);
  filter.type='bandpass';
  filter.frequency.value=freq;
  filter.Q.value=.82;

  osc.connect(filter);
  filter.connect(gain);
  osc.start(at);
  osc.stop(at+duration+.025);
}

function attentionSignal(level){
  if(!armed||!active)return;

  const t=ctx.currentTime+.004;
  const shift=Math.max(0,Math.min(5,level))*10;

  // Public-address attention sequence: clear, dry and intelligible.
  tone(780+shift,t,.22,.14,'triangle');
  tone(980+shift,t+.24,.22,.15,'triangle');
  tone(780+shift,t+.48,.22,.14,'triangle');
  tone(980+shift,t+.72,.30,.16,'triangle');

  tone(620+shift,t+1.10,.36,.14,'sine');
  tone(930+shift,t+1.48,.36,.15,'triangle');
}

function speakEmergency(force=false){
  if(!('speechSynthesis' in window)||!active)return;

  const now=Date.now();
  if(!force && now-lastSpeechAt<6200)return;
  lastSpeechAt=now;

  try{
    window.speechSynthesis.cancel();

    const u=new SpeechSynthesisUtterance(
      stage>=4
        ? 'Alerta. Alerta. Falla crítica de control. Estado de emergencia.'
        : 'Alerta. Alerta. Anomalía crítica en sistema de control.'
    );

    u.lang='es-MX';
    u.rate=.9;
    u.pitch=.88;
    u.volume=1;

    const voices=window.speechSynthesis.getVoices();
    const preferred=voices.find(v=>/^es-MX$/i.test(v.lang))
      || voices.find(v=>/^es/i.test(v.lang));
    if(preferred)u.voice=preferred;

    window.speechSynthesis.speak(u);
  }catch(e){}
}

function intervalFor(level){
  return [2700,2550,2400,2250,2100,1950][Math.max(0,Math.min(5,level))];
}

function stopLoop(){
  token++;
  if(timer){
    clearTimeout(timer);
    timer=null;
  }
}

function start(immediate=true){
  stopLoop();
  if(!active||!armed)return;

  const current=token;
  const run=()=>{
    if(current!==token||!active||!armed)return;
    attentionSignal(stage);
    speakEmergency(false);
    timer=setTimeout(run,intervalFor(stage));
  };

  if(immediate){
    attentionSignal(stage);
    speakEmergency(true);
    timer=setTimeout(run,intervalFor(stage));
  }else{
    timer=setTimeout(run,50);
  }
}

function setStage(nextStage,isActive=true){
  const next=Math.max(0,Math.min(5,Number(nextStage)||0));
  const changed=next!==stage||!!isActive!==active;

  stage=next;
  active=!!isActive;

  if(!active){
    stopLoop();
    try{window.speechSynthesis?.cancel()}catch(e){}
    return;
  }

  if(changed&&armed)start(true);
}

function stop(){
  active=false;
  stage=-1;
  stopLoop();
  try{window.speechSynthesis?.cancel()}catch(e){}
}

window.INTERAFAS_AUDIO={
  arm,
  setStage,
  stop,
  get armed(){return armed},
  get state(){return ctx?ctx.state:'not-created'}
};

if(sessionStorage.getItem('INTERAFAS_V20_AUDIO_PENDING')==='1'){
  arm();
}
})();