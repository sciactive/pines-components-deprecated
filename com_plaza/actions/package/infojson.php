<?php
/**
 * Return a JSON object of package info.
 *
 * @package Components\plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_plaza/listpackages') )
	punt_user(null, pines_url('com_plaza', 'package/list'));

$pines->page->override = true;
header('Content-Type: application/json');
if ($_REQUEST['local'] == 'true') {
	$package = $pines->com_package->db['packages'][$_REQUEST['name']];
} else {
	$index = $pines->com_plaza->get_index(null, $_REQUEST['publisher']);
	$package = $index['packages'][$_REQUEST['name']];
}
if (isset($package))
	$pines->page->override_doc(json_encode($package));

?>