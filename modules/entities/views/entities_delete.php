
<?php echo ajax_modal_template_header($heading) ?>

<?php echo form_tag('login', url_for('entities/','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING))) ?>
<div class="modal-body">    
<?php echo $content?>
</div> 
<?php echo ajax_modal_template_footer($button_title) ?>

</form>    
    
 
