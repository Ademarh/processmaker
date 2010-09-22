<?php
/**
 * class.dbtable.php
 *
 * ProcessMaker Open Source Edition
 * Copyright (C) 2004 - 2008 Colosa Inc.23
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * For more information, contact Colosa Inc, 2566 Le Jeune Rd.,
 * Coral Gables, FL, 33134, USA, or email info@colosa.com.
 *
 */
 
 /**
 * i18n_PO 
 * This class build bigger PO files without size limit and this not use much memory that the allowed
 * 
 * @package gulliver.system
 * @author Erik Amaru Ortiz <erik@colosa.com>
 * @date Aug 31th, 2010
 * @copyright (C) 2002 by Colosa Development Team.
 */
 
class i18n_PO
{
  private $_file   = NULL;
  private $_string = '';
  private $_meta;
  private $_fp;
  
  protected $_editingHeader;
  
  function __construct($file)
  {
    $this->_fp = fopen($file, 'w');
    
    if ( ! is_resource($this->_fp) ) {
      return FALSE;
    }
  }
  
  function buildInit()
  {
    // lock PO file exclusively
    if ( ! flock($this->_fp, LOCK_EX) ) {
      fclose($this->_fp);
      return FALSE;
    }
    
    $this->__init__();
  }
  
  function __init__()
  {
    $this->_meta = 'msgid ""';
    $this->_writeLine($this->_meta);
    $this->_meta = 'msgstr ""';
    $this->_writeLine($this->_meta);
    
    $this->_editingHeader = TRUE;
  }
  
  function addHeader($id, $value)
  {
    if( $this->_editingHeader ) {
      $meta = '"'.trim($id).': '.trim($value).'\n"';
      $this->_writeLine($meta);
    }
  }
  
  function addTranslatorComment($str)
  {
    $this->headerStroke();
    $comment = '# ' . trim($str);
    $this->_writeLine($comment);
  }
  
  function addExtractedComment($str)
  {
    $this->headerStroke();
    $comment = '#. ' . trim($str); 
    $this->_writeLine($comment);
  }
  
  function addReference($str)
  {
    $this->headerStroke();
    $reference = '#: ' . trim($str); 
    $this->_writeLine($reference);
  }
  
  function addFlag($str)
  {
    $this->headerStroke();
    $flag = '#, ' . trim($str); 
    $this->_writeLine($flag);
  }
  
  function addPreviousUntranslatedString($str)
  {
    $this->headerStroke();
    $str = '#| ' . trim($str); 
    $this->_writeLine($str);
  }
  
  function addTranslation($msgid, $msgstr)
  {
    $this->headerStroke();
    $this->_writeLine('msgid "'  . $this->prepare($msgid, true) . '"');
    $this->_writeLine('msgstr "' . $this->prepare($msgstr, true) . '"');
    $this->_writeLine('');
  }
  
  function _writeLine($str)
  {
    $this->_write($str . "\n");
  }
  
  function _write($str)
  {
    fwrite($this->_fp, $str);
  }
  
  function prepare($string, $reverse = false)
  {
    $string = str_replace('\"', '"', $string);
  
    if ($reverse) {
      $smap = array('"', "\n", "\t", "\r");
      $rmap = array('\"', '\\n"' . "\n" . '"', '\\t', '\\r');
      return (string) str_replace($smap, $rmap, $string);
    } else {
      $string = preg_replace('/"\s+"/', '', $string);
      $smap = array('\\n', '\\r', '\\t', '\"');
      $rmap = array("\n", "\r", "\t", '"');
      return (string) str_replace($smap, $rmap, $string);
    }
  }
  
  function headerStroke()
  {
    if( $this->_editingHeader ) {
      $this->_editingHeader = FALSE;
      $this->_writeLine('');;
    }
  }
  
  function __destruct() 
  {
    if ( $this->_fp )
      fclose($this->_fp);
  }
}