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
	if (e == 39 && <?php echo $nextSlideNum; ?>) /* right arrow */
		top.location='<?php echo "http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$nextSlideNum"; ?>';
	if (e == 37 && <?php echo $prevSlideNum+1; ?>) /* left arrow */
		top.location='<?php echo "http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$prevSlideNum"; ?>';
}
window.onkeyup = keypressHandler;
-->
</script>
