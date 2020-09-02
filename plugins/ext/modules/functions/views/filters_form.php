
<?php echo ajax_modal_template_header(TEXT_HEADING_REPORTS_FILTER_IFNO) ?>

<?php echo form_tag('reports_filters', url_for('ext/functions/filters','action=save&functions_id=' . filter_var($_GET['functions_id'],FILTER_SANITIZE_STRING) . '&reports_id=' . filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING) . (isset($_GET['parent_reports_id']) ? '&parent_reports_id=' . filter_var($_GET['parent_reports_id'],FILTER_SANITIZE_STRING):'').  (isset($_GET['id']) ? '&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING):'') ),array('class'=>'form-horizontal')) ?>

<div class="modal-body">
  <div class="form-body">
     

  <div class="form-group">
  	<label class="col-md-3 control-label" for="fields_id"><?php echo TEXT_SELECT_FIELD ?></label>
    <div class="col-md-9">	
  	  <?php echo select_tag('fields_id',fields::get_filters_choices($reports_info['entities_id'],false,"'fieldtype_formula'"),$obj['fields_id'],array('class'=>'form-control required','onChange'=>'load_fitlers_options(this.value)')) ?>
    </div>			
  </div>     
     
<div id="filters_options"></div>
 
   </div>
</div> 
 
<?php echo ajax_modal_template_footer() ?>

</form> 

<script>


  $(function() { 
    $('#reports_filters').validate();
    
    load_fitlers_options($('#fields_id').val());                                                                      
  });
  
  
function load_fitlers_options(fields_id)
{
  $('#filters_options').html('<div class="ajax-loading"></div>');
  
  $('#filters_options').load('<?php echo url_for("reports/filters_options")?>',{fields_id:fields_id, id:'<?php echo $obj["id"] ?>'},function(response, status, xhr) {
    if (status == "error") {                                 
       $(this).html('<div class="alert alert-error"><b>Error:</b> ' + xhr.status + ' ' + xhr.statusText+'<div>'+response +'</div></div>')                    
    }
    else
    {   
      appHandleUniform();
    }
  });
}  
  
</script>  

    
 
