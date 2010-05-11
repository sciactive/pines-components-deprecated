<?php
/**
 * com_elfinder's information.
 *
 * @package Pines
 * @subpackage com_elfinder
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'elFinder File Manager',
	'author' => 'SciActive (Component), Studio 42 Ltd. (JavaScript)',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'elFinder file manager and widget',
	'description' => 'A file manager using the elFinder jQuery plugin. See the readme in the includes folder for elFinder\'s license information.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_jquery'
	),
	'abilities' => array(
		array('finder', 'Use elFinder', 'User can manage files with elFinder.'),
	),
);

?>