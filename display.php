<?php
if(!function_exists('pdf_set_font')) {
    function pdf_set_font($pdf, $font_name, $fs, $encoding) {
        $font = pdf_findfont($pdf, $font_name, $encoding, 0);
        if ($font) {
            return pdf_setfont($pdf, $font, $fs);
        }
        return false;
    }
    function pdf_open_gif($pdf, $fn) {
	return pdf_open_image_file($pdf, "gif", $fn, null, null);
    }
    function pdf_open_jpeg($pdf, $fn) {
	return pdf_open_image_file($pdf, "jpeg", $fn, null, null);
    }
    function pdf_open_png($pdf, $fn) {
	return pdf_open_image_file($pdf, "png", $fn, null, null);
    }
    function pdf_open_tiff($pdf, $fn) {
	return pdf_open_image_file($pdf, "tiff", $fn, null, null);
    }
}

class display {

    // dump in config values
    function __construct($c) {
        foreach($c as $k => $v) {
            $this->$k = $v;    
        }
    }

    function _php(&$php) {
        if(!empty($php->filename)) include $php->filename;
        else eval('?>'.$php->text);
    
    }
}

class html extends display {

    function _presentation(&$presentation) {
        global $pres, $showScript;

        // Determine if we should cache
        // need to fix this check
        $cache_ok = 1;
        foreach($this->objs as $obj) {
            if(is_a($obj, '_example')) {
                $cache_ok = 0;
            }
        }
        reset($this->objs); // shouldn't be necessary, but is

        // allow caching
        if($cache_ok) header("Last-Modified: " . date("r", filemtime($this->presentationDir.'/'.$presentation->slides[$this->slideNum]->filename)));
        echo <<<HEADER
<!doctype html>
<html>
<head>
<base href="http://$_SERVER[HTTP_HOST]$this->baseDir">
<meta charset="utf-8">
<title>{$presentation->title}</title>
HEADER;
        switch($presentation->template) {
        case 'simple':
            $body_style = "margin-top: 1em;";
            break;
        case 'empty':
            $body_style = "margin-top: 0em;";
            break;
        case 'php2':
            $body_style = "margin-top: 5em;";
            break;
        default:
            $body_style = "margin-top: 8em;";
            break;
        }
        $this->body_style = $body_style;
        include 'getwidth.php';
        include $presentation->stylesheet;
        /* the following includes scripts necessary for various animations */
        if($presentation->animate || $presentation->jskeyboard) include 'keyboard.js.php';
        // Link Navigation (and next slide pre-fetching)
        $pres_url = explode('/', trim($_SERVER['PATH_INFO'], '/'));
        $pres_url = $showScript.'/'.$pres_url[0];

        if($this->slideNum) echo '<link rel="prev" href="'.$pres_url.'/'.$this->prevSlideNum."\">\n";
        if($this->nextSlideNum) echo '<link rel="next" href="'.$pres_url.'/'.$this->nextSlideNum."\">\n";
        echo '</head>';
        foreach($this->objs as $obj) {
            $obj->display();
        }
        echo <<<FOOTER
</html>
FOOTER;
}

    function _slide(&$slide) {
        if(!empty($slide->body_style))
            $body_style = $slide->body_style;
        else
            $body_style = $this->body_style;
        $class = '';
        if ($this->pres->template != 'php')
        {
            $class = " class='{$slide->template}'";
        }
        echo "<body style=\"".$body_style."\"$class>\n";
        $currentPres = $_SESSION['currentPres'];
        
        $navsize = $slide->navsize;
        if (isset($this->pres->navsize)) $navsize = $this->pres->navsize;

        $titlesize = $slide->titleSize;
        if (isset($this->pres->titlesize)) $titlesize = $this->pres->titlesize;

        $titlecolor = $slide->titleColor;
        if (isset($this->pres->titlecolor)) $titlecolor = $this->pres->titlecolor;
        
        $prev = $next = 0;
        if($this->slideNum < $this->maxSlideNum) {
            $next = $this->slideNum+1;
        }
        if($this->slideNum > 0) {
            $prev = $this->slideNum - 1;
        }
        $slidelistH = $this->winH - 30;
        $offset=0;
        switch($this->pres->template) {
        case 'simple':
            $slide->titleColor = '#000000';
            echo "<div align=\"$slide->titleAlign\" style=\"font-size: $titlesize; margin: 0 ".$offset."em 0 0;\"><a href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$this->slideNum\" style=\"text-decoration: none; color: $titlecolor;\">".markup_text($slide->title)."</a></div>";
            break;
        case 'empty':
            $slide->titleColor = '#000000';
            break;

        case 'php2':
            echo "<div id=\"stickyBar\" class=\"sticky\" align=\"$slide->titleAlign\" style=\"align: {$slide->titleAlign}; width: 100%\"><div class=\"navbar\">";
            echo "<table style=\"float: left;\" width=\"60%\" border=\"0\" cellpadding=0 cellspacing=0><tr>\n";
            if(!empty($slide->logo1)) $logo1 = $slide->logo1;
            else $logo1 = $this->pres->logo1;
            if(!empty($slide->logoimage1url)) $logo1url = $slide->logoimage1url;
            else $logo1url = $this->pres->logoimage1url;
            if(!empty($logo1)) {
                $size = getimagesize($logo1);
                echo "<td align=\"left\" $size[3]><a href=\"$logo1url\"><img src=\"$logo1\" border=\"0\" align=\"left\" style=\"float: left; margin-bottom: 0em; margin-left: 0em;\"></a></td>";
                $offset+=2;
            }
            ?>
            <td align="center">
            <?php echo "<div align=\"center\" style=\"font-size: $titlesize; margin: 0 ".$offset."em 0 0;\"><a title=\"".$this->pres->slides[$this->slideNum]->filename."\" href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$this->slideNum\" style=\"text-decoration: none; color: $titlecolor;\">".markup_text($slide->title)."</a></div>";?>
            </td>
            </tr></table>
            <br />
            <table style="float: right">
              <tr>
              <td class="c1"><b><?php echo $this->pres->title ?></b></td>
              <td><img src="images/vline.gif" hspace="5" /></td>
              <td class="c1"><?php echo date('Y-m-d') ?></td>
              <td><img src="images/blank.gif" width="5" /></td>
              <td><?php if( $this->slideNum > 0){
                         $prevSlide = $this->slideNum - 1;
                         echo "<a title=\"$this->prevTitle\" href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$prevSlide\">"
                         . '<img src="images/back.gif" border="0" hspace="2" /></a>';
                     } 
                     if($this->slideNum < $this->maxSlideNum) $this->nextSlideNum = $this->slideNum + 1;
              ?></td>
              <td bgcolor="999999"><img src="images/blank.gif" width="25" height="1" /><br />
              <span class="c2"><b><i>&nbsp;&nbsp;
              <a title="<?php echo $this->slideNum.' of '.$this->maxSlideNum?>" href="<?php echo "http://$_SERVER[HTTP_HOST]{$this->baseDir}/slidelist.php" ?>" onClick="window.open('<?php echo "http://$_SERVER[HTTP_HOST]{$this->baseDir}/slidelist.php" ?>','slidelist','toolbar=no,directories=no,location=no,status=no,menubar=no,resizable=no,scrollbars=yes,width=300,height=500,left=<?php echo $this->winW-300 ?>,top=0'); return false" class="linka"><?php echo $this->slideNum ?></a> &nbsp; &nbsp; </i></b></span></td>
                  <td><?php if( !empty($this->nextSlideNum) )
                    echo "<a title=\"$this->nextTitle\" href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$this->nextSlideNum\">"
                        . '<img src="images/next.gif" border="0" hspace="2" /></a>';
                ?></td>
              <td><img src="images/blank.gif" height="10" width="15" /></td>
              </tr>
            </table>
            <br clear="left" />
            <hr style="margin-left: 0; margin-right: 0; border: 0; color: <?php echo $titlecolor?>; background-color: <?php echo $titlecolor?>; height: 2px">
            </div></div>
            <?php
            break;

        case 'mysql':
            echo "<div id=\"stickyBar\" class=\"sticky\" align=\"$slide->titleAlign\" style=\"width: 100%\"><div class=\"navbar\">";
            echo "<table style=\"float: left;\" width=\"60%\" border=\"0\"><tr>\n";
            if(!empty($slide->logo1)) $logo1 = $slide->logo1;
            else $logo1 = $this->pres->logo1;
            if(!empty($slide->logoimage1url)) $logo1url = $slide->logoimage1url;
            else $logo1url = $this->pres->logoimage1url;
            if(!empty($logo1)) {
                $size = getimagesize($logo1);
                echo "<td align=\"left\" $size[3]><a href=\"$logo1url\"><img src=\"$logo1\" border=\"0\" align=\"left\" style=\"float: left; margin-bottom: 0.5em; margin-left: 1em;\" alt=\"".$this->pres->slides[$this->slideNum]->filename."\"></a></td>";
                $offset+=2;
            }
            ?>
            <td align="center">
            <b style="color: CC6600; font-size: 1.5em; font-family: arial, helvetica, verdana"><?php echo markup_text($slide->title) ?></b>
            </td>
            </tr></table>
            <br />
            <table style="float: right">
              <tr>
              <td class="c1"><b><?php echo $this->pres->title ?></b></td>
              <td><img src="images/vline.gif" hspace="5" /></td>
              <td class="c1"><?php echo date('Y-m-d') ?></td>
              <td><img src="images/blank.gif" width="5" /></td>
              <td><?php if( $this->slideNum > 0){
                         $prevSlide = $this->slideNum - 1;
                         echo "<a href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$prevSlide\">"
                         . '<img src="images/back.gif" border="0" hspace="2" /></a>';
                     } 
                     if($this->slideNum < $this->maxSlideNum) $this->nextSlideNum = $this->slideNum + 1;
              ?></td>
              <td bgcolor="999999"><img src="images/blank.gif" width="25" height="1" /><br />
              <span class="c2"><b><i>&nbsp;&nbsp;
              <a href="<?php echo "http://$_SERVER[HTTP_HOST]{$this->baseDir}/slidelist.php" ?>" onClick="window.open('<?php echo "http://$_SERVER[HTTP_HOST]{$this->baseDir}/slidelist.php" ?>','slidelist','toolbar=no,directories=no,location=no,status=no,menubar=no,resizable=no,scrollbars=yes,width=300,height=500,left=<?php echo $this->winW-300 ?>,top=0'); return false" class="linka"><?php echo $this->slideNum ?></a> &nbsp; &nbsp; </i></b></span></td>
                  <td><?php if( !empty($this->nextSlideNum) )
                    echo "<a href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$this->nextSlideNum\">"
                        . '<img src="images/next.gif" border="0" hspace="2" /></a>';
                ?></td>
              <td><img src="images/blank.gif" height="10" width="15" /></td>
              </tr>
            </table>
            <br clear="left" />
            <hr style="border: 0; color: #CC6600; background-color: #CC6600; height: 2px">
            </div></div>
            <?php
            break;

        case 'css':
            echo "<div id=\"stickyBar\" class=\"sticky\" align=\"$slide->titleAlign\" style=\"width: 100%;\"><div class=\"navbar\">";
            echo <<<ENDT
<div align='center' class='navbar_title'><a href='http://{$_SERVER['HTTP_HOST']}{$this->baseDir}{$this->showScript}/$currentPres/{$this->slideNum}' style='navbar_title_a'>
ENDT;
            echo $slide->title."</a></div>";
            if (isset($slide->subtitle)) {
                echo <<<ENDST
<div class="subtitle">{$slide->subtitle}</div>
ENDST;
            }
            echo "<div class='navbar_nr'>";
            echo <<<ENDD
<a href='http://{$_SERVER['HTTP_HOST']}{$this->baseDir}/slidelist.php' class='navbar-title' onClick="window.open('http://{$_SERVER['HTTP_HOST']}{$this->baseDir}/slidelist.php','slidelist','toolbar=no,directories=no,location=no,status=no,menubar=no,resizable=no,scrollbars=yes,width=300,height=500,left=10,top=0'); return false">{$this->slideNum}/{$this->maxSlideNum}</a></div>
ENDD;
            if ($this->pres->navbartopiclinks) {
                echo "<div style=\"float: left; margin: -0.2em 2em 0 0; font-size: $navsize;\"><a href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$prev\" style=\"text-decoration: none; color: $slide->navcolor;\">".markup_text($this->prevTitle)."</a></div>";
                echo "<div style=\"float: right; margin: -0.2em 2em 0 0; color: $slide->navcolor; font-size: $navsize;\"><a href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$next\" style=\"text-decoration: none; color: $slide->navcolor;\">".markup_text($this->nextTitle)."</a></div>";
            }
            echo "</div></div>\n";
            echo "<div class=\"mainarea\">\n";
            break;

        case 'php':
        default:
            echo "<div id=\"stickyBar\" class=\"sticky\" align=\"$slide->titleAlign\" style=\"width: 100%;\"><div class=\"navbar\">";
            if(!empty($slide->logo1)) $logo1 = $slide->logo1;
            else $logo1 = $this->pres->logo1;
            if(!empty($slide->logoimage1url)) $logo1url = $slide->logoimage1url;
            else $logo1url = $this->pres->logoimage1url;                
            if(!empty($logo1)) {
                echo "<a href=\"$logo1url\"><img src=\"$logo1\" border=\"0\" align=\"left\" style=\"float: left;\" alt=\"".$this->pres->slides[$this->slideNum]->filename."\"></a>";
                $offset+=2;
            }
            echo "<div align=\"center\" style=\"font-size: $titlesize; margin: 0 ".$offset."em 0 0;\"><a href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$this->slideNum\" style=\"text-decoration: none; color: $titlecolor;\">".markup_text($slide->title)."</a></div>";
            echo "<div style=\"font-size: $navsize; float: right; margin: -2em 0 0 0;\">";
            if(!empty($slide->logo2)) $logo2 = $slide->logo2;
            else $logo2 = $this->pres->logo2;
            if (!empty($logo2)) {
                echo "<img src=\"$logo2\" border=\"0\"><br/>";
                $offset-=2;
            }
            echo "<a href=\"http://$_SERVER[HTTP_HOST]{$this->baseDir}/slidelist.php\" style=\"text-decoration: none; color: $slide->titleColor;\" onClick=\"window.open('http://$_SERVER[HTTP_HOST]{$this->baseDir}/slidelist.php','slidelist','toolbar=no,directories=no,location=no,status=no,menubar=no,resizable=no,scrollbars=yes,width=300,height=$slidelistH,left=".($this->winW-300).",top=0'); return false\">".($this->slideNum)."/".($this->maxSlideNum)."</a></div>";
            if ($this->pres->navbartopiclinks) {
                echo "<div style=\"float: left; margin: -0.2em 2em 0 0; font-size: $navsize;\"><a href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$prev\" style=\"text-decoration: none; color: $slide->navcolor;\">".markup_text($this->prevTitle)."</a></div>";
                echo "<div style=\"float: right; margin: -0.2em 2em 0 0; color: $slide->navcolor; font-size: $navsize;\"><a href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$next\" style=\"text-decoration: none; color: $slide->navcolor;\">".markup_text($this->nextTitle)."</a></div>";
            }
            echo '</div></div>';
            break;
        }

        // Slide layout templates
        if(!empty($slide->layout)) switch($slide->layout) {
            case '2columns':
                echo "<div class=\"c2left\">\n";
                break;
            case '2columns-noborder':
                echo "<div class=\"c2leftnb\">\n";
                break;
            case 'box':
                echo "<div class=\"box\">\n";
                break;
        }

        // Automatic slides
        if($slide->template == 'titlepage') {
            $basefontsize = isset($slide->fontsize) ? $slide->fontsize:'5em';
            $smallerfontsize = (2*(float)$basefontsize/3).'em';
            $smallestfontsize = ((float)$basefontsize/2).'em';
            $p = $this->pres;
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
            if(!empty($p->twitter)) {
                echo "<div align=\"center\" style=\"font-size: $smallerfontsize;\">Twitter: $p->twitter</div><br />\n";
            }
            if(!empty($p->copyright)) {
                for($i=10; $i>$parts; $i--) echo "<br />\n";
                $str = str_replace('(c)','&copy;',$p->copyright);
                $str = str_replace('(R)','&reg;',$str);
                echo "<div align\=\"center\" style=\"font-size: 1em\">$str</div>\n";
            }    

        }
    }

