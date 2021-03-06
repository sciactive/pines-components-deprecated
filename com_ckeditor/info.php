<?php
/**
 * com_ckeditor's information.
 *
 * @package Components\ckeditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'CKEditor',
	'author' => 'SciActive',
	'version' => '3.6.3-1.1.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('editor'),
	'short_description' => 'CKEditor editor widget',
	'description' => 'CKEditor based editor widget.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery&com_bootstrap&com_pform'
	),
	'recommend' => array(
		'component' => 'com_elfinder'
	),
);

?>