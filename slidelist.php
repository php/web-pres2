<?php
    require_once 'config.php';
    require_once 'XML_Presentation.php';
    require_once 'XML_Slide.php';

	session_start();
?>
<html><title>Slide Listing</title>
<head>
<script language="JavaScript1.2">
<!--
function slide(url){
window.opener.location=url
}
//-->
</script>
</head>
<body bgcolor="#ffffff">
<?php
	echo "<table border=0><tr><th> # </th><td>Slide Title</td></tr>\n";
	$lastSection = $lastChapter = '';

	foreach($_SESSION['titles'] as $k=>$v) {
        $spacer = '';
		if(!empty($v['section'])) {
			$spacer = "&nbsp;&nbsp;&nbsp;&nbsp;";
			if($v['section'] != $lastSection) {
				echo "<tr><th colspan=2 align=left><font size=+2>$v[section]</font></th></tr>\n";
			}
		} 
		if(!empty($v['chapter'])) {
            $spacer .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			if($v['chapter'] != $lastChapter) {
				echo "<tr><th colspan=2 align=left>$spacer$v[chapter]</th></tr>\n";
			}
		} 
		$lastSection = isset($v['section']) ? $v['section'] : '';
		$lastChapter = isset($v['chapter']) ? $v['chapter'] : '';
		echo "<tr><td align=right>".($k)."</td><td><a href=\"slidelist.php\" style=\"text-decoration: none;\" onClick=\"javascript:slide('http://$_SERVER[HTTP_HOST]$baseDir$showScript/$_SESSION[currentPres]/$k'); window.close();\">$spacer$v[title]</a></td></tr>\n";
	}
	echo "</table>\n";
?>
</body>
</html>
