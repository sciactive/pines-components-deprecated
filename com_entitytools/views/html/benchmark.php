<?php
/**
 * Runs a benchmark of the entity manager.
 *
 * @package Components\entitytools
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 *
 * @todo Finish the benchmarking utility.
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Entity Manager Benchmark';
?>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_entitytools', 'benchmark')); ?>">
	<div class="pf-element pf-heading">
		<p>
			This entity manager benchmark will create, retrieve and delete
			1,000,000 entities and display the timing results here. It may take
			a <strong>VERY</strong> long time to complete. Are you sure you want
			to proceed?
		</p>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="sure" value="yes" />
		<input class="pf-button btn btn-primary" type="submit" value="Yes, proceed." />
	</div>
</form>