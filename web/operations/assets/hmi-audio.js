(()=>{
'use strict';

const AudioCtx=window.AudioContext||window.webkitAudioContext;
if(!AudioCtx)return;

let ctx=null;
let master=null;
let limiter=null;
let compressor=null;
let output=null;
let enabled=sessionStorage.getItem('INTERAFAS_AUDIO')!=='0';
let armed=false;
let stage=-1;
let active=false;
let loopTimer=null;
let bedNodes=[];
let testTimer=null;

function makeSoftClipCurve(amount=4){
  const n=65536;
  const curve=new Float32Array(n);
  for(let i=0;i<n;i++){
    const x=(i*2/(n-1))-1;
    curve[i]=Math.tanh(amount*x)/Math.tanh(amount);
  }
  return curve;
}

function ensureContext(){
  if(ctx)return ctx;
  ctx=new AudioCtx();

  /*
   * Internal alarm gain intentionally runs at ~2x nominal level.
   * The waveshaper + compressor act as a limiter so the signal is dense
   * rather than simply clipping. Physical loudness is still governed by
   * browser/OS/device volume.
   */
  master=ctx.createGain();
  master.gain.value=2.0;

  limiter=ctx.createWaveShaper();
  limiter.curve=makeSoftClipCurve(3.2);
  limiter.oversample='4x';

  compressor=ctx.createDynamicsCompressor();
  compressor.threshold.value=-8;
  compressor.knee.value=3;
  compressor.ratio.value=20;
  compressor.attack.value=.001;
  compressor.release.value=.18;

  output=ctx.createGain();
  output.gain.value=.98;

  master.connect(limiter);
  limiter.connect(compressor);
  compressor.connect(output);
  output.connect(ctx.destination);
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

function env(at,duration,peak=.12,attack=.008,release=.08){
  const g=ctx.createGain();
  const hold=Math.max(at+attack,at+duration-release);
  g.gain.setValueAtTime(.0001,at);
  g.gain.exponentialRampToValueAtTime(Math.max(.001,peak),at+attack);
  g.gain.setValueAtTime(Math.max(.001,peak),hold);
  g.gain.exponentialRampToValueAtTime(.0001,at+duration);
  g.connect(master);
  return g;
}

function tone(freq,at,duration=.22,peak=.11,type='sine',detune=0){
  if(!armed||!enabled)return;
  const o=ctx.createOscillator();
  const g=env(at,duration,peak);
  o.type=type;
  o.frequency.setValueAtTime(freq,at);
  o.detune.setValueAtTime(detune,at);
  o.connect(g);
  o.start(at);
  o.stop(at+duration+.03);
}

function warHorn(freq,at,duration=.55,peak=.13){
  tone(freq,at,duration,peak,'sawtooth',-7);
  tone(freq*1.012,at,duration,peak*.78,'sawtooth',7);
  tone(freq*2.01,at+.01,duration*.88,peak*.22,'square');
}

function impact(at,peak=.15){
  tone(42,at,.55,peak,'sine');
  tone(84,at+.005,.38,peak*.48,'triangle');
  tone(126,at+.01,.24,peak*.18,'sawtooth');
}

function sweep(from,to,at,duration=.7,peak=.09,type='sawtooth'){
  if(!armed||!enabled)return;
  const o=ctx.createOscillator();
  const f=ctx.createBiquadFilter();
  const g=env(at,duration,peak,.02,.09);
  o.type=type;
  o.frequency.setValueAtTime(Math.max(1,from),at);
  o.frequency.exponentialRampToValueAtTime(Math.max(1,to),at+duration);
  f.type='bandpass';
  f.frequency.value=900;
  f.Q.value=.8;
  o.connect(f);
  f.connect(g);
  o.start(at);
  o.stop(at+duration+.04);
}

function stopBed(){
  bedNodes.forEach(n=>{
    try{if(typeof n.stop==='function')n.stop()}catch(e){}
    try{n.disconnect()}catch(e){}
  });
  bedNodes=[];
}

function continuousAirRaid(level){
  stopBed();
  if(!armed||!enabled||!active)return;

  const t=ctx.currentTime;
  const bus=ctx.createGain();
  const levelGain=[.15,.17,.19,.22,.25,.28][Math.max(0,Math.min(5,level))];
  bus.gain.setValueAtTime(.0001,t);
  bus.gain.exponentialRampToValueAtTime(levelGain,t+.06);
  bus.connect(master);
  bedNodes.push(bus);

  // Main civil-defense / air-raid wail: slow rising/falling sweep.
  const siren=ctx.createOscillator();
  const sirenFilter=ctx.createBiquadFilter();
  const sirenGain=ctx.createGain();
  const lfo=ctx.createOscillator();
  const depth=ctx.createGain();

  siren.type='sawtooth';
  siren.frequency.value=590+level*18;
  lfo.type='sine';
  lfo.frequency.value=.20+level*.018;
  depth.gain.value=330+level*28;

  lfo.connect(depth);
  depth.connect(siren.frequency);

  sirenFilter.type='bandpass';
  sirenFilter.frequency.value=790;
  sirenFilter.Q.value=.75;
  sirenGain.gain.value=.21+level*.012;

  siren.connect(sirenFilter);
  sirenFilter.connect(sirenGain);
  sirenGain.connect(bus);
  siren.start();
  lfo.start();

  bedNodes.push(siren,sirenFilter,sirenGain,lfo,depth);

  // Second slightly detuned siren produces the unsettling "multiple sirens" effect.
  const siren2=ctx.createOscillator();
  const siren2Gain=ctx.createGain();
  const lfo2=ctx.createOscillator();
  const depth2=ctx.createGain();

  siren2.type='square';
  siren2.frequency.value=430+level*14;
  lfo2.type='sine';
  lfo2.frequency.value=.235+level*.015;
  depth2.gain.value=205+level*20;

  lfo2.connect(depth2);
  depth2.connect(siren2.frequency);
  siren2Gain.gain.value=.065+level*.008;
  siren2.connect(siren2Gain);
  siren2Gain.connect(bus);
  siren2.start();
  lfo2.start();

  bedNodes.push(siren2,siren2Gain,lfo2,depth2);

  // Sub-bass machinery / distant blast bed.
  const drone=ctx.createOscillator();
  const drone2=ctx.createOscillator();
  const low=ctx.createBiquadFilter();
  const dg=ctx.createGain();
  drone.type='sawtooth';
  drone2.type='sawtooth';
  drone.frequency.value=46;
  drone2.frequency.value=47.3;
  low.type='lowpass';
  low.frequency.value=170;
  low.Q.value=2;
  dg.gain.value=.10+level*.012;
  drone.connect(low);
  drone2.connect(low);
  low.connect(dg);
  dg.connect(bus);
  drone.start();
  drone2.start();

  bedNodes.push(drone,drone2,low,dg);

  // At critical stages add a rapid mechanical warning chopper.
  if(level>=3){
    const chopper=ctx.createOscillator();
    const cg=ctx.createGain();
    const gate=ctx.createOscillator();
    const gd=ctx.createGain();

    chopper.type='square';
    chopper.frequency.value=720+level*35;
    cg.gain.value=.0001;
    gate.type='square';
    gate.frequency.value=3.2+(level-3)*.65;
    gd.gain.value=.055+level*.006;

    gate.connect(gd);
    gd.connect(cg.gain);
    chopper.connect(cg);
    cg.connect(bus);
    chopper.start();
    gate.start();

    bedNodes.push(chopper,cg,gate,gd);
  }
}

function syncEmergencyBed(){
  if(!active||!enabled||!armed){
    stopBed();
    return;
  }
  continuousAirRaid(stage);
}

function pattern(level){
  if(!armed||!enabled||!active)return;
  const t=ctx.currentTime+.01;

  // Every stage has an immediate "master alarm" hit on top of the continuous siren.
  impact(t,.14+level*.015);

  switch(level){
    case 0:
      warHorn(220,t+.02,.62,.12);
      warHorn(330,t+.36,.55,.11);
      sweep(980,260,t+.72,.72,.075);
      break;
    case 1:
      warHorn(196,t+.02,.68,.13);
      warHorn(294,t+.30,.62,.125);
      warHorn(392,t+.59,.54,.115);
      sweep(1150,240,t+.90,.76,.085);
      break;
    case 2:
      warHorn(174,t+.02,.72,.14);
      warHorn(261,t+.27,.68,.135);
      warHorn(349,t+.54,.62,.13);
      tone(698,t+.60,.45,.065,'square');
      sweep(1320,220,t+.94,.80,.095);
      break;
    case 3:
      warHorn(164,t+.02,.78,.15);
      warHorn(246,t+.24,.72,.145);
      warHorn(329,t+.48,.68,.14);
      warHorn(493,t+.72,.55,.12);
      sweep(1450,205,t+1.00,.84,.105);
      break;
    case 4:
      warHorn(147,t+.02,.84,.16);
      warHorn(220,t+.22,.80,.155);
      warHorn(294,t+.44,.74,.15);
      warHorn(440,t+.66,.66,.135);
      sweep(1580,190,t+1.02,.88,.115);
      impact(t+1.12,.17);
      break;
    default:
      warHorn(130,t+.02,.90,.18);
      warHorn(196,t+.20,.86,.17);
      warHorn(261,t+.40,.82,.165);
      warHorn(392,t+.61,.74,.15);
      warHorn(523,t+.82,.66,.135);
      sweep(1720,175,t+1.05,.95,.13);
      impact(t+1.16,.19);
      impact(t+1.55,.17);
      break;
  }
}

function intervalFor(level){
  return [2800,2450,2100,1750,1450,1200][Math.max(0,Math.min(5,level))];
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
  else run();
}

function transition(level){
  if(!armed||!enabled)return;
  const t=ctx.currentTime+.005;
  impact(t,.16+level*.012);
  sweep(210,920+level*90,t+.01,.58,.09+level*.006);
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
    // No delay: continuous air-raid tone and master hit begin immediately.
    transition(stage);
    syncEmergencyBed();
    scheduleStage(true);
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
  const state=document.getElementById('hmi-audio-state');

  if(btn){
    btn.classList.toggle('audio-muted',!enabled);
    btn.classList.toggle('audio-armed',enabled&&armed);
    btn.classList.toggle('audio-needs-gesture',enabled&&!armed);

    if(!enabled){
      btn.textContent='AUDIO · SILENCIADO';
      btn.title='Emergency siren silenced';
    }else if(armed){
      btn.textContent='AUDIO · ACTIVO';
      btn.title='Air-raid master alarm armed — click to silence';
    }else{
      btn.textContent='ACTIVAR AUDIO';
      btn.title='Click once to arm emergency siren';
    }
  }

  if(state){
    const actual=ctx ? ctx.state : 'not-created';
    state.textContent=!enabled ? 'SILENCIADO' : armed ? 'ACTIVO' : 'BLOQUEADO · '+actual.toUpperCase();
    state.dataset.audioState=!enabled?'muted':armed?'running':'blocked';
  }
}

function gestureArm(){
  if(!enabled||armed)return;
  arm();
}

async function test(){
  const wasActive=active;
  const wasStage=stage;

  enabled=true;
  sessionStorage.setItem('INTERAFAS_AUDIO','1');
  await arm();

  if(!armed){
    updateButton();
    return false;
  }

  if(testTimer){
    clearTimeout(testTimer);
    testTimer=null;
  }

  active=true;
  stage=5;
  transition(5);
  syncEmergencyBed();
  pattern(5);

  if(!wasActive){
    testTimer=setTimeout(()=>{
      clearLoop();
      stopBed();
      active=false;
      stage=-1;
      testTimer=null;
      updateButton();
    },4200);
  }else{
    testTimer=setTimeout(()=>{
      stage=wasStage;
      active=true;
      syncEmergencyBed();
      scheduleStage(true);
      testTimer=null;
    },2500);
  }

  updateButton();
  return true;
}

window.INTERAFAS_AUDIO={
  setStage,
  toggle,
  enable,
  silence,
  arm,
  test,
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

  const testBtn=document.getElementById('hmi-audio-test');
  if(testBtn)testBtn.addEventListener('click',async e=>{
    e.preventDefault();
    e.stopPropagation();
    await test();
  });

  if(ctx)ctx.addEventListener?.('statechange',updateButton);

  document.addEventListener('pointerdown',gestureArm,{passive:true});
  document.addEventListener('keydown',gestureArm,{passive:true});

  if(enabled)arm();
});
})();