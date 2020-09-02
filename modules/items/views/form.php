<div class="items-form-conteiner">
<?php 

  $header_menu_button = ''; 
  
  //add templates menu in header
  if(class_exists('entities_templates'))
  {
    $header_menu_button = entities_templates::render_modal_header_menu($current_entity_id);
  }
  
  echo ajax_modal_template_header($header_menu_button . (strlen($entity_cfg->get('window_heading'))>0 ? $entity_cfg->get('window_heading') : TEXT_INFO));
  
  $is_new_item = (!isset($_GET['id']) ? true:false);
  
  $app_items_form_name = (isset($_GET['is_submodal']) ? 'sub_items_form':'items_form');
?>

<?php echo form_tag($app_items_form_name, url_for('items/','action=save' . (isset($_GET['id']) ? '&id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING):'') ),array('enctype'=>'multipart/form-data','class'=>'form-horizontal')) ?>
<div class="modal-body">
  <div class="form-body">
     
<?php echo input_hidden_tag('path',filter_var($_GET['path'],FILTER_SANITIZE_STRING)) ?>
<?php echo input_hidden_tag('redirect_to',$app_redirect_to) ?>
<?php echo input_hidden_tag('parent_item_id',$parent_entity_item_id) ?>
<?php if(isset($_GET['related'])) echo input_hidden_tag('related',filter_var($_GET['related'],FILTER_SANITIZE_STRING)) ?>
<?php if(isset($_GET['gotopage'])) echo input_hidden_tag('gotopage[' . key(filter_var_array($_GET['gotopage'])). ']',current(filter_var_array($_GET['gotopage']))) ?>
<?php if(isset($_GET['mail_groups_id'])) echo input_hidden_tag('mail_groups_id',filter_var($_GET['mail_groups_id'],FILTER_SANITIZE_STRING)) ?>

<?php    
  $html_user_password ='
          <div class="form-group">
          	<label class="col-md-3 control-label" for="password"><span class="required-label">*</span>' . TEXT_FIELDTYPE_USER_PASSWORD_TITLE . '</label>
            <div class="col-md-9">	
          	  ' . input_password_tag('password',array('class'=>'form-control input-medium','autocomplete'=>'off')) . '
              ' . tooltip_text(TEXT_FIELDTYPE_USER_PASSWORD_TOOLTIP) . '
            </div>			
          </div>        
        ';   


  $fields_access_schema = users::get_fields_access_schema(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($app_user['group_id'],FILTER_SANITIZE_STRING));
  
  //check fields access rules for item
  if(isset($_GET['id']))
  {
  	$access_rules = new access_rules(filter_var($current_entity_id,FILTER_SANITIZE_STRING), $obj);
  	$fields_access_schema += $access_rules->get_fields_view_only_access();
  }
      
  $count_tabs = db_count('app_forms_tabs',filter_var($current_entity_id,FILTER_SANITIZE_STRING),"entities_id");
  
  if($count_tabs>1)
  {        
    //put tabs heading html in array
    $html_tab = array();
    
    $tabs_query = db_fetch_all('app_forms_tabs',"entities_id='" . db_input(filter_var($current_entity_id,FILTER_SANITIZE_STRING)) . "' order by  sort_order, name");
    while($tabs = db_fetch_array($tabs_query))
    {
      $html_tab[filter_var($tabs['id'],FILTER_SANITIZE_STRING)] = '<li class="form_tab_' . filter_var($tabs['id'],FILTER_SANITIZE_STRING) . '"><a data-toggle="tab" href="#form_tab_' . filter_var($tabs['id'],FILTER_SANITIZE_STRING) . '">' . filter_var($tabs['name'],FILTER_SANITIZE_STRING) . '</a></li>';      
    }
              
    $count_tabs = 0;
    
    //put tags content html in array    
    $html_tab_content = array();
    
    $tabs_query = db_fetch_all('app_forms_tabs',"entities_id='" . db_input($current_entity_id) . "' order by  sort_order, name");
    while($tabs = db_fetch_array($tabs_query))
    {
              
      $html_tab_content[filter_var($tabs['id'],FILTER_SANITIZE_STRING)] = '
        <div class="tab-pane fade" id="form_tab_' . filter_var($tabs['id'],FILTER_SANITIZE_STRING) . '">
      ' . (strlen($tabs['description']) ? '<p>' . filter_var($tabs['description'],FILTER_SANITIZE_STRING) . '</p>' : '');
                  
      $count_fields = 0;
      $fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type not in (" . fields_types::get_type_list_excluded_in_form() . ") and  f.entities_id='" . db_input(filter_var($current_entity_id,FILTER_SANITIZE_STRING)) . "' and f.forms_tabs_id=t.id and f.forms_tabs_id='" . db_input(filter_var($tabs['id'],FILTER_SANITIZE_STRING)) . "' order by t.sort_order, t.name, f.sort_order, f.name");
      while($v = db_fetch_array($fields_query))
      {
        //check field access
        if(isset($fields_access_schema[filter_var($v['id'],FILTER_SANITIZE_STRING)])) continue;
        
        //handle params from GET
        if(isset($_GET['fields'][filter_var($v['id'],FILTER_SANITIZE_STRING)])) $obj['field_' . filter_var($v['id'],FILTER_SANITIZE_STRING)] = db_prepare_input(filter_var($_GET['fields'],FILTER_SANITIZE_STRING)[filter_var($v['id'],FILTER_SANITIZE_STRING)]);
        
        if($v['type']=='fieldtype_section')
        {
        	$html_tab_content[filter_var($tabs['id'],FILTER_SANITIZE_STRING)] .= '<div class="form-group-' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '">' . fields_types::render(filter_var($v['type'],FILTER_SANITIZE_STRING),$v,$obj,array('count_fields'=>$count_fields)) . '</div>';
        }
        elseif($v['type']=='fieldtype_dropdown_multilevel')
        {
        	$html_tab_content[filter_var($tabs['id'],FILTER_SANITIZE_STRING)] .= fields_types::render(filter_var($v['type'],FILTER_SANITIZE_STRING),filter_var_array($v),$obj,array('parent_entity_item_id'=>filter_var($parent_entity_item_id,FILTER_SANITIZE_STRING), 'form'=>'item', 'is_new_item'=>$is_new_item));
        }
        else
        {        
        	$v['is_required'] = (in_array(filter_var($v['type'],FILTER_SANITIZE_STRING),array('fieldtype_user_firstname','fieldtype_user_lastname','fieldtype_user_username','fieldtype_user_email')) ?  1 : filter_var($v['is_required'],FILTER_SANITIZE_STRING));
        	
	        $html_tab_content[filter_var($tabs['id'],FILTER_SANITIZE_STRING)] .='
	          <div class="form-group form-group-' . filter_var($v['id'],FILTER_SANITIZE_STRING) . ' form-group-' . filter_var($v['type'],FILTER_SANITIZE_STRING) . '">
	          	<label class="col-md-3 control-label" for="fields_' . filter_var($v['id'],FILTER_SANITIZE_STRING)  . '">' . 
	              ($v['is_required']==1 ? '<span class="required-label">*</span>':'') .
	              ($v['tooltip_display_as']=='icon' ? tooltip_icon(filter_var($v['tooltip'],FILTER_SANITIZE_STRING)) :'') .
	              fields_types::get_option(filter_var($v['type'],FILTER_SANITIZE_STRING),'name',filter_var($v['name'],FILTER_SANITIZE_STRING)) . 
	            '</label>
	            <div class="col-md-9">	
	          	  <div id="fields_' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '_rendered_value">' . fields_types::render(filter_var($v['type'],FILTER_SANITIZE_STRING),filter_var_array($v),$obj,array('parent_entity_item_id'=>filter_var($parent_entity_item_id,FILTER_SANITIZE_STRING), 'form'=>'item', 'is_new_item'=>$is_new_item)) . '</div>
	              ' . ($v['tooltip_display_as']!='icon' ? tooltip_text(filter_var($v['tooltip'],FILTER_SANITIZE_STRING)):'') . '
	            </div>			
	          </div>        
	        '; 
        }
        
        //including user password field for new user form
        if($v['type']=='fieldtype_user_username' and !isset($_GET['id']))
        {
          $html_tab_content[filter_var($tabs['id'],FILTER_SANITIZE_STRING)] .= $html_user_password;
        }
        
        $count_fields++;     
      }
      
      $html_tab_content[filter_var($tabs['id'],FILTER_SANITIZE_STRING)] .= '</div>';
      
      //if there is no fields for this tab then remove content from array
      if($count_fields==0)
      {
        unset($html_tab_content[filter_var($tabs['id'],FILTER_SANITIZE_STRING)]);
      }
      
      $count_tabs++;
    }
        
    
    $html = '<ul class="nav nav-tabs" id="form_tabs">';
    
    //build tabs heading and skip tabs with no fields
    $count = 0;
    foreach(filter_var_array($html_tab_content) as $tab_id=>$content)
    {
      $html .= ($count==0 ? str_replace('class="form_tab_' . $tab_id,'class="form_tab_' . $tab_id . ' active',$html_tab[$tab_id]) : $html_tab[$tab_id]);
      $count++;
    }
    
    $html .= '</ul>';
    
    $html .= '<div class="tab-content">';
    
    //build tabs content
    $count = 0;
    foreach(filter_var_array($html_tab_content) as $tab_id=>$content)
    {
      $html .= ($count==0 ? str_replace('tab-pane fade','tab-pane fade active in',$content) : $content); 
      $count++;
    }
    
    $html .= '</div>';
  
  }
  else
  {  
  	$count_fields = 0;
    $html = '';
    
    $tabs_query = db_fetch_all('app_forms_tabs',"entities_id='" . db_input(filter_var($current_entity_id,FILTER_SANITIZE_STRING)) . "' order by  sort_order, name");
    $tabs = db_fetch_array($tabs_query);
    
    if(strlen($tabs['description']))
    {
    	$html .= '<p>' . filter_var($tabs['description'],FILTER_SANITIZE_STRING) . '</p>';
    }
    
    
    $fields_query = db_query("select f.* from app_fields f where f.type not in (" . fields_types::get_type_list_excluded_in_form(). ") and  f.entities_id='" . db_input($current_entity_id) . "' order by f.sort_order, f.name");
    while($v = db_fetch_array($fields_query))
    {       
      //check field access
      if(isset($fields_access_schema[$v['id']])) continue;
      
      //handle params from GET
      if(isset($_GET['fields'][filter_var($v['id'],FILTER_SANITIZE_STRING)])) $obj['field_' . filter_var($v['id'],FILTER_SANITIZE_STRING)] = db_prepare_input(filter_var($_GET['fields'],FILTER_SANITIZE_STRING)[filter_var($v['id'],FILTER_SANITIZE_STRING)]);
      
      if($v['type']=='fieldtype_section')
      {
      	$html .= '<div class="form-group-' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '">' . fields_types::render(filter_var($v['type'],FILTER_SANITIZE_STRING),filter_var_array($v),$obj,array('count_fields'=>$count_fields)) . '</div>';
      }
      elseif($v['type']=='fieldtype_dropdown_multilevel')
      {
      	$html .= fields_types::render(filter_var($v['type'],FILTER_SANITIZE_STRING),filter_var_array($v),$obj,array('parent_entity_item_id'=>filter_var($parent_entity_item_id,FILTER_SANITIZE_STRING), 'form'=>'item', 'is_new_item'=>$is_new_item));
      }
      else
      {      
      	$v['is_required'] = (in_array(filter_var($v['type'],FILTER_SANITIZE_STRING),array('fieldtype_user_firstname','fieldtype_user_lastname','fieldtype_user_username','fieldtype_user_email')) ?  1 : filter_var($v['is_required'],FILTER_SANITIZE_STRING));
      	
	      $html .='
	          <div class="form-group form-group-' . filter_var($v['id'],FILTER_SANITIZE_STRING) . ' form-group-' . filter_var($v['type'],FILTER_SANITIZE_STRING) . '">
	          	<label class="col-md-3 control-label" for="fields_' . filter_var($v['id'],FILTER_SANITIZE_STRING)  . '">' .                
	              ($v['is_required']==1 ? '<span class="required-label">*</span>':'') .
	              ($v['tooltip_display_as']=='icon' ? tooltip_icon($v['tooltip']) :'') . 
	              fields_types::get_option(filter_var($v['type'],FILTER_SANITIZE_STRING),'name',filter_var($v['name'],FILTER_SANITIZE_STRING)) .               
	            '</label>
	            <div class="col-md-9">	
	          	  <div id="fields_' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '_rendered_value">' . fields_types::render(filter_var($v['type'],FILTER_SANITIZE_STRING),filter_var_array($v),$obj,array('parent_entity_item_id'=>filter_var($parent_entity_item_id,FILTER_SANITIZE_STRING), 'form'=>'item', 'is_new_item'=>$is_new_item)) . '</div>
	              ' . ($v['tooltip_display_as']!='icon' ? tooltip_text($v['tooltip']):'') . '
	            </div>			
	          </div>        
	        ';  
      }
        
      //including user password field for new user form
      if($v['type']=='fieldtype_user_username' and !isset($_GET['id']))
      {
        $html .= $html_user_password;
      } 
      
      $count_fields++;
    }
    
  }
  
  echo $html;
  
  
  //render templates fields values
  if(class_exists('entities_templates'))
  {
    echo entities_templates::render_fields_values(filter_var($current_entity_id,FILTER_SANITIZE_STRING));
  }
?>
 </div>
</div>
 
<?php 
	$extra_button = '';
	
	//prepare back button for sub-modal
	if(isset($_GET['is_submodal']))
	{
		$extra_button = '<button type="button" class="btn btn-default btn-submodal-back"><i class="fa fa-angle-left" aria-hidden="true"></i> ' . TEXT_BUTTON_BACK. '</button>';
	}
	
	//prepare delete button for gantt report
	require(component_path('items/items_form_gantt_delete_prepare'));
		
	echo ajax_modal_template_footer(false,$extra_button); 
	
	//check ruels for hidden fields by access
	if(isset($_GET['id']))
	{
		echo forms_fields_rules::prepare_hidden_fields(filter_var($current_entity_id,FILTER_SANITIZE_STRING), $obj, $fields_access_schema);
	}
?>
 
</form> 
</div> 

<?php 
	if(is_ext_installed())
	{
//		$smart_input = new smart_input($current_entity_id);
//		echo $smart_input->render();
	}
?>

<?php require(component_path('items/items_form.js')); ?> 