<?php
/**
 * com_fortune class.
 *
 * @package Components\fortune
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_fortune main class.
 *
 * @package Components\fortune
 */
class com_fortune extends component {
	/**
	 * Print a fortune.
	 */
	public function print_fortune() {
		global $pines;
		$module = new module('com_fortune', 'fortune', $pines->config->com_fortune->position);
		$module->fortune = $this->get_fortune();
	}

	/**
	 * Get the fortune database.
	 */
	public function get_fortune() {
		global $pines;
		$databases = $pines->config->com_fortune->databases;
	}
}

?>