<?php
/**
 * com_reports_sales_ranking class.
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
 * A list of monthly sales rankings.
 *
 * @package Pines
 * @subpackage com_reports
 */
class com_reports_sales_ranking extends entity {
	/**
	 * Load a sales ranking.
	 * @param int $id The ID of the ranking to load, 0 for a new ranking.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_reports', 'sales_ranking');
		// Defaults.
		$this->goals = array();
		$this->start_date = strtotime(date('m/01/Y 00:00:00'));
		$this->end_date = strtotime('+1 month 00:00:00', $this->start_date);
		$this->top_location = $_SESSION['user']->group;
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data(), $entity->get_sdata());
		}
	}

	/**
	 * Create a new instance.
	 * @return com_reports_sales_ranking The new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Delete the sales ranking.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted sales ranking [$this->name].", 'notice');
		return true;
	}

	/**
	 * Print a form to edit the sales ranking.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		
		$module = new module('com_reports', 'form_sales_ranking', 'content');
		$module->entity = $this;
		$module->employees = $pines->com_hrm->get_employees();

		return $module;
	}

	/**
	 * Creates and attaches a module which reports sales rankings.
	 * 
	 * @param group $location The location to list the rankings for.
	 * @param bool $descendents Whether to show descendent locations.
	 * @return module The sales ranking report module.
	 */
	function rank($location = null, $descendents = false) {
		global $pines;

		if (!isset($location->guid))
			$location = $this->top_location;

		$module = new module('com_reports', 'view_sales_rankings', 'content');
		$module->entity = $this;
		$module->location = $location;
		$module->descendents = $descendents;
		$module->rankings = array();
		$employees = $pines->com_hrm->get_employees();

		foreach ($employees as $key => $value) {
			if ( !$value->in_group($location) && (!$descendents || !$value->is_descendent($location)) )
				unset($employees[$key]);
		}

		// Date setup for different weekly and monthly breakdowns.
		if (format_date(time(), 'custom', 'w') == '1') {
			$current_start = strtotime('00:00:00', time());
		} else {
			$current_start = strtotime('00:00:00', strtotime('last Monday'));
		}
		if (format_date(time(), 'custom', 'w') == '0') {
			$current_end = strtotime('23:59:59', time()) + 1;
		} else {
			$current_end = strtotime('23:59:59', strtotime('next Sunday')) + 1;
		}
		if ($this->end_date > time()) {
			$days_passed = (int) format_date(time(), 'custom', 'j');
			$days_in_month = (int) format_date(time(), 'custom', 't');
		} else {
			$days_passed = (int) format_date($this->end_date, 'custom', 'j');
			$days_in_month = (int) format_date($this->end_date, 'custom', 't');
			$current_start = strtotime('00:00:00', strtotime('last Monday', $this->end_date));
			$current_end = strtotime('23:59:59', $this->end_date) + 1;
		}
		$last_start = strtotime('-1 week', $current_start);
		$last_end = strtotime('+1 week', $last_start);
		
		// Calculate the rankings for all of the employees.
		$module->total = array(
			'current' => 0,
			'last' => 0,
			'mtd' => 0,
			'goal' => 0,
			'trend' => 0,
			'pct' => 0
		);
		foreach ($employees as $cur_employee) {
			// Exclude employees with no sales goals.
			if ($this->goals[$cur_employee->guid] == 0)
				continue;

			$module->rankings[$cur_employee->guid] = array(
				'employee' => $cur_employee,
				'current' => 0,
				'last' => 0,
				'mtd' => 0,
				'trend' => 0,
				'pct' => 0,
				'goal' => $this->goals[$cur_employee->guid]
			);

			// Get the employee's sales totals for the entire sales period.
			$mtd_sales = $pines->entity_manager->get_entities(
					array('class' => com_sales_sale),
					array('&',
						'tag' => array('com_sales', 'sale'),
						'data' => array('status', 'paid'),
						'gte' => array('tender_date', $this->start_date),
						'lt' => array('tender_date', $this->end_date),
						'ref' => array('products', $cur_employee)
					)
				);

			foreach ($mtd_sales as &$cur_mtd_sale) {
				foreach ($cur_mtd_sale->products as $cur_product) {
					if (!$cur_product['salesperson']->is($cur_employee))
						continue;
					if ($cur_mtd_sale->tender_date >= $current_start && $cur_mtd_sale->tender_date <= $current_end)
						$module->rankings[$cur_employee->guid]['current'] += $cur_product['line_total'];
					elseif ($cur_mtd_sale->tender_date >= $last_start && $cur_mtd_sale->tender_date <= $last_end)
						$module->rankings[$cur_employee->guid]['last'] += $cur_product['line_total'];

					$module->rankings[$cur_employee->guid]['mtd'] += $cur_product['line_total'];
				}
			}
			unset($cur_mtd_sale, $mtd_sales);

			$mtd_returns = $pines->entity_manager->get_entities(
					array('class' => com_sales_return),
					array('&',
						'tag' => array('com_sales', 'return'),
						'data' => array('status', 'processed'),
						'gte' => array('tender_date', $this->start_date),
						'lt' => array('tender_date', $this->end_date),
						'ref' => array('products', $cur_employee)
					)
				);

			foreach ($mtd_returns as &$cur_mtd_return) {
				foreach ($cur_mtd_return->products as $cur_product) {
					if (!$cur_product['salesperson']->is($cur_employee))
						continue;
					if ($cur_mtd_return->tender_date >= $current_start && $cur_mtd_return->tender_date <= $current_end)
						$module->rankings[$cur_employee->guid]['current'] -= $cur_product['line_total'];
					elseif ($cur_mtd_return->tender_date >= $last_start && $cur_mtd_return->tender_date <= $last_end)
						$module->rankings[$cur_employee->guid]['last'] -= $cur_product['line_total'];
					$module->rankings[$cur_employee->guid]['mtd'] -= $cur_product['line_total'];
				}
			}
			unset($cur_mtd_return, $mtd_returns);

			$module->rankings[$cur_employee->guid]['trend'] = ($module->rankings[$cur_employee->guid]['mtd'] / $days_passed) * $days_in_month;

			if ($module->rankings[$cur_employee->guid]['goal'] == 0) {
				unset($module->rankings[$cur_employee->guid]);
			} else {
				$module->rankings[$cur_employee->guid]['pct'] = $module->rankings[$cur_employee->guid]['trend'] / $module->rankings[$cur_employee->guid]['goal'] * 100;
				// Update totals for the entire company location(s).
				$module->total['current'] += $module->rankings[$cur_employee->guid]['current'];
				$module->total['last'] += $module->rankings[$cur_employee->guid]['last'];
				$module->total['mtd'] += $module->rankings[$cur_employee->guid]['mtd'];
				$module->total['trend'] += $module->rankings[$cur_employee->guid]['trend'];
				$module->total['goal'] += $module->rankings[$cur_employee->guid]['goal'];
			}
		}
		// Account for employees potentially having $0 as a goal.
		if ($module->total['goal'] > 0) {
			$module->total['pct'] = $module->total['trend'] / $module->total['goal'] * 100;
		} else {
			$module->total['pct'] = 100;
		}
		// Sort and rank the employees by their trend percentage.
		usort($module->rankings, array($this, 'sort_ranks'));
		$rank = 1;
		foreach ($module->rankings as &$cur_rank) {
			$cur_rank['rank'] = $rank;
			$rank++;
		}
		unset($cur_rank);
		
		return $module;
	}

	/**
	 * Sort by the trend percentage.
	 *
	 * @param array $a The first entry.
	 * @param array $b The second entry.
	 * @return int The sort order.
	 * @access private
	 */
	private function sort_ranks($a, $b) {
		if ($a['pct'] > $b['pct'])
			return -1;
		if ($a['pct'] < $b['pct'])
			return 1;
		return 0;
	}
}

?>