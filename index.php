<?php
// vertical_piano_roll.php
// Single-file PHP app that renders a vertical piano roll from a MIDI file.
// Usage: Put this file in a PHP server document root and open in browser.
// The app reads the MIDI file in the browser (no server-side parsing required).
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Piano ScRoll - Vertical Piano Roll</title>
<style>
  :root{--bg:#0b1020;--card:#0f1724;--muted:#9aa4bf;--accent:#8be9fd}
  html,body{height:100%;margin:0;font-family:Inter,ui-sans-serif,system-ui,Segoe UI,Roboto,'Helvetica Neue',Arial}
  body{background:linear-gradient(180deg,#02030a 0%, #071021 60%);color:#e6eef8;display:flex;flex-direction:column}
  header{padding:12px 18px;display:flex;gap:12px;align-items:center}
  .brand{font-weight:600;font-size:18px;color:var(--accent)}
  .controls{display:flex;gap:10px;align-items:center;margin-left:auto}
  .card{background:rgba(255,255,255,0.02);border-radius:10px;padding:10px;box-shadow:0 6px 20px rgba(2,6,23,0.6)}
  #wrap{flex:1;display:flex;flex-direction:column;padding:14px;gap:12px}
  .viewport{flex:1;display:flex;flex-direction:column;gap:6px}
  #rollCanvas{flex:1;border-radius:8px;display:block;width:100%;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.005));cursor:grab}
  #keyboardCanvas{height:120px;border-radius:8px;background:linear-gradient(90deg, rgba(255, 255, 255, 1), rgba(255, 255, 255, 1));}
  label{font-size:13px;color:var(--muted)}
  input[type=range]{width:140px}
  .info{font-size:13px;color:var(--muted);margin-left:8px}
  .footer{font-size:12px;color:var(--muted);padding:8px 14px}
  .btn{background:transparent;border:1px solid rgba(255,255,255,0.06);padding:6px 8px;border-radius:8px;color:var(--muted);cursor:pointer}
  .btn:hover{border-color:var(--accent);color:var(--accent)}
  .filedrop{display:inline-block;padding:8px;border:1px dashed rgba(255,255,255,0.04);border-radius:8px}
  .small{font-size:12px}
</style>
</head>
<body>
<header>
  <div class="brand">Piano ScRoll - Vertical Piano Roll</div>
  <div class="controls">
    <label class="card filedrop">Drop MIDI file or Click to load<input id="midiFileInput" type="file" accept="audio/midi,audio/x-midi,.mid,.midi" style="display:none"></label>
    <button id="zoomIn" class="btn">Zoom+</button>
    <button id="zoomOut" class="btn">Zoom-</button>
    <label class="small">Speed <input id="pixelsPerSec" type="range" min="20" max="1200" value="200"></label>
    <div class="info" id="fileInfo">No file loaded</div>
  </div>
</header>

<div id="wrap">
  <div class="viewport card" style="padding:10px;">
    <canvas id="rollCanvas"></canvas>
    <canvas id="keyboardCanvas"></canvas>
  </div>
  <div class="footer">Click & drag vertically to scroll. Mouse wheel scrolls. The keys light up when notes are active at the current viewport top time. File is parsed client-side.</div>
</div>

<script>
// Minimal MIDI parser (enough to pull note on/off events and tempo)
function readUInt32BE(view, offset){return (view.getUint8(offset)<<24)|(view.getUint8(offset+1)<<16)|(view.getUint8(offset+2)<<8)|(view.getUint8(offset+3));}
function readUInt16BE(view, offset){return (view.getUint8(offset)<<8)|(view.getUint8(offset+1));}
function readVarLen(view, idxObj){var i=0;var v=0;while(true){var b=view.getUint8(idxObj.i++);v=(v<<7)|(b&0x7f);if(!(b&0x80))break;}return v}

function parseMIDI(arrayBuffer){
  const view=new DataView(arrayBuffer);
  let i=0;
  function readChars(n){let s='';for(let k=0;k<n;k++)s+=String.fromCharCode(view.getUint8(i++));return s}
  const header=readChars(4);if(header!=='MThd')throw new Error('Not a MIDI file');
  const headerLen=readUInt32BE(view,i);i+=4;
  const format=readUInt16BE(view,i);i+=2;
  const ntrks=readUInt16BE(view,i);i+=2;
  const division=readUInt16BE(view,i);i+=2; // ticks per beat if positive
  const ticksPerBeat = division & 0x7fff;
  i = 8 + headerLen; // position at end of header chunk

  const notes=[]; // {note, startTicks, endTicks, velocity, channel}
  let tempoMap=[{tick:0, usPerBeat:500000}]; // default 120bpm

  for(let t=0;t<ntrks;t++){
    const chunk=readChars(4);
    if(chunk!=='MTrk') throw new Error('Unexpected chunk: '+chunk);
    const len=readUInt32BE(view,i); i+=4;
    const trackEnd=i+len;
    let tick=0;
    let runningStatus=null;
    while(i<trackEnd){
      const delta = readVarLen(view,{i:i});
      i += (()=>{let count=0; let v=delta; // readVarLen advanced nothing because we passed a copy, so re-read properly
        // Actually readVarLen consumes via object; simpler: implement reading inline
      })();
      // Because of complexity above, reimplement reading within loop instead:
      break;}
  }
  // Because the above inline parsing for tracks is messy, we'll completely reparse using a simpler approach below.
  // Re-implement full parser more robustly:
}

// New parser implementation (single-pass)
function parseMIDI(arrayBuffer){
  const view=new DataView(arrayBuffer);
  let i=0;
  function readChars(n){let s='';for(let k=0;k<n;k++)s+=String.fromCharCode(view.getUint8(i++));return s}
  function readUint32(){const v = (view.getUint8(i)<<24)|(view.getUint8(i+1)<<16)|(view.getUint8(i+2)<<8)|view.getUint8(i+3); i+=4; return v}
  function readUint16(){const v = (view.getUint8(i)<<8)|view.getUint8(i+1); i+=2; return v}
  function readVar(){let v=0;while(true){const b=view.getUint8(i++);v=(v<<7)|(b&0x7f); if(!(b&0x80)) return v;}} 

  const header = readChars(4); if(header!=='MThd') throw new Error('Invalid MIDI');
  const headerLen = readUint32();
  const format = readUint16();
  const ntrks = readUint16();
  const division = readUint16();
  const ticksPerBeat = division;
  // skip rest of header chunk if any
  if(headerLen>6) i += (headerLen-6);

  const allNotes=[]; // across tracks
  const tempoEvents=[{tick:0, usPerBeat:500000}];

  for(let tr=0; tr<ntrks; tr++){
    const chunk = readChars(4); if(chunk!=='MTrk') throw new Error('Missing MTrk');
    const len = readUint32();
    const trackEnd = i + len;
    let tick = 0;
    let lastStatus = null;
    const openNotes = {}; // key: channel_note -> {startTick, velocity}

    while(i < trackEnd){
      const delta = readVar(); tick += delta;
      let statusByte = view.getUint8(i++);
      if(statusByte < 0x80){ // running status: reuse lastStatus and step back one
        i--; statusByte = lastStatus;
      } else {
        lastStatus = statusByte;
      }
      if(statusByte === 0xFF){ // meta
        const metaType = view.getUint8(i++);
        const lenMeta = readVar();
        if(metaType === 0x51){ // tempo
          const b0=view.getUint8(i++), b1=view.getUint8(i++), b2=view.getUint8(i++);
          const us = (b0<<16)|(b1<<8)|b2;
          tempoEvents.push({tick: tick, usPerBeat: us});
          // consume rest if lenMeta > 3
          for(let k=3;k<lenMeta;k++) i++;
        } else {
          i += lenMeta;
        }
      } else if((statusByte & 0xF0) === 0xF0){
        // SysEx or other: skip
        if(statusByte===0xF0 || statusByte===0xF7){ const l = readVar(); i+=l; }
        else { /* ignore */ }
      } else {
        const eventType = statusByte & 0xF0;
        const channel = statusByte & 0x0F;
        if(eventType === 0x90 || eventType === 0x80){
          const note = view.getUint8(i++);
          const vel = view.getUint8(i++);
          const key = channel + ':' + note;
          if(eventType === 0x90 && vel>0){
            // note on
            openNotes[key] = {startTick: tick, velocity: vel, channel: channel, note: note};
          } else {
            // note off
            if(openNotes[key]){
              const n = openNotes[key];
              allNotes.push({note: n.note, startTick: n.startTick, endTick: tick, velocity: n.velocity, channel: n.channel});
              delete openNotes[key];
            } else {
              // unmatched note off — ignore
            }
          }
        } else if(eventType === 0xA0 || eventType===0xB0 || eventType===0xE0){
          // two-data bytes
          i+=2;
        } else if(eventType===0xC0 || eventType===0xD0){
          // one-data byte
          i+=1;
        } else {
          // unknown - attempt to skip gracefully
        }
      }
    }
    // close any leftover notes at trackEnd
    for(const k in openNotes){ const n=openNotes[k]; allNotes.push({note:n.note,startTick:n.startTick,endTick:tick,velocity:n.velocity,channel:n.channel}); }
  }

  // Convert ticks -> seconds using tempo map
  // Build a function that maps tick to seconds by walking tempo events
  tempoEvents.sort((a,b)=>a.tick-b.tick);
  function tickToSec(tick){
    let sec = 0;
    for(let j=0;j<tempoEvents.length;j++){
      const cur = tempoEvents[j];
      const next = tempoEvents[j+1];
      const startTick = cur.tick;
      const endTick = next? next.tick : Infinity;
      if(tick >= endTick){
        const ticksSpan = endTick - startTick;
        sec += (ticksSpan * (cur.usPerBeat/1000000)) / ticksPerBeat;
      } else {
        const ticksSpan = tick - startTick;
        sec += (ticksSpan * (cur.usPerBeat/1000000)) / ticksPerBeat;
        break;
      }
    }
    return sec;
  }

  const notes = allNotes.map(n=>({note:n.note, start:tickToSec(n.startTick), end:tickToSec(n.endTick), velocity:n.velocity, channel:n.channel}));
  const totalEnd = notes.reduce((m,n)=>Math.max(m,n.end),0);
  return {notes, duration: totalEnd, ticksPerBeat};
}

// Piano roll rendering and UI
const rollCanvas = document.getElementById('rollCanvas');
const kbdCanvas = document.getElementById('keyboardCanvas');
const fileInput = document.getElementById('midiFileInput');
const fileInfo = document.getElementById('fileInfo');
const pixelsPerSecInput = document.getElementById('pixelsPerSec');
let pixelsPerSecond = Number(pixelsPerSecInput.value); // controls vertical scale
let notesData = {notes:[], duration:0};
let scrollSec = 0;
let minNote = 21, maxNote = 108; // piano range A0-C8
let drag = false, lastY=0;
let colorCache = {};

function resize(){
  rollCanvas.width = rollCanvas.clientWidth * devicePixelRatio;
  rollCanvas.height = (window.innerHeight - 220) * devicePixelRatio; // allow flexible
  rollCanvas.style.height = ((window.innerHeight - 220)) + 'px';
  kbdCanvas.width = rollCanvas.width;
  kbdCanvas.height = 120 * devicePixelRatio;
  render();
}
window.addEventListener('resize', resize);

pixelsPerSecInput.addEventListener('input', ()=>{pixelsPerSecond = Number(pixelsPerSecInput.value); render();});

// file handling via drag/drop and input
['dragenter','dragover'].forEach(e=>{document.addEventListener(e, ev=>{ev.preventDefault(); ev.dataTransfer && (ev.dataTransfer.dropEffect = 'copy');});});
['dragleave','drop'].forEach(e=>{document.addEventListener(e, ev=>{if(e==='drop'){ev.preventDefault(); handleDrop(ev.dataTransfer.files);} });});
fileInput.addEventListener('change', ()=>{handleDrop(fileInput.files);});

function handleDrop(files){ if(!files || files.length===0) return; const f=files[0]; fileInfo.textContent = 'Loading: '+f.name; const reader = new FileReader(); reader.onload = (e)=>{try{notesData = parseMIDI(e.target.result); fileInfo.textContent = f.name + ' — duration: ' + notesData.duration.toFixed(2) + 's'; scrollSec = 0; render();}catch(err){fileInfo.textContent = 'Error parsing MIDI: '+err.message}}; reader.readAsArrayBuffer(f);} 

// mouse scroll/drag
rollCanvas.addEventListener('mousedown', (e)=>{drag=true; lastY = e.clientY; rollCanvas.style.cursor='grabbing';});
window.addEventListener('mouseup', ()=>{drag=false; rollCanvas.style.cursor='grab';});
window.addEventListener('mousemove', (e)=>{if(!drag) return; const dy = e.clientY - lastY; lastY = e.clientY; scrollSec += dy / pixelsPerSecond * -1; clampScroll(); render();});
rollCanvas.addEventListener('wheel', (e)=>{e.preventDefault(); scrollSec += e.deltaY / pixelsPerSecond; clampScroll(); render();},{passive:false});

function clampScroll(){ if(!notesData || !notesData.duration) return; scrollSec = Math.max(0, Math.min(notesData.duration - (rollCanvas.height/devicePixelRatio) / pixelsPerSecond, scrollSec)); }

function noteToX(note){ const totalKeys = maxNote - minNote + 1; const w = rollCanvas.width / devicePixelRatio; const keyIndex = note - minNote; const keyWidth = w / totalKeys; return keyIndex * keyWidth; }
function noteToWidth(noteLengthSec){ const w = rollCanvas.width / devicePixelRatio; return noteLengthSec * pixelsPerSecond * (w/ w); }

function noteColor(note, channel=0){ const key = note+':'+channel; if(colorCache[key]) return colorCache[key]; // assign color based on pitch and channel
  const hue = (note - minNote) / (maxNote - minNote) * 360; const sat = 70; const light = 55 - (channel*5);
  const col = `hsl(${Math.round(hue)}, ${sat}%, ${light}%)`;
  colorCache[key]=col;return col; }

function render(){
  const ctx = rollCanvas.getContext('2d'); const dpr = devicePixelRatio;
  const w = rollCanvas.width; const h = rollCanvas.height;
  ctx.clearRect(0,0,w,h);
  // background grid of notes (vertical piano lanes)
  const totalKeys = maxNote - minNote + 1; const keyW = (w/dpr)/totalKeys;
  // draw horizontal time ticks
  const visibleSec = (h/dpr)/pixelsPerSecond;
  const startSec = scrollSec; const endSec = startSec + visibleSec;

  // subtle vertical grid for keys
  ctx.save(); ctx.scale(dpr,dpr);
  for(let k=0;k<totalKeys;k++){
    ctx.fillStyle = (k%12===1 || k%12===3 || k%12===6 || k%12===8 || k%12===10) ? 'rgba(228, 224, 224, 0.15)' : 'rgba(255,255,255,0.01)';
    ctx.fillRect(k*keyW,0,keyW, h/dpr);
  }

  // draw time tick lines and labels
  ctx.fillStyle = 'rgba(255,255,255,0.06)';
  ctx.font = '12px system-ui'; ctx.textAlign='left'; ctx.textBaseline='top';
  const niceIntervalSec = chooseTickInterval(visibleSec);
  const firstTick = Math.floor(startSec / niceIntervalSec) * niceIntervalSec;
  for(let t = firstTick; t <= endSec; t += niceIntervalSec){ const y = (t - startSec) * pixelsPerSecond; ctx.fillRect(0, y-0.5, w/dpr, 1); ctx.fillStyle='rgba(255,255,255,0.4)'; ctx.fillText(formatTime(t), 6, y+4); }

  // draw notes
  for(const n of notesData.notes){ if(n.end < startSec || n.start > endSec) continue; const topY = (n.start - startSec) * pixelsPerSecond; const bottomY = (n.end - startSec) * pixelsPerSecond; const x = noteToX(n.note); const width = keyW; const height = Math.max(3, bottomY - topY);
    ctx.fillStyle = noteColor(n.note, n.channel);
    ctx.globalAlpha = 0.95;
    roundRect(ctx, x+2, topY+2, width-4, height-4, 4);
    ctx.fill();
    // edge glow
    ctx.globalAlpha = 0.12; ctx.fillRect(x+2, topY+2, width-4, 6);
  }

  // draw play head at top (the 'moment')
  ctx.globalAlpha=1; ctx.fillStyle = 'rgba(255,255,255,0.06)'; ctx.fillRect(0,0,w/dpr,2);
  ctx.restore();

  // render keyboard
  renderKeyboard();
}
function renderKeyboard() {
  const ctx = kbdCanvas.getContext("2d");
  const dpr = devicePixelRatio;
  ctx.clearRect(0, 0, kbdCanvas.width, kbdCanvas.height);
  ctx.save();
  ctx.scale(dpr, dpr);

  const w = kbdCanvas.width / dpr;
  const h = kbdCanvas.height / dpr;
  const totalKeys = maxNote - minNote + 1;

  // compute positions of white keys
  const whiteNotes = [];
  for (let n = minNote; n <= maxNote; n++) {
    if (!isBlackKey(n)) whiteNotes.push(n);
  }
  const whiteKeyCount = whiteNotes.length;
  const whiteKeyWidth = w / whiteKeyCount;

  // map from MIDI note to white-key x position
  const keyX = {};
  let xPos = 0;
  for (let n = minNote; n <= maxNote; n++) {
    if (!isBlackKey(n)) {
      keyX[n] = xPos;
      xPos += whiteKeyWidth;
    }
  }

  // --- draw white keys ---
  for (let n = minNote; n <= maxNote; n++) {
    if (!isBlackKey(n)) {
      const x = keyX[n];
      ctx.fillStyle = "#e5e7eb"; // light gray
      ctx.fillRect(x, 0, whiteKeyWidth, h);
      ctx.strokeStyle = "#9ca3af";
      ctx.strokeRect(x, 0, whiteKeyWidth, h);
    }
  }

  // --- draw black keys ---
  const blackKeyHeight = h * 0.62;
  const blackKeyWidth = whiteKeyWidth * 0.65;
  for (let n = minNote; n <= maxNote; n++) {
    if (isBlackKey(n)) {
      // black keys sit between specific white keys
      const prevWhite = n - 1;
      const nextWhite = n + 1;
      if (keyX[prevWhite] !== undefined && keyX[nextWhite] !== undefined) {
        const x =
          (keyX[prevWhite] + keyX[nextWhite] + whiteKeyWidth) / 2 -
          blackKeyWidth / 2;
        ctx.fillStyle = "#111827";
        ctx.fillRect(x, 0, blackKeyWidth, blackKeyHeight);
        ctx.strokeStyle = "#000";
        ctx.strokeRect(x, 0, blackKeyWidth, blackKeyHeight);
      }
    }
  }

  // --- label white keys ---
  ctx.fillStyle = "rgba(0,0,0,0.6)";
  ctx.font = "11px system-ui";
  ctx.textAlign = "center";
  for (let n = minNote; n <= maxNote; n++) {
    if (!isBlackKey(n)) {
      const x = keyX[n] + whiteKeyWidth / 2;
      const name = noteName(n);
      ctx.fillText(name, x, h - 6);
    }
  }

  // --- highlight active notes ---
  const t = scrollSec;
  const active = new Set();
  for (const n of notesData.notes) {
    if (n.start <= t && n.end >= t) {
      active.add(n.note);
    }
  }

  for (const note of active) {
    const isBlack = isBlackKey(note);
    ctx.globalAlpha = 0.85;
    ctx.fillStyle = noteColor(note, 0);
    if (isBlack) {
      // find its position again
      const prevWhite = note - 1;
      const nextWhite = note + 1;
      if (keyX[prevWhite] !== undefined && keyX[nextWhite] !== undefined) {
        const x =
          (keyX[prevWhite] + keyX[nextWhite] + whiteKeyWidth) / 2 -
          blackKeyWidth / 2;
        ctx.fillRect(x, 0, blackKeyWidth, blackKeyHeight);
      }
    } else {
      const x = keyX[note];
      ctx.fillRect(x, 0, whiteKeyWidth, h);
    }
  }

  ctx.globalAlpha = 1;
  ctx.restore();
}


function isBlackKey(note){ const n = note % 12; return [1,3,6,8,10].includes(n); }
function noteName(n){ const names=['C','C#','D','D#','E','F','F#','G','G#','A','A#','B']; const oct = Math.floor(n/12)-1; return names[n%12]+oct; }
function roundRect(ctx,x,y,w,h,r){ if(w<0||h<0)return; ctx.beginPath(); ctx.moveTo(x+r,y); ctx.arcTo(x+w,y,x+w,y+h,r); ctx.arcTo(x+w,y+h,x,y+h,r); ctx.arcTo(x,y+h,x,y,r); ctx.arcTo(x,y,x+w,y,r); ctx.closePath(); }

function formatTime(s){ const mm = Math.floor(s/60); const ss = Math.floor(s%60); const ms = Math.floor((s - Math.floor(s))*1000); return mm+':'+String(ss).padStart(2,'0')+'.'+String(ms).padStart(3,'0'); }

function chooseTickInterval(visibleSec){ // choose a friendly interval for time ticks
  const nice = [0.1,0.2,0.5,1,2,5,10,15,30,60,120]; for(let n of nice){ if(visibleSec / n <= 12) return n; } return 60; }

// zoom controls
document.getElementById('zoomIn').addEventListener('click', ()=>{ pixelsPerSecond = Math.min(2000, pixelsPerSecond*1.3); pixelsPerSecInput.value = pixelsPerSecond; render(); });
document.getElementById('zoomOut').addEventListener('click', ()=>{ pixelsPerSecond = Math.max(10, pixelsPerSecond/1.3); pixelsPerSecInput.value = pixelsPerSecond; render(); });

// initial sizing
setTimeout(resize,100);

</script>
</body>
</html>
