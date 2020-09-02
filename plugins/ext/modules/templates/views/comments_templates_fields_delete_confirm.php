<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>

<?php 
  $obj = db_find('app_ext_comments_templates_fields',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
  $field = db_find('app_fields',filter_var($obj['fields_id'],FILTER_SANITIZE_STRING)); 
?>

<?php echo form_tag('login', url_for('ext/templates/comments_templates_fields','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . '&templates_id=' . filter_var($obj['templates_id'],FILTER_SANITIZE_STRING))) ?>
    
<div class="modal-body">    
<?php echo htmlentities(sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION,filter_var($field['name'],FILTER_SANITIZE_STRING)))?>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>    
    
 
