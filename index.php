<?php
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 fdm=marker
 * vim<600: sw=4 ts=4
 */
	if(!empty($PATH_INFO)) {
	  $topic = trim(substr($PATH_INFO,1));
	}

	require_once 'config.php';
	require_once 'XML_Presentation.php';

	session_start();
	session_register('selected_display_mode');
	
	$topics = array();

	$dir = opendir($presentationDir);
	while($file = readdir($dir)) {
		if($file[0] != '.' && substr($file,-4) == '.xml') {
			$i = substr($file, 0, strpos($file, '.'));
			$ps[$i] = "$presentationDir/$file";
		}
	}
	closedir($dir);

	$i = 0;
		
	foreach($ps as $pres_id=>$filename) {

		$p = &new XML_Presentation($filename);
		$p->setErrorHandling(PEAR_ERROR_DIE,"%s\n");
		$p->parse();
		$pres = $p->getObjects();
		
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
		$i++;
	}
	unset($pres);

	// default options for the file..
	$p = &new XML_Presentation("index.xml");
	$p->setErrorHandling(PEAR_ERROR_DIE,"%s\n");        
	$p->parse();        
	$pres = $p->getObjects();   
?>

<html>
<head>
<base href="<?="http://$HTTP_HOST".$baseDir?>">
<title>PHP Presents</title>
<?php include("css.php"); ?>
<script language="JavaScript1.2">
<!--
function change_mode() {
	document.cookie="display_mode="+document.modes_form.modes.value;	
	top.location=top.location.href;
}
-->
</script>
</head>
<body>
<?php

	echo '<div class="sticky" align="center" style="width: 100%;"><div class="navbar">';

	$logo1 = $pres[1]->logo1;
	echo "<img src=\"$logo1\" align=\"left\" style=\"float: left;\">";
 
	$logo2 = $pres[1]->logo2;

	if ($logo2) {
		echo "<img src=\"$logo2\" align=\"right\" style=\"float: right;\">";
	}
 
	echo "<div style=\"font-size: 3em; margin: 0 2.5em 0 0;\">PHP Presentation System</div>";

	echo '</div></div>';
?>
<br /><br /><br /><br /><br /><br />
<div class="shadow" style="margin: 1em 4em 0.8em 3em;">
<div class="output" style="font-size: 1.8em; margin: -0.5em 0 0 -0.5em;">
<?php if(!isset($topic)){ ?>
<p> Welcome to the PHP Presentation System. Here we list all of the available presentations stored
within this system.</p>
<p>
Simply click the topic you wish to find presentations on to view all available presentations.
</p>
<?php 
	foreach($topics as $i => $topic) {
		print('<p><a href="' . $baseDir . 'index.php/' . $i . '">' . $i . '</a> (' . $topic['count'] . ')</p>');
	}

} else {
	if(empty($display_mode)) $display_mode = 'html';
	$selected_display_mode = $display_mode;
?>
<form name="modes_form" action="<?=$PHP_SELF?>" method="POST">
<p>Please select a display mode:
<select name="modes" onChange="change_mode()">
<option value="html" <?=($display_mode=='html')?'SELECTED':''?>>Fancy HTML (Best with Mozilla)</option>
<option value="plainhtml" <?=($display_mode=='plainhtml')?'SELECTED':''?>>Plain HTML</option>
<option value="flash" <?=($display_mode=='flash')?'SELECTED':''?>>Flash 5 (navbar only)</option>
<option value="pdf" <?=($display_mode=='pdf')?'SELECTED':''?>>PDF (Barely working)</option>
</select>
</p>
</form>
<?php
switch($display_mode) {
	case 'html':
		if($jsKeyboard) {
			echo "<p>Keyboard controls are available: <br />\n".
				 " &lt;cursor-left&gt; previous slide<br />\n".
				 " &lt;cursor-right&gt; next slide<br />\n".
				 " also use &lt;cursor-right&gt; to step through animated slides.</p>\n";
		} else {
			echo "<p>Keyboard controls disabled</p>\n";
			break;
		}
		break;

	case 'flash':
		echo "<p>Keyboard controls available: <br />\n".
			 " &lt;Space&gt; or &lt;Enter&gt; next slide<br />\n".
			 " &lt;Backspace&gt; previous slide<br />\n";	
		break;
}
?>
<p>The available presentations are...</p>
<table align="center" class="index">
<tr><th>Title</th><th>Date</th><th>Location</th><th>Speaker</th><th>Slides</th></tr>
<?php
$prnum = sizeof($pr);

for($j=0; $j < $prnum; $j++) {

	if(strtolower($pr[$j]['topic']) == strtolower($topic)) {
		print("<tr><td class='index'><a href=\"$baseDir$showScript/{$pr[$j]['id']}\">{$pr[$j]['title']}</a></td><td class='index'>{$pr[$j]['date']}</td><td class='index'>{$pr[$j]['location']}</td><td class='index'>{$pr[$j]['speaker']}</td><td class='index'>{$pr[$j]['slidecount']}</td></tr>");

	}

}
echo '</table>';
}
?>
</div>
</div>
</body>
</html>
