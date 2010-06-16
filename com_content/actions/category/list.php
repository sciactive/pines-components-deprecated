<?php
/**
 * List categories.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_content/listcategories') )
	punt_user('You don\'t have necessary permission.', pines_url('com_content', 'category/list'));

$pines->com_content->list_categories();
?>