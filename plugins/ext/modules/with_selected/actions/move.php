<?php

//check report and access
$reports_info_query = db_query("select * from app_reports where id='" . db_input(filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING)). "'");
if($reports_info = db_fetch_array($reports_info_query))
{  
  $access_schema = users::get_entities_access_schema(filter_var($reports_info['entities_id'],FILTER_SANITIZE_STRING),$app_user['group_id']);
          
  if(!users::has_access('move',$access_schema))
  {      
    redirect_to('dashboard/access_forbidden'); 
  }
  
  $entity_info = db_find('app_entities',filter_var($reports_info['entities_id'],FILTER_SANITIZE_STRING));
  
  if($entity_info['parent_id']==0)
  {
    redirect_to('dashboard/page_not_found');
  }
}
else
{
  redirect_to('dashboard/page_not_found');
}

switch($app_module_action)
{ 
  case 'move_selected':
      $entities_id = filter_var($reports_info['entities_id'],FILTER_SANITIZE_STRING); 
      $entity_info = db_find('app_entities',filter_var($entities_id,FILTER_SANITIZE_STRING));
      
      //set default parent id
      $parent_item_id = 0;
                  
      //get parent id for sub-entities                                              
      if($entity_info['parent_id']>0)
      {
        if(strlen($_POST['move_to'])>0)
        {  
        	$path_info = items::get_path_info(filter_var($entity_info['parent_id'],FILTER_SANITIZE_STRING),filter_var((int)$_POST['move_to'],FILTER_SANITIZE_STRING));
           
          $go_to_url = url_for('items/items','path=' . $path_info['full_path'] . '/' . filter_var($entities_id,FILTER_SANITIZE_STRING));
                             
          $parent_item_id = filter_var((int)$_POST['move_to'],FILTER_SANITIZE_STRING);
        }
        
        //parent id is requried for sub-entities
        if($parent_item_id==0)
        {
          echo '<div class="alert alert-danger">' . TEXT_COPY_ERROR_PARENT_RECORD. '</div>';
          exit();
        }
      }
       
      //move records             
      if(count($app_selected_items[$_GET['reports_id']])>0 and $parent_item_id>0)
      {                    
        foreach($app_selected_items[$_GET['reports_id']] as $item_id)
        {
          $item_info_query = db_query("select * from app_entity_" . filter_var($entities_id,FILTER_SANITIZE_STRING) . " where id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");
          if($item_info = db_fetch_array($item_info_query))
          {
            $sql_data = array();             
            $sql_data['parent_item_id'] = filter_var($parent_item_id,FILTER_SANITIZE_STRING);            
            db_perform('app_entity_' . filter_var($entities_id,FILTER_SANITIZE_STRING),$sql_data,"update","id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");
            
            //track changes
            $log = new track_changes(filter_var($entities_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
            $log->log_move($parent_item_id);
          }
        }
                        
        echo '
          <div class="alert alert-success">' . TEXT_MOVING_COMPLETED . '</div> 
          <script>
            location.href="' . $go_to_url . '";
          </script>         
        ';
      }
      
                  
      exit();
    break;
}