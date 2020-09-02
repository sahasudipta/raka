<?php 

switch($app_module_action)
{  
  case 'save':
  
      //checking access
      if(isset($_GET['id']))
      {        
      	$access_rules = new access_rules(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($_GET['id'],FILTER_SANITIZE_STRING));
      	
      	if(!users::has_access('update',$access_rules->get_access_schema()))
      	{	
        	redirect_to('dashboard/access_forbidden');
      	}
      }      
      elseif(!isset($_GET['id']) and (!users::has_access('create') or !access_rules::has_add_buttons_access(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($parent_entity_item_id,FILTER_SANITIZE_STRING))))
      {
        redirect_to('dashboard/access_forbidden');
      }
      
      //check POST data for user form
      if($current_entity_id==1)
      {      
        require(component_path('items/validate_users_form'));
      }
            
      $fields_values_cache = items::get_fields_values_cache(filter_var_array($_POST['fields']),$current_path_array,filter_var($current_entity_id,FILTER_SANITIZE_STRING));      
      
      $fields_access_schema = users::get_fields_access_schema(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($app_user['group_id'],FILTER_SANITIZE_STRING));
            
      $app_send_to = array();             
      $app_send_to_new_assigned = array();
      $app_changed_fields = array();
                                  
      $is_new_item = true;
      $item_info = array();
      
      //get item info for exist item
      if(isset($_GET['id']))
      {      
        $is_new_item = false;          
        $item_info_query = db_query("select * from app_entity_" . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . " where id='" . db_input(filter_var(_get::int('id'),FILTER_SANITIZE_STRING)) . "'");
        $item_info = db_fetch_array($item_info_query);  
        
        $access_rules = new access_rules(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var_array($item_info));
        $fields_access_schema += $access_rules->get_fields_view_only_access();
        
        //add creators to send to
        if(fieldtype_created_by::is_notification_enabled(filter_var($current_entity_id,FILTER_SANITIZE_STRING)))
        {
        	$app_send_to[] = filter_var($item_info['created_by'],FILTER_SANITIZE_STRING);
        }
      }
      
      //prepare item data      
      $sql_data = array();
      
      $choices_values = new choices_values(filter_var($current_entity_id,FILTER_SANITIZE_STRING));
                                    
      $fields_query = db_query("select f.* from app_fields f where f.type not in (" . fields_types::get_reserverd_types_list(). ",'fieldtype_related_records','fieldtype_user_last_login_date','fieldtype_google_map','fieldtype_google_map_directions') and  f.entities_id='" . db_input(filter_var($current_entity_id,FILTER_SANITIZE_STRING)) . "' order by f.sort_order, f.name");
      while($field = db_fetch_array($fields_query))
      {
        $default_field_value = '';
        //check field access and skip fields without access
        if(isset($fields_access_schema[filter_var($field['id'],FILTER_SANITIZE_STRING)]))
        {
	        //for new item check if there is template field set and use it
	        if(!isset($_GET['id']) and isset($_POST['template_fields'][filter_var($field['id'],FILTER_SANITIZE_STRING)]))
	        {
	          $default_field_value = filter_var($_POST['template_fields'][filter_var($field['id'],FILTER_SANITIZE_STRING)],FILTER_SANITIZE_STRING);
	        }
	        //for new item check if there is default value and assign it if it's exist          
	        elseif(!isset($_GET['id']) and in_array(filter_var($field['type'],FILTER_SANITIZE_STRING),fields_types::get_types_wich_choices()))
	        {      
	        	$cfg = new fields_types_cfg(filter_var($field['configuration'],FILTER_SANITIZE_STRING));
	        	
	        	if($cfg->get('use_global_list')>0)
	        	{
	        		$check_query = db_query("select id from app_global_lists_choices where lists_id = '" . db_input(filter_var($cfg->get('use_global_list'),FILTER_SANITIZE_STRING)). "' and is_default=1");
	        	}
	        	else
	        	{
	        		$check_query = db_query("select id from app_fields_choices where fields_id='" . db_input(filter_var($field['id'],FILTER_SANITIZE_STRING)) . "' and is_default=1");
	        	}
	        		          
	          if($check = db_fetch_array($check_query))
	          {
	            $default_field_value = filter_var($check['id'],FILTER_SANITIZE_STRING);                            
	          }
	          else
	          {
	          	continue;
	          }
	        } 
	        elseif(!isset($_GET['id']) and $field['type']=='fieldtype_user_accessgroups')
	        {
	        	$default_field_value = access_groups::get_default_group_id();        	
	        }
	        elseif(!isset($_GET['id']) and $field['type']=='fieldtype_users_approve')
	        {
	        	$cfg = new fields_types_cfg($field['configuration']);
	        	
	        	$default_field_value = (is_array($cfg->get('users_by_default')) ? implode(',',$cfg->get('users_by_default')) : '');
	        }
	        elseif(!isset($_GET['id']) and $field['type']=='fieldtype_user_status')
	        {
	        	$default_field_value = 1;
	        }
	        else
	        {
	        	continue;
	        }
	      }  
        
        
        //submited field value
        $value = (isset($_POST['fields'][filter_var($field['id'],FILTER_SANITIZE_STRING)]) ? filter_var($_POST['fields'][filter_var($field['id'],FILTER_SANITIZE_STRING)],FILTER_SANITIZE_STRING) : $default_field_value);
         
        //current field value 
        $current_field_value = (isset($item_info['field_' . filter_var($field['id'],FILTER_SANITIZE_STRING)]) ? $item_info['field_' . filter_var($field['id'],FILTER_SANITIZE_STRING)] : ''); 
        
        //prepare process options        
        $process_options = array('class'          => filter_var($field['type'],FILTER_SANITIZE_STRING),
                                 'value'          => $value,
                                 'fields_cache'   => $fields_values_cache, 
                                 'field'          => filter_var_array($field),
                                 'is_new_item'    => $is_new_item,
                                 'current_field_value' => $current_field_value,
                                 'item' => (isset($_GET['id']) ? filter_var_array($item_info) : []),	
                                 );
        
        $sql_data['field_' . filter_var($field['id'],FILTER_SANITIZE_STRING)] = fields_types::process($process_options);
        
        //prepare choices values for fields with multiple values
        $choices_values->prepare($process_options);        
      } 
      
      //print_rr($sql_data);
      //exit();
                        
      if(isset($_GET['id']))
      {            	      	
      	//update item
      	$sql_data['date_updated'] = time();
        db_perform('app_entity_' . filter_var($current_entity_id,FILTER_SANITIZE_STRING),$sql_data,'update',"id='" . db_input(filter_var(_get::int('id'),FILTER_SANITIZE_STRING)) . "'");
        $item_id = filter_var((int)$_GET['id'],FILTER_SANITIZE_STRING);   
        
        if($current_entity_id==1)
        {
            public_registration::send_user_activation_email_msg(filter_var($item_id,FILTER_SANITIZE_STRING), filter_var_array($item_info));
        }
        
        //reset signatures
        fieldtype_digital_signature::reset_signature_if_data_changed(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING), filter_var_array($item_info));
        
      }
      else
      { 
        //genreation user password and sending notification for new user
        if($current_entity_id==1)
        {      
          require(component_path('items/crete_new_user'));
        }
        
        $sql_data['date_added'] = time();              
        $sql_data['created_by'] = filter_var($app_logged_users_id,FILTER_SANITIZE_STRING);
        $sql_data['parent_item_id'] = filter_var($parent_entity_item_id,FILTER_SANITIZE_STRING);
        db_perform('app_entity_' . filter_var($current_entity_id,FILTER_SANITIZE_STRING),$sql_data);
        $item_id = db_insert_id();          
                        
      }
            
      //insert choices values for fields with multiple values
      $choices_values->process(filter_var($item_id,FILTER_SANITIZE_STRING));
      
      //prepare user roles
      fieldtype_user_roles::set_user_roles_to_items(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
                       
      //autoupdate all field types
      fields_types::update_items_fields(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
      
      if(isset($_GET['id']))
      {
      	if(is_ext_installed())
      	{
      		//check public form notification
      		//using $item_info as item with previous values
      		public_forms::send_client_notification(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var_array($item_info));
      	
      		//sending sms
      		$modules = new modules('sms');
      		$sms = new sms(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
      		$sms->send_to = $app_send_to;
      		$sms->send_edit_msg(filter_var_array($item_info));
      		 
      		//subscribe
      		$modules = new modules('mailing');
      		$mailing = new mailing(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
      		$mailing->update(filter_var_array($item_info));
      		 
      		//email rules
      		$email_rules = new email_rules(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
      		$email_rules->send_edit_msg(filter_var_array($item_info));
      	}
      }
      else
      {
      	if(is_ext_installed())
      	{
      		//sending sms
      		$modules = new modules('sms');
      		$sms = new sms(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
      		$sms->send_to = $app_send_to;
      		$sms->send_insert_msg();
      		 
      		//subscribe
      		$modules = new modules('mailing');
      		$mailing = new mailing(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
      		$mailing->subscribe();
      		 
      		//email rules
      		$email_rules = new email_rules(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
      		$email_rules->send_insert_msg();
      		
      		//run actions after item insert
      		$processes = new processes(filter_var($current_entity_id,FILTER_SANITIZE_STRING));
      		$processes->run_after_insert(filter_var($item_id,FILTER_SANITIZE_STRING));
      	}
      }
                  
      //log changeds
      if(class_exists('track_changes'))
      {
      	$log = new track_changes(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
      	$log->log_prepare(isset($_GET['id']),filter_var_array($item_info));
      }
      
      //atuocreate comments if fields changed
      if(count($app_changed_fields))
      {
      	comments::add_comment_notify_when_fields_changed(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING),$app_changed_fields);
      }
                  
      /**
       * Start email notification code
       **/
       
      //include sender in notification              
      if(CFG_EMAIL_COPY_SENDER==1)
      {
        $app_send_to[] = $app_user['id'];
      }
      
      //Send notification if there are assigned users and items is new or there is changed fields or new assigned users
      if((count($app_send_to)>0 and !isset($_GET['id'])) or 
         (count($app_send_to)>0 and count($app_changed_fields)>0) or 
          count($app_send_to_new_assigned)>0)
      {                                   
        $breadcrumb = items::get_breadcrumb_by_item_id(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
        $item_name = $breadcrumb['text'];
        
        $entity_cfg = new entities_cfg(filter_var($current_entity_id,FILTER_SANITIZE_STRING));
        
        //prepare subject for update itme      
        if(count($app_changed_fields)>0)
        {
          $subject = (strlen($entity_cfg->get('email_subject_updated_item'))>0 ? $entity_cfg->get('email_subject_updated_item') . ' ' . $item_name : TEXT_DEFAULT_EMAIL_SUBJECT_UPDATED_ITEM . ' ' . $item_name);
          
          //add changed field values in subject
          $extra_subject = array();
          foreach(filter_var_array($app_changed_fields) as $v)
          {
            $extra_subject[] = $v['name'] . ': ' . $v['value']; 
          }
          
          $subject .= ' [' . implode(' | ', $extra_subject) . ']';
          
          $users_notifications_type = 'updated_item';
        }
        else
        {       
          //subject for new item    
          $subject = (strlen($entity_cfg->get('email_subject_new_item'))>0 ? $entity_cfg->get('email_subject_new_item') . ' ' . $item_name : TEXT_DEFAULT_EMAIL_SUBJECT_NEW_ITEM . ' ' . $item_name);
          
          $users_notifications_type = 'new_item';
        }
        
        //default email heading
        $heading = users::use_email_pattern_style('<div><a href="' . url_for('items/info','path=' . filter_var($_POST['path'],FILTER_SANITIZE_STRING) . '-' . filter_var($item_id,FILTER_SANITIZE_STRING),true) . '"><h3>' . $subject . '</h3></a></div>','email_heading_content');
        
        //if only users fields changed then send notification to new assigned users
        if(count($app_changed_fields)==0 and count($app_send_to_new_assigned)>0)
        {
          $app_send_to = $app_send_to_new_assigned;
        }
        
        //start sending email                  
        foreach(array_unique($app_send_to) as $send_to)
        {             	        	        	
          //prepare body  
        	if($entity_cfg->get('item_page_details_columns','2')==1)
        	{
        		$body = users::use_email_pattern('single_column',array('email_single_column'=>items::render_info_box(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($item_id,FILTER_SANITIZE_STRING),$send_to, false)));
        	}
        	else 
        	{
          	$body = users::use_email_pattern('single',array('email_body_content'=>items::render_content_box(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($item_id,FILTER_SANITIZE_STRING),$send_to),'email_sidebar_content'=>items::render_info_box(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($item_id,FILTER_SANITIZE_STRING),$send_to)));
        	}
               
          //echo $subject . $body;
          //exit();
          
          //change subject for new assigned user
          if(in_array($send_to,$app_send_to_new_assigned))
          {            
            $new_subject = (strlen($entity_cfg->get('email_subject_new_item'))>0 ? $entity_cfg->get('email_subject_new_item') . ' ' . $item_name : TEXT_DEFAULT_EMAIL_SUBJECT_NEW_ITEM . ' ' . $item_name);
            $new_heading = users::use_email_pattern_style('<div><a href="' . url_for('items/info','path=' . filter_var($_POST['path'],FILTER_SANITIZE_STRING) . '-' . filter_var($item_id,FILTER_SANITIZE_STRING),true) . '"><h3>' . $new_subject . '</h3></a></div>','email_heading_content');
            
            if(users_cfg::get_value_by_users_id($send_to, 'disable_notification')!=1 and $entity_cfg->get('disable_notification')!=1)
            {
            	users::send_to(array($send_to),$new_subject,$new_heading . $body);
            }
            
            //add users notification
            if($entity_cfg->get('disable_internal_notification')!=1)
            	users_notifications::add($new_subject, 'new_item', $send_to, filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
          }
          else          
          {
          	if(users_cfg::get_value_by_users_id($send_to, 'disable_notification')!=1 and $entity_cfg->get('disable_notification')!=1)
            {
            	users::send_to(array($send_to),$subject,$heading . $body);
            }
            
            //add users notification
            if($entity_cfg->get('disable_internal_notification')!=1)
            	users_notifications::add($subject, $users_notifications_type, $send_to, filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var( $item_id,FILTER_SANITIZE_STRING));
          }                                       
        } 
                       
      }
      /**
       * End email notification code
       **/   
       
       
      //set off redirect if add items from calendar reprot
      if(strstr($app_redirect_to,'calendarreport') or strstr($app_redirect_to,'pivot_calendars'))
      {
        exit();
      } 
      
      //set off redirect if add items from gantt reprot
      if(strstr($app_redirect_to,'ganttreport'))
      {
      	require(component_path('items/items_form_gantt_submit_prepare'));      	
      	exit();
      }
                      
      //redirect to related item
      if(isset($_POST['related']))
      {   
        $related_array = explode('-',filter_var($_POST['related'],FILTER_SANITIZE_STRING));
        $related_entities_id = $related_array[0];
        $related_items_id = $related_array[1]; 
        
        $table_info = related_records::get_related_items_table_name(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($related_entities_id,FILTER_SANITIZE_STRING));
        
        $sql_data = array('entity_' . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . '_items_id' => filter_var($item_id,FILTER_SANITIZE_STRING),
                                                                'entity_' . filter_var($related_entities_id,FILTER_SANITIZE_STRING) . $table_info['sufix'] . '_items_id' => filter_var($related_items_id,FILTER_SANITIZE_STRING));
        
        db_perform($table_info['table_name'],$sql_data);
        
        //autocreate comments
        related_records::autocreate_comments(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($item_id,FILTER_SANITIZE_STRING),filter_var($related_entities_id,FILTER_SANITIZE_STRING),filter_var($related_items_id,FILTER_SANITIZE_STRING));
        
        
        $path_info = items::get_path_info(filter_var($related_entities_id,FILTER_SANITIZE_STRING),filter_var($related_items_id,FILTER_SANITIZE_STRING));
        
        //atuoset fieldtype autostatus
        fieldtype_autostatus::set(filter_var($related_entities_id,FILTER_SANITIZE_STRING), filter_var($related_items_id,FILTER_SANITIZE_STRING));
                      
        redirect_to('items/info','path=' . filter_var($path_info['full_path'],FILTER_SANITIZE_STRING)); 
      }
      
      
      //relate mail to item
      if(isset($_POST['mail_groups_id']))
      {      	
      	require(component_path('ext/mail/relate_mail_to_item'));      	
      }
                         
      //redirects after adding new item                  
      if(!isset($_GET['id']) and ($app_redirect_to=='' or strstr($app_redirect_to,'report_')))
      {
      	$entity_cfg = new entities_cfg(filter_var($current_entity_id,FILTER_SANITIZE_STRING));
      	
      	switch($entity_cfg->get('redirect_after_adding','subentity'))
      	{      		
      		case 'subentity':
      			if($app_user['group_id']==0)
      			{
      				$entity_query = db_query("select * from app_entities where parent_id='" . db_input(filter_var($current_entity_id,FILTER_SANITIZE_STRING)) . "' order by sort_order, name limit 1");
      			}
      			else
      			{
      				$entity_query = db_query("select e.* from app_entities e, app_entities_access ea where e.parent_id='" . db_input(filter_var($current_entity_id,FILTER_SANITIZE_STRING)) . "' and e.id=ea.entities_id and length(ea.access_schema)>0 and ea.access_groups_id='" . db_input(filter_var($app_user['group_id'],FILTER_SANITIZE_STRING)) . "' order by e.sort_order, e.name limit 1");
      			}
      			
      			if($entity = db_fetch_array($entity_query))
      			{
      				redirect_to('items/items','path=' . filter_var($_POST['path'],FILTER_SANITIZE_STRING) . '-' . filter_var($item_id ,FILTER_SANITIZE_STRING). '/' . filter_var($entity['id'],FILTER_SANITIZE_STRING));
      			}
      			break;
      		case 'info':
      			redirect_to('items/info','path=' . filter_var($_POST['path'],FILTER_SANITIZE_STRING) . '-' . filter_var($item_id,FILTER_SANITIZE_STRING));
      			break;
      	}        
      }
      
      $gotopage = '';
      if(isset($_POST['gotopage']))
      {
      	$gotopage = '&gotopage[' . key(filter_var_array($_POST['gotopage'])). ']=' . current(filter_var_array($_POST['gotopage']));
      }
      
      //related records redirect
      related_records::handle_app_redirect();
      
      //other redirects      
      switch($app_redirect_to)
      {
      	case 'parent_item_info_page':      	      		      
      		redirect_to('items/info','path=' . app_path_get_parent_path($app_path));
      		break;
        case 'dashboard':
            redirect_to('dashboard/',substr($gotopage,1));
          break;
        case 'items_info':
            redirect_to('items/info','path=' . filter_var($_POST['path'],FILTER_SANITIZE_STRING));
          break;
        case 'parent_modal':
        		echo $item_id;
        		exit();
        	break;
        default:
            	if(strstr($app_redirect_to,'kanban'))
            	{
            		redirect_to('ext/kanban/view','id='  . str_replace('kanban','',$app_redirect_to). '&path=' . $app_path);
            	}
        	    elseif(strstr($app_redirect_to,'item_info_page'))
        	    {
        	        redirect_to('items/info','path=' . str_replace('item_info_page','',$app_redirect_to));
        	    }	        	  	
                elseif(strstr($app_redirect_to,'report_'))
                {
                    redirect_to('reports/view','reports_id=' . str_replace('report_','',$app_redirect_to) . $gotopage);
                }                                      
                else
                {           
                    $path_info = items::get_path_info($current_entity_id,$item_id);
                    redirect_to('items/items','path=' . $path_info['path_to_entity'] . $gotopage);
                }  
          break;
      }
      
      
    break;  
  case 'delete':
  	
  		$item_id = _get::int('id');
  		
  		$access_rules = new access_rules(filter_var($current_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
  		
      if(!users::has_access('delete',$access_rules->get_access_schema()))
      {
        redirect_to('dashboard/access_forbidden');
      }
      
      $item_info_query = db_query("select created_by from app_entity_" . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . " where id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");
      if(!$item_info = db_fetch_array($item_info_query))
      {
          redirect_to('dashboard/page_not_found');
      }
      
      //check current user delete
      if($current_entity_id==1 and $item_id==$app_user['id'])
      {
          $alerts->add(TEXT_YOU_CANT_DELETE_YOURSELF,'error');          
          redirect_to('items/info','path=' . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . '-' . filter_var($item_id,FILTER_SANITIZE_STRING));
      }
      
      if(users::has_access('delete_creator',$access_rules->get_access_schema()) and $item_info['created_by']!=filter_var($app_user['id'],FILTER_SANITIZE_STRING))
      {
      	redirect_to('dashboard/access_forbidden');
      }
      
      $path_info = items::get_path_info(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($item_id,FILTER_SANITIZE_STRING));
            
      
      $items_to_delete = items::get_items_to_delete(filter_var($current_entity_id,FILTER_SANITIZE_STRING),[filter_var($current_entity_id,FILTER_SANITIZE_STRING)=>[0=>filter_var($item_id,FILTER_SANITIZE_STRING)]]);
                  
      foreach($items_to_delete as $entity_id=>$items_list)
      {
      	foreach($items_list as $item_id)
      	{
      		items::delete(filter_var($entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
      	}
      }                        
      
      plugins::handle_action('delete_item');
      
      $gotopage = '';
      if(isset($_POST['gotopage']))
      {
      	$gotopage = '&gotopage[' . key(filter_var_array($_POST['gotopage'])). ']=' . current(filter_var_array($_POST['gotopage']));
      }
      
      //related records redirect
      related_records::handle_app_redirect();
      
      switch($app_redirect_to)
      {
      	case 'parent_item_info_page':
      		redirect_to('items/info','path=' . app_path_get_parent_path($app_path));
      		break;
        case 'dashboard':
            redirect_to('dashboard/',substr($gotopage,1));
          break;
        default:
                	
            if(strstr($app_redirect_to,'kanban'))
	        	{
	        		redirect_to('ext/kanban/view','id='  . str_replace('kanban','',$app_redirect_to). '&path=' . $app_path);
	        	}
        	  elseif(strstr($app_redirect_to,'item_info_page'))
        	  {
        	  	redirect_to('items/info','path=' . str_replace('item_info_page','',$app_redirect_to));
        	  }	        	  	
            elseif(strstr($app_redirect_to,'report_'))
            {
              redirect_to('reports/view','reports_id=' . str_replace('report_','',$app_redirect_to) . $gotopage);
            }                                      
            else
            {              
              redirect_to('items/items','path=' . $path_info['path_to_entity'] . $gotopage);
            }  
            
          break;
      }
      
      
    break;
  case 'attachments_upload':    
        $verifyToken = md5(filter_var($app_user['id'],FILTER_SANITIZE_STRING) . filter_var($_POST['timestamp'],FILTER_SANITIZE_STRING));
          
        if(strlen($_FILES['Filedata']['tmp_name']) and $_POST['token'] == $verifyToken)
        {
          $file = attachments::prepare_filename(filter_var($_FILES['Filedata']['name'],FILTER_SANITIZE_STRING));
                                        
          if(move_uploaded_file($_FILES['Filedata']['tmp_name'], DIR_WS_ATTACHMENTS  . $file['folder']  .'/'. $file['file']))
          {  
          	//autoresize images if enabled 
          	attachments::resize(DIR_WS_ATTACHMENTS  . $file['folder']  .'/'. $file['file']);
          	
            //add attachments to tmp table
            $sql_data = array('form_token'=>$verifyToken,'filename'=>$file['name'],'date_added'=>date('Y-m-d'),'container'=>filter_var($_GET['field_id'],FILTER_SANITIZE_STRING));
            db_perform('app_attachments',$sql_data);  
            
            //add file to queue
            if(class_exists('file_storage'))
            {
            	$file_storage = new file_storage();
            	$file_storage->add_to_queue(filter_var($_GET['field_id'],FILTER_SANITIZE_STRING), $file['name']);
            }
            
          }
        }
      exit();
    break;
    
  case 'attachments_preview': 
        $field_id = filter_var($_GET['field_id'],FILTER_SANITIZE_STRING);  
        
        $attachments_list = $uploadify_attachments[$field_id];
        
        //get new attachments
        $attachments_query = db_query("select filename from app_attachments where form_token='" . db_input(filter_var($_GET['token'],FILTER_SANITIZE_STRING)). "' and container='" . db_input(filter_var($_GET['field_id'],FILTER_SANITIZE_STRING)) . "'");
        while($attachments = db_fetch_array($attachments_query))
        {
          $attachments_list[] = $attachments['filename']; 
          
          if(!in_array($attachments['filename'],$uploadify_attachments_queue[$field_id])) $uploadify_attachments_queue[$field_id][] = $attachments['filename'];
        }
        
        $delete_file_url = url_for('items/items','action=attachments_delete_in_queue&path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING));
                             
        echo attachments::render_preview(filter_var($field_id,FILTER_SANITIZE_STRING), $attachments_list,$delete_file_url);
        
      exit();
    break;
 case 'attachments_delete_in_queue':
    	//chck form token
    	app_check_form_token();
    
    	attachments::delete_in_queue(filter_var($_POST['field_id'],FILTER_SANITIZE_STRING), filter_var($_POST['filename'],FILTER_SANITIZE_STRING));
    
    	exit();
    	break;
    
    
  case 'check_unique':
           
      echo items::check_unique(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var(_post::int('fields_id'),FILTER_SANITIZE_STRING),filter_var($_POST['fields_value'],FILTER_SANITIZE_STRING),(isset($_GET["id"]) ? filter_var($_GET["id"],FILTER_SANITIZE_STRING):false));
      
      exit();
    break;
    
  case 'set_listing_type':
  	$reports_info_query = db_query("select id from app_reports where id='" .db_input(filter_var( _get::int('reports_id'),FILTER_SANITIZE_STRING)). "'");
  	if($reports_info = db_fetch_array($reports_info_query))
  	{
  		db_query("update app_reports set listing_type='" . db_input(filter_var($_GET['type'],FILTER_SANITIZE_STRING)) . "' where id='" . db_input(filter_var($reports_info['id'],FILTER_SANITIZE_STRING)) . "'");
  	}
  	
  	redirect_to('items/items','path=' . $app_path);
  	break;
      
   
}

$entity_info = db_find('app_entities',filter_var($current_entity_id,FILTER_SANITIZE_STRING));
$entity_cfg = new entities_cfg(filter_var($current_entity_id,FILTER_SANITIZE_STRING));

//check if parent exist in path
if($entity_info['parent_id']>0 and $parent_entity_item_id==0)
{
	redirect_to('dashboard/access_forbidden');
}

$entity_listing_heading = (strlen($entity_cfg->get('listing_heading'))>0 ? $entity_cfg->get('listing_heading') : filter_var($entity_info['name'],FILTER_SANITIZE_STRING));

$app_title = app_set_title($entity_listing_heading);


if(!filters_panels::has_any(filter_var($current_entity_id,FILTER_SANITIZE_STRING), $entity_cfg))
{
	//use default filters if there is no any filters panes stup
	$default_reports_query = db_query("select * from app_reports where entities_id='" . db_input(filter_var($current_entity_id,FILTER_SANITIZE_STRING)). "' and reports_type='default'");
	if(db_num_rows($default_reports_query))
	{		
		$default_reports_info = db_fetch_array($default_reports_query);
		$force_filters_reports_id = filter_var($default_reports_info['id'],FILTER_SANITIZE_STRING);
	}
}

//create default entity report for logged user
//also reports will be split by paretn item
$reports_info = reports::create_default_entity_report(filter_var($current_entity_id,FILTER_SANITIZE_STRING), 'entity', $current_path_array);


 

