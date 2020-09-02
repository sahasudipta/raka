<?php

switch($app_module_action)
{
  case 'sort':
      if(isset($_POST['sort_items']))
      {
        $sort_order = 0;
        foreach(explode(',',filter_var($_POST['sort_items'],FILTER_SANITIZE_STRING)) as $v)
        {
          db_query("update app_entities_menu set sort_order='" . $sort_order . "' where id='" . str_replace('item_','',$v). "'");
          
          $sort_order++;
        }
      }
      exit();
    break;
  case 'sort_items':
    	if(isset($_POST['sort_items']))
    	{
    		db_query("update app_entities_menu set entities_list='" . str_replace('item_','',filter_var($_POST['sort_items'],FILTER_SANITIZE_STRING)) . "' where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)). "'",true);    	
    	}
    	exit();
    	break;
  case 'save':
  	  	
    $sql_data = array(
        'name' => db_prepare_input(filter_var($_POST['name'],FILTER_SANITIZE_STRING)),
        'icon' => db_prepare_input(filter_var($_POST['icon'],FILTER_SANITIZE_STRING)),
        'entities_list' => (isset($_POST['entities_list']) ? implode(',',filter_var_array($_POST['entities_list'])) : ''),
        'reports_list' => (isset($_POST['reports_list']) ? implode(',',filter_var_array($_POST['reports_list'])) : ''),
        'sort_order'=>db_prepare_input(filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING)),
        'parent_id' => filter_var($_POST['parent_id'],FILTER_SANITIZE_STRING),
    );
    
    
    if(isset($_GET['id']))
    {        
      db_perform('app_entities_menu',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
    }
    else
    {               
      db_perform('app_entities_menu',$sql_data);                  
    }
        
    redirect_to('entities/menu');      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {     
      	$obj = db_find('app_entities_menu',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
                 
        db_delete_row('app_entities_menu',filter_var($_GET['id'],FILTER_SANITIZE_STRING));        
                              
        $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$obj['name']),'success');
                        
        redirect_to('entities/menu');  
      }
    break;   
}