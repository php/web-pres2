<?php
	require_once 'config.php';

	session_start();

	require_once 'XML_Presentation.php';

	$dir = opendir($presentationDir);
	while($file = readdir($dir)) {
		if($file[0]!='.' && substr($file,-4)=='.xml') {
			$i = substr($file,0,strpos($file,'.'));
			$ps[$i] = "$presentationDir/$file";		
		}
	}
	closedir($dir);

	foreach($ps as $i=>$filename) {
		$p =& new XML_Presentation($filename);
		$p->setErrorHandling(PEAR_ERROR_DIE,"%s\n");
		$p->parse();
		$pres = $p->getObjects();
		$slidecount[$i] = count($pres[1]->slides);
		$title[$i] = $pres[1]->title; 
		if(isset($pres[1]->date)) { 
			$tmp = strtotime($pres[1]->date);
			if($tmp==-1) $date[$i] = $pres[1]->date;
			else $date[$i] = date('M j, Y',$tmp);
		} else $date[$i] = '&nbsp;';
		if(isset($pres[1]->speaker)) {
			$speaker[$i] = $pres[1]->speaker;
		} else $speaker[$i] = '&nbsp;';
		if(isset($pres[1]->location)) {
			$location[$i] = $pres[1]->location;
		} else $location[$i] = '&nbsp;';
	}

	asort($date); // Sort presentations by date
	reset($ps);
	echo <<<TOP
<html>
<head>
<title>Presentations</title>
<base href="http://$HTTP_HOST$baseDir">
</head>
<body>
TOP;
	echo "<h1>Available Presentations</h1>\n";
	echo "<table><tr><th>ID</th><th>Title</th><th>Date</th><th>Location</th><th>Speaker</th><th>Slides</th></tr>\n";
	foreach($date as $i=>$date) {
		echo <<<ROW
  <tr><th align="left"><a href="$showScript/$i">$i</a></th><td>$title[$i]</td><td>$date</td><td>$location[$i]</td><td>$speaker[$i]</td><td align="right">$slidecount[$i]</td></tr>
ROW;
	}
	echo "</table>\n";
?>
</body>
</html>
