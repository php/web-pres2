#!/usr/bin/php -q 
<?
dl('gd.so');
 
require_once('XML/Tree.php');
require_once('Image/Transform.php');

if (@$_POST || @$_GET) {
    echo "No hacking please";
    exit;
}


$f=  $_SERVER['argv'][1];
 
$exec = "tidy -asxml $f";
$data = `$exec`;
/* for debugging - just show it ! */ 
 $tree = new XML_Tree;
   $tree->getTreeFromString($data);
   $tree->dump();

class SDX_Parser {
    var $outputDir = '';

    function start($data) {
        $this->transform($data);
        // make the subdirectories?
        
            
        
        $fh = fopen ('slides.xml','w');
        fwrite($fh,'<?xml version="1.0" encoding="ISO-8859-1"?>'."\n");
        fwrite($fh,"<presentation {$this->presentation}>\n");
        fwrite($fh,"<title>{$this->title}</title>\n");
        fwrite($fh,"<topic>{$this->topic}</topic>\n");
        fwrite($fh,"<event>{$this->event}</event>\n");
        fwrite($fh,"<location>{$this->location}</location>\n");
        fwrite($fh,"<date>{$this->date}</date>\n");
        fwrite($fh,"<speaker>{$this->speaker}</speaker>\n");
        fwrite($fh,"<company>{$this->company}</company>\n");
        fwrite($fh,"<email>{$this->email}</email>\n");
        fwrite($fh,"<url>{$this->url}</url>\n");
  
        foreach($this->files as $f) {
            fwrite($fh,"<slide>{$this->outputDir}/{$f}</slide>\n");
        }
        fwrite($fh,'</presentation>');
        fclose($fh);
        
    }
    
    function makeOutputDir() {
        if (file_exists($this->outputDir)) {
            return;
        }
        $parts= explode('/',$this->outputDir);
        $dir ='';
        foreach($parts as $dir) {
            $fulldir .= $dir;
            if (!file_exists($fulldir)) {
                mkdir($fulldir);
            }
            $fulldir .= '/';
        }
    }
    
    var $_caseFolding  = TRUE;
    function transform($xml) {
        // Don't process input when it contains no XML elements.

        if (strpos($xml, '<') === false) {
            return $xml;
        }

        // Create XML parser, set parser options.

        $parser = xml_parser_create();

        xml_set_object($parser, $this);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, $this->_caseFolding);

        // Register SAX callbacks.

        xml_set_element_handler($parser, '_startElement', '_endElement');
        xml_set_character_data_handler($parser, '_characterData');

        // Parse input.

        if (!xml_parse($parser, $xml, true)) {
            $line = xml_get_current_line_number($parser);

            echo sprintf(
              "Transformer: XML Error: %s at line %d:%d\n",
              xml_error_string(xml_get_error_code($parser)),
              $line,
              xml_get_current_column_number($parser)
            );

             

            return '';
        }

         
        // Clean up.

