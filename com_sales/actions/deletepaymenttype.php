<?php
/**
 * Delete a payment type.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletepaymenttype') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listpaymenttypes', null, false));
	return;
}

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_payment_type) {
	if ( !$config->run_sales->delete_payment_type($cur_payment_type) )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_payment_type;
}
if (empty($failed_deletes)) {
	display_notice('Selected payment type(s) deleted successfully.');
} else {
	display_error('Could not delete payment types with given IDs: '.$failed_deletes);
}

$config->run_sales->list_payment_types();
?>