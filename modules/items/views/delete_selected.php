<?php echo ajax_modal_template_header(TEXT_HEADING_DELETE) ?>

<?php echo form_tag('delete_selected_form', url_for('items/delete_selected','action=delete_selected&reports_id=' . filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING) . '&path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING))) ?>

<?php echo input_hidden_tag('redirect_to',$app_redirect_to) ?>

<?php
if(!isset($app_selected_items[$_GET['reports_id']])) $app_selected_items[$_GET['reports_id']] = array();

if(count($app_selected_items[$_GET['reports_id']])==0)
{
  echo '
    <div class="modal-body">    
      <div>' . TEXT_PLEASE_SELECT_ITEMS . '</div>
    </div>    
  ' . ajax_modal_template_footer('hide-save-button');
}
else
{
?>

<div class="modal-body" >
  <div id="modal-body-content">    
    <p><?php echo TEXT_DELETE_SELECTED_CONFIRMATION ?></p> 
    
<?php 
if(entities::has_subentities($current_entity_id))
{
	$show_delete_confirm = false;
	$entities_query = db_query("select id from app_entities where parent_id='" . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . "'");
	while($entities = db_fetch_array($entities_query))
	{
		$items_query = db_query("select id from app_entity_" . filter_var($entities['id'],FILTER_SANITIZE_STRING) . " limit 1");
		if($items = db_fetch_array($items_query))
		{
			$show_delete_confirm = true;
			break;
		}
		
	}
	
	if($show_delete_confirm)
	{	
		echo '<div style="margin-top: 15px;" class="alert alert-warning">' . sprintf(TEXT_WARNING_ITEM_HAS_SUB_ITEM,$app_entities_cache[$current_entity_id]['name']). '</div><div class="single-checkbox"><label>' . input_checkbox_tag('delete_confirm',1,['class'=>'required']) . ' ' . TEXT_CONFIRM_DELETE . '</label></div>';
	}
	
}
?>    
     
  </div>
</div> 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

<?php } ?>
</form>  

<script>
 $('#delete_selected_form').validate({
	 submitHandler: function(form){
			app_prepare_modal_action_loading(form)
			form.submit();
		},
	 errorPlacement: function(error, element) {
		 error.insertAfter(".single-checkbox");                       
   }
 });
</script> 