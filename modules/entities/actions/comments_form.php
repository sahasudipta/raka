<?php
switch($app_module_action)
{
  case 'set_fields':
        if(isset($_POST['fields_in_comments'])) 
        {
          $sort_order = 0;
          foreach(explode(',',filter_var($_POST['fields_in_comments'],FILTER_SANITIZE_STRING)) as $v)
          {
            $sql_data = array('comments_status'=>1,'comments_forms_tabs_id'=>0,'comments_sort_order'=>$sort_order);
            db_perform('app_fields',$sql_data,'update',"id='" . db_input(str_replace('fields_','',$v)) . "'");
            $sort_order++;
          }
        }
        
        $tabs_query = db_fetch_all('app_comments_forms_tabs',"entities_id='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "' order by  sort_order, name");
        while($tabs = db_fetch_array($tabs_query))
        {
        	if(isset($_POST['forms_tabs_' . $tabs['id']]))
        	{
        		echo htmlentities($_POST['forms_tabs_' . filter_var($tabs['id'],FILTER_SANITIZE_STRING)]);
        		$sort_order = 0;
        		foreach(explode(',',filter_var($_POST['forms_tabs_' . filter_var($tabs['id'],FILTER_SANITIZE_STRING)],FILTER_SANITIZE_STRING)) as $v)
        		{
        			db_perform('app_fields',array('comments_forms_tabs_id'=>$tabs['id'],'comments_status'=>1, 'comments_sort_order'=>$sort_order),'update',"id='" . db_input(str_replace('fields_','',$v)) . "'");
        			$sort_order++;
        		}
        	}
        }
        
        if(isset($_POST['available_fields'])) 
        {          
          foreach(explode(',',filter_var($_POST['available_fields'],FILTER_SANITIZE_STRING)) as $v)
          {
            $sql_data = array('comments_status'=>0,'comments_sort_order'=>0,'comments_forms_tabs_id'=>0);
            db_perform('app_fields',$sql_data,'update',"id='" . db_input(str_replace('fields_','',$v)) . "'");            
          }
        }
      exit();
    break;
    
    case 'sort_tabs':
    	if(isset($_POST['forms_tabs_ol']))
    	{
    		$sort_order = 0;
    		foreach(explode(',',str_replace('forms_tabs_','',filter_var($_POST['forms_tabs_ol'],FILTER_SANITIZE_STRING))) as $v)
    		{
    			db_perform('app_comments_forms_tabs',array('sort_order'=>$sort_order),'update',"id='" . db_input($v) . "'");
    			$sort_order++;
    		}
    	}
    	exit();
    	break;
    case 'save_tab':
    	$sql_data = array(
    		'name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
    		'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),    	
    		'sort_order'=>(forms_tabs::get_last_sort_number(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING))+1),
    	);
    
    	if(isset($_GET['id']))
    	{
    		db_perform('app_comments_forms_tabs',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
    	}
    	else
    	{
    		db_perform('app_comments_forms_tabs',$sql_data);
    	}
    
    	redirect_to('entities/comments_form','entities_id=' . filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING));
    	break;
    case 'delete':
    	if(isset($_GET['id']))
    	{
    		$msg = comments_forms_tabs::check_before_delete(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
    
    		if(strlen($msg)>0)
    		{
    			$alerts->add($msg,'error');
    		}
    		else
    		{
    			$name = comments_forms_tabs::get_name_by_id(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
    
    			db_delete_row('app_comments_forms_tabs',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
    
    			$alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$name),'success');
    		}
    
    
    		redirect_to('entities/comments_form','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
    	}
    	break;    
}