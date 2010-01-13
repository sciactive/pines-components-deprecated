<?php
/**
 * com_sales_product class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A product.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_product extends entity {
	/**
	 * Load a product.
	 * @param int $id The ID of the product to load, null for a new product.
	 */
	public function __construct($id = null) {
		parent::__construct();
		$this->add_tag('com_sales', 'product');
		if (!is_null($id)) {
			global $config;
			$entity = $config->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			if (is_null($entity))
				return;
			$this->guid = $entity->guid;
			$this->parent = $entity->parent;
			$this->tags = $entity->tags;
			$this->entity_cache = array();
			$this->put_data($entity->get_data());
		}
	}

	/**
	 * Create a new instance.
	 */
	public static function factory() {
		global $config;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$config->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Delete the product.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted product $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the product.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the product.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $config;
		$config->editor->load();
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$jstree = new module('system', 'jstree', 'head');
		$ptags = new module('system', 'ptags.default', 'head');
		$module = new module('com_sales', 'form_product', 'content');
		$module->entity = $this;
		$module->manufacturers = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'manufacturer'), 'class' => com_sales_manufacturer));
		if (!is_array($module->manufacturers)) {
			$module->manufacturers = array();
		}
		$module->vendors = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'vendor'), 'class' => com_sales_vendor));
		if (!is_array($module->vendors)) {
			$module->vendors = array();
		}
		$module->tax_fees = $config->entity_manager->get_entities(array('tags' => array('com_sales', 'tax_fee'), 'class' => com_sales_tax_fee));
		if (!is_array($module->tax_fees)) {
			$module->tax_fees = array();
		}
		$module->actions = $config->run_sales->product_actions;
		if (!is_array($module->actions)) {
			$module->actions = array();
		}

		return $module;
	}
}

?>