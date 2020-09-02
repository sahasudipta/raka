<?php

switch($app_module_action)
{
  case 'create_timer':
        $timer_query = db_query("select * from app_ext_timer where entities_id='" . db_input(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING)) . "' and items_id='" . db_input(filter_var($_POST['items_id'],FILTER_SANITIZE_STRING)) . "' and users_id='" . db_input(filter_var($app_user['id'],FILTER_SANITIZE_STRING)) . "'");
        if(!$timer = db_fetch_array($timer_query))
        {
          $sql_data = array('seconds'=>0,
                            'entities_id'=>$_POST['entities_id'],
                            'items_id'=>$_POST['items_id'],
                            'users_id'=>$app_user['id'],                                                                                                   
                            );
                      
          db_perform('app_ext_timer',$sql_data);
        }
        
        echo timer::render_header_dropdown_menu();
        
      exit();
    break;
  case 'set_timer':
        $timer_query = db_query("select * from app_ext_timer where entities_id='" . db_input(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING)) . "' and items_id='" . db_input(filter_var($_POST['items_id'],FILTER_SANITIZE_STRING)) . "' and users_id='" . db_input(filter_var($app_user['id'],FILTER_SANITIZE_STRING)) . "'");
        if(!$timer = db_fetch_array($timer_query))
        {
          $sql_data = array('seconds'=>$_POST['seconds'],
                            'entities_id'=>$_POST['entities_id'],
                            'items_id'=>$_POST['items_id'],
                            'users_id'=>$app_user['id'],                                                                                                   
                            );
                      
          db_perform('app_ext_timer',$sql_data);
        }
        else
        {
          db_query("update app_ext_timer set seconds='" . db_input(filter_var($_POST['seconds'],FILTER_SANITIZE_STRING)) . "' where id='" . db_input(filter_var($timer['id'],FILTER_SANITIZE_STRING)) . "'");
        }
                        
      exit();
    break;
  case 'delete_timer':
        $timer_query = db_query("select * from app_ext_timer where entities_id='" . db_input(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING)) . "' and items_id='" . db_input(filter_var($_POST['items_id'],FILTER_SANITIZE_STRING)) . "' and users_id='" . db_input(filter_var($app_user['id'],FILTER_SANITIZE_STRING)) . "'");
        if($timer = db_fetch_array($timer_query))
        {
          db_delete_row('app_ext_timer',$timer['id']);
        }
        
        echo timer::render_header_dropdown_menu();
  
      exit();
    break;
}