<?php
// {{{ header
// vim: set tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Rasmus Lerdorf <rasmus@php.net>                             |
// +----------------------------------------------------------------------+
//
// $Id$
// }}}

require_once 'XML/Parser.php';
require_once 'objects.php';

/**
 * Presentation parser class.
 *
 * This class is a parser for the .pres files
 *
 * @author Rasmus Lerdorf <rasmus@php.net>
 * @version $Revision$
 * @access  public
 */
class XML_Presentation extends XML_Parser
{

    // {{{ properties

    /**
     * @var string
     */
    var $insideTag = '';

    /**
     * @var string
     */
    var $activeTag = '';

    /**
     * @var string
     */
    var $activeSection = '';

    /**
     * @var string
     */
    var $activeChapter = '';

    /**
     * @var array
     */
    var $objects = array();

    /**
     * @var int
     * Current Object Index
     */
    var $coid = 0;

    var $last_handler;

    var $stack = array();

    // }}}
    // {{{ Constructor

    /**
     * Constructor
     *
     * @access public
     * @param mixed File pointer or name of the slide file.
     * @return void
     */
    function __construct($handle = '')
    {
        parent::__construct('UTF-8');
        if (@is_resource($handle)) {
            $this->setInput($handle);
        } elseif ($handle != '') {
            $this->setInputFile($handle);
        } else {
            $this->raiseError('No filename passed.');
        }
    }

    // }}}
    // {{{ startHandler()

    /**
     * Start element handler for XML parser
     *
     * @access private
     * @param  object XML parser object
     * @param  string XML element
     * @param  array  Attributes of XML tag
     * @return void
     */
    function startHandler($parser, $element, &$attribs)
    {
        switch ($element) {
            /* These tags can have other tags inside */
            case 'PRESENTATION':
                $cl = '_'.strtolower($element);
                $this->objects[++$this->coid] = new $cl();
                $this->stack[] = $this->coid;
                $this->insideTag = $element;
                $this->_add_attribs($this->objects[$this->coid], $attribs);
                break;

            /* Special case for array properties */
            case 'SLIDE':
                $this->objects[$this->coid]->slides[] = new _pres_slide();
                $idx = count($this->objects[$this->coid]->slides)-1;
                $this->_add_attribs($this->objects[$this->coid]->slides[$idx], $attribs);
                $this->activeTag = $element;
                if(!empty($this->activeSection)) {
                    $this->objects[$this->coid]->slides[$idx]->Section = $this->activeSection;
                }
                if(!empty($this->activeChapter)) {
                    $this->objects[$this->coid]->slides[$idx]->Chapter = $this->activeChapter;
                }
                break; 

            /* Everything else can't */
            case 'SECTION':
                if(!empty($attribs['TITLE'])) {
                    $this->activeSection = $attribs['TITLE'];
                }
                break;

            case 'CHAPTER':
                if(!empty($attribs['TITLE'])) {
                    $this->activeChapter = $attribs['TITLE'];
                }
                break;

            default:
                $this->activeTag = $element;
                $this->_add_attribs($this->objects[$this->coid], $attribs, strtolower($element));
                break;
        }
        $this->last_handler = 'start';
    }

    // }}}
    // {{{ endHandler()

    /**
     * End element handler for XML parser
     *
     * @access private
     * @param  object XML parser object
     * @param  string
     * @return void
     */
    function endHandler($parser, $element)
    {
        if ($element == $this->insideTag) {
            $this->insideTag = '';
        }

        switch ($element) {
            case 'PRESENTATION':
                $this->coid = array_pop($this->stack);
                break;
			case 'SECTION':
				$this->activeSection = '';
				break;
			case 'CHAPTER':
				$this->activeChapter = '';
				break;
        }
        $this->activeTag = '';
        $this->last_handler = 'end';
    }

    // }}}
    // {{{ cdataHandler()

    /**
     * Handler for character data
     *
     * @access private
     * @param  object XML parser object
     * @param  string CDATA
     * @return void
     */
    function cdataHandler($parser, $cdata)
    {
        if(empty($this->activeTag)) return;

        $el = strtolower($this->activeTag);
        if($el == 'slide') {
            $idx = count($this->objects[$this->coid]->slides) - 1;
            if($this->last_handler == 'cdata')
                $this->objects[$this->coid]->slides[$idx]->filename .= $cdata;
            else 
                $this->objects[$this->coid]->slides[$idx]->filename = $cdata;
        } else {
            if($this->last_handler == 'cdata') {
                $this->objects[$this->coid]->$el .= $cdata;
            } else {
                $this->objects[$this->coid]->$el = $cdata;
            }
        }

        $this->last_handler = 'cdata';
    }

    // }}}
    // {{{ _add_attribs
    function _add_attribs(&$object, $attribs, $prefix='') {
        foreach($attribs as $attr=>$value) {
            $a = empty($prefix) ? strtolower($attr) : $prefix.ucfirst(strtolower($attr));
            $object->$a = $value;
        }
    }
    // }}}
    // {{{ getObjects()

    /**
     * Get Presentation Objects
     *
     * @access public
     * @return array
     */
    function getObjects()
    {
        return $this->objects;
    }

    // }}}

}
?>
