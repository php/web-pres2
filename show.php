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
	session_register('prevTitle');
	session_register('nextTitle');

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
<?
	if(isset($dims)) {
		list($winW, $winH) = explode('_',$dims);
	}


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
	// Fetch info about previous slide
	$prevSlideNum = $nextSlideNum = 0;
	if($slideNum > 0) {
		$prevSlideNum = $slideNum-1;
		$r =& new XML_Slide($pres[1]->slides[$slideNum-1]->filename);
		$r->parse();
		$objs = $r->getObjects();
		$prevTitle = $objs[1]->title;
	} else $prevTitle = '';
	if($slideNum < $maxSlideNum) {
		$nextSlideNum = $slideNum+1;
		$r =& new XML_Slide($pres[1]->slides[$slideNum+1]->filename);
		$r->parse();
		$objs = $r->getObjects();
		$nextTitle = (isset($objs[1]->title)) ? $objs[1]->title : '';
	} else $nextTitle = '';

	$r =& new XML_Slide($pres[1]->slides[$slideNum]->filename);
	$r->setErrorHandling(PEAR_ERROR_DIE,"%s\n");
	$r->parse();

	$objs = $r->getObjects();

	switch($pres[1]->navmode) {
		case 'flash':
			$body_style = "margin-top: 7em;";
			break;

		default:
			$body_style = "margin-top: 7em;";
			break;
	}
	

	/* default is css.php */
	include $pres[1]->stylesheet;
?>
</head>
<body onResize="get_dims();" style="<?=$body_style?>">
<?
	if(isset($pres[1]->jskeyboard) && $pres[1]->jskeyboard) { 
		include 'keyboard.js.php';
	}

	while(list($coid,$obj) = each($objs)) {
		$obj->display();
	}
	/*
	echo "<pre>DEBUG";
	print_r($pres);
	print_r($objs);
	echo "</pre>";
	*/
?>
</body>
</html>
