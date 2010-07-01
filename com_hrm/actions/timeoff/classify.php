<?php
/**
 * Approve or decline requested time off.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/managerto') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'editcalendar'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_request) {
	$cur_entity = com_hrm_rto::factory((int) $cur_request);
	$cur_entity->status = ($_REQUEST['status'] == 'approved' ? 'approved' : 'declined');

	if ( !isset($cur_entity->guid) || !$cur_entity->save() ) {
		$failed_updates .= (empty($failed_updates) ? '' : ', ').$cur_requests;
	} elseif ($_REQUEST['status'] == 'approved') {
		$new_event = com_hrm_event::factory();
		$new_event->time_off = true;
		$new_event->employee = $cur_entity->employee;
		$new_event->title = '(OFF) - '.$cur_entity->employee->name;
		$new_event->all_day = $cur_entity->all_day;
		$new_event->start = $cur_entity->start;
		$new_event->end = $cur_entity->end;
		$new_event->color = 'gainsboro';
		$new_event->save();
	}
}
if (empty($failed_updates)) {
	pines_notice('Selected request(s) successfully '.$_REQUEST['status'].'.');
} else {
	pines_error('Could not update requests with given IDs: '.$failed_updates);
}

redirect(pines_url('com_hrm', 'editcalendar'));

?>