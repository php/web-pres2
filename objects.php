<?php
// vim: set tabstop=4 shiftwidth=4 fdm=marker:

// {{{ Helper functions

// {{{ getFLashDimensions - Find the height and width of the given flash string
function getFlashDimensions($font,$title,$size) {
	$f = new SWFFont($font);
	$t = new SWFText();
	$t->setFont($f);
	$t->setHeight($size);
	$dx = $t->getWidth($title) + 10;
	$dy = $size+10;
	return array($dx,$dy);
}
// }}}

// }}}

	// {{{ Presentation List Classes
	class _presentation {
		function _presentation() {
			$this->title = 'No Title Text for this presentation yet';
			$this->navmode  = 'flash';
		}
	}

	class _pres_slide {
		function _pres_slide() {
			$this->filename = '';
		}
	}
	// }}}

	// {{{ Slide Class
	class _slide {

		function _slide() {
			$this->title = 'No Title Text for this slide yet';
			$this->titleSize  = 18;
			$this->titleColor = 'ffffff';
			$this->titleAlign = 'center';
			$this->titleFont  = 'fonts/Arial.fdb';
			$this->mode  = 'flash';
		}

		function display() {
			if(isset($this->titleMode)) $this->{$this->titleMode}();
			else $this->{$this->mode}();
		}

		function html() {
?>
<h1 align=<?=$this->titleAlign?>><?=$this->title?></h1>
<?php
		}

		function flash() {
			global $coid, $winW, $winH, $baseDir;

			list($dx,$dy) = getFlashDimensions($this->titleFont,$this->title,$this->titleSize);
			$dx = $winW;  // full width
?>
<div align="<?=$this->titleAlign?>" class="sticky">
<embed src="<?=$baseDir?>flash.php/<?echo time()?>?type=title&dy=<?=$dy?>&dx=<?=$dx?>&coid=<?=$coid?>" quality=high loop=false 
pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"
type="application/x-shockwave-flash" width="<?=$dx?>" height="<?=$dy?>">
</embed>
</div>
<?php
		}

	}
	// }}}

	// {{{ Blurb Class
	class _blurb {

		function _blurb() {
			$this->font  = 'fonts/Arial.fdb';
			$this->align = 'left';
			$this->title      = "No Blurb Title";
			$this->titleSize  = 16;
			$this->titleColor = 'ff1122';
			$this->text       = "No Blurb Text";
			$this->textSize   = 14;
			$this->textColor  = '000000';
			$this->mode       = 'html';
		}

		function display() {
			$this->{$this->mode}();
		}

		function html() {
?>
<h1><?=$this->title?></h1>
<blockquote><p><?=$this->text?></p></blockquote>
<?php

		}
	}
	// }}}

	// {{{ Image Class
	class _image {
		function _image() {
			$this->filename = '';
			$this->mode = 'html';
			$this->align = 'left';
		}

		function display() {
			$this->{$this->mode}();
		}

		function html() {
			if(isset($this->title)) echo '<h1>'.$this->title."</h1>\n";
			$size = getimagesize($this->filename);
?>
<div align="<?=$this->align?>">
<img src="<?=$this->filename?>" <?=$size[3]?>>
</div>
<?php

		}
	}
	// }}}

	// {{{ Example Class
	class _example {
		function _example() {
			$this->filename = '';
			$this->type = 'php';
			$this->mode = 'html';
			$this->fontsize = '2em';
			$this->rfontsize = '1.8em';
		}

		function display() {
			$this->{$this->mode}();
		}

		function html() {
			if(isset($this->title)) echo '<h1>'.$this->title."</h1>\n";
			$sz = (float) $this->fontsize;
			if(!$sz) $sz = 0.1;
			$offset = (1/$sz).'em';
			echo '<div class="shadow"><div class="emcode" style="font-size: '.$sz."em; margin: -$offset 0 0 -$offset;\">\n";
			if(!empty($this->filename)) highlight_file($this->filename);
			else highlight_string($this->text);
			echo "</div></div>\n";
			if(!empty($this->result) && $this->result!='no') {
				echo "<h2>Output</h2>\n";
				$sz = (float) $this->rfontsize;
				if(!$sz) $sz = 0.1;
				$offset = (1/$sz).'em';
				echo '<div class="shadow"><div class="output" style="font-size: '.$sz."em; margin: -$offset 0 0 -$offset;\">\n";
				if(!empty($this->filename)) include $this->filename;
				else eval('?>'.$this->text);
				echo "</div></div>\n";
			}
		}
	}
	// }}}

	// {{{ List Class
	class _list {
		function _list() {
			$this->mode = 'html';
		}

		function display() {
			$this->{$this->mode}();
		}

		function html() {
			if(isset($this->title)) 
				echo '<h1>'.$this->title."</h1>\n";
			echo '<ul>';
			while(list($k,$bul)=each($this->bullets)) $bul->display();
			echo '</ul>';
		}
	}
	// }}}

	// {{{ Bullet Class
	class _bullet {

		function _bullet() {
			$this->mode = 'html';
			$this->text = '';
		}

		function display() {
			$this->{$this->mode}();
		}

		function html() {
			if(!empty($this->fontsize)) $style = "style=\"font-size: ".$this->fontsize.';"';
			echo '<li $style>'.$this->text."</li>\n";
		}

	}
	// }}}

?>
