<?php
/**
 * com_sales class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_sales main class.
 *
 * Manage sales, products, manufacturers, vendors, etc.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales extends component {
	/**
	 * Whether the product selector JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded_product
	 */
	private $js_loaded_product = false;

	/**
	 * Calls the first payment process which matches the given arguments.
	 *
	 * @param array $arguments The arguments to pass to appropriate callbacks.
	 * @param mixed &$result If the process returns anything, this variable will be set to the return value.
	 * @return bool True on success, false on failure.
	 * @todo Finish calling this in all appropriate places.
	 */
	public function call_payment_process($arguments = array(), &$result = null) {
		global $pines;
		if (!is_array($arguments))
			return false;
		if (empty($arguments['action']))
			return false;
		if ($arguments['action'] != 'request' && $arguments['action'] != 'request_cust' && !is_object($arguments['ticket']))
			return false;
		foreach ($pines->config->com_sales->processing_types as $cur_type) {
			if ($arguments['name'] != $cur_type['name'])
				continue;
			if (!is_callable($cur_type['callback']))
				continue;
			$result = call_user_func_array($cur_type['callback'], array($arguments));
			return true;
		}
	}

	/**
	 * Calls any product actions which match the given arguments.
	 *
	 * @param array $arguments The arguments to pass to appropriate callbacks.
	 * @param int $times How many times to call the callback.
	 * @return bool True on success, false on failure.
	 * @todo Finish calling this in all appropriate places.
	 */
	public function call_product_actions($arguments = array(), $times = 1) {
		global $pines;
		if (!is_array($arguments))
			return false;
		if (empty($arguments['type']))
			return false;
		if (!is_object($arguments['product']))
			return false;
		// If the product has no actions associated with it, don't bother going through the actions.
		if (!is_array($arguments['product']->actions) || !$arguments['product']->actions)
			return true;
		foreach ($pines->config->com_sales->product_actions as $cur_action) {
			if (is_array($cur_action['type'])) {
				if (!in_array($arguments['type'], $cur_action['type']))
					continue;
			} else {
				if ($arguments['type'] != $cur_action['type'])
					continue;
			}
			if (!in_array($cur_action['name'], $arguments['product']->actions))
				continue;
			if (!is_callable($cur_action['callback']))
				continue;
			$arguments['name'] = $cur_action['name'];
			for ($i = 0; $i < (int) $times; $i++)
				call_user_func_array($cur_action['callback'], array($arguments));
		}
		return true;
	}

	/**
	 * Print a form to select date timespan.
	 *
	 * @param bool $all_time Currently searching all records or a timespan.
	 * @param string $start The current starting date of the timespan.
	 * @param string $end The current ending date of the timespan.
	 * @return module The form's module.
	 */
	public function date_select_form($all_time = false, $start = null, $end = null) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_sales', 'forms/date_selector', 'content');
		$module->all_time = $all_time;
		$module->start_date = $start;
		$module->end_date = $end;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Gets a product by its code.
	 *
	 * The first code checked is the product's SKU. If the product is found, it
	 * is returned, and searching ends. If not, each product's additional
	 * barcodes are checked until a match is found. If no product is found, null
	 * is returned.
	 *
	 * @param int $code The product's code.
	 * @return com_sales_product|null The product if it is found, null if it isn't.
	 */
	public function get_product_by_code($code) {
		global $pines;
		$product = $pines->entity_manager->get_entity(
				array('class' => com_sales_product),
				array('&',
					'tag' => array('com_sales', 'product'),
					'data' => array('sku', $code)
				)
			);
		if (isset($product))
			return $product;
		return $pines->entity_manager->get_entity(
				array('class' => com_sales_product),
				array('&',
					'tag' => array('com_sales', 'product'),
					'array' => array('additional_barcodes', $code)
				)
			);
	}

	/**
	 * Find the PO that corresponds to an incoming product.
	 *
	 * Only use this function if the stock entity is not available.
	 *
	 * @todo Go through each matched PO and check which one has the closest ETA.
	 * @param com_sales_product $product The product to search for.
	 * @param group $location Look through POs destined for this location.
	 * @return com_sales_po|null A PO, or null if nothing is found.
	 */
	public function get_origin_po($product, $location = null) {
		global $pines;
		// Get all the POs.
		$selector = array('&',
				'tag' => array('com_sales', 'po'),
				'data' => array(
					array('finished', false),
					array('final', true)
				),
				'ref' => array(
					array('pending_products', $product)
				)
			);
		if (isset($location))
			$selector['ref'][] = array('destination', $location);
		return $pines->entity_manager->get_entity(
				array('class' => com_sales_po),
				$selector
			);
	}

	/**
	 * Find the transfer that corresponds to an incoming product.
	 *
	 * Only use this function if the stock entity is not available.
	 *
	 * @todo Go through each matched transfer and check which one has the closest ETA.
	 * @param com_sales_product $product The product to search for.
	 * @param string $serial The serial to search for.
	 * @param group $location Look through transfers destined for this location.
	 * @return array|null An array with the transfer and stock entry, or null if nothing is found.
	 */
	public function get_origin_transfer($product, $serial = null, $location = null) {
		global $pines;
		// Get all the transfers.
		$selector = array('&',
				'tag' => array('com_sales', 'transfer'),
				'data' => array(
					array('finished', false),
					array('final', true)
				),
				'ref' => array(
					array('pending_products', $product)
				)
			);
		if (isset($serial))
			$selector['array'] = array('pending_serials', $serial);
		if (isset($location))
			$selector['ref'][] = array('destination', $location);
		$entities = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_transfer),
				$selector
			);
		// Iterate through all the transfers.
		foreach ($entities as $cur_transfer) {
			// Iterate the transfer's stock, looking for a match.
			foreach ($cur_transfer->stock as $cur_stock) {
				if (isset($serial) || isset($cur_stock->serial)) {
					// Check the serial with the stock entry's serial.
					if ($cur_stock->serial != $serial)
						continue;
				}
				// If it's not the right product, move on.
				if (!$product->is($cur_stock->product))
					continue;
				// If it's already received, move on.
				if ($cur_stock->in_array((array) $cur_transfer->received))
					continue;
				// If it's a match, return the transfer and the item.
				return array($cur_transfer, $cur_stock);
			}
		}
		// Nothing found, return null.
		return null;
	}

	/**
	 * Inform the user of anything important.
	 *
	 * @param string $title The title or category of the message.
	 * @param string $header The header title for the message.
	 * @param string $note The content of the message.
	 * @param string $link The url to send the user to upon clicking the header.
	 * @return module The notification's module.
	 */
	public function inform($title, $header, $note, $link = null) {
		global $pines;
		$module = new module('com_sales', 'show_note', 'right');
		$module->title = $title;
		$module->header = $header;
		$module->message = $note;
		if (isset($link))
			$module->link = $link;
		return $module;
	}

	/**
	 * Creates and attaches a module which lists cash counts.
	 * @param int $start_date The start date of cash counts to show.
	 * @param int $end_date The end date of cash counts to show.
	 * @param group $location The location to show cash counts for.
	 * @param bool $old Whether to show old cash counts instead.
	 */
	public function list_cashcounts($start_date = null, $end_date = null, $location = null, $old = false) {
		global $pines;

		$form = new module('com_sales', 'cashcount/listform', 'right');
		$module = new module('com_sales', 'cashcount/list', 'content');

		if (!isset($start_date))
			$start_date = strtotime('-1 week 00:00');
		if (!isset($end_date))
			$end_date = strtotime('23:59');
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		$selector = array('|',
			'data' => array(
				array('status', 'info_requested'),
				array('status', 'pending')
			)
		);
		if ($old)
			$selector[0] = '!&';
		$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		$module->counts = $pines->entity_manager->get_entities(
				array('class' => com_sales_cashcount),
				$selector,
				$or,
				array('&',
					'tag' => array('com_sales', 'cashcount'),
					'gte' => array('p_cdate', (int) $start_date),
					'lte' => array('p_cdate', (int) $end_date)
				)
			);
		$form->start_date = $start_date;
		$form->end_date = $end_date;
		$form->location = $location->guid;
		$form->old = $module->old = $old;

		// Remind the user to do a cash count if one is assigned to their location.
		if ($_SESSION['user']) {
			$_SESSION['user']->refresh();
			if ($_SESSION['user']->group->com_sales_task_cashcount)
				$this->inform('Assignment', 'Cash Drawer Count', 'Please perform a count of the cash in your location\'s drawer. Corporate is awaiting a cash count submission.', pines_url('com_sales', 'cashcount/edit'));
			if ($_SESSION['user']->group->com_sales_task_cashcount_audit)
				$this->inform('Assignment', 'Cash Drawer Audit', 'Please perform an audit of the cash in your location\'s drawer. Corporate is awaiting a cash drawer audit submission.', pines_url('com_sales', 'cashcount/audit'));
			if ($_SESSION['user']->group->com_sales_task_cashcount_deposit)
				$this->inform('Assignment', 'Cash Drawer Deposit', 'Please perform a deposit from the skimmed cash in your location. Corporate is awaiting a cash deposit submission.', pines_url('com_sales', 'cashcount/deposit'));
			if ($_SESSION['user']->group->com_sales_task_cashcount_skim)
				$this->inform('Assignment', 'Cash Drawer Skim', 'Please perform a skim from the cash in your location\'s drawer. Corporate is awaiting a cash skim submission.', pines_url('com_sales', 'cashcount/skim'));
		}

		if ( empty($module->counts) )
			pines_notice('No cash counts found.');
	}

	/**
	 * Creates and attaches a module which lists categories.
	 */
	public function list_categories() {
		global $pines;

		$module = new module('com_sales', 'category/list', 'content');

		$module->categories = $pines->entity_manager->get_entities(array('class' => com_sales_category), array('&', 'tag' => array('com_sales', 'category')));

		if ( empty($module->categories) )
			pines_notice('No categories found.');
	}
	
	/**
	 * Creates and attaches a module which lists countsheets.
	 * @param int $start_date The start date of countsheets to show.
	 * @param int $end_date The end date of countsheets to show.
	 * @param group $location The location to show countsheets for.
	 */
	public function list_countsheets($start_date = null, $end_date = null, $location = null) {
		global $pines;

		$module = new module('com_sales', 'countsheet/list', 'content');

		$selector = array('&', 'tag' => array('com_sales', 'countsheet'));
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lte'] = array('p_cdate', (int) $end_date);
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		if (!gatekeeper('com_sales/approvecountsheet'))
			$approved_selector = array('!&', 'data' => array('status', 'approved'));
		else
			$approved_selector = array('&');
		$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		$module->countsheets = $pines->entity_manager->get_entities(array('class' => com_sales_countsheet), $selector, $approved_selector, $or);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		$module->location = $location;

		// Remind the user to do a countsheet if one is assigned to their location.
		if ($_SESSION['user']) {
			$_SESSION['user']->refresh();
			if ($_SESSION['user']->group->com_sales_task_countsheet)
				$this->inform('Reminder', 'Inventory Countsheet', 'Please fill out a countsheet for your location when you are not busy. Corporate is awaiting the submission of an inventory count.', pines_url('com_sales', 'countsheet/edit'));
		}
	
		if ( empty($module->countsheets) )
			pines_notice('There are no countsheets.');
	}

	/**
	 * Creates and attaches a module which lists manufacturers.
	 */
	public function list_manufacturers() {
		global $pines;

		$module = new module('com_sales', 'manufacturer/list', 'content');

		$module->manufacturers = $pines->entity_manager->get_entities(array('class' => com_sales_manufacturer), array('&', 'tag' => array('com_sales', 'manufacturer')));

		if ( empty($module->manufacturers) )
			pines_notice('There are no manufacturers.');
	}

	/**
	 * Creates and attaches a module which lists payment types.
	 */
	public function list_payment_types() {
		global $pines;

		$module = new module('com_sales', 'paymenttype/list', 'content');

		$module->payment_types = $pines->entity_manager->get_entities(array('class' => com_sales_payment_type), array('&', 'tag' => array('com_sales', 'payment_type')));

		if ( empty($module->payment_types) )
			pines_notice('There are no payment types.');
	}

	/**
	 * Creates and attaches a module which lists pos.
	 * @param bool $finished Show finished POs instead of pending ones.
	 */
	public function list_pos($finished = false) {
		global $pines;

		$module = new module('com_sales', 'po/list', 'content');

		$module->pos = $pines->entity_manager->get_entities(
				array('class' => com_sales_po),
				array('&',
					'tag' => array('com_sales', 'po'),
					'data' => array('finished', $finished)
				)
			);

		if ( empty($module->pos) ) {
			pines_notice('There are no POs.');
			return;
		}

		// Check the purchase orders to see if any have not been received on time.
		$errors = array();
		foreach ($module->pos as $po) {
			// strtotime gives 12:00am versus time() which is to the second.
			if ($po->eta < strtotime('today 12:00am') && empty($po->received))
				$errors[] = "#{$po->po_number} was not received on time.";
		}
		if (!empty($errors)) {
			$type = 'Reminder';
			$head = 'Purchase Orders';
			$this->inform($type, $head, implode("\n", $errors));
		}
	}

	/**
	 * Creates and attaches a module which lists products.
	 */
	public function list_products() {
		global $pines;

		$module = new module('com_sales', 'product/list', 'content');

		$module->products = $pines->entity_manager->get_entities(array('class' => com_sales_product), array('&', 'tag' => array('com_sales', 'product')));

		if ( empty($module->products) )
			pines_notice('There are no products.');
	}

	/**
	 * Creates and attaches a module which lists returns.
	 * @param int $start_date The start date of returns to show.
	 * @param int $end_date The end date of returns to show.
	 * @param group $location The location to show returns for.
	 */
	public function list_returns($start_date = null, $end_date = null, $location = null) {
		global $pines;

		$module = new module('com_sales', 'return/list', 'content');

		$selector = array('&', 'tag' => array('com_sales', 'return'));
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lte'] = array('p_cdate', (int) $end_date);
		if (!isset($location))
			$location = $_SESSION['user']->group;
		$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		$module->returns = $pines->entity_manager->get_entities(array('class' => com_sales_return), $selector, $or);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		$module->location = $location;

		if ( empty($module->returns) )
			pines_notice('No returns found.');
	}

	/**
	 * Creates and attaches a module which lists sales.
	 * @param int $start_date The start date of sales to show.
	 * @param int $end_date The end date of sales to show.
	 * @param group $location The location to show sales for.
	 */
	public function list_sales($start_date = null, $end_date = null, $location = null) {
		global $pines;

		$module = new module('com_sales', 'sale/list', 'content');

		$selector = array('&', 'tag' => array('com_sales', 'sale'));
		if (isset($start_date))
			$selector['gte'] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$selector['lte'] = array('p_cdate', (int) $end_date);
		if (!isset($location))
			$location = $_SESSION['user']->group;
		$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
		$module->sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), $selector, $or);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		$module->location = $location;

		if ( empty($module->sales) )
			pines_notice('No sales found.');
	}

	/**
	 * Creates and attaches a module which lists shipments.
	 *
	 * @param bool $removed Whether to show shipments that have been sent.
	 * @param group $location The location to show shipments for.
	 */
	public function list_shipments($removed = false, $location = null) {
		global $pines;

		$module = new module('com_sales', 'stock/shipments', 'content');

		if ($removed) {
			$module->removed = true;
			$or = array();
		} else {
			if (!isset($location))
				$location = $_SESSION['user']->group;
			$or = array('|', 'ref' => array('group', $location->get_descendents(true)));
			$module->location = $location;
			$module->removed = false;
		}
		if ($pines->config->com_sales->ready_to_ship == 'invoice') {
			$selector = array('|', 'data' => array(array('status', 'invoiced'), array('status', 'paid')));
		} else {
			$selector = array('&', 'data' => array('status', 'paid'));
		}

		$module->sales = $pines->entity_manager->get_entities(array('class' => com_sales_sale), array('&', 'tag' => array('com_sales', 'sale', $removed ? 'shipping_shipped' : 'shipping_pending')), $selector, $or);
	}

	/**
	 * Creates and attaches a module which lists shippers.
	 */
	public function list_shippers() {
		global $pines;

		$module = new module('com_sales', 'shipper/list', 'content');

		$module->shippers = $pines->entity_manager->get_entities(array('class' => com_sales_shipper), array('&', 'tag' => array('com_sales', 'shipper')));

		if ( empty($module->shippers) )
			pines_notice('There are no shippers.');
	}

	/**
	 * Creates and attaches a module which lists stock.
	 *
	 * @param bool $removed Whether to show stock that is no longer physically in inventory.
	 * @param group $location The location to show stock for.
	 */
	public function list_stock($removed = false, $location = null) {
		global $pines;

		$module = new module('com_sales', 'stock/list', 'content');

		$show_empty = false;
		if ($removed) {
			$module->removed = true;
			$module->location = $_SESSION['user']->group;
			$module->stock = $pines->entity_manager->get_entities(
					array('class' => com_sales_stock),
					array('&', 'tag' => array('com_sales', 'stock')),
					array('!&', 'isset' => 'location')
				);
		} else {
			$module->removed = false;
			if (!isset($location)) {
				$show_empty = true;
				$module->location = $_SESSION['user']->group;
				$module->stock = array();
			} else {
				$module->location = $location;
				$module->stock = $pines->entity_manager->get_entities(
					array('class' => com_sales_stock),
					array('&', 'tag' => array('com_sales', 'stock'), array('isset' => 'location')),
					array('|', 'ref' => array('location', $location->get_descendents(true)))
				);
			}
		}

		if ( empty($module->stock) && !$show_empty )
			pines_notice('No stock found.');
	}

	/**
	 * Creates and attaches a module which lists taxes/fees.
	 */
	public function list_tax_fees() {
		global $pines;

		$module = new module('com_sales', 'taxfee/list', 'content');

		$module->tax_fees = $pines->entity_manager->get_entities(array('class' => com_sales_tax_fee), array('&', 'tag' => array('com_sales', 'tax_fee')));

		if ( empty($module->tax_fees) )
			pines_notice('There are no taxes/fees.');
	}

	/**
	 * Creates and attaches a module which lists transfers.
	 * @param bool $finished Show finished POs instead of pending ones.
	 */
	public function list_transfers($finished = false) {
		global $pines;

		$module = new module('com_sales', 'transfer/list', 'content');

		$module->transfers = $pines->entity_manager->get_entities(
				array('class' => com_sales_transfer),
				array('&',
					'tag' => array('com_sales', 'transfer'),
					'data' => array('finished', $finished)
				)
			);

		if ( empty($module->transfers) )
			pines_notice('There are no transfers.');
	}

	/**
	 * Creates and attaches a module which lists vendors.
	 */
	public function list_vendors() {
		global $pines;

		$module = new module('com_sales', 'vendor/list', 'content');

		$module->vendors = $pines->entity_manager->get_entities(array('class' => com_sales_vendor), array('&', 'tag' => array('com_sales', 'vendor')));

		if ( empty($module->vendors) )
			pines_notice('There are no vendors.');
	}

	/**
	 * Load the product selector.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load_product_select() {
		if (!$this->js_loaded_product) {
			$module = new module('com_sales', 'product/select', 'head');
			$module->render();
			$this->js_loaded_product = true;
		}
	}
	
	/**
	 * Print a form to select a location.
	 *
	 * @param int $location The currently set location to search in.
	 * @return module The form's module.
	 */
	public function location_select_form($location = null) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_sales', 'forms/location_selector', 'content');
		if (!isset($location)) {
			$module->location = $_SESSION['user']->group->guid;
		} else {
			$module->location = $location;
		}

		$pines->page->override_doc($module->render());
		return $module;
	}
	
	/**
	 * Process an instant approval payment.
	 *
	 * @param array &$array The argument array.
	 */
	public function payment_instant(&$array) {
		switch ($array['action']) {
			case 'approve':
				$array['payment']['status'] = 'approved';
				break;
			case 'tender':
			case 'return':
				$array['payment']['status'] = 'tendered';
				$array['payment']['label'] = $array['payment']['entity']->name;
				break;
			case 'void':
				$array['payment']['status'] = 'voided';
				break;
			case 'change':
				$array['ticket']->change_given = true;
				break;
		}
	}

	/**
	 * Process a manager approval payment.
	 *
	 * @param array &$array The argument array.
	 */
	public function payment_manager(&$array) {
		global $pines;
		switch ($array['action']) {
			case 'request':
				$module = new module('com_sales', 'forms/payment_manager');
				$pines->page->override_doc($module->render());
				break;
			case 'approve':
				if (gatekeeper('com_sales/manager')) {
					unset($array['payment']['data']['username']);
					unset($array['payment']['data']['password']);
					$array['payment']['status'] = 'approved';
				} else {
					if ($id = $pines->user_manager->authenticate($array['payment']['data']['username'], $array['payment']['data']['password'])) {
						$user = user::factory($id);
						$array['payment']['status'] = gatekeeper('com_sales/manager', $user) ? 'approved' : 'manager_approval_needed';
					} else {
						$array['payment']['status'] = 'manager_approval_needed';
					}
					unset($array['payment']['data']['username']);
					unset($array['payment']['data']['password']);
				}
				break;
			case 'tender':
			case 'return':
				$array['payment']['status'] = 'tendered';
				$array['payment']['label'] = $array['payment']['entity']->name;
				break;
			case 'void':
				$array['payment']['status'] = 'voided';
				break;
			case 'change':
				$array['ticket']->change_given = true;
				break;
		}
	}

	/**
	 * Creates and attaches a module containing a form for receiving inventory.
	 *
	 * @return module|null The new module on success, nothing on failure.
	 */
	public function print_receive_form() {
		global $pines;

		$selector = array('&', 'tag' => array('com_sales', 'po'), 'data' => array(array('final', true), array('finished', false)));
		
		$module = new module('com_sales', 'stock/formreceive', 'content');
		if (!gatekeeper('com_sales/receivelocation')) {
			$module->pos = (array) $pines->entity_manager->get_entities(
					array('class' => com_sales_po, 'skip_ac' => true),
					$selector
				);
		} else {
			$module->pos = (array) $pines->entity_manager->get_entities(
					array('class' => com_sales_po, 'skip_ac' => true),
					$selector,
					array('|', 'ref' => array('destination', $_SESSION['user']->group->get_descendents(true)))
				);
		}
		$module->categories = (array) $pines->entity_manager->get_entities(
				array('class' => com_sales_category),
				array('&',
					'tag' => array('com_sales', 'category'),
					'data' => array('enabled', true)
				)
			);

		return $module;
	}

	/**
	 * Creates and attaches a module containing a sales total page.
	 *
	 * @return module|null The new module on success, nothing on failure.
	 */
	public function print_sales_total() {
		global $pines;
		$module = new module('com_sales', 'sale/totals', 'content');
		$module->locations = $pines->user_manager->get_groups();
		$module->show_all = gatekeeper('com_sales/totalothersales');

		return $module;
	}

	/**
	 * Sort an array of specs.
	 *
	 * @param array &$array The array to sort.
	 * @return bool True on success, false on failure.
	 */
	public function sort_specs(&$array) {
		return uasort($array, array($this, 'sort_spec'));
	}

	/**
	 * Compare spec items for sorting.
	 *
	 * @param array $a Item a.
	 * @param array $b Item b.
	 * @return int Order.
	 * @access private
	 */
	private function sort_spec($a, $b) {
		$ac = isset($a['order']) ? $a['order'] : $a['name'];
		$bc = isset($b['order']) ? $b['order'] : $b['name'];
		return strnatcmp($ac, $bc);
	}

	/**
	 * Creates and attaches a module which lists a products history.
	 * @param string $serial The serial number of the product to search for.
	 * @param string $sku The sku code of the product(s) to search for.
	 * @param int $start_date The starting date to search for products within.
	 * @param int $end_date The ending date to search for products within.
	 * @param group $location The location to search for products in.
	 */
	public function track_product($serial = null, $sku = null, $start_date = null, $end_date = null, $location = null) {
		global $pines;

		$module = new module('com_sales', 'product/track', 'content');
		$module->items = array();
		// Primary options specify the criteria to search the inventory for.
		$selector = array('&', 'tag' => array('com_sales', 'stock'));
		if (!empty($sku)) {
			$module->sku = $sku;
			$countsheet_code = $sku;
			$selector['ref'] = array('product', $pines->com_sales->get_product_by_code($sku));
		}
		if (!empty($serial)) {
			$module->serial = $countsheet_code = $serial;
			$selector['data'] = array('serial', $serial);
		}
		// Secondary options specify the criteria to search the transactions.
		$secondary_options = array('&');
		if (!isset($location->guid))
			$location = $_SESSION['user']->group;
		$module->location = $location;
		$or = array('|', 'ref' => array('group', $location->get_descendents(true)));

		if (isset($start_date))
			$secondary_options['gte'][] = array('p_cdate', (int) $start_date);
		if (isset($end_date))
			$secondary_options['lte'][] = array('p_cdate', (int) $end_date);
		$module->start_date = $start_date;
		$module->end_date = $end_date;
		$module->all_time = (!isset($start_date) && !isset($end_date));
		$module->stock = $module->transactions = array();
		if (isset($module->serial) || isset($module->sku))
			$module->stock = $pines->entity_manager->get_entities(array('class' => com_sales_stock), $selector);

		if (empty($module->stock)) {
			pines_notice('There are no items matching your query.');
			return;
		}

		foreach ($module->stock as $cur_stock) {
			// Grab all invoices, countsheets, transfers and purchase orders for
			// all stock items with the given serial number / sku.
			$invoices = $pines->entity_manager->get_entities(
					array('class' => com_sales_sale, 'skip_ac' => true),
					$secondary_options,
					$or,
					array('&',
						'tag' => array('com_sales', 'sale'),
						'ref' => array('products', $cur_stock)
					)
				);
			$returns = $pines->entity_manager->get_entities(
					array('class' => com_sales_sale, 'skip_ac' => true),
					$secondary_options,
					$or,
					array('&',
						'tag' => array('com_sales', 'return'),
						'ref' => array('products', $cur_stock)
					)
				);
			$swaps = $pines->entity_manager->get_entities(
					array('class' => com_sales_tx, 'skip_ac' => true),
					$secondary_options,
					$or,
					array('&',
						'tag' => array('com_sales', 'sale_tx'),
						'data' => array('type', 'swap'),
						'ref' => array('item', $cur_stock)
					)
				);
			$countsheets = $pines->entity_manager->get_entities(
					array('class' => com_sales_countsheet, 'skip_ac' => true),
					$secondary_options,
					$or,
					array('&',
						'tag' => array('com_sales', 'countsheet'),
						'array' => array('entries', $countsheet_code)
					)
				);
			$transfers = $pines->entity_manager->get_entities(
					array('class' => com_sales_transfer, 'skip_ac' => true),
					$secondary_options,
					$or,
					array('&',
						'tag' => array('com_sales', 'transfer'),
						'ref' => array('stock', $cur_stock)
					)
				);
			$pos = $pines->entity_manager->get_entities(
					array('class' => com_sales_po, 'skip_ac' => true),
					$secondary_options,
					$or,
					array('&',
						'tag' => array('com_sales', 'po'),
						'ref' => array('received', $cur_stock)
					)
				);
			foreach (array_merge($invoices, $returns, $swaps, $countsheets, $transfers, $pos) as $cur_tx) {
				if (isset($module->transactions[$cur_tx->guid])) {
					$module->transactions[$cur_tx->guid]->qty++;
					if (!in_array($cur_stock->serial, $module->transactions[$cur_tx->guid]->serials))
						$module->transactions[$cur_tx->guid]->serials[] = $cur_stock->serial;
				} else {
					$cur_type = '';
					if ($cur_tx->has_tag('sale')) {
						$tx_info = 'Invoiced';
						$cur_type = 'sale';
					} elseif ($cur_tx->has_tag('return')) {
						$tx_info = 'Returned';
						$cur_type = 'return';
					} elseif ($cur_tx->has_tag('swap')) {
						$tx_info = ucwords($cur_tx->status);
						$cur_type = 'swap';
					} elseif ($cur_tx->has_tag('countsheet')) {
						$tx_info = 'Counted on Countsheet';
						$cur_type = 'countsheet';
					} elseif ($cur_tx->has_tag('transfer')) {
						$tx_info = 'Received on Transfer';
						$cur_type = 'transfer';
					} elseif ($cur_tx->has_tag('po')) {
						$tx_info = 'Received on PO';
						$cur_type = 'po';
					}
					$module->transactions[$cur_tx->guid] = (object) array(
						'product' => $cur_stock->product,
						'type' => $cur_type,
						'entity' => $cur_tx,
						'transaction_info' => $tx_info,
						'qty' => 1,
						'serials' => array($cur_stock->serial)
					);
				}
			}
		}
	}


	/**
	 * Use gaussian rounding to round a number to a certain decimal point.
	 *
	 * @param float $value The number to round.
	 * @param bool $string Whether to return a formatted string, instead of a float.
	 * @param int $decimal The number of decimal points. Defaults to value from config.
	 * @return float|string Float if $string is false, formatted string otherwise.
	 */
	public function round($value, $string = false, $decimal = null) {
		if (!isset($decimal)) {
			global $pines;
			$decimal = $pines->config->com_sales->dec;
		}
		$rnd = pow(10, $decimal);
		$mult = $value * $rnd;
		$value = $this->gaussian_round($mult);
		$value /= $rnd;
		if ($string)
			$value = number_format($value, $decimal, '.', '');
		return ($value);
	}

	/**
	 * Round a number to the nearest integer value using gaussian rounding.
	 * 
	 * @param float $value The number to round.
	 * @return float The rounded number.
	 */
	public function gaussian_round($value) {
		$absolute = abs($value);
		$sign     = ($value == 0 ? 0 : ($value < 0 ? -1 : 1));
		$floored  = floor($absolute);
		if ($absolute - $floored != 0.5) {
			return round($absolute) * $sign;
		}
		if ($floored % 2 == 1) {
			// Closest even is up.
			return ceil($absolute) * $sign;
		}
		// Closest even is down.
		return $floored * $sign;
	}
}

?>