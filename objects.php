<?php
// vim: set tabstop=4 shiftwidth=4 fdm=marker:

// {{{ Helper functions

// {{{ getFLashDimensions - Find the height and width of the given flash string
function getFlashDimensions($font,$title,$size) {
	$f = new SWFFont($font);
	$t = new SWFText();
	$t->setFont($f);
	$t->setHeight($size);
	$dx = $t->getWidth($title) + 10;
	$dy = $size+10;
	return array($dx,$dy);
}
// }}}

// {{{ my_new_pdf_page($pdf, $x, $y)
function my_new_pdf_page($pdf, $x, $y) {
	global $page_number;

	$page_number++;
	pdf_begin_page($pdf, $x, $y);
	// Having the origin in the bottom left corner confuses the
	// heck out of me, so let's move it to the top-left.
	pdf_translate($pdf,0,$y);
	pdf_scale($pdf, 1, -1);   // Reflect across horizontal axis
	pdf_set_value($pdf,"horizscaling",-100); // Mirror
}
// }}}

// {{{ my_pdf_page_number($pdf)
function my_pdf_page_number($pdf) {
	global $pdf_x, $pdf_y, $pdf_cx, $pdf_cy, $page_number, $page_index, $pdf_font;

	if(isset($page_index[$page_number]) && $page_index[$page_number] == 'titlepage') return;
	pdf_set_font($pdf, $pdf_font, -10, 'winansi');
	$dx = pdf_stringwidth($pdf,"- $page_number -");
	$x = (int)($pdf_x/2 - $dx/2);
	$pdf_cy = pdf_get_value($pdf, "texty");
	pdf_show_xy($pdf, "- $page_number -", $x, $pdf_y-20);
}
// }}}

/* {{{ my_pdf_paginated_code($pdf, $data, $x, $y, $tm, $bm, $lm, $rm, $font, $fs) {

   Function displays and paginates a bunch of text.  Wordwrapping is also
   done on long lines.  Top-down coordinates and a monospaced font are assumed.

     $data = text to display
     $x    = width of page
     $y    = height of page
     $tm   = top-margin
     $bm   = bottom-margin
     $lm   = left-margin
     $rm   = right-margin
     $font = font name
     $fs   = font size
*/
function my_pdf_paginated_code($pdf, $data, $x, $y, $tm, $bm, $lm, $rm, $font, $fs) {

	pdf_set_font($pdf, $font, $fs, 'winansi');	
	$cw = pdf_stringwidth($pdf,'m'); // Width of 1 char - assuming monospace
	$linelen = (int)(($x-$lm-$rm)/$cw);  // Number of chars on a line

	$pos = $i = 0;
	$len = strlen($data);

	pdf_set_text_pos($pdf, $lm, $tm);
	
	$np = true;
	while($pos < $len) {
		$nl = strpos(substr($data,$pos),"\n");
		if($nl===0) {
			if($np) { pdf_show($pdf, ""); $np = false; }
			else pdf_continue_text($pdf, "");
			$pos++;
			continue;
		}
		if($nl!==false) $ln = substr($data,$pos,$nl);
		else { 
			$ln = substr($data,$pos);
			$nl = $len-$pos;
		}
		if($nl>$linelen) { // Line needs to be wrapped
			$ln = wordwrap($ln,$linelen);
			$out = explode("\n", $ln);
		} else {
			$out[0] = $ln;	
		}
		foreach($out as $l) {
			if($np) { pdf_show($pdf, $l); $np = false; }
			else pdf_continue_text($pdf, $l);
		}
		$pos += $nl+1;
		if(pdf_get_value($pdf, "texty") >= ($y-$bm)) {
			my_pdf_page_number($pdf);
			pdf_end_page($pdf);
			my_new_pdf_page($pdf, $x, $y);

			pdf_set_font($pdf, $font, $fs, 'winansi');	
			pdf_set_text_pos($pdf, $lm, 60);
			$np = true;
		}
		
	}
}
// }}}

