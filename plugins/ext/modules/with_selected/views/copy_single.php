<?php echo ajax_modal_template_header(TEXT_HEADING_COPY) ?>

<?php echo form_tag('form-copy-to', url_for('ext/with_selected/copy_single','action=copy_single&path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING)),array('class'=>'form-horizontal')) ?>


<div class="modal-body ajax-modal-width-790" >
  <div id="modal-body-content">    
    <p><?php echo TEXT_COPY_SINGLE_CONFIRMATION ?></p>

<?php
  $entity_info = db_find('app_entities',filter_var($current_entity_id,FILTER_SANITIZE_STRING));
  if($entity_info['parent_id']>0)
  {
  	  	
    $report_info = reports::create_default_entity_report(filter_var($entity_info['id'],FILTER_SANITIZE_STRING), 'entity_menu');
        
    //check if parent reports was not set
    if($report_info['parent_id']==0)
    {
      reports::auto_create_parent_reports($report_info['id']);
      
      $report_info = db_find('app_reports',$report_info['id']);
    }
    
    $path_parsed = items::parse_path(filter_var($_GET['path'],FILTER_SANITIZE_STRING));
    $parent_path_info = items::get_path_info($path_parsed['parent_entity_id'],$path_parsed['parent_entity_item_id']);
    $copy_to_default = $parent_path_info['full_path'] . '/' . $current_entity_id;
    
    $choices = [];       
    $path_parsed = items::parse_path($app_path);    	 
    $choices[$path_parsed['parent_entity_item_id']]  = items::get_heading_field(filter_var($path_parsed['parent_entity_id'],FILTER_SANITIZE_STRING),filter_var($path_parsed['parent_entity_item_id'],FILTER_SANITIZE_STRING));
    $selected = $path_parsed['parent_entity_item_id'];
    
                   
    echo '
      		<div class="form-group">
						<label class="col-md-3 control-label" for="settings_copy_comments">' . TEXT_COPY_TO . '</label>
							<div class="col-md-9">    						
                ' . select_entities_tag('copy_to',$choices,$selected,['entities_id'=>filter_var($entity_info['parent_id'],FILTER_SANITIZE_STRING),'class'=>'form-control required','data-placeholder'=>TEXT_ENTER_VALUE]) . '
              </div>
					</div>
    ';
  }
  
  require(component_path('ext/with_selected/copy_options'));
    
?>  
  </div>
</div> 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_COPY) ?>


</form>  

<script>
  $(function(){
    $('#form-copy-to').submit(function(){
      
      $('button[type=submit]',this).css('display','none')
      $('#modal-body-content').css('visibility','hidden').css('height','1px');             
      $('#modal-body-content').after('<div class="ajax-loading"></div>');      
      
      $('#modal-body-content').load($(this).attr('action'),$(this).serializeArray(),function(){
        $('.ajax-loading').css('display','none');          
        $('#modal-body-content').css('visibility','visible').css('height','auto');
      })
    
      return false;
    })  
  })
</script>