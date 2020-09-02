<?php

//check report and access
$reports_info_query = db_query("select * from app_reports where id='" . db_input(filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING)). "'");
if($reports_info = db_fetch_array($reports_info_query))
{  
  $access_schema = users::get_entities_access_schema($reports_info['entities_id'],$app_user['group_id']);
          
  if(!users::has_access('update',$access_schema))
  {      
    redirect_to('dashboard/access_forbidden'); 
  }
}
else
{
  redirect_to('dashboard/page_not_found');
}

switch($app_module_action)
{ 
  case 'get_field_values':
      $fields_id = filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING);
      $field_info = db_find('app_fields',$fields_id);
            
      $field_cfg = new fields_types_cfg($field_info['configuration']);
      
      switch(filter_var($field_info['type'],FILTER_SANITIZE_STRING))
      {
      	case 'fieldtype_entity':

      		//skip this field in reports (where there is no PATH)
      		if(strlen($app_path)==0) return '';      		
      		      		      		      
      		$entity_info = db_find('app_entities',$field_cfg->get('entity_id'));
      		$field_entity_info = db_find('app_entities',$field_info['entities_id']);
      		
      		//get paretn id if exist
      		if($field_entity_info['parent_id']>0)
      		{
      			$path_array = explode('/',$app_path);
      			$v = explode('-',$path_array[count($path_array)-2]);
      			$parent_entity_item_id = $v[1];      		      			
      		}
      		else
      		{
      			$parent_entity_item_id = 0;
      		}	
      		
      		//print_r($field_entity_info);
      		//echo $app_path . ' = ' . $parent_entity_item_id;      		      	      	      		
      		
      		$choices = array();
      		
      		//add empty value if dispalys as dropdown and field is not requireed
      		if($field_cfg->get('display_as')=='dropdown')
      		{
      			$choices[''] = (strlen($field_cfg->get('default_text')) ? $field_cfg->get('default_text') : TEXT_NONE);
      		}
      		
      		$listing_sql_query = '';
      		$listing_sql_query_join = '';
      		
      		//if parent entity is the same then select records from paretn items only
      		if($parent_entity_item_id>0 and $entity_info['parent_id']>0 and $entity_info['parent_id']==$field_entity_info['parent_id'])
      		{
      			$listing_sql_query .= " and e.parent_item_id='" . db_input(filter_var($parent_entity_item_id,FILTER_SANITIZE_STRING)) . "'";
      		}
      		//if paretn is different then check level branch
      		elseif($parent_entity_item_id>0 and $entity_info['parent_id']>0 and $entity_info['parent_id']!=$field_entity_info['parent_id'])
      		{
      			$listing_sql_query = fieldtype_entity::prepare_parents_sql(filter_var($parent_entity_item_id,FILTER_SANITIZE_STRING),filter_var($entity_info['parent_id'],FILTER_SANITIZE_STRING),filter_var($field_entity_info['parent_id'],FILTER_SANITIZE_STRING));
      		}
      		
      		$default_reports_query = db_query("select * from app_reports where entities_id='" . db_input(filter_var($field_cfg->get('entity_id'),FILTER_SANITIZE_STRING)). "' and reports_type='entityfield" . filter_var($field_info['id'],FILTER_SANITIZE_STRING) . "'");
      		if($default_reports = db_fetch_array($default_reports_query))
      		{
      			$listing_sql_query = reports::add_filters_query(filter_var($default_reports['id'],FILTER_SANITIZE_STRING),$listing_sql_query);
      		
      			$info = reports::add_order_query($default_reports['listing_order_fields'],$field_cfg->get('entity_id'));
      			$listing_sql_query .= filter_var($info['listing_sql_query'],FILTER_SANITIZE_STRING);
      			$listing_sql_query_join .= filter_var($info['listing_sql_query_join'],FILTER_SANITIZE_STRING);
      		
      		}
      		else
      		{
      			$listing_sql_query .= " order by e.id";
      		}
      		
      		$field_heading_id = 0;
      		$fields_query = db_query("select f.* from app_fields f where f.is_heading=1 and  f.entities_id='" . db_input(filter_var($field_cfg->get('entity_id'),FILTER_SANITIZE_STRING)) . "'");
      		if($fields = db_fetch_array($fields_query))
      		{
      			$field_heading_id = $fields['id'];
      		}
      		
      		$listing_sql = "select  e.* from app_entity_" . filter_var($field_cfg->get('entity_id'),FILTER_SANITIZE_STRING) . " e "  . $listing_sql_query_join . " where e.id>0 " . $listing_sql_query;
      		$items_query = db_query($listing_sql);
      		while($item = db_fetch_array($items_query))
      		{
      			if($field_cfg->get('entity_id')==1)
      			{
      				$choices[filter_var($item['id'],FILTER_SANITIZE_STRING)] = $app_users_cache[filter_var($item['id'],FILTER_SANITIZE_STRING)]['name'];
      			}
      			elseif($field_heading_id>0)
      			{
      				//add paretn item name if exist
      				$parent_name = '';
      				if($entity_info['parent_id']>0 and $entity_info['parent_id']!=$field_entity_info['parent_id'])
      				{
      					$parent_name = items::get_heading_field($entity_info['parent_id'],filter_var($item['parent_item_id'],FILTER_SANITIZE_STRING)) . ' > ';
      				}
      		
      				$choices[filter_var($item['id'],FILTER_SANITIZE_STRING)] = $parent_name . items::get_heading_field_value($field_heading_id,$item);
      			}
      			else
      			{
      				$choices[filter_var($item['id'],FILTER_SANITIZE_STRING)] = filter_var($item['id'],FILTER_SANITIZE_STRING);
      			}
      		}
      		
      		//echo '<pre>';
      		//print_r($field_cfg);
      		      		
      		if($field_cfg->get('display_as')=='dropdown')
      		{
      			$attributes = array('class'=>'form-control chosen-select input-large');
      		
      			$html = select_tag('values[' . filter_var($fields_id ,FILTER_SANITIZE_STRING). ']',filter_var_array($choices),'',filter_var_array($attributes));
      		}      		
      		elseif($field_cfg->get('display_as')=='dropdown_muliple' or $field_cfg->get('display_as')=='checkboxes')
      		{
      			$attributes = array('class'=>'form-control chosen-select input-large',
      					'multiple'=>'multiple',
      					'data-placeholder'=>(strlen($field_cfg->get('default_text')) ? $field_cfg->get('default_text') : TEXT_SELECT_SOME_VALUES));
      		
      			$html = select_tag('values[' . filter_var($fields_id,FILTER_SANITIZE_STRING) . '][]',filter_var_array($choices),'',filter_var_array($attributes));
      		}      		
      		
      		break;
        case 'fieldtype_boolean':
            $choices = fieldtype_boolean::get_choices(filter_var_array($field_info));
            
            $html = select_tag('values[' . filter_var($fields_id,FILTER_SANITIZE_STRING) . ']',filter_var_array($choices),'',array('class'=>'form-control input-small'));
          break;
        case 'fieldtype_radioboxes':
        case 'fieldtype_dropdown':
        case 'fieldtype_image_map':
        case 'fieldtype_dropdown_multilevel':
        case 'fieldtype_stages':
            if($field_cfg->get('use_global_list')>0)
            {
              $choices = global_lists::get_choices($field_cfg->get('use_global_list'),false);
            }
            else
            {
              $choices = fields_choices::get_choices(filter_var($field_info['id'],FILTER_SANITIZE_STRING),false);
            }
                        	
            $html = select_tag('values[' . filter_var($fields_id,FILTER_SANITIZE_STRING) . ']',filter_var_array($choices),'',array('class'=>'form-control input-large chosen-select'));                
          break;
          
        case 'fieldtype_grouped_users':
            
	        	if($field_cfg->get('use_global_list')>0)
	        	{
	        		$choices = global_lists::get_choices($field_cfg->get('use_global_list'),false);
	        	}
	        	else
	        	{
	        		$choices = fields_choices::get_choices(filter_var($field_info['id'],FILTER_SANITIZE_STRING),false);
	        	}
            
            $attributes = array('class'=>'form-control chosen-select');
            
            if($field_cfg->get('display_as')=='checkboxes' or $field_cfg->get('display_as')=='dropdown_muliple')
            {
              $attributes['multiple'] = 'multiple';
              
              $html = select_tag('values[' . filter_var($fields_id,FILTER_SANITIZE_STRING) . '][]',filter_var_array($choices),'',filter_var_array($attributes));
            }
            else
            {
              $html = select_tag('values[' . filter_var($fields_id,FILTER_SANITIZE_STRING) . ']',filter_var_array($choices),'',filter_var_array($attributes));
            }
                        	
            
          break;                    
        case 'fieldtype_dropdown_multiple':
        case 'fieldtype_checkboxes':
        case 'fieldtype_tags':
            if($field_cfg->get('use_global_list')>0)
            {
              $choices = global_lists::get_choices($field_cfg->get('use_global_list'),false);
            }
            else
            {
              $choices = fields_choices::get_choices(filter_var($field_info['id'],FILTER_SANITIZE_STRING),false);
            }
                        	
            $html = select_tag('values[' . filter_var($fields_id ,FILTER_SANITIZE_STRING). '][]',filter_var_array($choices),'',array('class'=>'form-control chosen-select','multiple'=>'multiple'));
          break;          
          
        case 'fieldtype_input_numeric':
            $html =  input_tag('values[' . filter_var($fields_id,FILTER_SANITIZE_STRING) . ']','',array('class'=>'form-control input-medium numeric-fields')) . tooltip_text(TEXT_EXT_UPDATE_FIELD_INPUT_TIP);
          break;    
        case 'fieldtype_input_date':
        case 'fieldtype_input_datetime':
            $html = '
              <div class="input-group input-medium ">' . 
                input_tag('values[' . filter_var($fields_id,FILTER_SANITIZE_STRING) . ']','',array('class'=>'form-control date-fields')) .                 
                '<span class="input-group-btn"><button class="btn btn-default datepicker' . filter_var($fields_id ,FILTER_SANITIZE_STRING). '" type="button"><i class="fa fa-calendar"></i></button></span>
              </div>' . 
              tooltip_text(TEXT_EXT_UPDATE_FIELD_DATE_TIP);
             
             $html .='
              <script>
                 $(".datepicker' . filter_var($fields_id,FILTER_SANITIZE_STRING) . '").datepicker({
                    rtl: App.isRTL(),
                    autoclose: true,
                    weekStart: app_cfg_first_day_of_week,
                    format: "yyyy-mm-dd",
                }).on("changeDate", function(ev){                                                  					          	                    					
          					$("#values_' . filter_var($fields_id,FILTER_SANITIZE_STRING) . '").val($(".datepicker' . filter_var($fields_id,FILTER_SANITIZE_STRING) . '").datepicker("getFormattedDate"));          					
          					$(".datepicker' . filter_var($fields_id,FILTER_SANITIZE_STRING) . '").datepicker("hide");
          				});                                				              
              </script>
              ';
          break;
          
        case 'fieldtype_users_ajax':
        case 'fieldtype_users':
            
            
            $access_schema = users::get_entities_access_schema_by_groups(filter_var($field_info['entities_id'],FILTER_SANITIZE_STRING));
            
            $entity_access_schema = users::get_entities_access_schema(filter_var($field_info['entities_id'],FILTER_SANITIZE_STRING),filter_var($app_user['group_id'],FILTER_SANITIZE_STRING));
            
            /**
             *  if user have View Only Own access
             *  then we allows to see users from items which assigned to him only
             *  other users should be hidden
             */
            $users_query_assigned_only = '';
            if(users::has_access('view_assigned',$entity_access_schema) and $app_user['group_id']>0)
            {
              $users_query_assigned_only = " where u.id in (select cv.value from app_entity_" . filter_var($field_info['entities_id'],FILTER_SANITIZE_STRING) . "_values cv where cv.fields_id='" . db_input(filter_var($field_info['id'],FILTER_SANITIZE_STRING)) . "' and cv.items_id in (select cvi.items_id from app_entity_" . filter_var($field_info['entities_id'],FILTER_SANITIZE_STRING) . "_values cvi where cvi.fields_id='" . db_input(filter_var($field_info['id'],FILTER_SANITIZE_STRING)) . "' and cvi.value='" . db_input(filter_var($app_user['id'],FILTER_SANITIZE_STRING)) . "'))";        
            }
                  
            $choices = array();
            $users_query = db_query("select u.*,a.name as group_name from app_entity_1 u left join app_access_groups a on a.id=u.field_6 " . filter_var($users_query_assigned_only,FILTER_SANITIZE_STRING) . " order by u.field_8, u.field_7");
            while($users = db_fetch_array($users_query))
            {
              if(!isset($access_schema[filter_var($users['field_6'],FILTER_SANITIZE_STRING)]))
              {
                $access_schema[filter_var($users['field_6'],FILTER_SANITIZE_STRING)] = array();
              }
                
              if($users['field_6']==0 or in_array('view',$access_schema[$users['field_6']]) or in_array('view_assigned',$access_schema[$users['field_6']]))
              {               
                $group_name = (strlen(filter_var($users['group_name'],FILTER_SANITIZE_STRING))>0 ? $users['group_name'] : TEXT_ADMINISTRATOR);
                $choices[$group_name][filter_var($users['id'],FILTER_SANITIZE_STRING)] = $app_users_cache[filter_var($users['id'],FILTER_SANITIZE_STRING)]['name'];
              } 
            }            
                        
                            
            $attributes = array('class'=>'form-control  chosen-select',                                                          
                                'data-placeholder'=>TEXT_SELECT_SOME_VALUES);
                                
            if($field_cfg->get('display_as')=='checkboxes' or $field_cfg->get('display_as')=='dropdown_muliple')
            {
              $attributes['multiple'] = 'multiple';
              
              $html = select_tag('values[' . filter_var($fields_id,FILTER_SANITIZE_STRING) . '][]',filter_var_array($choices),'',filter_var_array($attributes));
            }
            else
            {
              $html = select_tag('values[' . filter_var($fields_id,FILTER_SANITIZE_STRING) . ']',filter_var_array($choices),'',filter_var_array($attributes));
            }            
                        
            
            
          break;
      }
      
      echo $html;
      
      exit();
    break;
  case 'update_selected':
      if(count($app_selected_items[$_GET['reports_id']])>0)
      {    
      	$updated_fields_types = [];
      	
      	//include smsm modules
      	$modules = new modules('sms');
      	
        foreach($app_selected_items[$_GET['reports_id']] as $item_id)
        {
          //get item info
          $item_info = db_find("app_entity_" . $reports_info['entities_id'],$item_id);
          
          $app_send_to = array();             
          $app_send_to_new_assigned = array();
          $app_changed_fields = array();
          
          foreach(filter_var_array($_POST['fields_id']) as $fields_id)
          {            
            //get field info
            $field_info = db_find('app_fields',$fields_id);
            
            $updated_fields_types[] = $field_info['type'];
            
            $field_cfg = new fields_types_cfg($field_info['configuration']);
            
            //submited value
            if(isset($_POST['values'][$fields_id]))
            {            
              $value = filter_var($_POST['values'][$fields_id],FILTER_SANITIZE_STRING);
            }
            else
            {
              $value = '';
            }
                        
            //prepare value to update
            switch($field_info['type'])
            {
              case 'fieldtype_radioboxes':
              case 'fieldtype_dropdown':                       
                  //there is no value changes for this field type
                      
                  if($value!=$item_info['field_'.$fields_id] and $field_cfg->get('notify_when_changed')==1)
                  {
                    $app_changed_fields[] = array(
                    		'name'=>$field_info['name'],
                    		'value'=>$app_choices_cache[$value]['name'],
                    		'fields_id'=>$field_info['id'],
                    		'fields_value'=>$value,
                    );
                  }                        
                break;
              
              case 'fieldtype_dropdown_multilevel':
              		if(strlen($value))
              		{                			              			
              			$value_array = ($field_cfg->get('use_global_list') ? global_lists::get_paretn_ids($value) : fields_choices::get_paretn_ids($value));
              			$value = implode(',',array_reverse($value_array));              			
              		}	
              	break;
              	
              case 'fieldtype_entity':
              case 'fieldtype_dropdown_multiple':
              case 'fieldtype_checkboxes':                       
              case 'fieldtype_tags':
                  if(is_array($value))
                  {
                    $value = implode(',',$value);
                  }                        
                break;
                                
              case 'fieldtype_grouped_users':
                  if(is_array($value))
                  {
                    $value = implode(',',$value);
                  }
                break;  
              case 'fieldtype_users_ajax':
              case 'fieldtype_users':
                  if(is_array($value))
                  {                                       
                    $value = implode(',',$value);  
                    
                    if($value!=$item_info['field_'.$fields_id])
                    {
                      foreach(array_diff(explode(',',$value),explode(',',$item_info['field_'.$fields_id])) as $v)
                      {
                        $app_send_to_new_assigned[] = $v;
                      }                      
                    }                                      
                  }
                break;
                
              case 'fieldtype_input_numeric':
                  if(preg_match('/^[0-9.]+$/',$value,$matches) or preg_match('/^([\+\-\*\/])([0-9.]+)$/',$value,$matches) or preg_match('/^([\+\-\*\/])([0-9.]+)([%])$/',$value,$matches))
                  {                    
                    if(count($matches)==3 or count($matches)==4)
                    {
                      if(count($matches)==3)
                      {
                        $value = $matches[2];
                      }
                      else
                      {
                        $value = (($matches[2] / 100) * $item_info['field_'.$fields_id]);                        
                      }
                      
                      switch($matches[1])
                      {
                        case '+':
                            $value = $item_info['field_'.$fields_id]+$value;
                          break;
                        case '-':
                            $value = $item_info['field_'.$fields_id]-$value;
                          break;
                        case '*':
                            $value = $item_info['field_'.$fields_id]*$value;
                          break;
                        case '/':
                            $value = $item_info['field_'.$fields_id]/$value;
                          break;
                      }
                    }                                        
                  }
                break;
                
              case 'fieldtype_input_date':
              case 'fieldtype_input_datetime':
                  if(preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$value,$matches) or preg_match('/^([\+\-])([0-9]+)$/',$value,$matches))
                  {
                    if(count($matches)==4)
                    {
                      $value = get_date_timestamp($value);
                    }
                    else
                    {
                      if(strlen($item_info['field_'.$fields_id])>0)
                      {
                        $value = strtotime($value . ' day',$item_info['field_'.$fields_id]);
                      }
                      else
                      {
                        $value = '';
                      }
                    }
                  }
                break;  
            }
            
            //update field value
            db_query("update app_entity_" . filter_var($reports_info['entities_id'],FILTER_SANITIZE_STRING) . " set field_" . filter_var($fields_id,FILTER_SANITIZE_STRING) . " = '" . db_input(filter_var($value,FILTER_SANITIZE_STRING)) . "' where id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");
            
            //set date updated
            db_query("update app_entity_" . filter_var($reports_info['entities_id'],FILTER_SANITIZE_STRING) . " set date_updated = '" . time() . "' where id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");
            
            //update choices values
            $choices_values = new choices_values($reports_info['entities_id']);                                                
            $choices_values->process_by_field_id($item_id,$fields_id,$field_info['type'],$value);
            
             
            //atuoset fieldtype autostatus
            fieldtype_autostatus::set($reports_info['entities_id'], $item_id);
            
            //autoupdate static text pattern
            fieldtype_text_pattern_static::set($reports_info['entities_id'], $item_id);
            
            //autocalculate time diff
            if(in_array('fieldtype_input_date',$updated_fields_types) or in_array('fieldtype_input_datetime',$updated_fields_types))
            {
            	fieldtype_days_difference::update_items_fields($reports_info['entities_id'], $item_id);
            	fieldtype_hours_difference::update_items_fields($reports_info['entities_id'], $item_id);
            	fieldtype_years_difference::update_items_fields($reports_info['entities_id'], $item_id);
            	fieldtype_months_difference::update_items_fields($reports_info['entities_id'], $item_id);
            }
            
            //atuocreate comments if fields changed
            if(count($app_changed_fields))
            {
            	comments::add_comment_notify_when_fields_changed($reports_info['entities_id'], $item_id,$app_changed_fields);
            }
            
            //log changeds
            if(class_exists('track_changes'))
            {
            	$log = new track_changes($reports_info['entities_id'], $item_id);
            	$log->log_update($item_info);
            }
            
            /**
             * Start email notification code
             **/
            $app_send_to = users::get_assigned_users_by_item($reports_info['entities_id'], $item_id);
            
            
            //sending sms            
            $sms = new sms($reports_info['entities_id'], $item_id);
            $sms->send_to = $app_send_to;
            $sms->send_edit_msg($item_info);            	
            
            
            //include sender in notification              
            if(CFG_EMAIL_COPY_SENDER==1)
            {
              $app_send_to[] = $app_user['id'];
            }
                        
            
             //Send notification if there are assigned users and there are changed fields or new assigned users
            if(!isset($_POST['do_not_notify']) and ((count($app_send_to)>0 and count($app_changed_fields)>0) or count($app_send_to_new_assigned)>0))
            {             	
              //$heading_field_id = fields::get_heading_id($reports_info['entities_id']);
              //$item_name = ($heading_field_id>0 ? $item_info['field_' . $heading_field_id] : $item_info['id']);
              
              $breadcrumb = items::get_breadcrumb_by_item_id($reports_info['entities_id'], $item_info['id']);
              $item_name = $breadcrumb['text'];
              
              $entity_cfg = new entities_cfg($reports_info['entities_id']);
              
              //prepare subject for update itme      
              if(count($app_changed_fields)>0)
              {
                $subject = (strlen($entity_cfg->get('email_subject_updated_item'))>0 ? $entity_cfg->get('email_subject_updated_item') . ' ' . $item_name : TEXT_DEFAULT_EMAIL_SUBJECT_UPDATED_ITEM . ' ' . $item_name);
                
                //add changed field values in subject
                $extra_subject = array();
                foreach($app_changed_fields as $v)
                {
                  $extra_subject[] = $v['name'] . ': ' . $v['value']; 
                }
                
                $subject .= ' [' . implode(' | ', $extra_subject) . ']';
              }
              else
              {       
                //subject for new item    
                $subject = (strlen($entity_cfg->get('email_subject_new_item'))>0 ? $entity_cfg->get('email_subject_new_item') . ' ' . $item_name : TEXT_DEFAULT_EMAIL_SUBJECT_NEW_ITEM . ' ' . $item_name);
              }
              
              
              $path_info = items::get_path_info($reports_info['entities_id'],$item_id);
              
              //default email heading
              $heading = users::use_email_pattern_style('<div><a href="' . url_for('items/info','path=' . $path_info['full_path'],true) . '"><h3>' . $subject . '</h3></a></div>','email_heading_content');
              
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
              		$body = users::use_email_pattern('single_column',array('email_single_column'=>items::render_info_box($reports_info['entities_id'],$item_id,$send_to, false)));
              	}
              	else
              	{
                	$body = users::use_email_pattern('single',array('email_body_content'=>items::render_content_box($reports_info['entities_id'],$item_id,$send_to),'email_sidebar_content'=>items::render_info_box($reports_info['entities_id'],$item_id,$send_to)));
              	}
                                    
                //change subject for new assigned user
                if(in_array($send_to,$app_send_to_new_assigned))
                {            
                  $new_subject = (strlen($entity_cfg->get('email_subject_new_item'))>0 ? $entity_cfg->get('email_subject_new_item') . ' ' . $item_name : TEXT_DEFAULT_EMAIL_SUBJECT_NEW_ITEM . ' ' . $item_name);
                  $new_heading = users::use_email_pattern_style('<div><a href="' . url_for('items/info','path=' . $path_info['full_path'],true) . '"><h3>' . $new_subject . '</h3></a></div>','email_heading_content');
                  
                  if(users_cfg::get_value_by_users_id($send_to, 'disable_notification')!=1 and $entity_cfg->get('disable_notification')!=1)
                  {
                  	users::send_to(array($send_to),$new_subject,$new_heading . $body);
                  }
                  
                  //add users notification
                  if($entity_cfg->get('disable_internal_notification')!=1)
                  	users_notifications::add($new_subject,'new_item', $send_to, $reports_info['entities_id'], $item_id);
                }
                else          
                {
                  if(users_cfg::get_value_by_users_id($send_to, 'disable_notification')!=1 and $entity_cfg->get('disable_notification')!=1)
                  {
                  	users::send_to(array($send_to),$subject,$heading . $body);
                  }
                  
                  //add users notification
                  if($entity_cfg->get('disable_internal_notification')!=1)
                  	users_notifications::add($subject,'updated_item', $send_to, $reports_info['entities_id'], $item_id);
                }                                       
              } 
                             
            }
            /**
             * End email notification code
             **/  
            
          }
        }
        
        switch($app_redirect_to)
        {
        	case 'parent_item_info_page':      	      		      
      		  $redirect_to = url_for('items/info','path=' . app_path_get_parent_path($app_path));
      			break;
        	default:
        		if(isset($_POST['path']))
        		{
        			$redirect_to = url_for('items/items','path=' . filter_var($_POST['path'],FILTER_SANITIZE_STRING));
        		}
        		else
        		{
        			$redirect_to = url_for('reports/view','reports_id=' . filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING));
        		}		
        		break;
        }
                                               
        
        echo '
            <div class="alert alert-success">' . TEXT_EXT_ITEMS_UPDATING_COMPLETED . '</div> 
            <script>
              location.href="' .  $redirect_to . '";
            </script>         
          ';
      }
                  
      exit();
    break;
}