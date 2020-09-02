
<?php echo ajax_modal_template_header(TEXT_WARNING) ?>

<?php echo form_tag('backup', url_for('tools/db_backup','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING))) ?>
<div class="modal-body">    
<?php
	$backup_info = db_find('app_backups',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
	echo htmlentities(sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION,'#' . filter_var($backup_info['id'],FILTER_SANITIZE_STRING) . ' - ' . format_date_time(filter_var($backup_info['date_added'],FILTER_SANITIZE_STRING))))
?>
</div> 
<?php echo ajax_modal_template_footer(TEXT_DELETE) ?>

</form>  