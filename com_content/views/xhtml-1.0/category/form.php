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
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		$("#p_muid_menu_position").autocomplete({
			source: <?php echo json_encode($pines->info->template->positions); ?>
		});
	});
	// ]]>
</script>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_content', 'category/save')); ?>">
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
		<label><span class="pf-label">Name</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Enabled</span>
			<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
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
		<span class="pf-note">These pages are assigned to this category.</span>
		<div class="pf-group">
			<div class="pf-field ui-widget-content ui-corner-all" style="padding: 1em; min-width: 300px; max-height: 200px; overflow: auto;">
				<?php foreach ($this->entity->pages as $cur_page) { ?>
				<a href="<?php echo htmlspecialchars(pines_url('com_content', 'page/edit', array('id' => $cur_page->guid))); ?>"><?php echo htmlspecialchars($cur_page->name); ?></a><br />
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_content', 'category/list')); ?>');" value="Cancel" />
	</div>
</form>