    function _blurb(&$blurb) {
        $effect = '';
        if($blurb->effect) $effect = "style='' effect='$blurb->effect'";

        if ($this->pres->template == 'css') {
	    if(!empty($blurb->title)) {
                echo '<div class="blurb-title">'.markup_text($blurb->title)."</div>\n";
            }
            $class = isset($blurb->class) ? $blurb->class : 'blurb';
            echo "<div {$effect}class='{$class}'>".markup_text($blurb->text)."</div>\n";
            return;
        }

        if($blurb->type=='speaker' && !$_SESSION['show_speaker_notes']) return;
        if(!empty($blurb->title)) {
            if($blurb->type=='speaker') $blurb->titlecolor='#ff3322';
            echo "<div $effect align=\"$blurb->talign\" style=\"font-size: $blurb->fontsize; color: $blurb->titlecolor\">".markup_text($blurb->title)."</div>\n";
        }
        if(!empty($blurb->text)) {
            if($blurb->type=='speaker') $blurb->textcolor='#ff3322';
            echo "<div $effect align=\"$blurb->align\" style=\"font-size: ".(2*(float)$blurb->fontsize/3)."em; color: $blurb->textcolor; margin-left: $blurb->marginleft; margin-right: $blurb->marginright; margin-top: $blurb->margintop; margin-bottom: $blurb->marginbottom;\">".markup_text($blurb->text)."</div><br />\n";
        }
    }

    function _image(&$image) {
        $effect = '';
        $class = '';
        $alt = ' alt=""';
        if($image->effect) $effect = "effect=\"$#image->effect\"";
        if(isset($image->alt)) $alt = " alt='{$image->title}'";
        if(isset($image->title)) echo "<h1 align=\"{$image->talign}\">".markup_text($image->title)."</h1>\n";
        if ($image->width) {
            $size = "width=\"{$image->width}\" height=\"{$image->height}\"";
        } else {
            $size = getimagesize($this->slideDir.$image->filename);
            if (!empty($image->scale)) {
                $width = $size[0] * $image->scale;
                $height = $size[1] * $image->scale;
                $size = "width=\"{$width}\" height=\"{$height}\"";
            } else {
                $size = $size[3];
            }
        }
        if (isset($image->class)) {
            $class=" class='{$image->class}'";
        }
	$style = '';
	if (isset($image->margintop)) {
	    $style.="margin-top: {$image->margintop};";
	}
	if (isset($image->marginbottom)) {
	    $style.="margin-bottom: {$image->marginbottom};";
	}
	if (isset($image->marginleft)) {
	    $style.="margin-left: {$image->marginleft};";
	}
	if (isset($image->marginright)) {
	    $style.="margin-right: {$image->marginright};";
	}
	if(!empty($style)) {
	    $style="style=\"$style\"";
	}
?>
<div <?php echo $effect?> align="<?php echo $image->align?>" style="margin-left: <?php echo $image->marginleft?>; margin-right: <?php echo $image->marginright?>;">
<img align="<?php echo $image->align?>" src="<?php echo $this->slideDir.$image->filename?>" <?php echo $size?> <?php echo $class?> <?php echo $style?> <?php echo $alt?>/>
</div>
<?php
        if(isset($image->clear)) echo "<br clear=\"".$image->clear."\"/>\n";

    }

