(()=>{
'use strict';

const AudioCtx=window.AudioContext||window.webkitAudioContext;
if(!AudioCtx)return;

let ctx=null;
let master=null;
let compressor=null;
let enabled=sessionStorage.getItem('INTERAFAS_AUDIO')!=='0';
let armed=false;
let stage=-1;
let active=false;
let loopTimer=null;
let lastPatternAt=0;

function ensureContext(){
  if(ctx)return ctx;
  ctx=new AudioCtx();

  compressor=ctx.createDynamicsCompressor();
  compressor.threshold.value=-18;
  compressor.knee.value=12;
  compressor.ratio.value=5;
  compressor.attack.value=.003;
  compressor.release.value=.22;

  master=ctx.createGain();
  master.gain.value=.17;

  master.connect(compressor);
  compressor.connect(ctx.destination);
  return ctx;
}

async function arm(){
  try{
    ensureContext();
    await ctx.resume();
    armed=ctx.state==='running';
    if(armed){
      sessionStorage.setItem('INTERAFAS_AUDIO','1');
      updateButton();
      if(active && enabled)scheduleStage(true);
    }
  }catch(e){}
}

function envGain(at,duration,peak=.16){
  const g=ctx.createGain();
  g.gain.setValueAtTime(.0001,at);
  g.gain.exponentialRampToValueAtTime(Math.max(.001,peak),at+.018);
  g.gain.exponentialRampToValueAtTime(.0001,at+duration);
  g.connect(master);
  return g;
}

function tone(freq,at,duration=.18,peak=.11,type='sine',detune=0){
  if(!armed||!enabled)return;
  const o=ctx.createOscillator();
  const g=envGain(at,duration,peak);
  o.type=type;
  o.frequency.setValueAtTime(freq,at);
  o.detune.setValueAtTime(detune,at);
  o.connect(g);
  o.start(at);
  o.stop(at+duration+.04);
}

function sweep(from,to,at,duration=.35,peak=.075,type='triangle'){
  if(!armed||!enabled)return;
  const o=ctx.createOscillator();
  const g=envGain(at,duration,peak);
  o.type=type;
  o.frequency.setValueAtTime(from,at);
  o.frequency.exponentialRampToValueAtTime(Math.max(1,to),at+duration);
  o.connect(g);
  o.start(at);
  o.stop(at+duration+.05);
}

function noiseBurst(at,duration=.16,peak=.035,center=1250){
  if(!armed||!enabled)return;
  const frames=Math.max(1,Math.floor(ctx.sampleRate*duration));
  const buffer=ctx.createBuffer(1,frames,ctx.sampleRate);
  const data=buffer.getChannelData(0);
  for(let i=0;i<frames;i++){
    const taper=1-(i/frames);
    data[i]=(Math.random()*2-1)*taper;
  }
  const src=ctx.createBufferSource();
  const filter=ctx.createBiquadFilter();
  const g=envGain(at,duration,peak);
  filter.type='bandpass';
  filter.frequency.setValueAtTime(center,at);
  filter.Q.value=4.2;
  src.buffer=buffer;
  src.connect(filter);
  filter.connect(g);
  src.start(at);
}

function lowPulse(at,freq=82,duration=.28,peak=.12){
  tone(freq,at,duration,peak,'sine');
  tone(freq*2,at+.008,duration*.78,peak*.28,'triangle');
}

function pattern(level){
  if(!armed||!enabled||!active)return;
  const t=ctx.currentTime+.02;

  switch(level){
    case 0:
      // Clean engineering pre-alarm: expensive two-note acknowledgement.
      tone(523.25,t,.16,.08,'sine');
      tone(783.99,t+.115,.22,.07,'triangle');
      tone(1046.5,t+.135,.16,.025,'sine');
      noiseBurst(t+.11,.09,.018,1800);
      break;

    case 1:
      // Control instability: harmonic double pulse with a restrained low layer.
      lowPulse(t,92,.24,.085);
      tone(466.16,t+.02,.18,.08,'triangle');
      tone(698.46,t+.12,.18,.07,'sine');
      lowPulse(t+.43,92,.21,.075);
      tone(554.37,t+.45,.17,.075,'triangle');
      tone(830.61,t+.55,.16,.055,'sine');
      break;

    case 2:
      // Process cascade: three-part metallic signature.
      lowPulse(t,78,.34,.11);
      tone(392,t+.01,.22,.095,'sawtooth');
      tone(587.33,t+.07,.21,.065,'triangle');
      noiseBurst(t+.05,.12,.025,1450);
      tone(493.88,t+.34,.18,.08,'triangle');
      tone(740,t+.39,.18,.055,'sine');
      sweep(260,520,t+.62,.28,.05,'triangle');
      break;

    case 3:
      // Metropolitan propagation: asymmetric urgent motif.
      lowPulse(t,72,.38,.13);
      tone(349.23,t,.2,.1,'sawtooth');
      tone(523.25,t+.055,.2,.065,'triangle');
      tone(698.46,t+.11,.18,.045,'sine');
      noiseBurst(t+.08,.14,.035,1120);
      lowPulse(t+.38,86,.25,.09);
      tone(440,t+.39,.18,.09,'square');
      tone(659.25,t+.47,.18,.055,'triangle');
      sweep(820,310,t+.68,.32,.055,'sawtooth');
      break;

    case 4:
      // Systemic failure: denser, lower, harder-edged.
      lowPulse(t,62,.46,.15);
      tone(311.13,t,.19,.11,'square');
      tone(466.16,t+.045,.19,.075,'sawtooth');
      noiseBurst(t+.02,.16,.045,920);
      tone(622.25,t+.22,.16,.075,'triangle');
      lowPulse(t+.39,74,.31,.115);
      tone(370,t+.40,.17,.105,'square');
      tone(554.37,t+.47,.17,.07,'triangle');
      sweep(980,245,t+.67,.34,.065,'sawtooth');
      break;

    default:
      // Catastrophic signature: low impact + dissonant precision tones.
      lowPulse(t,55,.52,.17);
      tone(293.66,t,.22,.12,'square');
      tone(440,t+.035,.22,.085,'sawtooth');
      tone(622.25,t+.07,.19,.06,'triangle');
      noiseBurst(t+.02,.18,.052,760);
      sweep(1180,210,t+.27,.42,.075,'sawtooth');
      lowPulse(t+.52,69,.33,.13);
      tone(329.63,t+.53,.17,.105,'square');
      tone(493.88,t+.59,.17,.075,'triangle');
      tone(739.99,t+.65,.14,.048,'sine');
      break;
  }

  lastPatternAt=performance.now();
}

function intervalFor(level){
  return [4200,3200,2350,1700,1200,900][Math.max(0,Math.min(5,level))];
}

function clearLoop(){
  if(loopTimer){
    clearTimeout(loopTimer);
    loopTimer=null;
  }
}

function scheduleStage(immediate=false){
  clearLoop();
  if(!active||!enabled||!armed)return;
  const run=()=>{
    if(!active||!enabled||!armed)return;
    pattern(stage);
    loopTimer=setTimeout(run,intervalFor(stage));
  };
  if(immediate)run();
  else loopTimer=setTimeout(run,Math.min(700,intervalFor(stage)));
}

function transition(level){
  if(!armed||!enabled)return;
  const t=ctx.currentTime+.015;
  // Short escalation cue distinct from repeating alarm motif.
  tone(330+level*42,t,.10,.055,'sine');
  tone(495+level*58,t+.075,.13,.05,'triangle');
  sweep(210+level*30,520+level*75,t+.14,.22,.035,'triangle');
}

function setStage(nextStage,isActive=true){
  const ns=Math.max(0,Math.min(5,Number(nextStage)||0));
  const changed=ns!==stage || isActive!==active;
  stage=ns;
  active=!!isActive;

  if(!active){
    clearLoop();
    return;
  }

  if(changed && armed && enabled){
    transition(stage);
    scheduleStage(false);
  }
}

function silence(){
  enabled=false;
  sessionStorage.setItem('INTERAFAS_AUDIO','0');
  clearLoop();
  updateButton();
}

async function enable(){
  enabled=true;
  sessionStorage.setItem('INTERAFAS_AUDIO','1');
  await arm();
  updateButton();
  if(active && armed)scheduleStage(true);
}

function toggle(){
  if(enabled && armed)silence();
  else enable();
}

function updateButton(){
  const btn=document.getElementById('hmi-audio-toggle');
  if(!btn)return;

  btn.classList.toggle('audio-muted',!enabled);
  btn.classList.toggle('audio-armed',enabled&&armed);
  btn.classList.toggle('audio-needs-gesture',enabled&&!armed);

  if(!enabled){
    btn.textContent='ЗВУК · ТИШИНА';
    btn.title='Alarm audio silenced';
  }else if(armed){
    btn.textContent='ЗВУК · ВКЛ';
    btn.title='Alarm audio enabled — click to silence';
  }else{
    btn.textContent='ЗВУК · НАЖАТЬ';
    btn.title='Click once to enable industrial alarm audio';
  }
}

function gestureArm(){
  if(!enabled||armed)return;
  arm();
}

window.INTERAFAS_AUDIO={setStage,toggle,enable,silence,arm,get enabled(){return enabled},get armed(){return armed}};

document.addEventListener('DOMContentLoaded',()=>{
  updateButton();

  const btn=document.getElementById('hmi-audio-toggle');
  if(btn)btn.addEventListener('click',e=>{
    e.preventDefault();
    e.stopPropagation();
    toggle();
  });

  // Browsers require a user gesture before unmuted Web Audio can start.
  document.addEventListener('pointerdown',gestureArm,{passive:true});
  document.addEventListener('keydown',gestureArm,{passive:true});

  // Try immediately for browsers/origins where autoplay is already allowed.
  if(enabled)arm();
});
})();