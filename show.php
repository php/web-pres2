<?php
	error_reporting(E_ALL);

	require_once 'config.php';

	set_time_limit(0);  // PDF generation can take a while
	if(!strlen($_SERVER['PATH_INFO'])) {
		header('Location: http://'.$_SERVER['HTTP_HOST'].$baseDir);
		exit;
	}

	require_once 'XML_Presentation.php';
	require_once 'XML_Slide.php';

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

	$presFile = trim($_SERVER['PATH_INFO']);			
	$presFile = trim($presFile,'/');			
	$lastPres = -1;
    if(isset($currentPres)) {
		$lastPres = $currentPres;
	}
	@list($currentPres,$slideNum) = explode('/',$presFile);
	if(!$slideNum) $slideNum = 0;
	if($slideNum<0) $slideNum = 0;
	if(!isset($_SESSION['titlesLoaded'])) $_SESSION['titlesLoaded'] = 0;
	$presFile = str_replace('..','',$currentPres);  // anti-hack
	$presFile = "$presentationDir/$presFile".'.xml';

	if(isset($_COOKIE['dims'])) {
		list($winW, $winH) = explode('_',$_COOKIE['dims']);
	}

	$p =& new XML_Presentation($presFile);
	$p->setErrorHandling(PEAR_ERROR_DIE,"%s\n");
	$p->parse();
	$pres = $p->getObjects();

	if(empty($_SESSION['titles']) || $lastPres != $currentPres || $_SESSION['titlesLoaded'] < filemtime($presFile)) {
		$_SESSION['titles'] = get_all_titles($pres[1]);
		$_SESSION['titlesLoaded'] = filemtime($presFile);
	}

	$maxSlideNum = count($pres[1]->slides)-1;

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

	if(isset($objs[1]->navmode)) $navmode = $objs[1]->navmode;
	else if(isset($pres[1]->navmode)) $navmode = $pres[1]->navmode;
	else $navmode = 'html';
	// Override with user-selected display mode
	if(isset($selected_display_mode)) $navmode = $selected_display_mode;

	switch($navmode) {
		case 'html':
		case 'flash':
			// Determine if we should cache
			$cache_ok = 1;
			foreach($objs as $obj) {
				if(is_a($obj, '_example')) {
					$cache_ok = 0;
				}
			}
			reset($objs); // shouldn't be necessary, but is

			// allow caching
			if($cache_ok) header("Last-Modified: " . date("r", filemtime($presentationDir.'/'.$pres[1]->slides[$slideNum]->filename)));
			echo <<<HEADER
<html>
<head>
<base href="http://$_SERVER[HTTP_HOST]$baseDir">
HEADER;
			switch($pres[1]->template) {
			case 'simple':
				$body_style = "margin-top: 1em;";
				break;
			default:
				$body_style = "margin-top: 7em;";
				break;
			}
			include 'getwidth.php';
			include $pres[1]->stylesheet;
			/* the following includes scripts necessary for various animations */
			if($pres[1]->animate || $pres[1]->jskeyboard) include 'keyboard.js.php';
			// Link Navigation (and next slide pre-fetching))
			if($slideNum) echo '<link rel="prev" href="'.$presentationDir.'/'.$pres[1]->slides[$prevSlideNum]->filename."\" />\n";
			if($nextSlideNum) echo '<link rel="next" href="'.$presentationDir.'/'.$pres[1]->slides[$nextSlideNum]->filename."\" />\n";
			echo '</head>';
			echo "<body onResize=\"get_dims();\" style=\"".$body_style."\">\n";
			while(list($coid,$obj) = each($objs)) {
				$obj->display();
			}
			/*
			echo "<pre>DEBUG";
			print_r($pres);
			print_r($objs);
			echo "</pre>";
			*/
			echo <<<FOOTER
</body>
</html>
FOOTER;
			break;

		case 'plainhtml':
			echo <<<HEADER
<html>
<head>
<base href="http://$_SERVER[HTTP_HOST]$baseDir">
</head>
<body>
HEADER;
			while(list($coid,$obj) = each($objs)) {
				$obj->display();
			}
			echo <<<FOOTER
</body>
</html>
FOOTER;
			break;

		case 'pdfus':
		case 'pdfusl':
		case 'pdfa4':
			$selected_display_mode = 'pdf';
			switch($navmode) {
				case 'pdfus': // US-Letter
					$pdf_x = 612;  $pdf_y = 792;
					break;
				case 'pdfusl': // US-Legal
					$pdf_x = 612;  $pdf_y = 1008;
					break;
				case 'pdfa4':  // A4
					$pdf_x = 595;  $pdf_y = 842;
					break;
			}
			// In PDF mode we loop through all the slides and make a single
			// big multi-page PDF document.
			$page_number = 0;
			$pdf = pdf_new();
			if(!empty($pdfResourceFile)) pdf_set_parameter($pdf, "resourcefile", $pdfResourceFile);
			pdf_open_file($pdf);
			pdf_set_info($pdf, "Author",isset($pres[1]->speaker)?$pres[1]->speaker:"Anonymous");
			pdf_set_info($pdf, "Title",isset($pres[1]->title)?$pres[1]->title:"No Title");
			pdf_set_info($pdf, "Creator", "See Author");
			pdf_set_info($pdf, "Subject", isset($pres[1]->topic)?$pres[1]->topic:"");

			while(list($slideNum,$slide) = each($pres[1]->slides)) {
				$r =& new XML_Slide($presentationDir.'/'.$pres[1]->slides[$slideNum]->filename);
				$r->setErrorHandling(PEAR_ERROR_DIE,"%s\n");
				$r->parse();

				$objs = $r->getObjects();
				my_new_pdf_page($pdf, $pdf_x, $pdf_y);
				$pdf_cx = $pdf_cy = 0;  // Globals that keep our current x,y position
				while(list($coid,$obj) = each($objs)) {
					$obj->display();
				}
				pdf_end_page($pdf);
			}

			my_new_pdf_page($pdf, $pdf_x, $pdf_y);
			pdf_set_font($pdf, $pdf_font , -20, 'winansi');
			$dx = pdf_stringwidth($pdf, "Index");
			$x = (int)($pdf_x/2 - $dx/2);
			pdf_set_parameter($pdf, "underline", 'true');
			pdf_show_xy($pdf,"Index",$x,60);
			pdf_set_parameter($pdf, "underline", 'false');
			$pdf_cy = pdf_get_value($pdf, "texty")+30;
			$old_cy = $pdf_cy;
			pdf_set_font($pdf, $pdf_font , -12, 'winansi');
			foreach($page_index as $pn=>$ti) {
				if($ti=='titlepage') continue;
				$ti.='    ';
				while(pdf_stringwidth($pdf,$ti)<($pdf_x-$pdf_cx*2.5-140)) $ti.='.';
				pdf_show_xy($pdf, $ti, $pdf_cx*2.5, $pdf_cy);
				$dx = pdf_stringwidth($pdf, $pn);
				pdf_show_xy($pdf, $pn, $pdf_x-2.5*$pdf_cx-$dx, $pdf_cy);
				$pdf_cy+=15;
				if($pdf_cy > ($pdf_y-50)) {
					pdf_end_page($pdf);
					pdf_begin_page($pdf, $pdf_x, $pdf_y);
					pdf_translate($pdf,0,$pdf_y);
					pdf_scale($pdf, 1, -1);
					pdf_set_value($pdf,"horizscaling",-100);
					$pdf_cy = $old_cy;
					pdf_set_font($pdf, $pdf_font , -12, 'winansi');
				}
			}
			pdf_end_page($pdf);
			pdf_close($pdf);
			$data = pdf_get_buffer($pdf);
			header('Content-type: application/pdf');
			header("Content-disposition: inline; filename=$currentPres.pdf");
			header("Content-length: " . strlen($data));
			echo $data;
			break;
	}

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
