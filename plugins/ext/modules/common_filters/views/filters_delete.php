
<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>

<?php echo form_tag('login', url_for('ext/common_filters/filters','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . '&reports_id=' . filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING). (isset($_GET['parent_reports_id']) ? '&parent_reports_id=' . filter_var($_GET['parent_reports_id'],FILTER_SANITIZE_STRING):''))) ?>
<?php echo input_hidden_tag('redirect_to',$app_redirect_to) ?>
<?php if(isset($_GET['path'])) echo input_hidden_tag('path',filter_var($_GET['path'],FILTER_SANITIZE_STRING)) ?>
<div class="modal-body">    
<?php echo TEXT_ARE_YOU_SURE?>
</div> 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>  
    
 
