<?php
	require_once 'config.php';

	if(!strlen($PATH_INFO)) {
		header('Location: http://'.$HTTP_HOST.$baseDir);
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

	if(isset($objs[1]->navmode)) $navmode = $objs[1]->navmode;
	else if(isset($pres[1]->navmode)) $navmode = $pres[1]->navmode;
	else $navmode = 'html';

	// Override with user-selected display mode
	if(isset($selected_display_mode)) $navmode = $selected_display_mode;

	switch($navmode) {
		case 'html':
		case 'flash':
			$body_style = "margin-top: 7em;";
			include 'getwidth.php';
			include $pres[1]->stylesheet;
			/* the following includes scripts necessary for various animations */
			if($pres[1]->animate || $pres[1]->jskeyboard) include 'keyboard.js.php';
			echo '</head>';
			echo "<body onResize=\"get_dims();\" style=\"$body_style\">\n";
			break;
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
