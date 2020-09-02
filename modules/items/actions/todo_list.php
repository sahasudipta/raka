<?php

switch($app_module_action)
{
	case 'update':
		$field_id = filter_var(_post::int('field_id'),FILTER_SANITIZE_STRING);
		$cfg = new fields_types_cfg($app_fields_cache[$current_entity_id][$field_id]['configuration']);
		
		$item_info_query = db_query("select field_" . filter_var($field_id,FILTER_SANITIZE_STRING) . " from app_entity_" . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . " where id='" . filter_var($current_item_id,FILTER_SANITIZE_STRING) . "'");
		if($item_info = db_fetch_array($item_info_query))
		{
			$todo_list = $item_info['field_' . filter_var($field_id,FILTER_SANITIZE_STRING)];
			$checked_value = '';
			
			foreach(preg_split('/\r\n|\r|\n/', $todo_list) as $key=>$value)
			{
				if($key==$_POST['list_id'])
				{										
					switch($_POST['is_checked'])
					{
						case '1':							
							$todo_list = str_replace($value,'*'.$value,$todo_list);
							$checked_value = $cfg->get('text_check') . ' ' . $value;
							break;
						case '0':
							$todo_list = str_replace($value,substr($value,1),$todo_list);
							$checked_value = $cfg->get('text_unckeck') . ' ' . substr($value,1);
							break;
					}
					break;
				}	
			}
			
			db_query("update app_entity_" . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . " set field_" . filter_var($field_id,FILTER_SANITIZE_STRING) . "='" . db_input($todo_list) . "' where id='" . db_input(filter_var($current_item_id,FILTER_SANITIZE_STRING)) . "'");
								
			//auto add comment
			if($cfg->get('use_comments')=='auto')
			{
				$sql_data = array(
						'entities_id'=>filter_var($current_entity_id,FILTER_SANITIZE_STRING),
						'items_id'=>filter_var($current_item_id,FILTER_SANITIZE_STRING),
						'description' => $checked_value,
						'date_added'=> time(),
						'created_by' => filter_var($app_user['id'],FILTER_SANITIZE_STRING),
				);
				
				db_perform('app_comments',$sql_data);
				
				$comments_id = db_insert_id();
				
				//send notificaton
				app_send_new_comment_notification($comments_id,$current_item_id,$current_entity_id);
				
				//track changes
				if(class_exists('track_changes'))
				{
					$log = new track_changes($current_entity_id, $current_item_id);
					$log->log_comment($comments_id,array());
				}
			}
		}
		break;
}		