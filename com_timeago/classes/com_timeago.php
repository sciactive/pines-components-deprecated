<?php
/**
 * com_timeago's information.
 *
 * @package Components\timeago
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_psteps main class.
 *
 * A JavaScript progression-based step editor.
 *
 * @package Components\psteps
 */
class com_timeago extends component {
	/**
	 * Whether the psteps JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the step transformer.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		global $pines;
		if (!$this->js_loaded) {
			if ($pines->config->compress_cssjs) {
				$file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT'].$pines->config->location);
				$js = (is_array($pines->config->loadcompressedjs)) ? $pines->config->loadcompressedjs : array();
				$js[] =  $file_root.'components/com_timeago/includes/'.($pines->config->debug_mode ? 'jquery.timeago.js' : 'jquery.timeago.min.js');
				$pines->config->loadcompressedjs = $js;
			} else {
				$module = new module('com_timeago', 'timeago', 'head');
				$module->render();
			}
			$this->js_loaded = true;
		}
	}
}

?>