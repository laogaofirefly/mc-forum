@extends('layouts.app')
@section('title', '在线地图')
@section('content')
<div class="space-y-5">
 <section class="card border-0 bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 p-5 text-white sm:p-7"><div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><p class="text-sm font-semibold tracking-wider text-emerald-100">DYNMAP · NATIVE MAP</p><h1 class="mt-1 text-2xl font-bold sm:text-3xl">服务器在线地图</h1><p class="mt-2 text-emerald-50">拖动浏览，滚轮缩放；地图瓦片经论坛同源接口安全加载。</p></div><a href="{{ $dynmapUrl }}" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-white px-4 py-2.5 text-center font-semibold text-emerald-700">完整 Dynmap ↗</a></div></section>
 <section class="card overflow-hidden"><div class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-4 py-3"><select id="worldSelect" class="rounded-lg border-slate-200 text-sm"><option>加载世界中…</option></select><select id="mapSelect" class="rounded-lg border-slate-200 text-sm"><option>默认图层</option></select><span id="mapHint" class="text-sm text-slate-500">准备地图…</span><button id="homeMap" class="ml-auto rounded-lg bg-primary-50 px-3 py-2 text-sm font-medium text-primary-700">回到中心</button></div><div id="mapViewport" class="relative h-[70vh] min-h-[520px] cursor-grab overflow-hidden bg-slate-900 select-none"><div id="tileLayer" class="absolute inset-0 origin-center"></div><div id="mapLoading" class="absolute inset-0 flex items-center justify-center bg-slate-900/70 text-white">正在加载地图…</div><div class="absolute bottom-3 left-3 rounded-lg bg-slate-950/70 px-3 py-2 text-xs text-white">拖动移动 · 滚轮缩放</div></div></section>
</div>
<style>#tileLayer{transform-origin:0 0}.dyn-tile{position:absolute;width:128px;height:128px;image-rendering:auto}</style>
<script>
(() => {
 const dataUrl='{{ route('dynmap.data') }}', configScriptUrl='{{ route('dynmap.config-script') }}', tileBase='{{ url('/map/tile') }}';
 const viewport=document.getElementById('mapViewport'),layer=document.getElementById('tileLayer'),loading=document.getElementById('mapLoading'),worldSel=document.getElementById('worldSelect'),mapSel=document.getElementById('mapSelect'),hint=document.getElementById('mapHint');
 let worlds=[],currentWorld=null,currentMap=null,state={x:0,y:0,scale:1},drag=null;
 const safe=s=>encodeURIComponent(String(s||''));
 const getWorlds=d=>{
  // 兼容 Dynmap 版本的 worlds、world、configuration.worlds 等不同结构。
  const normalize=s=>Array.isArray(s)?s:(s&&typeof s==='object'?Object.entries(s).map(([name,v])=>({name,...(v||{})})):[]);
  const direct=normalize(d?.worlds||d?.world||d?.configuration?.worlds||d?.config?.worlds||d?.data?.worlds);
  if(direct.length)return direct;
  // config.js 有时将世界信息包在额外对象中：递归寻找含 maps/map 的世界集合。
  const seen=new Set();
  const search=value=>{if(!value||typeof value!=='object'||seen.has(value))return [];seen.add(value);if(Array.isArray(value)){if(value.some(v=>v&&typeof v==='object'&&(v.maps||v.map||v.name||v.id)))return value;for(const v of value){const found=search(v);if(found.length)return found}}else{for(const v of Object.values(value)){const found=search(v);if(found.length)return found}}return []};
  return normalize(search(d));
};
 const mapsOf=w=>Array.isArray(w?.maps)?w.maps:(Array.isArray(w?.map)?w.map:[]);
 function tileUrl(tx,ty){const w=currentWorld.name||currentWorld.id,m=currentMap.prefix||currentMap.name||'flat';const path=`${tx>=0?'':'-'}${Math.abs(tx)}_${ty>=0?'':'-'}${Math.abs(ty)}.png`;return `${tileBase}/${safe(w)}/${safe(m)}/${path}`}
 function render(){if(!currentWorld||!currentMap)return;layer.innerHTML='';const size=128,range=7;for(let y=-range;y<=range;y++)for(let x=-range;x<=range;x++){const img=new Image();img.className='dyn-tile';img.style.left=(x*size)+'px';img.style.top=(y*size)+'px';img.src=tileUrl(x,y);img.onerror=()=>img.remove();layer.appendChild(img)}apply();loading.classList.add('hidden');hint.textContent=`${currentWorld.title||currentWorld.name} · ${currentMap.title||currentMap.name||'默认图层'}`}
 function apply(){layer.style.transform=`translate(${state.x}px,${state.y}px) scale(${state.scale})`;}
 function center(){state={x:viewport.clientWidth/2,y:viewport.clientHeight/2,scale:1};apply()}
 function choose(){currentWorld=worlds[worldSel.value];const maps=mapsOf(currentWorld);mapSel.innerHTML=maps.map((m,i)=>`<option value="${i}">${m.title||m.name||m.prefix||'默认图层'}</option>`).join('');currentMap=maps[0]||{name:'flat',prefix:'flat'};center();render()}
 async function boot(){try{let data;const r=await fetch(dataUrl+'?_='+Date.now(),{cache:'no-store'}),p=await r.json();if(r.ok&&p.ok)data=p.data;else{await new Promise((resolve,reject)=>{const s=document.createElement('script');s.src=configScriptUrl+'?_='+Date.now();s.onload=resolve;s.onerror=()=>reject(Error('Dynmap config.js 加载失败'));document.head.appendChild(s)});data=window.config||window.dynmapconfig||window.DynmapConfig;if(!data||!getWorlds(data).length){for(const key of Object.keys(window)){try{const candidate=window[key];if(candidate&&typeof candidate==='object'&&getWorlds(candidate).length){data=candidate;break}}catch(_){}}}if(!data)throw Error(p.message||'Dynmap 配置数据读取失败')}worlds=getWorlds(data);if(!worlds.length)throw Error('Dynmap 未返回世界数据');worldSel.innerHTML=worlds.map((w,i)=>`<option value="${i}">${w.title||w.name||w.id||'世界'}</option>`).join('');choose()}catch(e){loading.textContent=e.message;hint.textContent='连接失败'}}
 worldSel.onchange=choose;mapSel.onchange=()=>{currentMap=mapsOf(currentWorld)[mapSel.value]||currentMap;center();render()};document.getElementById('homeMap').onclick=center;
 viewport.addEventListener('pointerdown',e=>{drag={x:e.clientX,y:e.clientY,ox:state.x,oy:state.y};viewport.setPointerCapture(e.pointerId);viewport.classList.replace('cursor-grab','cursor-grabbing')});viewport.addEventListener('pointermove',e=>{if(!drag)return;state.x=drag.ox+e.clientX-drag.x;state.y=drag.oy+e.clientY-drag.y;apply()});viewport.addEventListener('pointerup',()=>{drag=null;viewport.classList.replace('cursor-grabbing','cursor-grab')});viewport.addEventListener('wheel',e=>{e.preventDefault();const before=state.scale,stateNew=Math.max(.4,Math.min(3,before*(e.deltaY<0?1.15:.87))),rect=viewport.getBoundingClientRect(),mx=e.clientX-rect.left,my=e.clientY-rect.top;state.x=mx-(mx-state.x)*(stateNew/before);state.y=my-(my-state.y)*(stateNew/before);state.scale=stateNew;apply()},{passive:false});window.addEventListener('resize',center);boot();
})();
</script>
@endsection