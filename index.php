<?php
	if(!empty($_SERVER['PATH_INFO'])) {
	  $topic = trim(substr(urldecode($_SERVER['PATH_INFO']),1));
	}

	require 'config.php';
	require 'XML_Presentation.php';
	require_once 'messages.php';

	session_start();
	
	$topics = array();
	$ps = array();

	if ($dir = @opendir($presentationDir)) {
		while($file = @readdir($dir)) {
			if($file[0] != '.' && substr($file,-4) == '.xml' && is_readable("$presentationDir/$file")) {
				$i = substr($file, 0, strpos($file, '.'));
				$ps[$i] = "$presentationDir/$file";
			}
		}

		@closedir($dir);
	}
	
	if (!$ps) {
		echo message('SLIDES_NOT_FOUND')." \$presentationDir $presentationDir<BR>";
		echo message('MODIFY_CONFIG')." config.php<BR>";
		exit;
	}
	
	$i = 0;
		
	foreach($ps as $pres_id=>$filename) {
		$fh = fopen($filename, "rb");
		$p = new XML_Presentation($fh);
		$p->setErrorHandling(PEAR_ERROR_TRIGGER, E_USER_WARNING);
		$check = $p->parse();
		if ($p->isError($check)) {
			continue;
		}
		$pres = $p->getObjects();
	
		// Do we have a generated reveal.js version of this presentation?	
		if(file_exists(substr($filename,0,strrpos($filename,'.')).".html")) {
			$pr[$i]['generated'] = basename(substr($filename,0,strrpos($filename,'.')));
		} else {
			$pr[$i]['generated'] = false;
		}
		$pr[$i]['id'] = $pres_id;
		$pr[$i]['slidecount'] = count($pres[1]->slides);
		$pr[$i]['title'] = $pres[1]->title;

		if(isset($pres[1]->date)) {
			$tmp = strtotime($pres[1]->date);
			if($tmp==-1) {
				$pr[$i]['date'] = $pres[1]->date;
			} else {
				$pr[$i]['date'] = date('M j, Y',$tmp);
			}
		} else $pr[$i]['date'] = '&nbsp;';

		if(isset($pres[1]->speaker)) {
			$pr[$i]['speaker'] = $pres[1]->speaker;
		} else $pr[$i]['speaker'] = '&nbsp;';
		
		if(isset($pres[1]->topic)) {
			$pr[$i]['topic'] = $pres[1]->topic;
			if(!empty($pres[1]->topic)){
				if(!isset($topics[$pres[1]->topic]['count'])) $topics[$pres[1]->topic]['count'] = 0;
				$topics[$pres[1]->topic]['count']++;
			}
		} else $pr[$i]['topic'] = '&nbsp;';

		if(isset($pres[1]->location)) {
			$pr[$i]['location'] = $pres[1]->location;
		} else $pr[$i]['location'] = '&nbsp;';

		if(isset($pres[1]->company)) {
			$pr[$i]['company'] = $pres[1]->company;
		} else $pr[$i]['company'] = '&nbsp;';
		$i++;
	}
	unset($pres);

	// default options for the file..
	$p = new XML_Presentation(fopen("index.xml", "rb"));
	$p->setErrorHandling(PEAR_ERROR_TRIGGER, E_USER_WARNING);
	$check = $p->parse();
	if ($p->isError($check)) {
		die("Could not parse index.xml, not sure what to do");
	}
	$pres = $p->getObjects();   
	$pres = $pres[1];
?>
<!doctype html>
<html>
<head>
<base href="<?php echo "http://".htmlspecialchars($_SERVER['HTTP_HOST']).$baseDir?>">
<meta charset="utf-8">
<title>PHP Presents</title>
<?php include "css.php"; ?>
<script>
function change_mode() {
	document.cookie="display_mode="+document.modes_form.modes.options[document.modes_form.modes.selectedIndex].value+"|"+document.modes_form.speaker.checked;
	top.location=top.location.href;
}
</script>
</head>
<body>
<?php

	echo '<div id="stickyBar" class="sticky" align="center" style="width: 100%;"><div class="navbar">';

	$logo1 = $pres->logo1;
	echo "<img src=\"$logo1\" align=\"left\" style=\"float: left;\">";
 
	$logo2 = $pres->logo2;

	if ($logo2) {
		echo "<img src=\"$logo2\" align=\"right\" style=\"float: right;\">";
	}
 
	echo "<div style=\"font-size: 3em; margin: 0 2.5em 0 0;\">".message('PRES2_TITLE')."</div>";

	echo '</div></div>';
