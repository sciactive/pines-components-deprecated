<?php
/**
 * Provides a form for the user to edit a group.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Group' : 'Editing ['.htmlentities($this->entity->groupname).']';
$this->note = 'Provide group details in this form.';
?>
<script type="text/javascript">
	// <![CDATA[
	$(function(){
		$("#group_tabs").tabs();
	});
	// ]]>
</script>
<form enctype="multipart/form-data" class="pform" method="post" id="group_details" action="<?php echo pines_url('com_user', 'savegroup'); ?>">
	<div id="group_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_general">General</a></li>
			<li><a href="#tab_logo">Logo</a></li>
			<li><a href="#tab_location">Location</a></li>
			<li><a href="#tab_abilities">Abilities</a></li>
		</ul>
		<div id="tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
					<?php if (isset($this->entity->uid)) { ?>
				<span>Created By: <span class="date"><?php echo $pines->user_manager->get_username($this->entity->uid); ?></span></span>
				<br />
					<?php } ?>
				<span>Created On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_cdate); ?></span></span>
				<br />
				<span>Modified On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_mdate); ?></span></span>
			</div>
			<?php } ?>
			<div class="element">
				<label><span class="label">Group Name</span>
					<input class="field ui-widget-content" type="text" name="groupname" size="24" value="<?php echo $this->entity->groupname; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Display Name</span>
					<input class="field ui-widget-content" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Email</span>
					<input class="field ui-widget-content" type="text" name="email" size="24" value="<?php echo $this->entity->email; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Phone</span>
					<input class="field ui-widget-content" type="text" name="phone" size="24" value="<?php echo pines_phone_format($this->entity->phone); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="element">
				<label><span class="label">Fax</span>
					<input class="field ui-widget-content" type="text" name="fax" size="24" value="<?php echo pines_phone_format($this->entity->fax); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="element">
				<label><span class="label">Timezone</span>
					<span class="note">Users in this group will inherit this timezone. Primary group has priority over secondary groups.</span>
					<select class="field ui-widget-content" name="timezone" size="1">
						<option value="">--System Default--</option>
						<?php $tz = DateTimeZone::listIdentifiers();
						sort($tz);
						foreach ($tz as $cur_tz) { ?>
						<option value="<?php echo $cur_tz; ?>"<?php echo $this->entity->timezone == $cur_tz ? ' selected="selected"' : ''; ?>><?php echo $cur_tz; ?></option>
						<?php } ?>
					</select></label>
			</div>
			<div class="element">
				<label><span class="label">Parent</span>
					<select class="field ui-widget-content" name="parent" size="1">
						<option value="none">--No Parent--</option>
						<?php echo $pines->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->entity->parent); ?>
					</select></label>
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_logo">
			<div class="element">
				<span class="label"><?php echo (isset($this->entity->logo)) ? 'Currently Set Logo' : 'Inherited Logo'; ?></span>
				<div class="group">
					<span class="field"><img src="<?php echo $this->entity->get_logo(); ?>" alt="Group Logo" /></span>
					<?php if (isset($this->entity->logo)) { ?>
					<br />
					<label><span class="field"><input class="field ui-widget-content" type="checkbox" name="remove_logo" value="ON" />Remove this logo.</span></label>
					<?php } ?>
				</div>
			</div>
			<div class="element">
				<label><span class="label">Change Logo</span>
					<input class="field ui-widget-content" type="file" name="image" /></label>
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_location">
			<div class="element">
				<script type="text/javascript">
					// <![CDATA[
					$(function(){
						var address_us = $("#address_us");
						var address_international = $("#address_international");
						$("#group_details [name=address_type]").change(function(){
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
				<span class="label">Address Type</span>
				<label><input class="field ui-widget-content" type="radio" name="address_type" value="us"<?php echo ($this->entity->address_type == 'us') ? ' checked="checked"' : ''; ?> /> US</label>
				<label><input class="field ui-widget-content" type="radio" name="address_type" value="international"<?php echo $this->entity->address_type == 'international' ? ' checked="checked"' : ''; ?> /> International</label>
			</div>
			<div id="address_us" style="display: none;">
				<div class="element">
					<label><span class="label">Address 1</span>
						<input class="field ui-widget-content" type="text" name="address_1" size="24" value="<?php echo $this->entity->address_1; ?>" /></label>
				</div>
				<div class="element">
					<label><span class="label">Address 2</span>
						<input class="field ui-widget-content" type="text" name="address_2" size="24" value="<?php echo $this->entity->address_2; ?>" /></label>
				</div>
				<div class="element">
					<span class="label">City, State</span>
					<input class="field ui-widget-content" type="text" name="city" size="15" value="<?php echo $this->entity->city; ?>" />
					<select class="field ui-widget-content" name="state">
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
				<div class="element">
					<label><span class="label">Zip</span>
						<input class="field ui-widget-content" type="text" name="zip" size="24" value="<?php echo $this->entity->zip; ?>" /></label>
				</div>
			</div>
			<div id="address_international" style="display: none;">
				<div class="element full_width">
					<label><span class="label">Address</span>
						<span class="field full_width"><textarea style="width: 100%;" rows="3" cols="35" name="address_international"><?php echo $this->entity->address_international; ?></textarea></span></label>
				</div>
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_abilities">
			<?php if ( $this->display_abilities ) { ?>
			<input type="hidden" name="abilities" value="true" />
			<script type="text/javascript">
				// <![CDATA[
				$(function(){
					$("#group_details .abilities_accordian").accordion({autoHeight: false, collapsible: true});
				});
				// ]]>
			</script>
			<br class="spacer" />
			<?php foreach ($this->sections as $cur_section) {
				$section_abilities = $pines->ability_manager->get_abilities($cur_section);
				if ( !count($section_abilities) ) continue; ?>
			<div class="abilities_accordian">
				<h3><a href="#"><?php echo $cur_section; ?></a></h3>
				<div style="max-height: 250px">
					<div class="element">
						<?php foreach ($section_abilities as $cur_ability) { ?>
						<label>
							<input class="ui-widget-content" type="checkbox" name="<?php echo $cur_section; ?>[]" value="<?php echo $cur_ability['ability']; ?>" <?php echo (array_search("{$cur_section}/{$cur_ability['ability']}", $this->entity->abilities) !== false) ? 'checked="checked" ' : ''; ?>/>
							<?php echo $cur_ability['title']; ?>&nbsp;<small><?php echo $cur_ability['description']; ?></small>
						</label>
						<br class="spacer" />
						<?php } ?>
					</div>
				</div>
			</div>
			<?php } ?>
			<?php } else { ?>
			<div class="element">
				<p>You do not have sufficient privileges to edit abilities.</p>
			</div>
			<?php } ?>
			<br class="spacer" />
		</div>
	</div>

	<div class="element buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_user', 'listgroups'); ?>');" value="Cancel" />
	</div>
</form>