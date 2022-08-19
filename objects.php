<?php
// vim: set tabstop=4 shiftwidth=4 fdm=marker:

require_once 'display.php';

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


function format_tt($arg) {
  return("<tt>".str_replace(' ', '&nbsp;', $arg[1])."</tt>");
}

/* {{{ string markup_text($str)
	*word*		Bold
	_word_		underline
	%word%		monospaced word (ie. %function()%)
	~word~		italics
	|rrggbb|word| Colour a word
	^N^		   Superscript
	@N@		   Subscript
	**word**	  Blink
	#id#		  Entity
*/
function markup_text($str) {
	global $p;
	$pres = $p->objects[1];

	$ret = $str;
#	$ret = preg_replace('/\*([\S ]+?)([^\\\])\*/','<strong>\1\2</strong>',$str);
	$ret = preg_replace('/#([[:alnum:]]+?)#/','&\1;',$ret);
	$ret = preg_replace('/\b_([^_][\S ]+?)_\b/','<u>\1</u>',$ret);

	// blink
	$ret = str_replace('\*',chr(1),$ret);
	$ret = preg_replace('/\*\*([\S ]+?)\*\*/','<blink>\1</blink>',$ret);
	$ret = str_replace(chr(1),'\*',$ret);

	// bold
	$ret = str_replace('\*',chr(1),$ret);
	$ret = preg_replace('/\*([\S ]+?)\*/','<strong>\1</strong>',$ret);
	$ret = str_replace(chr(1),'\*',$ret);

	// italics
	$ret = str_replace('\~',chr(1),$ret);
	$ret = preg_replace('/~([\S ]+?)~/','<i>\1</i>',$ret);
	$ret = str_replace(chr(1),'\~',$ret);

	// monospace font
	$ret = str_replace('\%',chr(1),$ret);
	$ret = preg_replace_callback('/%([\S ]+?)%/', 'format_tt', $ret);
	$ret = str_replace(chr(1),'%',$ret);

	// Hack by arjen: allow more than one word to be coloured
	$ret = preg_replace('/\|([0-9a-fA-F]+?)\|([\S ]+?)\|/','<font color="\1">\2</font>',$ret);
	$ret = preg_replace('/\^([[:alnum:]]+?)\^/','<sup>\1</sup>',$ret);
	$ret = preg_replace('/\@([[:alnum:]]+?)\@/','<sub>\1</sub>',$ret);
	// Quick hack by arjen: BR/ and TAB/ pseudotags from conversion
	$ret = preg_replace('/BR\//','<BR/>',$ret);
	$ret = preg_replace('/TAB\//',' ',$ret);

	$ret = preg_replace('/([\\\])([*#_|^@%])/', '\2', $ret);
	$ret = preg_replace_callback('/:-:(.*?):-:/', function ($matches) use ($pres) {
            return empty($pres->{$matches[1]}) ? '' : $pres->{$matches[1]};
        }, $ret);
	return $ret;
}
// }}}

function add_line_numbers($text)
{
	$lines = preg_split ('!$\n!m', $text);
	$lnwidth = strlen(count($lines));
	$format = '%'.$lnwidth."d: %s\n";
	$lined_text = '';
	while (list ($num, $line) = each ($lines)) {
			$lined_text .= sprintf($format, $num + 1, $line);
	}
	return $lined_text;
}


