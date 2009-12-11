<?php
/**
 * com_sales's common file.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->ability_manager->add('com_sales', 'receive', 'Receive Inventory', 'User can receive inventory into their stock.');
$config->ability_manager->add('com_sales', 'managestock', 'Manage Stock', 'User can transfer and adjust stock.');
$config->ability_manager->add('com_sales', 'managesales', 'Manage Sales', 'User can manage sales.');
$config->ability_manager->add('com_sales', 'newsale', 'Create Sales', 'User can create new sales.');
$config->ability_manager->add('com_sales', 'editsale', 'Edit Sales', 'User can edit current sales.');
$config->ability_manager->add('com_sales', 'deletesale', 'Delete Sales', 'User can delete current sales.');
$config->ability_manager->add('com_sales', 'managemanufacturers', 'Manage Manufacturers', 'User can manage manufacturers.');
$config->ability_manager->add('com_sales', 'newmanufacturer', 'Create Manufacturers', 'User can create new manufacturers.');
$config->ability_manager->add('com_sales', 'editmanufacturer', 'Edit Manufacturers', 'User can edit current manufacturers.');
$config->ability_manager->add('com_sales', 'deletemanufacturer', 'Delete Manufacturers', 'User can delete current manufacturers.');
$config->ability_manager->add('com_sales', 'managevendors', 'Manage Vendors', 'User can manage vendors.');
$config->ability_manager->add('com_sales', 'newvendor', 'Create Vendors', 'User can create new vendors.');
$config->ability_manager->add('com_sales', 'editvendor', 'Edit Vendors', 'User can edit current vendors.');
$config->ability_manager->add('com_sales', 'deletevendor', 'Delete Vendors', 'User can delete current vendors.');
$config->ability_manager->add('com_sales', 'manageshippers', 'Manage Shippers', 'User can manage shippers.');
$config->ability_manager->add('com_sales', 'newshipper', 'Create Shippers', 'User can create new shippers.');
$config->ability_manager->add('com_sales', 'editshipper', 'Edit Shippers', 'User can edit current shippers.');
$config->ability_manager->add('com_sales', 'deleteshipper', 'Delete Shippers', 'User can delete current shippers.');
$config->ability_manager->add('com_sales', 'managecustomers', 'Manage Customers', 'User can manage customers.');
$config->ability_manager->add('com_sales', 'newcustomer', 'Create Customers', 'User can create new customers.');
$config->ability_manager->add('com_sales', 'editcustomer', 'Edit Customers', 'User can edit current customers.');
$config->ability_manager->add('com_sales', 'deletecustomer', 'Delete Customers', 'User can delete current customers.');
$config->ability_manager->add('com_sales', 'managetaxfees', 'Manage Taxes/Fees', 'User can manage taxes/fees.');
$config->ability_manager->add('com_sales', 'newtaxfee', 'Create Taxes/Fees', 'User can create new taxes/fees.');
$config->ability_manager->add('com_sales', 'edittaxfee', 'Edit Taxes/Fees', 'User can edit current taxes/fees.');
$config->ability_manager->add('com_sales', 'deletetaxfee', 'Delete Taxes/Fees', 'User can delete current taxes/fees.');
$config->ability_manager->add('com_sales', 'managepaymenttypes', 'Manage Payment Types', 'User can manage payment types.');
$config->ability_manager->add('com_sales', 'newpaymenttype', 'Create Payment Types', 'User can create new payment types.');
$config->ability_manager->add('com_sales', 'editpaymenttype', 'Edit Payment Types', 'User can edit current payment types.');
$config->ability_manager->add('com_sales', 'deletepaymenttype', 'Delete Payment Types', 'User can delete current payment types.');
$config->ability_manager->add('com_sales', 'manageproducts', 'Manage Products', 'User can manage products.');
$config->ability_manager->add('com_sales', 'newproduct', 'Create Products', 'User can create new products.');
$config->ability_manager->add('com_sales', 'editproduct', 'Edit Products', 'User can edit current products.');
$config->ability_manager->add('com_sales', 'deleteproduct', 'Delete Products', 'User can delete current products.');
$config->ability_manager->add('com_sales', 'managepos', 'Manage Purchase Orders', 'User can manage POs.');
$config->ability_manager->add('com_sales', 'newpo', 'Create Purchase Orders', 'User can create new POs.');
$config->ability_manager->add('com_sales', 'editpo', 'Edit Purchase Orders', 'User can edit current POs.');
$config->ability_manager->add('com_sales', 'deletepo', 'Delete Purchase Orders', 'User can delete current POs.');
$config->ability_manager->add('com_sales', 'managecategories', 'Manage Categories', 'User can manage categories.');
$config->ability_manager->add('com_sales', 'viewcategories', 'View Categories', 'User can view categories.');

?>