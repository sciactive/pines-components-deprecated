<?php
/**
 * Edit a work schedule for an employee in the company.
 *
 * @package Components\calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() )
	punt_user(null, pines_url('com_calendar', 'editschedule'));

$employee = com_hrm_employee::factory((int)$_REQUEST['employee']);
if (!isset($employee->guid))
	return;

$pines->com_calendar->schedule_form($employee);

?>