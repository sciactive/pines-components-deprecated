<?php
/**
 * List products without categories.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/editproduct') )
	punt_user(null, pines_url('com_reports', 'maintenance/prodswocats'));

$pines->com_reports->products_wo_categories();

?>