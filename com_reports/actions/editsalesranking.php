<?php
/**
 * Provide a form to edit a sales ranking.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_reports/editsalesranking') )
		punt_user('You don\'t have necessary permission.', pines_url('com_reports', 'editsalesranking', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_reports/newsalesranking') )
		punt_user('You don\'t have necessary permission.', pines_url('com_reports', 'editsalesranking'));
}

$entity = com_reports_sales_ranking::factory((int) $_REQUEST['id']);

$entity->print_form();

?>