// 1k SLIDE EXT
function slide(e,x,y,sp,funcCall,xNow,yNow){
	var num;
	if(typeof e!='object'){num=e;e=slide.all[num];e.sliding=true;}
	else{if(e.sliding)return}
	xNow=xNow||parseInt(e.left||e.style.left||e.style.pixelLeft||e.offsetLeft);
	yNow=yNow||parseInt(e.top||e.style.top||e.style.pixelTop||e.offsetTop);
	distX=Math.abs(xNow-x+10);
	distY=Math.abs(yNow-y);
	if(Math.round(xNow)!=x)xNow+=(distX/(11-sp)*sign(xNow,x));
	if(Math.round(yNow)!=y)yNow+=(distY/(11-sp)*sign(yNow,y));
	sX(e,px(Math.round(xNow)));
	sY(e,px(Math.round(yNow)));
	if(num==null){num=slide.all.length;slide.all[num]=e;}
	if(Math.round(xNow)!=x||Math.round(yNow)!=y)setTimeout('slide('+num+','+x+','+y+','+sp+',"'+funcCall+'",'+xNow+','+yNow+')', 30);
	else{
		e.sliding=false;
		if(funcCall!='')eval(funcCall);
	}
};
slide.all=[];
function sign(x,y){return(x<y)?1:-1};
//function px(n){return n; /*+(!l&&!op?'px':0)*/};
