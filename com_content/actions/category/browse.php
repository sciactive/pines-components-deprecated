<?php
/**
 * Browse a category's pages.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$category = com_content_category::factory((int) $_REQUEST['id']);

if (!isset($category->guid)) {
	pines_notice('Requested category id could not be loaded.');
	return;
}

foreach ($category->pages as $cur_page) {
	if (!isset($cur_page))
		continue;
	$cur_page->print_intro();
}

?>