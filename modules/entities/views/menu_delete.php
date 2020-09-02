<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	<h4 class="modal-title"><?php echo TEXT_HEADING_DELETE ?></h4>
</div>

<?php echo form_tag('delete', url_for('entities/menu','action=delete&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING)),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
     
<?php 
	$obj = db_find('app_entities_menu',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
	echo htmlentities(sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION,filter_var($obj['name'],FILTER_SANITIZE_STRING)));
?>

  </div>
</div>
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form>    
    