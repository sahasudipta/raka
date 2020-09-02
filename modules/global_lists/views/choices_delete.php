
<?php echo ajax_modal_template_header($heading) ?>

<?php echo form_tag('login', url_for('global_lists/choices','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . '&lists_id=' .filter_var($_GET['lists_id'],FILTER_SANITIZE_STRING))) ?>
     
<div class="modal-body">    
<?php echo $content?>
</div> 
 
<?php echo ajax_modal_template_footer($button_title) ?>

</form>    
    
 
