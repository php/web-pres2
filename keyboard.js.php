<script type="text/javascript" language="javascript" src="1k.js"></script>
<script type="text/javascript" language="javascript" src="slide.js"></script>
<script language="JavaScript1.2">
<!--
if(!document.all){
	window.captureEvents(Event.KEYUP);
}else{
	document.onkeypress = keypressHandler;
}
function keypressHandler(e){
	var e;
	if(document.all) { //it's IE
		e = window.event.keyCode;
	}else{
		e = e.which;
	}
	if (e == 39) /* right arrow */
        {
            if (sliders.length > 0 && currentSlider < sliders.length) {
		try {
                slide(sliders[currentSlider], sliders[currentSlider].getAttribute('gotox'), 0, 0) ;
		} catch (e) { alert(e);}
		currentSlider = currentSlider+1;
            } else if (<?php echo $nextSlideNum; ?>) {
		top.location='<?php echo "http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$nextSlideNum"; ?>';
            }
        }
	if (e == 37 && <?php echo $prevSlideNum+1; ?>) /* left arrow */
		top.location='<?php echo "http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$prevSlideNum"; ?>';
}
window.onkeyup = keypressHandler;

var sliders = [];
var currentSlider = 0;

onload = function() {
	// find any objects with attribute slide
	var sliderdiv = document.getElementsByTagName('div');
	for (var i=0; i < sliderdiv.length; i++) {
	    if (sliderdiv[i].hasAttribute('slide')) {
		// ok, add this to our slider array
		//alert("slide "+sliderdiv[i].id+" at "+sliderdiv[i].offsetLeft+","+sliderdiv[i].offsetTop+"\n");
		sliderdiv[i].setAttribute('gotox',sliderdiv[i].offsetLeft);
		sliderdiv[i].setAttribute('gotoy',sliderdiv[i].offsetTop);
		sliderdiv[i].style.left = -1024;
		sliderdiv[i].style.top = sliderdiv[i].offsetTop;
		sliders[sliders.length] = sliderdiv[i];
	    }
	}
	for (i=0; i< sliders.length; i++) {
	    //alert(sliders[i].id);
	}
	//example = gE( "EXAMPLE" ) ;
	//sX( example, -100 ) ; // some browsers won't automatically populate the...
	//sY( example, -100 ) ; // ...position properties from the style sheet
	//slide( example, 200, 200, 3 ) ;
}
-->
</script>
