<?php
class display {
    function _php(&$php) {
        if(!empty($php->filename)) include $php->filename;
        else eval('?>'.$php->text);
    
    }
}

class html extends display {

    function _presentation(&$presentation) {
        global $objs, $presentationDir, $pres, $browser_is_IE,
               $prevSlideNum, $slideNum, $nextSlideNum, $baseDir;
                    
        // Determine if we should cache
        $cache_ok = 1;
        foreach($objs as $obj) {
            if(is_a($obj, '_example')) {
                $cache_ok = 0;
            }
        }
        reset($objs); // shouldn't be necessary, but is

        // allow caching
        if($cache_ok) header("Last-Modified: " . date("r", filemtime($presentationDir.'/'.$presentation->slides[$slideNum]->filename)));
        echo <<<HEADER
<html>
<head>
<base href="http://$_SERVER[HTTP_HOST]$baseDir">
<title>{$presentation->title}</title>
HEADER;
        switch($presentation->template) {
        case 'simple':
            $body_style = "margin-top: 1em;";
            break;
        case 'php2':
            $body_style = "margin-top: 5em;";
            break;
        default:
            $body_style = "margin-top: " . ($browser_is_IE ? "0px" : "8em") . ";";
            break;
        }
        include 'getwidth.php';
        include $presentation->stylesheet;
        /* the following includes scripts necessary for various animations */
        if($presentation->animate || $presentation->jskeyboard) include 'keyboard.js.php';
        // Link Navigation (and next slide pre-fetching)
        if($slideNum) echo '<link rel="prev" href="'.$presentationDir.'/'.$presentation->slides[$prevSlideNum]->filename."\" />\n";
        if($nextSlideNum) echo '<link rel="next" href="'.$presentationDir.'/'.$presentation->slides[$nextSlideNum]->filename."\" />\n";
        echo '</head>';
        echo "<body onResize=\"get_dims();\" style=\"".$body_style."\">\n";
        while(list($coid,$obj) = each($objs)) {
            $obj->display();
        }
			echo <<<FOOTER
</body>
</html>
FOOTER;
}
    

