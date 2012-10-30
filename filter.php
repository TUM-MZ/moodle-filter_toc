<?php
// This file is part of a module for Moodle, written by Nigel Cunningham
// of the Melbourne School of Theology.
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
 * @copyright  2012 onwards Melbourne School of Theology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_toc extends moodle_text_filter {

    private function get_next_tag_pos($text, $tags, $from)
    {
      $cur_pos = FALSE;
 
      foreach ($tags as $tag) {
        // Get the location of the next tag of this type.
        $this_pos = stripos($text, "<" . $tag, $from);

        // And the location of the first of any tags.
        if ($this_pos !== FALSE && ($cur_pos === FALSE || $this_pos < $cur_pos))
          $cur_pos = $this_pos;
      }

      return $cur_pos;
    }

    private function close_tags(&$toc_text, $last_level, $this_level)
    {
      for ($i = $last_level; $i > $this_level; $i--)
        $toc_text .= "</ul>";
    }

    private function open_tags(&$toc_text, $last_level, $this_level)
    {
      for ($i = $last_level; $i < $this_level; $i++)
        $toc_text .= "<ul>";
    }

    public function filter($text, array $options = array()) {
      global $PAGE;
      if ($PAGE->pagelayout <> "incourse")
        return $text;

      $heading_tags = array('h1', 'h2', 'h3', 'h4');
      $next_tag_pos = 0;
      $toc_text = "";
      $last_level = 0;

      $next_tag_pos = $this->get_next_tag_pos($text, $heading_tags, 0);

      while ($next_tag_pos !== FALSE) {
        $heading_level = intval(substr($text, $next_tag_pos + 2, 1));
        
        // Add anchor and back-link to the heading.
        $to_add = '<a name="[ID]" id="[ID]"></a><a href="#toc"><span class="toc_link" style="color: #ff6600;">&nbsp;&nbsp;(top)</span></a>';

        $close_start = stripos($text, "</" . substr($text, $next_tag_pos + 1, 2), $next_tag_pos);

        $open_end = strpos($text, ">", $next_tag_pos);

        $contained = substr($text, $open_end + 1, $close_start - $open_end - 1);
        $link_name = str_replace(" ", "_", $contained);
        foreach (array("&amp;", "!", "#", "(", ")", ".", ":", ";", "-") as $to_remove)
          $link_name = str_replace($to_remove, "", $link_name);

        $to_add = str_replace("[ID]", $link_name, $to_add);

        $text = substr($text, 0, $close_start) . $to_add . substr($text, $close_start);

        $next_tag_pos += strlen($to_add);
        
        // Add this tag to the table of contents text
        if ($heading_level > $last_level) {
          $this->open_tags($toc_text, $last_level, $heading_level);
        } else if ($heading_level < $last_level) {
          $this->close_tags($toc_text, $last_level, $heading_level);
        }
        $last_level = $heading_level;

        $toc_text .= "<li><a href='#" . $link_name . "'>" . $contained . "</a></li>\n";

        // Get the next tag location
        $next_tag_pos = $this->get_next_tag_pos($text, $heading_tags, $next_tag_pos + 1);
      }
      
      if ($toc_text != "") {
        $this->close_tags($toc_text, $last_level, 0);
        return '<div class="toc"><a name="toc" id="toc" /><h1>Inhaltsverzeichnis</h1>' . $toc_text .'</div>'. $text;
      }
      return $text;
    }
}
