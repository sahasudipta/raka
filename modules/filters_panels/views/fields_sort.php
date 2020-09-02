
<?php echo ajax_modal_template_header(TEXT_SORT_VALUES) ?>

<?php echo form_tag('choices_form', url_for('filters_panels/fields','action=sort&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&panels_id=' . filter_var($_GET['panels_id'],FILTER_SANITIZE_STRING)),array('class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
 
<div class="dd" id="choices_sort">
<ol class="dd-list">      
<?php 
$fields_query = db_query("select pf.*, f.name as field_name, f.type as field_type from app_filters_panels_fields pf, app_fields f where pf.fields_id=f.id and pf.panels_id='" . _get::int('panels_id') . "' order by pf.sort_order");

if(db_num_rows($fields_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>';

while($fields = db_fetch_array($fields_query))
{
	echo '<li class="dd-item" data-id="' . filter_var($fields['id'],FILTER_SANITIZE_STRING) . '"><div class="dd-handle">' . fields_types::get_option(filter_var($fields['field_type'],FILTER_SANITIZE_STRING),'name',filter_var($fields['field_name'],FILTER_SANITIZE_STRING)) . '</div></li>';
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
