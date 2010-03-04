<?php
/**
 * com_pgrid class.
 *
 * @package Pines
 * @subpackage com_pgrid
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_pgrid main class.
 *
 * A JavaScript data grid.
 *
 * @package Pines
 * @subpackage com_pgrid
 */
class com_pgrid extends component {
	/**
	 * Whether the pgrid JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the grid.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->js_loaded) {
			$module = new module('com_pgrid', 'pgrid', 'head');
			$icons = new module('com_pgrid', 'pgrid_icons', 'head');
			$this->js_loaded = true;
		}
	}
}

?>