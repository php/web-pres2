<?php
if (empty($pres)) $pres = $presentation;
?>
<style title="Default" type="text/css">
body {
	font-size: <?php echo $pres->basefontsize; ?>;
	margin-top:0em;
	margin-left:0em;
	margin-right:0em;
	margin-bottom:0em;
	<?php
	if ($pres->backgroundcol) { echo "background: {$pres->backgroundcol};\n"; }
	if ($pres->backgroundimage) echo "background-image: url({$pres->backgroundimage});\n";
	if ($pres->backgroundfixed) echo "background-attachment : fixed;\n";
	if ($pres->backgroundrepeat) echo "background-repeat : repeat\n";
	else echo "background-repeat : no-repeat\n";
	?>
}
div.sticky {
	margin: 0;
	position: fixed;
	top: 0em;
	left: 0em;
	right: auto;
	bottom: auto;
	width: auto;
}
div.bsticky {
	margin: 0;
	position: fixed;
	top: auto; 
	left: 0em;
	right: auto;
	bottom: 0em;
	width: 100%;
}
div.shadow {
	background: <?php echo $pres->shadowbackground; ?>;
	padding: 0.5em;
}
div.navbar {
	background: <?php echo $pres->navbarbackground; ?>;
	padding: 4;
	margin: 0;
        height: <?php echo $pres->navbarheight; ?>;
	color: #ffffff;
	font-family: verdana, tahoma, arial, helvetica, sans-serif;
	z-index: 99;
}
div.emcode {
	background: <?php echo $pres->examplebackground; ?>;
	border: thin solid #000000;
	padding: 0.5em;
	font-family: monospace;
}

div.output {
	font-family: monospace;
	background: <?php echo $pres->outputbackground; ?>;
	border: thin solid #000000;
	padding: 0.5em;
}

div.noshadow {
	font-family: monospace;
	background: <?php echo $pres->outputbackground; ?>;
}

table.index {
 background: <?php echo $pres->examplebackground; ?>;
 border: thin dotted #000000;                                                                                                 
 padding: 0.5em;
 font-family: monospace;
}

td.index {
 background: <?php echo $pres->examplebackground; ?>;
 padding: 1em;
 font-family: monospace;
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
.c2right {
	margin : 1em 1em 0 0; 
	padding-left : 1%;
	padding-right : 1%;
	border-style : solid; 
	border-top-width : 1px; 
	border-right-width : 1px; 
	border-bottom-width : 1px; 
	border-left-width : 1px; 
	border-right-color : inherit; 
	border-left-color : inherit; 
	width : 46%; 
	float : right; 
}
.c2rightnb {
	margin : 1em 1em 0 0; 
	padding-left : 1%;
	padding-right : 1%;
	width : 46%; 
	float : right; 
}
.c2left {
	margin : 1em 1em 0 0; 
	padding-left : 1%;
   	padding-right : 1%;
   	border-style : solid; 
	border-top-width : 1px; 
	border-right-width : 1px; 
	border-bottom-width : 1px; 
	border-left-width : 1px; 
   	border-right-color : inherit; 
   	border-left-color : inherit; 
   	width : 46%; 
   	float : left; 
}
.c2leftnb {
	margin : 1em 1em 0 0; 
	padding-left : 1%;
   	padding-right : 1%;
   	border-style : none; 
   	width : 46%; 
   	float : left; 
}
.box {
	margin : 0 0 0 0; 
	padding-left : 1%;
   	padding-right : 1%;
   	border-style : solid; 
	border-top-width : 1px; 
	border-right-width : 1px; 
	border-bottom-width : 1px; 
	border-left-width : 1px; 
   	border-right-color : inherit; 
   	border-left-color : inherit; 
   	float : left; 
}

A.linka { text-decoration: none; color: 000000; }
td.foo {color: ffffff; font-family: arial,verdana,helvetica; font-size: 70%}
span.c4 {position: fixed; bottom: 0.5em; right: 4em; top: auto; left: auto; color: ffffff; font-family: arial,verdana,helvetica; font-size: 70%}
td.c3 {color: CC6600; font-family: arial, helvetica, verdana}
span.c2 {color: ffffff; font-family: arial,hevetica,verdana}
span.c5 {position: fixed; bottom: 0.5em; right: 1em; top: auto; left: auto; color: 000000; font-family: arial,verdana,helvetica; font-size: 80%}
td.c1 {font-family: arial,helvetica,verdana; font-size: 80%}

</style>