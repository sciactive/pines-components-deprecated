<?php
/**
 * Provides a form for the customer to login.
 *
 * @package Components\customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Customer Login/Logout';
$this->note = 'Please enter your info, or scan your barcode to login or logout.';
?>
<form class="pf-form" id="p_muid_form" method="post" action="<?php echo htmlspecialchars(pines_url('com_customertimer', 'login')); ?>">
	<script type="text/javascript">
	pines(function(){
		var id_box = $("#p_muid_form [name=id]");
		var pw_box = $("#p_muid_form [name=password]");
		id_box.change(function(){
			if (id_box.val().indexOf("|") > 0) {
				pw_box.val(id_box.val().replace(/^[^|]*\|/, ""));
				id_box.val(id_box.val().replace(/\|.*$/, ""));
			}
		});
		$("#p_muid_form").submit(function(){
			id_box.change();
		});
		id_box.focus();
	});
	</script>
	<div class="pf-element">
		<label><span class="pf-label">Customer ID</span>
			<input class="pf-field" type="password" name="id" size="24" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Password</span>
			<input class="pf-field" type="password" name="password" size="24" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button btn btn-primary" type="submit" name="submit" value="Submit" />
		<input class="pf-button btn" type="reset" name="reset" value="Reset" />
	</div>
</form>