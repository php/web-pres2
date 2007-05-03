<?php /*
 A bit of fancy footwork to get the browser's inside dimensions in
 pixels.  Should work on both NS4+ and IE4+.  If it doesn't we default
 it to something sane.  The dimensions are returned to the server via
 a Javascript cookie so as to not muck up our nice clean URL.  The 
 function is called if we don't have the dimensions already, or on a
 resize event to fetch the new window dimensions.
*/?>
<script type="text/javascript" language="JavaScript" defer="defer">
<!--
function get_dims() {
    var winW = 1024;
    var winH = 650;

    if (window.innerWidth) { 
        winW = window.innerWidth;
        winH = window.innerHeight;
    } else if (document.all) {
	        winW = document.body.clientWidth;
	        winH = document.body.clientHeight;
    }
    document.cookie="dims="+winW+"_"+winH;
    location.reload(false);
}
//-->
</script>
