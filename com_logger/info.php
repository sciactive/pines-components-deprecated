<?php
/**
 * com_logger's information.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Logger',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'services' => array('log_manager'),
	'short_description' => 'System log manager',
	'description' => 'Provides a method for components to log their activity.',
	'abilities' => array(
		array('view', 'View Log', 'Let the user view the Pines log.'),
		array('clear', 'Clear Log', 'Let the user clear (delete) the pines log.')
	),
);

?>