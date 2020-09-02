
<?php echo ajax_modal_template_header($heading) ?>

<?php echo form_tag('delete_item_form', url_for('items/','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . '&path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING))) ?>

<?php echo input_hidden_tag('redirect_to',$app_redirect_to) ?>
<?php if(isset($_GET['gotopage'])) echo input_hidden_tag('gotopage[' . key(filter_var_array($_GET['gotopage'])). ']',current(filter_var_array($_GET['gotopage']))) ?>
    
<div class="modal-body">    
<?php echo $content?>
</div>
 
<?php echo ajax_modal_template_footer($button_title) ?>

</form>   

<script>
 $('#delete_item_form').validate({
	 submitHandler: function(form){
			app_prepare_modal_action_loading(form)
			form.submit();
		},
	 errorPlacement: function(error, element) {
		 error.insertAfter(".single-checkbox");                       
   }
	});
</script> 
    
 
