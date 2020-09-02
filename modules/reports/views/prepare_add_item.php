<?php echo ajax_modal_template_header(TEXT_ADD) ?>

<?php echo form_tag('prepare_add_item_form', url_for('reports/view','reports_id=' . filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING) ),array('class'=>'form-horizontal')) ?>

<?php
  $report_info_query = db_query("select r.*,e.name as entities_name,e.parent_id as entities_parent_id from app_reports r, app_entities e where e.id=r.entities_id and r.id='" . _get::int('reports_id') . "'");
  $report_info = db_fetch_array($report_info_query);
          
  $entity_info = db_find('app_entities',filter_var($report_info['entities_id'],FILTER_SANITIZE_STRING));
  $entity_cfg = entities::get_cfg(filter_var($report_info['entities_id'],FILTER_SANITIZE_STRING));
  
  $button_title = (strlen(filter_var($entity_cfg['insert_button'],FILTER_SANITIZE_STRING))>0 ? filter_var($entity_cfg['insert_button'],FILTER_SANITIZE_STRING) : TEXT_ADD);
  
  $parent_item_id = '';
  
  //prepare default value for dropdown
  if(isset($_GET["related"]))
  {
  	$related = explode('-',filter_var($_GET["related"],FILTER_SANITIZE_STRING));
  	$path_info = items::get_path_info($related[0],$related[1]);
  	$parent_item_id = $path_info['full_path'] . '/' . $entity_info['id'];  	  	
  }
  
  if(isset($_GET["parent_item_id"]))
  {
  	$related = explode('-',filter_var($_GET["parent_item_id"],FILTER_SANITIZE_STRING));
  	$path_info = items::get_path_info($related[0],$related[1]);
  	$parent_item_id = $path_info['full_path'] . '/' . $entity_info['id'];
  }
?>

<div class="modal-body">
  <div class="form-body">
  
  <div class="ajax-modal-width-790"></div>
  
  <div class="form-group" >
  	<label class="col-md-3 control-label" for="entities_id"><?php echo TEXT_ADD_IN ?></label>
    <div class="col-md-9">	
  	  <?php echo htmlentities(select_tag('parent_item_id',items::get_choices_by_entity($entity_info['id'],$entity_info['parent_id']),$parent_item_id,array('class'=>'form-control chosen-select required'))) ?>
    </div>			
  </div> 
         
   </div>
</div> 
 
<?php echo ajax_modal_template_footer($button_title) ?>

</form>
<?php 
	$params = "redirect_to=report_" . filter_var($report_info["id"],FILTER_SANITIZE_STRING);
		
	if(strstr($app_redirect_to,'calendarreport') or strstr($app_redirect_to,'pivot_calendars'))
	{
		$params = "redirect_to=" . $app_redirect_to . '&start=' . filter_var($_GET['start'],FILTER_SANITIZE_STRING). '&end=' . filter_var($_GET['end'],FILTER_SANITIZE_STRING) . '&view_name=' . filter_var($_GET['view_name'],FILTER_SANITIZE_STRING);
	}	
	elseif(strstr($app_redirect_to,'item_info_page'))
	{
		$params = "redirect_to=" . $app_redirect_to . (isset($_GET['fields']) ? '&fields[' . key(filter_var_array($_GET['fields'])) . ']=' . current(filter_var_array($_GET['fields'])) : '');
	}
	
	if(isset($_GET['mail_groups_id']))
	{
		$params .= '&mail_groups_id=' . filter_var($_GET['mail_groups_id'],FILTER_SANITIZE_STRING);
	}
?>
<script>
  $(function() { 
                  
    $('#prepare_add_item_form').validate({ignore:'',      
      submitHandler: function(form)
      {                              
        path = $('#parent_item_id').val();
        url = '<?php echo url_for("items/form", $params . (isset($_GET["related"]) ? "&related=" . filter_var($_GET["related"],FILTER_SANITIZE_STRING):"")) ?>'+'&path='+path;
            
        open_dialog(url)
        
        return false;                
      }
      });
                                                                        
  });
</script>