        xml_parser_free($parser);
        if ($this->fh) {
            fclose($this->fh);
        }
         
    }
    function _startElement($parser, $element, $attributes) {
        // Push element's name and attributes onto the stack.

        $this->_level++;
        $this->_elementStack[$this->_level]    = $element;
        $this->_attributesStack[$this->_level] = $attributes;
        $this->_cdataStack[$this->_level] = '';
        //echo "S:{$this->_level}:$element\n";
        if (method_exists($this, $element.'Start')) {
            call_user_func(array(&$this,$element.'Start'),$attributes);
        }
    }
    
    
    function _endElement($parser, $element) {
        $cdata     = $this->_cdataStack[$this->_level];
         echo "E:{$this->_level}:$element:$cdata\n";
        if (method_exists($this, $element.'End')) {
        
            $this->flushBlurb();
            call_user_func(array(&$this,$element.'End'),$cdata);
        }
        $this->_level--;
    }    

    function _characterData($parser, $cdata) {
        //echo "C:{$this->_elementStack[$this->_level]}:$cdata\n";
        if (!method_exists($this, $this->_elementStack[$this->_level] .'End') && (trim($cdata) != '')) {
            //echo "GOT CDATA for {$this->_elementStack[$this->_level]}";
            $this->blurb .= $cdata;
        }
        $this->_cdataStack[$this->_level] .= $cdata;
    }
    
    function bodyStart($a) {
        echo "--START--\n";

        //echo "GOT BODY! $a";
    }
    //function bodyEnd($a) {
    //       echo "--END--\n"; 
    //}
    var $blurb = '';
    function bodyCdata($a) {
        //echo "ADD BLURB $a\n";
        $this->blurb .= $a;
    }
    
    function flushBlurb() {
        if (trim($this->blurb) == '') {
            return;
        }
        //echo "FB: {$this->blurb}\n";
        $this->add("<blurb>{$this->blurb}</blurb>",TRUE);
        $this->blurb = '';
    }
    
    function hrStart($a) {
        //print_r($a); 
        $this->flushBlurb();
        $this->add('</slide>');

    }
    function hrEnd($a) {  }
    
    
    var $files = array();
    function h1End($a) {  
        if ($this->fh) {
            fclose($this->fh);
        }
        $filename= preg_replace('/[^a-z0-9]+/i','_',$a) . '.xml';
        $this->files[] = $filename;
        $this->makeOutputDir();
        $this->fh = fopen($this->outputDir.'/'.$filename,'w');
        
        $this->add('<?xml version="1.0" encoding="ISO-8859-1"?>');
        $this->add('<slide>');
        $this->add('<title>'.$a.'</title>',TRUE);
        $this->fontsize =0;
    }
    function h5End($a) {  // compiler settings
        $parts = explode("\n",$a);
        foreach($parts as $line) {
            $s  = strpos($line, ' ');
            $left = substr($line,0,$s);
            $right = substr($line,$s+1);
            if (!$this->$left) {
                $this->$left = '';
            } else {
                $this->$left .= ' ';
            }
            
            $this->$left .= $right;
        }
        
    }
    
    
    
    function h6End($a) { }  //comments
    function titleEnd($a) { }  //comments
     
    var $fontsize =0;
    function smallStart() {
        $this->fontsize--;
    }
     
    
    function preEnd($a) {
        $this->add('<example fontsize="1.2em"><![CDATA['.trim($a).']]></example>',TRUE);  
    } 
    var $inLI = FALSE;
    function liStart($a) {
         
        
    }
    function liEnd($a) {
        $this->add('<bullet>'.$a. '</bullet>', TRUE);
        
         
    }
    
    function ulStart($a) {
        
        $this->inLI = TRUE;
        $this->add('<list>');
    }
    function ulEnd($a) { 
       
        $this->inLI = FALSE;
        $this->add('</list>');
    }
    
   
    function imgStart($a){ 
        //print_r($a);
        // scale the image !
        $this->flushBlurb();
        if ($a['STYLE']) {
            //print_r($a);
            preg_match('/width: ([0-9]+)px; height: ([0-9]+)px/i', $a['STYLE'],$ar);
            print_r($ar);
            $it = Image_Transform::factory('GD');
            list($filename,$ext) = explode('.',$a['SRC']);
            $it->load( getenv('PWD'). '/'.$a['SRC'] );
            print_r($it);
            $it->scaleMaxX( $ar[1]); 
            $newfilename = $filename . '_'.$ar[1] .'.'. $ext;
            $it->save(getenv('PWD').'/'.$this->outputDir .'/'.$newfilename);
        } else {
            copy( getenv('PWD'). '/'.$a['SRC'], getenv('PWD').'/'.$this->outputDir .'/'.$a['SRC']);
            $newfilename = $a['SRC'];
        }
        
        
       
        $this->add(' <image align="center" scale="30%" filename="'. $newfilename.'" />',TRUE);
    }
     
    
    
    var $slideInfo = '';
    //function addressEnd($a) {
    //    $this->slideInfo .= $a . "\n";
    //}
    
    var $indent =0;
    function add($str, $noIndent=FALSE) {
        $ret = "\n";
        
        if (!$noIndent && ($str[0] == '<') && ($str[1] != '?')) {
            if ($str[1] == '/') {
                
                $this->indent -=2;
                fwrite($this->fh,str_repeat(' ', $this->indent).$str.$ret);
                return;
            } else {
                fwrite($this->fh,str_repeat(' ', $this->indent).$str.$ret);
                $this->indent +=2;
                
                return;
            }
        }
         
        fwrite($this->fh, str_repeat(' ', $this->indent).$str.$ret);
        
    }
    
}

 $sdxp = new SDX_Parser;
 $sdxp->start($data);

?>
