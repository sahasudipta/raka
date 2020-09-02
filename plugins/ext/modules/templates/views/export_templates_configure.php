
<ul class="page-breadcrumb breadcrumb">
  <li><?php echo link_to(TEXT_EXT_EXPORT_TEMPLATES,url_for('ext/templates/export_templates'))?><i class="fa fa-angle-right"></i></li>
  <li><?php echo htmlentities($template_info['entities_name']) ?><i class="fa fa-angle-right"></i></li>
  <li><?php echo htmlentities($template_info['name']) ?></li>
</ul>

<p><?php echo TEXT_EXT_EXPORT_TEMPLATES_TIP ?></p>

<?php 
	echo export_templates::get_available_fields_for_all_entities(filter_var($template_info['entities_id'],FILTER_SANITIZE_STRING));
?>

<?php echo form_tag('export_templates_form', url_for('ext/templates/export_templates','action=save_description&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING)),array('class'=>'form-horizontal')) ?>

<div class="row">
  <div  class="col-md-12">
    <?php echo textarea_tag('export_templates_description',$template_info['description']) ?>
    
    <br>

		<?php echo submit_tag(TEXT_BUTTON_SAVE) . ' ' . button_tag(TEXT_BUTTON_CANCEL,url_for('ext/templates/export_templates'),false,array('class'=>'btn btn-default'))  ?>
		
		<br>					
		
  </div>
</div>

</form>

<script>

$(function(){
        
  use_editor_full('export_templates_description',true)
     
  $('.insert_to_template_description').click(function(){
    html = $(this).html().trim();
    CKEDITOR.instances.export_templates_description.insertText(html);
  })
})
  
</script>
