<?php

if (!app_session_is_registered('processes_filter'))
{
	$processes_filter = 0;
	app_session_register('processes_filter');
}

$app_title = app_set_title(TEXT_EXT_PROCESSES);

switch($app_module_action)
{
	case 'set_processes_filter':
		$processes_filter = $_POST['processes_filter'];
	
		redirect_to('ext/processes/processes');
		break;
  case 'save':
    $sql_data = array('name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
                      'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),  
                      'button_title'=>filter_var($_POST['button_title'],FILTER_SANITIZE_STRING),
                      'button_position'=>(isset($_POST['button_position']) ? implode(',',filter_var_array($_POST['button_position'])) : ''),
                      'button_color'=>filter_var($_POST['button_color'],FILTER_SANITIZE_STRING),
                      'button_icon'=>filter_var($_POST['button_icon'],FILTER_SANITIZE_STRING),
                      'users_groups'=>(isset($_POST['users_groups']) ? implode(',', filter_var_array($_POST['users_groups'])) : ''),
                      'assigned_to'=>(isset($_POST['assigned_to']) ? implode(',', filter_var_array($_POST['assigned_to'])) : ''),
                      'access_to_assigned'=>(isset($_POST['access_to_assigned']) ? implode(',', filter_var_array($_POST['access_to_assigned'])) : ''),                      
                      'confirmation_text'=>filter_var($_POST['confirmation_text'],FILTER_SANITIZE_STRING),
                      'allow_comments'=>(isset($_POST['allow_comments']) ? 1 : 0),
                      'preview_prcess_actions'=>(isset($_POST['preview_prcess_actions']) ? 1 : 0),
                      'notes' => strip_tags(filter_var($_POST['notes'],FILTER_SANITIZE_STRING)),  
                      'payment_modules'=>(isset($_POST['payment_modules']) ? implode(',',filter_var_array($_POST['payment_modules'])) : ''),
                      'is_active'=>(isset($_POST['is_active']) ? 1 : 0),
                      'apply_fields_access_rules'=>(isset($_POST['apply_fields_access_rules']) ? 1 : 0),
                      'apply_fields_display_rules'=>(isset($_POST['apply_fields_display_rules']) ? 1 : 0),                      
                      'hide_entity_name'=>(isset($_POST['hide_entity_name']) ? 1 : 0),
                      'success_message'=>filter_var($_POST['success_message'],FILTER_SANITIZE_STRING),   
                      'redirect_to_items_listing'=>filter_var($_POST['redirect_to_items_listing'],FILTER_SANITIZE_STRING),                 
                      'disable_comments'=>(isset($_POST['disable_comments']) ? 1 : 0),
                      'javascript_in_from'=>filter_var($_POST['javascript_in_from'],FILTER_SANITIZE_STRING),
                      'javascript_onsubmit'=>filter_var($_POST['javascript_onsubmit'],FILTER_SANITIZE_STRING),
                      'sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),
                      );
        
    if(isset($_GET['id']))
    {               	
    	$process_info = db_find('app_ext_processes',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
    	 
    	//check entity and if it's changed remove process action
    	if($process_info['entities_id']!=$_POST['entities_id'])
    	{    		    	
    		 $actions_query = db_query("select * from app_ext_processes_actions where process_id=" . _get::int('id'));
    		 while($actions = db_fetch_array($actions_query))
    		 {    		 	    		 
    		 	 db_query("delete from app_ext_processes_actions where id='" . filter_var($actions['id'],FILTER_SANITIZE_STRING) . "'");
    		 	 db_query("delete from app_ext_processes_actions_fields where actions_id='" . db_input(filter_var($actions['id'],FILTER_SANITIZE_STRING)) . "'");
    		 }
    		 
    		 $reports_info_query = db_query("select * from app_reports where reports_type='process" . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . "'");
    		 if($reports_info = db_fetch_array($reports_info_query))
    		 {
    		 	 db_query("delete from app_reports_filters where reports_id='" . db_input(filter_var($reports_info['id'],FILTER_SANITIZE_STRING)) . "'");
    		 	 db_query("delete from app_reports where id='" . db_input(filter_var($reports_info['id'],FILTER_SANITIZE_STRING)) . "'");
    		 }
    	}
    	
      db_perform('app_ext_processes',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");            
    }
    else
    {              
      db_perform('app_ext_processes',$sql_data);   
      
      $insert_id = db_insert_id();                           
    }
        
    redirect_to('ext/processes/processes');      
  break;
  
  case 'delete':
      if(isset($_GET['id']))
      {      
        $obj = db_find('app_ext_processes',filter_var($_GET['id'],FILTER_SANITIZE_STRING));  
        
        db_query("delete from app_ext_processes where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        
        $actions_query = db_query("select * from app_ext_processes_actions where process_id=" . _get::int('id'));
        while($actions = db_fetch_array($actions_query))
        {        	
        	db_query("delete from app_ext_processes_actions where id='" . db_input(filter_var($actions['id'],FILTER_SANITIZE_STRING)) . "'");
        	db_query("delete from app_ext_processes_actions_fields where actions_id='" . db_input(filter_var($actions['id'],FILTER_SANITIZE_STRING)) . "'");
        	db_query("delete from app_ext_processes_clone_subitems where actions_id='" . db_input(filter_var($actions['id'],FILTER_SANITIZE_STRING)) . "'");
        	
        	$reports_info_query = db_query("select * from app_reports where reports_type='process_action" . filter_var($actions['id'],FILTER_SANITIZE_STRING) . "'");
        	if($reports_info = db_fetch_array($reports_info_query))
        	{
        		db_query("delete from app_reports_filters where reports_id='" . db_input(filter_var($reports_info['id'],FILTER_SANITIZE_STRING)) . "'");
        		db_query("delete from app_reports where id='" . db_input(filter_var($reports_info['id'],FILTER_SANITIZE_STRING)) . "'");
        	}
        }
        
        $reports_info_query = db_query("select * from app_reports where reports_type='process" . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . "'");
        if($reports_info = db_fetch_array($reports_info_query))
        {
        	db_query("delete from app_reports_filters where reports_id='" . db_input(filter_var($reports_info['id'],FILTER_SANITIZE_STRING)) . "'");
        	db_query("delete from app_reports where id='" . db_input(filter_var($reports_info['id'],FILTER_SANITIZE_STRING)) . "'");
        }
                                                                         
        redirect_to('ext/processes/processes');  
      }
    break; 
    
    case 'get_entities_buttons_positions':
    
    	$entities_id = filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING);
    
    	$obj = array();
    
    	if(isset($_POST['id']))
    	{
    		$obj = db_find('app_ext_processes',filter_var($_POST['id'],FILTER_SANITIZE_STRING));
    	}
    	else
    	{
    		$obj = db_show_columns('app_ext_processes');
    	}
    
    	$choices = array();
    	$choices['default'] = TEXT_DEFAULT;
    	$choices['menu_more_actions'] = TEXT_EXT_MENU_MORE_ACTIONS;
    	$choices['menu_with_selected'] = TEXT_EXT_MENU_WITH_SELECTED;
    	$choices['comments_section'] = TEXT_EXT_COMMENTS_SECTION;
    	$choices['run_after_insert'] = TEXT_EXT_RUN_PROCESS_AFTER_RECORD_INSERT;
    	
    	$buttons_query = db_query("select id, name from app_ext_processes_buttons_groups where entities_id='" . $entities_id . "' order by sort_order, name");
    	while($buttons = db_fetch_array($buttons_query))
    	{
    		$choices['buttons_groups_' . $buttons['id']] = $buttons['name'];
    	}
    
    	$html = '
         <div class="form-group">
          	<label class="col-md-3 control-label" for="access_to_assgined">' . TEXT_EXT_PROCESS_BUTTON_POSITION . '</label>
            <div class="col-md-9">
          	   ' .  select_tag('button_position[]', $choices,$obj['button_position'],array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple')) . '
            </div>
          </div>
        ';
    
    
    
    	echo $html;
    
    	exit();
    	break;
    
    case 'get_entities_users_fields':
    
    	$entities_id = filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING);    	
    
    	$obj = array();
    
    	if(isset($_POST['id']))
    	{
    		$obj = db_find('app_ext_processes',filter_var($_POST['id'],FILTER_SANITIZE_STRING));
    	}
    	else
    	{
    		$obj = db_show_columns('app_ext_processes');
    	}
    
    	$html = '';
        
    	$choices = array();    	
    	$fields_query = db_query("select f.*, if(f.type in (" . fields_types::get_reserverd_data_types_list() . "),-1,t.sort_order) as tab_sort_order from app_fields f,  app_forms_tabs t where f.forms_tabs_id=t.id  and f.type in ('fieldtype_users_approve','fieldtype_users','fieldtype_users_ajax','fieldtype_grouped_users','fieldtype_created_by') and f.entities_id='" . db_input($entities_id) . "' order by tab_sort_order, t.name, f.sort_order, f.name");
    	while($fields = db_fetch_array($fields_query))
    	{    
    		if(filter_var($fields['type'],FILTER_SANITIZE_STRING)=='fieldtype_created_by')
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
          	<label class="col-md-3 control-label" for="access_to_assgined">' . TEXT_EXT_ACCESS_TO_ASSIGNED_USERS . '</label>
            <div class="col-md-9">
          	   ' .  select_tag('access_to_assigned[]',$choices,$obj['access_to_assigned'],array('class'=>'form-control input-large chosen-select','multiple'=>'multiple')) . '               
            </div>
          </div>
        ';
    

    
    	echo $html;
    
    	exit();
    	break; 
    case 'copy':
    	
    	$process_id = _get::int('id');
    	
    	//copy process
    	$process_info_query = db_query("select * from app_ext_processes where id='" . $process_id . "'");
    	if($process_info = db_fetch_array($process_info_query))
    	{
    		$sql_data = $process_info;    		
    		unset($sql_data['id']);
    		$sql_data['name'] = $sql_data['name'] . ' (' . TEXT_EXT_NAME_COPY . ')';
    		$sql_data['is_active'] = 0;
    		    		
    		db_perform('app_ext_processes',$sql_data);
    		$new_process_id = db_insert_id();
    		
    		//copy actions
    		$actions_query = db_query("select * from app_ext_processes_actions where process_id=" . $process_id);
    		while($actions = db_fetch_array($actions_query))
    		{
    			$sql_data = $actions;
    			unset($sql_data['id']);
    			$sql_data['process_id'] = $new_process_id;
    			
    			db_perform('app_ext_processes_actions',$sql_data);
    			$new_action_id = db_insert_id();
    			
    			//copy fields
    			$fields_query = db_query("select * from app_ext_processes_actions_fields where actions_id='" . filter_var($actions['id'],FILTER_SANITIZE_STRING) . "'");
    			while($fields = db_fetch_array($fields_query))
    			{
    				$sql_data = $fields;
    				unset($sql_data['id']);
    				$sql_data['actions_id'] = $new_action_id;
    				
    				db_perform('app_ext_processes_actions_fields',$sql_data);
    			}
    			
    			//copy actions filters
    			$reports_info_query = db_query("select * from app_reports where reports_type='process_action" . filter_var($actions['id'],FILTER_SANITIZE_STRING) . "'");
    			if($reports_info = db_fetch_array($reports_info_query))
    			{
    				$sql_data = $reports_info;
    				unset($sql_data['id']);
    				$sql_data['reports_type'] = 'process_action' . $new_action_id;
    				 
    				db_perform('app_reports',$sql_data);
    				$new_reports_id = db_insert_id();
    				 
    				$reports_filters_query = db_query("select * from app_reports_filters where reports_id='" . filter_var($reports_info['id'],FILTER_SANITIZE_STRING) . "'");
    				while($reports_filters = db_fetch_array($reports_filters_query))
    				{
    					$sql_data = $reports_filters;
    					unset($sql_data['id']);
    					$sql_data['reports_id'] = $new_reports_id;
    						
    					db_perform('app_reports_filters',$sql_data);
    				}
    			}
    			
    		}
    		
    		//copy process filters
    		$reports_info_query = db_query("select * from app_reports where reports_type='process" . $process_id . "'");
    		if($reports_info = db_fetch_array($reports_info_query))
    		{
    			$sql_data = $reports_info;
    			unset($sql_data['id']);
    			$sql_data['reports_type'] = 'process' . $new_process_id;
    			
    			db_perform('app_reports',$sql_data);
    			$new_reports_id = db_insert_id();
    			
    			$reports_filters_query = db_query("select * from app_reports_filters where reports_id='" . filter_var($reports_info['id'],FILTER_SANITIZE_STRING) . "'");
    			while($reports_filters = db_fetch_array($reports_filters_query))
    			{
    				$sql_data = $reports_filters;
    				unset($sql_data['id']);
    				$sql_data['reports_id'] = $new_reports_id;
    				 
    				db_perform('app_reports_filters',$sql_data);
    			}    				    			    		
    		}
    		
    	}
    	
    	$alerts->add(TEXT_EXT_PROCESS_COPIED,'success');
    	redirect_to('ext/processes/processes');    	
    	
    	break;
}