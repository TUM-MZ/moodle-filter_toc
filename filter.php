<?php
// This file is part of a module for Moodle, written by Nigel Cunningham
// for the Melbourne School of Theology and now maintained by Nigel.
//
// It is has been made freely available with permission from MST.
//
// This module is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// TODO List:
// - Handle quoting
// - Configuration page
//    - Heading level range to use
//    - Title text to use
//    - Backlink text to use

/**
 * This filter provides an automatically generated
 * table of contents, based on heading tags in a page.
 *
 * @package    filter
 * @subpackage toc
 * @copyright  2012 onwards Melbourne School of Theology and Nigel Cunningham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_toc extends moodle_text_filter {

    private $headings_filter = "//h1 | //h2 | //h3 | //h4";

    private $headings_found = array();
    
    private $last_level = 0;
    
    private $toc_text = '';

    private function is_within_div($heading_node)
    {
	    $parent = $heading_node;
	    while ($parent->tagName !== "html") {
	      if ($parent->tagName == "div")
	        return true;
	      $parent = $parent->parentNode;
	    }
	    return false;
    }
    
    private function add_to_heading_list($domDocument, $heading_instance)
    {
      $heading_level = intval(substr($heading_instance->tagName, 1, 1));
      
      // Add anchor and back-link to the heading.
      $old_contents = $domDocument->saveXML($heading_instance);
      $heading_text = '';
      foreach ($heading_instance->childNodes as $child) {
        if ($child->nodeType == 3 || in_array($child->tagName, array("b","i", "strong", "em")))
          $heading_text .= $child->nodeValue;
      }
      
      $to_add = '<a name="[ID]" id="[ID]"></a><a href="#toc"><span class="toc_link" style="color: #ff6600;"> '.get_string('toc_top', 'filter_toc').'</span></a>';
      
      $link_name = str_replace(" ", "_", $heading_text);
      foreach (array("&amp;", "!", "#", "(", ")", ".", ":", ";", "-", "\"", "'") as $to_remove)
        $link_name = str_replace($to_remove, "", $link_name);
      
      $to_add = str_replace("[ID]", $link_name, $to_add);

      $fragment = $domDocument->createDocumentFragment();
      $fragment->appendXML(substr($old_contents, 0, strlen($old_contents) - 5) ."{$to_add}</h{$heading_level}>");
      $heading_instance->parentNode->replaceChild($fragment, $heading_instance);

      if($heading_level==1) {
        $heading_text = "<h2>".$heading_text."</h2>";
      }
      
      // Add this tag to the table of contents text
      $this->adjust_tag_level($heading_level);    
      $this->toc_text .= "<li><a href='#" . $link_name . "'>" . $heading_text . "</a></li>\n";
    }

    private function adjust_tag_level($this_level)
    {
      if ($this_level < $this->last_level) {
        for ($i = $this->last_level; $i > $this_level; $i--)
          $this->toc_text .= "</ul>";
      } else if ($this_level > $this->last_level) {
        for ($i = $this->last_level; $i < $this_level; $i++)
          $this->toc_text .= "<ul>";
      }
      $this->last_level = $this_level;
    }


    public function filter($text, array $options = array()) {
      global $PAGE;
      if ($PAGE->pagelayout <> "incourse")
        return $text;

      $next_tag_pos = 0;
      $toc_text = "";
      $last_level = 0;
      $num_entries = 0;

      libxml_use_internal_errors(true);

      $dom = new DOMDocument("1.0", "UTF-8");
      $dom->strictErrorChecking = false;
      $dom->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', "UTF-8"));

      // Completed remove the div.
	  $finder = new DomXPath($dom);
	  $instances = $finder->query($this->headings_filter);
	  if ($instances) {
	    foreach ($instances as $heading_instance) {
		  // Is it within a div?
		  if (!$this->is_within_div($heading_instance)) {
		    $this->add_to_heading_list($dom, $heading_instance);
  		    $num_entries++;
		  }
	  	}
      }

      if ($num_entries < 2)
        return $text;
      
      $text = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $dom->saveHTML()));

      # Converting $text to Doc and back can introduce some rubbish that confused Firefox during testing
      $text = str_replace("<strong/>", "", $text);
      $text = str_replace("&#13;", "", $text);
      
      $this->adjust_tag_level(0);
      $this->toc_text = '<div class="toc"><a name="toc" id="toc" /><h1>'.get_string('toc_index', 'filter_toc').'</h1>' . $this->toc_text .'</div>';
      $insert_at = stripos($text, "[contents]");

      if ($insert_at) {
        return substr($text, 0, $insert_at) . $this->toc_text . substr($text, $insert_at + 10);
      } else {
        return $this->toc_text. $text;
      }
    }
}
