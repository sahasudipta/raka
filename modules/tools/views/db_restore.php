<?php echo ajax_modal_template_header(TEXT_WARNING) ?>

<?php echo form_tag('backup', url_for('tools/db_restore_process','action=restore_by_id&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING))); ?> 

<div class="modal-body">    
<?php 
$backup_info = db_find('app_backups',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
echo htmlentities(sprintf(TEXT_DB_RESTORE_CONFIRMATION,filter_var($backup_info['filename'],FILTER_SANITIZE_STRING)))?>
</div>

<?php echo ajax_modal_template_footer(TEXT_BUTTON_RESTORE) ?>

</form>  