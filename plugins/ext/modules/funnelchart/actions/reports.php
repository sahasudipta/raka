<?php

//check access
if($app_user['group_id']>0)
{
  redirect_to('dashboard/access_forbidden');
}

switch($app_module_action)
{
  case 'save':
  
      $sql_data = array('name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),                        
                        'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),
                        'type'=>filter_var($_POST['type'],FILTER_SANITIZE_STRING),
                        'in_menu'=>(isset($_POST['in_menu']) ? filter_var($_POST['in_menu'],FILTER_SANITIZE_STRING):0),
                        'users_groups'=>(isset($_POST['users_groups']) ? implode(',',filter_var_array($_POST['users_groups'])):''),
                        'group_by_field'=>filter_var($_POST['group_by_field'],FILTER_SANITIZE_STRING),
                        'sum_by_field'=>(isset($_POST['sum_by_field']) ? implode(',',filter_var_array($_POST['sum_by_field'])):''),
                        'exclude_choices'=>(isset($_POST['exclude_choices']) ? implode(',',filter_var_array($_POST['exclude_choices'])):''),
                        );
                        
                                                            
      if(isset($_GET['id']))
      {        
        db_perform('app_ext_funnelchart',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
      }
      else
      {                               
        db_perform('app_ext_funnelchart',$sql_data);                    
      }
                                          
      redirect_to('ext/funnelchart/reports');
      
    break;
  case 'delete':
      $obj = db_find('app_ext_funnelchart',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
      
      db_delete_row('app_ext_funnelchart',filter_var($_GET['id'],FILTER_SANITIZE_STRING));   
      
      $report_info_query = db_query("select * from app_reports where reports_type='funnelchart" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)). "'");
      if($report_info = db_fetch_array($report_info_query))
      {          
        reports::delete_reports_by_id($report_info['id']);                                 
      }                           
      
      $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$obj['name']),'success');
      
      redirect_to('ext/funnelchart/reports');
    break; 
    
  case 'get_entities_fields_choices':
    	$html = '';
    	$field_query = db_query("select * from app_fields where id='" . _post::int('fields_id'). "'");
    	if($field = db_fetch_array($field_query))
    	{
    		$entity_info = db_find('app_entities',filter_var($field['entities_id'],FILTER_SANITIZE_STRING));
    		
    		if(isset($_POST['id']))
    		{
    			$obj = db_find('app_ext_funnelchart',filter_var($_POST['id'],FILTER_SANITIZE_STRING));
    		}
    		else
    		{
    			$obj = db_show_columns('app_ext_funnelchart');
    		}
    
    		$cfg = new fields_types_cfg(filter_var($field['configuration'],FILTER_SANITIZE_STRING));
    
    		if(filter_var($field['type'],FILTER_SANITIZE_STRING) == 'fieldtype_entity')
    		{
    			$choices = funnelchart::get_choices_by_entity($cfg->get('entity_id'));
    		}
    		elseif(filter_var($field['type'],FILTER_SANITIZE_STRING) == 'fieldtype_parent_item_id')
			{
				$choices = funnelchart::get_choices_by_entity($entity_info['parent_id']);
			}
			elseif($field['type'] == 'fieldtype_users' or filter_var($field['type'],FILTER_SANITIZE_STRING) == 'fieldtype_users_ajax')
    		{
    			$choices = users::get_choices_by_entity(filter_var($field['entities_id'],FILTER_SANITIZE_STRING));
    		}
    		elseif($field['type'] == 'fieldtype_created_by')
    		{
    			$choices = users::get_choices_by_entity(filter_var($field['entities_id'],FILTER_SANITIZE_STRING),'create');
    		}
    		else
    		{	
	    		if($cfg->get('use_global_list')>0)
	    		{
	    			$choices = global_lists::get_choices($cfg->get('use_global_list'));
	    		}
	    		else
	    		{
	    			$choices = fields_choices::get_choices(filter_var($field['id'],FILTER_SANITIZE_STRING));
	    		}
    		}
    
    		$html .= '
         <div class="form-group">
          	<label class="col-md-3 control-label" for="allowed_groups">' . TEXT_EXT_EXCLUDE_CHOICES . '</label>
            <div class="col-md-9">
          	   ' .  select_tag('exclude_choices[]',$choices,$obj['exclude_choices'],array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple')) . '
            </div>
          </div>
        ';
    	}
    	 
    	echo $html;
    	exit();
    	break;    
  case 'get_entities_fields':
      
        $entities_id = filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING);
        $entities_info = db_find('app_entities',filter_var($entities_id,FILTER_SANITIZE_STRING));
        
        $obj = array();

        if(isset($_POST['id']))
        {
          $obj = db_find('app_ext_funnelchart',filter_var($_POST['id'],FILTER_SANITIZE_STRING));  
        }
        else
        {
          $obj = db_show_columns('app_ext_funnelchart');
        }
        
        $html = '';
        
        if($entities_info['parent_id']>0)
        {	
        	$html .= '
        		<div class="form-group">
        		<label class="col-md-3 control-label" for="in_menu">' .  tooltip_icon(TEXT_EXT_IN_MENU_SUBENTITY_REPORT) . TEXT_IN_MENU . '</label>
        	    <div class="col-md-9">	
        	  	  <div class="checkbox-list"><label class="checkbox-inline">' .  input_checkbox_tag('in_menu','1',array('checked'=>filter_var($obj['in_menu'],FILTER_SANITIZE_STRING))) . '</label></div>
        	    </div>			
        	  </div>';
        }
        
        $choices = array();
        $fields_query = db_query("select f.*, if(f.type in (" . fields_types::get_reserverd_data_types_list() . "),-1,t.sort_order) as tab_sort_order from app_fields f,  app_forms_tabs t where f.forms_tabs_id=t.id  and f.type in ('fieldtype_stages','fieldtype_dropdown','fieldtype_autostatus','fieldtype_radioboxes','fieldtype_users','fieldtype_entity', 'fieldtype_entity_ajax','fieldtype_grouped_users','fieldtype_entity_multilevel','fieldtype_dropdown_multiple','fieldtype_checkboxes','fieldtype_created_by'" . ($entities_info['parent_id']>0 ? ",'fieldtype_parent_item_id'":''). ") and f.entities_id='" . db_input(filter_var($entities_id,FILTER_SANITIZE_STRING)) . "' order by tab_sort_order, t.name, f.sort_order, f.name");
        while($fields = db_fetch_array($fields_query))
        {        	
        	if(filter_var($fields['type'],FILTER_SANITIZE_STRING)=='fieldtype_parent_item_id')
        	{
        		$choices[filter_var($fields['id'],FILTER_SANITIZE_STRING)] = TEXT_FIELDTYPE_PARENT_ITEM_ID_TITLE . ' (' . entities::get_name_by_id($entities_info['parent_id']) . ')';
        	}
        	elseif(filter_var($fields['type'],FILTER_SANITIZE_STRING)=='fieldtype_created_by')
        	{
        		$choices[filter_var($fields['id'],FILTER_SANITIZE_STRING)] = TEXT_FIELDTYPE_CREATEDBY_TITLE;
        	}
        	else
        	{
          	$choices[filter_var($fields['id'],FILTER_SANITIZE_STRING)] = filter_var($fields['name'],FILTER_SANITIZE_STRING);
        	}
        }
        
        $html .= '
         <div class="form-group">
          	<label class="col-md-3 control-label" for="allowed_groups">' . TEXT_EXT_GROUP_BY_FIELD . '</label>
            <div class="col-md-9">	
          	   ' .  select_tag('group_by_field',$choices,$obj['group_by_field'],array('class'=>'form-control input-large required','onChange'=>'ext_get_entities_fields_choices()')) . '
               ' . tooltip_text(TEXT_EXT_GROUP_BY_FIELD_INFO) . '
            </div>			
          </div>
        ';
        
        $html .= '<div id="fields_chocies_list"></div>';
        
        $choices = array();        
        $fields_query = db_query("select * from app_fields where type in ('fieldtype_input_numeric','fieldtype_input_numeric_comments','fieldtype_formula','fieldtype_js_formula','fieldtype_mysql_query','fieldtype_days_difference','fieldtype_hours_difference') and entities_id='" . db_input($entities_id) . "'");
        while($fields = db_fetch_array($fields_query))
        {
        	$choices[filter_var($fields['id'],FILTER_SANITIZE_STRING)] = filter_var($fields['name'],FILTER_SANITIZE_STRING);
        }
        
        $html .= '
         <div class="form-group">
          	<label class="col-md-3 control-label" for="allowed_groups">' . TEXT_EXT_SUM_BY_FIELD . '</label>
            <div class="col-md-9">
          	   ' .  select_tag('sum_by_field[]',$choices,$obj['sum_by_field'],array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple')) . '
               ' . tooltip_text(TEXT_EXT_SUM_BY_FIELD_INFO) . '
            </div>
          </div>
        ';
                
        echo $html;
        
      exit();
    break;
}