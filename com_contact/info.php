<?php
/**
 * com_contact's information.
 *
 * @package Pines
 * @subpackage com_contact
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Contact Form',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'A simple contact form',
	'description' => 'A contact form used to send messages from the website.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_pform'
	),
);

?>