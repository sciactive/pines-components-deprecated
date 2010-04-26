<?php
/**
 * com_user's configuration defaults.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'empty_pw',
		'cname' => 'Empty Passwords',
		'description' => 'Allow users to have empty passwords.',
		'value' => false,
	),
	array(
		'name' => 'show_cur_user',
		'cname' => 'Show Current User',
		'description' => 'Display the name of the current user in the page header.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'create_admin',
		'cname' => 'Create Admin',
		'description' => 'Allow the creation of an admin user.',
		'value' => true,
	),
	array(
		'name' => 'create_admin_secret',
		'cname' => 'Create Admin Secret',
		'description' => 'The secret necessary to create an admin user.',
		'value' => '874jdiv8',
	),
	array (
		'name' => 'max_username_length',
		'cname' => 'Username Max Length',
		'description' => 'The maximum length for usernames. 0 for unlimited.',
		'value' => 0,
	),
	array (
		'name' => 'max_groupname_length',
		'cname' => 'Groupname Max Length',
		'description' => 'The maximum length for groupnames. 0 for unlimited.',
		'value' => 0,
	),
	array (
		'name' => 'min_pin_length',
		'cname' => 'User PIN Min Length',
		'description' => 'The minimum length for user PINs. 0 for no minimum.',
		'value' => 5,
	),
	array(
		'name' => 'resize_logos',
		'cname' => 'Resize Logos',
		'description' => 'Resize the group logos before saving them.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'logo_width',
		'cname' => 'Logo Width',
		'description' => 'If resizing logos, use this width.',
		'value' => 200,
		'peruser' => true,
	),
	array(
		'name' => 'logo_height',
		'cname' => 'Logo Height',
		'description' => 'If resizing logos, use this height.',
		'value' => 75,
		'peruser' => true,
	),
);

?>