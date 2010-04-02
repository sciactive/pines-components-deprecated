<?php
/**
 * Edit an employee's timeclock history.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/manageclock') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'edittimeclock', array('id' => $_REQUEST['id']), false));

$employee = com_hrm_employee::factory((int) $_REQUEST['id']);
if (is_null($employee->guid)) {
	pines_error('Requested employee id is not accessible.');
	return;
}

if ( empty($employee->timeclock) )
	pines_notice("No timeclock data is stored for employee [{$employee->name}].");

$employee->print_timeclock();

?>