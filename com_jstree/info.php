<?php
/**
 * com_jstree' information.
 *
 * @package Components\jstree
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'jsTree',
	'author' => 'SciActive (Component), Ivan Bozhanov (JavaScript)',
	'version' => '1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'jsTree jQuery plugin',
	'description' => 'A JavaScript tree jQuery component. Includes the context menu plugin.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery'
	),
);

?>