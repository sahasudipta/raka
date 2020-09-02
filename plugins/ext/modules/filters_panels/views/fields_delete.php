<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	<h4 class="modal-title"><?php echo TEXT_HEADING_DELETE ?></h4>
</div>

<?php echo form_tag('delete', url_for('ext/filters_panels/fields','action=delete&redirect_to=' . $app_redirect_to . '&panels_id=' . filter_var($_GET['panels_id'],FILTER_SANITIZE_STRING). '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING)),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
     
<?php 	
	echo TEXT_ARE_YOU_SURE;
?>

  </div>
</div>
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>  