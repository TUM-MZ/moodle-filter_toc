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

$plugin->version  = 2012012700;
$plugin->requires = 2011102700;  // Requires this Moodle version
$plugin->component= 'filter_toc';
$plugin->release = 1;
