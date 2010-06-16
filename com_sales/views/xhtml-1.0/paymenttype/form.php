<?php
/**
 * Provides a form for the user to edit a payment type.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Payment Type' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide payment type details in this form.';
?>
<form class="pf-form" method="post" id="payment_type_details" action="<?php echo htmlentities(pines_url('com_sales', 'paymenttype/save')); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo "{$this->entity->user->name} [{$this->entity->user->username}]"; ?></span></div>
		<div>Group: <span class="date"><?php echo "{$this->entity->group->name} [{$this->entity->group->groupname}]"; ?></span></div>
		<?php } ?>
		<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
		<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label><span class="pf-label">Name</span>
			<input class="pf-field ui-widget-content" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Enabled</span>
			<input class="pf-field ui-widget-content" type="checkbox" name="enabled" size="24" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Kick Drawer</span>
			<span class="pf-note">If set, when this payment type is used, the cash drawer will be kicked open.</span>
			<input class="pf-field ui-widget-content" type="checkbox" name="kick_drawer" size="24" value="ON"<?php echo $this->entity->kick_drawer ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Change Type</span>
			<span class="pf-note">If set, change will be given from this payment type. Usually "Cash" is the change type.</span>
			<input class="pf-field ui-widget-content" type="checkbox" name="change_type" size="24" value="ON"<?php echo $this->entity->change_type ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Minimum Charge</span>
			<span class="pf-note">The minimum charge in dollars that this payment type will accept.</span>
			<input class="pf-field ui-widget-content" type="text" name="minimum" size="24" value="<?php echo $this->entity->minimum; ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Maximum Charge</span>
			<span class="pf-note">The maximum charge in dollars that this payment type will accept.</span>
			<input class="pf-field ui-widget-content" type="text" name="maximum" size="24" value="<?php echo $this->entity->maximum; ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Processing Type</span>
			<span class="pf-note">This will determine how the payment is approved and processed.</span>
			<select class="pf-field ui-widget-content" name="processing_type" size="6">
				<?php foreach ($this->processing_types as $cur_type) { ?>
				<option value="<?php echo $cur_type['name']; ?>" title="<?php echo $cur_type['description']; ?>"<?php echo $this->entity->processing_type == $cur_type['name'] ? ' selected="selected"' : ''; ?>><?php echo $cur_type['cname']; ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'paymenttype/list')); ?>');" value="Cancel" />
	</div>
</form>