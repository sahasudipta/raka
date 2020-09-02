<?php

switch($app_module_action)
{
  case 'sort':
      if(isset($_POST['sort_items']))
      {
        $sort_order = 0;
        foreach(explode(',',filter_var($_POST['sort_items'],FILTER_SANITIZE_STRING)) as $v)
        {
          db_query("update app_access_groups set sort_order='" . $sort_order . "' where id='" . str_replace('item_','',$v). "'");
          
          $sort_order++;
        }
      }
      exit();
    break;
  case 'save':
    $sql_data = array('name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
                      'sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),                        
                      'is_default'=>(isset($_POST['is_default']) ? filter_var($_POST['is_default'],FILTER_SANITIZE_STRING):0),
                      'is_ldap_default'=>(isset($_POST['is_ldap_default']) ? filter_var($_POST['is_ldap_default'],FILTER_SANITIZE_STRING):0),
                      'ldap_filter' => filter_var($_POST['ldap_filter'],FILTER_SANITIZE_STRING),
                      );
    
    if(isset($_POST['is_default']))
    {
      db_query("update app_access_groups set is_default = 0");
    }
    
    if(isset($_POST['is_ldap_default']))
    {
      db_query("update app_access_groups set is_ldap_default = 0");
    }
    
    if(isset($_GET['id']))
    {        
      db_perform('app_access_groups',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
    }
    else
    {               
      db_perform('app_access_groups',$sql_data);                  
    }
        
    redirect_to('users_groups/users_groups');      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {      
        $msg = access_groups::check_before_delete(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
        
        if(strlen($msg)>0)
        {
          $alerts->add($msg,'error');
        }
        else
        {
          $name = access_groups::get_name_by_id(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          
          db_delete_row('app_access_groups',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          db_delete_row('app_entities_access',filter_var($_GET['id'],FILTER_SANITIZE_STRING),'access_groups_id');
                              
          $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$name),'success');
        }
                
        redirect_to('users_groups/users_groups');  
      }
    break;   
}