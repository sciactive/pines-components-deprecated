<?php
/**
 * A view to load Nivo Slider.
 *
 * @package Pines
 * @subpackage com_nivoslider
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadcss("<?php echo htmlentities($pines->config->rela_location); ?>components/com_nivoslider/includes/nivo-slider.css");
	pines.loadjs("<?php echo htmlentities($pines->config->rela_location); ?>components/com_nivoslider/includes/<?php echo $pines->config->debug_mode ? 'jquery.nivo.slider.js' : 'jquery.nivo.slider.pack.js'; ?>");
	// ]]>
</script>