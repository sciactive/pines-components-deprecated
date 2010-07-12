<?php
/**
 * Prevent deleting a logged in customer.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Deny customers who are logged in from being deleted.
 *
 * @param array &$arguments Arguments.
 * @param string $name Hook name.
 * @param object &$object The customer being deleted.
 * @todo Fix this to work with the new floor system.
 */
function com_customertimer__check_delete(&$arguments, $name, &$object) {
	/*
	if (!is_object($object))
		return;
	$logins = com_customertimer_login_tracker::factory();
	if ($logins->logged_in($object)) {
		pines_notice("{$object->guid}: {$object->name} is currently logged in to the customer timer and cannot be deleted until logged out.");
		$arguments = false;
	}
	 */
}

$pines->hook->add_callback('com_customer_customer->delete', -10, 'com_customertimer__check_delete');

?>