// {{{ strip_markups
function strip_markups($str) {

	$ret = str_replace('\*',chr(1),$str);
	$ret = preg_replace('/\*([\S ]+?)\*/','\1',$ret);
	$ret = str_replace(chr(1),'\*',$ret);

	$ret = preg_replace('/\b_([\S ]+?)_\b/','\1',$ret);
	$ret = str_replace('\%',chr(1),$ret);
	$ret = preg_replace('/%([\S ]+?)%/','\1',$ret);
	$ret = str_replace(chr(1),'\%',$ret);

	$ret = preg_replace('/~([\S ]+?)~/','\1',$ret);
	// Hack by arjen: allow more than one word to be coloured
	$ret = preg_replace('/\|([0-9a-fA-F]+?)\|([\S ]+?)\|/','\2',$ret);
	$ret = preg_replace('/\^([[:alnum:]]+?)\^/','^\1',$ret);
	$ret = preg_replace('/\@([[:alnum:]]+?)\@/','_\1',$ret);
	$ret = preg_replace('/~([\S ]+?)~/','<i>\1</i>',$ret);
	// Quick hack by arjen: BR/ and TAB/ pseudotags from conversion
	$ret = preg_replace('/BR\//','<BR/>',$ret);
	$ret = preg_replace('/TAB\//','',$ret);
	$ret = preg_replace('/([\\\])([*#_|^@%])/', '\2', $ret);
	return $ret;
} 
// }}}

