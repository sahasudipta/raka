<?php

switch($app_module_action)
{
  case 'save':
      $sql_data = array(
      	'name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
      	'notes'=>filter_var($_POST['notes'],FILTER_SANITIZE_STRING)
      );
                                                                              
      if(isset($_GET['id']))
      {        
        db_perform('app_global_lists',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
      }
      else
      {               
        db_perform('app_global_lists',$sql_data);
      }
      
      redirect_to('global_lists/lists');      
    break;
  case 'delete':
      if(isset($_GET['id']))
      {      
        $msg = global_lists::check_before_delete($_GET['id']);
        
        if(strlen($msg)>0)
        {
          $alerts->add($msg,'error');
        }
        else
        {
          $name = global_lists::get_name_by_id(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
                    
          db_delete_row('app_global_lists',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          db_delete_row('app_global_lists_choices',filter_var($_GET['id'],FILTER_SANITIZE_STRING),'lists_id');
          
          $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$name),'success');
        }
        
        redirect_to('global_lists/lists');  
      }
    break;    
}