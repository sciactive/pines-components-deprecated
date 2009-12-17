<?php
/**
 * Provides a form for the user to edit a sale.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
if (is_null($this->entity->guid)) {
	$this->title = 'New Sale';
} elseif ($this->entity->status == 'quoted') {
	// TODO: Process for quoting sales.
	$this->title = 'Quoted Sale ['.htmlentities($this->entity->guid).']';
} elseif ($this->entity->status == 'invoiced') {
	$this->title = 'Invoiced Sale ['.htmlentities($this->entity->guid).']';
} elseif ($this->entity->status == 'paid') {
	$this->title = 'Paid Sale ['.htmlentities($this->entity->guid).']';
}
$this->note = 'Use this form to edit a sale.';
?>
<form class="pform" method="post" id="sale_details" action="<?php echo pines_url($this->new_option, $this->new_action); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
			<?php if (isset($this->entity->uid)) { ?>
		<span>Created By: <span class="date"><?php echo $config->user_manager->get_username($this->entity->uid); ?></span></span>
		<br />
			<?php } ?>
		<span>Created On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_cdate); ?></span></span>
		<br />
		<span>Modified On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_mdate); ?></span></span>
	</div>
	<?php } ?>
	<script type="text/javascript">
		// <![CDATA[
		var customer_box;
		var customer_search_box;
		var customer_search_button;
		var customer_table;
		var customer_dialog;
		var products;
		var products_table;
		var product_code;
		var payments;
		var payments_table;
		var require_customer = false;

		// Number of decimal places to round to.
		var dec = <?php echo intval($config->com_sales->dec); ?>;
<?php
		$taxes_percent = array();
		$taxes_flat = array();
		foreach ($this->tax_fees as $cur_tax_fee) {
			if (!$cur_tax_fee->enabled)
				continue;
			foreach($cur_tax_fee->locations as $cur_location) {
				if (!$_SESSION['user']->ingroup($cur_location->guid))
					continue;
				if ($cur_tax_fee->type == 'percentage') {
					$taxes_percent[] = array('name' => $cur_tax_fee->name, 'rate' => $cur_tax_fee->rate);
					break;
				} elseif ($cur_tax_fee->type == 'flat_rate') {
					$taxes_flat[] = array('name' => $cur_tax_fee->name, 'rate' => $cur_tax_fee->rate);
					break;
				}
			}
		}
?>
		var taxes_percent = JSON.parse("<?php echo addSlashes(json_encode($taxes_percent)) ?>");
		var taxes_flat = JSON.parse("<?php echo addSlashes(json_encode($taxes_flat)) ?>");
		var status = JSON.parse("<?php echo addSlashes(json_encode($this->entity->status)); ?>");

		function round_to_dec(value) {
			var rnd = Math.pow(10, dec);
			var mult = value * rnd;
			value = gaussianRound(mult);
			value /= rnd;
			value = value.toFixed(dec);
			return (value);
		}

		function gaussianRound(x) {
			var absolute = Math.abs(x);
			var sign     = x == 0 ? 0 : (x < 0 ? -1 : 1);
			var floored  = Math.floor(absolute);
			if (absolute - floored != 0.5) {
				return Math.round(absolute) * sign;
			}
			if (floored % 2 == 1) {
				// Closest even is up.
				return Math.ceil(absolute) * sign;
			}
			// Closest even is down.
			return floored * sign;
		}

		$(document).ready(function(){
			customer_box = $("#customer");
			customer_search_box = $("#customer_search");
			customer_search_button = $("#customer_search_button");
			customer_table = $("#customer_table");
			customer_dialog = $("#customer_dialog");
			products = $("#products");
			products_table = $("#products_table");
			product_code = $("#product_code");
			payments_table = $("#payments_table");
			payments = $("#payments");

			customer_search_box.keydown(function(eventObject){
				if (eventObject.keyCode == 13) {
					customer_search(this.value);
					return false;
				}
			});
			customer_search_button.click(function(){
				customer_search(customer_search_box.val());
			});

			customer_table.pgrid({
				pgrid_paginate: true,
				pgrid_multi_select: false,
				pgrid_double_click: function(){
					customer_dialog.dialog('option', 'buttons').Done();
				}
			});

			customer_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function(){
						var rows = customer_table.pgrid_get_selected_rows().pgrid_export_rows();
						if (!rows[0]) {
							alert("Please select a customer.");
							return;
						} else {
							var customer = rows[0];
						}
						customer_box.val(customer.key+": \""+customer.values[0]+"\"");
						customer_search_box.val("");
						customer_dialog.dialog('close');
					}
				}
			});

			<?php if ($this->entity->status == 'invoiced' || $this->entity->status == 'paid') { ?>
			products_table.pgrid({
				pgrid_view_height: "160px",
				pgrid_paginate: false,
				pgrid_toolbar: false
			});
			<?php } else { ?>
			products_table.pgrid({
				pgrid_view_height: "160px",
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'text',
						label: 'Code: ',
						load: function(textbox){
							textbox.keydown(function(e){
								if (e.keyCode == 13) {
									var code = textbox.val();
									if (code == "") {
										alert("Please enter a product code.");
										return;
									}
									textbox.val("");
									var loader;
									$.ajax({
										url: "<?php echo pines_url('com_sales', 'productsearch'); ?>",
										type: "POST",
										dataType: "json",
										data: {"code": code},
										beforeSend: function(){
											loader = pines.alert('Retrieving product from server...', 'Product Search', 'icon picon_16x16_animations_throbber', {pnotify_hide: false});
										},
										complete: function(){
											loader.pnotify_remove();
										},
										error: function(XMLHttpRequest, textStatus){
											pines.error("An error occured while trying to lookup the product code:\n"+textStatus);
										},
										success: function(data){
											if (!data) {
												alert("No product was found with the code "+code+".");
												return;
											}
											var serial = "";
											if (data.serialized) {
												while (!serial) {
													serial = prompt("This item is serialized. Please provide the serial:");
												}
											}
											products_table.pgrid_add([{key: data.guid, values: [data.sku, data.name, serial, 'in-store', 1, data.unit_price, "", "", ""]}], function(){
												var cur_row = $(this);
												cur_row.data("product", data);
											});
											update_products();
										}
									});
								}
							});
						}
					},
					{type: 'separator'},
					{
						type: 'button',
						text: 'Serial',
						extra_class: 'icon picon_16x16_stock_generic_stock_id',
						double_click: true,
						click: function(e, rows){
							var product = rows.data("product");
							if (!product.serialized)
								return;
							var serial = rows.pgrid_get_value(3);
							do {
								serial = prompt("This item is serialized. Please provide the serial:", serial);
							} while (!serial && serial != null);
							if (serial != null) {
								rows.pgrid_set_value(3, serial);
								update_products();
							}
						}
					},
					{
						type: 'button',
						text: 'Delivery',
						extra_class: 'icon picon_16x16_emblems_emblem-package',
						multi_select: true,
						click: function(e, rows){
							rows.each(function(){
								var delivery = rows.pgrid_get_value(4);
								delivery = (delivery == 'in-store') ? 'shipped' : 'in-store';
								rows.pgrid_set_value(4, delivery);
							});
							update_products();
						}
					},
					{
						type: 'button',
						text: 'Quantity',
						extra_class: 'icon picon_16x16_stock_data_stock_record-number',
						double_click: true,
						click: function(e, rows){
							var product = rows.data("product");
							if (product.serialized)
								return;
							var qty = rows.pgrid_get_value(5);
							do {
								qty = prompt("Please enter a quantity:", qty);
							} while ((parseInt(qty) < 1 || isNaN(parseInt(qty))) && qty != null);
							if (qty != null) {
								rows.pgrid_set_value(5, parseInt(qty));
								update_products();
							}
						}
					},
					{
						type: 'button',
						text: 'Discount',
						extra_class: 'icon picon_16x16_stock_form_stock_form-currency-field',
						click: function(e, rows){
							var product = rows.data("product");
							if (!product.discountable) {
								alert("The selected product is not discountable.")
								return;
							}
							var discount = rows.pgrid_get_value(7);
							do {
								discount = prompt("Enter an amount($#.##) or a percent (#.##%) to discount each unit:", discount);
							} while ((!discount.match(/^(\$-?\d+(\.\d+)?)|(-?\d+(\.\d+)?%)$/)) && discount != null);
							if (discount != null) {
								rows.pgrid_set_value(7, discount);
								update_products();
							}
						}
					},
					{
						type: 'button',
						text: 'Remove',
						extra_class: 'icon picon_16x16_actions_edit-delete',
						multi_select: true,
						click: function(e, rows){
							rows.pgrid_delete();
							update_products();
						}
					}
				]
			});
			<?php } ?>

			// Load the data for any existing products.
			var loader;
			products_table.pgrid_get_all_rows().each(function(){
				if (!loader)
					loader = pines.alert('Retrieving product information from server...', 'Loading Products', 'icon picon_16x16_animations_throbber', {pnotify_hide: false});
				var cur_row = $(this);
				var cur_export = cur_row.pgrid_export_rows();
				var cur_guid = cur_export[0].key;
				$.ajax({
					url: "<?php echo pines_url('com_sales', 'productsearch'); ?>",
					type: "POST",
					async: false,
					dataType: "json",
					data: {"code": cur_guid, "useguid": true},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to lookup a product:\n"+textStatus);
					},
					success: function(data){
						if (!data) {
							alert("No product was found with the id "+cur_guid+".");
							return;
						}
						cur_row.data("product", data);
					}
				});
			});
			if (loader)
				loader.pnotify_remove();

			<?php if ($this->entity->status == 'paid') { ?>
			payments_table.pgrid({
				pgrid_view_height: "150px",
				pgrid_paginate: false,
				pgrid_footer: false,
				pgrid_toolbar: false
			});
			<?php } else { ?>
			payments_table.pgrid({
				pgrid_view_height: "150px",
				pgrid_paginate: false,
				pgrid_footer: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Remove',
						extra_class: 'icon picon_16x16_actions_edit-delete',
						multi_select: true,
						click: function(e, rows){
							rows.pgrid_delete();
							update_payments();
						}
					}
				]
			});

			$("button.payment-button").hover(function(){
				$(this).addClass("ui-state-hover");
			}, function(){
				$(this).removeClass("ui-state-hover");
			}).click(function(){
				var payment_type = JSON.parse(this.value);
				// TODO: Minimums
				$("<div title=\"Payment Amount\" />").each(function(){
					var amount_dialog = $(this);
					// A button for the current amount due.
					amount_dialog.append($("<button />").addClass("ui-state-default ui-corner-all").hover(function(){
						$(this).addClass("ui-state-hover");
					}, function(){
						$(this).removeClass("ui-state-hover");
					}).html($("#amount_due").html()).css({"float": "left", "clear": "both", "min-height": "60px", "width": "100%", "text-align": "center", "margin": "2px"})
					.click(function(){
						payments_table.pgrid_add([{key: payment_type.guid, values: [
							payment_type.name,
							round_to_dec($("#amount_due").html())
						]}]);
						amount_dialog.dialog("close");
						update_payments();
					}));
					// Buttons for common amounts.
					$.each(["1", "5", "10", "20", "50", "100"], function(){
						var cur_amount = this;
						amount_dialog.append($("<button />").addClass("ui-state-default ui-corner-all").hover(function(){
							$(this).addClass("ui-state-hover");
						}, function(){
							$(this).removeClass("ui-state-hover");
						}).html(String(cur_amount)).css({"float": "left", "min-height": "60px", "min-width": "60px", "text-align": "center", "margin": "2px"})
						.click(function(){
							payments_table.pgrid_add([{key: payment_type.guid, values: [
								payment_type.name,
								round_to_dec(cur_amount)
							]}]);
							amount_dialog.dialog("close");
							update_payments();
						}));
					});
					// A button for a custom amount.
					amount_dialog.append($("<button />").addClass("ui-state-default ui-corner-all").hover(function(){
						$(this).addClass("ui-state-hover");
					}, function(){
						$(this).removeClass("ui-state-hover");
					}).html("Another Amount").css({"float": "left", "clear": "both", "min-height": "60px", "width": "100%", "text-align": "center", "margin": "2px"})
					.click(function(){
						var cur_amount = null;
						do {
							cur_amount = prompt("Amount in dollars:", cur_amount);
						} while (isNaN(parseInt(cur_amount)) && cur_amount != null);
						if (cur_amount != null) {
							payments_table.pgrid_add([{key: payment_type.guid, values: [
								payment_type.name,
								round_to_dec(cur_amount)
							]}]);
						}
						amount_dialog.dialog("close");
						update_payments();
					}));
				}).dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true
				});
			});
			<?php } ?>

			// Load any initial products.
			update_products();
		});

		function update_products() {
			var rows = products_table.pgrid_get_all_rows();
			if (!rows)
				return;
			var subtotal = 0;
			var taxes = 0;
			var itemfees = 0;
			var total = 0;
			require_customer = false;
			// Calculate ticket totals.
			rows.each(function(){
				var cur_row = $(this);
				var product = cur_row.data("product");
				if (product.require_customer) {
					require_customer = true;
				}
				var price = parseFloat(cur_row.pgrid_get_value(6));
				var qty = parseInt(cur_row.pgrid_get_value(5));
				var discount = cur_row.pgrid_get_value(7);
				var cur_itemfees = 0;
				if (isNaN(price))
					price = 0;
				if (isNaN(qty))
					qty = 1;
				if (product.discountable && discount != "") {
					var discount_price;
					if (discount.match(/^\$-?\d+(\.\d+)?$/)) {
						discount = parseFloat(discount.replace(/[^0-9.-]/, ''));
						discount_price = price - discount;
					} else if (discount.match(/^-?\d+(\.\d+)?%$/)) {
						discount = parseFloat(discount.replace(/[^0-9.-]/, ''));
						discount_price = price - (price * (discount / 100));
					}
					if (!isNaN(product.floor) && round_to_dec(discount_price) < round_to_dec(product.floor)) {
						alert("The discount lowers the product's price below the limit. The maximum discount possible for this item ["+product.name+"], is $"+round_to_dec(product.unit_price - product.floor)+" or "+round_to_dec((product.unit_price - product.floor) / product.unit_price * 100)+"%.");
						cur_row.pgrid_set_value(7, "");
					} else {
						price = discount_price;
					}
				}
				var line_total = price * qty;
				if (!product.tax_exempt) {
					$.each(taxes_percent, function(){
						taxes += (this.rate / 100) * line_total;
					});
					$.each(taxes_flat, function(){
						taxes += this.rate * qty;
					});
				}
				$.each(product.fees_percent, function(){
					cur_itemfees += (this.rate / 100) * line_total;
				});
				$.each(product.fees_flat, function(){
					cur_itemfees += this.rate * qty;
				});
				itemfees += cur_itemfees;
				subtotal += line_total;
				cur_row.pgrid_set_value(8, round_to_dec(line_total));
				cur_row.pgrid_set_value(9, round_to_dec(cur_itemfees));
			});
			total = subtotal + itemfees + taxes;
			$("#subtotal").html(round_to_dec(subtotal));
			$("#itemfees").html(round_to_dec(itemfees));
			$("#taxes").html(round_to_dec(taxes));
			$("#total").html(round_to_dec(total));

			// Update the products input element.
			products.val(JSON.stringify(rows.pgrid_export_rows()));

			update_payments();
		}

		function update_payments() {
			var rows = payments_table.pgrid_get_all_rows();
			var total = parseFloat($("#total").html());
			var amount_tendered = 0;
			var amount_due = 0;
			var change = 0;
			if (isNaN(total))
				return;
			// Calculate the total payments.
			rows.each(function(){
				var cur_row = $(this);
				var amount = parseFloat(cur_row.pgrid_get_value(2).replace(/[^0-9.-]/g, ""));
				if (isNaN(amount))
					amount = 0;
				amount_tendered += amount;
			});
			amount_due = total - amount_tendered;
			if (amount_due < 0) {
				change = Math.abs(amount_due);
				amount_due = 0;
			}
			$("#amount_tendered").html(round_to_dec(amount_tendered));
			$("#amount_due").html(round_to_dec(amount_due));
			$("#change").html(round_to_dec(change));
			
			payments.val(JSON.stringify(rows.pgrid_export_rows()));
		}

		function customer_search(search_string) {
			var loader;
			$.ajax({
				url: "<?php echo pines_url("com_sales", "customersearch"); ?>",
				type: "POST",
				dataType: "json",
				data: {"q": search_string},
				beforeSend: function(){
					loader = pines.alert('Searching for customers...', 'Customer Search', 'icon picon_16x16_animations_throbber', {pnotify_hide: false});
					customer_table.pgrid_get_all_rows().pgrid_delete();
				},
				complete: function(){
					loader.pnotify_remove();
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to find customers:\n"+textStatus);
				},
				success: function(data){
					if (!data) {
						alert("No customers were found that matched the query.");
						return;
					}
					customer_dialog.dialog('open');
					customer_table.pgrid_add(data);
				}
			});
		}
		// ]]>
	</script>
	<div class="element">
		<label for="customer_search"><span class="label">Customer</span>
			<span class="note">Enter part of a name, company, email, or phone # to search.</span></label>
		<div class="group">
			<input class="field" type="text" id="customer" name="customer" size="20" onfocus="this.blur();" value="<?php echo htmlentities($this->entity->customer->guid ? "{$this->entity->customer->guid}: \"{$this->entity->customer->name}\"" : 'No Customer Selected'); ?>" />
			<br />
			<input class="field" type="text" id="customer_search" name="customer_search" size="20" />
			<button type="button" id="customer_search_button"><span class="picon_16x16_actions_system-search" style="padding-left: 16px; background-repeat: no-repeat;">Search</span></button>
		</div>
	</div>
	<div id="customer_dialog" title="Pick a Customer">
		<table id="customer_table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Company</th>
					<th>Job Title</th>
					<th>Address 1</th>
					<th>Address 2</th>
					<th>City</th>
					<th>State</th>
					<th>Zip</th>
					<th>Home Phone</th>
					<th>Work Phone</th>
					<th>Cell Phone</th>
					<th>Fax</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
				</tr>
			</tbody>
		</table>
		<br class="spacer" />
	</div>
	<div class="element full_width">
		<span class="label">Products</span>
		<div class="group">
			<div class="field">
				<table id="products_table">
					<thead>
						<tr>
							<th>SKU</th>
							<th>Product</th>
							<th>Serial</th>
							<th>Delivery</th>
							<th>Quantity</th>
							<th>Price</th>
							<th>Discount</th>
							<th>Line Total</th>
							<th>Fees</th>
						</tr>
					</thead>
					<tbody>
						<?php if (is_array($this->entity->products)) { foreach ($this->entity->products as $cur_product) {
								if (is_null($cur_product['entity']))
									continue;
								?>
						<tr title="<?php echo $cur_product['entity']->guid; ?>">
							<td><?php echo $cur_product['entity']->sku; ?></td>
							<td><?php echo $cur_product['entity']->name; ?></td>
							<td><?php echo $cur_product['serial']; ?></td>
							<td><?php echo $cur_product['delivery']; ?></td>
							<td><?php echo $cur_product['quantity']; ?></td>
							<td><?php echo $cur_product['price']; ?></td>
							<td><?php echo $cur_product['discount']; ?></td>
							<td><?php echo $config->run_sales->round($cur_product['line_total'], $config->com_sales->dec); ?></td>
							<td><?php echo $config->run_sales->round($cur_product['fees'], $config->com_sales->dec); ?></td>
						</tr>
						<?php } } ?>
					</tbody>
				</table>
			</div>
			<input class="field" type="hidden" id="products" name="products" size="20" />
		</div>
	</div>
	<div class="element full_width">
		<span class="label">Ticket Totals</span>
		<div class="group">
			<div class="field" style="float: right; font-size: 1.2em; text-align: right;">
				<span class="label">Subtotal</span><span class="field" id="subtotal">0.00</span><br />
				<span class="label">Item Fees</span><span class="field" id="itemfees">0.00</span><br />
				<span class="label">Tax</span><span class="field" id="taxes">0.00</span><br />
				<hr /><br />
				<span class="label">Total</span><span class="field" id="total">0.00</span>
			</div>
			<hr class="field" style="clear: both;" />
		</div>
	</div>
	<div class="element full_width">
		<span class="label">Payments</span>
		<?php if ($this->entity->status != 'paid') { ?>
		<div class="note">
			<div style="text-align: left;">
				<?php foreach ($this->payment_types as $cur_payment_type) { ?>
				<button id="payment_<?php echo $cur_payment_type->guid; ?>" class="ui-state-default ui-corner-all payment-button" type="button" style="margin-bottom: 2px;" value="<?php echo htmlentities(json_encode((object) array("guid" => $cur_payment_type->guid, "name" => $cur_payment_type->name, "minimum" => $cur_payment_type->minimum))); ?>">
					<span class="picon_32x32_actions_list-add" style="display: block; padding-top: 32px; min-width: 50px; background-repeat: no-repeat; background-position: top center;"><?php echo $cur_payment_type->name; ?></span>
				</button>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
		<?php /*
		<div class="group">
			<div style="float: right;">
				<?php foreach ($this->payment_types as $cur_payment_type) { ?>
				<button id="payment_<?php echo $cur_payment_type->guid; ?>" class="field ui-state-default ui-corner-all payment-button" type="button" value="<?php echo htmlentities(json_encode((object) array("guid" => $cur_payment_type->guid, "name" => $cur_payment_type->name, "minimum" => $cur_payment_type->minimum))); ?>">
					<span class="picon_32x32_actions_list-add" style="display: block; padding-top: 32px; min-width: 32px; background-repeat: no-repeat; background-position: top center;"><?php echo $cur_payment_type->name; ?></span>
				</button>
				<?php } ?>
			</div>
			<br style="clear: both;" />
		</div>
		 */ ?>
		<div style="margin-top: 5px;" class="group">
			<div class="field">
				<table id="payments_table">
					<thead>
						<tr>
							<th>Type</th>
							<th>Amount</th>
						</tr>
					</thead>
					<tbody>
						<?php if (is_array($this->entity->payments)) { foreach ($this->entity->payments as $cur_payment) {
								if (is_null($cur_payment['entity']))
									continue;
								?>
						<tr title="<?php echo $cur_payment['entity']->guid; ?>">
							<td><?php echo $cur_payment['entity']->name; ?></td>
							<td><?php echo $config->run_sales->round($cur_payment['amount'], $config->com_sales->dec); ?></td>
						</tr>
						<?php } } ?>
					</tbody>
				</table>
			</div>
			<input class="field" type="hidden" id="payments" name="payments" size="20" />
		</div>
	</div>
	<div class="element full_width">
		<span class="label">Tendered</span>
		<div class="group">
			<div class="field" style="float: right; font-size: 1.2em; text-align: right;">
				<span class="label">Amount Tendered</span><span class="field" id="amount_tendered">0.00</span><br />
				<span class="label">Amount Due</span><span style="font-weight: bold;" class="field" id="amount_due">0.00</span><br />
				<hr /><br />
				<span class="label">Change</span><span style="font-weight: bold;" class="field" id="change">0.00</span>
			</div>
			<hr class="field" style="clear: both;" />
		</div>
	</div>
	<div class="element full_width">
		<label><span class="label">Comments</span>
			<span class="field full_width"><textarea style="width: 100%;" rows="3" cols="35" name="comments"><?php echo $this->entity->comments; ?></textarea></span></label>
	</div>
	<div class="element buttons">
		<?php if ( !is_null($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="process" value="Complete" />
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="process" value="Invoice" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listsales'); ?>';" value="Cancel" />
	</div>
</form>