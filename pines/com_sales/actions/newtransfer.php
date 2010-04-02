<?php
/**
 * Provide a form to create a new transfer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managestock') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'newtransfer', array('id' => $_REQUEST['id']), false));

$list = explode(',', $_REQUEST['id']);

if (empty($list)) {
	pines_notice('No inventory specified for transfer!');
	return;
}

$entity = com_sales_transfer::factory();
$entity->stock = array();
if (is_array($list)) {
	foreach ($list as $cur_stock_guid) {
		$cur_stock = com_sales_stock::factory($cur_stock_guid);
		if (isset($cur_stock->guid))
			$entity->stock[] = $cur_stock;
	}
}
$entity->print_form();

?>