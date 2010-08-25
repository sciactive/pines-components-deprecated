<?php
/**
 * Provides a form for the user to edit an page.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Page' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide page details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
$pines->com_ptags->load();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_content', 'page/save')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			$("#p_muid_menu_position").autocomplete({
				source: <?php echo json_encode($pines->info->template->positions); ?>
			});

			$("#p_muid_page_tabs").tabs();
		});
		// ]]>
	</script>
	<div id="p_muid_page_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_categories">Categories</a></li>
			<li><a href="#p_muid_tab_advanced">Advanced</a></li>
		</ul>
		<div id="p_muid_tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
				<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
			</div>
			<?php } ?>
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
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show on Front Page</span>
					<span class="pf-note">Use this to show the full content.</span>
					<input class="pf-field" type="checkbox" name="show_front_page" value="ON"<?php echo $this->entity->show_front_page ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Tags</span>
				<div class="pf-group">
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="content_tags" size="24" value="<?php echo htmlspecialchars(implode(',', $this->entity->content_tags)); ?>" />
					<script type="text/javascript">
						// <![CDATA[
						pines(function(){
							$("#p_muid_form [name=content_tags]").ptags();
						});
						// ]]>
					</script>
				</div>
			</div>
			<div class="pf-element pf-heading">
				<h1>Intro</h1>
			</div>
			<div class="pf-element pf-full-width">
				<textarea rows="3" cols="35" class="peditor" style="width: 100%;" name="intro"><?php echo $this->entity->intro; ?></textarea>
			</div>
			<div class="pf-element pf-heading">
				<h1>Content</h1>
			</div>
			<div class="pf-element pf-full-width">
				<textarea rows="8" cols="35" class="peditor" style="width: 100%; height: 500px;" name="content"><?php echo $this->entity->content; ?></textarea>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_categories">
			<div class="pf-element pf-full-width">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						// Category Grid
						$("#p_muid_category_grid").pgrid({
							pgrid_toolbar: true,
							pgrid_toolbar_contents: [
								{type: 'button', text: 'Expand', title: 'Expand All', extra_class: 'picon picon-arrow-down', selection_optional: true, return_all_rows: true, click: function(e, rows){
									rows.pgrid_expand_rows();
								}},
								{type: 'button', text: 'Collapse', title: 'Collapse All', extra_class: 'picon picon-arrow-right', selection_optional: true, return_all_rows: true, click: function(e, rows){
									rows.pgrid_collapse_rows();
								}},
								{type: 'separator'},
								{type: 'button', text: 'All', title: 'Check All', extra_class: 'picon picon-checkbox', selection_optional: true, return_all_rows: true, click: function(e, rows){
									$("input", rows).attr("checked", "true");
								}},
								{type: 'button', text: 'None', title: 'Check None', extra_class: 'picon picon-dialog-cancel', selection_optional: true, return_all_rows: true, click: function(e, rows){
									$("input", rows).removeAttr("checked");
								}}
							],
							pgrid_hidden_cols: [1],
							pgrid_sort_col: 1,
							pgrid_sort_ord: "asc",
							pgrid_paginate: false,
							pgrid_view_height: "300px"
						});
					});
					// ]]>
				</script>
				<table id="p_muid_category_grid">
					<thead>
						<tr>
							<th>Order</th>
							<th>In</th>
							<th>Name</th>
							<th>Pages</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$category_guids = $this->entity->get_categories_guid();
					foreach($this->categories as $cur_category) { ?>
						<tr title="<?php echo $cur_category->guid; ?>" class="<?php echo $cur_category->children ? 'parent ' : ''; ?><?php echo isset($cur_category->parent) ? "child {$cur_category->parent->guid} " : ''; ?>">
							<td><?php echo isset($cur_category->parent) ? $cur_category->array_search($cur_category->parent->children) + 1 : '0' ; ?></td>
							<td><input type="checkbox" name="categories[]" value="<?php echo $cur_category->guid; ?>" <?php echo in_array($cur_category->guid, $category_guids) ? 'checked="checked" ' : ''; ?>/></td>
							<td><?php echo htmlspecialchars($cur_category->name); ?></td>
							<td><?php echo count($cur_category->pages); ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_advanced">
			<div class="pf-element pf-heading">
				<h1>Dates</h1>
				<p>Dates can be entered in almost any standard English phrase. (Next Monday, July 1st, Tomorrow 4pm, etc.)</p>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Override Created Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="p_cdate" value="<?php echo $this->entity->p_cdate ? format_date($this->entity->p_cdate, 'full_med') : ''; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Override Modified Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="p_mdate" value="<?php echo $this->entity->p_mdate ? format_date($this->entity->p_mdate, 'full_med') : ''; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Begin Publish Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="publish_begin" value="<?php echo $this->entity->publish_begin ? format_date($this->entity->publish_begin, 'full_med') : format_date(time(), 'full_med'); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">End Publish Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="publish_end" value="<?php echo $this->entity->publish_end ? format_date($this->entity->publish_end, 'full_med') : ''; ?>" /></label>
			</div>
			<div class="pf-element pf-heading">
				<h1>Options</h1>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Title</span>
					<input class="pf-field" type="checkbox" name="show_title" value="ON"<?php echo $this->entity->show_title ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Intro on Page</span>
					<input class="pf-field" type="checkbox" name="show_intro" value="ON"<?php echo $this->entity->show_intro ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Menu</span>
					<input class="pf-field" type="checkbox" name="show_menu" value="ON"<?php echo $this->entity->show_menu ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Menu Position</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_menu_position" name="menu_position" size="24" value="<?php echo htmlspecialchars($this->entity->menu_position); ?>" /></label>
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
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_content', 'page/list')); ?>');" value="Cancel" />
	</div>
</form>