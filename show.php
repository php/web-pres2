<?php
	require_once 'config.php';
	$c = compact('presentationDir', 'baseDir', 'showScript', 'helpPage', 'baseFontSize', 
	             'flashFontScale', 'pdfFontScale', 'pdfResourceFile', 'pdf_font', 
	             'pdf_font_bold', 'pdf_example_font', 'jsKeyboard');

	set_time_limit(0); // PDF generation can take a while
	if(!strlen($_SERVER['PATH_INFO'])) {
		header('Location: http://'.$_SERVER['HTTP_HOST'].$baseDir);
		exit;
	}

	require_once 'XML_Presentation.php';
	require_once 'XML_Slide.php';

	session_start();

	// Figure out which presentation file to read and slide to show
	$presFile = trim($_SERVER['PATH_INFO']);
	$presFile = trim($presFile ,'/');
	if (substr($presFile,-4) == ".pdf") {
		$navmode = 'pdfus';
		$presFile = substr($presFile, 0, -4);
	}
	$lastPres = null;
	if(isset($_SESSION['currentPres'])) {
		$lastPres = $_SESSION['currentPres'];
	}

	/*
	Adding support for URLs such as
	http://shiflett.org/talks/oscon2004/php-security
	*/
	$urlArray = explode('/', (string) $presFile);
	$slideNumIndex = sizeof($urlArray) - 1;
	if ($urlArray[$slideNumIndex] == strval(intval($urlArray[$slideNumIndex]))) {
		$slideNum = $urlArray[$slideNumIndex];
		unset($urlArray[$slideNumIndex]);
	}
	else {
		$slideNum = '';
	}
	$_SESSION['currentPres'] = trim(implode('-', $urlArray), '-');

	if(!isset($_SESSION['titlesLoaded'])) $_SESSION['titlesLoaded'] = 0;
	$presFile = str_replace('..','',(string)$_SESSION['currentPres']); // anti-hack
	$presFile = "$presentationDir/$presFile".'.xml';
	
	if (!file_exists($presFile)) {
		$presFile = "$baseDir/tools/missing.xml";
	}

	// Load in the presentation
	$fh = fopen($presFile, "rb");
	$p = new XML_Presentation($fh);
	$p->setErrorHandling(PEAR_ERROR_DIE,"%s\n");
	$p->parse();
	$pres = $p->getObjects();
	$pres = $pres[1];

	// Set display: html, plainhtml, pdfus, etc.
	if (!isset($navmode)) {
		if (isset($_SESSION['selected_display_mode'])) { 
			$navmode = $_SESSION['selected_display_mode'];
		} elseif (isset($pres->navmode)) { 
			$navmode = $pres->navmode;
		}	else { 
			$navmode = 'html';
		}
	}
	$mode = new $navmode($c);

	// Browser window size, used for JavaScript resizing
	if(isset($_COOKIE['dims'])) {
		list($mode->winW, $mode->winH) = explode('_',$_COOKIE['dims']);
	}
	if (!isset($mode->winW)) {
		$mode->winW = 0;
	}
	if (!isset($winH)) {
		$mode->winH = 0;
	}

	// Store slide titles in session variable
	if(empty($_SESSION['titles']) || $lastPres != $_SESSION['currentPres'] || $_SESSION['titlesLoaded'] < filemtime($presFile)) {
		$_SESSION['titles'] = get_all_titles($pres);
		$_SESSION['titlesLoaded'] = filemtime($presFile);
	}

	// Sanity check slide number ranges
	$mode->maxSlideNum = count($pres->slides)-1;
	if (empty($slideNum) || $slideNum < 0) {
		// Make sure we don't go beyond the first slide
		$mode->slideNum = 0;
	} elseif($slideNum > $mode->maxSlideNum) {
		// Make sure we don't go beyond the last slide
		$mode->slideNum = $mode->maxSlideNum;
	} else {
		$mode->slideNum = $slideNum;
	}
	
	// Fetch info about previous and next slides
	$mode->prevSlideNum = $mode->nextSlideNum = 0;
	if($mode->slideNum > 0) {
		$mode->prevSlideNum = $mode->slideNum-1;
		$mode->prevTitle = @$_SESSION['titles'][$mode->prevSlideNum]['title'];
	} else $mode->prevTitle = '';
	if($mode->slideNum < $mode->maxSlideNum) {
		$mode->nextSlideNum = $mode->slideNum+1;
		$mode->nextTitle = @$_SESSION['titles'][$mode->nextSlideNum]['title'];
	} else $mode->nextTitle = '';

	// Load the slide
	$mode->slideDir = dirname($presentationDir.'/'.$pres->slides[$mode->slideNum]->filename).'/';
	$fh = fopen($presentationDir.'/'.$pres->slides[$mode->slideNum]->filename, "rb");
	$r = new XML_Slide($fh);
	$r->setErrorHandling(PEAR_ERROR_DIE,"%s\n");
	$r->parse();
	// Display slide
	$mode->objs = $r->getObjects();
	$mode->pres =& $pres;
	$pres->display();

function get_all_titles($pres) {
	global $presentationDir;

	while(list($slideNum,$slide) = each($pres->slides)) {
		$fh = fopen($presentationDir.'/'.$pres->slides[$slideNum]->filename, "rb");
		$r = new XML_Slide($fh);
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
