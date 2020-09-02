<?php if($app_module=='ganttchart' and $app_action=='dhtmlx'): ?>
<script src="js/dhtmlxGantt-6.3.2/codebase/dhtmlxgantt.js"></script>
<script src="js/dhtmlxGantt-6.3.2/codebase/ext/dhtmlxgantt_marker.js"></script>
<script src="js/dhtmlxGantt-6.3.2/codebase/ext/dhtmlxgantt_fullscreen.js"></script>
<script src="js/dhtmlxGantt-6.3.2/codebase/api.js"></script>
<?php 
	if(is_file('js/dhtmlxGantt-6.3.2/codebase/locale/locale_' . APP_LANGUAGE_SHORT_CODE . '.js'))
	{
		echo '<script src="js/dhtmlxGantt-6.3.2/codebase/locale/locale_' . APP_LANGUAGE_SHORT_CODE . '.js"></script>'; 
	}
?>
<link href="js/dhtmlxGantt-6.3.2/codebase/dhtmlxgantt.css" rel="stylesheet">
<link rel="stylesheet" href="js/dhtmlxGantt-6.3.2/codebase/skins/dhtmlxgantt_meadow.css?v=20180227">
<?php endif ?>

<?php if(in_array($app_module_path,['ext/pivot_calendars/view','ext/calendar/personal','ext/calendar/public','ext/calendar/report','dashboard/dashboard','dashboard/reports','dashboard/reports_groups']) ): ?>
<link rel=stylesheet href="js/fullcalendar-3.10.0/fullcalendar.min.css" type="text/css">
<link rel=stylesheet href="js/fullcalendar-3.10.0/fullcalendar.print.min.css"  media="print">
<link rel=stylesheet href="js/fullcalendar-scheduler-1.9.4/scheduler.min.css" type="text/css">
<?php endif ?>


<?php if(in_array($app_module_path,['ext/pivotreports/view','dashboard/dashboard','dashboard/reports'])): ?>
<link rel="stylesheet" type="text/css" href="js/pivottable-master/dist/pivot.css">
<link rel="stylesheet" type="text/css" href="js/pivottable-master/dist/c3.min.css">
<?php endif ?>


<?php if($app_module=='timeline_reports' and $app_action=='view'): ?>
<link rel="stylesheet" type="text/css" href="js/timeline-2.9.1/timeline.css">
<?php endif ?>

<link rel="stylesheet" type="text/css" href="js/app-chat/app-chat.css?v=1">

<link rel="stylesheet" type="text/css" href="js/app-mail/app-mail.css?v=1">