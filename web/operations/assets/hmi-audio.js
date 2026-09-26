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
let bedNodes=[];

function ensureContext(){
  if(ctx)return ctx;
  ctx=new AudioCtx();

  compressor=ctx.createDynamicsCompressor();
  compressor.threshold.value=-20;
  compressor.knee.value=8;
  compressor.ratio.value=7;
  compressor.attack.value=.002;
  compressor.release.value=.28;

  master=ctx.createGain();
  master.gain.value=.23;

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
      if(active&&enabled){
        syncEmergencyBed();
        scheduleStage(true);
      }
    }
  }catch(e){}
}

function connectGain(at,duration,peak=.16,attack=.018,release=.12){
  const g=ctx.createGain();
  g.gain.setValueAtTime(.0001,at);
  g.gain.exponentialRampToValueAtTime(Math.max(.001,peak),at+attack);
  g.gain.setValueAtTime(Math.max(.001,peak),Math.max(at+attack,at+duration-release));
  g.gain.exponentialRampToValueAtTime(.0001,at+duration);
  g.connect(master);
  return g;
}

function tone(freq,at,duration=.18,peak=.10,type='sine',detune=0){
  if(!armed||!enabled)return;
  const o=ctx.createOscillator();
  const g=connectGain(at,duration,peak);
  o.type=type;
  o.frequency.setValueAtTime(freq,at);
  o.detune.setValueAtTime(detune,at);
  o.connect(g);
  o.start(at);
  o.stop(at+duration+.03);
}

function horn(freq,at,duration=.34,peak=.14){
  if(!armed||!enabled)return;
  tone(freq,at,duration,peak,'sawtooth',-4);
  tone(freq*1.005,at,duration,peak*.62,'sawtooth',4);
  tone(freq*2,at+.006,duration*.86,peak*.16,'triangle');
}

function impact(at,peak=.16){
  if(!armed||!enabled)return;
  tone(52,at,.42,peak,'sine');
  tone(104,at+.006,.28,peak*.40,'triangle');
  noiseBurst(at,.13,peak*.18,420);
}

function sweep(from,to,at,duration=.42,peak=.08,type='sawtooth'){
  if(!armed||!enabled)return;
  const o=ctx.createOscillator();
  const f=ctx.createBiquadFilter();
  const g=connectGain(at,duration,peak,.025,.08);
  o.type=type;
  o.frequency.setValueAtTime(Math.max(1,from),at);
  o.frequency.exponentialRampToValueAtTime(Math.max(1,to),at+duration);
  f.type='lowpass';
  f.frequency.value=1800;
  f.Q.value=1.2;
  o.connect(f);
  f.connect(g);
  o.start(at);
  o.stop(at+duration+.04);
}

function noiseBurst(at,duration=.16,peak=.035,center=1100){
  if(!armed||!enabled)return;
  const frames=Math.max(1,Math.floor(ctx.sampleRate*duration));
  const buffer=ctx.createBuffer(1,frames,ctx.sampleRate);
  const data=buffer.getChannelData(0);
  for(let i=0;i<frames;i++){
    const x=Math.random()*2-1;
    const taper=Math.pow(1-i/frames,1.7);
    data[i]=x*taper;
  }
  const src=ctx.createBufferSource();
  const filter=ctx.createBiquadFilter();
  const g=connectGain(at,duration,peak,.01,.05);
  filter.type='bandpass';
  filter.frequency.setValueAtTime(center,at);
  filter.Q.value=3.4;
  src.buffer=buffer;
  src.connect(filter);
  filter.connect(g);
  src.start(at);
}

function stopBed(){
  bedNodes.forEach(n=>{
    try{if(typeof n.stop==='function')n.stop()}catch(e){}
    try{n.disconnect()}catch(e){}
  });
  bedNodes=[];
}

function makeNoiseLoop(seconds=2){
  const frames=Math.floor(ctx.sampleRate*seconds);
  const buffer=ctx.createBuffer(1,frames,ctx.sampleRate);
  const data=buffer.getChannelData(0);
  let last=0;
  for(let i=0;i<frames;i++){
    const white=Math.random()*2-1;
    last=last*.93+white*.07;
    data[i]=last;
  }
  const src=ctx.createBufferSource();
  src.buffer=buffer;
  src.loop=true;
  return src;
}

