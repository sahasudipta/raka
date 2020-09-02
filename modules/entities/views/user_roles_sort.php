<?php echo ajax_modal_template_header(TEXT_SORT_VALUES) ?>

<?php echo form_tag('choices_form', url_for('entities/user_roles','action=sort&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&fields_id=' . filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING)),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
 
<div class="dd" id="choices_sort">      
<?php 
$count_query = db_query("select count(*) as total from app_user_roles where fields_id = '" . db_input(_get::int('fields_id')) . "' order by sort_order, name");
$count = db_fetch_array($count_query);

if($count['total']>0)
{
	$html = '<ol class="dd-list">';

	$choices_query = db_query("select * from app_user_roles where fields_id = '" . db_input(filter_var(_get::int('fields_id'),FILTER_SANITIZE_STRING)). "' order by sort_order, name");

	while($v = db_fetch_array($choices_query))
	{
		$html .= '<li class="dd-item" data-id="' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '"><div class="dd-handle">' . filter_var($v['name'],FILTER_SANITIZE_STRING) . '</div></li>';
	}

	$html .= '</ol>';
	
	echo $html;
}
?>
</div>
      
   </div>
</div> 
<?php echo input_hidden_tag('choices_sorted') ?> 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>
$(function(){
  $('#choices_sort').nestable({
      group: 1
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