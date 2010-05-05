<?php
/**
 * Displays a welcome note to the user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Welcome to {$pines->config->option_title}";
$this->note = 'You are now registered and logged in.';
?>
<div>
	<?php echo $pines->config->com_user->reg_message_welcome; ?>
</div>