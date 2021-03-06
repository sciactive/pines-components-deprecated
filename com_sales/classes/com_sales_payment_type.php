<?php
/**
 * com_sales_payment_type class.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A payment type.
 *
 * @package Components\sales
 */
class com_sales_payment_type extends entity {
	/**
	 * Load a payment type.
	 * @param int $id The ID of the payment type to load, 0 for a new payment type.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_sales', 'payment_type');
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				return;
			}
		}
		// Defaults.
		$this->enabled = true;
		$this->processing_type = 'com_sales/instant';
	}

	/**
	 * Create a new instance.
	 * @return com_sales_payment_type The new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	public function info($type) {
		switch ($type) {
			case 'name':
				return $this->name;
			case 'type':
				return 'payment type';
			case 'types':
				return 'payment types';
			case 'url_edit':
				if (gatekeeper('com_sales/editpaymenttype'))
					return pines_url('com_sales', 'paymenttype/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_sales/listpaymenttypes'))
					return pines_url('com_sales', 'paymenttype/list');
				break;
			case 'icon':
				return 'picon-view-bank';
		}
		return null;
	}

	/**
	 * Delete the payment type.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted payment type $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the payment type.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the payment type.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_sales', 'paymenttype/form', 'content');
		$module->entity = $this;
		$module->processing_types = (array) $pines->config->com_sales->processing_types;

		return $module;
	}
}

?>