// }}}

	// {{{ Presentation List Classes
	class _tag {
		function display() {
			global $mode;
			
			$class = get_class($this);
			$mode->$class($this);
		}
	}
	
	class _presentation extends _tag {
		function __construct() {
			global $baseFontSize, $jsKeyboard, $baseDir;

			$this->title = 'No Title Text for this presentation yet';
			$this->navmode  = 'html';
			$this->mode  = 'html';
			$this->navsize=NULL; // nav bar font size
			$this->template = 'php';
			$this->jskeyboard = $jsKeyboard;
			$this->logo1 = 'images/php_logo.gif';
			$this->logo2 = NULL;
			$this->basefontsize = $baseFontSize;
			$this->backgroundcol = false;
			$this->backgroundfixed = false;
			$this->backgroundimage = false;
			$this->backgroundrepeat = false;
			$this->navbarbackground = 'url(images/trans.png) transparent fixed';
			$this->navbartopiclinks = true;
			$this->navbarheight = '6em';
			$this->examplebackground = '#dcdcdc';
			$this->outputbackground = '#eeee33';
			$this->shadowbackground = '#777777';
			$this->stylesheet = 'css.php';
			$this->logoimage1url = 'http://' . $_SERVER['HTTP_HOST'] . $baseDir;
			$this->animate=false;
		}
	}

	class _pres_slide extends _tag {
		function __construct() {
			$this->filename = '';
		}
	}
	// }}}

	// {{{ Slide Class
	class _slide extends _tag {

		function __construct() {
			$this->title = 'No Title Text for this slide yet';
			$this->subtitle = '';
			$this->titleSize  = "3em";
			$this->titleColor = '#ffffff';
			$this->navcolor = '#EFEF52';
			$this->navsize  = "2em";
			$this->titleAlign = 'center';
			$this->titleFont  = 'fonts/Verdana.fdb';
			$this->template   = 'php';
			$this->layout = '';
		}

	}
	// }}}

	// {{{ Blurb Class
	class _blurb extends _tag {

		function __construct() {
			$this->font  = 'fonts/Verdana.fdb';
			$this->align = 'left';
			$this->talign = 'left';
			$this->fontsize	 = '2.66em';
			$this->marginleft   = '1em';
			$this->marginright  = '1em';
			$this->margintop	= '0.2em';	
			$this->marginbottom = '0em';	
			$this->title		= '';
			$this->titlecolor   = '#000000';
			$this->text		 = '';
			$this->textcolor	= '#000000';
			$this->effect	   = '';
			$this->type		 = '';
		}

	}
	// }}}

	// {{{ Image Class
	class _image extends _tag {
		function __construct() {
			$this->filename = '';
			$this->align = 'left';
			$this->talign = 'left';
			$this->marginleft = "auto";
			$this->marginright = "auto";
			$this->effect = '';
			$this->width = '';
			$this->height = '';
		}
	}
	// }}}

	// {{{ Example Class
	class _example extends _tag {
		function __construct() {
			$this->filename = '';
			$this->type = 'php';
			$this->fontsize = '2em';
			$this->rfontsize = '1.8em';
			$this->marginright = '3em';
			$this->marginleft = '3em';
			$this->margintop = '1em';
			$this->marginbottom = '0.8em';
			$this->hide = false;
			$this->result = false;
			$this->width = '';
			$this->condition = '';
			$this->linktext = "Result";
			$this->iwidth = '100%';
			$this->iheight = '80%';
			$this->localhost = false;
			$this->effect = '';
			$this->linenumbers = false;
		}

		function _highlight_none($fn) {
			$data = file_get_contents($fn);
			echo '<pre>' . htmlspecialchars($data) . "</pre>\n";
		}
	
		// {{{ highlight()	
		function highlight($slideDir) {
			static $temap = array(
				'py' => 'python',
				'pl' => 'perl',
				'php' => 'php',
				'inc' => 'php',
				'html' => 'html',
				'sql' => 'sql',
				'java' => 'java',
				'xml' => 'xml',
				'js' => 'javascript',
				'c' => 'c'
			);

			if(!empty($this->filename)) {
				$_html_filename = preg_replace('/\?.*$/','',$slideDir.$this->filename);
				if ($this->type == 'php') {
					$p = pathinfo($this->filename);
					$this->type = @$temap[$p['extension']];
				}
				switch($this->type) {
					case 'php':
					case 'genimage':
					case 'iframe':
					case 'link':
					case 'nlink':
					case 'embed':
					case 'flash':
					case 'system':
						if ($this->linenumbers) {
							ob_start();
							highlight_file($_html_filename);
							$contents = ob_get_contents();
							ob_end_clean();
							echo add_line_numbers($contents);
						} else {
							highlight_file($_html_filename);
						}
						break;
					case 'c':
						$prog = trim(`which c2html`);
						if (!empty($prog)) {
							print `cat {$_html_filename} | $prog -cs`;
						} else {
							$this->_highlight_none($_html_filename);
						}
						break;
					case 'perl':
						$prog = trim(`which perl2html`);
						if (!empty($prog)) {
							print `cat {$_html_filename} | $prog -cs`;
						} else {
							$this->_highlight_none($_html_filename);
						}
						break;
					case 'java':
						$prog = trim(`which java2html`);
						if (!empty($prog)) {
							print `cat {$_html_filename} | java2html -cs`;
						} else {
							$this->_highlight_none($_html_filename);
						}
						break;
					case 'python':
						$prog = trim(`which code2html`);
						if (!empty($prog)) {
							print nl2br(trim(`$prog -lpython --no-header -ohtml $_html_filename | sed -e 's/\t/\&nbsp\;\&nbsp;\&nbsp\; /g'`));
						} else {
							$this->_highlight_none($_html_filename);
						}
						break;
					case 'javascript':
						$prog = trim(`which code2html`);
						if (!empty($prog)) {
							print nl2br(trim(`$prog -ljavascript -ohtml-light --no-header $_html_filename | sed -e 's/  /\&nbsp\;\&nbsp; /g'`));
						} else {
							$this->_highlight_none($_html_filename);
						}
						break;
					case 'sql':
						$prog = trim(`which code2html`);
						if (!empty($prog)) {
							print "<pre>";
							print `$prog --no-header -lsql $_html_filename`;
							print "</pre>";
						} else {
							$this->_highlight_none($_html_filename);
						}
						break;
					case 'xml':
						$prog = trim(`which code2html`);
						if (!empty($prog)) {
							print "<pre>";
							print `$prog --no-header -lhtml $_html_filename`;
							print "</pre>";
						} else {
							$this->_highlight_none($_html_filename);
						}
						break;
					case 'html':
						$_html_file = file_get_contents($_html_filename);
						echo $_html_file."\n";
						break;
					
					case 'shell':
					default:
						$this->_highlight_none($_html_filename);
						break;
				}
			} else {
				switch($this->type) {
                    case 'marked':
                        echo "<pre>";
                        echo preg_replace('/^\|(.*)$/m','<font color="#802000">\1</font>',htmlspecialchars($this->text));
                        echo "</pre>";
                        break;

					case 'php':
						if ($this->linenumbers) {
							$text = add_line_numbers($this->text);
							highlight_string($text);
						} else {
							highlight_string($this->text);
						}
						break;

					case 'shell':
						echo '<pre>'.markup_text(htmlspecialchars($this->text))."</pre>\n";
						break;
					case 'html':
						echo $this->text."\n";
						break;
					case 'perl':
						$text = str_replace('"', '\\"', $this->text);
						print `echo "{$text}" | perl2html -cs`;
						break;
					case 'c':
						$text = str_replace('"', '\'', $this->text);
						$text = str_replace('\\n', '', $text);
						print `echo "{$text}" | c2html -cs`;
						break;
					case 'xml':
						$prog = trim(`which code2html`);
						if (!empty($prog)) {
							$text = str_replace('"', '\\"', $this->text);
							echo "<pre>\n";
							print `echo "{$text}" | code2html -lhtml --no-header`;
							echo "</pre>";
						} else {
							echo "<pre>".htmlspecialchars($this->text)."</pre>\n";
						}
						break;
					case 'javascript':
						$prog = trim(`which code2html`);
						if (!empty($prog)) {
							$text = str_replace('"', '\\"', $this->text);
							echo "<pre>\n";
							print `echo "{$text}" | $prog -ljavascript -ohtml-light --no-header | sed -e 's/  /\&nbsp\;\&nbsp; /g'`;
							echo "</pre>";
						} else {
							$this->_highlight_none($_html_filename);
						}
						break;

					default:
						echo "<pre>".htmlspecialchars($this->text)."</pre>\n";
						break;
				}
			}
		}
		// }}}

	}
	// }}}

	// {{{ Break Class
	class _break extends _tag {
		function __construct() {
			$this->lines = 1;
		}

	}
	// }}}

	// {{{ Div Class
	class _div extends _tag {
		function __construct() {
			$this->effect = '';
		}
	}

	class _div_end extends _tag {
		/* empty */
	}
	// }}}

	// {{{ List Class
	class _list extends _tag {
		function __construct() {
			$this->fontsize	= '3em';
			$this->marginleft  = '0em';
			$this->marginright = '0em';
			$this->num = 1;
			$this->alpha = 'a';
		}

	}
	// }}}

	// {{{ Bullet Class
	class _bullet extends _tag {

		function __construct() {
			$this->text = '';
			$this->effect = '';
			$this->id = '';
			$this->type = '';
		}

	}
	// }}}

	// {{{ Table Class
	class _table extends _tag {
		function __construct() {
			$this->fontsize	= '3em';
			$this->marginleft  = '0em';
			$this->marginright = '0em';
			$this->border = 0;
			$this->columns = 2;
			$this->bgcolor = null;
		}

	}
	// }}}

	// {{{ Cell Class
	class _cell extends _tag {

		function __construct() {
			$this->text = '';
			$this->slide = '';
			$this->id = '';
			$this->end_row = false;
			$this->offset = 0;
		}

	}
	// }}}

	// {{{ Link Class
	class _link extends _tag {

		function __construct() {
			$this->href  = '';
			$this->align = 'left';
			$this->fontsize	 = '2em';
			$this->textcolor	= '#000000';
			$this->marginleft   = '0em';
			$this->marginright  = '0em';
			$this->margintop	= '0em';	
			$this->marginbottom = '0em';	
		}

	}
	// }}}

	// {{{ PHP Eval Class
	class _php extends _tag {

		function __construct() {
			$this->filename = '';
		}

	}
	// }}}

	// {{{ Divider Class
	class _divide extends _tag {
		/* empty */
	}
	// }}}

	// {{{ Footer Class
	class _footer extends _tag {
		/* empty */
	}
	// }}}

	// {{{ Movie Class
	class _movie extends _tag {

		function __construct() {
			$this->filename = '';
			$this->autoplay = 'true';
			$this->width = 800;
			$this->height = 600;
            $this->marginleft = "auto";
            $this->marginright = "auto";
		}

	}
	// }}}
?>
