

<?php echo ajax_modal_template_header($template_info['name']) ?>

<?php 

echo form_tag('export-form', url_for('items/export_template','path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING) . '&templates_id=' . filter_var($_GET['templates_id'],FILTER_SANITIZE_STRING)),['class'=>'form-horizontal']) . input_hidden_tag('action','export');  

if(strlen($template_info['template_filename']))
{
    $item = items::get_info(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($current_item_id,FILTER_SANITIZE_STRING));
    
    $pattern = new fieldtype_text_pattern;
    $filename = $pattern->output_singe_text(filter_var($template_info['template_filename'],FILTER_SANITIZE_STRING), filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var_array($item));
}
else
{
    $filename = $template_info['name'] . '_' . filter_var($current_item_id,FILTER_SANITIZE_STRING);
}

if($template_info['type']=='docx')
{
   echo '
    <div class="modal-body ajax-modal-width-790">
        <div class="form-group">
            <label class="col-md-3 control-label">' . TEXT_FILENAME . '</label>
			<div class="col-md-9">
                <div class="input-group input-xlarge">
            		' . input_tag('filename',$filename,['class'=>'form-control required']). '
            		<span class="input-group-addon">
            			.docx
            		</span>
            	</div>
                <label id="filename-error" class="error" for="filename"></label>
            </div>
        </div>  
    </div>
    '; 
   
   echo ajax_modal_template_footer('<i class="fa fa-download" aria-hidden="true"></i> ' . TEXT_DOWNLOAD);
}
else
{
?>


<div class="modal-body ajax-modal-width-790">    

<div id="export_templates_preview">	
	<style>
		<?php echo $template_info['template_css'] ?>
	</style>
		
	<?php echo $template_info['template_header'] . export_templates::get_html(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($current_item_id,FILTER_SANITIZE_STRING),filter_var($_GET['templates_id'],FILTER_SANITIZE_STRING)) . $template_info['template_footer']; ?>
		
</div>

<p>
<?php
	

  echo TEXT_FILENAME . '<br>' . input_tag('filename',$filename,array('class'=>'form-control input-xlarge')); 
?>
</p>

<div><?php echo TEXT_EXT_PRINT_BUTTON_PDF_NOTE ?></div>
</div> 

<?php
  $buttons_html = '
		<button type="button" class="btn btn-primary btn-template-export"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></button> 
		<button type="button" class="btn btn-primary btn-template-export-word"><i class="fa fa-file-word-o" aria-hidden="true"></i></button>'; 
  $buttons_html .= ' <button type="button" class="btn btn-primary btn-template-print"><i class="fa fa-print" aria-hidden="true"></i> ' .  TEXT_PRINT . '</button>';
  echo ajax_modal_template_footer('hide-save-button',$buttons_html);
  
}
?>

</form>  

<script>
  $('.btn-template-export').click(function(){
    $('#action').val('export');
    $('#export-form').attr('target','_self')
    $('#export-form').submit();
  })
  
  $('.btn-template-export-word').click(function(){
    $('#action').val('export_word');
    $('#export-form').attr('target','_self')
    $('#export-form').submit();
  })
  
  $('.btn-template-print').click(function(){
    $('#action').val('print');
    $('#export-form').attr('target','_new')
    $('#export-form').submit();
  })
</script>