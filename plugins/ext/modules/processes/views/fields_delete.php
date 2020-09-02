<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>

<?php 
  $obj = db_find('app_ext_processes_actions_fields',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
  $field = db_find('app_fields',$obj['fields_id']); 
?>

<?php echo form_tag('login', url_for('ext/processes/fields','process_id=' . filter_var(_get::int('process_id'),FILTER_SANITIZE_STRING) . '&actions_id=' . filter_var(_get::int('actions_id'),FILTER_SANITIZE_STRING) . '&action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING))) ?>
    
<div class="modal-body">    
<?php echo htmlentities(sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION,filter_var($field['name'],FILTER_SANITIZE_STRING)))?>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>    
    
 
