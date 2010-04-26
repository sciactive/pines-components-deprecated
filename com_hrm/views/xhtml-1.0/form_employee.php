<?php
/**
 * Provides a form for the user to edit a employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Employee' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide employee account details in this form.';
?>
<script type="text/javascript">
	// <![CDATA[
	$(function(){
		var addresses = $("#addresses");
		var addresses_table = $("#addresses_table");
		var address_dialog = $("#address_dialog");

		addresses_table.pgrid({
			pgrid_paginate: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Add Address',
					extra_class: 'icon picon_16x16_actions_list-add',
					selection_optional: true,
					click: function(){
						address_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Remove Address',
					extra_class: 'icon picon_16x16_actions_list-remove',
					click: function(e, rows){
						rows.pgrid_delete();
						update_address();
					}
				}
			]
		});

		// Address Dialog
		address_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 600,
			buttons: {
				"Done": function() {
					var cur_address_type = $("#cur_address_type").val();
					var cur_address_addr1 = $("#cur_address_addr1").val();
					var cur_address_addr2 = $("#cur_address_addr2").val();
					var cur_address_city = $("#cur_address_city").val();
					var cur_address_state = $("#cur_address_state").val();
					var cur_address_zip = $("#cur_address_zip").val();
					if (cur_address_type == "" || cur_address_addr1 == "") {
						alert("Please provide a name and a street address.");
						return;
					}
					var new_address = [{
						key: null,
						values: [
							cur_address_type,
							cur_address_addr1,
							cur_address_addr2,
							cur_address_city,
							cur_address_state,
							cur_address_zip
						]
					}];
					addresses_table.pgrid_add(new_address);
					update_addresses();
					$(this).dialog('close');
				}
			}
		});

		function update_addresses() {
			$("#cur_address_type, #cur_address_addr1, #cur_address_addr2, #cur_address_city, #cur_address_state, #cur_address_zip").val("");
			addresses.val(JSON.stringify(addresses_table.pgrid_get_all_rows().pgrid_export_rows()));
		}

		update_addresses();

		// Attributes
		var attributes = $("#tab_attributes .attributes");
		var attributes_table = $("#tab_attributes .attributes_table");
		var attribute_dialog = $("#tab_attributes .attribute_dialog");

		attributes_table.pgrid({
			pgrid_paginate: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Add Attribute',
					extra_class: 'icon picon_16x16_actions_list-add',
					selection_optional: true,
					click: function(){
						attribute_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Remove Attribute',
					extra_class: 'icon picon_16x16_actions_list-remove',
					click: function(e, rows){
						rows.pgrid_delete();
						update_attributes();
					}
				}
			]
		});

		// Attribute Dialog
		attribute_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 500,
			buttons: {
				"Done": function() {
					var cur_attribute_name = attribute_dialog.find("input[name=cur_attribute_name]").val();
					var cur_attribute_value = attribute_dialog.find("input[name=cur_attribute_value]").val();
					if (cur_attribute_name == "" || cur_attribute_value == "") {
						alert("Please provide both a name and a value for this attribute.");
						return;
					}
					var new_attribute = [{
						key: null,
						values: [
							cur_attribute_name,
							cur_attribute_value
						]
					}];
					attributes_table.pgrid_add(new_attribute);
					update_attributes();
					$(this).dialog('close');
				}
			}
		});

		function update_attributes() {
			attribute_dialog.find("input[name=cur_attribute_name]").val("");
			attribute_dialog.find("input[name=cur_attribute_value]").val("");
			attributes.val(JSON.stringify(attributes_table.pgrid_get_all_rows().pgrid_export_rows()));
		}

		update_attributes();

		$("#employee_tabs").tabs();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="employee_details" action="<?php echo htmlentities(pines_url('com_hrm', 'saveemployee')); ?>">
	<div id="employee_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_general">General</a></li>
			<li><a href="#tab_user_account">User Account</a></li>
			<li><a href="#tab_addresses">Addresses</a></li>
			<li><a href="#tab_attributes">Attributes</a></li>
		</ul>
		<div id="tab_general">
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
				<label><span class="pf-label">First Name</span>
					<input class="pf-field ui-widget-content" type="text" name="name_first" size="24" value="<?php echo $this->entity->name_first; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Middle Name</span>
					<input class="pf-field ui-widget-content" type="text" name="name_middle" size="24" value="<?php echo $this->entity->name_middle; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Last Name</span>
					<input class="pf-field ui-widget-content" type="text" name="name_last" size="24" value="<?php echo $this->entity->name_last; ?>" /></label>
			</div>
			<?php if ($pines->config->com_hrm->ssn_field && gatekeeper('com_hrm/showssn')) { ?>
			<div class="pf-element">
				<label><span class="pf-label">SSN</span>
					<span class="pf-note">Without dashes.</span>
					<input class="pf-field ui-widget-content" type="text" name="ssn" size="24" value="<?php echo $this->entity->ssn; ?>" /></label>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Email</span>
					<input class="pf-field ui-widget-content" type="text" name="email" size="24" value="<?php echo $this->entity->email; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Job Title</span>
					<input class="pf-field ui-widget-content" type="text" name="job_title" size="24" value="<?php echo $this->entity->job_title; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Cell Phone</span>
					<input class="pf-field ui-widget-content" type="text" name="phone_cell" size="24" value="<?php echo format_phone($this->entity->phone_cell); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Work Phone</span>
					<input class="pf-field ui-widget-content" type="text" name="phone_work" size="24" value="<?php echo format_phone($this->entity->phone_work); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Home Phone</span>
					<input class="pf-field ui-widget-content" type="text" name="phone_home" size="24" value="<?php echo format_phone($this->entity->phone_home); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Fax</span>
					<input class="pf-field ui-widget-content" type="text" name="fax" size="24" value="<?php echo format_phone($this->entity->fax); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Schedule Color</span>
					<select class="pf-field ui-widget-content" name="color">
						<option value="blue" <?php echo ($this->entity->color == 'blue') ? 'selected="selected"' : ''; ?>>Blue</option>
						<option value="blueviolet" <?php echo ($this->entity->color == 'blueviolet') ? 'selected="selected"' : ''; ?>>Blue Violet</option>
						<option value="brown" <?php echo ($this->entity->color == 'brown') ? 'selected="selected"' : ''; ?>>Brown</option>
						<option value="cornflowerblue" <?php echo ($this->entity->color == 'cornflowerblue') ? 'selected="selected"' : ''; ?>>Cornflower Blue</option>
						<option value="darkorange" <?php echo ($this->entity->color == 'darkorange') ? 'selected="selected"' : ''; ?>>Dark Orange</option>
						<option value="gainsboro" <?php echo ($this->entity->color == 'gainsboro') ? 'selected="selected"' : ''; ?>>Gainsboro</option>
						<option value="gold" <?php echo ($this->entity->color == 'gold') ? 'selected="selected"' : ''; ?>>Gold</option>
						<option value="greenyellow" <?php echo ($this->entity->color == 'greenyellow') ? 'selected="selected"' : ''; ?>>Green Yellow</option>
						<option value="lightpink" <?php echo ($this->entity->color == 'lightpink') ? 'selected="selected"' : ''; ?>>Light Pink</option>
						<option value="olive" <?php echo ($this->entity->color == 'olive') ? 'selected="selected"' : ''; ?>>Olive</option>
						<option value="red" <?php echo ($this->entity->color == 'red') ? 'selected="selected"' : ''; ?>>Red</option>
						<option value="vanilla" <?php echo ($this->entity->color == 'vanilla') ? 'selected="selected"' : ''; ?>>Vanilla</option>
					</select></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Description</span><br />
				<textarea rows="3" cols="35" class="pf-field peditor" style="width: 100%;" name="description"><?php echo $this->entity->description; ?></textarea>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="tab_user_account">
			<div class="pf-element pf-heading">
				<p>Attach a user to this employee.</p>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Username</span>
					<span class="pf-note">Provide either the username or GUID of the user to attach.</span>
					<span class="pf-note">Blank to remove any currently attached user.</span>
					<input class="pf-field ui-widget-content" type="text" name="username" size="24" value="<?php echo $this->entity->user_account->username; ?>" /></label>
			</div>
			<div class="pf-element">
				<span class="pf-label">Sync User</span>
				<label>
					<input class="pf-field ui-widget-content" type="checkbox" name="sync_user" value="ON" <?php echo ($this->entity->sync_user ? 'checked="checked" ' : ''); ?>/> Keep the user's data in sync with this employee's. (Employee data will overwrite user data.)
				</label>
			</div>
			<fieldset class="pf-group">
				<legend>User Templates</legend>
				<div class="pf-element pf-heading">
					<p>You can use a template to create a user for this employee.</p>
				</div>
				<script type="text/javascript">
					// <![CDATA[
					$(function(){
						var template = $("#employee_details [name=user_template]");
						var pgroupselects = $("#employee_details .user_template_group");
						template.change(function(){
							pgroupselects.hide();
							if (this.value != "null")
								$("#employee_details [name=user_template_group_"+this.value+"]").show();
							return true;
						});
						pgroupselects.hide();
					});
					// ]]>
				</script>
				<div class="pf-element">
					<label><span class="pf-label">User Template</span>
						<select class="pf-field ui-widget-content" name="user_template" size="1">
							<option value="null">-- Select a Template --</option>
							<?php foreach ($this->user_templates as $cur_template) { ?>
							<option value="<?php echo $cur_template->guid; ?>"><?php echo $cur_template->name; ?></option>
							<?php } ?>
						</select></label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Primary Group</span>
						<?php foreach ($this->user_templates as $cur_template) { ?>
						<select class="pf-field ui-widget-content user_template_group" name="user_template_group_<?php echo $cur_template->guid; ?>" size="1">
							<?php if (!isset($cur_template->group)) { ?>
							<option value="null">-- No Primary Group --</option>
							<?php } else { ?>
							<option value="<?php echo $cur_template->group->guid; ?>"><?php echo $cur_template->group->name; ?> [<?php echo $cur_template->group->groupname; ?>]</option>
							<?php echo $pines->user_manager->get_group_tree('<option value="#guid#">#mark##name# [#groupname#]</option>', $pines->user_manager->get_group_array($cur_template->group->guid), null, '', '-> '); ?>
							<?php } ?>
						</select>
						<?php } ?>
					</label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Username</span>
						<input class="pf-field ui-widget-content" type="text" name="user_template_username" size="24" /></label>
				</div>
				<script type="text/javascript">
					// <![CDATA[
					$(function(){
						var password = $("#employee_details [name=user_template_password]");
						var password2 = $("#employee_details [name=user_template_password2]");
						$("#employee_details").submit(function(){
							if (password.val() != password2.val()) {
								alert("Your passwords do not match.");
								return false;
							}
							return true;
						});
					});
					// ]]>
				</script>
				<div class="pf-element">
					<label><span class="pf-label">Password</span>
						<input class="pf-field ui-widget-content" type="password" name="user_template_password" size="24" /></label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Repeat Password</span>
						<input class="pf-field ui-widget-content" type="password" name="user_template_password2" size="24" /></label>
				</div>
			</fieldset>
			<br class="pf-clearing" />
		</div>
		<div id="tab_addresses">
			<div class="pf-element pf-heading">
				<h1>Main Address</h1>
			</div>
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					$(function(){
						var address_us = $("#address_us");
						var address_international = $("#address_international");
						$("#employee_details [name=address_type]").change(function(){
							var address_type = $(this);
							if (address_type.is(":checked") && address_type.val() == "us") {
								address_us.show();
								address_international.hide();
							} else if (address_type.is(":checked") && address_type.val() == "international") {
								address_international.show();
								address_us.hide();
							}
						}).change();
					});
					// ]]>
				</script>
				<span class="pf-label">Address Type</span>
				<label><input class="pf-field ui-widget-content" type="radio" name="address_type" value="us"<?php echo ($this->entity->address_type == 'us') ? ' checked="checked"' : ''; ?> /> US</label>
				<label><input class="pf-field ui-widget-content" type="radio" name="address_type" value="international"<?php echo $this->entity->address_type == 'international' ? ' checked="checked"' : ''; ?> /> International</label>
			</div>
			<div id="address_us" style="display: none;">
				<div class="pf-element">
					<label><span class="pf-label">Address 1</span>
						<input class="pf-field ui-widget-content" type="text" name="address_1" size="24" value="<?php echo $this->entity->address_1; ?>" /></label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Address 2</span>
						<input class="pf-field ui-widget-content" type="text" name="address_2" size="24" value="<?php echo $this->entity->address_2; ?>" /></label>
				</div>
				<div class="pf-element">
					<span class="pf-label">City, State</span>
					<input class="pf-field ui-widget-content" type="text" name="city" size="15" value="<?php echo $this->entity->city; ?>" />
					<select class="pf-field ui-widget-content" name="state">
						<option value="">None</option>
						<?php foreach (array(
								'AL' => 'Alabama',
								'AK' => 'Alaska',
								'AZ' => 'Arizona',
								'AR' => 'Arkansas',
								'CA' => 'California',
								'CO' => 'Colorado',
								'CT' => 'Connecticut',
								'DE' => 'Delaware',
								'DC' => 'DC',
								'FL' => 'Florida',
								'GA' => 'Georgia',
								'HI' => 'Hawaii',
								'ID' => 'Idaho',
								'IL' => 'Illinois',
								'IN' => 'Indiana',
								'IA' => 'Iowa',
								'KS' => 'Kansas',
								'KY' => 'Kentucky',
								'LA' => 'Louisiana',
								'ME' => 'Maine',
								'MD' => 'Maryland',
								'MA' => 'Massachusetts',
								'MI' => 'Michigan',
								'MN' => 'Minnesota',
								'MS' => 'Mississippi',
								'MO' => 'Missouri',
								'MT' => 'Montana',
								'NE' => 'Nebraska',
								'NV' => 'Nevada',
								'NH' => 'New Hampshire',
								'NJ' => 'New Jersey',
								'NM' => 'New Mexico',
								'NY' => 'New York',
								'NC' => 'North Carolina',
								'ND' => 'North Dakota',
								'OH' => 'Ohio',
								'OK' => 'Oklahoma',
								'OR' => 'Oregon',
								'PA' => 'Pennsylvania',
								'RI' => 'Rhode Island',
								'SC' => 'South Carolina',
								'SD' => 'South Dakota',
								'TN' => 'Tennessee',
								'TX' => 'Texas',
								'UT' => 'Utah',
								'VT' => 'Vermont',
								'VA' => 'Virginia',
								'WA' => 'Washington',
								'WV' => 'West Virginia',
								'WI' => 'Wisconsin',
								'WY' => 'Wyoming'
							) as $key => $cur_state) { ?>
						<option value="<?php echo $key; ?>"<?php echo $this->entity->state == $key ? ' selected="selected"' : ''; ?>><?php echo $cur_state; ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Zip</span>
						<input class="pf-field ui-widget-content" type="text" name="zip" size="24" value="<?php echo $this->entity->zip; ?>" /></label>
				</div>
			</div>
			<div id="address_international" style="display: none;">
				<div class="pf-element pf-full-width">
				<label><span class="pf-label">Address</span>
					<span class="pf-field pf-full-width"><textarea class="ui-widget-content" style="width: 100%;" rows="3" cols="35" name="address_international"><?php echo $this->entity->address_international; ?></textarea></span></label>
				</div>
			</div>
			<div class="pf-element pf-heading">
				<h1>Additional Addresses</h1>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Additional Addresses</span>
				<div class="pf-group">
					<table id="addresses_table">
						<thead>
							<tr>
								<th>Type</th>
								<th>Address 1</th>
								<th>Address 2</th>
								<th>City</th>
								<th>State</th>
								<th>Zip</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->entity->addresses as $cur_address) { ?>
							<tr>
								<td><?php echo $cur_address['type']; ?></td>
								<td><?php echo $cur_address['address_1']; ?></td>
								<td><?php echo $cur_address['address_2']; ?></td>
								<td><?php echo $cur_address['city']; ?></td>
								<td><?php echo $cur_address['state']; ?></td>
								<td><?php echo $cur_address['zip']; ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<input type="hidden" id="addresses" name="addresses" size="24" />
				</div>
			</div>
			<div id="address_dialog" title="Add an Address">
				<div class="pf-form">
					<div class="pf-element">
						<label>
							<span class="pf-label">Type</span>
							<input class="pf-field ui-widget-content" type="text" size="24" name="cur_address_type" id="cur_address_type" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Address 1</span>
							<input class="pf-field ui-widget-content" type="text" size="24" name="cur_address_addr1" id="cur_address_addr1" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Address 2</span>
							<input class="pf-field ui-widget-content" type="text" size="24" name="cur_address_addr2" id="cur_address_addr2" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">City, State, Zip</span>
							<input class="pf-field ui-widget-content" type="text" size="8" name="cur_address_city" id="cur_address_city" />
							<input class="pf-field ui-widget-content" type="text" size="2" name="cur_address_state" id="cur_address_state" />
							<input class="pf-field ui-widget-content" type="text" size="5" name="cur_address_zip" id="cur_address_zip" />
						</label>
					</div>
				</div>
				<br class="pf-clearing" />
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="tab_attributes">
			<div class="pf-element pf-full-width">
				<span class="pf-label">Attributes</span>
				<div class="pf-group">
					<table class="attributes_table">
						<thead>
							<tr>
								<th>Name</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->entity->attributes as $cur_attribute) { ?>
							<tr>
								<td><?php echo $cur_attribute['name']; ?></td>
								<td><?php echo $cur_attribute['value']; ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<input type="hidden" name="attributes" />
				</div>
			</div>
			<div class="attribute_dialog" style="display: none;" title="Add an Attribute">
				<div class="pf-form">
					<div class="pf-element">
						<label>
							<span class="pf-label">Name</span>
							<input class="pf-field ui-widget-content" type="text" name="cur_attribute_name" size="24" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content" type="text" name="cur_attribute_value" size="24" />
						</label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_hrm', 'listemployees')); ?>');" value="Cancel" />
	</div>
</form>