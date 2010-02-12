<?php
/**
 * Delete a countsheet.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletecountsheet') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcountsheets', null, false));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_sheet) {
	$cur_entity = com_sales_countsheet::factory((int) $cur_sheet);
	if ( is_null($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_sheet;
}
if (empty($failed_deletes)) {
	display_notice('Selected countsheet(s) deleted successfully.');
} else {
	display_error('Could not delete countsheets with given IDs: '.$failed_deletes);
	display_notice('Note that countsheets cannot be deleted after items have been received on them.');
}

$pines->com_sales->list_countsheets();
?>