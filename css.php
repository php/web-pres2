<style title="Default" type="text/css">
body {
	font-size: <?php echo $pres[1]->basefontsize; ?>;
	margin-left:1.5em;
	margin-right:0em;
	margin-bottom:0em;
	<?php
	if ($pres[1]->backgroundcol) { echo "background: $pres[1]->backgroundcol;\n"; }
	if ($pres[1]->backgroundimage) echo "background-image: url({$pres[1]->backgroundimage});\n";
	if ($pres[1]->backgroundfixed) echo "background-attachment : fixed;\nbackground-repeat : no-repeat;\n";
	?>
}
div.sticky {
	margin: 0;
<?if(strstr($HTTP_USER_AGENT,'MSIE')): // Need a much smarter check here ?>
	position: absolute;
<?else:?>
	position: fixed;
<?endif?>
	top: 0em;
	left: 0em;
	right: auto;
	bottom: auto;
	width: auto;
}
div.shadow {
	background: #777777;
	padding: 0.5em;
}
div.navbar {
	background: <?php echo $pres[1]->navbarbackground; ?>;
	padding: 4;
	margin: 0;
	<?php if ($pres[1]->navbartopiclinks) { ?>height: 6em;<?php } ?>
	color: #ffffff;
	font-family: verdana, tahoma, arial, helvetica, sans-serif;
}
div.emcode {
	background: #cccccc;
	border: thin solid #000000;
	padding: 0.5em;
	font-family: monospace;
}
div.output {
	font-family: monospace;
	background: #cccc55;
	border: thin solid #000000;
	padding: 0.5em;
}
h1 {
	font-size: 2em;
}
p,li {
	font-size: 2.6em;
}
a {
	text-decoration: none;
}
a:hover {
	text-decoration: underline;
}
</style>