    // Because we are eval()'ing code from slides, obfuscate all local 
    // variables so we don't get run over
    function _example(&$example) {
        global $pres;
		if(empty($example->filename)) $example->text = preg_replace_callback('/:-:(.*?):-:/', function($matches) { return $this->pres->{$matches[1]}; }, $example->text);


        $_html_effect = '';
        if($example->effect) $_html_effect = "effect=\"$example->effect\"";
        // Bring posted variables into the function-local namespace 
        // so examples will work
        foreach($_POST as $_html_key => $_html_val) {
            $$_html_key = $_html_val;
        }
        foreach($_SERVER as $_html_key => $_html_val) {
            $$_html_key = $_html_val;
        }

        if(isset($example->title)) {
             if ($this->pres->template == 'css') echo '<div class="example-title">'.markup_text($example->title)."</div>\n";
             else  echo '<div style="font-size: '.(4*(float)$example->fontsize/3).'em;">'.markup_text($example->title)."</div>\n";
        }
        if(!empty($pres->exampleclass)) $_html_exampleclass = $pres->exampleclass;
        if(!empty($this->objs[1]->exampleclass)) $_html_exampleclass = $this->objs[1]->exampleclass;
        if(!empty($example->class)) $_html_exampleclass = $example->class;
        if(!empty($pres->exampleoutputclass)) $_html_exampleoutputclass = $pres->exampleoutputclass;
        if(!empty($this->objs[1]->exampleoutputclass)) $_html_exampleoutputclass = $this->objs[1]->exampleoutputclass;
        if(!empty($example->outputclass)) $_html_exampleoutputclass = $example->outputclass;
        if(!$example->hide) {
            if ($this->pres->template != 'css') {
                $_html_sz = (float) $example->fontsize;
                if(!$_html_sz) $_html_sz = 0.1;
                $_html_offset = (1/$_html_sz).'em';
                echo '<div '.$_html_effect.' class="'.(empty($_html_exampleclass)?'shadow':$_html_exampleclass).'" style="margin: '.
                    ((float)$example->margintop).'em '.
                    ((float)$example->marginright+1).'em '.
                    ((float)$example->marginbottom).'em '.
                    ((float)$example->marginleft).'em;'.
                    ((!empty($example->width)) ? "width: $example->width;" : "").
                    '">';
                if(!empty($pres->examplebackground)) $_html_examplebackground = $pres->examplebackground;
                if(!empty($this->objs[1]->examplebackground)) $_html_examplebackground = $this->objs[1]->examplebackground;
                if(!empty($example->examplebackground)) $_html_examplebackground = $example->examplebackground;
/*            }

            if (($this->pres->template == 'css') and (isset($example->class))) {
                echo "<div class='{$example->class}'>";
            } else {
*/                echo '<div class="'.(empty($_html_exampleoutputclass)?'emcode':$_html_exampleoutputclass).'" style="font-size: '.$_html_sz."em; margin: -$_html_offset 0 0 -$_html_offset;".
                    ((!empty($_html_examplebackground)) ? "background: $_html_examplebackground;" : '').
                    (($example->type=='shell') ? 'font-family: monotype.com, courier, monospace; background: #000000; color: #ffffff; padding: 0px;' : '').
                    '">';
            } else {
                if (isset($example->class)) {
                    echo "<div class='{$example->class}'>";
                } else {
                    echo "<div class='example'>";
                }
            }

            $example->highlight($this->slideDir);

            if ($pres->template != 'css') {
                echo "</div>";
            }
            echo "</div>\n";
        }
        if($example->result && (empty($example->condition) || isset(${$example->condition})) && (empty($example->required_extension) || extension_loaded($example->required_extension))) {
            if (!$example->hide) {
                if ($this->pres->template == 'css' and (isset($example->class))) {
                    if (!isset($example->output_word)) {
                        $example->output_word = 'Output:';
                    }
                    echo "<div class='{$example->class}_output_text'>{$example->output_word}</div>\n";
                } else {
                    echo '<div style="font-size: '.(4*(float)$example->fontsize/3)."em;\">Output</div>\n";
                }
            }
            $_html_sz = (float) $example->rfontsize;
            if(!$_html_sz) $_html_sz = 0.1;
            $_html_offset = (1/$_html_sz).'em';
            if(!empty($example->global) && !isset($GLOBALS[$example->global])) {
                global ${$example->global};
            }
            if(!empty($pres->outputbackground)) $_html_outputbackground = $pres->outputbackground;
            if(!empty($this->objs[1]->outputbackground)) $_html_outputbackground = $this->objs[1]->outputbackground;
            if(!empty($example->outputbackground)) $_html_outputbackground = $example->outputbackground;
            if(!empty($example->anchor)) echo "<a name=\"$example->anchor\"></a>\n";
            if ($this->pres->template == 'css' and (isset($example->class))) {
                echo "<div class='{$example->class}_output'>";
            } else {
                echo '<div class="'.(empty($_html_exampleclass)?'shadow':$_html_exampleclass).'" style="margin: ';
                echo ((float)$example->margintop).'em '.
                     ((float)$example->marginright+1).'em '.
                     ((float)$example->marginbottom).'em '.
                     ((float)$example->marginleft).'em;'.
                     ((!empty($example->rwidth)) ? "width: $example->rwidth;" : "").
                     '">';
                echo '<div '.$_html_effect.' class="'.(empty($_html_exampleoutputclass)?'output':$_html_exampleoutputclass).'" style="font-size: '.$_html_sz."em; margin: -$_html_offset 0 0 -$_html_offset; ".
                     ((!empty($_html_outputbackground)) ? "background: $_html_outputbackground;" : '').
                     "\">\n";
            }
            if(!empty($example->filename)) {
                $_html_filename = preg_replace('/\?.*$/','',$this->slideDir.$example->filename);
                switch($example->type) {
                    case 'genimage':
                        echo '<img src="'.$this->slideDir.$example->filename."\">\n";
                        break;
                    case 'iframe':
			if(substr($example->filename,0,5)=='http:')
                        	echo "<iframe width=\"$example->iwidth\" height=\"$example->iheight\" src=\"$example->filename\"><a href=\"$example->filename\" class=\"resultlink\">$example->linktext</a></iframe>\n";
			else
                        	echo "<iframe width=\"$example->iwidth\" height=\"$example->iheight\" src=\"$this->slideDir$example->filename\"><a href=\"$this->slideDir$example->filename\" class=\"resultlink\">$example->linktext</a></iframe>\n";
                        break;
                    case 'link':
                        echo "<a href=\"$this->slideDir$example->filename\" class=\"resultlink\">$example->linktext</a><br />\n";
                        break;    
                    case 'nlink':
                        echo "<a href=\"$this->slideDir$example->filename\" class=\"resultlink\" target=\"_blank\">$example->linktext</a><br />\n";
                        break;
                    case 'embed':
                        echo "<embed src=\"$this->slideDir$example->filename\" class=\"resultlink\" width=\"800\" height=\"800\"></embed><br />\n";
                        break;
                    case 'flash':
                        echo "<embed src=\"$this->slideDir$example->filename?".time()." quality=high loop=true
pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\" 
type=\"application/x-shockwave-flash\" width=$example->iwidth height=$example->iheight>\n";
                        break;
                    case 'system':
                        system("DISPLAY=localhost:0 $this->slideDir$example->filename");
                        break;    
                    default:
                        if (isset($example->encoding)) {
                            ob_start();
                            include $_html_filename;
                            $result = ob_get_contents();
                            ob_end_clean();
                            echo iconv($example->encoding, 'utf8', $result);
                        } else {
                            include $_html_filename;
						}
                        break;
                }
            } else {
                if (isset($example->encoding)) {
                    $example->text = iconv('utf8', $example->encoding, $example->text);
                    ob_start();
                    if($example->type=='marked') {
                        $text = preg_replace("/^\|/m","",$example->text);
                        eval('?>'.$text);
                    } else {
                        eval('?>'.$example->text);
                    }
                    $result = ob_get_contents();
                    ob_end_clean();
                    echo iconv($example->encoding, 'utf8', $result);
                } else {
                    if($example->type=='marked') {
                        $text = preg_replace("/^\|/m","",$example->text);
                        eval('?>'.$text);
                    } else {
                        eval('?>'.$example->text);
                    }
                }
            }
            if ($this->pres->template == 'css' and (isset($example->class))) {
                echo "</div>\n";
            } else {
                echo "</div></div>\n";
            }
        }
    }

    function _break(&$break) {
        echo str_repeat("<br/>\n", $break->lines);
    }

    function _div(&$div) {
        $effect = '';
        if($div->effect) $effect = "effect=\"$div->effect\"";
        echo "<div style='' $effect>";
    }

    function _div_end(&$div) {
        echo "</div>";
    }

    function _list(&$list) {
        if (!isset($list->bullets)) return;
        $align = $style = '';
        if ( ($this->pres->template != 'css') && ( isset($list->title) )) {
            if(!empty($list->fontsize)) $style = "font-size: ".$list->fontsize.';';
            if(!empty($list->align)) $align = 'align="'.$list->align.'"';
            echo "<div $align style=\"$style\">".markup_text($list->title)."</div>\n";
        }
        if (isset($list->class)) {
            echo "<ul class='{$list->class}'>";
        } else {
            if(!empty($list->lineheight)) $style = "line-height: ".$list->lineheight.';';
            echo '<ul class="pres" style="'.$style.'">';
        }
        while(list($k,$bul)=each($list->bullets)) { $bul->display(); }
        echo '</ul>';
    }

    function _bullet(&$bullet) {
        if ($bullet->text == "") $bullet->text = "&nbsp;";
        $style='';
        $type='';
        $effect='';
        $eff_str='';
        $ml = $bullet->level;

        if(!empty($bullet->marginleft)) $ml += (float)$bullet->marginleft;
        else if(!empty($this->objs[$this->coid]->marginleft)) $ml += (float)$this->objs[$this->coid]->marginleft;

        if($ml) {
            $style .= "margin-left: ".$ml."em;";
        }

        if(!empty($bullet->lineheight)) $style .= "line-height: ".$bullet->lineheight.';';

        if(!empty($bullet->start)) {
            if(is_numeric($bullet->start)) {
                $this->objs[$this->coid]->num = (int)$bullet->start;    
            } else {
                $this->objs[$this->coid]->alpha = $bullet->start;
            }
        }
        if(!empty($bullet->type)) $type = $bullet->type;
        else if(!empty($this->objs[$this->coid]->type)) $type = $this->objs[$this->coid]->type;

        if(!empty($bullet->effect)) $effect = $bullet->effect;
        else if(!empty($this->objs[$this->coid]->effect)) $effect = $this->objs[$this->coid]->effect;

        if(!empty($bullet->fontsize)) $style .= "font-size: ".$bullet->fontsize.';';
        else if(!empty($this->objs[$this->coid]->fontsize)) $style .= "font-size: ".(2*(float)$this->objs[$this->coid]->fontsize/3).'em;';

        if(!empty($bullet->marginright)) $style .= "margin-right: ".$bullet->marginleft.';';
        else if(!empty($this->objs[$this->coid]->marginright)) $style .= "margin-right: ".$this->objs[$this->coid]->marginright.';';

        if(!empty($bullet->padding)) $style .= "padding: ".$bullet->padding.';';
        else if(!empty($this->objs[$this->coid]->padding)) $style .= "padding: ".$this->objs[$this->coid]->padding.';';

        if ($effect) {
            $eff_str = "id=\"$bullet->id\" effect=\"$effect\"";
        } 
        switch($type) {
            case 'numbered':
            case 'number':
            case 'decimal':
                $symbol = $this->objs[$this->coid]->num++ . '.';
                break;
            case 'no-bullet':
            case 'none':
                $symbol='';
                break;
            case 'alpha':
                $symbol = $this->objs[$this->coid]->alpha++ . '.';
                break;
            case 'ALPHA':
                $symbol = strtoupper($this->objs[$this->coid]->alpha++) . '.';
                break;
            case 'arrow':
                $symbol = '&rarr;';
                break;
            case 'asterisk':
                $symbol = '&lowast;';
                break;
            case 'darrow':
                $symbol = '&rArr;';
                break;
            case 'dot':
                $symbol = '&sdot;';
                break;
            case 'rgillemet':
                $symbol = '&raquo;';
                break;
            case 'csymbol':
                $symbol = '&curren;';
                break;
            case 'oplus':
                $symbol = '&oplus;';
                break;
            case 'otimes':
                $symbol = '&otimes;';
                break;
            case 'spades':
                $symbol = '&spades;';
                break;
            case 'clubs':
                $symbol = '&clubs;';
                break;
            case 'hearts':
                $symbol = '&hearts;';
                break;
            case 'diams':
                $symbol = '&diams;';
                break;
            case 'lozenge':
                $symbol = '&loz;';
                break;
            case 'hyphen':
                $symbol = '-';
                break;
            default:
                $symbol = '&bull;';
                break;
        }

        $style .= 'list-style-type: none;';

        $markedText = ($bullet->text == '&nbsp;') ? $bullet->text : markup_text(htmlspecialchars($bullet->text));

        if ($this->pres->template == 'css') {
            if (isset($bullet->class)) {
                $class = $bullet->class;
            } else {
                $class = "pres_bullet";
            }
            $attrs = '';
            if (isset($bullet->effect) && !empty($bullet->effect)) {
                $attrs = " style='' effect='{$bullet->effect}'";
            }
            if ($symbol != '&bull;')
                echo "\n<li class='$class' style='list-style-type: none;'><div $attrs><tt>{$symbol}</tt> $markedText</div></li>";
            else 
                echo "\n<li class='$class'><div $attrs>$markedText</div></li>";
        } else {
            echo "\n<div $eff_str style=\"position: relative;\"><li style=\"$style\">".'<tt>'.$symbol.'</tt> '.$markedText."</li></div>\n";
        }
    }

