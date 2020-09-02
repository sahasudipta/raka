<?php echo ajax_modal_template_header(TEXT_HEADING_EXPORT) ?>



<?php
if(!isset($app_selected_items[$_GET['reports_id']])) $app_selected_items[$_GET['reports_id']] = array();

if(count($app_selected_items[$_GET['reports_id']])==0)
{
  echo '
    <div class="modal-body">    
      <div>' . TEXT_PLEASE_SELECT_ITEMS . '</div>
    </div>    
  ' . ajax_modal_template_footer('hide-save-button');
}
else
{
    
    $fields_access_schema = users::get_fields_access_schema($current_entity_id,$app_user['group_id']);
    
    $attachments_fields = [];
    
    $fields_query = db_query("select f.* from app_fields f, app_forms_tabs t where f.forms_tabs_id = t.id and f.type in ('" . implode("','",fields_types::get_attachments_types()) . "') and f.entities_id='" . db_input($current_entity_id) . "' order by t.sort_order, f.sort_order, f.name");
    while($v = db_fetch_array($fields_query))
    {
        if(isset($fields_access_schema[$v['id']]))
        {
            if($fields_access_schema[$v['id']]=='hide') continue;
        }
        
        $attachments_fields[] = '<div><label>' . input_checkbox_tag('fields[]',$v['id'],array('id'=>'fields_' . $v['id'],'class'=>'export_fields export_fields_' . $v['id'],'checked'=>'checked')) . ' ' . fields_types::get_option($v['type'],'name',$v['name']) . '</label></div>';
    }
?>


<div class="modal-body">

<ul class="nav nav-tabs" id="items_export_tabs">
  <li class="active"><a href="#select_fields_tab"  data-toggle="tab"><?php echo TEXT_SELECT_FIELD_TO_EXPORT ?></a></li>
  <li><a href="#my_templates_tab"  data-toggle="tab"><?php echo TEXT_MY_TEMPLATES ?></a></li>
  
<?php
  if(count($attachments_fields))
  {
      echo '<li><a href="#attachments_tab"  data-toggle="tab">' . TEXT_ATTACHMENTS . '</a></li>';
  }
?>   
</ul>
    
<div class="tab-content">
  <div class="tab-pane fade active in" id="select_fields_tab">
	
	<div id="items_export_templates_button"></div>
	<div id="items_export_templates_selected" style="display:none">
		<br>
		<div class="alert alert-info">
			<span id="items_export_templates_selected_data"></span>
			<div style="float: right"><a title="<?php echo addslashes(TEXT_UPDATE_SELECTED_TEMPLATE_INFO)?>" href="javascript: update_items_export_templates()"><i class="fa fa-refresh" aria-hidden="true" ></i> <?php echo TEXT_BUTTON_UPDATE ?></a></div>
		</div>
	</div>

<?php echo form_tag('export_form', url_for('items/export','path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING)),array('class'=>'form-inline'))  . input_hidden_tag('action','export') . input_hidden_tag('reports_id',filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING)) ?>  
<p>
<?php


$exclude_types = ($app_entities_cache[filter_var($current_entity_id,FILTER_SANITIZE_STRING)]['parent_id']==0 ? ",'fieldtype_parent_item_id'":'');



$tabs_query = db_fetch_all('app_forms_tabs',"entities_id='" . db_input($current_entity_id) . "' order by  sort_order, name");
while($tabs = db_fetch_array($tabs_query))
{
  $fileds_html = '';
  
  $fields_query = db_query("select f.*,if(f.type in ('fieldtype_id','fieldtype_date_added','fieldtype_created_by'),-1,f.sort_order) as field_sort_order from app_fields f where  f.type not in ('fieldtype_action','fieldtype_section' {$exclude_types}) and f.entities_id='" . db_input(filter_var($current_entity_id,FILTER_SANITIZE_STRING)) . "' and forms_tabs_id='" . db_input(filter_var($tabs['id'],FILTER_SANITIZE_STRING)) . "' order by field_sort_order, f.name");
  while($v = db_fetch_array($fields_query))
  {      
    //check field access
    if(isset($fields_access_schema[$v['id']]))
    {
      if($fields_access_schema[$v['id']]=='hide') continue;
    }
    
    if(in_array($v['type'],array('fieldtype_attachments','fieldtype_textarea','fieldtype_textarea_wysiwyg','fieldtype_input_file','fieldtype_attachments')))
    {
      $checked = '';
    }
    else
    {
      $checked = 'checked';
    }
        
    $fileds_html .= '<div><label>' . input_checkbox_tag('fields[]',filter_var($v['id'],FILTER_SANITIZE_STRING),array('id'=>'fields_' . filter_var($v['id'],FILTER_SANITIZE_STRING),'class'=>'export_fields export_fields_' . filter_var($v['id'],FILTER_SANITIZE_STRING) . ' fields_tabs_' . filter_var($tabs['id'],FILTER_SANITIZE_STRING),'checked'=>$checked)) . ' ' . fields_types::get_option(filter_var($v['type'],FILTER_SANITIZE_STRING),'name',filter_var($v['name'],FILTER_SANITIZE_STRING)) . '</label></div>'; 
  }
  
  if(strlen($fileds_html)>0)
  {
    echo '<p><div><label><b>' . input_checkbox_tag('all_tab_fields_' . filter_var($tabs['id'],FILTER_SANITIZE_STRING),'',array('checked'=>'checked','onChange'=>'select_all_by_classname(\'all_tab_fields_' . filter_var($tabs['id'],FILTER_SANITIZE_STRING) . '\',\'fields_tabs_' . filter_var($tabs['id'],FILTER_SANITIZE_STRING) . '\')')) . filter_var($tabs['name'],FILTER_SANITIZE_STRING). '</b></label></div>' . $fileds_html . '</p>';
  }
} 

	echo '<div><label>' . input_checkbox_tag('export_url','url',array('class'=>'export_fields export_fields_url','checked'=>'checked')) . ' ' . TEXT_URL . '</label></div>';

?>
</p>

<br>


	
<div class="form-group">	
	<?php
			$current_entity_info = db_find('app_entities',filter_var($current_entity_id,FILTER_SANITIZE_STRING));
			echo input_tag('filename',filter_var($current_entity_info['name'],FILTER_SANITIZE_STRING),array('class'=>'form-control input-large required')) 
	?>
</div>
<div class="form-group">
	<label  for="file_extension">&nbsp;</label>
	<?php 
		$choices = ['xlsx'=>'.xlsx','csv'=>'.csv','txt'=>'.txt'];
		echo select_tag('file_extension',$choices,'xlsx',array('class'=>'form-control input-small'))
	?>	
</div>	

<br><br>
	
	<div>
		<?php 
			echo '
				<button type="button" class="btn btn-primary" id="btn_export"><i class="fa fa-file-excel-o"></i> ' . TEXT_BUTTON_EXPORT . '</button> 
				<button type="button" class="btn btn-primary" id="btn_export_print"><i class="fa fa-print"></i> ' . TEXT_PRINT . '</button>'; 
		?>	
		
	</div>


</form>

  </div>
  <div class="tab-pane fade" id="my_templates_tab">
		
		<?php echo form_tag('export_templates_form', url_for('items/export','action=save_templates&path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING)),array('class'=>'form-inline')) ?>
		<?php echo TEXT_ADD_NEW_TEMPLATE ?>
			<div class="row">
				<div class="col-md-7">					
					<?php echo input_tag('templates_name','',array('class'=>'form-control required','placeholder'=>TEXT_ENTER_TEMPLATE_NAME)) ?>
					<?php echo input_hidden_tag('export_fields_list') ?>				
				</div>
				<div class="col-md-5">				
					<?php echo submit_tag(TEXT_BUTTON_ADD) ?>
				</div>
			</div>
		</form>
		<p><?php echo tooltip_text(TEXT_SAVE_TAMPLATE_NOTE)?></p>
		<div id="action_response_msg"></div>
		<br>
			
		<div id="items_export_templates"></div>	
		 
  </div>
  
<?php
  if(count($attachments_fields))
  {
      $html = '
        <div class="tab-pane fade" id="attachments_tab">
        ' . form_tag('export_form', url_for('items/export_attachments','path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING)),array('class'=>'form-inline'))  . input_hidden_tag('action','export') . input_hidden_tag('reports_id',(int)filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING)) . '
            ' . implode('',$attachments_fields). '

              <div class="input-group input-large margin-top-10 margin-bottom-10">
                ' . input_tag('filename',filter_var($current_entity_info['name'],FILTER_SANITIZE_STRING),array('class'=>'form-control required','minlength'=>1,'required'=>'required')) . '
                <span class="input-group-addon">.zip</span>
              </div>

            <button type="submit" class="btn btn-primary" id="btn_export"><i class="fa fa-file-archive-o"></i> ' . TEXT_BUTTON_EXPORT . '</button>
          </form>
        </div>

        
        ';
      
      
      
      echo $html;
  }
?>
</div>
  

</div> 

<?php echo ajax_modal_template_footer('hide-save-button') ?>

<?php require(component_path('items/export.js')); ?>

<?php } ?>
  
