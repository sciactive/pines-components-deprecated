<?php
/**
 * Includes for the calendar.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 *
 * Built upon:
 *
 * FullCalendar Created by Adam Shaw
 * http://arshaw.com/fullcalendar/
 *
 * Very Simple Context Menu Plugin by Intekhab A Rizvi
 * http://intekhabrizvi.wordpress.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadcss("<?php echo $pines->config->rela_location; ?>components/com_hrm/includes/fullcalendar.css");
	pines.loadjs("<?php echo $pines->config->rela_location; ?>components/com_hrm/includes/fullcalendar.min.js");
	<?php if (gatekeeper('com_hrm/editcalendar')) { ?>
		pines.loadcss("<?php echo $pines->config->rela_location; ?>components/com_hrm/includes/context/css/vscontext.css");
		pines.loadjs("<?php echo $pines->config->rela_location; ?>components/com_hrm/includes/context/vscontext.jquery.js");
		pines.loadjs("<?php echo $pines->config->rela_location; ?>components/com_hrm/includes/context/menu_action.js");
	<?php } ?>
	// ]]>
</script>