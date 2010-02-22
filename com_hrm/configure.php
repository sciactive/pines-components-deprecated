<?php
/**
 * com_hrm's configuration.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'global_employees',
		'cname' => 'Globalize Employees',
		'description' => 'Ensure that every user can access all employees by setting the "other" access control to read.',
		'value' => true,
	),
	array(
		'name' => 'ssn_field',
		'cname' => 'SSN Field',
		'description' => 'Allow Pines to store a Social Security Number for employees.',
		'value' => true,
	),
	array(
		'name' => 'allow_attach',
		'cname' => 'Allow User Attach',
		'description' => 'Allow users to be attached to employees.',
		'value' => true,
	),
	array(
		'name' => 'user_templates',
		'cname' => 'User Templates',
		'description' => 'Users to use as templates to allow new users to be created by the HRM. Comma seperated list of Name=ID pairs. Such as "Manager=11,Inventory=12,Salesman=13".',
		'value' => '',
	),
	array(
		'name' => 'employee_departments',
		'cname' => 'Department Names & Colors',
		'description' => 'These groups will show up in the calendar with their associated colors.',
		'value' => 'Company Wide:cornflowerblue, Corporate:gold, IT:blueviolet, Sales:olive',
	),
);

?>