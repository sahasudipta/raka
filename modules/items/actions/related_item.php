<?php

if(!users::has_access('update'))
{        
  redirect_to('dashboard/access_forbidden');
}

switch($app_module_action)
{  
  case 'remove_related_items';
      if(isset($_POST['items']))
      {
      	$table_info = related_records::get_related_items_table_name($current_entity_id,filter_var($_POST['related_entities_id'],FILTER_SANITIZE_STRING));
      	
        foreach(filter_var_array($_POST['items']) as $id)
        {        	
          $relatd_items_info = db_find($table_info['table_name'],filter_var_array($id));
        	related_records::autocreate_comments_delete(filter_var($_POST['related_entities_id'],FILTER_SANITIZE_STRING),$relatd_items_info['entity_' . filter_var($_POST['related_entities_id'],FILTER_SANITIZE_STRING)  . $table_info['sufix'] . '_items_id'], filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($current_item_id,FILTER_SANITIZE_STRING));
        	
        	db_delete_row($table_info['table_name'], filter_var_array($id));                    
        }
      }
      
      redirect_to('items/info','path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING));
    break;
  case 'remove_related_item':
    	
  	  $table_info = related_records::get_related_items_table_name($current_entity_id,filter_var($_GET['related_entity_id'],FILTER_SANITIZE_STRING));
  		db_delete_row($table_info['table_name'], filter_var($_GET['id'],FILTER_SANITIZE_STRING));            
        
      redirect_to('items/info','path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING));
    
    exit();
  break;
    
  case 'add_related_item':
                
      if(isset($_POST['items']) and isset($_POST['related_entities_id']))
      {
        $related_entities_id = (int)$_POST['related_entities_id'];
        
        $table_info = related_records::get_related_items_table_name(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($related_entities_id,FILTER_SANITIZE_STRING));
                
        foreach(filter_var_array($_POST['items']) as $related_items_id)
        {
          $check_query = db_query("select * from " . $table_info['table_name'] . " where entity_" . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . "_items_id=" . filter_var((int)$current_item_id,FILTER_SANITIZE_STRING) . " and entity_" . filter_var($related_entities_id,FILTER_SANITIZE_STRING)  . $table_info['sufix'] . "_items_id = '" . filter_var((int)$related_items_id,FILTER_SANITIZE_STRING) . "'");
          if(!$check = db_fetch_array($check_query))
          {            
            $sql_data = array('entity_' . $current_entity_id . '_items_id' => $current_item_id,                              
                              'entity_' . $related_entities_id  . $table_info['sufix'] . '_items_id' => $related_items_id);
                              
            db_perform($table_info['table_name'],$sql_data);
            
            related_records::autocreate_comments($related_entities_id,$related_items_id, $current_entity_id,$current_item_id);
                        
          }
        }
      }
      
      redirect_to('items/info','path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING));
      
    break; 
}