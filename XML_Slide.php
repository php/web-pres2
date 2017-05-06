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
 * Slide parser class.
 *
 * This class is a parser for a made up presentation slide format
 *
 * @author Rasmus Lerdorf <rasmus@php.net>
 * @version $Revision$
 * @access  public
 */
class XML_Slide extends XML_Parser
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
     * @var array
     */
    var $objects = array();

    /**
     * @var int
     * Current Object Index
     */
    var $coid = 0;

	var $level = 0;

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
            case 'SLIDE':
            case 'BLURB':
            case 'IMAGE':
            case 'MOVIE':
            case 'LIST':
			case 'BREAK':
            case 'EXAMPLE':
            case 'LINK':
            case 'PHP':
			case 'TABLE':
                $cl = '_'.strtolower($element);
                $this->objects[++$this->coid] = new $cl();
                $this->stack[] = $this->coid;
                $this->insideTag = $element;
                $this->_add_attribs($this->objects[$this->coid], $attribs);
                $this->activeTag = 'text';
                break;

            case 'DIV':
                $cl = '_'.strtolower($element);
                $this->objects[++$this->coid] = new $cl();
                $this->stack[] = $this->coid;
                $this->insideTag = $element;
                $this->_add_attribs($this->objects[$this->coid], $attribs);
                $this->activeTag = 'text';
                break;

            /* Divider for indicating where to switch areas for layouts */
            case 'DIVIDE':
                $cl = '_'.strtolower($element);
                $this->objects[++$this->coid] = new $cl();
                $this->stack[] = $this->coid;
                $this->insideTag = $element;
                $this->_add_attribs($this->objects[$this->coid], $attribs);
                $this->activeTag = 'text';
                break;

            /* Special case for array properties */
            case 'BULLET':
			case 'ITEM':
			case 'NUM':
			case 'LI':
                $this->objects[$this->coid]->bullets[] = new _bullet();
                $idx = count($this->objects[$this->coid]->bullets) - 1;
                $this->_add_attribs($this->objects[$this->coid]->bullets[$idx], $attribs);
				$this->objects[$this->coid]->bullets[$idx]->level = $this->level;
				if($element=='NUM') {
					$this->objects[$this->coid]->bullets[$idx]->type = 'number';
				}
                $this->activeTag = 'BULLET'; 
				$this->level++;
                break; 
			case 'CELL':
                $this->objects[$this->coid]->cells[] = new _cell();
                $idx = count($this->objects[$this->coid]->cells) - 1;
                $this->_add_attribs($this->objects[$this->coid]->cells[$idx], $attribs);
                $this->activeTag = $element;
                break; 

            /* Everything else can't */
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
            case 'SLIDE':
                $this->objects[] = new _footer();
                /* fall-through */
            case 'BLURB':
            case 'IMAGE':
            case 'LIST':
            case 'EXAMPLE':
            case 'LINK':
            case 'PHP':
            case 'DIVIDE':
                $this->coid = array_pop($this->stack);
                break;
            case 'DIV':
                $this->objects[++$this->coid] = new _div_end();
                $this->coid = array_pop($this->stack);
                break;
			case 'BULLET':
			case 'ITEM':
			case 'NUM':
			case 'LI':
				$this->level--;
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
        if($el == 'bullet') {
            $idx = count($this->objects[$this->coid]->bullets) - 1;
            if($this->last_handler == 'cdata')
                $this->objects[$this->coid]->bullets[$idx]->text .= $cdata;
            else 
                $this->objects[$this->coid]->bullets[$idx]->text = $cdata;
		} elseif($el == 'cell') {
            $idx = count($this->objects[$this->coid]->cells) - 1;
            if($this->last_handler == 'cdata')
                $this->objects[$this->coid]->cells[$idx]->text .= $cdata;
            else 
                $this->objects[$this->coid]->cells[$idx]->text = $cdata;
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
     * Get Slide Objects
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