    function _table(&$table) {
        $align = '';
        if(!empty($table->align)) $align = 'align="'.$table->align.'"';
        if(isset($table->title)) {
            if(!empty($table->fontsize)) $style = "style=\"font-size: ".$table->fontsize.';"';
            echo "<div $align $style>".markup_text($table->title)."</div>\n";
        }
        $i=0;
        $width="100%";
        if(!empty($table->width)) {
            $width = $table->width;
        }
        if ($this->pres->template == 'css') {
            $class = isset($table->class) ? " class='{$table->class}'" : "";
            echo "<table$class>";
        } else {
            echo '<table '.$align.' width="'.$width.'" border="'.$table->border.'" '.(isset($table->bgcolor)?"bgcolor=\"{$table->bgcolor}\"":'').'>';
        }
        while(list($k,$cell)=each($table->cells)) {
            if(!($i % $table->columns)) {
                echo "<tr>\n";
            }
            echo " <td ";
            if(isset($cell->class)) echo "class=\"".$cell->class."\"";
            if(isset($cell->align)) echo "align=\"".$cell->align."\"";
            if(isset($cell->bgcolor)) echo "bgcolor=\"".$cell->bgcolor."\"";
            echo ">";
            $cell->display();
            echo " </td>";
            if(!(($i+1) % $table->columns)) {
                echo "</tr>\n";
            }
            $i++;
        }
        echo '</table><br />';
    }

    function _cell(&$cell) {
        if ($this->pres->template = "css") {
            echo markup_text($cell->text);
            return;
        }
        $style='';
        if(!empty($cell->fontsize)) $style .= "font-size: ".$cell->fontsize.';';
        else if(!empty($this->objs[$this->coid]->fontsize)) $style .= "font-size: ".(2*(float)$this->objs[$this->coid]->fontsize/3).'em;';
        if(!empty($cell->marginleft)) $style .= "margin-left: ".$cell->marginleft.';';
        else if(!empty($this->objs[$this->coid]->marginleft)) $style .= "margin-left: ".$this->objs[$this->coid]->marginleft.';';

        if(!empty($cell->marginright)) $style .= "margin-right: ".$cell->marginleft.';';
        else if(!empty($this->objs[$this->coid]->marginright)) $style .= "margin-right: ".$this->objs[$this->coid]->marginright.';';

        if(!empty($cell->padding)) $style .= "padding: ".$cell->padding.';';
        else if(!empty($this->objs[$this->coid]->padding)) $style .= "padding: ".$this->objs[$this->coid]->padding.';';

        if(!empty($cell->bold) && $cell->bold) $style .= 'font-weight: bold;';
        else if(!empty($this->objs[$this->coid]->bold) && $this->objs[$this->coid]->bold) $style .= 'font-weight: bold;';

        echo "<span style=\"$style\">".markup_text($cell->text)."</span>\n";
    }

    function _link(&$link) {
        if(empty($link->text)) $link->text = preg_replace_callback('/:-:(.*?):-:/',function($matches) { return isset($this->pres->{$matches[1]}) ? $this->pres->{$matches[1]} : '';},$link->href);
        if(empty($link->leader)) $leader = preg_replace_callback('/:-:(.*?):-:/',function($matches) { return isset($this->pres->{$matches[1]}) ? $this->pres->{$matches[1]} : '';},$link->leader);
        else $leader='';
        if (empty($link->target)) $link->target = '_self';
        if(!empty($link->text)) {
            if ($this->pres->template == 'css') {
                $class = '';
                if (empty($link->class)) $link->class = 'link'; 
                echo "<div class='{$link->class}'>".markup_text($leader)."<a href='{$link->href}' target='{$link->target}'>".markup_text($link->text)."</a></div>\n";
            }
            else {
                echo "<div align=\"$link->align\" style=\"font-size: $link->fontsize; color: $link->textcolor; margin-left: $link->marginleft; margin-right: $link->marginright; margin-top: $link->margintop; margin-bottom: $link->marginbottom;\">$leader<a href=\"$link->href\" target=\"{$link->target}\">".markup_text($link->text)."</a></div><br />\n";
            }
        }
    }

    function _divide(&$divide) {

        // Slide layout templates
        if(!empty($this->objs[1]->layout)) switch($this->objs[1]->layout) {
            case '2columns':
                echo "</div>\n<div class=\"c2right\">\n";
                break;
            case '2columns-noborder':
                echo "</div>\n<div class=\"c2rightnb\">\n";
                break;
        }
    }

    function _movie(&$movie) {
        echo <<<EOB
<div align="{$movie->align}" style="margin-left: <?php echo {$movie->marginleft}; margin-right: {$movie->marginright};">
<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="{$movie->width}" height="{$movie->height}" codebase="http://www.apple.com/qtactivex/qtplugin.cab">
<param name="src" value="{$this->slideDir}{$movie->filename}" >
<param name="autoplay" value="{$movie->autoplay}">
<param name="controller" value="false">
<!--[if !IE]> -->
<object data="{$this->slideDir}{$movie->filename}" type="video/quicktime" width="{$movie->width}" height="{$movie->height}">
<param name="src" value="{$this->slideDir}{$movie->filename}" >
<param name="autoplay" value="{$movie->autoplay}">
<param name="controller" value="false">
</object>
<!-- <![endif]-->
</object>
</div>
EOB;
    }

    function _footer(&$footer) {
        global $pres;

        // Slide layout templates
        if(!empty($this->objs[1]->layout)) switch($this->objs[1]->layout) {
            default:
                echo "</div>\n";
                break;
        }
        // Navbar layout templates
        switch($pres->template) {
            case 'mysql':
                ?>
                <div class="bsticky">
                <img style="margin-bottom: -0.3em" src="images/bottomswoop.gif" width="100%" height="50" />
                <span class="c4">&copy; Copyright 2002 MySQL AB</span>
                </div>
                <?php
                break;
            case 'css':
                echo "</div>\n";
        }
        echo "</body>\n";
    }
}

class plainhtml extends html {

    function _presentation(&$presentation) {
        global $pres;

        echo <<<HEADER
<!doctype html>
<html>
<head>
<base href="http://$_SERVER[HTTP_HOST]$this->baseDir">
<meta charset="utf-8">
<title>{$presentation->title}</title>
</head>
<body>
HEADER;
        while(list($this->coid,$obj) = each($this->objs)) {
            $obj->display();
        }
        echo <<<FOOTER
</body>
</html>
FOOTER;
}

