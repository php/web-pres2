<?php
	require_once 'config.php';

	if(!strlen($PATH_INFO)) {
		header('Location: http://'.$HTTP_HOST.$baseDir.'list.php');
		exit;
	}

	session_start();
	session_register('pres');
	session_register('objs');
	session_register('winH');
	session_register('winW');
	session_register('currentPres');
	session_register('slideNum');
	session_register('maxSlideNum');

	$presFile = trim($PATH_INFO);			
	$presFile = trim($presFile,'/');			
	list($currentPres,$slideNum) = explode('/',$presFile);
	if(!$slideNum) $slideNum = 0;
	if($slideNum<0) $slideNum = 0;
	$presFile = str_replace('..','',$currentPres);  // anti-hack
	$presFile = "$presentationDir/$presFile".'.xml';
?>
<html>
<head>
<base href="<?="http://$HTTP_HOST".$baseDir?>">
<?/*
 A bit of fancy footwork to get the browser's inside dimensions in
 pixels.  Should work on both NS4+ and IE4+.  If it doesn't we default
 it to something sane.  The dimensions are returned to the server via
 a Javascript cookie so as to not muck up our nice clean URL.  The 
 function is called if we don't have the dimensions already, or on a
 resize event to fetch the new window dimensions.
*/?>
<script language="JavaScript1.2">
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
	top.location=top.location.href;
}
<?if(!isset($dims)) {?>
get_dims();
<? } ?>
-->
</script>
<? if(isset($dims)) {
		list($winW, $winH) = explode('_',$dims);
   }
/* The stylesheet will move out into its own file soon,
   it is just a bit easier working with an embedded one
   for now while it changes often. */
?>
<style title="Default" type="text/css">
body {
	font-size: 12pt;
	margin-left:1.5em;
	margin-right:0em;
	margin-top:6em; 
	margin-bottom:0em;
}
div.sticky {
	margin: 0;
	position: absolute;
	position: fixed;
	top: 0em;
	left: 0em;
	right: auto;
	bottom: auto;
	width: auto;
}
div.shadow {
	background: #777777;
	padding: 0.5em;
	margin: 0 1em 0 0;
}
div.emcode {
	background: #cccccc;
	border: thin solid #000000;
	padding: 0.5em;
}
div.output {
	font-family: monospace;
	background: #cccc55;
	border: thin solid #000000;
	padding: 0.5em;
}
h1 {
	font-size: 2em;
}
p,li {
	font-size: 2.6em;
}

</style>
</head>
<?php
	error_reporting(E_ALL);

	require_once 'XML_Presentation.php';
	require_once 'XML_Slide.php';
	
	$p =& new XML_Presentation($presFile);
	$p->setErrorHandling(PEAR_ERROR_DIE,"%s\n");
	$p->parse();
	$pres = $p->getObjects();

	$maxSlideNum = count($pres[1]->slides)-1;

	// Make sure we don't go beyond the last slide
	if($slideNum > $maxSlideNum) {
		$slideNum = $maxSlideNum;
	}
	$r =& new XML_Slide($pres[1]->slides[$slideNum]->filename);
	$r->setErrorHandling(PEAR_ERROR_DIE,"%s\n");
	$r->parse();

	$objs = $r->getObjects();
	
?>
<body onResize="get_dims();">
<?php
	while(list($coid,$obj) = each($objs)) {
		$obj->display();
	}
	/*
	echo "<pre>";
	print_r($pres);
	print_r($objs);
	echo "</pre>";
	*/
?>
</body>
</html>
