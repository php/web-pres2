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
?>
<div align="center"><font face="arial"><h1>Available Presentations</h1></font></div>
<table align="center" border="0" cellpadding="1" cellspacing="0" valign="top" width="95%">
	<tr>

		<td bgcolor="#000000">
			<table border="0" cellpadding="3" cellspacing="1" width="100%">
				<tr bgcolor="#ccccff">
					<th align="center" width="10%"><font face="arial" size="3">ID</font></th>
					<th align="center" width="25%"><font face="arial" size="3">Title</font></th>
					<th align="center" width="15%"><font face="arial" size="3">Date</font></th>

					<th align="center" width="20%"><font face="arial" size="3">Location</font></th>
					<th align="center" width="20%"><font face="arial" size="3">Speaker</font></th>
					<th align="center" width="10%"><font face="arial" size="3">Slides</font></th>
				</tr>
<?
	foreach($date as $i=>$date) {
		echo <<<ROW
				<tr bgcolor="#ffffff">
					<td align="left"><font face="arial" size="3"><b><a href="$showScript/$i">$i</a></b></font></td>
					<td align="center"><font face="arial" size="3">$title[$i]</font></td>
					<td align="center"><font face="arial" size="3">$date</font></td>
					<td align="center"><font face="arial" size="3">$location[$i]</font></td>
					<td align="center"><font face="arial" size="3">$speaker[$i]</font></td>
					<td align="center"><font face="arial" size="3">$slidecount[$i]</font></td>
				</tr>

ROW;
	}
?>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
