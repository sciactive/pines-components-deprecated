<?php
/**
 * com_reports's configuration.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'timespans',
		'cname' => 'Report Timespans',
		'description' => 'Hours separated by dashes, using the 24-hour time format.',
		'value' => array(
			'10-13',
			'13-16',
			'16-19',
			'19-24'
		),
	),
);

?>