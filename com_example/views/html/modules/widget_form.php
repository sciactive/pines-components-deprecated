<?php
/**
 * Provides a form for the user to choose a widget.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$widgets = $pines->entity_manager->get_entities(array('class' => com_example_widget), array('&', 'tag' => array('com_example', 'widget')));
$pines->entity_manager->sort($widgets, 'name');
?>
<div class="pf-form">
	<div class="pf-element">
		<label><span class="pf-label">Widget</span>
			<select class="pf-field ui-widget-content ui-corner-all" name="id">
				<?php foreach ($widgets as $cur_widget) { ?>
				<option value="<?php echo (int) $cur_widget->guid ?>"><?php echo htmlspecialchars($cur_widget->name); ?></option>
				<?php } ?>
			</select></label>
	</div>
</div>