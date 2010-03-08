<?php
/**
 * Add abilities.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->ability_manager->add('com_customertimer', 'viewstatus', 'View Customer Status', 'User can view the timer status of logged in customers.');
$pines->ability_manager->add('com_customertimer', 'notifystatus', 'Notify Customer Status', 'User is notifies when customers are running out of points.');
$pines->ability_manager->add('com_customertimer', 'login', 'Login Users', 'User can log a customer in to the time tracker.');
$pines->ability_manager->add('com_customertimer', 'forcelogout', 'Force Logout', 'User can log a customer out of the time tracker.');

?>