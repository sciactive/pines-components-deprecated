<?php
/**
 * Show a page.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	$entity = com_content_page::factory((int) $_REQUEST['id']);
} else {
	$entity = $pines->entity_manager->get_entity(
			array('class' => com_content_page),
			array('&',
				'tag' => array('com_content', 'page'),
				'strict' => array(
					array('alias', $_REQUEST['a']),
					array('enabled', true)
				)
			)
		);
}

if (!isset($entity->guid) || !$entity->ready())
	throw new HttpClientException(null, 404);

// Set the default variant for pages.
if ($pines->config->com_content->page_variant && $pines->com_content->is_variant_valid($pines->config->com_content->page_variant)) {
	$cur_template = $pines->current_template;
	$pines->config->$cur_template->variant = $pines->config->com_content->page_variant;
}

// Check for and set the variant for the current template.
if (isset($entity->variants[$pines->current_template]) && $pines->com_content->is_variant_valid($entity->variants[$pines->current_template])) {
	$cur_template = $pines->current_template;
	$pines->config->$cur_template->variant = $entity->variants[$pines->current_template];
}

// Page title.
if ($entity->title_use_name || !isset($entity->title))
	$title = format_content($entity->name);
else
	$title = format_content($entity->title);
switch ($entity->get_option('title_position')) {
	case 'prepend':
		$pines->page->title_pre("$title - ");
		break;
	case 'append':
		$pines->page->title(" - $title");
		break;
	case 'replace':
		$pines->page->title_set($title);
		break;
}

// Meta tags.
if ($entity->meta_tags) {
	$module = new module('com_content', 'meta_tags', 'head');
	$module->entity = $entity;
}

if ($entity->get_option('show_breadcrumbs')) {
	$module = new module('com_content', 'breadcrumb', 'breadcrumbs');
	$module->entity = $entity;
}

$entity->print_page();

?>