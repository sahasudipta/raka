<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>

<?php echo form_tag('login', url_for('entities/user_roles','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . '&entities_id=' .filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&fields_id=' .filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING))) ?>
     
<div class="modal-body">    
<?php echo sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION,user_roles::get_name_by_id(filter_var($_GET['id'],FILTER_SANITIZE_STRING)))?>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>   