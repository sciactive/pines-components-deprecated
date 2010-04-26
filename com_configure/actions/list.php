<?php
/**
 * Show list of configurable components.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ($_REQUEST['peruser']) {
	if ( !gatekeeper('com_configure/editperuser') && !gatekeeper('com_configure/viewperuser') )
		punt_user('You don\'t have necessary permission.', pines_url('com_configure', 'list', $_GET));
} else {
	if ( !gatekeeper('com_configure/edit') && !gatekeeper('com_configure/view') )
		punt_user('You don\'t have necessary permission.', pines_url('com_configure', 'list', $_GET));
}

if ($_REQUEST['peruser']) {
	$user = null;
	if ($_REQUEST['type'] == 'user') {
		$user = user::factory((int) $_REQUEST['id']);
		if (!isset($user->guid))
			$user = null;
	} elseif ($_REQUEST['type'] == 'group') {
		$user = group::factory((int) $_REQUEST['id']);
		if (!isset($user->guid))
			$user = null;
	}
	$pines->configurator->list_components_peruser($user);
} else {
	$pines->configurator->list_components();
}

?>