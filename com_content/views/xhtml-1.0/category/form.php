<?php
/**
 * Provides a form for the user to edit a category.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Category' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide category details in this form.';
$pines->com_pgrid->load();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_content', 'category/save')); ?>">
	<style type="text/css">
		/* <![CDATA[ */
		#p_muid_pages .page {
			cursor: default;
		}
		/* ]]> */
	</style>
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			$("#p_muid_menu_position").autocomplete({
				source: <?php echo json_encode($pines->info->template->positions); ?>
			});

			// Updating pages.
			var pages_input = $("#p_muid_pages_input");
			$("#p_muid_pages").delegate("a.remove", "click", function(){
				$(this).closest(".page").remove();
				update_pages();
				return false;
			}).sortable({
				update: function(){
					update_pages();
				}
			});

			// Update page order.
			var update_pages = function(){
				var guids = [];
				$(".page", "#p_muid_pages").each(function(){
					guids.push($(this).attr("title"));
				});
				pages_input.val(JSON.stringify(guids));
			};

			update_pages();


			// Conditions
			var conditions = $("#p_muid_form [name=conditions]");
			var conditions_table = $("#p_muid_form .conditions_table");
			var condition_dialog = $("#p_muid_form .condition_dialog");
			var cur_condition = null;

			conditions_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add Condition',
						extra_class: 'picon picon-document-new',
						selection_optional: true,
						click: function(){
							cur_condition = null;
							condition_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Edit Condition',
						extra_class: 'picon picon-document-edit',
						double_click: true,
						click: function(e, rows){
							cur_condition = rows;
							condition_dialog.find("input[name=cur_condition_type]").val(rows.pgrid_get_value(1));
							condition_dialog.find("input[name=cur_condition_value]").val(rows.pgrid_get_value(2));
							condition_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Remove Condition',
						extra_class: 'picon picon-edit-delete',
						click: function(e, rows){
							rows.pgrid_delete();
							update_conditions();
						}
					}
				],
				pgrid_view_height: "300px"
			});

			// Condition Dialog
			condition_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 500,
				buttons: {
					"Done": function(){
						var cur_condition_type = condition_dialog.find("input[name=cur_condition_type]").val();
						var cur_condition_value = condition_dialog.find("input[name=cur_condition_value]").val();
						if (cur_condition_type == "") {
							alert("Please provide a type for this condition.");
							return;
						}
						if (cur_condition == null) {
							// Is this a duplicate type?
							var dupe = false;
							conditions_table.pgrid_get_all_rows().each(function(){
								if (dupe) return;
								if ($(this).pgrid_get_value(1) == cur_condition_type)
									dupe = true;
							});
							if (dupe) {
								pines.notice('There is already a condition of that type.');
								return;
							}
							var new_condition = [{
								key: null,
								values: [
									cur_condition_type,
									cur_condition_value
								]
							}];
							conditions_table.pgrid_add(new_condition);
						} else {
							cur_condition.pgrid_set_value(1, cur_condition_type);
							cur_condition.pgrid_set_value(2, cur_condition_value);
						}
						$(this).dialog('close');
					}
				},
				close: function(){
					update_conditions();
				}
			});

			var update_conditions = function(){
				condition_dialog.find("input[name=cur_condition_type]").val("");
				condition_dialog.find("input[name=cur_condition_value]").val("");
				conditions.val(JSON.stringify(conditions_table.pgrid_get_all_rows().pgrid_export_rows()));
			};

			update_conditions();

			condition_dialog.find("input[name=cur_condition_type]").autocomplete({
				"source": <?php echo (string) json_encode((array) array_keys($pines->depend->checkers)); ?>
			});

			$("#p_muid_category_tabs").tabs();
		});
		// ]]>
	</script>
	<div id="p_muid_category_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_conditions">Conditions</a></li>
		</ul>
		<div id="p_muid_tab_general">
			<div class="pf-element pf-full-width">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						var alias = $("#p_muid_form [name=alias]");
						$("#p_muid_form [name=name]").change(function(){
							if (alias.val() == "")
								alias.val($(this).val().replace(/[^\w\d\s-.]/g, '').replace(/\s/g, '-').toLowerCase());
						}).blur(function(){
							$(this).change();
						}).focus(function(){
							if (alias.val() == $(this).val().replace(/[^\w\d\s-.]/g, '').replace(/\s/g, '-').toLowerCase())
								alias.val("");
						});
					});
					// ]]>
				</script>
				<label>
					<span class="pf-label">Name</span>
					<div class="pf-group pf-full-width">
						<input class="pf-field ui-widget-content ui-corner-all" style="width: 100%;" type="text" name="name" value="<?php echo htmlspecialchars($this->entity->name); ?>" />
					</div>
				</label>
			</div>
			<div class="pf-element pf-full-width">
				<label>
					<span class="pf-label">Alias</span>
					<div class="pf-group pf-full-width">
						<input class="pf-field ui-widget-content ui-corner-all" style="width: 100%;" type="text" name="alias" value="<?php echo htmlspecialchars($this->entity->alias); ?>" onkeyup="this.value=this.value.replace(/[^\w\d-.]/g, '_');" />
					</div>
				</label>
			</div>
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<?php if (isset($this->entity->user)) { ?>
				<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
				<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
				<?php } ?>
				<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
				<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Title</span>
					<input class="pf-field" type="checkbox" name="show_title" value="ON"<?php echo $this->entity->show_title ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Menu</span>
					<input class="pf-field" type="checkbox" name="show_menu" value="ON"<?php echo $this->entity->show_menu ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Menu Position</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_menu_position" name="menu_position" size="24" value="<?php echo htmlspecialchars($this->entity->menu_position); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Pages in Menu</span>
					<input class="pf-field" type="checkbox" name="show_pages_in_menu" value="ON"<?php echo $this->entity->show_pages_in_menu ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Breadcrumbs</span>
					<input class="pf-field" type="checkbox" name="show_breadcrumbs" value="ON"<?php echo $this->entity->show_breadcrumbs ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Parent</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="parent">
						<option value="null">-- No Parent --</option>
						<?php
						/**
						 * Print children of a category into the select box.
						 * @param com_content_category $parent The parent category.
						 * @param com_content_category|null $entity The current category.
						 * @param string $prefix The prefix to insert before names.
						 */
						function com_content__category_form_children($parent, $entity, $prefix = '->') {
							foreach ($parent->children as $category) {
								if ($category->is($entity))
									continue;
								?>
								<option value="<?php echo $category->guid; ?>"<?php echo $category->is($entity->parent) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars("{$prefix} {$category->name}"); ?></option>
								<?php
								if ($category->children)
									com_content__category_form_children($category, $entity, "{$prefix}->");
							}
						}
						foreach ($this->categories as $category) {
							if ($category->is($this->entity))
								continue;
							?>
							<option value="<?php echo $category->guid; ?>"<?php echo $category->is($this->entity->parent) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($category->name); ?></option>
							<?php
							if ($category->children)
								com_content__category_form_children($category, $this->entity);
						} ?>
					</select>
				</label>
			</div>
			<div class="pf-element">
				<span class="pf-label">Pages</span>
				<span class="pf-note">These pages are assigned to this category. Drag and drop them into the desired order.</span>
				<div class="pf-group">
					<?php if ($this->entity->pages) { ?>
					<ol id="p_muid_pages" class="pf-field ui-widget-content ui-corner-all" style="padding: 1em 1em 1em 3em; min-width: 300px;">
						<?php foreach ($this->entity->pages as $cur_page) { ?>
						<li class="page" title="<?php echo htmlspecialchars($cur_page->guid); ?>"><?php echo htmlspecialchars($cur_page->name); ?> <a href="<?php echo htmlspecialchars(pines_url('com_content', 'page/edit', array('id' => $cur_page->guid))); ?>" onclick="window.open(this.href); return false;">Edit</a> <a href="#" class="remove">Remove</a></li>
						<?php } ?>
					</ol>
					<?php } else { ?>
					<span class="pf-field">There are no pages in this category.</span>
					<?php } ?>
				</div>
				<input type="hidden" name="pages" id="p_muid_pages_input" value="" />
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_conditions">
			<div class="pf-element pf-heading">
				<h1>Page Conditions</h1>
				<p>Users will only see this page if these conditions are met.</p>
			</div>
			<div class="pf-element pf-full-width">
				<table class="conditions_table">
					<thead>
						<tr>
							<th>Type</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($this->entity->conditions)) foreach ($this->entity->conditions as $cur_key => $cur_value) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_key); ?></td>
							<td><?php echo htmlspecialchars($cur_value); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="conditions" />
			</div>
			<div class="condition_dialog" style="display: none;" title="Add a Condition">
				<div class="pf-form">
					<div class="pf-element">
						<span class="pf-label">Detected Types</span>
						<span class="pf-note">These types were detected on this system.</span>
						<div class="pf-group">
							<div class="pf-field"><em><?php echo htmlspecialchars(implode(', ', array_keys($pines->depend->checkers))); ?></em></div>
						</div>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Type</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_type" size="24" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_value" size="24" /></label>
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
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_content', 'category/list')); ?>');" value="Cancel" />
	</div>
</form>