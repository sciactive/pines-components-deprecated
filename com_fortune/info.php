<?php
/**
 * com_fortune's information.
 *
 * @package Components\fortune
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Fortune',
	'author' => 'SciActive',
	'version' => '0.01.0alpha',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Daily fortune',
	'description' => 'Reminiscent of the "fortune" program in Unix, this prints a daily adage from a database of fortunes, quotes, riddles, etc.',
	'depend' => array(
		'pines' => '<3'
	),
);

?>