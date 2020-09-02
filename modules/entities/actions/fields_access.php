<?php

switch($app_module_action)
{
  case 'set_access':
                  
        if(isset($_POST['access']))
        {
          
          foreach(filter_var_array($_POST['access']) as $access_groups_id=>$fields)
          {
            foreach($fields as $id=>$access)
            {
              if(in_array($access,array('view','hide')))
              {
                $sql_data = array('access_schema'=>$access);
              
                $acess_info_query = db_query("select access_schema from app_fields_access where entities_id='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "' and access_groups_id='" . db_input(filter_var($access_groups_id,FILTER_SANITIZE_STRING)) . "' and fields_id='" . db_input(filter_var($id,FILTER_SANITIZE_STRING)) . "'");
                if($acess_info = db_fetch_array($acess_info_query))
                {
                  db_perform('app_fields_access',$sql_data,'update',"entities_id='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "' and access_groups_id='" . db_input(filter_var($access_groups_id,FILTER_SANITIZE_STRING)). "'  and fields_id='" . db_input(filter_var($id,FILTER_SANITIZE_STRING)) . "'");
                }
                else
                {
                  $sql_data['entities_id'] = filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING);
                  $sql_data['access_groups_id'] = filter_var($access_groups_id,FILTER_SANITIZE_STRING);
                  $sql_data['fields_id'] = filter_var($id,FILTER_SANITIZE_STRING);
                  db_perform('app_fields_access',$sql_data);
                }
              }
              else
              {
                db_query("delete from app_fields_access where entities_id='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "' and access_groups_id='" . db_input(filter_var($access_groups_id,FILTER_SANITIZE_STRING)). "'  and fields_id='" . db_input(filter_var($id,FILTER_SANITIZE_STRING)) . "'");
              }
            }                                                                                
          }
          
          $alerts->add(TEXT_ACCESS_UPDATED,'success');
        }
                        
      redirect_to('entities/fields_access','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&ui_accordion_active=' . filter_var($_POST['ui_accordion_active'],FILTER_SANITIZE_STRING));
    break;
}