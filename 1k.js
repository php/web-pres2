// 1k DOM API - http://timmorgan.info/1k/
d=document;
function gE(e){return d.getElementById(e)};
function cE(t){return d.createElement(t||'div')};
function aC(e,p){return (p||d.body).appendChild(e)};
function sE(e){e.style.visibility='visible'};
function hE(e){e.style.visibility='hidden'};
function sZ(e,z){e.style.zIndex=z};
function sX(e,x){e.style.left=px(x)};
function sY(e,y){e.style.top=px(y)};
function sW(e,w){e.style.width=px(w)};
function sH(e,h){e.style.height=px(h)};
function sC(e,t,r,b,l){e.style.clip='rect('+t+' '+r+' '+b+' '+l+')'};
function wH(e,h){e.innerHTML=h};
function sB(e,b){e.style.background=b};
function aE(e,ev,f){ev=ev.replace(/^(on)?/,'on');if(!e[ev+'c'])e[ev+'c']=[];e[ev+'c'][e[ev+'c'].length]=f;if(!e[ev])e[ev]=function(v){v=v||event;if(!v.currentTarget)v.currentTarget=e;if(!v.target)v.target=v.currentTarget;for(i=0;i<e[ev+'c'].length;i++)e[ev+'c'][i](v)}};
function px(n){return(typeof n=='string')?n:n+'px'};