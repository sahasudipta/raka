<?php

switch($app_module_action)
{
	case 'save_javascript':
		$cfg = new entities_cfg($_GET['entities_id']);
		$cfg->set('javascript_in_from',$_POST['javascript_in_from']);
		$cfg->set('javascript_onsubmit',$_POST['javascript_onsubmit']);
		
		$alerts->add(TEXT_CONFIGURATION_UPDATED,'success');
		
		redirect_to('entities/forms','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
		
		break;
  case 'sort_fields':
        //print_r($_POST);
        $tabs_query = db_fetch_all('app_forms_tabs',"entities_id='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "' order by  sort_order, name");
        while($tabs = db_fetch_array($tabs_query))
        {
          if(isset($_POST['forms_tabs_' . $tabs['id']]))
          {
            $sort_order = 0;
            foreach(explode(',',filter_var($_POST['forms_tabs_' . filter_var($tabs['id'],FILTER_SANITIZE_STRING)],FILTER_SANITIZE_STRING)) as $v)
            {
              db_perform('app_fields',array('forms_tabs_id'=>$tabs['id'], 'sort_order'=>$sort_order),'update',"id='" . db_input(str_replace('form_fields_','',$v)) . "'");
              $sort_order++;
            }
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
          db_perform('app_forms_tabs',array('sort_order'=>$sort_order),'update',"id='" . db_input(filter_var($v,FILTER_SANITIZE_STRING)) . "'");
          $sort_order++;
        }
      }      
      exit();
    break;
  case 'save_tab':
      $sql_data = array('name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
                        'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),
                        'description'=>filter_var($_POST['description'],FILTER_SANITIZE_STRING),                        
                        );
      
      if(isset($_GET['id']))
      {        
        db_perform('app_forms_tabs',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
      }
      else
      {   
      	$sql_data['sort_order'] = (forms_tabs::get_last_sort_number(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING))+1);
        db_perform('app_forms_tabs',$sql_data);
      }
      
      redirect_to('entities/forms','entities_id=' . filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING));      
    break;
  case 'delete':
      if(isset($_GET['id']))
      {      
        $msg = forms_tabs::check_before_delete(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
        
        if(strlen($msg)>0)
        {
          $alerts->add($msg,'error');
        }
        else
        {
          $name = forms_tabs::get_name_by_id(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          
          db_delete_row('app_forms_tabs',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          
          $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$name),'success');
        }
        
      
        redirect_to('entities/forms','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));  
      }
    break;  
      
}