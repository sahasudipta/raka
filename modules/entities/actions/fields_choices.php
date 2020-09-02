<?php

switch($app_module_action)
{
  case 'save':
			  	
      $sql_data = array('fields_id'=>filter_var($_POST['fields_id'],FILTER_SANITIZE_STRING),
                        'parent_id'=>(strlen(filter_var($_POST['parent_id'],FILTER_SANITIZE_STRING))==0 ? 0 : filter_var($_POST['parent_id'],FILTER_SANITIZE_STRING)),
                        'name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),                        
                        'users'=> (isset($_POST['users']) ? implode(',',filter_var_array($_POST['users'])):''),
                        'is_default'=>(isset($_POST['is_default']) ? filter_var($_POST['is_default'],FILTER_SANITIZE_STRING):0),
                        'is_active'=>(isset($_POST['is_active']) ? filter_var($_POST['is_active'],FILTER_SANITIZE_STRING):0),
                        'bg_color'=>filter_var($_POST['bg_color'],FILTER_SANITIZE_STRING),                        
                        'sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),
                        'value'=> (isset($_POST['value']) ? str_replace(',','.',filter_var($_POST['value'],FILTER_SANITIZE_STRING)) : ''),
                        );
                                                                              
      if(isset($_POST['is_default']))
      {
        db_query("update app_fields_choices set is_default = 0 where fields_id = '" . db_input(filter_var($_POST['fields_id'],FILTER_SANITIZE_STRING)). "'");
      }  
            
      if(isset($_GET['id']))
      {        
      	//paretn can't be the same as record id
      	if($_POST['parent_id']==$_GET['id'])
      	{
      		$sql_data['parent_id'] = 0;
      	}
      	
        db_perform('app_fields_choices',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        $choices_id = filter_var($_GET['id'],FILTER_SANITIZE_STRING);
      }
      else
      {               
        db_perform('app_fields_choices',$sql_data);
        $choices_id = db_insert_id();
      }
      
      //upload and prepare image map filename
      fieldtype_image_map::upload_map_filename($choices_id);
      
      redirect_to('entities/fields_choices','entities_id=' . filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING). '&fields_id=' . filter_var($_POST['fields_id'],FILTER_SANITIZE_STRING));      
    break;
  case 'delete':
      if(isset($_GET['id']))
      {      
        $msg = fields_choices::check_before_delete($_GET['id']);
        
        if(strlen($msg)>0)
        {
          $alerts->add($msg,'error');
        }
        else
        {
          $name = fields_choices::get_name_by_id(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          
          $tree = fields_choices::get_tree(filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING),filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          
          foreach($tree as $v)
          {
            db_delete_row('app_fields_choices',$v['id']);
          }
          
          db_delete_row('app_fields_choices',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          
          //delete choices filters
          $reports_info_query = db_query("select * from app_reports where reports_type='fields_choices" . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . "'");
          if($reports_info = db_fetch_array($reports_info_query))
          {
          	db_query("delete from app_reports_filters where reports_id='" . db_input(filter_var($reports_info['id'],FILTER_SANITIZE_STRING)) . "'");
          	db_query("delete from app_reports where id='" . db_input(filter_var($reports_info['id'],FILTER_SANITIZE_STRING)) . "'");
          }
          
          //delete map images
          fieldtype_image_map::delete_map_files(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          
          $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$name),'success');
        }
        
        redirect_to('entities/fields_choices','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&fields_id=' . filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING));  
      }
    break;
  case 'sort':
      $choices_sorted = filter_var($_POST['choices_sorted'],FILTER_SANITIZE_STRING);
      
      if(strlen($choices_sorted)>0)
      {      	      
        $choices_sorted = json_decode(stripslashes($choices_sorted),true);
        
        fields_choices::sort_tree(filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING),$choices_sorted);
      }
                       
      redirect_to('entities/fields_choices','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&fields_id=' . filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING));
    break;         
}

$field_info = db_find('app_fields',filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING));

$cfg = new fields_types_cfg($field_info['configuration']);

if($cfg->get('use_global_list')>0)
{
  redirect_to('global_lists/choices','lists_id=' . $cfg->get('use_global_list'));
}