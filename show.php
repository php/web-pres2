<?php
	error_reporting(E_ALL);

	require_once 'config.php';
	require_once 'sniff.php';

	set_time_limit(0);  // PDF generation can take a while
	if(!strlen($_SERVER['PATH_INFO'])) {
		header('Location: http://'.$_SERVER['HTTP_HOST'].$baseDir);
		exit;
	}

	require_once 'XML_Presentation.php';
	require_once 'XML_Slide.php';

	session_start();

	$presFile = trim($_SERVER['PATH_INFO']);			
	$presFile = trim($presFile,'/');			
	$lastPres = null;
	if(isset($_SESSION['currentPres'])) {
		$lastPres = $_SESSION['currentPres'];
	}
	@list($_SESSION['currentPres'],$slideNum) = explode('/',$presFile);
	if(!isset($_SESSION['titlesLoaded'])) $_SESSION['titlesLoaded'] = 0;
	$presFile = str_replace('..','',$_SESSION['currentPres']);  // anti-hack
	$presFile = "$presentationDir/$presFile".'.xml';

	if(isset($_COOKIE['dims'])) {
		list($winW, $winH) = explode('_',$_COOKIE['dims']);
	}
	if (!isset($winW)) {
		$winW = 0;
	}
	if (!isset($winH)) {
		$winH = 0;
	}

	$p =& new XML_Presentation($presFile);
	$p->setErrorHandling(PEAR_ERROR_DIE,"%s\n");
	$p->parse();
	$pres = $p->getObjects();

	// nav mode no longer settable on a per-slide basis
	if(isset($pres[1]->navmode)) $navmode = $pres[1]->navmode;
	else $navmode = 'html';
	// Override with user-selected display mode
	if(isset($_SESSION['selected_display_mode'])) $navmode = $_SESSION['selected_display_mode'];

	$mode = new $navmode;

	if(empty($_SESSION['titles']) || $lastPres != $_SESSION['currentPres'] || $_SESSION['titlesLoaded'] < filemtime($presFile)) {
		$_SESSION['titles'] = get_all_titles($pres[1]);
		$_SESSION['titlesLoaded'] = filemtime($presFile);
	}

	$maxSlideNum = count($pres[1]->slides)-1;

	// Make sure we don't go beyond the first slide
	if(!$slideNum || $slideNum<0) $slideNum = 0;
	// Make sure we don't go beyond the last slide
	if($slideNum > $maxSlideNum) {
		$slideNum = $maxSlideNum;
	}
	// Fetch info about previous slide
	$prevSlideNum = $nextSlideNum = 0;
	if($slideNum > 0) {
		$prevSlideNum = $slideNum-1;
		$prevTitle = @$_SESSION['titles'][$prevSlideNum]['title'];
	} else $prevTitle = '';
	if($slideNum < $maxSlideNum) {
		$nextSlideNum = $slideNum+1;
		$nextTitle = @$_SESSION['titles'][$nextSlideNum]['title'];
	} else $nextTitle = '';

	$slideDir = dirname($presentationDir.'/'.$pres[1]->slides[$slideNum]->filename).'/';

	$r =& new XML_Slide($presentationDir.'/'.$pres[1]->slides[$slideNum]->filename);
	$r->setErrorHandling(PEAR_ERROR_DIE,"%s\n");
	$r->parse();

	$objs = $r->getObjects();

	$pres[1]->display();

function get_all_titles($pres) {
	global $presentationDir;

	reset($pres);
	while(list($slideNum,$slide) = each($pres->slides)) {
		$r =& new XML_Slide($presentationDir.'/'.$pres->slides[$slideNum]->filename);
		$r->parse();

		$objs = $r->getObjects();
		if(!$objs) continue;
		$titles[$slideNum]['title'] = $objs[1]->title;
		if(!empty($pres->slides[$slideNum]->Section)) {
			$titles[$slideNum]['section'] = $pres->slides[$slideNum]->Section;
		}
		if(!empty($pres->slides[$slideNum]->Chapter)) {
			$titles[$slideNum]['chapter'] = $pres->slides[$slideNum]->Chapter;
		}
	}
	return($titles);
}
?>
