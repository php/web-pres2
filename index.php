<?php

	require_once 'config.php';
	session_start();
	require_once 'XML_Presentation.php';
	
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
<title>PHP Presents</title>
<?php include("css.php"); ?>
</head>
<body>
<?php

 echo "<div class='sticky' align='$this->titleAlign' style='width: 100%;'><div class='navbar'>";

   $logo1 = $pres[1]->logo1;

 echo "<img src='$logo1' align='left' style='float: left;'>";
 
   $logo2 = $pres[1]->logo2;

 if ($logo2) {
   echo "<img src='$logo2' align='right' style='float: right;'>";
 }
 
 echo "<div style='font-size: $this->titleSize; margin: 0 2.5em 0 0;'><a href='http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$slideNum' style='text-decoration: none; color: $this->titleColor;'>$this->title</a></div>";

 if ($pres[1]->navbartopiclinks) {
    echo "<div style='float: left; margin: -0.2em 2em 0 0; font-size: $this->navSize;'><a href='http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$prev' style='text-decoration: none; color: $this->navColor;'>$prevTitle</a></div>";
    echo "<div style='float: right; margin: -0.2em 2em 0 0; color: $this->navColor; font-size: $this->navSize;'><a href='http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$next' style='text-decoration: none; color: $this->navColor;'>$nextTitle</a></div>";
 }
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
	print('<p><a href="' . $PHP_SELF . '?topic=' . $i . '">' . $i . '</a> (' . $topic['count'] . ')</p>');
}

} else {
?>
<p>The available presentations are...</p>
<table>
<tr><th>Title</th><th>Date</th><th>Location</th><th>Speaker</th><th>Slides</th></tr>
<?php
$prnum = sizeof($pr);

for($j=0; $j < $prnum; $j++) {

	if(strtolower($pr[$j]['topic']) == strtolower($topic)) {
		print("<tr><td><a href=\"$baseDir$showScript/{$pr[$j]['id']}\">{$pr[$j]['title']}</a></td><td>{$pr[$j]['date']}</td>");
		print("<td>{$pr[$j]['location']}</td><td>{$pr[$j]['speaker']}</td><td>{$pr[$j]['slidecount']}</td></tr>");

	}

}
echo '</table>';
}
?>
</div>
</div>
</body>
</html>
