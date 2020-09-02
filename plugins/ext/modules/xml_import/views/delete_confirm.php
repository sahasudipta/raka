<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>

<?php $obj = db_find('app_ext_xml_import_templates',filter_var($_GET['id'],FILTER_SANITIZE_STRING)); ?>

<?php echo form_tag('login', url_for('ext/xml_import/templates','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING) )) ?>
    
<div class="modal-body">    
<?php echo filter_var(sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION,filter_var($obj['name'],FILTER_SANITIZE_STRING)),FILTER_SANITIZE_STRING)?>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>    
    
 
