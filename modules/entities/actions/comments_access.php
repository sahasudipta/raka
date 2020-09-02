<?php

switch($app_module_action)
{
  case 'set_access':
                                          
        if(isset($_POST['access']))
        {          
          foreach($_POST['access'] as $access_groups_id=>$access)
          {                                    
            $sql_data = array('access_schema'=>str_replace('_',',',$access));
            
            $acess_info_query = db_query("select access_schema from app_comments_access where entities_id='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "' and access_groups_id='" . db_input(filter_var($access_groups_id,FILTER_SANITIZE_STRING)). "'");
            if($acess_info = db_fetch_array($acess_info_query))
            {
              db_perform('app_comments_access',$sql_data,'update',"entities_id='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "' and access_groups_id='" . db_input(filter_var($access_groups_id,FILTER_SANITIZE_STRING)). "'");
            }
            else
            {
              $sql_data['entities_id'] = filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING);
              $sql_data['access_groups_id'] = filter_var($access_groups_id,FILTER_SANITIZE_STRING);
              db_perform('app_comments_access',$sql_data);
            }          
          }
          
          $alerts->add(TEXT_ACCESS_UPDATED,'success');
        }
                        
      redirect_to('entities/comments_access','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
    break;
}