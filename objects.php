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

// }}}

	// {{{ Presentation List Classes
	class _presentation {
		function _presentation() {
			global $baseFontSize, $jsKeyboard, $baseDir ,$HTTP_HOST;

			$this->title = 'No Title Text for this presentation yet';
			$this->navmode  = 'flash';
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
			$this->mode  = 'html';
			$this->layout = '';
		}

		function display() {
			if(isset($this->titleMode)) $this->{$this->titleMode}();
			else $this->{$this->mode}();
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
				$p = $pres[1];
				echo <<<TITLEPAGE
<br /><br /><br /><br />
<div align="center" style="font-size: $basefontsize;">$p->title</div>
<br />
<div align="center" style="font-size: $smallerfontsize;">$p->event</div>
<br />
<div align="center" style="font-size: $smallerfontsize;">$p->date. $p->location</div>
<br />
<div align="center" style="font-size: $smallerfontsize;">$p->speaker &lt;<a href="mailto:$p->email">$p->email</a>&gt;</div>
<br />
<div align="center" style="font-size: $smallerfontsize;"><a href="$p->url">$p->url</a></div>
<br />
TITLEPAGE;
				
			}
		}

		function flash() {
			global $coid, $winW, $winH, $baseDir;

			list($dx,$dy) = getFlashDimensions($this->titleFont,$this->title,$this->titleSize);
			$dx = $winW;  // full width
?>
<div align="<?=$this->titleAlign?>" class="sticky">
<embed src="<?=$baseDir?>flash.php/<?echo time()?>?type=title&dy=<?=$dy?>&dx=<?=$dx?>&coid=<?=$coid?>" quality=high loop=false 
pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"
type="application/x-shockwave-flash" width="<?=$dx?>" height="<?=$dy?>">
</embed>
</div>
<?php
		}

	}
	// }}}

	// {{{ Blurb Class
	class _blurb {

		function _blurb() {
			$this->font  = 'fonts/Verdana.fdb';
			$this->align = 'left';
			$this->fontsize     = '2.66em';
			$this->marginleft   = '1em';
			$this->marginright  = '1em';
			$this->margintop    = '0.2em';	
			$this->marginbottom = '0em';	
			$this->title        = '';
			$this->titlecolor   = '#000000';
			$this->text         = '';
			$this->textcolor    = '#000000';
			$this->mode         = 'html';
		}

		function display() {
			$this->{$this->mode}();
		}

		function html() {
			if(!empty($this->title)) {
				echo "<div style=\"font-size: $this->fontsize; color: $this->titlecolor\">$this->title</div>\n";
			}
			if(!empty($this->text)) {
				echo "<div style=\"font-size: ".(2*(float)$this->fontsize/3)."em; color: $this->textcolor; margin-left: $this->marginleft; margin-right: $this->marginright; margin-top: $this->margintop; margin-bottom: $this->marginbottom;\">$this->text</div><br />\n";
			}
		}
	}
	// }}}

	// {{{ Image Class
	class _image {
		function _image() {
			$this->filename = '';
			$this->mode = 'html';
			$this->align = 'left';
			$this->marginleft = "auto";
			$this->marginright = "auto";
		}

		function display() {
			$this->{$this->mode}();
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
	}
	// }}}

	// {{{ Example Class
	class _example {
		function _example() {
			$this->filename = '';
			$this->type = 'php';
			$this->mode = 'html';
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
			$this->{$this->mode}();
		}

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
				if(!empty($objs[1]->examplebackground)) $_html_examplebackground = $objs[1]->examplebackground;
				if(!empty($this->examplebackground)) $_html_examplebackground = $this->examplebackground;

				echo '<div class="emcode" style="font-size: '.$_html_sz."em; margin: -$_html_offset 0 0 -$_html_offset;".
					(($this->type=='shell') ? 'font-family: monotype.com, courier, monospace; background: #000000; color: #ffffff; padding: 0px;' : '').
					((!empty($_html_examplebackground)) ? "background: $_html_examplebackground;" : '').
					'">';

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

						default:
							echo "<pre>".htmlspecialchars($this->text)."</pre>\n";
							break;
					}
				}
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
	}
	// }}}

	// {{{ List Class
	class _list {
		function _list() {
			$this->mode        = 'html';
			$this->fontsize    = '3em';
			$this->marginleft  = '0em';
			$this->marginright = '0em';
		}

		function display() {
			$this->{$this->mode}();
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
	}
	// }}}

	// {{{ Bullet Class
	class _bullet {

		function _bullet() {
			$this->mode = 'html';
			$this->text = '';
			$this->slide = '';
			$this->id = '';
		}

		function display() {
			$this->{$this->mode}();
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
			$this->mode         = 'html';
		}

		function display() {
			$this->{$this->mode}();
		}

		function html() {
			if(empty($this->text)) $this->text = $this->href;
			if(!empty($this->leader)) $leader = $this->leader;
			else $leader='';
			if(!empty($this->text)) {
				echo "<div align=\"$this->align\" style=\"font-size: $this->fontsize; color: $this->textcolor; margin-left: $this->marginleft; margin-right: $this->marginright; margin-top: $this->margintop; margin-bottom: $this->marginbottom;\">$leader<a href=\"$this->href\">$this->text</a></div><br />\n";
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

		function _divide() {
			$this->mode         = 'html';
		}

		function display() {
			$this->{$this->mode}();
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
	}
	// }}}

	// {{{ Footer Class
	class _footer {

		function _footer() {
			$this->mode         = 'html';
		}

		function display() {
			$this->{$this->mode}();
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
	}
	// }}}
?>
