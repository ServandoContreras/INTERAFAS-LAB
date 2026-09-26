(()=>{
'use strict';

const AudioCtx=window.AudioContext||window.webkitAudioContext;
if(!AudioCtx)return;

let ctx=null;
let master=null;
let compressor=null;
let enabled=sessionStorage.getItem('INTERAFAS_AUDIO')!=='0';
let armed=false;
let active=false;
let stage=-1;
let pulseTimer=null;
let sequenceToken=0;

function ensureContext(){
  if(ctx)return ctx;

  ctx=new AudioCtx();

  master=ctx.createGain();
  master.gain.value=.72;

  compressor=ctx.createDynamicsCompressor();
  compressor.threshold.value=-12;
  compressor.knee.value=8;
  compressor.ratio.value=5;
  compressor.attack.value=.003;
  compressor.release.value=.16;

  master.connect(compressor);
  compressor.connect(ctx.destination);

  return ctx;
}

function updateButton(){
  const btn=document.getElementById('hmi-audio-toggle');
  const state=document.getElementById('hmi-audio-state');

  if(btn){
    btn.classList.toggle('audio-muted',!enabled);
    btn.classList.toggle('audio-armed',enabled&&armed);
    btn.classList.toggle('audio-needs-gesture',enabled&&!armed);

    if(!enabled){
      btn.textContent='AUDIO · SILENCIADO';
      btn.title='Alarma audible silenciada';
    }else if(armed){
      btn.textContent='AUDIO · ACTIVO';
      btn.title='Alarma audible activa — clic para silenciar';
    }else{
      btn.textContent='ACTIVAR AUDIO';
      btn.title='Clic para habilitar audio';
    }
  }

  if(state){
    const actual=ctx?ctx.state:'not-created';
    state.textContent=!enabled?'SILENCIADO':armed?'ACTIVO':'BLOQUEADO · '+String(actual).toUpperCase();
    state.dataset.audioState=!enabled?'muted':armed?'running':'blocked';
  }
}

async function arm(){
  try{
    ensureContext();
    await ctx.resume();
    armed=ctx.state==='running';
    if(armed)sessionStorage.setItem('INTERAFAS_AUDIO','1');
    updateButton();
    if(armed&&enabled&&active)startSequence(true);
    return armed;
  }catch(e){
    armed=false;
    updateButton();
    return false;
  }
}

function envelope(at,duration,peak){
  const g=ctx.createGain();
  const attack=.012;
  const release=.055;

  g.gain.setValueAtTime(.0001,at);
  g.gain.exponentialRampToValueAtTime(peak,at+attack);
  g.gain.setValueAtTime(peak,Math.max(at+attack,at+duration-release));
  g.gain.exponentialRampToValueAtTime(.0001,at+duration);
  g.connect(master);
  return g;
}

function cleanTone(freq,at,duration=.34,peak=.19,type='square'){
  if(!armed||!enabled)return;

  const osc=ctx.createOscillator();
  const filter=ctx.createBiquadFilter();
  const gain=envelope(at,duration,peak);

  osc.type=type;
  osc.frequency.setValueAtTime(freq,at);

  filter.type='bandpass';
  filter.frequency.value=freq;
  filter.Q.value=1.05;

  osc.connect(filter);
  filter.connect(gain);
  osc.start(at);
  osc.stop(at+duration+.03);
}

function alertPulse(level){
  if(!armed||!enabled||!active)return;

  const t=ctx.currentTime+.005;
  const lift=Math.max(0,Math.min(5,level))*18;

  // Clean alternating attention tones; intentionally no sub-bass or distortion.
  cleanTone(780+lift,t,.30,.18,'square');
  cleanTone(1040+lift,t+.31,.30,.19,'square');
  cleanTone(780+lift,t+.62,.30,.18,'square');
  cleanTone(1040+lift,t+.93,.36,.20,'square');

  // Short upper harmonic gives the "public warning speaker" edge.
  cleanTone(1560+lift,t+.31,.16,.045,'triangle');
  cleanTone(1560+lift,t+.93,.18,.05,'triangle');
}

function intervalFor(level){
  return [1560,1480,1400,1320,1240,1160][Math.max(0,Math.min(5,level))];
}

function stopSequence(){
  sequenceToken++;
  if(pulseTimer){
    clearTimeout(pulseTimer);
    pulseTimer=null;
  }
}

function startSequence(immediate=true){
  stopSequence();
  if(!active||!enabled||!armed)return;

  const token=sequenceToken;
  const run=()=>{
    if(token!==sequenceToken||!active||!enabled||!armed)return;
    alertPulse(stage);
    pulseTimer=setTimeout(run,intervalFor(stage));
  };

  if(immediate)run();
  else pulseTimer=setTimeout(run,80);
}

function setStage(nextStage,isActive=true){
  const next=Math.max(0,Math.min(5,Number(nextStage)||0));
  const changed=next!==stage||!!isActive!==active;

  stage=next;
  active=!!isActive;

  if(!active){
    stopSequence();
    return;
  }

  if(changed&&armed&&enabled)startSequence(true);
}

function silence(){
  enabled=false;
  sessionStorage.setItem('INTERAFAS_AUDIO','0');
  stopSequence();
  updateButton();
}

async function enable(){
  enabled=true;
  sessionStorage.setItem('INTERAFAS_AUDIO','1');
  await arm();
  if(active&&armed)startSequence(true);
  updateButton();
}

function toggle(){
  if(enabled&&armed)silence();
  else enable();
}

function stop(){
  active=false;
  stage=-1;
  stopSequence();
}

window.INTERAFAS_AUDIO={
  setStage,
  toggle,
  enable,
  silence,
  arm,
  stop,
  get state(){return ctx?ctx.state:'not-created'},
  get enabled(){return enabled},
  get armed(){return armed}
};

document.addEventListener('DOMContentLoaded',()=>{
  updateButton();

  const btn=document.getElementById('hmi-audio-toggle');
  if(btn)btn.addEventListener('click',async e=>{
    e.preventDefault();
    e.stopPropagation();
    if(enabled&&armed)silence();
    else await enable();
  });

  const resume=()=>{
    if(enabled&&!armed)arm();
  };

  document.addEventListener('pointerdown',resume,{passive:true});
  document.addEventListener('keydown',resume,{passive:true});

  if(enabled)arm();
});
})();