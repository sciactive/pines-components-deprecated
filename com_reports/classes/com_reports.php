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
	 * Creates and attaches a module which lists company paystubs.
	 *
	 * @return module The payroll list module.
	 */
	function list_paystubs() {
		global $pines;

		$module = new module('com_reports', 'list_paystubs', 'content');
		$module->paystubs = $pines->entity_manager->get_entities(array('class' => com_reports_paystub), array('&', 'tag' => array('com_reports', 'paystub')));

		if ( empty($module->paystubs) )
			pines_notice('There are no completed paystubs to view.');
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
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The form's module.
	 */
	public function location_select_form($location = null, $descendents = false) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_reports', 'location_selector', 'content');
		if (!isset($location)) {
			$module->location = $_SESSION['user']->group->guid;
		} else {
			$module->location = $location;
		}
		$module->descendents = $descendents;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Creates and attaches a module which summarizes employee totals.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The employee summary module.
	 */
	function employee_summary($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'employee_summary', 'content');

		$selector = array('&');
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$selector['tag'] = array('com_sales', 'sale');
		$selector['strict'] = array('status', 'paid');
		$sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		$selector['tag'] = array('com_sales', 'return');
		$selector['strict'] = array('status', 'processed');
		$returns = $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector, $or);
		$module->invoices = array_merge($sales, $returns);

		return $module;
	}

	/**
	 * Creates and attaches a module which summarizes all sales, returns and voids.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module the invoice summary module.
	 */
	function invoice_summary($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'invoice_summary', 'content');

		$selector = array('&');
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$selector['tag'] = array('com_sales', 'sale');
		$sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		$selector['tag'] = array('com_sales', 'return');
		$returns = $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector, $or);
		$module->invoices = array_merge($sales, $returns);

		return $module;
	}

	/**
	 * Creates and attaches a module which summarizes locational totals.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The location summary module.
	 */
	function location_summary($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'location_summary', 'content');

		$selector = array('&');
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$selector['tag'] = array('com_sales', 'sale');
		$selector['strict'] = array('status', 'paid');
		$sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		$selector['tag'] = array('com_sales', 'return');
		$selector['strict'] = array('status', 'processed');
		$returns = $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector, $or);
		$module->invoices = array_merge($sales, $returns);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports sales.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param int $employee The employee to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The attendance report module.
	 */
	function report_attendance($start_date = null, $end_date = null, $location = null, $employee = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_attendance', 'content');
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if (!isset($employee)) {
			$module->employees = $pines->com_hrm->get_employees(true);
			foreach ($module->employees as $key => &$cur_employee) {
				if (!($cur_employee->in_group($location) || ($descendents && $cur_employee->is_descendent($location))))
					unset($module->employees[$key]);
			}
		} else {
			$module->employee = $employee;
		}
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		$module->location = $location;
		$module->descendents = $descendents;
	}

	/**
	 * Creates and attaches a module which reports calendar events.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The calendar report module.
	 */
	function report_calendar($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_calendar', 'content');

		$selector = array('&', 'tag' => array('com_calendar', 'event'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('start', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('end', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->events = $pines->entity_manager->get_entities(array('class' => com_calendar_event), $selector, $or);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports employee issues.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The issues report module.
	 */
	function report_issues($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_issues', 'content');

		$selector = array('&', 'tag' => array('com_hrm', 'issue'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->issues = $pines->entity_manager->get_entities(array('class' => com_hrm_issue), $selector, $or);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports MiFi Sales.
	 *
	 * @param int $verbose Whether to show extraneous application information.
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The MiFi report module.
	 */
	function report_mifi($verbose = false, $start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_mifi', 'content');

		$selector = array('&', 'tag' => array('com_sales', 'sale'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$selector['strict'] = array('status', 'paid');
		//$selector['match'] = array('payments', 'MiFi Finance');
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		$module->verbose = $verbose;
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		// Make sure this sale is not attached to any returns.
		foreach ($module->sales as $key => &$cur_sale) {
			$return = $pines->entity_manager->get_entity(
				array('class' => com_sales_return, 'skip_ac' => true),
				array('&', 'tag' => array('com_sales', 'return'), 'ref' => array('sale', $cur_sale))
			);
			if (isset($return->guid))
				unset($module->sales[$key]);
		}
		return $module;
	}

	/**
	 * Creates and attaches a module which shows customers with MiFi Financing.
	 *
	 * @return module The MiFi report module.
	 */
	function report_mifi_available() {
		global $pines;

		$module = new module('com_reports', 'report_mifi_available', 'content');

		// Select any MiFi applications with existing financing approved
		$groups = $_SESSION['user']->group->get_descendents(true);
		$module->applications = $pines->entity_manager->get_entities(
				array('class' => com_mifi_application, 'skip_ac' => true),
				array('&',
					'tag' => array('com_mifi', 'application'),
					'gte' => array('approval_amount', 1500),
					'lte' => array('p_cdate', strtotime('-30 days'))
				),
				array('|', 'ref' => array('group', $groups))
			);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports MiFi Contracts.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The MiFi report module.
	 */
	function report_mifi_contracts($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_mifi_contracts', 'content');

		$selector = array('&',
				'tag' => array('com_mifi', 'contract'),
				'strict' => array('status', 'tendered')
			);
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		//$selector['match'] = array('payments', 'MiFi');
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->contracts = $pines->entity_manager->get_entities(array('class' => com_mifi_contract), $selector, $or);

		return $module;
	}

	/**
	 * Creates and attaches a module which shows customers with MiFi Faxsheets.
	 *
	 * @return module The MiFi report module.
	 */
	function report_mifi_faxsheets() {
		global $pines;

		$module = new module('com_reports', 'report_mifi_faxsheets', 'content');

		// Select any MiFi contracts with faxsheets requests.
		$groups = $_SESSION['user']->group->get_descendents(true);
		$module->contracts = $pines->entity_manager->get_entities(
				array('class' => com_mifi_contract, 'skip_ac' => true),
				array('&',
					'tag' => array('com_mifi', 'contract'),
					'isset' => array('faxsheet_request'),
					'strict' => array('status', 'tendered')
				),
				array('!&',
					'strict' => array('archived', true)
				)
			);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports employee payroll information.
	 *
	 * @param bool $entire_company Whether or not to show the entire company.
	 * @param group $location The group to report on.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The employee payroll module.
	 */
	function report_payroll($entire_company = true, $location = null, $descendents = false) {
		global $pines;

		$pay_start = strtotime($pines->config->com_hrm->pay_start);
		$pay_period = $pines->config->com_hrm->pay_period;
		$total_time = (strtotime('0:00:00') - $pay_start)/$pay_period;
		// Start date is 1 day after the last pay period.
		$start_date = $pay_start + (floor($total_time) * $pay_period) + 86400;
		if (floor($total_time) == $total_time)
			$start_date = strtotime('-1 week', $start_date);
		// End date is at the end of the last day of the pay period.
		$end_date = $pay_start + (ceil($total_time) * $pay_period) + 86399;

		$paystub = $pines->entity_manager->get_entity(array('class' => com_reports_paystub),
				array('&',
					'tag' => array('com_reports', 'paystub'),
					'gte' => array('end', (int) $start_date)
				)
			);
		if (isset($paystub->guid))
			return $paystub->show($entire_company, $location, $descendents);

		$module = new module('com_reports', 'report_payroll', 'content');
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->entire_company = $entire_company;
		$module->employees = $pines->com_hrm->get_employees(true);
		$or = array();
		if (!$module->entire_company) {
			// Location of the report.
			if (!isset($location->guid))
				$location = $_SESSION['user']->group;
			if ($descendents)
				$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
			else
				$or = array('|', 'ref' => array('group', $location));
			$module->location = $location;
			$module->descendents = $descendents;
			foreach ($module->employees as $key => &$cur_employee) {
				if (!($cur_employee->in_group($location) || ($descendents && $cur_employee->is_descendent($location))))
					unset($module->employees[$key]);
			}
		}
		// Sales
		$selector = array('&',
				'tag' => array('com_sales', 'sale'),
				'data' => array('status', 'paid'),
				'gte' => array('p_cdate', (int) $module->start_date),
				'lt' => array('p_cdate', (int) $module->end_date)
			);
		if (!empty($or))
			$selector = array_merge($selector, $or);
		$sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector);
		// Returns
		$selector = array('&',
				'tag' => array('com_sales', 'return'),
				'data' => array('status', 'processed'),
				'gte' => array('p_cdate', (int) $module->start_date),
				'lt' => array('p_cdate', (int) $module->end_date)
			);
		if (!empty($or))
			$selector = array_merge($selector, $or);
		$returns = $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector);
		$module->invoices = array_merge($sales, $returns);

		return $module;
	}

	/**
	 * Creates and attaches a module which reports product details.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The location to report sales for.
	 * @param bool $descendents Whether to show descendent locations.
	 * @param bool $types The types of transactions to show.
	 * @return module The product details report module.
	 */
	function report_product_details($start_date = null, $end_date = null, $location = null, $descendents = false, $types = null) {
		global $pines;

		$module = new module('com_reports', 'report_product_details', 'content');
		if (isset($types)) {
			$module->types = $types;
		} else {
			$module->types = array(
				'sold' => true,
				'invoiced' => true,
				'returned' => true,
				'voided' => true,
				'return' => true
			);
		}

		$selector = array('&', 'tag' => array('com_sales', 'sale'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->transactions = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);

		if ($module->types['return']) {
			$selector['tag'] = array('com_sales', 'return');
			$selector['strict'] = array('status', 'processed');
			$module->transactions = array_merge($module->transactions, $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector, $or));
		}
	}

	/**
	 * Creates and attaches a module which reports sales.
	 *
	 * @param int $start The start date of the report.
	 * @param int $end The end date of the report.
	 * @param group $location The location to report sales for.
	 * @param employee $employee The employee to report sales for.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The sales report module.
	 */
	function report_sales($start, $end, $location = null, $employee = null, $descendents = false) {
		global $pines;

		$form = new module('com_reports', 'form_sales', 'right');
		$head = new module('com_reports', 'show_calendar_head', 'head');
		$module = new module('com_reports', 'report_sales', 'content');

		$selector = array('&', 'tag' => array('com_sales', 'transaction', 'sale_tx'));
		$or = array();
		// Datespan of the report.
		$date_start = strtotime('00:00:00', $start);
		$date_end = strtotime('23:59:59', $end) + 1;
		$selector['gte'] = array('p_cdate', $date_start);
		$selector['lt'] = array('p_cdate', $date_end);
		$module->date[0] = $form->date[0] = $date_start;
		$module->date[1] = $form->date[1] = $date_end;
		// Employee and location of the report.
		if (isset($employee->guid)) {
			$selector['ref'] = array('products', $employee);
			$module->employee = $form->employee = $employee;
			$module->title = 'Sales Report for '.$employee->name;
		} elseif (isset($location->guid)) {
			$module->title = 'Sales Report for '.$location->name;
		} else {
			$location = $_SESSION['user']->group;
			$module->all = true;
			$module->title = 'Sales Report for All Locations';
		}
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $form->location = $location->guid;
		$form->employees = $pines->com_hrm->get_employees();
		$module->descendents = $form->descendents = $descendents;
		$selector['tag'] = array('com_sales', 'sale');
		$selector['data'] = array('status', 'paid');
		$sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		$selector['tag'] = array('com_sales', 'return');
		$selector['data'] = array('status', 'processed');
		$returns = $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector, $or);
		$module->invoices = array_merge($sales, $returns);
	}

	/**
	 * Creates and attaches a module which reports warehouse items.
	 *
	 * @param int $start_date The start date of the report.
	 * @param int $end_date The end date of the report.
	 * @param group $location The location to report sales for.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The product details report module.
	 */
	function report_warehouse($start_date = null, $end_date = null, $location = null, $descendents = false) {
		global $pines;

		$module = new module('com_reports', 'report_warehouse', 'content');
		
		$selector = array('&', 'tag' => array('com_sales', 'sale'), 'strict' => array('status', 'paid'));
		// Datespan of the report.
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lt'] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		// Location of the report.
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if ($descendents)
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		else
			$or = array('|', 'ref' => array('group', $location));
		$module->location = $location;
		$module->descendents = $descendents;
		$module->transactions = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
	}
}

?>