// }}}

	// {{{ Presentation List Classes
	class _presentation {
		function _presentation() {
			global $baseFontSize, $jsKeyboard, $baseDir ,$HTTP_HOST;

			$this->title = 'No Title Text for this presentation yet';
			$this->navmode  = 'html';
			$this->mode  = 'html';
			$this->navsize=NULL; // nav bar font size
			$this->template = 'php';
			$this->jskeyboard = $jsKeyboard;
			$this->logo1 = 'images/php_logo.gif';
			$this->logo2 = NULL;
			$this->basefontsize = $baseFontSize;
			$this->backgroundcol = false;
			$this->backgroundfixed = false;
			$this->backgroundimage = false;
			$this->backgroundrepeat = false;
			$this->navbarbackground = 'url(images/trans.png) transparent fixed';
			$this->navbartopiclinks = true;
			$this->navbarheight = '6em';
			$this->examplebackground = '#cccccc';
			$this->outputbackground = '#eeee33';
			$this->shadowbackground = '#777777';
			$this->stylesheet = 'css.php';
			$this->logoimage1url = 'http://' . $HTTP_HOST . $baseDir . '/index.php';
			$this->animate=false;
		}
	}

	class _pres_slide {
		function _pres_slide() {
			$this->filename = '';
		}
	}
	// }}}

	// {{{ Slide Class
	class _slide {

		function _slide() {
			$this->title = 'No Title Text for this slide yet';
			$this->titleSize  = "3em";
			$this->titleColor = '#ffffff';
			$this->navColor = '#EFEF52';
			$this->navSize  = "2em";
			$this->titleAlign = 'center';
			$this->titleFont  = 'fonts/Verdana.fdb';
			$this->template   = 'php';
			$this->layout = '';
		}

		function display() {
			global $pres, $selected_display_mode;
			if(isset($pres[1]->navmode)) $mode = $pres[1]->navmode;
			if(isset($this->navmode)) $mode = $this->navmode;
			if(isset($selected_display_mode)) $mode = $selected_display_mode;
				
			$this->$mode();

		}

		function html() {
			global 	$slideNum, $maxSlideNum, $winW, $prevTitle, 
					$nextTitle, $currentPres, $baseDir, $showScript,
					$pres, $objs;
			
			$navsize = $this->navSize;
			if ($pres[1]->navsize) $navsize = $pres[1]->navsize;
			
			$prev = $next = 0;
			if($slideNum < $maxSlideNum) {
				$next = $slideNum+1;
			}
			if($slideNum > 0) {
				$prev = $slideNum - 1;
			}
			switch($pres[1]->template) {
				default:
				echo "<div class='sticky' align='$this->titleAlign' style='width: 100%;'><div class='navbar'>";
				if(!empty($this->logo1)) $logo1 = $this->logo1;
				else $logo1 = $pres[1]->logo1;
				if(!empty($this->logoimage1url)) $logo1url = $this->logoimage1url;
				else $logo1url = $pres[1]->logoimage1url;				
				if(!empty($logo1)) echo "<a href='$logo1url'><img src='$logo1' border='0' align='left' style='float: left;'></a>";
				if(!empty($this->logo2)) $logo2 = $this->logo2;
				else $logo2 = $pres[1]->logo2;
				if (!empty($logo2)) {
					echo "<img src='$logo2' align='right' style='float: right;'>";
				}
				echo "<div style='font-size: $this->titleSize; margin: 0 2.5em 0 0;'><a href='http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$slideNum' style='text-decoration: none; color: $this->titleColor;'>$this->title</a></div>";
				if ($pres[1]->navbartopiclinks) {
					echo "<div style='float: left; margin: -0.2em 2em 0 0; font-size: $navsize;'><a href='http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$prev' style='text-decoration: none; color: $this->navColor;'>$prevTitle</a></div>";
					echo "<div style='float: right; margin: -0.2em 2em 0 0; color: $this->navColor; font-size: $navsize;'><a href='http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$next' style='text-decoration: none; color: $this->navColor;'>$nextTitle</a></div>";
				}
				echo '</div></div>';
				break;
			}

			// Slide layout templates
			if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
				case '2columns':
					echo "<div class=\"c2left\">\n";
					break;
				case 'box':
					echo "<div class=\"box\">\n";
					break;
			}

			// Automatic slides
			if($objs[1]->template == 'titlepage') {
				$basefontsize = isset($objs[1]->fontsize) ? $objs[1]->fontsize:'5em';
				$smallerfontsize = (2*(float)$basefontsize/3).'em';
				$smallestfontsize = ((float)$basefontsize/2).'em';
				$p = $pres[1];
				$parts =  ( !empty($p->title) + !empty($p->event) +
							(!empty($p->date)||!empty($p->location)) + 
							(!empty($p->speaker)||!empty($p->email)) +
							!empty($p->url) + !empty($p->subtitle) );
				for($i=10; $i>$parts; $i--) echo "<br />\n";
				if(!empty($p->title)) 
					echo "<div align=\"center\" style=\"font-size: $basefontsize;\">$p->title</div><br />\n";
				if(!empty($p->subtitle)) 
					echo "<div align=\"center\" style=\"font-size: $smallestfontsize;\">$p->subtitle</div><br />\n";
				if(!empty($p->event))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->event</div><br />\n";
				if(!empty($p->date) && !empty($p->location))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->date. $p->location</div><br />\n";
				else if(!empty($p->date))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->date</div><br />\n";
				else if(!empty($p->location))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->location</div><br />\n";
				if(!empty($p->email) && !empty($p->email))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->speaker &lt;<a href=\"mailto:$p->email\">$p->email</a>&gt;</div><br />\n";
				else if(!empty($p->email))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">&lt;<a href=\"mailto:$p->email\">$p->email</a>&gt;</div><br />\n";
				else if(!empty($p->speaker))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->speaker</div><br />\n";
				if(!empty($p->url)) 
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\"><a href=\"$p->url\">$p->url</a></div><br />\n";
				if(!empty($p->copyright)) {
					for($i=10; $i>$parts; $i--) echo "<br />\n";
					$str = str_replace('(c)','&copy;',$p->copyright);
					$str = str_replace('(R)','&reg;',$str);
					echo "<div align\=\"center\" style=\"font-size: 1em\">$str</div>\n";
				}	
			}
		}

		function plainhtml() {
			global 	$slideNum, $maxSlideNum, $winW, $prevTitle, 
					$nextTitle, $currentPres, $baseDir, $showScript,
					$pres, $objs;
			
			$navsize = $this->navSize;
			if ($pres[1]->navsize) $navsize = $pres[1]->navsize;
			
			$prev = $next = 0;
			if($slideNum < $maxSlideNum) {
				$next = $slideNum+1;
			}
			if($slideNum > 0) {
				$prev = $slideNum - 1;
			}
			switch($pres[1]->template) {
				default:
				echo "<table border=0 width=\"100%\"><tr rowspan=2><td width=1>";
				if(!empty($this->logo1)) $logo1 = $this->logo1;
				else $logo1 = $pres[1]->logo1;
				if(!empty($this->logoimage1url)) $logo1url = $this->logoimage1url;
				else $logo1url = $pres[1]->logoimage1url;				
				if(!empty($logo1)) echo "<a href=\"$logo1url\"><img src=\"$logo1\" border=\"0\" align=\"left\"></a>\n";
				echo "</td>\n";
				if ($pres[1]->navbartopiclinks) {
					echo "<td align=\"left\">";
					if($prevTitle) echo "<a href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$prev\" style=\"text-decoration: none;\"><font size=+2>Previous: $prevTitle</font></a></td>\n";
					if($nextTitle) echo "<td align=\"right\"><a href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$next\" style=\"text-decoration: none;\"><font size=+2>Next: $nextTitle</font></a></td>";
				}
				echo "<td rowspan=2 width=1>";
				if(!empty($this->logo2)) $logo2 = $this->logo2;
				else $logo2 = $pres[1]->logo2;
				if (!empty($logo2)) {
					echo "<img src=\"$logo2\" align=\"right\">\n";
				}
				echo "</td>\n";
				echo "<tr><th colspan=3 align=\"center\"><font size=+4>$this->title</font></th></table>\n";

				break;
			}

			// Slide layout templates
			if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
				case '2columns':
					echo "<table width=\"100%\"><tr><td valign=\"top\">\n";
					break;
				case 'box':
					echo "<table><tr><td>\n";
					break;
			}

			// Automatic slides
			if($objs[1]->template == 'titlepage') {
				$basefontsize = isset($objs[1]->fontsize) ? $objs[1]->fontsize:'5em';
				$smallerfontsize = (2*(float)$basefontsize/3).'em';
				$p = $pres[1];
				$parts =  ( !empty($p->title) + !empty($p->event) +
							(!empty($p->date)||!empty($p->location)) + 
							(!empty($p->speaker)||!empty($p->email)) +
							!empty($p->url) + !empty($p->subtitle) );
				for($i=10; $i>$parts; $i--) echo "<br />\n";
				if(!empty($p->title)) 
					echo "<div align=\"center\" style=\"font-size: $basefontsize;\">$p->title</div><br />\n";
				if(!empty($p->subtitle)) 
					echo "<div align=\"center\" style=\"font-size: $smallestfontsize;\">$p->subtitle</div><br />\n";
				if(!empty($p->event))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->event</div><br />\n";
				if(!empty($p->date) && !empty($p->location))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->date. $p->location</div><br />\n";
				else if(!empty($p->date))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->date</div><br />\n";
				else if(!empty($p->location))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->location</div><br />\n";
				if(!empty($p->email) && !empty($p->email))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->speaker &lt;<a href=\"mailto:$p->email\">$p->email</a>&gt;</div><br />\n";
				else if(!empty($p->email))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">&lt;<a href=\"mailto:$p->email\">$p->email</a>&gt;</div><br />\n";
				else if(!empty($p->speaker))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->speaker</div><br />\n";
				if(!empty($p->url)) 
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\"><a href=\"$p->url\">$p->url</a></div><br />\n";
				if(!empty($p->copyright)) {
					for($i=10; $i>$parts; $i--) echo "<br />\n";
					$str = str_replace('(c)','&copy;',$p->copyright);
					$str = str_replace('(R)','&reg;',$str);
					echo "<div align\=\"center\" style=\"font-size: 1em\">$str</div>\n";
				}	
				
			}
		}

		function flash() {
			global $objs,$pres,$coid, $winW, $winH, $baseDir;

			list($dx,$dy) = getFlashDimensions($this->titleFont,$this->title,flash_fixsize($this->titleSize));
			$dx = $winW;  // full width
?>
<div align="<?=$this->titleAlign?>" class="sticky">
<embed src="<?=$baseDir?>flash.php/<?echo time()?>?type=title&dy=<?=$dy?>&dx=<?=$dx?>&coid=<?=$coid?>" quality=high loop=false 
pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"
type="application/x-shockwave-flash" width="<?=$dx?>" height="<?=$dy?>">
</embed>
</div>
<?php
			// Slide layout templates
			if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
				case '2columns':
					echo "<div class=\"c2left\">\n";
					break;
				case 'box':
					echo "<div class=\"box\">\n";
					break;
			}

			// Automatic slides
			if($objs[1]->template == 'titlepage') {
				$basefontsize = isset($objs[1]->fontsize) ? $objs[1]->fontsize:'5em';
				$smallerfontsize = (2*(float)$basefontsize/3).'em';
				$p = $pres[1];
				$parts =  ( !empty($p->title) + !empty($p->event) +
							(!empty($p->date)||!empty($p->location)) + 
							(!empty($p->speaker)||!empty($p->email)) +
							!empty($p->url) + !empty($p->subtitle) );
				for($i=10; $i>$parts; $i--) echo "<br />\n";
				if(!empty($p->title)) 
					echo "<div align=\"center\" style=\"font-size: $basefontsize;\">$p->title</div><br />\n";
				if(!empty($p->subtitle)) 
					echo "<div align=\"center\" style=\"font-size: $smallestfontsize;\">$p->subtitle</div><br />\n";
				if(!empty($p->event))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->event</div><br />\n";
				if(!empty($p->date) && !empty($p->location))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->date. $p->location</div><br />\n";
				else if(!empty($p->date))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->date</div><br />\n";
				else if(!empty($p->location))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->location</div><br />\n";
				if(!empty($p->email) && !empty($p->email))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->speaker &lt;<a href=\"mailto:$p->email\">$p->email</a>&gt;</div><br />\n";
				else if(!empty($p->email))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">&lt;<a href=\"mailto:$p->email\">$p->email</a>&gt;</div><br />\n";
				else if(!empty($p->speaker))
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">$p->speaker</div><br />\n";
				if(!empty($p->url)) 
					echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\"><a href=\"$p->url\">$p->url</a></div><br />\n";
				if(!empty($p->copyright)) {
					for($i=10; $i>$parts; $i--) echo "<br />\n";
					$str = str_replace('(c)','&copy;',$p->copyright);
					$str = str_replace('(R)','&reg;',$str);
					echo "<div align\=\"center\" style=\"font-size: 1em\">$str</div>\n";
				}	
				
			}
		}

		function pdf() {
			global 	$pdf, $pdf_x, $pdf_y, $slideNum, $maxSlideNum, 
					$currentPres, $baseDir, $showScript, $pres, $objs,
					$pdf_cx, $pdf_cy, $page_index, $page_number, $pdf_font;

			$p = $pres[1];
			$middle = (int)($pdf_y/2) - 40;

			$pdf_cy = 25;  // top-margin
			$pdf_cx = 40;
		
			if($objs[1]->template == 'titlepage') {
				$loc = $middle - 80 * ( !empty($p->title) + !empty($p->event) +
										!empty($p->date) + 
										(!empty($p->speaker)||!empty($p->email)) +
										!empty($p->url) + !empty($p->subtitle) )/2;
				if(!empty($p->title)) {
					pdf_set_font($pdf, $pdf_font, -36, 'winansi');
					pdf_show_boxed($pdf, $p->title, 10, $loc, $pdf_x-20, 40, 'center');
				}

				if(!empty($p->subtitle)) {
					$loc += 50;
					pdf_set_font($pdf, $pdf_font , -22, 'winansi');
					pdf_show_boxed($pdf, $p->subtitle, 10, $loc, $pdf_x-20, 40, 'center');
				}
				
				if(!empty($p->event)) {
					$loc += 80;
					pdf_set_font($pdf, $pdf_font , -30, 'winansi');
					pdf_show_boxed($pdf, $p->event, 10, $loc, $pdf_x-20, 40, 'center');
				}

				if(!empty($p->date) && !empty($p->location)) {
					$loc += 80;
					pdf_set_font($pdf, $pdf_font , -30, 'winansi');
					pdf_show_boxed($pdf, $p->date.'. '.$p->location, 10, $loc, $pdf_x-20, 40, 'center');
				} else if(!empty($p->date)) {
					$loc += 80;
					pdf_set_font($pdf, $pdf_font , -30, 'winansi');
					pdf_show_boxed($pdf, $p->date, 10, $loc, $pdf_x-20, 40, 'center');
				} else if(!empty($p->location)) {
					$loc += 80;
					pdf_set_font($pdf, $pdf_font , -30, 'winansi');
					pdf_show_boxed($pdf, $p->location, 10, $loc, $pdf_x-20, 40, 'center');

				}
				if(!empty($p->speaker) && !empty($p->email)) {
					$loc += 80;
					pdf_set_font($pdf, $pdf_font , -30, 'winansi');
					pdf_show_boxed($pdf, $p->speaker.' <'.$p->email.'>', 10, $loc, $pdf_x-20, 40, 'center');
				} else if(!empty($p->speaker)) {
					$loc += 80;
					pdf_set_font($pdf, $pdf_font, -30, 'winansi');
					pdf_show_boxed($pdf, $p->speaker, 10, $loc, $pdf_x-20, 40, 'center');
				} else if(!empty($p->email)) {
					$loc += 80;
					pdf_set_font($pdf, $pdf_font , -30, 'winansi');
					pdf_show_boxed($pdf, ' <'.$p->email.'>', 10, $loc, $pdf_x-20, 40, 'center');
				}
				if(!empty($p->url)) {
					$loc += 80;
					pdf_set_font($pdf, $pdf_font , -30, 'winansi');
					pdf_show_boxed($pdf, $p->url, 10, $loc, $pdf_x-20, 40, 'center');
				}
				if(!empty($p->copyright)) {
					pdf_moveto($pdf, 60, $pdf_y-60);
					pdf_lineto($pdf, $pdf_x-60, $pdf_y-60);
					pdf_stroke($pdf);
					pdf_set_font($pdf, $pdf_font , -10, 'winansi');
					$x = (int)($pdf_x/2 - pdf_stringwidth($pdf, $p->copyright)/2);
					$str = str_replace('(c)',chr(0xa9), $p->copyright);
					$str = str_replace('(R)',chr(0xae), $str);
					pdf_show_xy($pdf, $str, $x, $pdf_y-45);
				}
				$page_index[$page_number] = 'titlepage';
			} else { // No header on the title page
				pdf_set_font($pdf, $pdf_font , -12, 'winansi');
				pdf_show_boxed($pdf, "Slide $slideNum/$maxSlideNum", $pdf_cx, $pdf_cy, $pdf_x-2*$pdf_cx, 1, 'left');
				if(isset($p->date)) $d = $p->date;
				else $d = strftime("%B %e %Y");
				$w = pdf_stringwidth($pdf, $d);
				pdf_show_boxed($pdf, $d, 40, $pdf_cy, $pdf_x-2*$pdf_cx, 1, 'right');
				pdf_set_font($pdf, $pdf_font , -24, 'winansi');
				pdf_show_boxed($pdf, $this->title, 40, $pdf_cy, $pdf_x-2*$pdf_cx, 1, 'center');

				$page_index[$page_number] = $this->title;
			}

			$pdf_cy += 30;	
			if($slideNum) { 
				pdf_moveto($pdf,40,$pdf_cy); 
				pdf_lineto($pdf,$pdf_x-40,$pdf_cy);	
				pdf_stroke($pdf);
			}
			$pdf_cy += 20;	
			pdf_set_text_pos($pdf, $pdf_cx, $pdf_cy);
		}
	}
	// }}}

	// {{{ Blurb Class
	class _blurb {

		function _blurb() {
			$this->font  = 'fonts/Verdana.fdb';
			$this->align = 'left';
			$this->talign = 'left';
			$this->fontsize     = '2.66em';
			$this->marginleft   = '1em';
			$this->marginright  = '1em';
			$this->margintop    = '0.2em';	
			$this->marginbottom = '0em';	
			$this->title        = '';
			$this->titlecolor   = '#000000';
			$this->text         = '';
			$this->textcolor    = '#000000';
		}

		function display() {
			global $objs, $pres, $selected_display_mode;

			if(isset($this->mode)) $mode = $this->mode;
			else if(isset($objs[1]->mode)) $mode = $objs[1]->mode;
			else if(isset($pres[1]->mode)) $mode = $pres[1]->mode;
			else $mode='html';
			if(isset($selected_display_mode)) $mode = $selected_display_mode;
			$this->$mode();
		}

		function html() {
			if(!empty($this->title)) {
				echo "<div align=\"$this->talign\" style=\"font-size: $this->fontsize; color: $this->titlecolor\">$this->title</div>\n";
			}
			if(!empty($this->text)) {
				echo "<div align=\"$this->align\" style=\"font-size: ".(2*(float)$this->fontsize/3)."em; color: $this->textcolor; margin-left: $this->marginleft; margin-right: $this->marginright; margin-top: $this->margintop; margin-bottom: $this->marginbottom;\">$this->text</div><br />\n";
			}
		}

		function plainhtml() {
			if(!empty($this->title)) {
				echo "<h1 align=\"$this->talign\"><font color=\"$this->titlecolor\">$this->title</font></h1>\n";
			}
			if(!empty($this->text)) {
				echo "<p align=\"$this->align\"><font color=\"$this->textcolor\">$this->text</font></p>\n";
			}
		}

		function flash() {
			$this->html();
		}

		function pdf() {
			global $pdf, $pdf_x, $pdf_y, $pdf_cx, $pdf_cy, $pdf_font;

			if(!empty($this->title)) {
				pdf_set_font($pdf, $pdf_font , -16, 'winansi');
				$dx = pdf_stringwidth($pdf,$this->title);
				$pdf_cy = pdf_get_value($pdf, "texty");
				switch($this->talign) {
					case 'center':
						$x = (int)($pdf_x/2 - $dx/2);
						break;
					case 'right':
						$x = $pdf_x - $dx - $pdf_cx;
						break;
					default:
					case 'left':
						$x = $pdf_cx;
						break;
				}
				pdf_set_text_pos($pdf,$x,$pdf_cy);
				pdf_continue_text($pdf, $this->title);
				$pdf_cy = pdf_get_value($pdf, "texty");
				pdf_set_text_pos($pdf,$x,$pdf_cy+5);
			}
			$pdf_cy = pdf_get_value($pdf, "texty");

			switch(strtolower($this->align)) {
				case 'right':
					$align = 'right';
					break;
				case 'center':
					$align = 'center';
					break;
				default:
					$align = "justify";
					break;
			}
			pdf_set_font($pdf, $pdf_font , -12, 'winansi');
			$height=10;	
			while(pdf_show_boxed($pdf, $this->text, $pdf_cx+20, $pdf_cy-$height, $pdf_x-2*($pdf_cx-20), $height, $align, 'blind')) $height+=10;

			if( ($pdf_cy + $height) > $pdf_y-40 ) {
				my_pdf_page_number($pdf);
				pdf_end_page($pdf);
				my_new_pdf_page($pdf, $pdf_x, $pdf_y);
				$pdf_cx = 40;
				$pdf_cy = 60;
			}

			pdf_set_font($pdf, $pdf_font , -12, 'winansi');
			pdf_show_boxed($pdf, str_replace("\n",' ',$this->text), $pdf_cx+20, $pdf_cy-$height, $pdf_x-2*($pdf_cx+20), $height, $align);
			pdf_continue_text($pdf, "\n");
		}

	}
	// }}}

	// {{{ Image Class
	class _image {
		function _image() {
			$this->filename = '';
			$this->align = 'left';
			$this->marginleft = "auto";
			$this->marginright = "auto";
		}

		function display() {
			global $objs, $pres, $selected_display_mode;
			if(isset($this->mode)) $mode = $this->mode;
			else if(isset($objs[1]->mode)) $mode = $objs[1]->mode;
			else if(isset($pres[1]->mode)) $mode = $pres[1]->mode;
			else $mode='html';
			if(isset($selected_display_mode)) $mode = $selected_display_mode;
			$this->$mode();
		}

		function html() {
			if(isset($this->title)) echo '<h1>'.$this->title."</h1>\n";
			$size = getimagesize($this->filename);
?>
<div align="<?=$this->align?>"
 style="margin-left: <?=$this->marginleft?>; margin-right: <?=$this->marginright?>;">
<img src="<?=$this->filename?>" <?=$size[3]?>>
</div>
<?php

		}

		function plainhtml() {
			if(isset($this->title)) echo '<h1>'.$this->title."</h1>\n";
			$size = getimagesize($this->filename);
?>
<div align="<?=$this->align?>"
 style="margin-left: <?=$this->marginleft?>; margin-right: <?=$this->marginright?>;">
<img src="<?=$this->filename?>" <?=$size[3]?>>
</div>
<?php
		}

		function flash() {
			$this->html();
		}

		function pdf() {
			global $pdf, $pdf_x, $pdf_cx, $pdf_cy, $pdf_y, $pdf_font;

			if(isset($this->title)) {
				$pdf_cy = pdf_get_value($pdf, "texty");
				pdf_set_text_pos($pdf,$pdf_cx,$pdf_cy);
				pdf_set_font($pdf, $pdf_font , -16, 'winansi');
				pdf_continue_text($pdf, $this->title);
				pdf_continue_text($pdf, "\n");
			}
			$pdf_cy = pdf_get_value($pdf, "texty")-5;

			list($dx,$dy,$type) = getimagesize($this->filename);
			$dx = $pdf_x*$dx/1024;
			$dy = $pdf_x*$dy/1024;

			switch($type) {
				case 1:
					$im = pdf_open_gif($pdf, $this->filename);
					break;
				case 2:
					$im = pdf_open_jpeg($pdf, $this->filename);
					break;
				case 3:
					$im = pdf_open_png($pdf, $this->filename);
					break;
				case 7:
					$im = pdf_open_tiff($pdf, $this->filename);
					break;
			}
			if(isset($im)) {
				$pdf_cy = pdf_get_value($pdf, "texty");
				if(($pdf_cy + $dy) > ($pdf_y-60)) {
					my_pdf_page_number($pdf);
					pdf_end_page($pdf);
					my_new_pdf_page($pdf, $pdf_x, $pdf_y);
					$pdf_cx = 40;
					$pdf_cy = 60;
				}
				switch($this->align) {
					case 'right':
						$x = $pdf_x - $dx - $pdf_cx;
						break;
					case 'center':
						$x = (int)($pdf_x/2 - $dx/2);
						break;
					case 'left':
					default:
						$x = $pdf_cx;
						break;
				}
				pdf_save($pdf);
				pdf_translate($pdf,0,$pdf_y);

				$scale = $pdf_x/1024;
				pdf_scale($pdf,1,-1);
				pdf_place_image($pdf, $im, $x, ($pdf_y-$pdf_cy-$dy), $scale);
				pdf_restore($pdf);
				pdf_set_text_pos($pdf,$pdf_cx,$pdf_cy+$dy);
			}		
		}

	}
	// }}}

	// {{{ Example Class
	class _example {
		function _example() {
			$this->filename = '';
			$this->type = 'php';
			$this->fontsize = '2em';
			$this->rfontsize = '1.8em';
			$this->marginright = '3em';
			$this->marginleft = '3em';
			$this->margintop = '1em';
			$this->marginbottom = '0.8em';
			$this->hide = false;
			$this->result = false;
			$this->width = '';
			$this->condition = '';
			$this->linktext = "Result";
			$this->iwidth = '100%';
			$this->iheight = '80%';
			$this->localhost = false;
		}

		function display() {
			global $objs, $pres, $selected_display_mode;
			if(isset($this->mode)) $mode = $this->mode;
			else if(isset($objs[1]->mode)) $mode = $objs[1]->mode;
			else if(isset($pres[1]->mode)) $mode = $pres[1]->mode;
			else $mode='html';
			if(isset($selected_display_mode)) $mode = $selected_display_mode;
			$this->$mode();
		}
	
		// {{{ highlight()	
		function highlight() {
			if(!empty($this->filename)) {
				$_html_filename = preg_replace('/\?.*$/','',$this->filename);
				switch($this->type) {
					case 'php':
					case 'genimage':
					case 'iframe':
					case 'link':
					case 'embed':
					case 'flash':
					case 'system':
						highlight_file($_html_filename);
						break;
					case 'shell':
						$_html_file = file_get_contents($_html_filename);
						echo '<pre>'.htmlspecialchars($_html_file)."</pre>\n";
						break;
					case 'c':
						print `cat {$_html_filename} | c2html -cs`;
						break;
					case 'perl':
						print `cat {$_html_filename} | perl2html -cs`;
						break;
					case 'java':
						print `cat {$_html_filename} | java2html -cs`;
						break;
					case 'python':
						print `py2html -stdout -format:rawhtml $_html_filename`;
						break;
					case 'html':
						$_html_file = file_get_contents($_html_filename);
						echo $_html_file."\n";
						break;
							
					default:
						$_html_file = file_get_contents($_html_filename);
						echo "<pre>".htmlspecialchars($_html_file)."</pre>\n";
						break;
				}
			} else {
				switch($this->type) {
					case 'php':
						highlight_string($this->text);
						break;
					case 'shell':
						echo '<pre>'.htmlspecialchars($this->text)."</pre>\n";
						break;
					case 'html':
						echo $this->text."\n";
						break;
					case 'perl':
						print `echo "{$this->text}" | perl2html -cs`;
						break;
					case 'c':
						print `echo "{$this->text}" | c2html -cs`;
						break;

					default:
						echo "<pre>".htmlspecialchars($this->text)."</pre>\n";
						break;
				}
			}
		}
		// }}}

		// {{{ html()
		// Because we are eval()'ing code from slides, obfuscate all local 
		// variables so we don't get run over
		function html() {
			global $pres, $objs;
			// Bring posted variables into the function-local namespace 
			// so examples will work
			foreach($_POST as $_html_key => $_html_val) {
				$$_html_key = $_html_val;
			}
			foreach($_SERVER as $_html_key => $_html_val) {
				$$_html_key = $_html_val;
			}

			if(isset($this->title)) echo '<div style="font-size: '.(4*(float)$this->fontsize/3).'em;">'.$this->title."</div>\n";
			if(!$this->hide) {
				$_html_sz = (float) $this->fontsize;
				if(!$_html_sz) $_html_sz = 0.1;
				$_html_offset = (1/$_html_sz).'em';
				echo '<div class="shadow" style="margin: '.
					((float)$this->margintop).'em '.
					((float)$this->marginright+1).'em '.
					((float)$this->marginbottom).'em '.
					((float)$this->marginleft).'em;'.
					((!empty($this->width)) ? "width: $this->width;" : "").
					'">';
				if(!empty($pres[1]->examplebackground)) $_html_examplebackground = $pres[1]->examplebackground;
				if(!empty($objs[1]->examplebackground)) $_html_examplebackground = $objs[1]->examplebackground;
				if(!empty($this->examplebackground)) $_html_examplebackground = $this->examplebackground;

				echo '<div class="emcode" style="font-size: '.$_html_sz."em; margin: -$_html_offset 0 0 -$_html_offset;".
					((!empty($_html_examplebackground)) ? "background: $_html_examplebackground;" : '').
					(($this->type=='shell') ? 'font-family: monotype.com, courier, monospace; background: #000000; color: #ffffff; padding: 0px;' : '').
					'">';

				$this->highlight();

				echo "</div></div>\n";
			}
			if($this->result && (empty($this->condition) || (!empty($this->condition) && isset(${$this->condition})))) {
				if(!$this->hide) echo '<div style="font-size: '.(4*(float)$this->fontsize/3)."em;\">Output</div>\n";
				$_html_sz = (float) $this->rfontsize;
				if(!$_html_sz) $_html_sz = 0.1;
				$_html_offset = (1/$_html_sz).'em';
				if(!empty($this->global) && !isset($GLOBALS[$this->global])) {
					global ${$this->global};
				}
				if(!empty($pres[1]->outputbackground)) $_html_outputbackground = $pres[1]->outputbackground;
				if(!empty($objs[1]->outputbackground)) $_html_outputbackground = $objs[1]->outputbackground;
				if(!empty($this->outputbackground)) $_html_outputbackground = $this->outputbackground;
				if(!empty($this->anchor)) echo "<a name=\"$this->anchor\">\n";
				echo '<div class="shadow" style="margin: '.
					((float)$this->margintop).'em '.
					((float)$this->marginright+1).'em '.
					((float)$this->marginbottom).'em '.
					((float)$this->marginleft).'em;'.
					((!empty($this->rwidth)) ? "width: $this->rwidth;" : "").
					'">';
				echo '<div class="output" style="font-size: '.$_html_sz."em; margin: -$_html_offset 0 0 -$_html_offset; ".
					((!empty($_html_outputbackground)) ? "background: $_html_outputbackground;" : '').
					"\">\n";
				if(!empty($this->filename)) {
					$_html_filename = preg_replace('/\?.*$/','',$this->filename);
					switch($this->type) {
						case 'genimage':
							echo "<img src=\"$this->filename\">\n";
							break;
						case 'iframe':
							echo "<iframe width=\"$this->iwidth\" height=\"$this->iheight\" src=\"$this->filename\"><a href=\"$this->filename\" class=\"resultlink\">$this->linktext</a></iframe>\n";
							break;
						case 'link':
							echo "<a href=\"$this->filename\" class=\"resultlink\">$this->linktext</a><br />\n";
							break;	
						case 'embed':
							echo "<embed src=\"$this->filename\" class=\"resultlink\" width=\"800\" height=\"800\"></embed><br />\n";
							break;
						case 'flash':
							echo "<embed src=\"$this->filename?".time()." quality=high loop=true
pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\" 
type=\"application/x-shockwave-flash\" width=$this->iwidth height=$this->iheight>\n";
							break;
						case 'system':
							system("DISPLAY=localhost:0 $this->filename");
							break;	
						default:
							include $_html_filename;
							break;
					}
				} else {
					switch($this->type) {
						default:
							eval('?>'.$this->text);
							break;
					}
				}
				echo "</div></div>\n";
				if(!empty($this->anchor)) echo "</a>\n";
			}
		}
		/// }}}

		// {{{ plainhtml()
		function plainhtml() {
			global $pres, $objs;
			// Bring posted variables into the function-local namespace 
			// so examples will work
			foreach($_POST as $_html_key => $_html_val) {
				$$_html_key = $_html_val;
			}
			foreach($_SERVER as $_html_key => $_html_val) {
				$$_html_key = $_html_val;
			}

			if(isset($this->title)) echo '<h1>'.$this->title."</h1>\n";
			if(!$this->hide) {
				if(!empty($pres[1]->examplebackground)) $_html_examplebackground = $pres[1]->examplebackground;
				if(!empty($objs[1]->examplebackground)) $_html_examplebackground = $objs[1]->examplebackground;
				if(!empty($this->examplebackground)) $_html_examplebackground = $this->examplebackground;

				echo "<table bgcolor=\"$_html_examplebackground\"><tr><td>\n";
				$this->highlight();
				echo "</td></tr></table>\n";
			}
			if($this->result && (empty($this->condition) || (!empty($this->condition) && isset(${$this->condition})))) {
				if(!$this->hide) {
					echo "<h2>Output</h2>\n";
				}
				if(!empty($this->global) && !isset($GLOBALS[$this->global])) {
					global ${$this->global};
				}
				if(!empty($pres[1]->outputbackground)) $_html_outputbackground = $pres[1]->outputbackground;
				if(!empty($objs[1]->outputbackground)) $_html_outputbackground = $objs[1]->outputbackground;
				if(!empty($this->outputbackground)) $_html_outputbackground = $this->outputbackground;
				if(!empty($this->anchor)) echo "<a name=\"$this->anchor\">\n";
				echo "<br /><table bgcolor=\"$_html_outputbackground\"><tr><td>\n";

				if(!empty($this->filename)) {
					$_html_filename = preg_replace('/\?.*$/','',$this->filename);
					switch($this->type) {
						case 'genimage':
							echo "<img src=\"$this->filename\">\n";
							break;
						case 'iframe':
						case 'link':
						case 'embed':
							echo "<a href=\"$this->filename\">$this->linktext</a><br />\n";
							break;
						case 'flash':
							echo "<embed src=\"$this->filename?".time()." quality=high loop=true
pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\" 
type=\"application/x-shockwave-flash\" width=$this->iwidth height=$this->iheight>\n";
							break;
						case 'system':
							system("DISPLAY=localhost:0 $this->filename");
							break;	
						default:
							include $_html_filename;
							break;
					}
				} else {
					switch($this->type) {
						default:
							eval('?>'.$this->text);
							break;
					}
				}
				echo "</td></tr></table>\n";
				if(!empty($this->anchor)) echo "</a>\n";
			}
		}
		// }}}

		// {{{ flash()
		function flash() {
			$this->html();
		}
		// }}}

		// {{{ pdf()
		function pdf() {
			global $pres, $objs, $pdf, $pdf_cx, $pdf_cy, $pdf_x, $pdf_y, $pdf_font, $pdf_example_font;

			// Bring posted variables into the function-local namespace 
			// so examples will work
			foreach($_POST as $_html_key => $_html_val) {
				$$_html_key = $_html_val;
			}
			foreach($_SERVER as $_html_key => $_html_val) {
				$$_html_key = $_html_val;
			}

			if(!empty($this->title)) {
				$pdf_cy = pdf_get_value($pdf, "texty");
				pdf_set_text_pos($pdf,$pdf_cx,$pdf_cy);  // Force to left-margin
				pdf_set_font($pdf, $pdf_font , -16, 'winansi');
				pdf_continue_text($pdf, $this->title);
				pdf_continue_text($pdf, "");
			}
			$pdf_cy = pdf_get_value($pdf, "texty");

			if(!$this->hide) {
				if(!empty($this->filename)) {
					$_html_filename = preg_replace('/\?.*$/','',$this->filename);
					$_html_file = file_get_contents($_html_filename);
				} else {
					$_html_file = $this->text;
				}
				switch($this->type) {
					case 'php':
					case 'genimage':
					case 'iframe':
					case 'link':
					case 'embed':
					case 'flash':
					case 'system':
					case 'shell':
					case 'c':
					case 'perl':
					case 'java':
					case 'python':
					case 'html':
					default:
						if($_html_file[strlen($_html_file)-1] != "\n") $_html_file .= "\n";
						my_pdf_paginated_code($pdf, $_html_file, $pdf_x, $pdf_y, $pdf_cy+10, 60, $pdf_cx+30, $pdf_cx, $pdf_example_font, -10);
						pdf_continue_text($pdf,"");
						break;
				}
				
			}			
			$pdf_cy = pdf_get_value($pdf, "texty");
			if($this->result && (empty($this->condition) || (!empty($this->condition) && isset(${$this->condition})))) {
				if(!$this->hide) {
					$pdf_cy = pdf_get_value($pdf, "texty");
					pdf_set_text_pos($pdf,$pdf_cx+20,$pdf_cy);  // Force to left-margin
					pdf_set_font($pdf, $pdf_font , -14, 'winansi');
					pdf_continue_text($pdf, "Output:");
					pdf_continue_text($pdf, "");
				}
				$pdf_cy = pdf_get_value($pdf, "texty");

				if(!empty($this->global) && !isset($GLOBALS[$this->global])) {
					global ${$this->global};
				}
				if(!empty($this->filename)) {
					$_html_filename = preg_replace('/\?.*$/','',$this->filename);
					switch($this->type) {
						case 'genimage':
							// need some trick to fetch the generated image here
							break;
						case 'iframe':
						case 'link':
						case 'embed':
							// don't think we can do these in pdf
							break;
						case 'flash':
							// Definitely can't do this one	
							break;
						case 'system':
							// system("DISPLAY=localhost:0 $this->filename");
							break;	
						default:
							// Need something to turn html into pdf here?
							// Perhaps just output buffering and stripslashes
							// include $_html_filename;
							break;
					}
				} else {
					switch($this->type) {
						default:
							ob_start();
							eval('?>'.$this->text);
							$data = strip_tags(ob_get_contents());
							ob_end_clean();
							if($data[strlen($data)-1] != "\n") $data .= "\n";
							my_pdf_paginated_code($pdf, $data, $pdf_x, $pdf_y, $pdf_cy, 60, $pdf_cx+30, $pdf_cx, $pdf_example_font, -10);
							pdf_continue_text($pdf,"");
							break;
					}
				}
			}

		}
		// }}}

	}
	// }}}

	// {{{ List Class
	class _list {
		function _list() {
			$this->fontsize    = '3em';
			$this->marginleft  = '0em';
			$this->marginright = '0em';
		}

		function display() {
			global $objs, $pres, $selected_display_mode;
			if(isset($this->mode)) $mode = $this->mode;
			else if(isset($objs[1]->mode)) $mode = $objs[1]->mode;
			else if(isset($pres[1]->mode)) $mode = $pres[1]->mode;
			else $mode='html';
			if(isset($selected_display_mode)) $mode = $selected_display_mode;
			$this->$mode();
		}

		function html() {
			if(isset($this->title)) {
				if(!empty($this->fontsize)) $style = "style=\"font-size: ".$this->fontsize.';"';
				echo "<div $style>".$this->title."</div>\n";
			}
			echo '<ul>';
			while(list($k,$bul)=each($this->bullets)) $bul->display();
			echo '</ul>';
		}

		function plainhtml() {
			if(isset($this->title)) echo "<h1>$this->title</h1>\n";
			echo '<ul>';
			while(list($k,$bul)=each($this->bullets)) $bul->display();
			echo '</ul>';
		}

		function flash() {
			$this->html();
		}

		function pdf() {
			global $pdf, $pdf_cx, $pdf_cy, $pdf_font;

			if(isset($this->title)) {
				$pdf_cy = pdf_get_value($pdf, "texty");
				pdf_set_text_pos($pdf,$pdf_cx,$pdf_cy);
				pdf_set_font($pdf, $pdf_font, -16, 'winansi');
				pdf_continue_text($pdf, $this->title);
				pdf_continue_text($pdf, "");
			}
			while(list($k,$bul)=each($this->bullets)) $bul->display();
			
		}
	}
	// }}}

	// {{{ Bullet Class
	class _bullet {

		function _bullet() {
			$this->text = '';
			$this->slide = '';
			$this->id = '';
		}

		function display() {
			global $objs, $pres, $selected_display_mode;
			if(isset($this->mode)) $mode = $this->mode;
			else if(isset($objs[1]->mode)) $mode = $objs[1]->mode;
			else if(isset($pres[1]->mode)) $mode = $pres[1]->mode;
			else $mode='html';
			if(isset($selected_display_mode)) $mode = $selected_display_mode;
			$this->$mode();
		}

		function html() {
			global $objs, $coid;

			$style='';
			if(!empty($this->fontsize)) $style .= "font-size: ".$this->fontsize.';';
			else if(!empty($objs[$coid]->fontsize)) $style .= "font-size: ".(2*(float)$objs[$coid]->fontsize/3).'em;';
			if(!empty($this->marginleft)) $style .= "margin-left: ".$this->marginleft.';';
			else if(!empty($objs[$coid]->marginleft)) $style .= "margin-left: ".$objs[$coid]->marginleft.';';

			if(!empty($this->marginright)) $style .= "margin-right: ".$this->marginleft.';';
			else if(!empty($objs[$coid]->marginright)) $style .= "margin-right: ".$objs[$coid]->marginright.';';

			if(!empty($this->padding)) $style .= "padding: ".$this->padding.';';
			else if(!empty($objs[$coid]->padding)) $style .= "padding: ".$objs[$coid]->padding.';';

			if ($this->slide) {
			    // we put the slide info in as an attribute to js can get it
			    echo "<div id='$this->id' slide='true' style='position:relative;'>";
			}
			echo "<li style=\"$style\">".$this->text."</li>\n";
			if ($this->slide) {
			    echo "</div>";
			}
		}

		function plainhtml() {
			echo "<li>".$this->text."</li>\n";
		}

		function flash() {
			$this->html();
		}

		function pdf() {
			global $pdf, $pdf_x, $pdf_y, $pdf_cx, $pdf_cy, $pdf_font;

			$pdf_cy = pdf_get_value($pdf, "texty");
		
			pdf_set_font($pdf, $pdf_font, -12, 'winansi');
			$height=10;	
			while(pdf_show_boxed($pdf, 'o '.$this->text, 60, $pdf_cy, $pdf_x-120, $height, 'left', 'blind')) $height+=10;
			if( ($pdf_cy + $height) > $pdf_y-40 ) {
				my_pdf_page_number($pdf);
				pdf_end_page($pdf);
				my_new_pdf_page($pdf, $pdf_x, $pdf_y);
				$pdf_cx = 40;
				$pdf_cy = 60;
			}
			pdf_set_font($pdf, $pdf_font , -12, 'winansi');
			pdf_show_boxed($pdf, 'o '.$this->text, 60, $pdf_cy-$height, $pdf_x-120, $height, 'left');
			$pdf_cy+=$height;
			pdf_set_text_pos($pdf, $pdf_cx, $pdf_cy);
			pdf_continue_text($pdf,"");	
		}
	}
	// }}}

	// {{{ Table Class
	class _table {
		function _table() {
			$this->fontsize    = '3em';
			$this->marginleft  = '0em';
			$this->marginright = '0em';
			$this->border = 0;
		}

		function display() {
			global $objs, $pres, $selected_display_mode;
			if(isset($this->mode)) $mode = $this->mode;
			else if(isset($objs[1]->mode)) $mode = $objs[1]->mode;
			else if(isset($pres[1]->mode)) $mode = $pres[1]->mode;
			else $mode='html';
			if(isset($selected_display_mode)) $mode = $selected_display_mode;
			$this->$mode();
		}

		function html() {
			if(isset($this->title)) {
				if(!empty($this->fontsize)) $style = "style=\"font-size: ".$this->fontsize.';"';
				echo "<div $style>".$this->title."</div>\n";
			}
			$i=0;
			echo '<table width="100%" border="'.$this->border.'">';
			while(list($k,$cell)=each($this->cells)) {
				if(!($i % $this->columns)) {
					echo "<tr>\n";
				}
				echo " <td>";
				$cell->display();
				echo " </td>";
				if(!(($i+1) % $this->columns)) {
					echo "</tr>\n";
				}
				$i++;
			}
			echo '</table><br />';
		}

		function plainhtml() {
			if(isset($this->title)) echo "<h1>$this->title</h1>\n";
			echo '<table width="100%" border=1>';
			$i = 0;
			while(list($k,$cell)=each($this->cells)) {
				if(!($i % $this->columns)) {
					echo "<tr>\n";
				}
				echo " <td>";
				$cell->display();
				echo " </td>";
				if(!($i % $this->columns)==0) {
					echo "</tr>\n";
				}
				$i++;
			}
			echo '</table><br />';
		}

		function flash() {
			$this->html();
		}

		function pdf() {
			global $pdf, $pdf_cx, $pdf_cy, $pdf_font;

			if(isset($this->title)) {
				$pdf_cy = pdf_get_value($pdf, "texty");
				pdf_set_text_pos($pdf,$pdf_cx,$pdf_cy);
				pdf_set_font($pdf, $pdf_font, -16, 'winansi');
				pdf_continue_text($pdf, $this->title);
				pdf_continue_text($pdf, "");
			}
			while(list($k,$cell)=each($this->cells)) $cell->display();
			pdf_continue_text($pdf, "");
		}
	}
	// }}}

	// {{{ Cell Class
	class _cell {

		function _cell() {
			$this->text = '';
			$this->slide = '';
			$this->id = '';
		}

		function display() {
			global $objs, $pres, $selected_display_mode;
			if(isset($this->mode)) $mode = $this->mode;
			else if(isset($objs[1]->mode)) $mode = $objs[1]->mode;
			else if(isset($pres[1]->mode)) $mode = $pres[1]->mode;
			else $mode='html';
			if(isset($selected_display_mode)) $mode = $selected_display_mode;
			$this->$mode();
		}

		function html() {
			global $objs, $coid;

			$style='';
			if(!empty($this->fontsize)) $style .= "font-size: ".$this->fontsize.';';
			else if(!empty($objs[$coid]->fontsize)) $style .= "font-size: ".(2*(float)$objs[$coid]->fontsize/3).'em;';
			if(!empty($this->marginleft)) $style .= "margin-left: ".$this->marginleft.';';
			else if(!empty($objs[$coid]->marginleft)) $style .= "margin-left: ".$objs[$coid]->marginleft.';';

			if(!empty($this->marginright)) $style .= "margin-right: ".$this->marginleft.';';
			else if(!empty($objs[$coid]->marginright)) $style .= "margin-right: ".$objs[$coid]->marginright.';';

			if(!empty($this->padding)) $style .= "padding: ".$this->padding.';';
			else if(!empty($objs[$coid]->padding)) $style .= "padding: ".$objs[$coid]->padding.';';

			echo "<span style=\"$style\">".$this->text."</span>\n";
		}

		function plainhtml() {
			echo $this->text."\n";
		}

		function flash() {
			$this->html();
		}

		function pdf() {
			global $pdf, $pdf_x, $pdf_y, $pdf_cx, $pdf_cy, $pdf_font;

			$pdf_cy = pdf_get_value($pdf, "texty");
		
			pdf_set_font($pdf, $pdf_font, -12, 'winansi');
			$height=10;	
			while(pdf_show_boxed($pdf, $this->text, 60, $pdf_cy, $pdf_x-120, $height, 'left', 'blind')) $height+=10;
			if( ($pdf_cy + $height) > $pdf_y-40 ) {
				my_pdf_page_number($pdf);
				pdf_end_page($pdf);
				my_new_pdf_page($pdf, $pdf_x, $pdf_y);
				$pdf_cx = 40;
				$pdf_cy = 60;
			}
			pdf_set_font($pdf, $pdf_font, -12, 'winansi');
			pdf_show_boxed($pdf, $this->text, 60, $pdf_cy-$height, $pdf_x-120, $height, 'left');
			$pdf_cy+=$height;
			pdf_set_text_pos($pdf, $pdf_cx, $pdf_cy);
			pdf_continue_text($pdf,"");	
		}
	}
	// }}}

	// {{{ Link Class
	class _link {

		function _link() {
			$this->href  = '';
			$this->align = 'left';
			$this->fontsize     = '2em';
			$this->textcolor    = '#000000';
			$this->marginleft   = '0em';
			$this->marginright  = '0em';
			$this->margintop    = '0em';	
			$this->marginbottom = '0em';	
		}

		function display() {
			global $objs, $pres, $selected_display_mode;
			if(isset($this->mode)) $mode = $this->mode;
			else if(isset($objs[1]->mode)) $mode = $objs[1]->mode;
			else if(isset($pres[1]->mode)) $mode = $pres[1]->mode;
			else $mode='html';
			if(isset($selected_display_mode)) $mode = $selected_display_mode;
			$this->$mode();
		}

		function html() {
			if(empty($this->text)) $this->text = $this->href;
			if(!empty($this->leader)) $leader = $this->leader;
			else $leader='';
			if(!empty($this->text)) {
				echo "<div align=\"$this->align\" style=\"font-size: $this->fontsize; color: $this->textcolor; margin-left: $this->marginleft; margin-right: $this->marginright; margin-top: $this->margintop; margin-bottom: $this->marginbottom;\">$leader<a href=\"$this->href\">$this->text</a></div><br />\n";
			}
		}

		function plainhtml() {
			if(empty($this->text)) $this->text = $this->href;
			if(!empty($this->leader)) $leader = $this->leader;
			else $leader='';
			if(!empty($this->text)) {
				echo "$leader<a href=\"$this->href\">$this->text</a><br />\n";
			}
		}

		function flash() {
			$this->html();
		}

		function pdf() {
			global $pdf, $pdf_cx, $pdf_x, $pdf_y, $pdf_font;

			if(empty($this->text)) $this->text = $this->href;
			if(!empty($this->leader)) $leader = $this->leader;
			else $leader='';

			if(!empty($this->text)) {
				$pdf_cy = pdf_get_value($pdf, "texty")+10;
				pdf_set_font($pdf, $pdf_font, -12, 'winansi');
				if(strlen($leader)) $lx = pdf_stringwidth($pdf, $leader);
				else $lx=0;
				$dx = pdf_stringwidth($pdf, $this->text);
				switch($this->align) {
					case 'center':
						$x = (int)($pdf_x/2-$dx/2-$lx/2);
						break;

					case 'right':
						$x = $pdf_x-$pdf_cx-$dx-$lx-15;
						break;

					case 'left':
					default:
						$x = $pdf_cx;	
						break;
				}
				pdf_add_weblink($pdf, $x+$lx, $pdf_y-$pdf_cy-3, $x+$dx+$lx, ($pdf_y-$pdf_cy)+12, $this->text);
				pdf_show_xy($pdf, $leader.$this->text, $x, $pdf_cy);
				pdf_continue_text($pdf,"");
			}
		}
	}
	// }}}

	// {{{ PHP Eval Class
	class _php {

		function _php() {
			$this->filename = '';
		}

		function display() {
			if(!empty($this->filename)) include $this->filename;
			else eval('?>'.$this->text);
		}
	}
	// }}}

	// {{{ Divider Class
	class _divide {

		function display() {
			global $objs, $pres, $selected_display_mode;
			if(isset($this->mode)) $mode = $this->mode;
			else if(isset($objs[1]->mode)) $mode = $objs[1]->mode;
			else if(isset($pres[1]->mode)) $mode = $pres[1]->mode;
			else $mode='html';
			if(isset($selected_display_mode)) $mode = $selected_display_mode;
			$this->$mode();
		}

		function html() {
			global $objs;

			// Slide layout templates
			if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
				case '2columns':
					echo "</div>\n<div class=\"c2right\">\n";
					break;
			}
		}

		function plainhtml() {
			global $objs;

			// Slide layout templates
			if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
				case '2columns':
					echo "</td>\n<td valign=\"top\">\n";
					break;
			}
		}

		function flash() {
			$this->html();
		}

		function pdf() {

		}
	}
	// }}}

	// {{{ Footer Class
	class _footer {

		function display() {
			global $objs, $pres, $selected_display_mode;
			if(isset($this->mode)) $mode = $this->mode;
			else if(isset($objs[1]->mode)) $mode = $objs[1]->mode;
			else if(isset($pres[1]->mode)) $mode = $pres[1]->mode;
			else $mode='html';
			if(isset($selected_display_mode)) $mode = $selected_display_mode;
			$this->$mode();
		}

		function html() {
			global $objs;

			// Slide layout templates
			if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
				default:
					echo "</div>\n";
					break;
			}
		}

		function plainhtml() {
			global $objs;

			// Slide layout templates
			if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
				default:
					echo "</td></tr></table>\n";
					break;
			}
		}

		function flash() {
			$this->html();
		}

		function pdf() {
			global $pdf;

			my_pdf_page_number($pdf);
		}

	}
	// }}}
?>
