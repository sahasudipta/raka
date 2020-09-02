<?php

if (!app_session_is_registered('functions_filter'))
{
	$functions_filter = 0;
	app_session_register('functions_filter');
}

$app_title = app_set_title(TEXT_EXT_FUNCTION);

switch($app_module_action)
{
	case 'set_functions_filter':
		$functions_filter = $_POST['functions_filter'];
	
		redirect_to('ext/functions/functions');
		break;
  case 'save':
    $sql_data = array('name'=>$_POST['name'],
                      'entities_id'=>$_POST['entities_id'],
                      'functions_name'=>$_POST['functions_name'],
                      'functions_formula'=>$_POST['functions_formula'],
                      'notes' => strip_tags($_POST['notes']),
                      );
        
    if(isset($_GET['id']))
    {               	
    	$functions_info = db_find('app_ext_functions',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
    	 
    	//check function  entity and if it's changed remove report filters
    	if($functions_info['entities_id']!=$_POST['entities_id'])
    	{    		    	
    		db_query("update app_reports set entities_id='" . filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING) . "' where id='" . filter_var($functions_info['reports_id'],FILTER_SANITIZE_STRING) . "'");	
    		
    		//reset filters    		
    		db_query("delete from app_reports_filters where reports_id='" . db_input(filter_var($functions_info['reports_id'],FILTER_SANITIZE_STRING)) . "'");
    	}
    	
      db_perform('app_ext_functions',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");            
    }
    else
    {     
    
      //atuo create report that allows setup filters for function
      $sql_reports_data = array('name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
                       'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),
                       'reports_type'=>'functions',                                              
                       'in_menu'=>0,
                       'in_dashboard'=>0,
                       'listing_order_fields'=>'',
                       'created_by'=>filter_var($app_logged_users_id,FILTER_SANITIZE_STRING),
                       );
                   
      db_perform('app_reports',$sql_reports_data);
      $reports_id = db_insert_id();
      
      $sql_data['reports_id'] = filter_var($reports_id,FILTER_SANITIZE_STRING);
      
      //insert function                    
      db_perform('app_ext_functions',$sql_data);   
      
      $insert_id = db_insert_id();                           
    }
        
    redirect_to('ext/functions/functions');      
  break;
  
  case 'delete':
      if(isset($_GET['id']))
      {      
        $obj = db_find('app_ext_functions',filter_var($_GET['id'],FILTER_SANITIZE_STRING));  
        
        db_query("delete from app_ext_functions where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        
        //delete reports
        db_query("delete from app_reports where id='" . db_input(filter_var($obj['reports_id'],FILTER_SANITIZE_STRING)) . "' and reports_type='functions'");
        db_query("delete from app_reports_filters where reports_id='" . db_input(filter_var($obj['reports_id'],FILTER_SANITIZE_STRING)) . "'");        
                                                         
        redirect_to('ext/functions/functions');  
      }
    break;
     
  case 'get_available_fields':
        
        echo fields::get_available_fields_helper(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING), 'functions_formula');        
        
      exit();
    break;  
}