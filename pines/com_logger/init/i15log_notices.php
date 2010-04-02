<?php
/**
 * Take over the notice functions to log them.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_logger->log_errors) {
	/**
	 * Log a displayed error.
	 *
	 * @param string $args The error text.
	 */
	function com_logger_log_error($args) {
		global $pines;
		$pines->log_manager->log($args[0], 'error');
		return $args;
	}
	$pines->hook->add_callback('$pines->page->error', -15, 'com_logger_log_error');
}

if ($pines->config->com_logger->log_notices) {
	/**
	 * Log a displayed notice.
	 *
	 * @param string $args The notice text.
	 */
	function com_logger_log_notice($args) {
		global $pines;
		$pines->log_manager->log($args[0], 'notice');
		return $args;
	}
	$pines->hook->add_callback('$pines->page->notice', -15, 'com_logger_log_notice');
}

?>