
<?php echo ajax_modal_template_header($heading) ?>

<?php echo form_tag('login', url_for('entities/forms','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . '&entities_id=' .filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) ?>
    
<?php if(isset($_GET['redirect_to']))echo input_hidden_tag('redirect_to',filter_var($_GET['redirect_to'],FILTER_SANITIZE_STRING)) ?>

<div class="modal-body">    
<?php echo $content?>
</div>
 
<?php echo ajax_modal_template_footer($button_title) ?>

</form>    
    
 
