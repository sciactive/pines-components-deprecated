<?php
/**
 * Provide a form to edit a page.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_content/editpage') )
		punt_user(null, pines_url('com_content', 'page/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_content/newpage') )
		punt_user(null, pines_url('com_content', 'page/edit'));
}

$entity = com_content_page::factory((int) $_REQUEST['id']);
$entity->print_form();

?>