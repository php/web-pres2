<?php
	require_once 'config.php';

	session_start();
?>
<html><title>Slide Listing</title>
<head>
<script language="JavaScript1.2">
<!--
function slide(url){
window.opener.location=url
}
-->
</script>
</head>
<body bgcolor="#ffffff">
<?php
	echo "<table border=0><tr><th> # </th><td>Slide Title</td></tr>\n";
	foreach($titles as $k=>$v) {
		echo "<tr><td align=right>".($k)."</td><td><a href=\"slidelist.php\" style=\"text-decoration: none;\" onClick=\"javascript:slide('http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$k'); window.close();\">$v</a></td></tr>\n";
	}
	echo "</table>\n";
?>
