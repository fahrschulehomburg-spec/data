<?php
/**
 * Plugin Name: TheorieRoute – Schritt-Animationen
 * Description: Abspielbare Verkehrs-Animationen (Play/Pause/Schritt) für Lektionen. Inhalt: <div class="tr-play" data-anim="ID"> mit Beschriftungen je Sprache.
 */
if (!defined('ABSPATH')) exit;
add_action('wp_footer', function () {
  if (!is_singular(['lesson', 'topics', 'courses', 'tutor_quiz']) && empty($_GET['trplay'])) return;
  echo '<style id="tr-play-css">' . <<<'CSS'
.tr-play{--road:#4a505a;--grass:#dfe6d3;--mark:#f4f1e8;background:var(--grass);border:1px solid #dedbd2;border-radius:12px;overflow:hidden;margin:14px 0 10px;max-width:640px;font-family:inherit}
.tr-play svg{display:block;width:100%;height:auto}
.tr-play .tp-scenes{display:flex;gap:6px;flex-wrap:wrap;padding:8px 8px 0}
.tr-play .tp-scenes button{flex:1 1 auto;border:1px solid #c9c6bc;background:#fff;color:#16202B;border-radius:8px;padding:6px 10px;font-size:.9em;cursor:pointer}
.tr-play .tp-scenes button.on{background:#16202B;color:#fff;border-color:#16202B}
.tr-play .tp-bar{display:flex;align-items:center;gap:6px;padding:8px;background:#fff;border-top:1px solid #dedbd2}
.tr-play .tp-bar button{border:0;background:#16202B;color:#fff;border-radius:8px;min-width:40px;height:36px;font-size:16px;cursor:pointer}
.tr-play .tp-bar button.tp-play{background:#F5B700;color:#16202B;min-width:52px}
.tr-play .tp-dots{display:flex;gap:4px;margin-inline-start:auto}
.tr-play .tp-dots i{width:9px;height:9px;border-radius:50%;background:#c9c6bc;display:block}
.tr-play .tp-dots i.on{background:#F5B700}
.tr-play .tp-cap{background:#16202B;color:#fff;padding:10px 14px;min-height:3.2em;line-height:1.4;font-size:.98em}
.tr-play .tp-cap b{color:#F5B700}
.tr-play .tp-data{display:none}
.tr-play .tr-bl{opacity:0}
.tr-play .blink .tr-bl{animation:trbl .8s steps(1) infinite}
@keyframes trbl{0%{opacity:1}50%{opacity:0}}
CSS . '</style>';
  echo '<script id="tr-play-js">' . <<<'JS'
(function(){
var NS='http://www.w3.org/2000/svg';
function el(n,a,p){var e=document.createElementNS(NS,n);for(var k in a)e.setAttribute(k,a[k]);if(p)p.appendChild(e);return e;}
function car(svg,color){var g=el('g',{},svg);var b=el('g',{},g);
 el('rect',{x:-14,y:-7,width:28,height:14,rx:4,fill:color,stroke:'rgba(0,0,0,.35)','stroke-width':.8},b);
 el('rect',{x:3,y:-5.5,width:6,height:11,rx:1.5,fill:'rgba(255,255,255,.6)'},b);
 el('rect',{x:-11,y:-5.5,width:4,height:11,rx:1.5,fill:'rgba(0,0,0,.2)'},b);
 el('circle',{cx:12.5,cy:-6,r:2.6,fill:'#f5a300','class':'tr-bl'},b);
 el('circle',{cx:-12.5,cy:-6,r:2.6,fill:'#f5a300','class':'tr-bl'},b);
 return g;}
function cross(svg,arrows){
 el('rect',{width:400,height:300,fill:'var(--grass)'},svg);
 el('rect',{x:160,y:0,width:80,height:300,fill:'var(--road)'},svg);
 el('rect',{x:0,y:110,width:400,height:80,fill:'var(--road)'},svg);
 var d={stroke:'var(--mark)','stroke-width':1.6,'stroke-dasharray':'10 8'};
 [[200,0,200,104],[200,196,200,300],[0,150,154,150],[246,150,400,150]].forEach(function(c){var o={x1:c[0],y1:c[1],x2:c[2],y2:c[3]};for(var k in d)o[k]=d[k];el('line',o,svg);});
 [[160,0,160,110],[240,0,240,110],[160,190,160,300],[240,190,240,300],[0,110,160,110],[0,190,160,190],[240,110,400,110],[240,190,400,190]].forEach(function(c){el('line',{x1:c[0],y1:c[1],x2:c[2],y2:c[3],stroke:'#c9c6bc','stroke-width':2},svg);});
 if(arrows){var a='M0,22 L0,0 Q0,-8 -8,-8 L-14,-8 L-14,-13 L-24,-4 L-14,5 L-14,0 L-8,0 Q-6,0 -6,2 L-6,22 Z';
  el('path',{d:a,fill:'var(--mark)',transform:'translate(230,236)'},svg);
  el('path',{d:a,fill:'var(--mark)',transform:'translate(170,64) rotate(180)'},svg);}
}
var LIB={
 'links2':{scenes:[
  {arrows:false,cars:{A:{c:'#2f6fd6',p:'M216,330 L213,205 L212,150 L212,146 Q212,130 196,130 L-40,130'},B:{c:'#d63a2f',p:'M184,-30 L187,95 L188,150 L188,154 Q188,170 204,170 L440,170'}},
   steps:[{t:2.2,A:[30,80],B:[30,80],bl:'AB'},{t:1.8,A:[80,120],B:[80,120],bl:'AB'},{t:2,A:[120,180],B:[120,180],bl:'AB'},{t:3.4,A:[180,340],B:[180,340],bl:''},{t:0,A:[340,340],B:[340,340],bl:''}]},
  {arrows:true,cars:{A:{c:'#2f6fd6',p:'M216,330 L213,205 L212,192 C205,160 150,132 125,130 L-40,130'},B:{c:'#d63a2f',p:'M184,-30 L187,95 L188,108 C195,140 250,168 275,170 L440,170'}},
   steps:[{t:2.2,A:[30,80],B:[30,80],bl:'AB'},{t:1.8,A:[80,120],B:[80,120],bl:'AB'},{t:3.6,A:[120,340],B:[120,340],bl:''},{t:0,A:[340,340],B:[340,340],bl:''}]}
 ]}
};
function ease(x){return x<.5?2*x*x:1-Math.pow(-2*x+2,2)/2;}
function init(box){
 var L=LIB[box.getAttribute('data-anim')];if(!L||box._tp)return;box._tp=1;
 var caps=[].slice.call(box.querySelectorAll('.tp-data [data-scene]'));
 var names=[].slice.call(box.querySelectorAll('.tp-data [data-name]'));
 var rtl=box.getAttribute('dir')==='rtl';
 var sc=document.createElement('div');sc.className='tp-scenes';box.appendChild(sc);
 var svg=el('svg',{viewBox:'0 0 400 300',role:'img'});box.appendChild(svg);
 var cap=document.createElement('div');cap.className='tp-cap';box.appendChild(cap);
 var bar=document.createElement('div');bar.className='tp-bar';box.appendChild(bar);
 function btn(t,c,f){var b=document.createElement('button');b.type='button';b.textContent=t;if(c)b.className=c;b.onclick=f;bar.appendChild(b);return b;}
 var S={s:0,i:0,t:0,play:false,hold:0},cars={},paths={},last=0;
 function build(){svg.innerHTML='';var sd=L.scenes[S.s];cross(svg,sd.arrows);cars={};paths={};
  for(var k in sd.cars){paths[k]=el('path',{d:sd.cars[k].p,fill:'none',stroke:'none'},svg);cars[k]=car(svg,sd.cars[k].c);}
  [].forEach.call(sc.children,function(b,j){b.className=j===S.s?'on':'';});
  dots.innerHTML='';sd.steps.forEach(function(){dots.appendChild(document.createElement('i'));});}
 function pos(k,d){var p=paths[k],n=p.getTotalLength();d=Math.max(0,Math.min(n,d));var a=p.getPointAtLength(d),b=p.getPointAtLength(Math.min(n,d+1)),c=p.getPointAtLength(Math.max(0,d-1));var ang=Math.atan2(b.y-c.y,b.x-c.x)*180/Math.PI;cars[k].setAttribute('transform','translate('+a.x.toFixed(1)+','+a.y.toFixed(1)+') rotate('+ang.toFixed(1)+')');}
 function render(){var st=L.scenes[S.s].steps[S.i];for(var k in cars){var r=st[k];pos(k,r[0]+(r[1]-r[0])*ease(S.t));cars[k].setAttribute('class',st.bl.indexOf(k)>=0&&S.t<1.001?'blink':'');}
  var c=caps.filter(function(x){return +x.getAttribute('data-scene')===S.s&&+x.getAttribute('data-step')===S.i;})[0];cap.innerHTML=c?c.innerHTML:'';
  [].forEach.call(dots.children,function(d,j){d.className=j===S.i?'on':'';});
  pb.textContent=S.play?'❚❚':'▶';}
 function tick(ts){var dt=last?(ts-last)/1000:0;last=ts;var steps=L.scenes[S.s].steps,st=steps[S.i];
  if(S.play){if(S.t<1){S.t=st.t?Math.min(1,S.t+dt/st.t):1;}else{S.hold+=dt;if(S.hold>1.4){if(S.i<steps.length-1){S.i++;S.t=0;S.hold=0;}else{S.play=false;}}}}
  render();if(S.play||S.anim){requestAnimationFrame(tick);}else{last=0;}}
 function go(){last=0;requestAnimationFrame(tick);}
 btn('↺','',function(){S.i=0;S.t=0;S.hold=0;S.play=true;go();});
 btn(rtl?'▶':'◀','',function(){S.play=false;if(S.t<0.05&&S.i>0)S.i--;S.t=0;render();});
 var pb=btn('▶','tp-play',function(){var steps=L.scenes[S.s].steps;if(!S.play&&S.i===steps.length-1&&S.t>=1){S.i=0;S.t=0;}S.play=!S.play;S.hold=0;go();});
 btn(rtl?'◀':'▶│','',function(){var steps=L.scenes[S.s].steps;S.play=false;if(S.t>=1&&S.i<steps.length-1){S.i++;S.t=0;}S.anim=1;var stop=S.i;(function run(ts){var st=steps[stop];var dt=last?(ts-last)/1000:0;last=ts;S.t=st.t?Math.min(1,S.t+dt/st.t):1;render();if(S.t<1&&!S.play)requestAnimationFrame(run);else{last=0;S.anim=0;}})(performance.now());});
 var dots=document.createElement('div');dots.className='tp-dots';bar.appendChild(dots);
 L.scenes.forEach(function(x,j){var b=document.createElement('button');b.type='button';var nm=names.filter(function(n){return +n.getAttribute('data-name')===j;})[0];b.textContent=nm?nm.textContent:('Szene '+(j+1));b.onclick=function(){S.s=j;S.i=0;S.t=0;S.play=false;build();render();};sc.appendChild(b);});
 var lab=box.getAttribute('data-label');if(lab)svg.setAttribute('aria-label',lab);
 box._seek=function(s,i,t){if(s!==S.s){S.s=s;build();}S.i=i;S.t=t;S.play=false;render();};
 build();render();
}
function all(){[].forEach.call(document.querySelectorAll('.tr-play'),init);}
if(document.readyState!=='loading')all();else document.addEventListener('DOMContentLoaded',all);
})();
JS . '</script>';
}, 50);