function startEmergencyBed(level){
  stopBed();
  if(!armed||!enabled||!active||level<3)return;

  const t=ctx.currentTime;
  const bus=ctx.createGain();
  bus.gain.setValueAtTime(.0001,t);
  bus.gain.exponentialRampToValueAtTime(level>=5?.22:level===4?.15:.085,t+.35);
  bus.connect(master);
  bedNodes.push(bus);

  // Mechanical room rumble.
  const rumble=ctx.createOscillator();
  const rumble2=ctx.createOscillator();
  const low=ctx.createBiquadFilter();
  const rumbleGain=ctx.createGain();
  rumble.type='sawtooth';
  rumble2.type='sawtooth';
  rumble.frequency.value=level>=5?48:55;
  rumble2.frequency.value=(level>=5?48:55)*1.018;
  low.type='lowpass';
  low.frequency.value=240;
  low.Q.value=2.8;
  rumbleGain.gain.value=level>=5?.24:.16;
  rumble.connect(low);
  rumble2.connect(low);
  low.connect(rumbleGain);
  rumbleGain.connect(bus);
  rumble.start();
  rumble2.start();
  bedNodes.push(rumble,rumble2,low,rumbleGain);

  // Sweeping emergency siren driven continuously by an LFO.
  const siren=ctx.createOscillator();
  const sirenGain=ctx.createGain();
  const sirenFilter=ctx.createBiquadFilter();
  const freqLfo=ctx.createOscillator();
  const freqDepth=ctx.createGain();
  siren.type='sawtooth';
  siren.frequency.value=level>=5?510:430;
  freqLfo.type='sine';
  freqLfo.frequency.value=level>=5?.78:.56;
  freqDepth.gain.value=level>=5?270:175;
  freqLfo.connect(freqDepth);
  freqDepth.connect(siren.frequency);
  sirenFilter.type='bandpass';
  sirenFilter.frequency.value=level>=5?760:680;
  sirenFilter.Q.value=1.2;
  sirenGain.gain.value=level>=5?.20:.12;
  siren.connect(sirenFilter);
  sirenFilter.connect(sirenGain);
  sirenGain.connect(bus);
  siren.start();
  freqLfo.start();
  bedNodes.push(siren,sirenGain,sirenFilter,freqLfo,freqDepth);

  // Fast metallic modulation makes it feel like a real alarm annunciator.
  if(level>=4){
    const carrier=ctx.createOscillator();
    const metalGain=ctx.createGain();
    const ampLfo=ctx.createOscillator();
    const ampDepth=ctx.createGain();
    carrier.type='square';
    carrier.frequency.value=level>=5?735:620;
    metalGain.gain.value=0.0;
    ampLfo.type='square';
    ampLfo.frequency.value=level>=5?4.6:3.1;
    ampDepth.gain.value=level>=5?.055:.032;
    ampLfo.connect(ampDepth);
    ampDepth.connect(metalGain.gain);
    carrier.connect(metalGain);
    metalGain.connect(bus);
    carrier.start();
    ampLfo.start();
    bedNodes.push(carrier,metalGain,ampLfo,ampDepth);
  }

  // Filtered machinery-noise layer.
  const noise=makeNoiseLoop();
  const nf=ctx.createBiquadFilter();
  const ng=ctx.createGain();
  nf.type='bandpass';
  nf.frequency.value=level>=5?980:760;
  nf.Q.value=2.1;
  ng.gain.value=level>=5?.065:.035;
  noise.connect(nf);
  nf.connect(ng);
  ng.connect(bus);
  noise.start();
  bedNodes.push(noise,nf,ng);
}

function syncEmergencyBed(){
  if(!active||!enabled||!armed||stage<3){
    stopBed();
    return;
  }
  startEmergencyBed(stage);
}

