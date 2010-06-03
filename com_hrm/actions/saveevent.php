<?php
/**
 * Save a new event for the company calendar.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/editcalendar') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'editcalendar'));

if (isset($_REQUEST['employee'])) {	
	if ($_REQUEST['event_date'] != 'Date') {
		$event_month = date('n', strtotime($_REQUEST['event_date']));
		$event_day = date('j', strtotime($_REQUEST['event_date']));
		$event_year = date('Y', strtotime($_REQUEST['event_date']));
		$event_endmonth = date('n', strtotime($_REQUEST['event_enddate']));
		$event_endday = date('j', strtotime($_REQUEST['event_enddate']));
		$event_endyear = date('Y', strtotime($_REQUEST['event_enddate']));
	} else {
		// Default to the current date.
		$event_month = date('n');
		$event_day = date('j');
		$event_year = date('Y');
		$event_endmonth = date('n');
		$event_endday = date('j');
		$event_endyear = date('Y');
	}
	if (isset($_REQUEST['id'])) {
		$event = com_hrm_event::factory((int) $_REQUEST['id']);
		if (!isset($event->guid)) {
			pines_error('The calendar was altered while editing the event.');
			$pines->com_hrm->show_calendar();
			return;
		}
	} else {
		$event = com_hrm_event::factory();
		$event->id = 0;
	}

	$event->employee = com_hrm_employee::factory((int) $_REQUEST['employee']);
	$location = $event->employee->group;
	$event->title = $event->employee->name;
	$event->color = $event->employee->color;

	if (!isset($event->employee->guid)) {
		$event_details = explode(':', $_REQUEST['employee']);
		$event->title = $event->employee = $event_details[0];
		$event->color = $event_details[1];
		$location = group::factory((int) $_REQUEST['location']);
		if (!isset($location->guid)) {
			pines_error('The specified location for this event does not exist.');
			$pines->com_hrm->show_calendar();
			return;
		}
	}
	
	if ($_REQUEST['event_label'] != 'Label') {
		$event->label = $_REQUEST['event_label'];
		$event->title = $event->label .' - '. $event->title;
	}
	$event->all_day = ($_REQUEST['all_day'] == 'allDay') ? true : false;
	$event->start = mktime($event->all_day ? 0 : $_REQUEST['event_start'],0,0,$event_month,$event_day,$event_year);
	$event->end = mktime($event->all_day ? 23 : $_REQUEST['event_end'],$event->all_day ? 59 : 0,$event->all_day ? 59 : 0,$event_endmonth,$event_endday,$event_endyear);
	if ($event->all_day) {
		$days = ceil(($event->end - $event->start) / 86400);
		$event->scheduled = isset($event->employee->workday_length) ? $event->employee->workday_length : $pines->config->com_hrm->workday_length;
		$event->scheduled *= 3600 * $days;
	} else {
		$event->scheduled = $event->end - $event->start;
	}
	
	if ($pines->config->com_hrm->global_events)
		$event->ac->other = 1;

	if ($event->save()) {
		$event->group = $location;
		$event->save();
		$action = ( isset($_REQUEST['id']) ) ? 'Saved ' : 'Entered ';
		pines_notice($action.'['.$event->title.']');
	} else {
		pines_error('Error saving event. Do you have permission?');
	}
}

redirect(pines_url('com_hrm', 'editcalendar', array('location' => $location->guid)));
?>