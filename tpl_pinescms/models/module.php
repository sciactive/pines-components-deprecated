<?php
/**
 * Template for a module.
 *
 * @package Pines
 * @subpackage tpl_pinescms
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>
<div class="module <?php echo htmlspecialchars($this->classes); ?>">
	<?php if ($this->show_title && (!empty($this->title) || !empty($this->note))) { ?>
	<div class="module_title">
		<?php if (!empty($this->title)) { ?>
			<h2><?php echo $this->title; ?></h2>
		<?php } ?>
		<?php if (!empty($this->note)) { ?>
			<p><?php echo $this->note; ?></p>
		<?php } ?>
	</div>
	<?php } ?>
	<div class="module_content">
		<?php echo $this->content; ?>
	</div>
</div>
<div style="font-size: 0px; height: 0; clear: both;">&nbsp;</div>