function pattern(level){
  if(!armed||!enabled||!active)return;
  const t=ctx.currentTime+.025;

  switch(level){
    case 0:
      // Engineering pre-alarm: clear but unmistakable.
      impact(t,.075);
      horn(440,t+.03,.25,.085);
      tone(660,t+.07,.21,.065,'triangle');
      horn(554.37,t+.42,.27,.075);
      tone(830.61,t+.46,.21,.055,'triangle');
      break;

    case 1:
      // Control instability: three authoritative horn strikes.
      impact(t,.11);
      horn(370,t,.34,.12);
      horn(494,t+.38,.32,.11);
      horn(370,t+.76,.34,.12);
      sweep(740,330,t+.80,.34,.055);
      break;

    case 2:
      // Process cascade: dual klaxon with falling annunciator sweep.
      impact(t,.145);
      horn(330,t,.42,.145);
      tone(660,t+.02,.34,.065,'square');
      horn(440,t+.46,.39,.135);
      tone(880,t+.48,.30,.055,'triangle');
      noiseBurst(t+.43,.18,.045,1050);
      sweep(1180,280,t+.88,.48,.09);
      break;

    case 3:
      // Metropolitan propagation: urgent asymmetric master alarm.
      impact(t,.18);
      horn(294,t,.46,.16);
      horn(392,t+.31,.42,.15);
      horn(523,t+.64,.38,.14);
      noiseBurst(t+.28,.18,.055,860);
      sweep(980,245,t+.94,.52,.105);
      break;

    case 4:
      // Systemic failure: heavy klaxon over continuous siren bed.
      impact(t,.21);
      horn(262,t,.48,.18);
      horn(349,t+.28,.46,.17);
      horn(466,t+.56,.43,.16);
      tone(698,t+.58,.37,.075,'square');
      sweep(1250,220,t+.92,.56,.12);
      noiseBurst(t+.16,.25,.065,720);
      break;

    default:
      // Catastrophic: master emergency signature.
      impact(t,.24);
      horn(220,t,.52,.20);
      horn(330,t+.21,.48,.19);
      horn(440,t+.43,.45,.18);
      horn(587,t+.65,.41,.17);
      tone(880,t+.67,.34,.085,'square');
      noiseBurst(t+.10,.31,.075,620);
      sweep(1450,190,t+.94,.62,.14);
      impact(t+1.18,.17);
      break;
  }
}

function intervalFor(level){
  return [3600,2700,2050,1600,1250,1050][Math.max(0,Math.min(5,level))];
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
  else loopTimer=setTimeout(run,260);
}

function transition(level){
  if(!armed||!enabled)return;
  const t=ctx.currentTime+.015;
  impact(t,.12+level*.018);
  sweep(260+level*38,780+level*95,t+.04,.32,.065+level*.008,'sawtooth');
  tone(392+level*36,t+.09,.24,.065,'square');
}

function setStage(nextStage,isActive=true){
  const ns=Math.max(0,Math.min(5,Number(nextStage)||0));
  const changed=ns!==stage||isActive!==active;
  stage=ns;
  active=!!isActive;

  if(!active){
    clearLoop();
    stopBed();
    return;
  }

  if(changed&&armed&&enabled){
    transition(stage);
    syncEmergencyBed();
    scheduleStage(false);
  }
}

function silence(){
  enabled=false;
  sessionStorage.setItem('INTERAFAS_AUDIO','0');
  clearLoop();
  stopBed();
  updateButton();
}

async function enable(){
  enabled=true;
  sessionStorage.setItem('INTERAFAS_AUDIO','1');
  await arm();
  updateButton();
  if(active&&armed){
    syncEmergencyBed();
    scheduleStage(true);
  }
}

function toggle(){
  if(enabled&&armed)silence();
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
    btn.title='Industrial master alarm enabled — click to silence';
  }else{
    btn.textContent='ЗВУК · НАЖАТЬ';
    btn.title='Click once to arm industrial master alarm';
  }
}

function gestureArm(){
  if(!enabled||armed)return;
  arm();
}

window.INTERAFAS_AUDIO={
  setStage,
  toggle,
  enable,
  silence,
  arm,
  get enabled(){return enabled},
  get armed(){return armed}
};

document.addEventListener('DOMContentLoaded',()=>{
  updateButton();

  const btn=document.getElementById('hmi-audio-toggle');
  if(btn)btn.addEventListener('click',e=>{
    e.preventDefault();
    e.stopPropagation();
    toggle();
  });

  document.addEventListener('pointerdown',gestureArm,{passive:true});
  document.addEventListener('keydown',gestureArm,{passive:true});

  if(enabled)arm();
});
})();