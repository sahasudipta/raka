<?php echo ajax_modal_template_header(TEXT_SORT_VALUES) ?>

<?php echo form_tag('choices_form', url_for('entities/listing_sections','action=sort&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&listing_types_id=' . filter_var($_GET['listing_types_id'],FILTER_SANITIZE_STRING)),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
 
<div class="dd" id="choices_sort">      
<ol class="dd-list">
<?php 
	$filters_query = db_query("select * from app_listing_sections where listing_types_id='" . db_input(_get::int('listing_types_id')) . "' order by sort_order, name");
	while($v = db_fetch_array($filters_query))
	{
		$title = '';
		
		if(strlen($v['name']))
		{
			$title = filter_var($v['name'],FILTER_SANITIZE_STRING);
		}
		elseif(strlen($v['fields']))
		{
			$choices = [];
			$fields_query = db_query("select * from app_fields where id in (" . filter_var($v['fields'],FILTER_SANITIZE_STRING) . ") order by field(id," . filter_var($v['fields'],FILTER_SANITIZE_STRING) . ")");
			while($fields = db_fetch_array($fields_query))
			{
				$choices[] = fields_types::get_option(filter_var($fields['type'],FILTER_SANITIZE_STRING),'name',filter_var($fields['name'],FILTER_SANITIZE_STRING));
			}
			 
			$title = implode(', ',$choices);
		}
		
		echo '<li class="dd-item" data-id="' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '"><div class="dd-handle">' . filter_var($title,FILTER_SANITIZE_STRING) . '</div></li>';
	}
?>
</ol>
</div>
      
   </div>
</div> 
<?php echo input_hidden_tag('choices_sorted') ?> 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
$(function(){
  $('#choices_sort').nestable({
      group: 0,
      maxDepth:1,
  }).on('change',function(e){
    output = $(this).nestable('serialize');
    
    if (window.JSON) 
    {
      output = window.JSON.stringify(output);
      $('#choices_sorted').val(output);
    } 
    else 
    {
      alert('JSON browser support required!');      
    }    
  })
})

</script>