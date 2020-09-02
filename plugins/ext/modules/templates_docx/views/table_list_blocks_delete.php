<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>

<?php echo form_tag('login', url_for('ext/templates_docx/table_list_blocks','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . '&templates_id=' . filter_var($template_info['id'],FILTER_SANITIZE_STRING) . '&parent_block_id=' . filter_var($parent_block['id'],FILTER_SANITIZE_STRING))) ?>
    
<div class="modal-body">    
<?php echo TEXT_ARE_YOU_SURE ?>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>    