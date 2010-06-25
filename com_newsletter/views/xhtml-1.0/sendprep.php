<?php
/**
 * Provides a form with options for sending a newsletter.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Sending {$this->mail->name}";
$pines->com_jstree->load();
?>
<script type='text/javascript'>
	// <![CDATA[
	pines(function(){
		// Location Tree
		var location = $("#p_muid_form [name=location]");
		$("#p_muid_form .location_tree")
		.bind("before.jstree", function (e, data) {
			if (data.func == "parse_json" && "args" in data && 0 in data.args && "attr" in data.args[0] && "id" in data.args[0].attr)
				data.args[0].attr.id = "p_muid_"+data.args[0].attr.id;
		})
		.bind("select_node.jstree", function(e, data){
			location.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
		})
		.jstree({
			"plugins" : [ "themes", "json_data", "ui" ],
			"json_data" : {
				"ajax" : {
					"dataType" : "json",
					"url" : "<?php echo pines_url('com_jstree', 'groupjson'); ?>"
				}
			},
			"ui" : {
				"select_limit" : 1,
				"initially_select" : ["p_muid_<?php echo $_SESSION['user']->group->guid; ?>"]
			}
		});
	});
	// ]]>
</script>
<form class="pf-form" id="p_muid_form" method="post" action="<?php echo htmlentities(pines_url('com_newsletter', 'send')); ?>">
	<div class="pf-element">
		<label><span class="pf-label">From Address</span>
		<input class="pf-field ui-widget-content" type="text" name="from" size="24" value="<?php echo htmlentities($pines->config->com_newsletter->default_from); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Reply to Address</span>
		<input class="pf-field ui-widget-content" type="text" name="replyto" size="24" value="<?php echo htmlentities($pines->config->com_newsletter->default_reply_to); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Subject</span>
		<input class="pf-field ui-widget-content" type="text" name="subject" size="24" value="<?php echo htmlentities($this->mail->subject); ?>" /></label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Select Groups</span>
		<span class="pf-note">Click group name to select children as well.</span>
	</div>
	<div class="pf-element location_tree"></div>
	<div class="pf-element pf-heading">
		<h1>Options</h1>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Include a link to the mail's web address.</span>
		<span class="pf-note">For online viewing.</span>
		<input class="pf-field ui-widget-content" type="checkbox" name="include_permalink" checked /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="mail_id" value="<?php echo $_REQUEST['mail_id']; ?>" />
		<input type="hidden" name="location" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_newsletter', 'list')); ?>');" value="Cancel" />
	</div>
</form>