    function _slide(&$slide) {
        global $pres;
        
        $currentPres = $_SESSION['currentPres'];
        
        $navsize = $slide->navsize;
        if ($pres->navsize) $navsize = $pres->navsize;
        
        $prev = $next = 0;
        if($this->slideNum < $this->maxSlideNum) {
            $next = $this->slideNum+1;
        }
        if($this->slideNum > 0) {
            $prev = $this->slideNum - 1;
        }
        switch($pres->template) {
            default:
            echo "<table border=0 width=\"100%\"><tr rowspan=2><td width=1>";
            if(!empty($slide->logo1)) $logo1 = $slide->logo1;
            else $logo1 = $pres->logo1;
            if(!empty($slide->logoimage1url)) $logo1url = $slide->logoimage1url;
            else $logo1url = $pres->logoimage1url;                
            if(!empty($logo1)) echo "<a href=\"$logo1url\"><img src=\"$logo1\" border=\"0\" align=\"left\"></a>\n";
            echo "</td>\n";
            if ($pres->navbartopiclinks) {
                echo "<td align=\"left\">";
                if($this->prevTitle) echo "<a href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$prev\" style=\"text-decoration: none;\"><font size=+2>Previous: ".markup_text($this->prevTitle)."</font></a></td>\n";
                if($this->nextTitle) echo "<td align=\"right\"><a href=\"http://$_SERVER[HTTP_HOST]$this->baseDir$this->showScript/$currentPres/$next\" style=\"text-decoration: none;\"><font size=+2>Next: ".markup_text($this->nextTitle)."</font></a></td>";
            }
            echo "<td rowspan=2 width=1>";
            if(!empty($slide->logo2)) $logo2 = $slide->logo2;
            else $logo2 = $pres->logo2;
            if (!empty($logo2)) {
                echo "<img src=\"$logo2\" align=\"right\">\n";
            }
            echo "</td>\n";
            echo "<tr><th colspan=3 align=\"center\"><font size=+4>".markup_text($slide->title)."</font></th></table>\n";

            break;
        }

        // Slide layout templates
        if(!empty($this->objs[1]->layout)) switch($this->objs[1]->layout) {
            case '2columns':
                echo "<table width=\"100%\"><tr><td valign=\"top\">\n";
                break;
            case '2columns-noborder':
                echo "<table width=\"100%\" border=\"0\"><tr><td valign=\"top\">\n";
                break;
            case 'box':
                echo "<table><tr><td>\n";
                break;
        }

        // Automatic slides
        if($this->objs[1]->template == 'titlepage') {
            $basefontsize = isset($this->objs[1]->fontsize) ? $this->objs[1]->fontsize:'5em';
            $smallerfontsize = (2*(float)$basefontsize/3).'em';
            $smallestfontsize = ((float)$basefontsize/2).'em';
            $p = $pres;
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

    function _blurb(&$blurb) {
        if($blurb->type=='speaker' && !$_SESSION['show_speaker_notes']) return;
        if(!empty($blurb->title)) {
            if($blurb->type=='speaker') $blurb->titlecolor='#ff3322';
            echo "<h1 align=\"$blurb->talign\"><font color=\"$blurb->titlecolor\">".markup_text($blurb->title)."</font></h1>\n";
        }
        if(!empty($blurb->text)) {
            if($blurb->type=='speaker') $blurb->textcolor='#ff3322';
            echo "<p align=\"$blurb->align\"><font color=\"$blurb->textcolor\">".markup_text($blurb->text)."</font></p>\n";
        }
    }

    function _image(&$image) {
        if(isset($image->title)) echo "<h1 align=\"{$image->talign}\">".markup_text($image->title)."</h1>\n";
        if ($image->width) {
            $size = "width=\"{$image->width}\" height=\"{$image->height}\"";
        } else {
            $size = getimagesize($this->slideDir.$image->filename);
            $size = $size[3];
        }
?>
<div align="<?php echo $image->align?>" style="margin-left: <?php echo $image->marginleft?>; margin-right: <?php echo $image->marginright?>;">
<img src="<?php echo $this->slideDir.$image->filename?>" <?php echo $size?>>
</div>
<?php
    }

    function _example(&$example) {
        global $pres;
        // Bring posted variables into the function-local namespace 
        // so examples will work
        foreach($_POST as $_html_key => $_html_val) {
            $$_html_key = $_html_val;
        }
        foreach($_SERVER as $_html_key => $_html_val) {
            $$_html_key = $_html_val;
        }

        if(isset($example->title)) echo '<h1>'.markup_text($example->title)."</h1>\n";
        if(!$example->hide) {
            if(!empty($pres->examplebackground)) $_html_examplebackground = $pres->examplebackground;
            if(!empty($this->objs[1]->examplebackground)) $_html_examplebackground = $this->objs[1]->examplebackground;
            if(!empty($example->examplebackground)) $_html_examplebackground = $example->examplebackground;

            echo "<table bgcolor=\"$_html_examplebackground\"><tr><td>\n";
            $example->highlight($this->slideDir);
            echo "</td></tr></table>\n";
        }
        if($example->result && (empty($example->condition) || isset(${$example->condition})) && (empty($example->required_extension) || extension_loaded($example->required_extension))) {
            if(!$example->hide) {
                echo "<h2>Output</h2>\n";
            }
            if(!empty($example->global) && !isset($GLOBALS[$example->global])) {
                global ${$example->global};
            }
            if(!empty($pres->outputbackground)) $_html_outputbackground = $pres->outputbackground;
            if(!empty($this->objs[1]->outputbackground)) $_html_outputbackground = $this->objs[1]->outputbackground;
            if(!empty($example->outputbackground)) $_html_outputbackground = $example->outputbackground;
            if(!empty($example->anchor)) echo "<a name=\"$example->anchor\"></a>\n";
            echo "<br /><table bgcolor=\"$_html_outputbackground\"><tr><td>\n";

            if(!empty($example->filename)) {
                $_html_filename = preg_replace('/\?.*$/','',$this->slideDir.$example->filename);
                switch($example->type) {
                    case 'genimage':
                        echo "<img src=\"$this->slideDir$example->filename\">\n";
                        break;
                    case 'iframe':
                    case 'link':
                    case 'embed':
                        echo "<a href=\"$this->slideDir$example->filename\">$example->linktext</a><br />\n";
                        break;
                    case 'flash':
                        echo "<embed src=\"$this->slideDir$example->filename?".time()." quality=high loop=true
pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\" 
type=\"application/x-shockwave-flash\" width=$example->iwidth height=$example->iheight>\n";
                        break;
                    case 'system':
                        system("DISPLAY=localhost:0 $this->slideDir$example->filename");
                        break;    
                    default:
                        include $_html_filename;
                        break;
                }
            } else {
                switch($example->type) {
                    default:
                        eval('?>'.$example->text);
                        break;
                }
            }
            echo "</td></tr></table>\n";
#                if(!empty($example->anchor)) echo "</a>\n";
        }
    }

    function _list(&$list) {
        if(isset($list->title)) echo "<h1>".markup_text($list->title)."</h1>\n";
        echo '<ul>';
        while(list($k,$bul)=each($list->bullets)) $bul->display();
        echo '</ul>';
    }

    function _bullet(&$bullet) {
        if ($bullet->text == "") $bullet->text = "&nbsp;";
        echo "<li>".markup_text($bullet->text)."</li>\n";
    }

    function _table(&$table) {
        if(!empty($table->align)) $align = 'align="'.$table->align.'"'; else $align = '';
        if(isset($table->title)) echo "<h1 $align>".markup_text($table->title)."</h1>\n";
        echo '<table '.$align.' width="100%" border=1>';
        $i = 0;
        while(list($k,$cell)=each($table->cells)) {
            if(!($i % $table->columns)) {
                echo "<tr>\n";
            }
            echo " <td>";
            $cell->display();
            echo " </td>";
            if(!(($i+1) % $table->columns)) {
                echo "</tr>\n";
            }
            $i++;
        }
        echo '</table><br />';
    }

    function _cell(&$cell) {
        echo markup_text($cell->text)."\n";
    }

    function _link(&$link) {
        if(empty($link->text)) $link->text = $link->href;
        if(!empty($link->leader)) $leader = $link->leader;
        else $leader='';
        if (empty($link->target)) $link->target = '_self';
        if(!empty($link->text)) {
            echo "$leader<a href=\"$link->href\" target=\"{$link->target}\">".markup_text($link->text)."</a><br />\n";
        }
    }

    function _divide(&$divide) {

        // Slide layout templates
        if(!empty($this->objs[1]->layout)) switch($this->objs[1]->layout) {
            case '2columns':
                echo "</td>\n<td valign=\"top\">\n";
                break;
            case '2columns-noborder':
                echo "</td>\n<td valign=\"top\">\n";
                break;
        }
    }

    function _footer(&$footer) {

        // Slide layout templates
        if(!empty($this->objs[1]->layout)) switch($this->objs[1]->layout) {
            default:
                echo "</td></tr></table>\n";
                break;
        }
    }
}

class flash extends html {

    function _slide(&$slide) {
        global $pres;

        list($dx,$dy) = getFlashDimensions($slide->titleFont,$slide->title,flash_fixsize($slide->titleSize));
        $dx = $this->winW;  // full width
?>
<div align="<?php echo $slide->titleAlign?>" class="sticky" id="stickyBar">
<embed src="<?php echo $this->baseDir?>flash.php/<?php echo time()?>?type=title&dy=<?php echo $dy?>&dx=<?php echo $dx?>&coid=<?php echo $this->coid?>" quality=high loop=false 
pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"
type="application/x-shockwave-flash" width="<?php echo $dx?>" height="<?php echo $dy?>">
</embed>
</div>
<?php
        // Slide layout templates
        if(!empty($this->objs[1]->layout)) switch($this->objs[1]->layout) {
            case '2columns':
                echo "<div class=\"c2left\">\n";
                break;
            case '2columns-noborder':
                echo "<div class=\"c2leftnb\">\n";
                break;
            case 'box':
                echo "<div class=\"box\">\n";
                break;
        }

        // Automatic slides
        if($this->objs[1]->template == 'titlepage') {
            $basefontsize = isset($this->objs[1]->fontsize) ? $this->objs[1]->fontsize:'5em';
            $smallerfontsize = (2*(float)$basefontsize/3).'em';
            $smallestfontsize = ((float)$basefontsize/2).'em';
            $p = $pres;
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
            if(!empty($p->email) && !empty($p->speaker))
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
}

class pdf extends display {

    function __construct($c) {
        parent::__construct($c);
    }

    // {{{ my_new_pdf_page($pdf, $x, $y, $new_page)
    function my_new_pdf_page(&$pdf, $x, $y, $new_page=true) {
        if ($new_page) $this->page_number++;
        pdf_begin_page($pdf, $x, $y);
        // Having the origin in the bottom left corner confuses the
        // heck out of me, so let's move it to the top-left.
        pdf_translate($pdf,0,$y);
        pdf_scale($pdf, 1, -1);   // Reflect across horizontal axis
        pdf_set_value($pdf,"horizscaling",-100); // Mirror

    }
    // }}}

    function my_new_pdf_end_page(&$pdf) {
        pdf_end_page($pdf);
    }
    
    // {{{ my_pdf_page_number($pdf)
    function my_pdf_page_number(&$pdf, $pdf_x, $pdf_y) {    
        if(isset($this->page_index[$this->page_number]) && $this->page_index[$this->page_number] == 'titlepage') return;
        pdf_set_font($pdf, $this->pdf_font, -10, 'winansi');
        $fnt = pdf_findfont($pdf, $this->pdf_font, 'winansi', 0); 
        $dx = pdf_stringwidth($pdf,"- $this->page_number -",$fnt,-10);
        $x = (int)($pdf_x/2 - $dx/2);
        $pdf_cy = pdf_get_value($pdf, "texty", null);
        pdf_show_xy($pdf, "- $this->page_number -", $x, $pdf_y-20);
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
    function my_pdf_paginated_code(&$pdf, $data, $x, $y, $tm, $bm, $lm, $rm, $font, $fs) {
        $data = strip_markups($data);    
        pdf_set_font($pdf, $font, $fs, 'winansi');    
        $fnt = pdf_findfont($pdf, $font, 'winansi', 0);
        $cw = pdf_stringwidth($pdf,'m',$fnt,$fs); // Width of 1 char - assuming monospace
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
                $out = array($ln);    
            }
            foreach($out as $l) {
                $l = str_replace("\t",'    ',$l);  // 4-space tabs - should probably be an attribute
                if($np) { pdf_show($pdf, $l); $np = false; }
                else pdf_continue_text($pdf, $l);
            }
            $pos += $nl+1;
            if(pdf_get_value($pdf, "texty", null) >= ($y-$bm)) {
                $this->my_pdf_page_number($pdf, $x, $y);
                $this->my_new_pdf_end_page($pdf);
                $this->my_new_pdf_page($pdf, $x, $y, true);
    
                pdf_set_font($pdf, $font, $fs, 'winansi');    
                pdf_set_text_pos($pdf, $lm, 60);
                $np = true;
            }
            
        }
    }
    // }}}

    function _presentation(&$presentation) {
        global $pres;
                
        $_SESSION['selected_display_mode'] = get_class($this);
        // In PDF mode we loop through all the slides and make a single
        // big multi-page PDF document.
        $this->page_number = 0;
        $this->pdf = pdf_new();
        if(!empty($pdfResourceFile)) pdf_set_parameter($this->pdf, "resourcefile", $pdfResourceFile);
        pdf_open_file($this->pdf,null);
        pdf_set_info($this->pdf, "Author",isset($presentation->speaker)?$presentation->speaker:"Anonymous");
        pdf_set_info($this->pdf, "Title",isset($presentation->title)?$presentation->title:"No Title");
        pdf_set_info($this->pdf, "Creator", "See Author");
        pdf_set_info($this->pdf, "Subject", isset($presentation->topic)?$presentation->topic:"");

        while(list($this->slideNum,$slide) = each($presentation->slides)) {
            // testing hack
            $slideDir = dirname($this->presentationDir.'/'.$presentation->slides[$this->slideNum]->filename).'/';
            $fn = $this->presentationDir.'/'.$presentation->slides[$this->slideNum]->filename;
            $fh = fopen($fn, "rb");
            $r = new XML_Slide($fh);
            $r->setErrorHandling(PEAR_ERROR_DIE,"%s ($fn)\n");
            $r->parse();

            $this->objs = $r->getObjects();
            $this->my_new_pdf_page($this->pdf, $this->pdf_x, $this->pdf_y, true);
            $this->pdf_cx = $this->pdf_cy = 0;  // Globals that keep our current x,y position
            while(list($this->coid,$obj) = each($this->objs)) {
                    $obj->display();
            }
            $this->my_new_pdf_end_page($this->pdf);
        }

        $this->my_new_pdf_page($this->pdf, $this->pdf_x, $this->pdf_y, true);
        pdf_set_font($this->pdf, $this->pdf_font , -20, 'winansi');
        $fnt = pdf_findfont($this->pdf, $this->pdf_font, 'winansi', 0);
        $dx = pdf_stringwidth($this->pdf, "Index",$fnt,-20);
        $x = (int)($this->pdf_x/2 - $dx/2);
        pdf_set_parameter($this->pdf, "underline", 'true');
        pdf_show_xy($this->pdf,"Index",$x,60);
        pdf_set_parameter($this->pdf, "underline", 'false');
        $this->pdf_cy = pdf_get_value($this->pdf, "texty", null)+30;
        $old_cy = $this->pdf_cy;
        pdf_set_font($this->pdf, $this->pdf_font , -12, 'winansi');

        if(is_array($this->page_index)) foreach($this->page_index as $pn=>$ti) {
            if($ti=='titlepage') continue;
            $ti.='    ';
            while(pdf_stringwidth($this->pdf,$ti,$fnt,-12)<($this->pdf_x-$this->pdf_cx*2.5-140)) $ti.='.';
            pdf_show_xy($this->pdf, $ti, $this->pdf_cx*2.5, $this->pdf_cy);
            $dx = pdf_stringwidth($this->pdf, $pn,$fnt,-12);
            pdf_show_xy($this->pdf, $pn, $this->pdf_x-2.5*$this->pdf_cx-$dx, $this->pdf_cy);
            $this->pdf_cy+=15;
            if($this->pdf_cy > ($this->pdf_y-50)) {
                $this->my_new_pdf_end_page($this->pdf);
                $this->my_new_pdf_page($this->pdf, $this->pdf_x, $this->pdf_y, false);
                $this->pdf_cy = $old_cy;
                pdf_set_font($this->pdf, $this->pdf_font , -12, 'winansi');
            }
        }
        $this->my_new_pdf_end_page($this->pdf);
        pdf_close($this->pdf);
        $data = pdf_get_buffer($this->pdf);
        header('Content-type: application/pdf');
        header('Content-disposition: inline; filename='.$_SESSION['currentPres'].'.pdf');
        header("Content-length: " . strlen($data));
        echo $data;
    }

    function _slide(&$slide) {
        global $pres;
        $currentPres = $_SESSION['currentPres'];

        $p = $this->objs[1];
        $middle = (int)($this->pdf_y/2) - 40;

        $this->pdf_cy = 25;  // top-margin
        $this->pdf_cx = 40;
        if($this->objs[1]->template == 'titlepage') {
            $p = $pres;
            $loc = $middle - 80 * ( !empty($p->title) + !empty($p->event) +
                                    !empty($p->date) + 
                                    (!empty($p->speaker)||!empty($p->email)) +
                                    !empty($p->url) + !empty($p->subtitle) )/2;
            if(!empty($p->title)) {
                pdf_set_font($this->pdf, $this->pdf_font, -36, 'winansi');
                pdf_show_boxed($this->pdf, $p->title, 10, $loc, $this->pdf_x-20, 40, 'center',null);
            }

            if(!empty($p->subtitle)) {
                $loc += 50;
                pdf_set_font($this->pdf, $this->pdf_font , -22, 'winansi');
                pdf_show_boxed($this->pdf, $p->subtitle, 10, $loc, $this->pdf_x-20, 40, 'center',null);
            }
            
            if(!empty($p->event)) {
                $loc += 80;
                pdf_set_font($this->pdf, $this->pdf_font , -30, 'winansi');
                pdf_show_boxed($this->pdf, $p->event, 10, $loc, $this->pdf_x-20, 40, 'center',null);
            }

            if(!empty($p->date) && !empty($p->location)) {
                $loc += 80;
                pdf_set_font($this->pdf, $this->pdf_font , -30, 'winansi');
                pdf_show_boxed($this->pdf, $p->date.'. '.$p->location, 10, $loc, $this->pdf_x-20, 40, 'center',null);
            } else if(!empty($p->date)) {
                $loc += 80;
                pdf_set_font($this->pdf, $this->pdf_font , -30, 'winansi');
                pdf_show_boxed($this->pdf, $p->date, 10, $loc, $this->pdf_x-20, 40, 'center',null);
            } else if(!empty($p->location)) {
                $loc += 80;
                pdf_set_font($this->pdf, $this->pdf_font , -30, 'winansi');
                pdf_show_boxed($this->pdf, $p->location, 10, $loc, $this->pdf_x-20, 40, 'center',null);

            }
            if(!empty($p->speaker) && !empty($p->email)) {
                $loc += 80;
                pdf_set_font($this->pdf, $this->pdf_font , -30, 'winansi');
                pdf_show_boxed($this->pdf, $p->speaker.' <'.$p->email.'>', 10, $loc, $this->pdf_x-20, 40, 'center',null);
            } else if(!empty($p->speaker)) {
                $loc += 80;
                pdf_set_font($this->pdf, $this->pdf_font, -30, 'winansi');
                pdf_show_boxed($this->pdf, $p->speaker, 10, $loc, $this->pdf_x-20, 40, 'center',null);
            } else if(!empty($p->email)) {
                $loc += 80;
                pdf_set_font($this->pdf, $this->pdf_font , -30, 'winansi');
                pdf_show_boxed($this->pdf, ' <'.$p->email.'>', 10, $loc, $this->pdf_x-20, 40, 'center',null);
            }
            if(!empty($p->url)) {
                $loc += 80;
                pdf_set_font($this->pdf, $this->pdf_font , -30, 'winansi');
                pdf_show_boxed($this->pdf, $p->url, 10, $loc, $this->pdf_x-20, 40, 'center',null);
            }
            if(!empty($p->copyright)) {
                pdf_moveto($this->pdf, 60, $this->pdf_y-60);
                pdf_lineto($this->pdf, $this->pdf_x-60, $this->pdf_y-60);
                pdf_stroke($this->pdf);
                pdf_set_font($this->pdf, $this->pdf_font , -10, 'winansi');
                $fnt = pdf_findfont($this->pdf, $this->pdf_font, 'winansi', 0);
                $x = (int)($this->pdf_x/2 - pdf_stringwidth($this->pdf, $p->copyright, $fnt, -10)/2);
                $str = str_replace('(c)',chr(0xa9), $p->copyright);
                $str = str_replace('(R)',chr(0xae), $str);
                pdf_show_xy($this->pdf, $str, $x, $this->pdf_y-45);
            }
            $this->page_index[$this->page_number] = 'titlepage';
        } else { // No header on the title page
            pdf_set_font($this->pdf, $this->pdf_font , -12, 'winansi');
            $fnt = pdf_findfont($this->pdf, $this->pdf_font, 'winansi', 0);
            pdf_show_boxed($this->pdf, "Slide $this->slideNum/$this->maxSlideNum", $this->pdf_cx, $this->pdf_cy, $this->pdf_x-2*$this->pdf_cx, 1, 'left',null);
            if(isset($p->date)) $this->d = $this->date;
            else $this->d = strftime("%B %e %Y");
            $w = pdf_stringwidth($this->pdf, $this->d, $fnt, -12);
            pdf_show_boxed($this->pdf, $this->d, 40, $this->pdf_cy, $this->pdf_x-2*$this->pdf_cx, 1, 'right',null);
            pdf_set_font($this->pdf, $this->pdf_font , -24, 'winansi');
            pdf_show_boxed($this->pdf, strip_markups($slide->title), 40, $this->pdf_cy, $this->pdf_x-2*$this->pdf_cx, 1, 'center',null);

            $this->page_index[$this->page_number] = strip_markups($slide->title);
        }

        $this->pdf_cy += 30;    
        if($this->slideNum) { 
            pdf_moveto($this->pdf,40,$this->pdf_cy); 
            pdf_lineto($this->pdf,$this->pdf_x-40,$this->pdf_cy);    
            pdf_stroke($this->pdf);
        }
        $this->pdf_cy += 20;    
        pdf_set_text_pos($this->pdf, $this->pdf_cx, $this->pdf_cy);
    }

    function _blurb(&$blurb) {
        if($blurb->type=='speaker' && !$_SESSION['show_speaker_notes']) return;
        if(!empty($blurb->title)) {
            if($blurb->type=='speaker') {
                pdf_setcolor($this->pdf,'fill','rgb',1,0,0,null);
            }
            pdf_set_font($this->pdf, $this->pdf_font , -16, 'winansi');
            $fnt = pdf_findfont($this->$pdf, $this->pdf_font, 'winansi', 0);
            $dx = pdf_stringwidth($this->pdf,$blurb->title, $fnt, -16);
            $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
            switch($blurb->talign) {
                case 'center':
                    $x = (int)($this->pdf_x/2 - $dx/2);
                    break;
                case 'right':
                    $x = $this->pdf_x - $dx - $this->pdf_cx;
                    break;
                default:
                case 'left':
                    $x = $this->pdf_cx;
                    break;
            }
            pdf_set_text_pos($this->pdf,$x,$this->pdf_cy);
            pdf_continue_text($this->pdf, strip_markups($blurb->title));
            $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
            pdf_set_text_pos($this->pdf,$x,$this->pdf_cy+5);
            pdf_setcolor($this->pdf,'fill','rgb',0,0,0,null);
        }
        $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);

        switch(strtolower($blurb->align)) {
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

        pdf_save($this->pdf);
        pdf_translate($this->pdf,0,$this->pdf_y);
        pdf_scale($this->pdf,1, -1);
        pdf_set_font($this->pdf, $this->pdf_font , 12, 'winansi');
        $leading = pdf_get_value($this->pdf, "leading", null);
        $height = $inc = 12+$leading;    
        $txt = strip_markups($blurb->text);

        while(pdf_show_boxed($this->pdf, $txt, $this->pdf_cx+20, $this->pdf_y-$this->pdf_cy, $this->pdf_x-2*($this->pdf_cx-20), $height, $align, 'blind')!=0) $height+=$inc;

        pdf_restore($this->pdf);

        if( ($this->pdf_cy + $height) > $this->pdf_y-40 ) {
            $this->my_pdf_page_number($this->pdf, $this->pdf_x, $this->pdf_y);
            $this->my_new_pdf_end_page($this->pdf);
            $this->my_new_pdf_page($this->pdf, $this->pdf_x, $this->pdf_y, true);
            $this->pdf_cx = 40;
            $this->pdf_cy = 60;
        }

        pdf_set_font($this->pdf, $this->pdf_font , -12, 'winansi');
        if($blurb->type=='speaker') {
            pdf_setcolor($this->pdf,'fill','rgb',1,0,0,null);
        }
        pdf_show_boxed($this->pdf, str_replace("\n",' ',$txt), $this->pdf_cx+20, $this->pdf_cy-$height, $this->pdf_x-2*($this->pdf_cx+20), $height, $align,null);
        pdf_continue_text($this->pdf, "\n");
        pdf_setcolor($this->pdf,'fill','rgb',0,0,0,null);
    }

    function _image(&$image) {
        if(isset($image->title)) {
            $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
            pdf_set_text_pos($this->pdf,$this->pdf_cx,$this->pdf_cy);
            pdf_set_font($this->pdf, $this->pdf_font , -16, 'winansi');
            pdf_continue_text($this->pdf, $image->title);
            pdf_continue_text($this->pdf, "\n");
        }
        $this->pdf_cy = pdf_get_value($this->pdf, "texty", null)-5;
        pdf_set_font($this->pdf, $this->pdf_font , -12, 'winansi');
        $fnt = pdf_findfont($this->pdf, $this->pdf_font, 'winansi', 0);
        $cw = pdf_stringwidth($this->pdf,'m', $fnt, -12);  // em unit width
        if ($image->width) {
            $dx = $image->height;
            $dy = $image->width;
            list(,,$type) = getimagesize($this->slideDir.$image->filename);
        } else {
            list($dx,$dy,$type) = getimagesize($this->slideDir.$image->filename);
        }
        $dx = $this->pdf_x*$dx/1024;
        $dy = $this->pdf_x*$dy/1024;

        switch($type) {
            case 1:
                if(!strstr($image->filename,'blank')) 
                    $im = pdf_open_gif($this->pdf, $this->slideDir.$image->filename);
                break;
            case 2:
                $im = pdf_open_jpeg($this->pdf, $this->slideDir.$image->filename);
                break;
            case 3:
                $im = pdf_open_png($this->pdf, $this->slideDir.$image->filename);
                break;
            case 7:
                $im = pdf_open_tiff($this->pdf, $this->slideDir.$image->filename);
                break;
        }
        if(isset($im)) {
            $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
            if(($this->pdf_cy + $dy) > ($this->pdf_y-60)) {
                $this->my_pdf_page_number($this->pdf, $this->pdf_x, $this->pdf_y);
                $this->my_new_pdf_end_page($this->pdf);
                $this->my_new_pdf_page($this->pdf, $this->pdf_x, $this->pdf_y, true);
                $this->pdf_cx = 40;
                $this->pdf_cy = 60;
            }
            switch($image->align) {
                case 'right':
                    $x = $this->pdf_x - $dx - $this->pdf_cx;
                    break;
                case 'center':
                    $x = (int)($this->pdf_x/2 - $dx/2);
                    break;
                case 'left':
                default:
                    $x = $this->pdf_cx;
                    break;
            }
            if(isset($image->marginleft)) {
                $x+= ((int)$image->marginleft) * $cw;
            }
            if(isset($image->marginright)) {
                $x-= ((int)$image->marginright) * $cw;
            }
            pdf_save($this->pdf);
            pdf_translate($this->pdf,0,$this->pdf_y);

            $scale = $this->pdf_x/1024;
            pdf_scale($this->pdf,1,-1);
            pdf_place_image($this->pdf, $im, $x, ($this->pdf_y-$this->pdf_cy-$dy), $scale);
            pdf_restore($this->pdf);
            pdf_set_text_pos($this->pdf,$this->pdf_cx,$this->pdf_cy+$dy);
        }        
    }

    function _example(&$example) {
        global $pres;

        // Bring posted variables into the function-local namespace 
        // so examples will work
        foreach($_POST as $_html_key => $_html_val) {
            $$_html_key = $_html_val;
        }
        foreach($_SERVER as $_html_key => $_html_val) {
            $$_html_key = $_html_val;
        }

        if(!empty($example->title)) {
            $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
            pdf_set_text_pos($this->pdf,$this->pdf_cx,$this->pdf_cy);  // Force to left-margin
            pdf_set_font($this->pdf, $this->pdf_font , -16, 'winansi');
            pdf_continue_text($this->pdf, strip_markups($example->title));
            pdf_continue_text($this->pdf, "");
        }
        $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);

        if(!$example->hide) {
            if(!empty($example->filename)) {
                $_html_filename = preg_replace('/\?.*$/','',$this->slideDir.$example->filename);
                $_html_file = @file_get_contents($_html_filename);
            } else {
                $_html_file = $example->text;
            }
            switch($example->type) {
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
                case 'sql':
                case 'html':
                default:
                    if($_html_file[strlen($_html_file)-1] != "\n") $_html_file .= "\n";
                    $this->my_pdf_paginated_code($this->pdf, $_html_file, $this->pdf_x, $this->pdf_y, $this->pdf_cy+10, 60, $this->pdf_cx+30, $this->pdf_cx, $this->pdf_example_font, -10);
                    pdf_continue_text($this->pdf,"");
                    break;
            }
            
        }            
        $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
        if($example->result && $example->type != 'iframe' && (empty($example->condition) || isset(${$example->condition})) && (empty($example->required_extension) || extension_loaded($example->required_extension))) {
            if(!$example->hide) {
                $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
                pdf_set_text_pos($this->pdf,$this->pdf_cx+20,$this->pdf_cy);  // Force to left-margin
                pdf_set_font($this->pdf, $this->pdf_font , -14, 'winansi');
                pdf_continue_text($this->pdf, "Output:");
                pdf_continue_text($this->pdf, "");
            }
            $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);

            if(!empty($example->global) && !isset($GLOBALS[$example->global])) {
                global ${$example->global};
            }
            if(!empty($example->filename)) {
                $_html_filename = preg_replace('/\?.*$/','',$this->slideDir.$example->filename);
                switch($example->type) {
                    case 'genimage':
                        $fn = tempnam("/tmp","pres2");
                        $img = file_get_contents("http://".$_SERVER['HTTP_HOST']."/".$this->baseDir.$this->slideDir.$example->filename);
                        $fp_out = fopen($fn,"wb");
                        fwrite($fp_out,$img);
                        fclose($fp_out);
                        list($dx,$dy,$type) = getimagesize($fn);
                        $dx = $this->pdf_x*$dx/1024;
                        $dy = $this->pdf_x*$dy/1024;

                        switch($type) {
                            case 1:
                                $im = pdf_open_gif($this->pdf, $fn);
                                break;
                            case 2:
                                $im = pdf_open_jpeg($this->pdf, $fn);
                                break;
                            case 3:
                                $im = pdf_open_png($this->pdf, $fn);
                                break;
                            case 7:
                                $im = pdf_open_tiff($this->pdf, $fn);
                                break;
                        }
                        if(isset($im)) {
                            $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
                            if(($this->pdf_cy + $dy) > ($this->pdf_y-60)) {
                                $this->my_pdf_page_number($this->pdf, $this->pdf_x, $this->pdf_y);
                                $this->my_new_pdf_end_page($this->pdf);
                                $this->my_new_pdf_page($this->pdf, $this->pdf_x, $this->pdf_y, true);
                                $this->pdf_cx = 40;
                                $this->pdf_cy = 60;
                            }
                            pdf_save($this->pdf);
                            pdf_translate($this->pdf,0,$this->pdf_y);

                            $scale = $this->pdf_x/1024;
                            pdf_scale($this->pdf,1 * $scale, -1 * $scale);
                            pdf_place_image($this->pdf, $im, $this->pdf_cx, ($this->pdf_y-$this->pdf_cy-$dy), $scale);
                            pdf_restore($this->pdf);
                            pdf_set_text_pos($this->pdf,$this->pdf_cx,$this->pdf_cy+$dy);
                        }
                        unlink($fn);
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
                        // system("DISPLAY=localhost:0 $this->slideDir$example->filename");
                        break;    
                    default:
                        // Need something to turn html into pdf here?
                        // Perhaps just output buffering and stripslashes
                        // include $_html_filename;
                        // -- copying code from below as a temp solution --
                        ob_start();
                        eval('?>'.file_get_contents($_html_filename));
                        $data = strip_tags(ob_get_contents());
                        ob_end_clean();
                        if(strlen($data) && $data[strlen($data)-1] != "\n") $data .= "\n";
                        $this->my_pdf_paginated_code($this->pdf, $data, $this->pdf_x, $this->pdf_y, $this->pdf_cy, 60, $this->pdf_cx+30, $this->pdf_cx, $this->pdf_example_font, -10);
                        pdf_continue_text($this->pdf,"");
                        break;
                }
            } else {
                switch($example->type) {
                    default:
                        ob_start();
                        eval('?>'.$example->text);
                        $data = strip_tags(ob_get_contents());
                        ob_end_clean();
                        if(strlen($data) && $data[strlen($data)-1] != "\n") $data .= "\n";
                        $this->my_pdf_paginated_code($this->pdf, $data, $this->pdf_x, $this->pdf_y, $this->pdf_cy, 60, $this->pdf_cx+30, $this->pdf_cx, $this->pdf_example_font, -10);
                        pdf_continue_text($this->pdf,"");
                        break;
                }
            }
        }
    }

    function _break(&$break) { /* empty */ }
    
    function _list(&$list) {
        if (!isset($list->bullets)) return;
        if(isset($list->title)) {
            $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
            pdf_set_text_pos($this->pdf,$this->pdf_cx,$this->pdf_cy);
            pdf_set_font($this->pdf, $this->pdf_font, -16, 'winansi');
            pdf_continue_text($this->pdf, strip_markups($list->title));
            pdf_continue_text($this->pdf, "");
        }
        if(!empty($list->start)) {
            if(is_numeric($list->start)) {
                $list->num = (int)$list->start;    
            } else {
                $list->alpha = $list->start;
            }
        }
        while(list($k,$bul)=each($list->bullets)) $bul->display();
        
    }

    function _bullet(&$bullet) {
        $type = '';
        $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
    
        pdf_set_font($this->pdf, $this->pdf_font, -12, 'winansi');
        $height=10;    
        $txt = strip_markups($bullet->text);

        pdf_save($this->pdf);
        pdf_translate($this->pdf,0,$this->pdf_y);
        pdf_scale($this->pdf,1, -1);
        pdf_set_font($this->pdf, $this->pdf_font , 12, 'winansi');
        $leading = pdf_get_value($this->pdf, "leading", null);
        $inc = $leading;    
        while(pdf_show_boxed($this->pdf, $txt, $this->pdf_cx+30, $this->pdf_y-$this->pdf_cy, $this->pdf_x-2*($this->pdf_cx+20), $height, 'left', 'blind')) $height+=$inc;

        pdf_restore($this->pdf);

        //clean up eols so we get a nice pdf output
        if (strstr($txt,"\r\n")) {
            $eol = "\r\n";
        } elseif (strstr($txt,"\r")) {
            $eol = "\r";
        } else {
            $eol = "\n";
        }
        $txt = str_replace($eol," ", $txt);
        $txt = str_replace("  "," ",$txt);

        if( ($this->pdf_cy + $height) > $this->pdf_y-40 ) {
            $this->my_pdf_page_number($this->pdf, $this->pdf_x, $this->pdf_y);
            $this->my_new_pdf_end_page($this->pdf);
            $this->my_new_pdf_page($this->pdf, $this->pdf_x, $this->pdf_y, true);
            $this->pdf_cx = 40;
            $this->pdf_cy = 60;
        }

        pdf_set_font($this->pdf, $this->pdf_font , -12, 'winansi');
        if($bullet->type=='speaker') {
            pdf_setcolor($this->pdf,'fill','rgb',1,0,0,null);
        }

        if(!empty($bullet->start)) {
            if(is_numeric($bullet->start)) {
                $this->objs[$this->coid]->num = (int)$bullet->start;
            } else {
                $this->objs[$this->coid]->alpha = $bullet->start;
            }
        }

        if(!empty($bullet->type)) $type = $bullet->type;
        else if(!empty($this->objs[$this->coid]->type)) $type = $this->objs[$this->coid]->type;

        switch($type) {
            case 'numbered':
            case 'number':
            case 'decimal':
                $symbol = ++$this->objs[$this->coid]->num . '.';
                $pdf_cx_height = 30;
                break;
            case 'no-bullet':
                case 'none':
                $symbol='';
                $pdf_cx_height = 20;
                break;
            case 'alpha':
                $symbol = $this->objs[$this->coid]->alpha++ . '.';
                break;
            case 'ALPHA':
                $symbol = strtoupper($this->objs[$this->coid]->alpha++) . '.';
                $pdf_cx_height = 30;
                break;
            case 'asterisk':
                $symbol = '*';
                $pdf_cx_height = 20;
                break;
            case 'hyphen':
                $symbol = '-';
                $pdf_cx_height = 20;
                break;
            default:
                $symbol = 'o';
                $pdf_cx_height = 20;
                break;
        }

        pdf_show_xy($this->pdf, $symbol, $this->pdf_cx+20 + $bullet->level*10, $this->pdf_cy+$leading-1);
        pdf_show_boxed($this->pdf, $txt, $this->pdf_cx+40 + $bullet->level*10, $this->pdf_cy-$height, $this->pdf_x-2*($this->pdf_cx+20), $height, 'left',null);
        pdf_continue_text($this->pdf,"\n");
        $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
        pdf_set_text_pos($this->pdf, $this->pdf_cx, $this->pdf_cy-$leading/2);
        pdf_setcolor($this->pdf,'fill','rgb',0,0,0,null);
    }

    function _table(&$table) {
        if(isset($table->title)) {
            $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
            pdf_set_text_pos($this->pdf,$this->pdf_cx,$this->pdf_cy);
            pdf_set_font($this->pdf, $this->pdf_font, -16, 'winansi');
            pdf_continue_text($this->pdf, strip_markups($table->title));
            pdf_continue_text($this->pdf, "");
        }
        $width="100%";
        if(!empty($table->width)) {
            $width = $table->width;
        }
        $width = (int)$width;
        $max_w = $this->pdf_x - 2*$this->pdf_cx;
        $max_w = $max_w * $width/100;
        $cell_offset = $max_w/$table->columns;

        $i = 1;
        while(list($k,$cell)=each($table->cells)) {
            if(!($i % $table->columns)) {
                $cell->end_row = false;
            } 
            if(($i % $table->columns)==0) {
                $cell->end_row = true;
                $cell->offset = $cell_offset;
            }

            $cell->display();

            $i++;

        }
        pdf_continue_text($this->pdf, "");
    }

    function _cell(&$cell) {
        static $row_text = array();

        $row_text[] = $cell->text;
        if(!$cell->end_row) return;
        
        $this->pdf_cy = pdf_get_value($this->pdf, "texty", null);
    
        pdf_set_font($this->pdf, $this->pdf_font, -12, 'winansi');
        $height=10;    
        $txt = strip_markups($row_text[0]);
        while(pdf_show_boxed($this->pdf, $txt, 60, $this->pdf_cy, $this->pdf_x-120, $height, 'left', 'blind')) $height+=10;
        if( ($this->pdf_cy + $height) > $this->pdf_y-40 ) {
            $this->my_pdf_page_number($this->pdf, $this->pdf_x, $this->pdf_y);
            $this->my_new_pdf_end_page($this->pdf);
            $this->my_new_pdf_page($this->pdf, $this->pdf_x, $this->pdf_y, true);
            $this->pdf_cx = 40;
            $this->pdf_cy = 60;
        }
        pdf_set_font($this->pdf, $this->pdf_font, -12, 'winansi');
        if(!empty($cell->bold) && $cell->bold) pdf_set_font($this->pdf, $this->pdf_font_bold, -12, 'winansi');
        else if(!empty($obj->bold) && $obj->bold) pdf_set_font($this->pdf, $this->pdf_font_bold, -12, 'winansi');
        $off = 0;
        foreach($row_text as $t) {
            pdf_show_boxed($this->pdf, strip_markups($t), 60+$off, $this->pdf_cy-$height, 60+$off+$cell->offset, $height, 'left',null);
            $off += $cell->offset;
        }
        $this->pdf_cy+=$height;
        pdf_set_text_pos($this->pdf, $this->pdf_cx, $this->pdf_cy);
        pdf_continue_text($this->pdf,"");    
        $row_text = array();
    }

    function _link(&$link) {
		if(empty($link->text)) $link->text = preg_replace_callback('/:-:(.*?):-:/', function($matches) { return $this->pres->{$matches[1]}; }, $link->href);
		if(empty($link->leader)) $leader = preg_replace_callback('/:-:(.*?):-:/', function($matches) { return $this->pres->{$matches[1]}; }, $link->leader);
        else $leader='';

        if(!empty($link->text)) {
            $this->pdf_cy = pdf_get_value($this->pdf, "texty", null)+10;
            pdf_set_font($this->pdf, $this->pdf_font, -12, 'winansi');
            $fnt = pdf_findfont($this->pdf, $this->pdf_font, 'winansi', 0);
            if(strlen($leader)) $lx = pdf_stringwidth($this->pdf, $leader, $fnt, -12);
            else $lx=0;
            $dx = pdf_stringwidth($this->pdf, $link->text, $fnt, -12);
            $cw = pdf_stringwidth($this->pdf,'m', $fnt, -12);  // em unit width
            switch($link->align) {
                case 'center':
                    $x = (int)($this->pdf_x/2-$dx/2-$lx/2);
                    break;

                case 'right':
                    $x = $this->pdf_x-$this->pdf_cx-$dx-$lx-15;
                    break;

                case 'left':
                default:
                    $x = $this->pdf_cx;    
                    break;
            }
            if($link->marginleft) $x += (int)(((float)$link->marginleft) * $cw);
            pdf_add_weblink($this->pdf, $x+$lx, $this->pdf_y-$this->pdf_cy-3, $x+$dx+$lx, ($this->pdf_y-$this->pdf_cy)+12, $link->text);
            pdf_show_xy($this->pdf, strip_markups($leader).strip_markups($link->text), $x, $this->pdf_cy);
            pdf_continue_text($this->pdf,"");
        }
    }

    function _div(&$div) {
    }

    function _div_end(&$div) {
    }

    function _divide(&$divide) { /* empty */ }

    function _movie(&$movie) { /* empty */ }

    function _footer(&$footer) {        
        if($this->objs[1]->template != 'titlepage') {
            $this->my_pdf_page_number($this->pdf, $this->pdf_x, $this->pdf_y);
        }
    }
}

class pdfus extends pdf {
    function __construct($c) {
        // US-Letter
        $this->pdf_x = 612;  $this->pdf_y = 792;
        parent::__construct($c);
    }
}    

class pdfusl extends pdf {
    function __construct($c) {
        // US-Legal
        $this->pdf_x = 612;  $this->pdf_y = 1008;
        parent::__construct($c);
    }
}    

class pdfa4 extends pdf {
    function __construct($c) {
        // A4
        $this->pdf_x = 595;  $this->pdf_y = 842;
        parent::__construct($c);
    }
}    

?>
