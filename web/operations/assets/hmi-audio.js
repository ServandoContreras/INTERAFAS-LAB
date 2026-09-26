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

function ensureContext(){
  if(ctx)return ctx;

  ctx=new AudioCtx();

  master=ctx.createGain();
  master.gain.value=.58;

  compressor=ctx.createDynamicsCompressor();
  compressor.threshold.value=-10;
  compressor.knee.value=6;
  compressor.ratio.value=6;
  compressor.attack.value=.002;
  compressor.release.value=.12;

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

function gainEnvelope(at,duration,peak){
  const g=ctx.createGain();
  g.gain.setValueAtTime(.0001,at);
  g.gain.linearRampToValueAtTime(peak,at+.008);
  g.gain.setValueAtTime(peak,Math.max(at+.008,at+duration-.045));
  g.gain.exponentialRampToValueAtTime(.0001,at+duration);
  g.connect(master);
  return g;
}

function voice(freq,at,duration=.18,peak=.17,type='square'){
  if(!armed||!active)return;

  const o=ctx.createOscillator();
  const filter=ctx.createBiquadFilter();
  const g=gainEnvelope(at,duration,peak);

  o.type=type;
  o.frequency.setValueAtTime(freq,at);

  filter.type='bandpass';
  filter.frequency.value=freq;
  filter.Q.value=.72;

  o.connect(filter);
  filter.connect(g);
  o.start(at);
  o.stop(at+duration+.02);
}

function emergencyPattern(level){
  if(!armed||!active)return;

  const t=ctx.currentTime+.004;
  const s=Math.max(0,Math.min(5,level));
  const shift=s*16;

  /*
   * Public-warning style cadence:
   * 3 short high alerts -> 1 lower acknowledgement ->
   * rapid alternating pair. Clean and intentionally piercing.
   */
  voice(920+shift,t,.17,.17,'square');
  voice(920+shift,t+.23,.17,.17,'square');
  voice(920+shift,t+.46,.17,.17,'square');

  voice(690+shift,t+.72,.30,.19,'square');

  voice(840+shift,t+1.08,.14,.16,'square');
  voice(1110+shift,t+1.24,.14,.18,'square');
  voice(840+shift,t+1.40,.14,.16,'square');
  voice(1110+shift,t+1.56,.20,.18,'square');

  // Light harmonic only for intelligibility over laptop speakers.
  voice(1680+shift,t+1.24,.10,.035,'sine');
  voice(1680+shift,t+1.56,.12,.038,'sine');
}

function intervalFor(level){
  return [1940,1870,1800,1730,1660,1590][Math.max(0,Math.min(5,level))];
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
    emergencyPattern(stage);
    timer=setTimeout(run,intervalFor(stage));
  };

  if(immediate)run();
  else timer=setTimeout(run,40);
}

function setStage(nextStage,isActive=true){
  const next=Math.max(0,Math.min(5,Number(nextStage)||0));
  const changed=next!==stage||!!isActive!==active;

  stage=next;
  active=!!isActive;

  if(!active){
    stopLoop();
    return;
  }

  if(changed&&armed)start(true);
}

function stop(){
  active=false;
  stage=-1;
  stopLoop();
}

window.INTERAFAS_AUDIO={
  arm,
  setStage,
  stop,
  get armed(){return armed},
  get state(){return ctx?ctx.state:'not-created'}
};
})();