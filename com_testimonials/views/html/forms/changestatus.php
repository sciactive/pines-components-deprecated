<?php
/**
 * Display a form to change the status on a testimonial.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

?>
<style type="text/css">
	#p_muid_form .form-alerts {
		line-height:13px;
		padding:7px;
		text-align:center;
		margin-bottom:7px;
		clear:both;
	}
</style>
<form class="pf-form" id="p_muid_form" action="">
	<div class="row-fluid">
		<h4 style="text-align: center;">Approve / Deny Testimonials</h4>
		<p style="text-align: center;">Used with the testimonial module to display feedback.</p>
	</div>
	<?php if (!$this->entity->share) { ?>
	<div class="row-fluid">
		<div class="span10 offset1"><div style="text-align: center; background: #aa0000; color: #fff; padding: 3px; font-size: 12px; font-weight:bold;" class="">This customer does not want to share their feedback and therefore cannot be used as a displayed testimonial.</div></div>
	</div>
	<?php } ?>
	<div class="row-fluid">
		<div class="span10 offset1">
			<div style="background: #0088cc; padding: 4px; color: #fff; text-transform: uppercase; font-size: 12px; text-align:center; font-weight:bold;">Original Feedback - Cannot be Edited.</div>
			<blockquote style="background: #eee; padding: 20px 20px 10px;" class="clearfix">
				<p>"<?php echo htmlspecialchars($this->entity->feedback); ?>"</p>
				<small><?php echo $this->entity->create_author(); ?></small>
				<div class="pull-right rating-container"> 
					<span class="star-container">
					<?php if (isset($this->entity->rating)) {
						for ($c = 1; $c <= 5; $c++) { 
							if ((int) $this->entity->rating >= $c) { ?>
							<span class="star"><i class="icon-star"></i></span>
						<?php } else { ?>
							<span class="star"><i class="icon-star-empty"></i></span>
						<?php } 
						}
					} ?>
					</span>
				</div>
			</blockquote>
		</div>
	</div>
	<?php if (!empty($this->entity->quotefeedback)) { ?>
	<div class="row-fluid">
		<div class="span10 offset1">
			<div style="background: #557700; padding: 4px; color: #fff; text-transform: uppercase; font-size: 12px; text-align:center; font-weight:bold;">Currently Using Quoted Version.</div>
			<blockquote style="background: #eee; padding: 20px;">
				<p>"<?php echo htmlspecialchars($this->entity->quotefeedback); ?>"</p>
				<small><?php echo $this->entity->create_author(); ?></small>
			</blockquote>
		</div>
	</div>
	<?php } ?>
	
	<div class="row-fluid">
        <div class="span10 offset1">
            <hr style="margin: 5px 0 10px;">
            <div class="clearfix">
				<span style="width: 35%; display:inline-block; font-weight:bold; text-transform: uppercase;">Approve / Deny</span>
				<select name="status" style="width: 50%; float:right; box-sizing: border-box; -moz-box-sizing: content-box; height: 20px;">
					<option value=""></option>
					<option value="">Select</option>
					<option value="approved">Approve</option>
					<option value="denied">Deny</option>
				</select>
			</div>
			<?php if (gatekeeper('com_testimonials/quotetestimonials')) { ?>
			<hr style="margin: 10px 0 10px;">
            <span style="width: 35%; display:inline-block; font-weight:bold; text-transform: uppercase;">
				Quote Feedback
				<span class="" style="font-size: 10px; display:inline-block; font-weight: normal; text-transform: none;">If desired, you can quote/edit part of the feedback for the testimonial instead.</span>
			</span>
			<textarea style="width: 50%; float: right; vertical-align:top;" name="quotefeedback"><?php echo isset($this->entity->quotefeedback) ? htmlspecialchars($this->entity->quotefeedback) : ''; ?></textarea>
			<?php } ?>
		</div>
    </div>
</form>