<?php
/**
 * com_hrm_bonus class.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A bonus for an employee.
 *
 * @package Components\hrm
 */
class com_hrm_adjustment extends entity {
	/**
	 * Load an bonus.
	 * @param int $id The ID of the bonus to load, 0 for a new bonus.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_hrm', 'adjustment');
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
		$this->comments = array();
	}

	/**
	 * Create a new instance.
	 * @return com_hrm_bonus The new instance.
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
				return "Adjustment $this->guid";
			case 'type':
				return 'adjustment';
			case 'types':
				return 'adjustments';
			case 'url_list':
				if (gatekeeper('com_hrm/listadjustments'))
					return pines_url('com_hrm', 'adjustment/list');
				break;
			case 'icon':
				return 'picon-accessories-calculator';
		}
		return null;
	}

	/**
	 * Delete the bonus.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted adjustment $this->guid.", 'notice');
		return true;
	}
}

?>