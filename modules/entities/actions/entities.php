<?php

if(isset($_POST['switch_to_entities_id']))
{
  redirect_to('entities/entities_configuration&entities_id=' . filter_var($_POST['switch_to_entities_id'],FILTER_SANITIZE_STRING));
}

switch($app_module_action)
{
  case 'save':
      $sql_data = array('name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
                        'display_in_menu' => filter_var($_POST['display_in_menu'],FILTER_SANITIZE_STRING),
                        'notes' => strip_tags(filter_var($_POST['notes'],FILTER_SANITIZE_STRING)),
                        'sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING));
      
      if(isset($_GET['id']))
      {        
        db_perform('app_entities',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
      }
      else
      {
        if(isset($_POST['parent_id']))
        {
          $sql_data['parent_id'] = $_POST['parent_id'];
        } 
               
        db_perform('app_entities',$sql_data);
        $id = db_insert_id();
        
        entities::prepare_tables($id);
        
        $forms_tab_id = entities::insert_default_form_tab($id);
        
        entities::insert_reserved_fields($id,$forms_tab_id);
      }
      
      redirect_to('entities/');      
    break;
  case 'delete':
      if(isset($_GET['id']))
      {      
        $msg = entities::check_before_delete(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
        
        if(strlen($msg)>0)
        {
          $alerts->add($msg,'error');
        }
        else
        {
          $name = entities::get_name_by_id($_GET['id']);
          
          related_records::delete_entities_related_items_table($_GET['id']);
          
          entities::delete($_GET['id']);
          
          entities::delete_tables($_GET['id']);
                                       
          $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$name),'success');
        }
        
        redirect_to('entities/');  
      }
    break;
}


$entities_list = entities::get_tree();



