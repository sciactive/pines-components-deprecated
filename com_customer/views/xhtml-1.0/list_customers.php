<?php
/**
 * Lists customers and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Customers';
?>
<script type="text/javascript">
	// <![CDATA[

	$(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_customer', 'editcustomer'); ?>'},
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_customer', 'editcustomer', array('id' => '#title#')); ?>'},
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:#col_2#', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_customer', 'deletecustomer', array('id' => '#title#')); ?>', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'customers',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('system', 'pgrid_save_state'); ?>", {view: "com_customer/list_customers", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#customer_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="customer_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Email</th>
			<th>Company</th>
			<th>Job Title</th>
			<th>Country</th>
			<th>Address</th>
			<th>City</th>
			<th>State</th>
			<th>Zip</th>
			<th>Home Phone</th>
			<th>Work Phone</th>
			<th>Cell Phone</th>
			<th>Fax</th>
			<th>Login Disabled</th>
			<th>Points</th>
			<th>Peak</th>
			<th>All-Time</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->customers as $customer) { ?>
		<tr title="<?php echo $customer->guid; ?>">
			<td><?php echo $customer->guid; ?></td>
			<td><?php echo $customer->name; ?></td>
			<td><?php echo $customer->email; ?></td>
			<td><?php echo $customer->company->name; ?></td>
			<td><?php echo $customer->job_title; ?></td>
			<td><?php echo $customer->address_type == 'us' ? 'US' : 'Intl'; ?></td>
			<td><?php echo $customer->address_type == 'us' ? $customer->address_1.' '.$customer->address_2 : $customer->address_international; ?></td>
			<td><?php echo $customer->city; ?></td>
			<td><?php echo $customer->state; ?></td>
			<td><?php echo $customer->zip; ?></td>
			<td><?php echo $customer->phone_home; ?></td>
			<td><?php echo $customer->phone_work; ?></td>
			<td><?php echo $customer->phone_cell; ?></td>
			<td><?php echo $customer->fax; ?></td>
			<td><?php echo ($customer->login_disabled ? 'Yes' : 'No'); ?></td>
			<td><?php echo $customer->points; ?></td>
			<td><?php echo $customer->peak_points; ?></td>
			<td><?php echo $customer->total_points; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>