<?php
/**
 * com_pgentity's configuration defaults.
 *
 * @package Components\pgentity
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'use_plperl',
		'cname' => 'Use PL/Perl Functions',
		'description' => 'This speeds up regular expression matching a lot, but requires the Perl Procedural Language to be installed on your Postgres server.',
		'value' => true,
	),
	array(
		'name' => 'cache',
		'cname' => 'Cache Entities',
		'description' => 'Cache recently retrieved entities to speed up database queries.',
		'value' => false,
	),
	array(
		'name' => 'cache_threshold',
		'cname' => 'Cache Threshold',
		'description' => 'Cache entities after they\'re accessed this many times.',
		'value' => 4,
	),
	array(
		'name' => 'cache_limit',
		'cname' => 'Cache Limit',
		'description' => 'The number of recently retrieved entities to cache. If you\'re running out of memory, try lowering this value. 0 means unlimited.',
		'value' => 50,
	),
	array(
		'name' => 'log_query_on_error',
		'cname' => 'Log Query on Error',
		'description' => 'If a query causes an error, log the entire query. (Due to potential security risks, this is off by default.)',
		'value' => false,
	),
);

?>