    function _slide(&$slide) {
        global 	$slideNum, $maxSlideNum, $winW, $winH, $prevTitle, 
                $nextTitle, $baseDir, $showScript,
                $pres, $objs;
        $currentPres = $_SESSION['currentPres'];
        
        $navsize = $slide->navSize;
        if ($pres[1]->navsize) $navsize = $pres[1]->navsize;

        $titlesize = $slide->titleSize;
        if (isset($pres[1]->titlesize)) $titlesize = $pres[1]->titlesize;

        $titlecolor = $slide->titleColor;
        if (isset($pres[1]->titlecolor)) $titlecolor = $pres[1]->titlecolor;
        
        $prev = $next = 0;
        if($slideNum < $maxSlideNum) {
            $next = $slideNum+1;
        }
        if($slideNum > 0) {
            $prev = $slideNum - 1;
        }
        $slidelistH = $winH - 30;
        $offset=0;
        switch($pres[1]->template) {

            case 'simple':
            $slide->titleColor = '#000000';
            echo "<div align=\"$slide->titleAlign\" style=\"font-size: $titlesize; margin: 0 ".$offset."em 0 0;\"><a href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$slideNum\" style=\"text-decoration: none; color: $titlecolor;\">".markup_text($slide->title)."</a></div>";
            break;

            case 'php2':
            echo "<div id=\"stickyBar\" class=\"sticky\" align=\"$slide->titleAlign\" style=\"width: 100%\"><div class=\"navbar\">";
            echo "<table style=\"float: left;\" width=\"60%\" border=\"0\" cellpadding=0 cellspacing=0><tr>\n";
            if(!empty($slide->logo1)) $logo1 = $slide->logo1;
            else $logo1 = $pres[1]->logo1;
            if(!empty($slide->logoimage1url)) $logo1url = $slide->logoimage1url;
            else $logo1url = $pres[1]->logoimage1url;				
            if(!empty($logo1)) {
                $size = getimagesize($logo1);
                echo "<td align=\"left\" $size[3]><a href=\"$logo1url\"><img src=\"$logo1\" border=\"0\" align=\"left\" style=\"float: left; margin-bottom: 0em; margin-left: 0em;\"></a></td>";
                $offset+=2;
            }
            ?>
            <td align="center">
            <?echo "<div align=\"center\" style=\"font-size: $titlesize; margin: 0 ".$offset."em 0 0;\"><a title=\"".$pres[1]->slides[$slideNum]->filename."\" href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$slideNum\" style=\"text-decoration: none; color: $titlecolor;\">".markup_text($slide->title)."</a></div>";?>
            </td>
            </tr></table>
            <br />
            <table style="float: right">
              <tr>
              <td class="c1"><b><?= $pres[1]->title ?></b></td>
              <td><img src="images/vline.gif" hspace="5" /></td>
              <td class="c1"><?= date('Y-m-d') ?></td>
              <td><img src="images/blank.gif" width="5" /></td>
              <td><? if( $slideNum > 0){
                         $prevSlide = $slideNum - 1;
                         echo "<a title=\"$prevTitle\" href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$prevSlide\">"
                         . '<img src="images/back.gif" border="0" hspace="2" /></a>';
                     } 
                     if($slideNum < $maxSlideNum) $nextSlideNum = $slideNum + 1;
              ?></td>
              <td bgcolor="999999"><img src="images/blank.gif" width="25" height="1" /><br />
              <span class="c2"><b><i>&nbsp;&nbsp;
              <a title="<?= $slideNum.' of '.$maxSlideNum?>" href="<?= "http://$_SERVER[HTTP_HOST]{$baseDir}slidelist.php" ?>" onClick="window.open('<?= "http://$_SERVER[HTTP_HOST]{$baseDir}slidelist.php" ?>','slidelist','toolbar=no,directories=no,location=no,status=no,menubar=no,resizable=no,scrollbars=yes,width=300,height=500,left=<?= $winW-300 ?>,top=0'); return false" class="linka"><?= $slideNum ?></a> &nbsp; &nbsp; </i></b></span></td>
                  <td><? if( !empty($nextSlideNum) )
                    echo "<a title=\"$nextTitle\" href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$nextSlideNum\">"
                        . '<img src="images/next.gif" border="0" hspace="2" /></a>';
                ?></td>
              <td><img src="images/blank.gif" height="10" width="15" /></td>
              </tr>
            </table>
            <br clear="left" />
            <hr style="margin-left: 0; margin-right: 0; border: 0; color: <?=$titlecolor?>; background-color: <?=$titlecolor?>; height: 2px">
            </div></div>
            <?	
            break;

            case 'mysql':
            echo "<div id=\"stickyBar\" class=\"sticky\" align=\"$slide->titleAlign\" style=\"width: 100%\"><div class=\"navbar\">";
            echo "<table style=\"float: left;\" width=\"60%\" border=\"0\"><tr>\n";
            if(!empty($slide->logo1)) $logo1 = $slide->logo1;
            else $logo1 = $pres[1]->logo1;
            if(!empty($slide->logoimage1url)) $logo1url = $slide->logoimage1url;
            else $logo1url = $pres[1]->logoimage1url;				
            if(!empty($logo1)) {
                $size = getimagesize($logo1);
                echo "<td align=\"left\" $size[3]><a href=\"$logo1url\"><img src=\"$logo1\" border=\"0\" align=\"left\" style=\"float: left; margin-bottom: 0.5em; margin-left: 1em;\" alt=\"".$pres[1]->slides[$slideNum]->filename."\"></a></td>";
                $offset+=2;
            }
            ?>
            <td align="center">
            <b style="color: CC6600; font-size: 1.5em; font-family: arial, helvetica, verdana"><?= markup_text($slide->title) ?></b>
            </td>
            </tr></table>
            <br />
            <table style="float: right">
              <tr>
              <td class="c1"><b><?= $pres[1]->title ?></b></td>
              <td><img src="images/vline.gif" hspace="5" /></td>
              <td class="c1"><?= date('Y-m-d') ?></td>
              <td><img src="images/blank.gif" width="5" /></td>
              <td><? if( $slideNum > 0){
                         $prevSlide = $slideNum - 1;
                         echo "<a href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$prevSlide\">"
                         . '<img src="images/back.gif" border="0" hspace="2" /></a>';
                     } 
                     if($slideNum < $maxSlideNum) $nextSlideNum = $slideNum + 1;
              ?></td>
              <td bgcolor="999999"><img src="images/blank.gif" width="25" height="1" /><br />
              <span class="c2"><b><i>&nbsp;&nbsp;
              <a href="<?= "http://$_SERVER[HTTP_HOST]{$baseDir}slidelist.php" ?>" onClick="window.open('<?= "http://$_SERVER[HTTP_HOST]{$baseDir}slidelist.php" ?>','slidelist','toolbar=no,directories=no,location=no,status=no,menubar=no,resizable=no,scrollbars=yes,width=300,height=500,left=<?= $winW-300 ?>,top=0'); return false" class="linka"><?= $slideNum ?></a> &nbsp; &nbsp; </i></b></span></td>
                  <td><? if( !empty($nextSlideNum) )
                    echo "<a href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$nextSlideNum\">"
                        . '<img src="images/next.gif" border="0" hspace="2" /></a>';
                ?></td>
              <td><img src="images/blank.gif" height="10" width="15" /></td>
              </tr>
            </table>
            <br clear="left" />
            <hr style="border: 0; color: #CC6600; background-color: #CC6600; height: 2px">
            </div></div>
            <?	
            break;

            case 'php':
            default:
            echo "<div id=\"stickyBar\" class=\"sticky\" align=\"$slide->titleAlign\" style=\"width: 100%;\"><div class=\"navbar\">";
            if(!empty($slide->logo1)) $logo1 = $slide->logo1;
            else $logo1 = $pres[1]->logo1;
            if(!empty($slide->logoimage1url)) $logo1url = $slide->logoimage1url;
            else $logo1url = $pres[1]->logoimage1url;				
            if(!empty($logo1)) {
                echo "<a href=\"$logo1url\"><img src=\"$logo1\" border=\"0\" align=\"left\" style=\"float: left;\" alt=\"".$pres[1]->slides[$slideNum]->filename."\"></a>";
                $offset+=2;
            }
            echo "<div align=\"center\" style=\"font-size: $titlesize; margin: 0 ".$offset."em 0 0;\"><a href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$slideNum\" style=\"text-decoration: none; color: $titlecolor;\">".markup_text($slide->title)."</a></div>";
            echo "<div style=\"font-size: $navsize; float: right; margin: -2em 0 0 0;\">";
            if(!empty($slide->logo2)) $logo2 = $slide->logo2;
            else $logo2 = $pres[1]->logo2;
            if (!empty($logo2)) {
                echo "<img src=\"$logo2\" border=\"0\"><br/>";
                $offset-=2;
            }
            echo "<a href=\"http://$_SERVER[HTTP_HOST]{$baseDir}slidelist.php\" style=\"text-decoration: none; color: $slide->titleColor;\" onClick=\"window.open('http://$_SERVER[HTTP_HOST]{$baseDir}slidelist.php','slidelist','toolbar=no,directories=no,location=no,status=no,menubar=no,resizable=no,scrollbars=yes,width=300,height=$slidelistH,left=".($winW-300).",top=0'); return false\">".($slideNum)."/".($maxSlideNum)."</a></div>";
            if ($pres[1]->navbartopiclinks) {
                echo "<div style=\"float: left; margin: -0.2em 2em 0 0; font-size: $navsize;\"><a href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$prev\" style=\"text-decoration: none; color: $slide->navColor;\">".markup_text($prevTitle)."</a></div>";
                echo "<div style=\"float: right; margin: -0.2em 2em 0 0; color: $slide->navColor; font-size: $navsize;\"><a href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$next\" style=\"text-decoration: none; color: $slide->navColor;\">".markup_text($nextTitle)."</a></div>";
            }
            echo '</div></div>';
            break;
        }

        // Slide layout templates
        if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
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

    function _blurb(&$blurb) {
        if($blurb->type=='speaker' && !$_SESSION['show_speaker_notes']) return;
        $effect = '';
        if($blurb->effect) $effect = "effect=\"$blurb->effect\"";
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
        global $slideDir;

        $effect = '';
        if($image->effect) $effect = "effect=\"$image->effect\"";
        if(isset($image->title)) echo '<h1>'.markup_text($image->title)."</h1>\n";
        if ($image->width) {
            $size = "width=\"{$image->width}\" height=\"{$image->height}\"";
        } else {
            $size = getimagesize($slideDir.$image->filename);
            $size = $size[3];
        }
?>
<div <?=$effect?> align="<?=$image->align?>" style="margin-left: <?=$image->marginleft?>; margin-right: <?=$image->marginright?>;">
<img align="<?=$image->align?>" src="<?=$slideDir.$image->filename?>" <?=$size?>>
</div>
<?php
        if(isset($image->clear)) echo "<br clear=\"".$image->clear."\"/>\n";

    }

    // Because we are eval()'ing code from slides, obfuscate all local 
    // variables so we don't get run over
    function _example(&$example) {
        global $pres, $objs, $slideDir;
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

        if(isset($example->title)) echo '<div style="font-size: '.(4*(float)$example->fontsize/3).'em;">'.markup_text($example->title)."</div>\n";
        if(!$example->hide) {
            $_html_sz = (float) $example->fontsize;
            if(!$_html_sz) $_html_sz = 0.1;
            $_html_offset = (1/$_html_sz).'em';
            echo '<div '.$_html_effect.' class="shadow" style="margin: '.
                ((float)$example->margintop).'em '.
                ((float)$example->marginright+1).'em '.
                ((float)$example->marginbottom).'em '.
                ((float)$example->marginleft).'em;'.
                ((!empty($example->width)) ? "width: $example->width;" : "").
                '">';
            if(!empty($pres[1]->examplebackground)) $_html_examplebackground = $pres[1]->examplebackground;
            if(!empty($objs[1]->examplebackground)) $_html_examplebackground = $objs[1]->examplebackground;
            if(!empty($example->examplebackground)) $_html_examplebackground = $example->examplebackground;

            echo '<div class="emcode" style="font-size: '.$_html_sz."em; margin: -$_html_offset 0 0 -$_html_offset;".
                ((!empty($_html_examplebackground)) ? "background: $_html_examplebackground;" : '').
                (($example->type=='shell') ? 'font-family: monotype.com, courier, monospace; background: #000000; color: #ffffff; padding: 0px;' : '').
                '">';

            $example->highlight();

            echo "</div></div>\n";
        }
        if($example->result && (empty($example->condition) || (!empty($example->condition) && isset(${$example->condition})))) {
            if(!$example->hide) echo '<div style="font-size: '.(4*(float)$example->fontsize/3)."em;\">Output</div>\n";
            $_html_sz = (float) $example->rfontsize;
            if(!$_html_sz) $_html_sz = 0.1;
            $_html_offset = (1/$_html_sz).'em';
            if(!empty($example->global) && !isset($GLOBALS[$example->global])) {
                global ${$example->global};
            }
            if(!empty($pres[1]->outputbackground)) $_html_outputbackground = $pres[1]->outputbackground;
            if(!empty($objs[1]->outputbackground)) $_html_outputbackground = $objs[1]->outputbackground;
            if(!empty($example->outputbackground)) $_html_outputbackground = $example->outputbackground;
            if(!empty($example->anchor)) echo "<a name=\"$example->anchor\"></a>\n";
            echo '<div class="shadow" style="margin: '.
                ((float)$example->margintop).'em '.
                ((float)$example->marginright+1).'em '.
                ((float)$example->marginbottom).'em '.
                ((float)$example->marginleft).'em;'.
                ((!empty($example->rwidth)) ? "width: $example->rwidth;" : "").
                '">';
            echo '<div '.$_html_effect.' class="output" style="font-size: '.$_html_sz."em; margin: -$_html_offset 0 0 -$_html_offset; ".
                ((!empty($_html_outputbackground)) ? "background: $_html_outputbackground;" : '').
                "\">\n";
            if(!empty($example->filename)) {
                $_html_filename = preg_replace('/\?.*$/','',$slideDir.$example->filename);
                switch($example->type) {
                    case 'genimage':
                        echo '<img src="'.$slideDir.$example->filename."\">\n";
                        break;
                    case 'iframe':
                        echo "<iframe width=\"$example->iwidth\" height=\"$example->iheight\" src=\"$slideDir$example->filename\"><a href=\"$slideDir$example->filename\" class=\"resultlink\">$example->linktext</a></iframe>\n";
                        break;
                    case 'link':
                        echo "<a href=\"$slideDir$example->filename\" class=\"resultlink\">$example->linktext</a><br />\n";
                        break;	
                    case 'nlink':
                        echo "<a href=\"$slideDir$example->filename\" class=\"resultlink\" target=\"_blank\">$example->linktext</a><br />\n";
                        break;
                    case 'embed':
                        echo "<embed src=\"$slideDir$example->filename\" class=\"resultlink\" width=\"800\" height=\"800\"></embed><br />\n";
                        break;
                    case 'flash':
                        echo "<embed src=\"$slideDir$example->filename?".time()." quality=high loop=true
pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\" 
type=\"application/x-shockwave-flash\" width=$example->iwidth height=$example->iheight>\n";
                        break;
                    case 'system':
                        system("DISPLAY=localhost:0 $slideDir$example->filename");
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
            echo "</div></div>\n";
#           if(!empty($example->anchor)) echo "</a>\n";
        }
    }

    function _break(&$break) {
        echo str_repeat("<br/>\n", $break->lines);
    }

    function _list(&$list) {
        if (!isset($list->bullets)) return;
        $align = '';
        if(isset($list->title)) {
            if(!empty($list->fontsize)) $style = "font-size: ".$list->fontsize.';';
            if(!empty($list->align)) $align = 'align="'.$list->align.'"';
            echo "<div $align style=\"$style\">".markup_text($list->title)."</div>\n";
        }
        echo '<ul>';
        while(list($k,$bul)=each($list->bullets)) { $bul->display(); }
        echo '</ul>';
    }

    function _bullet(&$bullet) {
        global $objs, $coid;

        if ($bullet->text == "") $bullet->text = "&nbsp;";
        $style='';
        $type='';
        $effect='';
        $eff_str='';
        $ml = $bullet->level;

        if(!empty($bullet->marginleft)) $ml += (float)$bullet->marginleft;
        else if(!empty($objs[$coid]->marginleft)) $ml += (float)$objs[$coid]->marginleft;

        if($ml) {
            $style .= "margin-left: ".$ml."em;";
        }

        if(!empty($bullet->start)) {
            if(is_numeric($bullet->start)) {
                $objs[$coid]->num = (int)$bullet->start;	
            } else {
                $objs[$coid]->alpha = $bullet->start;
            }
        }
        if(!empty($bullet->type)) $type = $bullet->type;
        else if(!empty($objs[$coid]->type)) $type = $objs[$coid]->type;

        if(!empty($bullet->effect)) $effect = $bullet->effect;
        else if(!empty($objs[$coid]->effect)) $effect = $objs[$coid]->effect;

        if(!empty($bullet->fontsize)) $style .= "font-size: ".$bullet->fontsize.';';
        else if(!empty($objs[$coid]->fontsize)) $style .= "font-size: ".(2*(float)$objs[$coid]->fontsize/3).'em;';

        if(!empty($bullet->marginright)) $style .= "margin-right: ".$bullet->marginleft.';';
        else if(!empty($objs[$coid]->marginright)) $style .= "margin-right: ".$objs[$coid]->marginright.';';

        if(!empty($bullet->padding)) $style .= "padding: ".$bullet->padding.';';
        else if(!empty($objs[$coid]->padding)) $style .= "padding: ".$objs[$coid]->padding.';';

        if ($effect) {
            $eff_str = "id=\"$bullet->id\" effect=\"$effect\"";
        } 
        switch($type) {
            case 'numbered':
            case 'number':
            case 'decimal':
                $symbol = $objs[$coid]->num++ . '.';
                break;
            case 'no-bullet':
            case 'none':
                $symbol='';
                break;
            case 'alpha':
                $symbol = $objs[$coid]->alpha++ . '.';
                break;
            case 'ALPHA':
                $symbol = strtoupper($objs[$coid]->alpha++) . '.';
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

        echo "<div $eff_str><li style=\"$style\">".'<tt>'.$symbol.'</tt> '.markup_text($bullet->text)."</li></div>\n";
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
        echo '<table '.$align.' width="'.$width.'" border="'.$table->border.'">';
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
        global $objs, $coid;

        $style='';
        if(!empty($cell->fontsize)) $style .= "font-size: ".$cell->fontsize.';';
        else if(!empty($objs[$coid]->fontsize)) $style .= "font-size: ".(2*(float)$objs[$coid]->fontsize/3).'em;';
        if(!empty($cell->marginleft)) $style .= "margin-left: ".$cell->marginleft.';';
        else if(!empty($objs[$coid]->marginleft)) $style .= "margin-left: ".$objs[$coid]->marginleft.';';

        if(!empty($cell->marginright)) $style .= "margin-right: ".$cell->marginleft.';';
        else if(!empty($objs[$coid]->marginright)) $style .= "margin-right: ".$objs[$coid]->marginright.';';

        if(!empty($cell->padding)) $style .= "padding: ".$cell->padding.';';
        else if(!empty($objs[$coid]->padding)) $style .= "padding: ".$objs[$coid]->padding.';';

        if(!empty($cell->bold) && $cell->bold) $style .= 'font-weight: bold;';
        else if(!empty($objs[$coid]->bold) && $objs[$coid]->bold) $style .= 'font-weight: bold;';

        echo "<span style=\"$style\">".markup_text($cell->text)."</span>\n";
    }

    function _link(&$link) {
        if(empty($link->text)) $link->text = $link->href;
        if(!empty($link->leader)) $leader = $link->leader;
        else $leader='';
        if (empty($link->target)) $link->target = '_self';
        if(!empty($link->text)) {
            echo "<div align=\"$link->align\" style=\"font-size: $link->fontsize; color: $link->textcolor; margin-left: $link->marginleft; margin-right: $link->marginright; margin-top: $link->margintop; margin-bottom: $link->marginbottom;\">$leader<a href=\"$link->href\" target=\"{$link->target}\">".markup_text($link->text)."</a></div><br />\n";
        }
    }

    function _divide(&$divide) {
        global $objs;

        // Slide layout templates
        if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
            case '2columns':
                echo "</div>\n<div class=\"c2right\">\n";
                break;
            case '2columns-noborder':
                echo "</div>\n<div class=\"c2rightnb\">\n";
                break;
        }
    }

    function _footer(&$footer) {
        global $objs, $pres, $nextTitle;

        // Slide layout templates
        if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
            default:
                echo "</div>\n";
                break;
        }
        // Navbar layout templates
        switch($pres[1]->template) {
            case 'mysql':
                if(!strstr($_SERVER['HTTP_USER_AGENT'],'MSIE')) {
                ?>
                <div class="bsticky">
                <img style="margin-bottom: -0.3em" src="images/bottomswoop.gif" width="100%" height="50" />
                <span class="c4">&copy; Copyright 2002 MySQL AB</span>
                </div>
                <?
                }
                break;
/* (this seemed too intrusive)
            case 'php2':
                if($nextTitle) {
                ?>
                <span class="C5">
                    <?echo 'next: '.markup_text($nextTitle);?>
                </span>
                <?
                }
                break;
*/
        }
    }

}

class plainhtml extends html {

    function _presentation(&$presentation) {
        global $pres, $objs;

            echo <<<HEADER
<html>
<head>
<base href="http://$_SERVER[HTTP_HOST]$baseDir">
<title>{$presentation->title}</title>
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
}

    function _slide(&$slide) {
        global 	$slideNum, $maxSlideNum, $winW, $prevTitle, 
                $nextTitle, $baseDir, $showScript,
                $pres, $objs;
        $currentPres = $_SESSION['currentPres'];
        
        $navsize = $slide->navSize;
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
            if(!empty($slide->logo1)) $logo1 = $slide->logo1;
            else $logo1 = $pres[1]->logo1;
            if(!empty($slide->logoimage1url)) $logo1url = $slide->logoimage1url;
            else $logo1url = $pres[1]->logoimage1url;				
            if(!empty($logo1)) echo "<a href=\"$logo1url\"><img src=\"$logo1\" border=\"0\" align=\"left\"></a>\n";
            echo "</td>\n";
            if ($pres[1]->navbartopiclinks) {
                echo "<td align=\"left\">";
                if($prevTitle) echo "<a href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$prev\" style=\"text-decoration: none;\"><font size=+2>Previous: ".markup_text($prevTitle)."</font></a></td>\n";
                if($nextTitle) echo "<td align=\"right\"><a href=\"http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$next\" style=\"text-decoration: none;\"><font size=+2>Next: ".markup_text($nextTitle)."</font></a></td>";
            }
            echo "<td rowspan=2 width=1>";
            if(!empty($slide->logo2)) $logo2 = $slide->logo2;
            else $logo2 = $pres[1]->logo2;
            if (!empty($logo2)) {
                echo "<img src=\"$logo2\" align=\"right\">\n";
            }
            echo "</td>\n";
            echo "<tr><th colspan=3 align=\"center\"><font size=+4>".markup_text($slide->title)."</font></th></table>\n";

            break;
        }

        // Slide layout templates
        if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
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
        global $slideDir;

        if(isset($image->title)) echo '<h1>'.markup_text($image->title)."</h1>\n";
        if ($image->width) {
            $size = "width=\"{$image->width}\" height=\"{$image->height}\"";
        } else {
            $size = getimagesize($slideDir.$image->filename);
            $size = $size[3];
        }
?>
<div align="<?=$image->align?>"
style="margin-left: <?=$image->marginleft?>; margin-right: <?=$image->marginright?>;">
<img src="<?=$slideDir.$image->filename?>" <?=$size?>>
</div>
<?php
    }

    function _example(&$example) {
        global $pres, $objs, $slideDir;
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
            if(!empty($pres[1]->examplebackground)) $_html_examplebackground = $pres[1]->examplebackground;
            if(!empty($objs[1]->examplebackground)) $_html_examplebackground = $objs[1]->examplebackground;
            if(!empty($example->examplebackground)) $_html_examplebackground = $example->examplebackground;

            echo "<table bgcolor=\"$_html_examplebackground\"><tr><td>\n";
            $example->highlight();
            echo "</td></tr></table>\n";
        }
        if($example->result && (empty($example->condition) || (!empty($example->condition) && isset(${$example->condition})))) {
            if(!$example->hide) {
                echo "<h2>Output</h2>\n";
            }
            if(!empty($example->global) && !isset($GLOBALS[$example->global])) {
                global ${$example->global};
            }
            if(!empty($pres[1]->outputbackground)) $_html_outputbackground = $pres[1]->outputbackground;
            if(!empty($objs[1]->outputbackground)) $_html_outputbackground = $objs[1]->outputbackground;
            if(!empty($example->outputbackground)) $_html_outputbackground = $example->outputbackground;
            if(!empty($example->anchor)) echo "<a name=\"$example->anchor\"></a>\n";
            echo "<br /><table bgcolor=\"$_html_outputbackground\"><tr><td>\n";

            if(!empty($example->filename)) {
                $_html_filename = preg_replace('/\?.*$/','',$slideDir.$example->filename);
                switch($example->type) {
                    case 'genimage':
                        echo "<img src=\"$slideDir$example->filename\">\n";
                        break;
                    case 'iframe':
                    case 'link':
                    case 'embed':
                        echo "<a href=\"$slideDir$example->filename\">$example->linktext</a><br />\n";
                        break;
                    case 'flash':
                        echo "<embed src=\"$slideDir$example->filename?".time()." quality=high loop=true
pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\" 
type=\"application/x-shockwave-flash\" width=$example->iwidth height=$example->iheight>\n";
                        break;
                    case 'system':
                        system("DISPLAY=localhost:0 $slideDir$example->filename");
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
#			if(!empty($example->anchor)) echo "</a>\n";
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
        $i = 1;
        while(list($k,$cell)=each($table->cells)) {
            if(!($i % $table->columns)) {
                echo "<tr>\n";
            }
            echo " <td>";
            $cell->display();
            echo " </td>";
            if(($i % $table->columns)==0) {
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
        global $objs;

        // Slide layout templates
        if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
            case '2columns':
                echo "</td>\n<td valign=\"top\">\n";
                break;
            case '2columns-noborder':
                echo "</td>\n<td valign=\"top\">\n";
                break;
        }
    }

    function _footer(&$footer) {
        global $objs;

        // Slide layout templates
        if(!empty($objs[1]->layout)) switch($objs[1]->layout) {
            default:
                echo "</td></tr></table>\n";
                break;
        }
    }

}


class flash extends html {

    function _slide(&$slide) {
        global $objs,$pres,$coid, $winW, $winH, $baseDir;

        list($dx,$dy) = getFlashDimensions($slide->titleFont,$slide->title,flash_fixsize($slide->titleSize));
        $dx = $winW;  // full width
?>
<div align="<?=$slide->titleAlign?>" class="sticky" id="stickyBar">
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
            case '2columns-noborder':
                echo "<div class=\"c2leftnb\">\n";
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
}

class pdf extends display {

    function _presentation(&$presentation) {
        global $pres, $presentationDir;
        global 	$pdf, $pdf_x, $pdf_y, $slideNum, $maxSlideNum, 
                $baseDir, $showScript, $pres, $objs,
                $pdf_cx, $pdf_cy, $page_index, $page_number, $pdf_font;

        // In PDF mode we loop through all the slides and make a single
        // big multi-page PDF document.
        $page_number = 0;
        $pdf = pdf_new();
        if(!empty($pdfResourceFile)) pdf_set_parameter($pdf, "resourcefile", $pdfResourceFile);
        pdf_open_file($pdf);
        pdf_set_info($pdf, "Author",isset($presentation->speaker)?$presentation->speaker:"Anonymous");
        pdf_set_info($pdf, "Title",isset($presentation->title)?$presentation->title:"No Title");
        pdf_set_info($pdf, "Creator", "See Author");
        pdf_set_info($pdf, "Subject", isset($presentation->topic)?$presentation->topic:"");

        while(list($slideNum,$slide) = each($presentation->slides)) {
          $slideDir = dirname($presentationDir.'/'.$presentation->slides[$slideNum]->filename).'/';
            $r =& new XML_Slide($presentationDir.'/'.$presentation->slides[$slideNum]->filename);
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
        header('Content-disposition: inline; filename='.$_SESSION['currentPres'].'.pdf');
        header("Content-length: " . strlen($data));
        echo $data;
}


    function _slide(&$slide) {
        global 	$pdf, $pdf_x, $pdf_y, $slideNum, $maxSlideNum, 
                $baseDir, $showScript, $pres, $objs,
                $pdf_cx, $pdf_cy, $page_index, $page_number, $pdf_font;
        $currentPres = $_SESSION['currentPres'];

        $p = $pres[1];
        $middle = (int)($pdf_y/2) - 40;

        $pdf_cy = 25;  // top-margin
        $pdf_cx = 40;
    
        print_r($objs[1]->template);
    
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
            pdf_show_boxed($pdf, strip_markups($slide->title), 40, $pdf_cy, $pdf_x-2*$pdf_cx, 1, 'center');

            $page_index[$page_number] = strip_markups($slide->title);
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

    function _blurb(&$blurb) {
        global $pdf, $pdf_x, $pdf_y, $pdf_cx, $pdf_cy, $pdf_font;

        if(!empty($blurb->title)) {
            if($blurb->type=='speaker') {
                pdf_setcolor($pdf,'fill','rgb',1,0,0);
            }
            pdf_set_font($pdf, $pdf_font , -16, 'winansi');
            $dx = pdf_stringwidth($pdf,$blurb->title);
            $pdf_cy = pdf_get_value($pdf, "texty");
            switch($blurb->talign) {
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
            pdf_continue_text($pdf, strip_markups($blurb->title));
            $pdf_cy = pdf_get_value($pdf, "texty");
            pdf_set_text_pos($pdf,$x,$pdf_cy+5);
            pdf_setcolor($pdf,'fill','rgb',0,0,0);
        }
        $pdf_cy = pdf_get_value($pdf, "texty");

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

        pdf_save($pdf);
        pdf_translate($pdf,0,$pdf_y);
        pdf_scale($pdf,1,-1);
        pdf_set_font($pdf, $pdf_font , 12, 'winansi');
        $leading = pdf_get_value($pdf, "leading");
        $height = $inc = 12+$leading;	
        $txt = strip_markups($blurb->text);

        while(pdf_show_boxed($pdf, $txt, $pdf_cx+20, $pdf_y-$pdf_cy, $pdf_x-2*($pdf_cx-20), $height, $align, 'blind')!=0) $height+=$inc;

        pdf_restore($pdf);

        if( ($pdf_cy + $height) > $pdf_y-40 ) {
            my_pdf_page_number($pdf);
            pdf_end_page($pdf);
            my_new_pdf_page($pdf, $pdf_x, $pdf_y);
            $pdf_cx = 40;
            $pdf_cy = 60;
        }

        pdf_set_font($pdf, $pdf_font , -12, 'winansi');
        if($blurb->type=='speaker') {
            pdf_setcolor($pdf,'fill','rgb',1,0,0);
        }
        pdf_show_boxed($pdf, str_replace("\n",' ',$txt), $pdf_cx+20, $pdf_cy-$height, $pdf_x-2*($pdf_cx+20), $height, $align);
        pdf_continue_text($pdf, "\n");
        pdf_setcolor($pdf,'fill','rgb',0,0,0);
    }

    function _image(&$image) {
        global $pdf, $pdf_x, $pdf_cx, $pdf_cy, $pdf_y, $pdf_font, $slideDir;

        if (strstr($this->filename,"blank")) return;
        
        if(isset($image->title)) {
            $pdf_cy = pdf_get_value($pdf, "texty");
            pdf_set_text_pos($pdf,$pdf_cx,$pdf_cy);
            pdf_set_font($pdf, $pdf_font , -16, 'winansi');
            pdf_continue_text($pdf, $image->title);
            pdf_continue_text($pdf, "\n");
        }
        $pdf_cy = pdf_get_value($pdf, "texty")-5;
        pdf_set_font($pdf, $pdf_font , -12, 'winansi');
        $cw = pdf_stringwidth($pdf,'m');  // em unit width

        if ($image->width) {
            $dx = $image->height;
            $dy = $image->width;
            list(,,$type) = getimagesize($slideDir.$image->filename);
        } else {
            list($dx,$dy,$type) = getimagesize($slideDir.$image->filename);
        }
        $dx = $pdf_x*$dx/1024;
        $dy = $pdf_x*$dy/1024;

        switch($type) {
            case 1:
                $im = pdf_open_gif($pdf, $slideDir.$image->filename);
                break;
            case 2:
                $im = pdf_open_jpeg($pdf, $slideDir.$image->filename);
                break;
            case 3:
                $im = pdf_open_png($pdf, $slideDir.$image->filename);
                break;
            case 7:
                $im = pdf_open_tiff($pdf, $slideDir.$image->filename);
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
            switch($image->align) {
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
            if(isset($image->marginleft)) {
                $x+= ((int)$image->marginleft) * $cw;
            }
            if(isset($image->marginright)) {
                $x-= ((int)$image->marginright) * $cw;
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

    function _example(&$example) {
        global $pres, $objs, $pdf, $pdf_cx, $pdf_cy, $pdf_x, $pdf_y, $pdf_font, $pdf_example_font, $slideDir, $baseDir;

        // Bring posted variables into the function-local namespace 
        // so examples will work
        foreach($_POST as $_html_key => $_html_val) {
            $$_html_key = $_html_val;
        }
        foreach($_SERVER as $_html_key => $_html_val) {
            $$_html_key = $_html_val;
        }

        if(!empty($example->title)) {
            $pdf_cy = pdf_get_value($pdf, "texty");
            pdf_set_text_pos($pdf,$pdf_cx,$pdf_cy);  // Force to left-margin
            pdf_set_font($pdf, $pdf_font , -16, 'winansi');
            pdf_continue_text($pdf, strip_markups($example->title));
            pdf_continue_text($pdf, "");
        }
        $pdf_cy = pdf_get_value($pdf, "texty");

        if(!$example->hide) {
            if(!empty($example->filename)) {
                $_html_filename = preg_replace('/\?.*$/','',$slideDir.$example->filename);
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
                case 'xml':
                default:
                    if($_html_file[strlen($_html_file)-1] != "\n") $_html_file .= "\n";
                    my_pdf_paginated_code($pdf, $_html_file, $pdf_x, $pdf_y, $pdf_cy+10, 60, $pdf_cx+30, $pdf_cx, $pdf_example_font, -10);
                    pdf_continue_text($pdf,"");
                    break;
            }
            
        }			
        $pdf_cy = pdf_get_value($pdf, "texty");
        if($example->result && $example->type!='iframe' && (empty($example->condition) || (!empty($example->condition) && isset(${$example->condition})))) {
            if(!$example->hide) {
                $pdf_cy = pdf_get_value($pdf, "texty");
                pdf_set_text_pos($pdf,$pdf_cx+20,$pdf_cy);  // Force to left-margin
                pdf_set_font($pdf, $pdf_font , -14, 'winansi');
                pdf_continue_text($pdf, "Output:");
                pdf_continue_text($pdf, "");
            }
            $pdf_cy = pdf_get_value($pdf, "texty");

            if(!empty($example->global) && !isset($GLOBALS[$example->global])) {
                global ${$example->global};
            }
            if(!empty($example->filename)) {
                $_html_filename = preg_replace('/\?.*$/','',$slideDir.$example->filename);
                switch($example->type) {
                    case 'genimage':
                        $fn = tempnam("/tmp","pres2");
                        $img = file_get_contents("http://localhost/".$baseDir.$slideDir.$example->filename,"r");
                        $fp_out = fopen($fn,"wb");
                        fwrite($fp_out,$img);
                        fclose($fp_out);
                        list($dx,$dy,$type) = getimagesize($fn);
                        $dx = $pdf_x*$dx/1024;
                        $dy = $pdf_x*$dy/1024;

                        switch($type) {
                            case 1:
                                $im = pdf_open_gif($pdf, $fn);
                                break;
                            case 2:
                                $im = pdf_open_jpeg($pdf, $fn);
                                break;
                            case 3:
                                $im = pdf_open_png($pdf, $fn);
                                break;
                            case 7:
                                $im = pdf_open_tiff($pdf, $fn);
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
                            pdf_save($pdf);
                            pdf_translate($pdf,0,$pdf_y);

                            $scale = $pdf_x/1024;
                            pdf_scale($pdf,1,-1);
                            pdf_place_image($pdf, $im, $pdf_cx, ($pdf_y-$pdf_cy-$dy), $scale);
                            pdf_restore($pdf);
                            pdf_set_text_pos($pdf,$pdf_cx,$pdf_cy+$dy);
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
                        // system("DISPLAY=localhost:0 $slideDir$example->filename");
                        break;	
                    default:
                        // Need something to turn html into pdf here?
                        // Perhaps just output buffering and stripslashes
                        // include $_html_filename;
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
                        my_pdf_paginated_code($pdf, $data, $pdf_x, $pdf_y, $pdf_cy, 60, $pdf_cx+30, $pdf_cx, $pdf_example_font, -10);
                        pdf_continue_text($pdf,"");
                        break;
                }
            }
        }
    }

    function _break(&$break) { /* empty */ }
    
    function _list(&$list) {
        global $pdf, $pdf_cx, $pdf_cy, $pdf_font;

        if (!isset($list->bullets)) return;
        if(isset($list->title)) {
            $pdf_cy = pdf_get_value($pdf, "texty");
            pdf_set_text_pos($pdf,$pdf_cx,$pdf_cy);
            pdf_set_font($pdf, $pdf_font, -16, 'winansi');
            pdf_continue_text($pdf, strip_markups($list->title));
            pdf_continue_text($pdf, "");
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
        global $pdf, $pdf_x, $pdf_y, $pdf_cx, $pdf_cy, $pdf_font, $obj;
        $type = '';
        $pdf_cy = pdf_get_value($pdf, "texty");
    
        pdf_set_font($pdf, $pdf_font, -12, 'winansi');
        $height=10;	
        $txt = strip_markups($bullet->text);

        pdf_save($pdf);
        pdf_translate($pdf,0,$pdf_y);
        pdf_scale($pdf,1,-1);
        pdf_set_font($pdf, $pdf_font , 12, 'winansi');
        $leading = pdf_get_value($pdf, "leading");
        $inc = $leading;	
        while(pdf_show_boxed($pdf, $txt, $pdf_cx+30, $pdf_y-$pdf_cy, $pdf_x-2*($pdf_cx+20), $height, 'left', 'blind')) $height+=$inc;

        pdf_restore($pdf);

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

        if( ($pdf_cy + $height) > $pdf_y-40 ) {
            my_pdf_page_number($pdf);
            pdf_end_page($pdf);
            my_new_pdf_page($pdf, $pdf_x, $pdf_y);
            $pdf_cx = 40;
            $pdf_cy = 60;
        }

        pdf_set_font($pdf, $pdf_font , -12, 'winansi');
        if($bullet->type=='speaker') {
            pdf_setcolor($pdf,'fill','rgb',1,0,0);
        }

        if(!empty($bullet->start)) {
            if(is_numeric($bullet->start)) {
                $obj->num = (int)$bullet->start;
            } else {
                $obj->alpha = $bullet->start;
            }
        }

        if(!empty($bullet->type)) $type = $bullet->type;
        else if(!empty($obj->type)) $type = $obj->type;

        switch($type) {
            case 'numbered':
            case 'number':
            case 'decimal':
                print_r($obj);
                $symbol = ++$obj->num . '.';
                $pdf_cx_height = 30;
                break;
            case 'no-bullet':
                case 'none':
                $symbol='';
                $pdf_cx_height = 20;
                break;
            case 'alpha':
                $symbol = $obj->alpha++ . '.';
                break;
            case 'ALPHA':
                $symbol = strtoupper($obj->alpha++) . '.';
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

        pdf_show_xy($pdf, $symbol, $pdf_cx+20 + $bullet->level*10, $pdf_cy+$leading-1);
        pdf_show_boxed($pdf, $txt, $pdf_cx+40 + $bullet->level*10, $pdf_cy-$height, $pdf_x-2*($pdf_cx+20), $height, 'left');
        pdf_continue_text($pdf,"\n");
        $pdf_cy = pdf_get_value($pdf, "texty");
        pdf_set_text_pos($pdf, $pdf_cx, $pdf_cy-$leading/2);
        pdf_setcolor($pdf,'fill','rgb',0,0,0);
    }

    function _table(&$table) {
        global $pdf, $pdf_x, $pdf_cx, $pdf_cy, $pdf_font;

        if(isset($table->title)) {
            $pdf_cy = pdf_get_value($pdf, "texty");
            pdf_set_text_pos($pdf,$pdf_cx,$pdf_cy);
            pdf_set_font($pdf, $pdf_font, -16, 'winansi');
            pdf_continue_text($pdf, strip_markups($table->title));
            pdf_continue_text($pdf, "");
        }
        $width="100%";
        if(!empty($table->width)) {
            $width = $table->width;
        }
        $width = (int)$width;
        $max_w = $pdf_x - 2*$pdf_cx;
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
        pdf_continue_text($pdf, "");
    }

    function _cell(&$cell) {
        global $pdf, $pdf_x, $pdf_y, $pdf_cx, $pdf_cy, $pdf_font, $pdf_font_bold, $coid;
        static $row_text = array();

        $row_text[] = $cell->text;
        if(!$cell->end_row) return;
        
        $pdf_cy = pdf_get_value($pdf, "texty");
    
        pdf_set_font($pdf, $pdf_font, -12, 'winansi');
        $height=10;	
        $txt = strip_markups($row_text[0]);
        while(pdf_show_boxed($pdf, $txt, 60, $pdf_cy, $pdf_x-120, $height, 'left', 'blind')) $height+=10;
        if( ($pdf_cy + $height) > $pdf_y-40 ) {
            my_pdf_page_number($pdf);
            pdf_end_page($pdf);
            my_new_pdf_page($pdf, $pdf_x, $pdf_y);
            $pdf_cx = 40;
            $pdf_cy = 60;
        }
        pdf_set_font($pdf, $pdf_font, -12, 'winansi');
        if(!empty($cell->bold) && $cell->bold) pdf_set_font($pdf, $pdf_font_bold, -12, 'winansi');
        else if(!empty($obj->bold) && $obj->bold) pdf_set_font($pdf, $pdf_font_bold, -12, 'winansi');
        $off = 0;
        foreach($row_text as $t) {
            pdf_show_boxed($pdf, strip_markups($t), 60+$off, $pdf_cy-$height, 60+$off+$cell->offset, $height, 'left');
            $off += $cell->offset;
        }
        $pdf_cy+=$height;
        pdf_set_text_pos($pdf, $pdf_cx, $pdf_cy);
        pdf_continue_text($pdf,"");	
        $row_text = array();
    }

    function _link(&$link) {
        global $pdf, $pdf_cx, $pdf_x, $pdf_y, $pdf_font;


        if(empty($link->text)) $link->text = $link->href;
        if(!empty($link->leader)) $leader = $link->leader;
        else $leader='';

        if(!empty($link->text)) {
            $pdf_cy = pdf_get_value($pdf, "texty")+10;
            pdf_set_font($pdf, $pdf_font, -12, 'winansi');
            if(strlen($leader)) $lx = pdf_stringwidth($pdf, $leader);
            else $lx=0;
            $dx = pdf_stringwidth($pdf, $link->text);
            $cw = pdf_stringwidth($pdf,'m');  // em unit width
            switch($link->align) {
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
            if($link->marginleft) $x += (int)(((float)$link->marginleft) * $cw);
            pdf_add_weblink($pdf, $x+$lx, $pdf_y-$pdf_cy-3, $x+$dx+$lx, ($pdf_y-$pdf_cy)+12, $link->text);
            pdf_show_xy($pdf, strip_markups($leader).strip_markups($link->text), $x, $pdf_cy);
            pdf_continue_text($pdf,"");
        }
    }

    function _divide(&$divide) { /* empty */ }
    
    function _footer(&$footer) {
        global $pdf;

        my_pdf_page_number($pdf);
    }

}

// US-Letter
class pdfus extends pdf {
    function pdfus() {
        global $pdf_x, $pdf_y;
        $pdf_x = 612;  $pdf_y = 792;
    }
}    

// US-Legal
class pdfusl extends pdf {
    function pdfusl() {
        global $pdf_x, $pdf_y;
        $pdf_x = 612;  $pdf_y = 1008;
    }
}    

// A4
class pdfa4 extends pdf {
    function pdfa4() {
        global $pdf_x, $pdf_y;
        $pdf_x = 595;  $pdf_y = 842;
    }
}    

?>