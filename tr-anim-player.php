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
.tr-play .tr-br{opacity:0}
.tr-play .blink .tr-bl,.tr-play .blinkr .tr-br{animation:trbl .8s steps(1) infinite}
@keyframes trbl{0%{opacity:1}50%{opacity:0}}
CSS . '</style>';
  echo '<script id="tr-play-js">' . <<<'JS'
(function(){
var NS='http://www.w3.org/2000/svg';
function el(n,a,p){var e=document.createElementNS(NS,n);for(var k in a)e.setAttribute(k,a[k]);if(p)p.appendChild(e);return e;}
function car(svg,sp){var c=sp.c,L=sp.L||28,W=sp.W||14,h=L/2,w=W/2;var g=el('g',{},svg);var b=el('g',{},g);
 if(sp.kind==='ped'){el('ellipse',{cx:0,cy:0,rx:4,ry:7,fill:c},b);el('circle',{cx:1,cy:0,r:3.6,fill:'#e9c7a0',stroke:'#5a4030','stroke-width':.6},b);return g;}
 if(sp.kind==='rad'){el('rect',{x:-10,y:-1.5,width:20,height:3,rx:1.5,fill:'#222'},b);el('rect',{x:5,y:-5,width:2,height:10,rx:1,fill:'#222'},b);el('ellipse',{cx:-1,cy:0,rx:5,ry:6,fill:c},b);el('circle',{cx:0,cy:0,r:3.4,fill:'#e9c7a0',stroke:'#5a4030','stroke-width':.6},b);return g;}
 if(sp.kind==='lkw'){if(sp.tw)el('polygon',{points:(h-4)+','+w+' '+(h+6)+','+(w+34)+' '+(-h+6)+','+(w+34)+' '+(-h+16)+','+w,fill:'#c8102e',opacity:.22,stroke:'#c8102e','stroke-dasharray':'4 3','stroke-width':1},b);
  el('rect',{x:-h,y:-w,width:L-16,height:W,rx:2,fill:'#e9e6df',stroke:'rgba(0,0,0,.45)','stroke-width':.8},b);
  el('rect',{x:h-14,y:-w+1,width:14,height:W-2,rx:3,fill:c,stroke:'rgba(0,0,0,.4)','stroke-width':.8},b);el('rect',{x:h-5,y:-w+3,width:3.5,height:W-6,rx:1,fill:'rgba(255,255,255,.6)'},b);}
 else if(sp.kind==='trak'){el('rect',{x:-h,y:-w,width:L,height:W,rx:2,fill:c,stroke:'rgba(0,0,0,.35)','stroke-width':.8},b);
  el('rect',{x:-h+3,y:-w+2,width:L*0.4,height:W-4,rx:1.5,fill:'rgba(0,0,0,.28)'},b);
  el('rect',{x:-h-3,y:-w-2,width:9,height:4,rx:1,fill:'#222'},b);el('rect',{x:-h-3,y:w-2,width:9,height:4,rx:1,fill:'#222'},b);}
 else if(sp.kind!=='lkw'){el('rect',{x:-h,y:-w,width:L,height:W,rx:4,fill:c,stroke:'rgba(0,0,0,.35)','stroke-width':.8},b);
  el('rect',{x:h-11,y:-w+1.5,width:6,height:W-3,rx:1.5,fill:'rgba(255,255,255,.6)'},b);
  el('rect',{x:-h+3,y:-w+1.5,width:4,height:W-3,rx:1.5,fill:'rgba(0,0,0,.2)'},b);}
 el('circle',{cx:h-1.5,cy:-w+1,r:2.6,fill:'#f5a300','class':'tr-bl'},b);
 el('circle',{cx:-h+1.5,cy:-w+1,r:2.6,fill:'#f5a300','class':'tr-bl'},b);
 el('circle',{cx:h-1.5,cy:w-1,r:2.6,fill:'#f5a300','class':'tr-br'},b);
 el('circle',{cx:-h+1.5,cy:w-1,r:2.6,fill:'#f5a300','class':'tr-br'},b);
 return g;}
function dl(svg,c,dash){var o={x1:c[0],y1:c[1],x2:c[2],y2:c[3],stroke:'var(--mark)','stroke-width':1.6};if(dash)o['stroke-dasharray']='10 8';el('line',o,svg);}
function kerb(svg,L){L.forEach(function(c){el('line',{x1:c[0],y1:c[1],x2:c[2],y2:c[3],stroke:'#c9c6bc','stroke-width':2},svg);});}
var ARL='M0,22 L0,0 Q0,-8 -8,-8 L-14,-8 L-14,-13 L-24,-4 L-14,5 L-14,0 L-8,0 Q-6,0 -6,2 L-6,22 Z';
var ARS='M-3,22 L-3,-4 L-8,-4 L0,-16 L8,-4 L3,-4 L3,22 Z';
var IMG='https://www.fahrschule-homburg.be/wp-content/uploads/fh-kurse/b/img/';
function sign(svg,f,x,y,w){el('line',{x1:x,y1:y+w*0.6,x2:x,y2:y+w*1.5,stroke:'#777','stroke-width':2},svg);var i=el('image',{x:x-w/2,y:y-w/2,width:w,height:w},svg);i.setAttributeNS('http://www.w3.org/1999/xlink','href',IMG+f);i.setAttribute('href',IMG+f);}
function teeth(svg,x,y,r){var g=el('g',{transform:'translate('+x+','+y+') rotate('+r+')'},svg);for(var i=0;i<4;i++)el('polygon',{points:(i*9-16)+',0 '+(i*9-10)+',0 '+(i*9-13)+',7',fill:'var(--mark)'},g);}
function zebra(svg,x,w,y1,y2){for(var y=y1+4;y<y2-6;y+=12)el('rect',{x:x,y:y,width:w,height:7,fill:'var(--mark)'},svg);}
function zebraV(svg,y,h,x1,x2){for(var x=x1+4;x<x2-6;x+=12)el('rect',{x:x,y:y,width:7,height:h,fill:'var(--mark)'},svg);}
function trees(svg,L){L.forEach(function(t){el('circle',{cx:t[0],cy:t[1],r:16,fill:'#9db58a'},svg);el('circle',{cx:t[0]-4,cy:t[1]-4,r:9,fill:'#b4c9a2'},svg);});}
function cross(svg,lay){
 el('rect',{width:400,height:300,fill:'var(--grass)'},svg);
 if(lay==='4spur'){
  el('rect',{x:140,y:0,width:120,height:300,fill:'var(--road)'},svg);
  el('rect',{x:0,y:110,width:400,height:80,fill:'var(--road)'},svg);
  dl(svg,[200,0,200,108]);dl(svg,[200,192,200,300]);
  dl(svg,[230,196,230,300],1);dl(svg,[170,0,170,104],1);
  dl(svg,[0,150,134,150],1);dl(svg,[266,150,400,150],1);
  dl(svg,[200,192,260,192]);dl(svg,[140,108,200,108]);
  kerb(svg,[[140,0,140,110],[260,0,260,110],[140,190,140,300],[260,190,260,300],[0,110,140,110],[0,190,140,190],[260,110,400,110],[260,190,400,190]]);
  el('path',{d:ARL,fill:'var(--mark)',transform:'translate(218,240)'},svg);
  el('path',{d:ARS,fill:'var(--mark)',transform:'translate(245,240)'},svg);
  el('path',{d:ARL,fill:'var(--mark)',transform:'translate(182,60) rotate(180)'},svg);
  el('path',{d:ARS,fill:'var(--mark)',transform:'translate(155,60) rotate(180)'},svg);
  return;}
 if(lay==='tjunc'){el('rect',{x:0,y:110,width:400,height:80,fill:'var(--road)'},svg);el('rect',{x:160,y:188,width:80,height:112,fill:'var(--road)'},svg);
  dl(svg,[0,150,400,150],1);dl(svg,[200,206,200,300],1);kerb(svg,[[0,110,400,110],[0,190,160,190],[240,190,400,190],[160,190,160,300],[240,190,240,300]]);
  dl(svg,[200,197,240,197],1);teeth(svg,220,198,0);sign(svg,'026-18fe530d36bc.png',254,206,26);trees(svg,[[40,50],[150,40],[320,60],[60,250],[330,255]]);return;}
 if(lay==='radweg'){el('rect',{x:160,y:0,width:80,height:300,fill:'var(--road)'},svg);el('rect',{x:0,y:110,width:400,height:80,fill:'var(--road)'},svg);
  el('rect',{x:242,y:0,width:16,height:110,fill:'#b0584a'},svg);el('rect',{x:242,y:190,width:16,height:110,fill:'#b0584a'},svg);
  for(var y=112;y<188;y+=8){el('rect',{x:242,y:y,width:4,height:4,fill:'var(--mark)'},svg);el('rect',{x:254,y:y,width:4,height:4,fill:'var(--mark)'},svg);}
  [[200,0,200,104],[200,196,200,300],[0,150,154,150],[300,150,400,150]].forEach(function(c){dl(svg,c,1);});
  kerb(svg,[[160,0,160,110],[240,0,240,110],[160,190,160,300],[240,190,240,300],[0,110,160,110],[0,190,160,190],[258,110,400,110],[258,190,400,190],[258,0,258,110],[258,190,258,300]]);
  zebra(svg,272,20,110,190);trees(svg,[[40,50],[330,50],[60,250],[340,255]]);return;}
 if(lay==='zebra'||lay==='zebrainsel'){el('rect',{x:0,y:110,width:400,height:80,fill:'var(--road)'},svg);kerb(svg,[[0,110,400,110],[0,190,400,190]]);
  if(lay==='zebra'){dl(svg,[0,150,236,150],1);dl(svg,[294,150,400,150],1);zebra(svg,250,30,110,190);}
  else{dl(svg,[0,150,190,150],1);dl(svg,[190,150,244,150]);dl(svg,[286,150,400,150],1);zebra(svg,250,30,110,144);zebra(svg,250,30,156,196);el('rect',{x:244,y:143,width:42,height:14,rx:7,fill:'#c9d3b8',stroke:'#f4f1e8','stroke-width':1.5},svg);el('circle',{cx:265,cy:150,r:3,fill:'#1d4f9c'},svg);}
  sign(svg,'111-741dc72ce979.png',236,204,24);sign(svg,'111-741dc72ce979.png',294,80,24);trees(svg,[[40,50],[150,40],[340,60],[60,250],[160,262],[350,255]]);return;}
 if(lay==='kreis2'){el('rect',{x:160,y:0,width:80,height:300,fill:'var(--road)'},svg);el('rect',{x:0,y:110,width:400,height:80,fill:'var(--road)'},svg);
  el('circle',{cx:200,cy:150,r:100,fill:'var(--road)'},svg);el('circle',{cx:200,cy:150,r:44,fill:'#c9d3b8',stroke:'#f4f1e8','stroke-width':2},svg);
  el('circle',{cx:200,cy:150,r:72,fill:'none',stroke:'var(--mark)','stroke-width':1.6,'stroke-dasharray':'10 8'},svg);
  dl(svg,[200,0,200,48],1);dl(svg,[200,252,200,300],1);dl(svg,[0,150,98,150],1);dl(svg,[302,150,400,150],1);
  var tri=function(x,y,r){var g=el('g',{transform:'translate('+x+','+y+') rotate('+r+')'},svg);for(var i=0;i<4;i++)el('polygon',{points:(i*9-16)+',0 '+(i*9-10)+',0 '+(i*9-13)+',7',fill:'var(--mark)'},g);};
  tri(220,262,0);tri(180,38,180);tri(312,130,-90);tri(88,170,90);
  [[30,40],[350,40],[40,260],[355,262]].forEach(function(t){el('circle',{cx:t[0],cy:t[1],r:16,fill:'#9db58a'},svg);});return;}
 if(lay==='gerade'){el('rect',{x:0,y:110,width:400,height:80,fill:'var(--road)'},svg);dl(svg,[0,150,400,150],1);kerb(svg,[[0,110,400,110],[0,190,400,190]]);
  [[30,60],[120,40],[300,55],[370,250],[210,262],[60,245]].forEach(function(t){el('circle',{cx:t[0],cy:t[1],r:16,fill:'#9db58a'},svg);el('circle',{cx:t[0]-4,cy:t[1]-4,r:9,fill:'#b4c9a2'},svg);});return;}
 el('rect',{x:160,y:0,width:80,height:300,fill:'var(--road)'},svg);
 el('rect',{x:0,y:110,width:400,height:80,fill:'var(--road)'},svg);
 [[200,0,200,104],[200,196,200,300],[0,150,154,150],[246,150,400,150]].forEach(function(c){dl(svg,c,1);});
 kerb(svg,[[160,0,160,110],[240,0,240,110],[160,190,160,300],[240,190,240,300],[0,110,160,110],[0,190,160,190],[240,110,400,110],[240,190,400,190]]);
}
var LIB={
 'links2':{scenes:[
  {lay:'',cars:{A:{c:'#2f6fd6',p:'M216,330 L213,205 L212,150 L212,146 Q212,130 196,130 L-40,130'},B:{c:'#d63a2f',p:'M184,-30 L187,95 L188,150 L188,154 Q188,170 204,170 L440,170'}},
   steps:[{t:2.2,A:[30,80],B:[30,80],bl:'AB'},{t:1.8,A:[80,120],B:[80,120],bl:'AB'},{t:2,A:[120,180],B:[120,180],bl:'AB'},{t:3.4,A:[180,340],B:[180,340],bl:''},{t:0,A:[340,340],B:[340,340],bl:''}]},
  {lay:'4spur',cars:{A:{c:'#2f6fd6',p:'M215,330 L215,195 C213,165 160,132 130,130 L-40,130'},B:{c:'#d63a2f',p:'M185,-30 L185,105 C187,135 240,168 270,170 L440,170'}},
   steps:[{t:2.2,A:[30,80],B:[30,80],bl:'AB'},{t:1.8,A:[80,128],B:[80,128],bl:'AB'},{t:3.6,A:[128,340],B:[128,340],bl:''},{t:0,A:[340,340],B:[340,340],bl:''}]}
 ]},
 'rvl':{scenes:[
  {lay:'',cars:{A:{c:'#2f6fd6',p:'M220,330 L220,-40'},R:{c:'#d63a2f',p:'M440,130 L-40,130'}},
   steps:[{t:2.2,A:[30,100],R:[20,90]},{t:3,A:[100,125],R:[90,330]},{t:2.6,A:[125,260],R:[330,480]},{t:0,A:[260,260],R:[480,480]}]},
  {lay:'',cars:{A:{c:'#2f6fd6',p:'M220,330 L220,-40'},R:{c:'#d63a2f',p:'M-40,170 L440,170'}},
   steps:[{t:2.2,A:[30,100],R:[20,90]},{t:3,A:[100,300],R:[90,185]},{t:2.6,A:[300,420],R:[185,480]},{t:0,A:[420,420],R:[480,480]}]},
  {lay:'',cars:{A:{c:'#2f6fd6',p:'M220,330 L220,-40'},R:{c:'#d63a2f',p:'M440,130 L250,130 Q182,130 180,198 L180,340'}},
   steps:[{t:2.2,A:[30,100],R:[20,90],bl:'R'},{t:3.2,A:[100,125],R:[90,300],bl:'R'},{t:2.6,A:[125,260],R:[300,420]},{t:0,A:[260,260],R:[420,420]}]}
 ]},
 'ueberh':{scenes:[
  {lay:'gerade',cars:{T:{c:'#3f8a3a',kind:'trak',L:34,W:18,p:'M-40,170 L440,170'},A:{c:'#2f6fd6',p:'M-40,170 L60,170 C95,170 95,130 130,130 L250,130 C285,130 285,170 320,170 L440,170'}},
   steps:[{t:2.4,A:[20,80],T:[185,195]},{t:2,A:[80,160],T:[195,205],bl:'A'},{t:2.6,A:[160,300],T:[205,222]},{t:2.2,A:[300,430],T:[222,236],br:'A'},{t:0,A:[430,430],T:[236,236]}]},
  {lay:'gerade',cars:{T:{c:'#3f8a3a',kind:'trak',L:34,W:18,p:'M-40,170 L440,170'},G:{c:'#d63a2f',p:'M440,130 L-40,130'},A:{c:'#2f6fd6',p:'M-40,170 L60,170 C95,170 95,130 130,130 L250,130 C285,130 285,170 320,170 L440,170'}},
   steps:[{t:2.4,A:[20,80],T:[185,195],G:[0,60]},{t:3,A:[80,95],T:[195,205],G:[60,330]},{t:2.4,A:[95,170],T:[205,214],G:[330,520],bl:'A'},{t:2.4,A:[170,300],T:[214,230],G:[520,520]},{t:2.2,A:[300,430],T:[230,244],G:[520,520],br:'A'},{t:0,A:[430,430],T:[244,244],G:[520,520]}]}
 ]},
 'kreis2':{scenes:[
  {lay:'kreis2',cars:{A:{c:'#2f6fd6',p:'M220.0,330.0 L220.0,326.9 L220.0,323.8 L220.0,320.7 L220.0,317.6 L220.0,314.5 L220.0,311.5 L220.0,308.4 L220.0,305.3 L220.0,302.2 L220.0,299.1 L220.0,296.0 L220.0,292.9 L220.0,289.8 L220.0,286.7 L220.0,283.6 L220.0,280.5 L220.0,277.5 L220.0,274.4 L220.0,271.3 L220.0,268.2 L220.0,265.1 L220.0,262.0 L220.0,260.2 L220.1,258.5 L220.2,256.8 L220.4,255.2 L220.6,253.6 L220.9,252.0 L221.2,250.5 L221.6,249.1 L222.0,247.7 L222.5,246.3 L223.0,244.9 L223.6,243.6 L224.2,242.3 L224.9,241.1 L225.7,239.9 L226.4,238.7 L227.3,237.5 L228.2,236.4 L229.1,235.3 L230.1,234.2 L231.2,233.1 L232.3,232.1 L233.4,231.1 L234.6,230.1 L235.9,229.1 L237.2,228.2 L238.6,227.2 L240.0,226.3 L241.5,225.4 L243.0,224.5 L245.6,222.9 L248.1,221.3 L250.5,219.6 L252.9,217.8 L255.3,215.9 L257.5,213.9 L259.7,211.9 L261.9,209.7 L263.9,207.5 L265.9,205.3 L267.8,202.9 L269.6,200.5 L271.3,198.1 L272.9,195.6 L274.5,193.0 L275.9,190.4 L277.3,187.7 L278.6,185.0 L279.7,182.2 L280.8,179.4 L281.8,176.6 L282.7,173.7 L283.4,170.8 L284.1,167.9 L284.7,164.9 L285.2,162.0 L285.5,159.0 L285.8,156.0 L285.9,153.0 L286.0,150.0 L285.9,147.0 L285.8,144.0 L285.5,141.0 L285.2,138.0 L284.7,135.1 L284.1,132.1 L283.4,129.2 L282.7,126.3 L281.8,123.4 L280.8,120.6 L279.7,117.8 L278.6,115.0 L277.3,112.3 L275.9,109.6 L274.5,107.0 L272.9,104.4 L271.3,101.9 L269.6,99.5 L267.8,97.1 L265.9,94.7 L263.9,92.5 L261.9,90.3 L259.7,88.1 L257.5,86.1 L255.3,84.1 L252.9,82.2 L250.5,80.4 L248.1,78.7 L245.6,77.1 L243.0,75.5 L240.4,74.1 L237.7,72.7 L235.0,71.4 L232.2,70.3 L229.4,69.2 L226.6,68.2 L223.7,67.3 L220.8,66.6 L217.9,65.9 L214.9,65.3 L212.0,64.8 L209.0,64.5 L206.0,64.2 L203.0,64.1 L200.0,64.0 L197.0,64.1 L194.0,64.2 L191.0,64.5 L188.0,64.8 L185.1,65.3 L182.1,65.9 L179.2,66.6 L176.3,67.3 L173.4,68.2 L170.6,69.2 L167.8,70.3 L165.0,71.4 L162.3,72.7 L159.6,74.1 L157.0,75.5 L154.4,77.1 L151.9,78.7 L149.5,80.4 L147.1,82.2 L144.7,84.1 L142.5,86.1 L140.3,88.1 L138.1,90.3 L136.1,92.5 L134.1,94.7 L132.7,96.4 L131.4,98.1 L130.1,99.8 L128.8,101.5 L127.6,103.2 L126.4,104.9 L125.2,106.5 L124.1,108.2 L122.9,109.8 L121.8,111.4 L120.7,112.9 L119.5,114.4 L118.4,115.9 L117.2,117.3 L116.0,118.7 L114.8,120.0 L113.5,121.2 L112.3,122.4 L110.9,123.6 L109.6,124.6 L108.1,125.6 L106.6,126.4 L105.1,127.2 L103.5,127.9 L101.8,128.6 L100.0,129.1 L98.1,129.5 L96.2,129.8 L94.1,129.9 L92.0,130.0 L89.0,130.0 L86.0,130.0 L83.0,130.0 L80.0,130.0 L77.0,130.0 L74.0,130.0 L71.0,130.0 L68.0,130.0 L65.0,130.0 L62.0,130.0 L59.0,130.0 L56.0,130.0 L53.0,130.0 L50.0,130.0 L47.0,130.0 L44.0,130.0 L41.0,130.0 L38.0,130.0 L35.0,130.0 L32.0,130.0 L29.0,130.0 L26.0,130.0 L23.0,130.0 L20.0,130.0 L17.0,130.0 L14.0,130.0 L11.0,130.0 L8.0,130.0 L5.0,130.0 L2.0,130.0 L-1.0,130.0 L-4.0,130.0 L-7.0,130.0 L-10.0,130.0 L-13.0,130.0 L-16.0,130.0 L-19.0,130.0 L-22.0,130.0 L-25.0,130.0 L-28.0,130.0 L-31.0,130.0 L-34.0,130.0 L-37.0,130.0 L-40.0,130.0'}},
   steps:[{t:2,A:[30,100]},{t:4.2,A:[100,340]},{t:1.8,A:[340,415],br:'A'},{t:2.4,A:[415,580],br:'A'},{t:0,A:[580,580]}]},
  {lay:'kreis2',cars:{A:{c:'#2f6fd6',p:'M220.0,330.0 L220.0,326.9 L220.0,323.8 L220.0,320.7 L220.0,317.6 L220.0,314.5 L220.0,311.5 L220.0,308.4 L220.0,305.3 L220.0,302.2 L220.0,299.1 L220.0,296.0 L220.0,292.9 L220.0,289.8 L220.0,286.7 L220.0,283.6 L220.0,280.5 L220.0,277.5 L220.0,274.4 L220.0,271.3 L220.0,268.2 L220.0,265.1 L220.0,262.0 L220.0,260.2 L220.1,258.5 L220.2,256.8 L220.4,255.2 L220.6,253.6 L220.9,252.0 L221.2,250.5 L221.6,249.1 L222.0,247.7 L222.5,246.3 L223.0,244.9 L223.6,243.6 L224.2,242.3 L224.9,241.1 L225.7,239.9 L226.4,238.7 L227.3,237.5 L228.2,236.4 L229.1,235.3 L230.1,234.2 L231.2,233.1 L232.3,232.1 L233.4,231.1 L234.6,230.1 L235.9,229.1 L237.2,228.2 L238.6,227.2 L240.0,226.3 L241.5,225.4 L243.0,224.5 L245.8,222.8 L248.4,221.1 L251.1,219.2 L253.6,217.2 L256.1,215.2 L258.5,213.0 L260.8,210.8 L261.7,209.8 L262.4,208.7 L263.0,207.5 L263.4,206.2 L263.6,204.9 L263.8,203.5 L263.8,202.1 L263.7,200.6 L263.5,199.1 L263.2,197.5 L262.8,195.9 L262.4,194.3 L261.9,192.6 L261.3,191.0 L260.8,189.3 L260.2,187.6 L259.6,185.8 L259.0,184.1 L258.4,182.4 L257.9,180.7 L257.3,179.0 L256.9,177.3 L256.4,175.7 L256.1,174.0 L255.8,172.4 L255.6,170.8 L255.5,169.3 L255.6,167.8 L255.7,166.4 L256.0,165.0 L256.5,163.0 L256.9,161.1 L257.3,159.1 L257.6,157.1 L257.8,155.1 L257.9,153.0 L258.0,151.0 L258.0,149.0 L257.9,147.0 L257.8,144.9 L257.6,142.9 L257.3,140.9 L256.9,138.9 L256.5,137.0 L256.0,135.0 L255.5,133.0 L254.8,131.1 L254.1,129.2 L253.4,127.3 L252.6,125.5 L251.7,123.7 L250.7,121.9 L249.7,120.1 L248.6,118.4 L247.5,116.7 L246.3,115.1 L245.1,113.5 L243.8,111.9 L242.4,110.4 L241.0,109.0 L239.6,107.6 L238.1,106.2 L236.5,104.9 L234.9,103.7 L233.3,102.5 L231.6,101.4 L229.9,100.3 L228.1,99.3 L226.3,98.3 L224.5,97.4 L222.7,96.6 L220.8,95.9 L218.9,95.2 L217.0,94.5 L215.0,94.0 L213.0,93.5 L211.1,93.1 L209.1,92.7 L207.1,92.4 L205.1,92.2 L203.0,92.1 L201.0,92.0 L199.0,92.0 L197.0,92.1 L194.9,92.2 L193.5,92.3 L192.1,92.2 L190.6,91.9 L189.1,91.6 L187.6,91.1 L186.1,90.6 L184.5,90.0 L183.0,89.3 L181.4,88.5 L179.8,87.7 L178.2,86.8 L176.6,86.0 L175.0,85.1 L173.5,84.2 L171.9,83.3 L170.3,82.5 L168.7,81.7 L167.2,80.9 L165.7,80.2 L164.1,79.5 L162.7,79.0 L161.2,78.5 L159.8,78.1 L158.3,77.9 L157.0,77.8 L155.6,77.8 L154.3,78.0 L153.1,78.3 L151.8,78.8 L150.7,79.6 L148.1,81.4 L145.5,83.4 L143.1,85.5 L140.7,87.7 L138.4,90.0 L136.2,92.3 L134.1,94.7 L132.7,96.4 L131.4,98.1 L130.1,99.8 L128.8,101.5 L127.6,103.2 L126.4,104.9 L125.2,106.5 L124.1,108.2 L122.9,109.8 L121.8,111.4 L120.7,112.9 L119.5,114.4 L118.4,115.9 L117.2,117.3 L116.0,118.7 L114.8,120.0 L113.5,121.2 L112.3,122.4 L110.9,123.6 L109.6,124.6 L108.1,125.6 L106.6,126.4 L105.1,127.2 L103.5,127.9 L101.8,128.6 L100.0,129.1 L98.1,129.5 L96.2,129.8 L94.1,129.9 L92.0,130.0 L89.0,130.0 L86.0,130.0 L83.0,130.0 L80.0,130.0 L77.0,130.0 L74.0,130.0 L71.0,130.0 L68.0,130.0 L65.0,130.0 L62.0,130.0 L59.0,130.0 L56.0,130.0 L53.0,130.0 L50.0,130.0 L47.0,130.0 L44.0,130.0 L41.0,130.0 L38.0,130.0 L35.0,130.0 L32.0,130.0 L29.0,130.0 L26.0,130.0 L23.0,130.0 L20.0,130.0 L17.0,130.0 L14.0,130.0 L11.0,130.0 L8.0,130.0 L5.0,130.0 L2.0,130.0 L-1.0,130.0 L-4.0,130.0 L-7.0,130.0 L-10.0,130.0 L-13.0,130.0 L-16.0,130.0 L-19.0,130.0 L-22.0,130.0 L-25.0,130.0 L-28.0,130.0 L-31.0,130.0 L-34.0,130.0 L-37.0,130.0 L-40.0,130.0'}},
   steps:[{t:2,A:[30,100]},{t:2,A:[100,185],bl:'A'},{t:2.6,A:[185,296]},{t:2,A:[296,366],br:'A'},{t:2.4,A:[366,530],br:'A'},{t:0,A:[530,530]}]},
  {lay:'kreis2',cars:{R:{c:'#d63a2f',p:'M440.0,130.0 L437.0,130.0 L433.9,130.0 L430.9,130.0 L427.8,130.0 L424.8,130.0 L421.7,130.0 L418.7,130.0 L415.7,130.0 L412.6,130.0 L409.6,130.0 L406.5,130.0 L403.5,130.0 L400.4,130.0 L397.4,130.0 L394.3,130.0 L391.3,130.0 L388.3,130.0 L385.2,130.0 L382.2,130.0 L379.1,130.0 L376.1,130.0 L373.0,130.0 L370.0,130.0 L367.0,130.0 L363.9,130.0 L360.9,130.0 L357.8,130.0 L354.8,130.0 L351.7,130.0 L348.7,130.0 L345.7,130.0 L342.6,130.0 L339.6,130.0 L336.5,130.0 L333.5,130.0 L330.4,130.0 L327.4,130.0 L324.3,130.0 L321.3,130.0 L318.3,130.0 L315.2,130.0 L312.2,130.0 L309.1,130.0 L306.1,130.0 L303.0,130.0 L300.0,130.0 L298.8,130.0 L297.7,130.2 L296.7,130.3 L295.7,130.6 L294.8,130.9 L294.0,131.2 L293.2,131.6 L292.5,132.0 L291.8,132.4 L291.1,132.8 L290.5,133.2 L290.0,133.6 L289.4,134.0 L289.0,134.4 L288.5,134.7 L288.1,135.0 L287.7,135.2 L287.3,135.3 L286.9,135.4 L286.6,135.4 L286.2,135.4 L285.9,135.2 L285.6,134.9 L285.3,134.5 L285.0,134.0 L284.7,133.3 L284.4,132.5 L284.1,131.6 L283.8,130.5 L283.4,129.2 L282.7,126.3 L281.8,123.4 L280.8,120.6 L279.7,117.8 L278.6,115.0 L277.3,112.3 L275.9,109.6 L274.5,107.0 L272.9,104.4 L271.3,101.9 L269.6,99.5 L267.8,97.1 L265.9,94.7 L263.9,92.5 L261.9,90.3 L259.7,88.1 L257.5,86.1 L255.3,84.1 L252.9,82.2 L250.5,80.4 L248.1,78.7 L245.6,77.1 L243.0,75.5 L240.4,74.1 L237.7,72.7 L235.0,71.4 L232.2,70.3 L229.4,69.2 L226.6,68.2 L223.7,67.3 L220.8,66.6 L217.9,65.9 L214.9,65.3 L212.0,64.8 L209.0,64.5 L206.0,64.2 L203.0,64.1 L200.0,64.0 L197.0,64.1 L194.0,64.2 L191.0,64.5 L188.0,64.8 L185.1,65.3 L182.1,65.9 L179.2,66.6 L176.3,67.3 L173.4,68.2 L170.6,69.2 L167.8,70.3 L165.0,71.4 L162.3,72.7 L159.6,74.1 L157.0,75.5 L154.4,77.1 L151.9,78.7 L149.5,80.4 L147.1,82.2 L144.7,84.1 L142.5,86.1 L140.3,88.1 L138.1,90.3 L136.1,92.5 L134.1,94.7 L132.7,96.4 L131.4,98.1 L130.1,99.8 L128.8,101.5 L127.6,103.2 L126.4,104.9 L125.2,106.5 L124.1,108.2 L122.9,109.8 L121.8,111.4 L120.7,112.9 L119.5,114.4 L118.4,115.9 L117.2,117.3 L116.0,118.7 L114.8,120.0 L113.5,121.2 L112.3,122.4 L110.9,123.6 L109.6,124.6 L108.1,125.6 L106.6,126.4 L105.1,127.2 L103.5,127.9 L101.8,128.6 L100.0,129.1 L98.1,129.5 L96.2,129.8 L94.1,129.9 L92.0,130.0 L89.0,130.0 L86.0,130.0 L83.0,130.0 L80.0,130.0 L77.0,130.0 L74.0,130.0 L71.0,130.0 L68.0,130.0 L65.0,130.0 L62.0,130.0 L59.0,130.0 L56.0,130.0 L53.0,130.0 L50.0,130.0 L47.0,130.0 L44.0,130.0 L41.0,130.0 L38.0,130.0 L35.0,130.0 L32.0,130.0 L29.0,130.0 L26.0,130.0 L23.0,130.0 L20.0,130.0 L17.0,130.0 L14.0,130.0 L11.0,130.0 L8.0,130.0 L5.0,130.0 L2.0,130.0 L-1.0,130.0 L-4.0,130.0 L-7.0,130.0 L-10.0,130.0 L-13.0,130.0 L-16.0,130.0 L-19.0,130.0 L-22.0,130.0 L-25.0,130.0 L-28.0,130.0 L-31.0,130.0 L-34.0,130.0 L-37.0,130.0 L-40.0,130.0'},A:{c:'#2f6fd6',p:'M220.0,330.0 L220.0,326.9 L220.0,323.8 L220.0,320.7 L220.0,317.6 L220.0,314.5 L220.0,311.5 L220.0,308.4 L220.0,305.3 L220.0,302.2 L220.0,299.1 L220.0,296.0 L220.0,292.9 L220.0,289.8 L220.0,286.7 L220.0,283.6 L220.0,280.5 L220.0,277.5 L220.0,274.4 L220.0,271.3 L220.0,268.2 L220.0,265.1 L220.0,262.0 L220.0,260.2 L220.1,258.5 L220.2,256.8 L220.4,255.2 L220.6,253.6 L220.9,252.0 L221.2,250.5 L221.6,249.1 L222.0,247.7 L222.5,246.3 L223.0,244.9 L223.6,243.6 L224.2,242.3 L224.9,241.1 L225.7,239.9 L226.4,238.7 L227.3,237.5 L228.2,236.4 L229.1,235.3 L230.1,234.2 L231.2,233.1 L232.3,232.1 L233.4,231.1 L234.6,230.1 L235.9,229.1 L237.2,228.2 L238.6,227.2 L240.0,226.3 L241.5,225.4 L243.0,224.5 L245.8,222.8 L248.4,221.1 L251.1,219.2 L253.6,217.2 L256.1,215.2 L258.5,213.0 L260.8,210.8 L261.7,209.8 L262.4,208.7 L263.0,207.5 L263.4,206.2 L263.6,204.9 L263.8,203.5 L263.8,202.1 L263.7,200.6 L263.5,199.1 L263.2,197.5 L262.8,195.9 L262.4,194.3 L261.9,192.6 L261.3,191.0 L260.8,189.3 L260.2,187.6 L259.6,185.8 L259.0,184.1 L258.4,182.4 L257.9,180.7 L257.3,179.0 L256.9,177.3 L256.4,175.7 L256.1,174.0 L255.8,172.4 L255.6,170.8 L255.5,169.3 L255.6,167.8 L255.7,166.4 L256.0,165.0 L256.5,163.0 L256.9,161.0 L257.3,159.0 L257.6,157.0 L257.8,155.0 L257.9,152.9 L258.0,150.9 L258.0,148.9 L257.9,146.8 L257.8,144.8 L257.5,142.8 L257.3,140.7 L256.9,138.7 L256.5,136.7 L256.0,134.8 L255.4,132.8 L254.7,130.9 L254.0,128.9 L253.3,127.1 L252.4,125.2 L251.5,123.4 L250.6,121.6 L249.5,119.8 L248.4,118.1 L247.3,116.4 L246.1,114.8 L244.8,113.2 L243.5,111.6 L242.1,110.1 L240.7,108.6 L239.2,107.2 L237.7,105.9 L236.1,104.6 L234.5,103.3 L232.8,102.2 L231.1,101.0 L229.4,100.0 L227.6,99.0 L225.8,98.0 L223.9,97.2 L222.0,96.4 L220.1,95.6 L218.2,94.9 L216.3,94.3 L214.3,93.8 L212.3,93.3 L210.3,92.9 L208.3,92.6 L206.3,92.3 L204.2,92.2 L202.2,92.0 L200.2,92.0 L198.1,92.0 L196.1,92.1 L194.1,92.3 L192.0,92.6 L190.0,92.9 L188.0,93.3 L186.0,93.7 L184.0,94.2 L182.1,94.8 L180.2,95.5 L178.0,96.3 L175.7,97.2 L173.3,98.3 L170.7,99.4 L168.0,100.7 L165.2,102.0 L162.3,103.4 L159.2,104.8 L156.2,106.3 L153.0,107.8 L149.8,109.3 L146.5,110.9 L143.2,112.5 L139.9,114.0 L136.6,115.6 L133.2,117.1 L129.9,118.6 L126.6,120.0 L123.3,121.4 L120.0,122.7 L116.8,124.0 L113.7,125.1 L110.6,126.2 L107.6,127.1 L104.7,128.0 L101.9,128.7 L99.3,129.2 L96.7,129.7 L94.3,129.9 L92.0,130.0 L89.0,130.0 L86.0,130.0 L83.0,130.0 L80.0,130.0 L77.0,130.0 L74.0,130.0 L71.0,130.0 L68.0,130.0 L65.0,130.0 L62.0,130.0 L59.0,130.0 L56.0,130.0 L53.0,130.0 L50.0,130.0 L47.0,130.0 L44.0,130.0 L41.0,130.0 L38.0,130.0 L35.0,130.0 L32.0,130.0 L29.0,130.0 L26.0,130.0 L23.0,130.0 L20.0,130.0 L17.0,130.0 L14.0,130.0 L11.0,130.0 L8.0,130.0 L5.0,130.0 L2.0,130.0 L-1.0,130.0 L-4.0,130.0 L-7.0,130.0 L-10.0,130.0 L-13.0,130.0 L-16.0,130.0 L-19.0,130.0 L-22.0,130.0 L-25.0,130.0 L-28.0,130.0 L-31.0,130.0 L-34.0,130.0 L-37.0,130.0 L-40.0,130.0'}},
   steps:[{t:3,A:[30,185],R:[0,162]},{t:3,A:[185,311],R:[162,300]},{t:2,A:[311,407],R:[300,316],x:[134,96]},{t:2,A:[407,520],R:[316,316],x:[134,96]}]}
 ]},
 'b1':{scenes:[
  {lay:'tjunc',cars:{L:{c:'#d63a2f',p:'M-40,170 L440,170'},A:{c:'#2f6fd6',p:'M220,330 L220,205 Q220,172 250,170 L440,170'}},
   steps:[{t:2.2,A:[30,100],L:[0,90],br:'A'},{t:3,A:[100,125],L:[90,330],br:'A'},{t:2.8,A:[125,330],L:[330,520]},{t:0,A:[330,330],L:[520,520]}]},
  {lay:'tjunc',cars:{L:{c:'#d63a2f',p:'M-40,170 L440,170'},R:{c:'#8a939e',p:'M440,130 L-40,130'},A:{c:'#2f6fd6',p:'M220,330 L220,205 Q220,138 186,130 L-40,130'}},
   steps:[{t:2.2,A:[30,100],L:[0,60],R:[0,20],bl:'A'},{t:3,A:[100,125],L:[60,330],R:[20,170],bl:'A'},{t:2.4,A:[125,125],L:[330,520],R:[170,520],bl:'A'},{t:3,A:[125,360],L:[520,520],R:[520,520],bl:'A'},{t:0,A:[360,360],L:[520,520],R:[520,520]}]}
 ]},
 'rechtsab':{scenes:[
  {lay:'radweg',cars:{C:{c:'#e08a1e',kind:'rad',p:'M250,330 L250,-40'},A:{c:'#2f6fd6',p:'M220,330 L220,205 Q221,172 252,170 L440,170'}},
   steps:[{t:2.2,A:[30,90],C:[0,20],br:'A'},{t:2.4,A:[90,125],C:[20,110],br:'A'},{t:2.6,A:[125,125],C:[110,300],br:'A'},{t:2.8,A:[125,330],C:[300,400],br:'A'},{t:0,A:[330,330],C:[400,400]}]},
  {lay:'radweg',cars:{P:{c:'#7a3fa0',kind:'ped',p:'M282,206 L282,90'},A:{c:'#2f6fd6',p:'M220,330 L220,205 Q221,172 252,170 L440,170'}},
   steps:[{t:2.2,A:[30,100],P:[0,4],br:'A'},{t:2.8,A:[100,172],P:[4,24],br:'A'},{t:4,A:[172,172],P:[24,118]},{t:2.4,A:[172,330],P:[118,118]},{t:0,A:[330,330],P:[118,118]}]}
 ]},
 'zebra':{scenes:[
  {lay:'zebra',cars:{P:{c:'#7a3fa0',kind:'ped',p:'M265,212 L265,86'},A:{c:'#2f6fd6',p:'M-40,170 L440,170'}},
   steps:[{t:2.2,A:[30,150],P:[0,6]},{t:2.6,A:[150,271],P:[6,22]},{t:4.4,A:[271,271],P:[22,126]},{t:2.4,A:[271,480],P:[126,126]},{t:0,A:[480,480],P:[126,126]}]},
  {lay:'zebrainsel',cars:{P:{c:'#7a3fa0',kind:'ped',p:'M265,86 L265,214'},A:{c:'#2f6fd6',p:'M-40,170 L440,170'}},
   steps:[{t:2.2,A:[30,150],P:[0,20]},{t:2.8,A:[150,271],P:[20,58]},{t:3.4,A:[271,271],P:[58,128]},{t:2.4,A:[271,480],P:[128,128]},{t:0,A:[480,480],P:[128,128]}]}
 ]},
 'linksab':{scenes:[
  {lay:'',cars:{G:{c:'#d63a2f',p:'M180,-40 L180,340'},F:{c:'#8a939e',p:'M232,340 L232,-40'},A:{c:'#2f6fd6',p:'M216,330 L209,205 L208,152 L208,146 Q208,130 192,130 L-40,130'}},
   steps:[{t:2.2,A:[30,100],G:[0,40],F:[0,30],bl:'A'},{t:2.6,A:[100,178],G:[40,150],F:[30,120],bl:'A'},{t:3,A:[178,178],G:[150,390],F:[120,390],bl:'A'},{t:2.8,A:[178,360],G:[390,390],F:[390,390],bl:'A'},{t:0,A:[360,360],G:[390,390],F:[390,390]}]}
 ]},
 'lkwrechts':{scenes:[
  {lay:'radweg',cars:{C:{c:'#e08a1e',kind:'rad',p:'M250,360 L250,-40'},T:{c:'#2f6fd6',kind:'lkw',L:62,W:18,tw:1,p:'M226,380 L226,270 C226,252 218,250 218,232 L218,200 C218,174 238,170 262,170 L460,170'}},
   steps:[{t:2.4,T:[0,70],C:[0,20],br:'T'},{t:2.8,T:[70,140],C:[20,150],br:'T'},{t:3,T:[140,140],C:[150,330],br:'T'},{t:3.4,T:[140,390],C:[330,420],br:'T'},{t:0,T:[390,390],C:[420,420]}]}
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
 function build(){svg.innerHTML='';var sd=L.scenes[S.s];cross(svg,sd.lay);cars={};paths={};
  for(var k in sd.cars){paths[k]=el('path',{d:sd.cars[k].p,fill:'none',stroke:'none'},svg);cars[k]=car(svg,sd.cars[k]);}
  [].forEach.call(sc.children,function(b,j){b.className=j===S.s?'on':'';});
  dots.innerHTML='';sd.steps.forEach(function(){dots.appendChild(document.createElement('i'));});}
 function pos(k,d){var p=paths[k],n=p.getTotalLength();d=Math.max(0,Math.min(n,d));var a=p.getPointAtLength(d),b=p.getPointAtLength(Math.min(n,d+1)),c=p.getPointAtLength(Math.max(0,d-1));var ang=Math.atan2(b.y-c.y,b.x-c.x)*180/Math.PI;cars[k].setAttribute('transform','translate('+a.x.toFixed(1)+','+a.y.toFixed(1)+') rotate('+ang.toFixed(1)+')');}
 function render(){var st=L.scenes[S.s].steps[S.i];for(var k in cars){var r=st[k];pos(k,r[0]+(r[1]-r[0])*ease(S.t));var on=S.t<1.001,cl=[];if((st.bl||'').indexOf(k)>=0&&on)cl.push('blink');if((st.br||'').indexOf(k)>=0&&on)cl.push('blinkr');cars[k].setAttribute('class',cl.join(' '));}
  var xo=svg.querySelector('.tp-x');if(xo)xo.remove();if(st.x){var gx=el('g',{'class':'tp-x',transform:'translate('+st.x[0]+','+st.x[1]+')'},svg);el('circle',{r:13,fill:'#c8102e',opacity:.9},gx);el('path',{d:'M-6,-6 L6,6 M6,-6 L-6,6',stroke:'#fff','stroke-width':3,'stroke-linecap':'round'},gx);}
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
