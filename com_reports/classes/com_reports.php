<?php
/**
 * com_reports class.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_reports main class.
 *
 * @package Pines
 * @subpackage com_reports
 */
class com_reports extends component {
	/**
	 * Print a form to select date timespan.
	 *
	 * @param bool $all_time Currently searching all records or a timespan.
	 * @param string $start The current starting date of the timespan.
	 * @param string $end The current ending date of the timespan.
	 * @return module The form's module.
	 */
	public function date_select_form($all_time = false, $start = null, $end = null) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_reports', 'date_selector', 'content');
		$module->all_time = $all_time;
		$module->start_date = $start;
		$module->end_date = $end;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Creates and attaches a module which lists sales rankings.
	 *
	 * @return module The sales report module.
	 */
	function list_sales_rankings() {
		global $pines;

		$module = new module('com_reports', 'list_sales_rankings', 'content');
		$module->rankings = $pines->entity_manager->get_entities(array('class' => com_reports_sales_ranking), array('&', 'tag' => array('com_reports', 'sales_ranking')));
	}

	/**
	 * Print a form to select a location.
	 *
	 * @param int $location The currently set location to search in.
	 * @return module The form's module.
	 */
	public function location_select_form($location = null) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_reports', 'location_selector', 'content');
		if (!isset($location)) {
			$module->location = $_SESSION['user']->group->guid;
		} else {
			$module->location = $location;
		}

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Creates and attaches a module which reports sales.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param int $employee The employee to report on.
	 * @return module The attendance report module.
	 */
	function report_attendance($start_date = null, $end_date = null, $location = null, $employee = null) {
		global $pines;

		$module = new module('com_reports', 'report_attendance', 'content');
		if (!isset($employee)) {
			$module->employees = $pines->com_hrm->get_employees();
			foreach ($module->employees as $key => &$cur_employee) {
				if (!($cur_employee->in_group($location) || $cur_employee->is_descendent($location)))
					unset($module->employees[$key]);
			}
		} else {
			$module->employee = $employee;
		}
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		$module->location = $location;
	}

	/**
	 * Creates and attaches a module which reports employee issues.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @return module The attendance report module.
	 */
	function report_issues($start_date = null, $end_date = null, $location = null) {
		global $pines;

		$module = new module('com_reports', 'report_issues', 'content');

		$selector = array('&', 'tag' => array('com_hrm', 'issue'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lte'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		$or = array('|', 'ref' => array('location', $location->get_descendents(true)));
		$module->location = $location;
		$module->issues = $pines->entity_manager->get_entities(array('class' => com_hrm_issue), $selector, $or);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports product details.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The location to report sales for.
	 * @return module The product details report module.
	 */
	function report_product_details($start_date = null, $end_date = null, $location = null) {
		global $pines;

		$module = new module('com_reports', 'report_product_details', 'content');

		$selector = array('&', 'tag' => array('com_sales', 'sale'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lte'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;

		$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		$module->location = $location;
		$transactions = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		$selector['tag'] = array('com_sales', 'return');
		$module->transactions = array_merge($transactions, $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector, $or));
	}

	/**
	 * Creates and attaches a module which reports sales.
	 *
	 * @param int $start The start date of the report.
	 * @param int $end The end date of the report.
	 * @param group $location The location to report sales for.
	 * @param employee $employee The employee to report sales for.
	 * @return module The sales report module.
	 */
	function report_sales($start, $end, $location = null, $employee = null) {
		global $pines;

		$form = new module('com_reports', 'form_sales', 'right');
		$head = new module('com_reports', 'show_calendar_head', 'head');
		$module = new module('com_reports', 'report_sales', 'content');

		$selector = array('&',
			'tag' => array('com_sales', 'transaction', 'sale_tx'));
		$or = array();
		// Datespan of the report.
		$date_start = strtotime('00:00', $start);
		$date_end = strtotime('23:59', $end);
		$selector['gte'] = array('p_cdate', $date_start);
		$selector['lte'] = array('p_cdate', $date_end);
		$module->date[0] = $form->date[0] = $date_start;
		$module->date[1] = $form->date[1] = $date_end;
		// Employee and location of the report.
		if (isset($employee->guid)) {
			$selector['ref'] = array('user', $employee);
			$module->employee = $form->employee = $employee;
			$module->title = 'Sales Report for '.$employee->name;
		} elseif (isset($location->guid)) {
			$module->title = 'Sales Report for '.$location->name;
		} else {
			$location = $_SESSION['user']->group;
			$module->all = true;
			$module->title = 'Sales Report for All Locations';
		}
		$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		$module->location = $form->location = $location->guid;
		$form->employees = $pines->com_hrm->get_employees();
		$module->transactions = $pines->entity_manager->get_entities(array('class' => com_sales_tx), $selector, $or);
	}
}

?>