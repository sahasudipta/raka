<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>

<?php echo form_tag('login', url_for('entities/listing_sections','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . '&listing_types_id=' . filter_var($_GET['listing_types_id'],FILTER_SANITIZE_STRING) . '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) ?>
    
<div class="modal-body">    
<?php echo TEXT_ARE_YOU_SURE?>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>  