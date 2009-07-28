<?php
/**
 * com_about's configuration.
 *
 * @package Pines
 * @subpackage com_about
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->com_about = new DynamicConfig;

/**
 * Description of your installation.
 */
$config->com_about->description = "This is the default installation for ".$config->option_title.".";

/**
 * Whether to show Pines' description underneath yours.
 */
$config->com_about->describe_self = true;

?>