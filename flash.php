<?php

// Load class definitions
require_once 'objects.php';
require_once 'config.php';

session_start();
$currentPres = $_SESSION['currentPres'];

if (!extension_loaded('ming')) {
    if (!dl('php_ming.so')) {
        exit;
    }
}

$m = new SWFMovie();

/*
$fp = fopen("/tmp/debug.txt","w");
fputs($fp,"coid=$coid\ntype=$type\ntext=".($objs[$coid]->title)."\n");
fclose($fp);
*/
switch($type) {
	case 'title':
		// Entire movie will get key events - make shape that covers the whole thing
		$s = new SWFShape();
		$s->setRightFill($s->addFill(0, 0, 0));
		$s->drawLine($dx, 0);
		$s->drawLine(0, $dy);
		$s->drawLine(-$dx, 0);
		$s->drawLine(0, -$dy);

		// Need a button to receive the key events - shape from above
		$b = new SWFButton();
		$b->addShape($s, SWFBUTTON_KEYPRESS);

		// Space bar or Enter takes us to the next slide
		if($slideNum < $maxSlideNum) {
			$next = $slideNum+1;
			$b->addAction(new SWFAction("getURL('http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$next','_self');"), swfbutton_keypress(' '));
			$b->addAction(new SWFAction("getURL('http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$next','_self');"), swfbutton_keypress(chr(13)));
		}

		// Backspace or DEL bar takes us to the previous slide
		if($slideNum > 0) {
			$prev = $slideNum - 1;
			$b->addAction(new SWFAction("getURL('http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$prev','_self');"), swfbutton_keypress(chr(8)));
			$b->addAction(new SWFAction("getURL('http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$prev','_self');"), swfbutton_keypress(chr(127)));
		}

		// ESC reloads the current slide
		$b->addAction(new SWFAction("getURL('http://$_SERVER[HTTP_HOST]$baseDir$showScript/$currentPres/$slideNum','_self');"), swfbutton_keypress(chr(27)));

		// ? brings up the help page
		$b->addAction(new SWFAction("getURL('http://$_SERVER[HTTP_HOST]$baseDir$helpPage','_blank');"), swfbutton_keypress('?'));

		$f = new SWFFont($objs[$coid]->titleFont);
		$m->setBackground(0x66, 0x66, 0x99);
		$t = new SWFText();
		$t->setFont($f);

		$rgb = rgb($objs[$coid]->titleColor);	
		$t->setColor($rgb[0], $rgb[1], $rgb[2]);

		$tHeight = flash_fixsize($objs[$coid]->titleSize);
		$t->setHeight($tHeight);

		$tText = $objs[$coid]->title;
		$t->addString($tText);

		$m->setDimension($dx, $dy);

		// Add the text to the movie and position it
		$i = $m->add($t);
		$i->moveTo((int)($dx/2)-$t->getWidth($tText)/2, $dy-round($t->getDescent())-($dy-$tHeight)/2);

		// Don't forget to add the button
		$m->add($b);
		break;

	case 'blurb':
		$m->setBackground(0xff, 0xff, 0xff);
		$m->setDimension($dx, $dy);
		$t = new SWFTextField();

		if(!empty($objs[$coid]->title)) {
			$rgb = rgb($objs[$coid]->titleColor);	
			$t->setColor($rgb[0], $rgb[1], $rgb[2]);
			$t = new SWFText();
			$f = new SWFFont($objs[$coid]->font);
			$t->setFont($f);
			$t->setHeight(flash_fixsize($objs[$coid]->titleSize));
			$t->addString($objs[$coid]->title);
			$i = $m->add($t);
			if($in==0) $i->moveTo(5, 0);
		}
		foreach($el['text'] as $in=>$val) {
			if(!empty($el['text'][$in]['data'])) {
				$t = new SWFTextField();
				$t->setColor($defaultColor[0], $defaultColor[1], $defaultColor[2]);
				$t->align(SWFTEXTFIELD_ALIGN_LEFT);	
				$t->setFont($f);
				$t->setHeight((int)($_GET['h']/5));
				$t->addString($el['text'][$in]['data']);
				$i = $m->add($t);
				if($in==0) $i->moveTo(15, 5);
			}
		}
		break;
}

header('Content-type: application/x-shockwave-flash');
$m->output();

function rgb($rgb) {
	if(strlen($rgb)==6) {
		$r = hexdec(substr($rgb,0,2));
		$g = hexdec(substr($rgb,2,2));
		$b = hexdec(substr($rgb,4,2));
	} else $r = $g = $b = 0;
	return array($r,$g,$b);
}

?>
