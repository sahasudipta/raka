<?php

class records_visibility
{
	
	static function merget_fields_choices($entities_id)
	{
		global $app_entities_cache;
		
		$allowed_types = [
				'fieldtype_dropdown',
				'fieldtype_dropdown_multiple',
				'fieldtype_dropdown_multilevel',
				'fieldtype_tags',
				'fieldtype_checkboxes',
				'fieldtype_radioboxes',
				'fieldtype_entity',
				'fieldtype_entity_ajax',
				'fieldtype_entity_multilevel',		        
		];
		
		$choices = [];
		
		$users_fields_query = db_query("select id, name, type, configuration from app_fields where entities_id=1 and type in ('" . implode("','",$allowed_types). "')");
		while($users_fields = db_fetch_array($users_fields_query))
		{
			$users_cfg = new fields_types_cfg($users_fields['configuration']);
			
			$fields_query = db_query("select id, name, type, configuration from app_fields where entities_id='" . $entities_id . "' and type in ('" . implode("','",$allowed_types). "')");
			while($fields = db_fetch_array($fields_query))
			{
				$cfg = new fields_types_cfg($fields['configuration']);
				//echo $users_fields['name'] . ' ' . $cfg->get('use_global_list') . '==' . $fields['name'] . ': ' . $users_cfg->get('use_global_list') . '<br>';
				
				if(($cfg->get('entity_id')==$users_cfg->get('entity_id') and $cfg->get('entity_id')>0 and $users_cfg->get('entity_id')>0) 
						or ($cfg->get('use_global_list')==$users_cfg->get('use_global_list') and $cfg->get('use_global_list')>0 and $users_cfg->get('use_global_list')>0))
				{
					$choices[$users_fields['id'] . '-' . $fields['id']] = TEXT_USERS . ': ' . $users_fields['name'] . ' => ' . $app_entities_cache[$entities_id]['name'] . ': ' . $fields['name'];   
				}								
			}
		}
		
		//ruels for current users and users fields types
		$fields_query = db_query("select id, name, type, configuration from app_fields where entities_id='" . $entities_id . "' and type in ('fieldtype_users','fieldtype_users_ajax','fieldtype_created_by')");
		while($fields = db_fetch_array($fields_query))
		{
		    $choices['current_user-' . $fields['id']] = TEXT_CURRENT_USER . ' => ' . $app_entities_cache[$entities_id]['name'] . ': ' . fields_types::get_option($fields['type'],'name',$fields['name']);
		}
		
		return $choices;
	}
	
	static function count_filters($rules_id)
	{
		$count = 0;
		$reports_info_query = db_query("select id from app_reports where reports_type='records_visibility" . $rules_id . "'");
		if($reports_info = db_fetch_array($reports_info_query))
		{
			$count_query = db_query("select count(*) as total from app_reports_filters where reports_id='" . filter_var($reports_info['id'],FILTER_SANITIZE_STRING) . "'");
			$count = db_fetch_array($count_query);
				
			$count = $count['total'];
		}
	
		return $count;
	}
	
