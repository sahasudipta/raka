<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>

<?php echo form_tag('login', url_for('entities/infopage_entityfield_filters','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . '&reports_id=' . filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING). '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&related_entities_id=' . filter_var($_GET['related_entities_id'],FILTER_SANITIZE_STRING). '&fields_id=' . filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING))) ?>
    
<div class="modal-body">    
<?php echo TEXT_ARE_YOU_SURE?>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>  