<?php

if (!app_session_is_registered('entities_templates_filter')) 
{
  $entities_templates_filter = 0;
  app_session_register('entities_templates_filter');    
} 

switch($app_module_action)
{
  case 'set_entities_templates_filter':
      $entities_templates_filter = $_POST['entities_templates_filter'];
      
      redirect_to('ext/templates/entities_templates');
    break;
  case 'sort_templates':
        if(isset($_POST['templates'])) 
        {
          $sort_order = 0;
          foreach(explode(',',filter_var($_POST['templates'],FILTER_SANITIZE_STRING)) as $v)
          {
            $sql_data = array('sort_order'=>$sort_order);
            db_perform('app_ext_entities_templates',$sql_data,'update',"id='" . db_input(str_replace('template_','',$v)) . "'");
            $sort_order++;
          }
        }
      exit();
    break;  
  case 'save':
    $sql_data = array('name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
                      'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),
                      'is_active' => (isset($_POST['is_active']) ? 1:0),
                      'sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),
                      'users_groups' => (isset($_POST['users_groups']) ? implode(',',filter_var_array($_POST['users_groups'])):''),                                            
                      'assigned_to' => (isset($_POST['assigned_to']) ? implode(',',filter_var_array($_POST['assigned_to'])):''),
                      );
        
    if(isset($_GET['id']))
    { 
      //check if template entity was changed and reset fields   
      $template_info = db_find('app_ext_entities_templates',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
      if($template_info['entities_id']!=$_POST['entities_id'])
      {
        db_query("delete from app_ext_entities_templates_fields where templates_id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
      }
                           
      db_perform('app_ext_entities_templates',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
    }
    else
    {                     
      db_perform('app_ext_entities_templates',$sql_data);                              
    }
        
    redirect_to('ext/templates/entities_templates');      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {      
              
        db_query("delete from app_ext_entities_templates where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        db_query("delete from app_ext_entities_templates_fields where templates_id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
                            
        $alerts->add(TEXT_EXT_WARN_DELETE_TEMPLATE_SUCCESS,'success');
                     
        redirect_to('ext/templates/entities_templates');  
      }
    break;   
}