	static function add_access_query($entities_id)
	{
		global $app_user, $app_fields_cache;
		
		//print_rr($app_user);
		
		$sql = [];
		
		//skip admins
		if($app_user['group_id']==0) return '';
		
		$rules_query = db_query("select * from app_records_visibility_rules where is_active=1 and entities_id='" . db_input(filter_var($entities_id,FILTER_SANITIZE_STRING)) . "' and find_in_set(" . filter_var($app_user['group_id'],FILTER_SANITIZE_STRING). ",users_groups)");						
		while($rules = db_fetch_array($rules_query))
		{
			$listing_sql_query = "";
			
			$reports_info_query = db_query("select id from app_reports where reports_type='records_visibility" . filter_var($rules['id'],FILTER_SANITIZE_STRING) . "'");
			if($reports_info = db_fetch_array($reports_info_query))
			{
				$listing_sql_query = reports::add_filters_query(filter_var($reports_info['id'],FILTER_SANITIZE_STRING),$listing_sql_query);								
			}
			
			if(strlen($rules['merged_fields']))
			{
				foreach(explode(',',filter_var($rules['merged_fields'],FILTER_SANITIZE_STRING)) as $merged_fields)
				{
					$merged_fields = explode('-',$merged_fields);
					$users_fields_id = filter_var($merged_fields[0],FILTER_SANITIZE_STRING);
					$fields_id = filter_var($merged_fields[1],FILTER_SANITIZE_STRING);
					
					//ruels for current users and users fields types
					if($users_fields_id=='current_user')
					{
					    if($app_fields_cache[$entities_id][$fields_id]['type']=='fieldtype_created_by')
					    {
					        $listing_sql_query .= " and e.created_by='" . db_input(filter_var($app_user['id'],FILTER_SANITIZE_STRING))."'";
					    }
					    else
					    {
					        $listing_sql_query .= " and (select count(*) from app_entity_" . filter_var($entities_id,FILTER_SANITIZE_STRING) . "_values as cv where cv.items_id=e.id and cv.fields_id='" . db_input(filter_var($fields_id,FILTER_SANITIZE_STRING))  . "' and cv.value = '" . db_input(filter_var($app_user['id'],FILTER_SANITIZE_STRING)) . "')>0 ";
					    }
					}
					else
					{															
    					if(!isset($app_user['fields']['field_' . $users_fields_id])) continue;
    					
    					$value = $app_user['fields']['field_' . $users_fields_id];
    					
    					if(!strlen($value)) $value=0;
    					
    					if(in_array($app_fields_cache[$entities_id][$fields_id]['type'],['fieldtype_entity_multilevel']))
    					{
    						$listing_sql_query .= " and e.field_".filter_var($fields_id,FILTER_SANITIZE_STRING)."='".filter_var($value,FILTER_SANITIZE_STRING)."'";
    					}
    					else 
    					{					
    						$listing_sql_query .= " and (select count(*) from app_entity_" . filter_var($entities_id,FILTER_SANITIZE_STRING) . "_values as cv where cv.items_id=e.id and cv.fields_id='" . db_input(filter_var($fields_id,FILTER_SANITIZE_STRING))  . "' and cv.value in (" . filter_var($value,FILTER_SANITIZE_STRING) . "))>0 ";
    					}
					}
				}
			}
			
			//check empty values
			if(strlen($rules['merged_fields_empty_values']))
			{
			    foreach(explode(',',filter_var($rules['merged_fields_empty_values'],FILTER_SANITIZE_STRING)) as $fields_id)
			    {
			        if(isset($app_fields_cache[filter_var($entities_id,FILTER_SANITIZE_STRING)][filter_var($fields_id,FILTER_SANITIZE_STRING)]))
			        {
			            switch($app_fields_cache[filter_var($entities_id,FILTER_SANITIZE_STRING)][filter_var($fields_id,FILTER_SANITIZE_STRING)]['type'])
			            {
			                case 'fieldtype_entity_multilevel':
			                case 'fieldtype_dropdown':
			                case 'fieldtype_radioboxes':
			                case 'fieldtype_created_by':
			                    $listing_sql_query .= " and e.field_".filter_var($fields_id,FILTER_SANITIZE_STRING)."=0 ";
			                    break;
			                default:
			                    $listing_sql_query .= " and length(e.field_".filter_var($fields_id,FILTER_SANITIZE_STRING).")=0 ";
			                    break;
			            }
			            
			        }
			    }
			    
			}
			
			
			if(substr($listing_sql_query,0,3) == 'and') $listing_sql_query = substr($listing_sql_query,3);
			if(substr($listing_sql_query,0,4) == ' and') $listing_sql_query = substr($listing_sql_query,4);
			
			if(strlen($listing_sql_query))
			{
				$sql[] = $listing_sql_query;
			}
		}
		
		//print_r($sql);
		
		if(count($sql))
		{
			return " and ((" . implode(') or (', $sql). "))";
		}
		else
		{
			return '';
		}
		
	}
	
	static function users_by_visibility_rules($entity_id, $item_id)
	{
	    global $app_user;
	    	    	    
	    //get users groups for entity
	    $users_groups = [];
	    $rules_query = db_query("select users_groups from app_records_visibility_rules where is_active=1 and entities_id='" . filter_var($entity_id,FILTER_SANITIZE_STRING) . "'");
	    while($rules = db_fetch_array($rules_query))
	    {
	        $users_groups = array_merge($users_groups,explode(',',filter_var($rules['users_groups'],FILTER_SANITIZE_STRING)));
	    }
	    
	    if(!count($users_groups)) return [];
	    
	    $users_groups = array_unique($users_groups);
	    
	    //print_rr($users_groups);
	    
	    //hold current user;
	    $app_user_holder = $app_user;
	    
	    $users_list = [];
	    
	    $users_query = db_query("select * from app_entity_1 where field_6 in (" . implode(',',filter_var_array($users_groups)) . ")", false);
	    while($users = db_fetch_array($users_query))
	    {
	        $app_user = [
	            'id' => filter_var($users['id'],FILTER_SANITIZE_STRING),
	            'group_id' => filter_var($users['field_6'],FILTER_SANITIZE_STRING),
	            'fields' => filter_var_array($users),
	        ];
	        
	        //print_rr($app_user);
	        
	        $item_query = db_query("select e.id from app_entity_" . filter_var($entity_id,FILTER_SANITIZE_STRING) . " e where e.id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "' " . items::add_access_query(filter_var($entity_id,FILTER_SANITIZE_STRING),'') . " " . records_visibility::add_access_query(filter_var($entity_id,FILTER_SANITIZE_STRING)) . " " . items::add_access_query_for_parent_entities(filter_var($entity_id,FILTER_SANITIZE_STRING)),false);
	        if($item = db_fetch_array($item_query))
	        {
	            //print_rr($item);
	            
	            $users_list[] = filter_var($users['id'],FILTER_SANITIZE_STRING);
	        }
	    }
	    
	    //restore current user
	    $app_user = $app_user_holder;
	    
	    //exit();
	    
	    return $users_list;
	}
	
}