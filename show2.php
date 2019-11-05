<?php
require 'vendor/autoload.php';

class PresFormatter implements ezcTemplateCustomFunction
{
	public static function getCustomFunctionDefinition( $name )
	{
		switch ( $name )
		{
			case 'format_text':
			case 'has_attribute':
			case 'get_attribute':
			case 'replace_properties':
			case 'run_code':
				$def = new ezcTemplateCustomFunctionDefinition();
				$def->class = __CLASS__;
				$def->method = $name;
				return $def;
		}
	}

	static public function run_code( $code )
	{
		ob_start();
		try
		{
			eval( "?>$code" );
		}
		catch( Exception $e )
		{
			// ignore
		}

		$val = ob_get_contents();
		ob_clean();
		return $val;
	}

	static public function format_text( $pres, $text )
	{
		$ret = $text;
		#	$ret = preg_replace('/\*([\S ]+?)([^\\\])\*/','<strong>\1\2</strong>',$str);
		$ret = preg_replace('/#([[:alnum:]]+?)#/','&\1;',$ret);
		$ret = preg_replace('/\b_([\S ]+?)_\b/','<u>\1</u>',$ret);

		// blink
		$ret = str_replace('\*',chr(1),$ret);
		$ret = preg_replace('/\*\*([\S ]+?)\*\*/','<blink>\1</blink>',$ret);
		$ret = str_replace(chr(1),'\*',$ret);

		// bold
		$ret = str_replace('\*',chr(1),$ret);
		$ret = preg_replace('/\*([\S ]+?)\*/','<strong>\1</strong>',$ret);
		$ret = str_replace(chr(1),'\*',$ret);

		// strike
		$ret = str_replace('\-\-\-',chr(1),$ret);
		$ret = preg_replace('/\-\-\-([\S ]+?)\-\-\-/','<s>\1</s>',$ret);
		$ret = str_replace(chr(1),'\-\-\-',$ret);

		// italics
		$ret = str_replace('\~',chr(1),$ret);
		$ret = preg_replace('/~([\S ]+?)~/','<i>\1</i>',$ret);
		$ret = str_replace(chr(1),'\~',$ret);

		// monospace font
		$ret = str_replace('\%',chr(1),$ret);
		$ret = preg_replace('/%([\S ]+?)%/', '<tt>\1</tt>', $ret);
		$ret = str_replace(chr(1),'%',$ret);

		// Hack by arjen: allow more than one word to be coloured
		$ret = preg_replace('/\|([0-9a-fA-F]+?)\|([\S ]+?)\|/','<span style="color: #\1">\2</span>',$ret);
		$ret = preg_replace('/\^([[:alnum:]]+?)\^/','<sup>\1</sup>',$ret);
		$ret = preg_replace('/\@([[:alnum:]]+?)\@/','<sub>\1</sub>',$ret);
		// Quick hack by arjen: BR/ and TAB/ pseudotags from conversion
		$ret = preg_replace('/BR\//','<BR/>',$ret);
		$ret = preg_replace('/TAB\//',' ',$ret);

		$ret = preg_replace('/([\\\])([*#_|^@%])/', '\2', $ret);
		$ret = preg_replace_callback(
			'/:-:(.*?):-:/',
			function($matches) use ($pres) {
				return $pres->{$matches[1]}; // Careful!
			},
			$ret
		);
		return $ret;
	}

	static public function replace_properties( $pres, $value )
	{
		$value = preg_replace_callback(
			'/:-:(.*?):-:/',
			function($matches) use ($pres) {
				return $pres->{$matches[1]}; // Careful!
			},
			$value
		);
		return $value;
	}

	static public function get_attribute( $node, $attribute )
	{
		$attr_value = $node->getAttribute( $attribute );
		return PresFormatter::replace_properties( $GLOBALS['pres'], $attr_value );
	}

	static public function has_attribute( $node, $attribute )
	{
		$attr_value = $node->hasAttribute( $attribute );
		return $attr_value;
	}
}

class PresRst implements ezcTemplateCustomFunction
{
	public static function getCustomFunctionDefinition( $name )
	{
		switch ( $name )
		{
			case 'title':
				$def = new ezcTemplateCustomFunctionDefinition();
				$def->class = __CLASS__;
				$def->method = $name;
				return $def;
		}
	}

	public function title( $text, $marker )
	{
		return $text . "\n" . str_repeat( $marker, strlen( $text ) ) . "\n\n";
	}
}

$tc = ezcTemplateConfiguration::getInstance();
$tc->addExtension( 'PresFormatter' );
$tc->addExtension( 'PresRst' );

$base = $_SERVER['DOCUMENT_ROOT'] . '/presentations/';
@list( $dummy, $prest, $slideNr ) = explode( '/', $_SERVER['PATH_INFO'] );
if ( $slideNr === null || $slideNr === '' )
{
	$slideNr = 0;
}

$pres = new Presentation( $prest );
if ( $slideNr === 'pdf' )
{
	header( 'Content-type: text/plain' );
	$pres->renderToRst( "/tmp/{$prest}.rst" );
}
else
{
	echo $pres->display( $slideNr );
}


class Presentation
{
	private $properties;
	public $slideFiles;
	public $base;
	public $presName;

	function __get( $name )
	{
		return $this->properties[$name];
	}

	function __construct( $pres )
	{
		$this->presName = $pres;
		$presFile = $GLOBALS['base'] . $pres . '.xml';
		$xml = simplexml_load_file( $presFile );

		foreach (
			array(
				'title', 'event', 'location', 'date', 'speaker',
				'email', 'url', 'joindin', 'twitter', 'lat', 'lon',
			) as $prop )
		{
			$this->properties[$prop] = (string) $xml->$prop;
		}
		$this->properties['talk_id'] = $pres;

		$this->slideFiles = array();
		foreach ( $xml->slide as $slide )
		{
			$this->slideFiles[] = (string) $slide;
		}

		// template configuration
		$templateDir = 'default';
		if ( isset( $xml['templatePath'] ) )
		{
			$templateDir = (string) $xml['templateDir'];
		}
		$this->css = array( "core.css" );
		if ( isset( $xml['css'] ) )
		{
			$this->css[] = $xml['css'];
		}

		$tc = ezcTemplateConfiguration::getInstance();
		$tc->templatePath = $GLOBALS['base'] . 'templates/' . $templateDir;
		$tc->compilePath = '/tmp/template-cache';
	}

	function display( $slideNr )
	{
		$xml = new DomDocument;
		$xml->load( $GLOBALS['base'] . $this->slideFiles[$slideNr] );
		$parts = explode( '/', $this->slideFiles[$slideNr] );

		$this->base = '/presentations/slides/' . join( '', array_slice( $parts, -2, 1 ) ) . '/';
		
		$tpl = new ezcTemplate();
		$tpl->send->node = $xml->documentElement;
		$tpl->send->pres = $this;
		$tpl->send->pres->forPdf = isset( $_GET['pdf'] );
		$tpl->send->pres->pdfIndex = isset( $_GET['pdf'] ) ? (int) $_GET['pdf'] : 0;
		$tpl->send->slideNr = $slideNr;
		$tpl->send->css = $this->css;

		echo $tpl->process( 'slide.ezt' );
	}

	function renderToRst( $filename )
	{
		$tpl = new ezcTemplate();
		$tpl->send->pres = $this;
		echo $tpl->process( 'rst/presentation.ezt' );
	}
}