?>
<br /><br /><br /><br /><br /><br />
<div class="shadow" style="margin: 1em 4em 0.8em 3em;">
<div class="output" style="font-size: 1.8em; margin: -0.5em 0 0 -0.5em;">
<?php if(empty($topic)){ ?>
<p><?php echo message('WELCOME_MSG'); ?></p>
<?php 
	ksort($topics);
	print('<table width="100%"><tr>'."\n");
	$col = 0;
	if (!isset($topic_cols) || $topic_cols == 0) {
		$topic_cols = 2;
	}
	$percent = (int)(100 / $topic_cols);
	foreach($topics as $i => $topic) {
		printf('<td width="%.1f%%" class="output" style="font-size: 1.8em; padding-bottom: 15px"><a href="' . $baseDir . 'index.php/%s">' . $i . '</a> (' . $topic['count'] . ')</td>'."\n", $percent, urlencode($i));
		if (++$col >= $topic_cols) { 
			$col=0; 
			print("</tr>\n<tr>"); 
		}
	}
	print('</tr></table>');
} else {
	if(empty($_COOKIE['display_mode'])) { $display_mode = 'html'; $form_speaker='false'; } 
	else { 
		list($display_mode,$form_speaker) = explode('|',$_COOKIE['display_mode']); 
	}
	$_SESSION['show_speaker_notes'] = ($form_speaker=='true');
	$_SESSION['selected_display_mode'] = $display_mode;

	// flags for extensions
	if (!extension_loaded('ming')) {
		$flag_ext_ming = false;
	} else {
		$flag_ext_ming = true;
	}
	if (!extension_loaded('pdf')) {
		$flag_ext_pdf = false;
	} else {
		$flag_ext_pdf = true;
	}

?>
<form name="modes_form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
<p><?php echo message('SELECT_MODE'); ?>
<select name="modes" onChange="change_mode()">
<option value="html" <?php echo ($display_mode=='html')?'SELECTED':''?>><?php echo message('OPT_FANCYHTML'); ?></option>
<option value="plainhtml" <?php echo ($display_mode=='plainhtml')?'SELECTED':''?>><?php echo message('OPT_PLAINHTML'); ?></option>
	<?php if ($flag_ext_ming) { ?>
<option value="flash" <?php echo ($display_mode=='flash')?'SELECTED':''?>><?php echo message('OPT_FLASH'); ?></option>
	<?php } ?>
	<?php if ($flag_ext_pdf) { ?>
<option value="pdfus" <?php echo ($display_mode=='pdfus')?'SELECTED':''?>><?php echo message('OPT_PDFLETTER'); ?></option>
<option value="pdfusl" <?php echo ($display_mode=='pdfusl')?'SELECTED':''?>><?php echo message('OPT_PDFLEGAL'); ?></option>
<option value="pdfa4" <?php echo ($display_mode=='pdfa4')?'SELECTED':''?>><?php echo message('OPT_PDFA4'); ?></option>
	<?php } ?>
</select>
<br />
<?php echo message('SHOW_NOTES'); ?> <input type="checkbox" name="speaker" <?php echo ($form_speaker=='true')?'checked':''?> onChange="change_mode()">
</p>
</form>
<?php
switch($display_mode) {
	case 'html':
		if($jsKeyboard) {
			echo "<p>".nl2br(message('HTML_KEYBOARD_CONTROLS'))."</p>\n";
		} else {
			echo "<p>".message('HTML_NO_KEYBOARD_CONTROLS')."</p>\n";
			break;
		}
		break;

	case 'flash':
		echo "<p>".nl2br(message('FLASH_KEYBOARD_CONTROLS'))."</p>\n";
		break;
}
?>
<p><?php echo message('FONT_SIZES'); ?></p>
<p><?php echo message('AVAILABLE_PRESENTATIONS'); ?></p>
<table align="center" class="index">
<tr><th align="left"> &nbsp;<?php echo message('PRES_TITLE'); ?></th>
	<th align="left"> &nbsp;<?php echo message('PRES_DATE'); ?></th>
	<th align="left"> &nbsp;<?php echo message('PRES_LOCATION'); ?></th>
	<th align="left"> &nbsp;<?php echo message('PRES_SPEAKER'); ?></th>
	<th align="left"> &nbsp;<?php echo message('PRES_SLIDES'); ?></th></tr>
<?php
$prnum = sizeof($pr);

function cmp($a,$b) {
	return strtotime($b['date']) - strtotime($a['date']);
}
usort($pr,'cmp');

for($j=0; $j < $prnum; $j++) {

	if(strtolower($pr[$j]['topic']) == strtolower($topic)) {
		if(!$pr[$j]['generated']) {
			echo "<tr><td class='index'><a href=\"$baseDir$showScript/{$pr[$j]['id']}\">{$pr[$j]['title']}</a></td><td class='index'>{$pr[$j]['date']}</td><td class='index'>{$pr[$j]['location']}</td><td class='index'>{$pr[$j]['speaker']}</td><td class='index'>{$pr[$j]['slidecount']}</td></tr>";
		} else {
			echo "<tr><td class='index'><a href=\"$baseDir{$pr[$j]['generated']}\">{$pr[$j]['title']}</a></td><td class='index'>{$pr[$j]['date']}</td><td class='index'>{$pr[$j]['location']}</td><td class='index'>{$pr[$j]['speaker']}</td><td class='index'>{$pr[$j]['slidecount']}</td></tr>";

		}
	}

}
echo '</table>';
}
?>
</div>
</div>
</body>
</html>
<?php
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 fdm=marker
 * vim<600: sw=4 ts=4
 */
