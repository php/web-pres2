<style title="Default" type="text/css">
body {
	font-size: <?php echo $pres[1]->basefontsize; ?>;
	margin-left:1.5em;
	margin-right:0em;
	margin-bottom:0em;
	<?php
	if ($pres[1]->backgroundcol) { echo "background: {$pres[1]->backgroundcol};\n"; }
	if ($pres[1]->backgroundimage) echo "background-image: url({$pres[1]->backgroundimage});\n";
	if ($pres[1]->backgroundfixed) echo "background-attachment : fixed;\n";
	if ($pres[1]->backgroundrepeat) echo "background-repeat : repeat\n";
	else echo "background-repeat : no-repeat\n";
	?>
}
div.sticky {
	margin: 0;
<?if(strstr($_SERVER['HTTP_USER_AGENT'],'MSIE')): // Need a much smarter check here ?>
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
div.bsticky {
	margin: 0;
<?if(strstr($_SERVER['HTTP_USER_AGENT'],'MSIE')): // Need a much smarter check here ?>
	position: absolute;
<?else:?>
	position: fixed;
<?endif?>
	top: auto; 
	left: 0em;
	right: auto;
	bottom: 0em;
	width: 100%;
}
div.shadow {
	background: <?php echo $pres[1]->shadowbackground; ?>;
	padding: 0.5em;
}
div.navbar {
	background: <?php echo $pres[1]->navbarbackground; ?>;
	padding: 4;
	margin: 0;
        height: <?php echo $pres[1]->navbarheight; ?>;
	color: #ffffff;
	font-family: verdana, tahoma, arial, helvetica, sans-serif;
	z-index: 99;
}
div.emcode {
	background: <?php echo $pres[1]->examplebackground; ?>;
	border: thin solid #000000;
	padding: 0.5em;
	font-family: monospace;
}

div.output {
	font-family: monospace;
	background: <?php echo $pres[1]->outputbackground; ?>;
	border: thin solid #000000;
	padding: 0.5em;
}

table.index {
 background: <?php echo $pres[1]->examplebackground; ?>;
 border: thin dotted #000000;                                                                                                 
 padding: 0.5em;
 font-family: monospace;
}

td.index {
 background: <?php echo $pres[1]->examplebackground; ?>;
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
td.c1 {font-family: arial,helvetica,verdana; font-size: 80%}

</style>
