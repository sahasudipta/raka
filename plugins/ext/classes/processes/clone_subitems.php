<?php

class clone_subitems
{
	
	static function clone_process($actions_id, $parent_id=0, $linked_item_id, $parent_item_id, $item_type = 'parent_item_id')
	{
		global $app_fields_cache, $app_user;
		
		$rules_query = db_query("select * from app_ext_processes_clone_subitems where actions_id='" . filter_var($actions_id,FILTER_SANITIZE_STRING) . "' and parent_id='" . filter_var($parent_id,FILTER_SANITIZE_STRING) . "'");
		while($rules = db_fetch_array($rules_query))
		{
		    $items_query = db_query("select e.* " . fieldtype_formula::prepare_query_select(filter_var($rules['from_entity_id'],FILTER_SANITIZE_STRING), '') . " from app_entity_" . db_input(filter_var($rules['from_entity_id'],FILTER_SANITIZE_STRING)) . " e where e." . db_input(filter_var($item_type,FILTER_SANITIZE_STRING)) . "='" . db_input(filter_var($linked_item_id ,FILTER_SANITIZE_STRING)). "'");
			while($items = db_fetch_array($items_query))
			{
				$choices_values = new choices_values($rules['to_entity_id']);
				
				//prepare sql data
				$sql_data = [];
				$sql_data['parent_item_id'] = $parent_item_id;
				foreach(preg_split('/\r\n|\r|\n/',$rules['fields']) as $values)
				{
					if(!strstr($values,'=')) continue;
						
					$values = explode('=',str_replace(array(' ','[',']'),'',$values));
					$from_field_id = trim($values[0]);
					$to_field_id = trim($values[1]);
					
					//prepare default fields
					if(isset($items['field_' . $from_field_id]))
					{
						if(isset($app_fields_cache[$rules['to_entity_id']][$to_field_id]))
						{
							if(in_array($app_fields_cache[$rules['to_entity_id']][$to_field_id]['type'],fields_types::get_attachments_types()))
							{
								$sql_data['field_' . $to_field_id] = attachments::copy($items['field_' . $from_field_id]);
							}
							else
							{
								$sql_data['field_' . $to_field_id] = $items['field_' . $from_field_id];
							}
							
							//prepare choices
							$process_options = array(
									'class'          => $app_fields_cache[$rules['to_entity_id']][$to_field_id]['type'],
									'value'          => $items['field_' . $from_field_id],									
									'field'          => $app_fields_cache[$rules['to_entity_id']][$to_field_id],									
							);
							
							$choices_values->prepare($process_options);
						}
					}
					//value from internal fields id or parent_item_id
					elseif(isset($items[$from_field_id]) and isset($app_fields_cache[$rules['to_entity_id']][$to_field_id]))
					{
					    $sql_data['field_' . $to_field_id] = $items[$from_field_id];
					}	
					//prepare single value
					elseif(isset($app_fields_cache[$rules['to_entity_id']][$to_field_id]))
					{
					    $sql_data['field_' . $to_field_id] = "{$from_field_id}";
					}
					//handle parent_item_id for cloned item
					elseif($to_field_id=='parent_item_id' and is_numeric($from_field_id))
					{
					    $sql_data['parent_item_id'] = $from_field_id;
					}					
				}
				
				$sql_data['date_added'] = time();
				$sql_data['created_by'] = $app_user['id'];
				
				//print_rr($sql_data);
				
				db_perform('app_entity_' . $rules['to_entity_id'], $sql_data);
				$insert_id = db_insert_id();
				
				//insert choices values for fields with multiple values
				$choices_values->process($insert_id);
				
				//autoupdate all field types
				fields_types::update_items_fields($rules['to_entity_id'], $insert_id);				
				
				self::clone_process($actions_id, $rules['id'], $items['id'], $insert_id);
			}
		}
	}
	
	static function get_rules_tree($actions_id, $parent_id=0, $choices = [], $level=0)
	{
		$rules_query = db_query("select * from app_ext_processes_clone_subitems where actions_id='" . filter_var($actions_id,FILTER_SANITIZE_STRING) . "' and parent_id='" . filter_var($parent_id,FILTER_SANITIZE_STRING) . "'");
		while($rules = db_fetch_array($rules_query))
		{
			$choices[] = [
					'id' => filter_var($rules['id'],FILTER_SANITIZE_STRING),
					'parent_id' => filter_var($rules['parent_id'],FILTER_SANITIZE_STRING),
					'from_entity_id' => filter_var($rules['from_entity_id'],FILTER_SANITIZE_STRING),
					'to_entity_id' => filter_var($rules['to_entity_id'],FILTER_SANITIZE_STRING),
					'level' => $level,
			];
			
			$choices = self::get_rules_tree($actions_id,filter_var($rules['id'],FILTER_SANITIZE_STRING),$choices,$level+1);
		}
		
		return $choices;
	}
	
	static function delete_rule($actions_id, $parent_id)
	{
		$rules = self::get_rules_tree($actions_id,$parent_id);
		
		$rules[] = ['id'=>$parent_id];
		
		foreach($rules as $rule)
		{
			db_delete_row('app_ext_processes_clone_subitems', filter_var($rule['id'],FILTER_SANITIZE_STRING));
		}
	}
}
