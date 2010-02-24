<?php
/**
 * Lists all calendar events and allows users to manipulate them.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zhuber@gmail.com>
 * @copyright Zak Huber
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
<script type='text/javascript'>
// <![CDATA[
	$(document).ready(function() {
		// Create the calendar object.
		$('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			dragOpacity: {
				agenda: .5,
				'': 0.85
			},
			firstHour: 8,
			theme: true,
			editable: true,
			events: [
				<?php
				// Read in all existing events.
				$event_counter = 0;
				foreach ($this->events as $cur_event) {
					if ($event_counter > 0)
						echo ',';
					echo '{';
					if ($cur_event->id != 0) {
						echo 'group: true,';
						echo 'id: '. $cur_event->id .',';
						echo '_id: '. $cur_event->id .',';
						echo 'guid: '. $cur_event->guid .',';
					} else {
						echo 'group: false,';
						echo 'id: '. $cur_event->guid .',';
						echo '_id: '. $cur_event->guid .',';
					}
					echo 'title: \''. $cur_event->title .'\',';
					echo 'start: '. $cur_event->start .',';
					echo 'end: '. $cur_event->end .',';
					echo 'className: \''. $cur_event->color .'\',';
					//echo 'allDay: '. $cur_event->all_day;
					echo ($cur_event->all_day) ? 'allDay: true' : 'allDay: false';
					echo '}';
					$event_counter++;
				} ?>
			],
			eventClick: function(calEvent,jsEvent,view) {
				if (calEvent.selected == true) {
					calEvent.selected = false;
					$(this).removeClass('ui-state-disabled');
				} else {
					calEvent.selected = true;
					$(this).addClass('ui-state-disabled');
				}
			},
			eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
				event.selected = false;
				save_calendar();
			},
			eventDragStop: function( event, jsEvent, ui, view ) {
				// Deselect all events when changing the calendar timespan.
				var events = $("#calendar").fullCalendar('clientEvents');
				jQuery.each(events, function(i, val) {
					val.selected = false;
				});
				$("#calendar").fullCalendar('refetchEvents');
			},
			eventResize: function(event,dayDelta,minuteDelta,revertFunc,jsEvent,ui,view) {
				event.selected = false;
				save_calendar();
			},
			viewDisplay: function(view) {
				// Deselect all events when changing the calendar timespan.
				var events = $("#calendar").fullCalendar('clientEvents');
				jQuery.each(events, function(i, val) {
					val.selected = false;
				});
				$("#calendar").fullCalendar('refetchEvents');
			}
		});
		// Right-Click Menu.
		$('#calendar').vscontext({menuBlock: 'vs-context-menu'});
	});
	// Add new events to the calendar, mostly for duplicating events.
	function add_events(events) {
		$.ajax({
			url: "<?php echo pines_url('com_hrm', 'addevents'); ?>",
			type: "POST",
			dataType: "html",
			data: {"events": events},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to add events to the calendar.");
			}
		});
		window.location = "<?php echo pines_url('com_hrm', 'editcalendar'); ?>";
	}
	// Save all of the calendar events by exporting the data to their entities.
	function save_calendar() {
		var events = $("#calendar").fullCalendar('clientEvents');
		var events_dump = ''; //new Array();
		//var events_array = new Array();
		//var event_count = 0;
		jQuery.each(events, function(i, val) {
			if (val.group) {
				events_dump += val.guid.toString() +'|'+ val.id.toString() +'|';
				//events_array[0] = val.guid.toString();
				//events_array[1] = val.id.toString();
			} else {
				events_dump += val.id.toString() + '|0|';
				//events_array[0] = val.id.toString();
				//events_array[1] = '0';
			}
			var event_start = val.start.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d\:]+)\s.*/, '$1');
			var event_end = val.end.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d\:]+)\s.*/, '$1');
			events_dump += val.title +'|'+ event_start +'|'+ event_end +'|'+ val.className +'|'+ val.allDay +',';
			//events_array[2] = val.title;
			//events_array[3] = event_start;
			//events_array[4] = event_end;
			//events_array[5] = val.className;
			//events_array[6] = val.allDay;
			//events_dump[event_count] = events_array;
			//event_count++;
		});
		$.ajax({
			url: "<?php echo pines_url('com_hrm', 'savecalendar'); ?>",
			type: "POST",
			dataType: "html",
			data: {"events": events_dump},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to save the calendar.");
			}
		});
	}
// ]]>
</script>
<div id="calendar">
	<div class="vs-context-menu">
		<ul>
			<li class="copy"><a href="javascript:copy();" id="menu_1">Duplicate</a></li>
			<li class="unlink"><a href="javascript:unlink();" id="menu_2">Unlink</a></li>
			<li class="edit"><a href="javascript:edit();" id="menu_3">Edit</a></li>
			<li class="delete seprator"><a href="javascript:del();" id="menu_4">Delete</a></li>
			<li class="clear seprator"><a href="javascript:clear();" id="menu_5">Clear_All</a></li>
			<li class="help"><a href="javascript:help();" id="menu_6">Help</a></li>
		</ul>
	</div>
</div>