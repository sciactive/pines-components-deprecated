<?php
/**
 * Sale receipt helper options.
 *
 * @package Components\storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Receipt';
?>
<div class="pf-form">
	<div class="pf-element">
		This is your order receipt. Please save it for your records.
	</div>
	<div class="pf-element pf-buttons">
		<button class="pf-button btn btn-primary" type="button" onclick="window.open(<?php echo htmlspecialchars(json_encode(pines_url('com_storefront', 'checkout/receipt', array('id' => $this->entity->guid, 'template' => 'tpl_print')))); ?>, 'orderreceipt', 'status=0,toolbar=0,location=0,menubar=1,directories=0,resizable=1,scrollbars=1,height=600,width=800');">Print Receipt</button>
		<button class="pf-button btn btn-primary" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url())); ?>);">Go Back Home</button>
	</div>
</div>