<?php

$fields_info_query = db_query("select * from app_fields where id='" . db_input(filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING)) . "'");
if(!$fields_info = db_fetch_array($fields_info_query))
{
  redirect_to('entities/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
}

switch($app_module_action)
{
  case 'save':
      $fields_configuration = filter_var($_POST['fields_configuration'],FILTER_SANITIZE_STRING);
      
      switch($fields_info['type'])
      {
        case 'fieldtype_related_records':
            if(isset($_POST['fields_in_listing']))
            {
              $fields_configuration['fields_in_listing'] = implode(',',filter_var_array($_POST['fields_in_listing']));
            }
            else
            {
              $fields_configuration['fields_in_listing'] = '';
            } 
            
            if(isset($_POST['fields_in_popup']))
            {
              $fields_configuration['fields_in_popup'] = implode(',',filter_var_array($_POST['fields_in_popup']));
            }
            else
            {
              $fields_configuration['fields_in_popup'] = '';
            }  
            
            $fields_configuration['create_related_comment'] = filter_var($_POST['create_related_comment'],FILTER_SANITIZE_STRING);
            $fields_configuration['create_related_comment_text'] = filter_var($_POST['create_related_comment_text'],FILTER_SANITIZE_STRING);
            $fields_configuration['delete_related_comment'] = filter_var($_POST['delete_related_comment'],FILTER_SANITIZE_STRING);
            $fields_configuration['delete_related_comment_text'] = filter_var($_POST['delete_related_comment_text'],FILTER_SANITIZE_STRING);
            $fields_configuration['create_related_comment_to'] = filter_var($_POST['create_related_comment_to'],FILTER_SANITIZE_STRING);
            $fields_configuration['create_related_comment_to_text'] = filter_var($_POST['create_related_comment_to_text'],FILTER_SANITIZE_STRING);
            $fields_configuration['delete_related_comment_to'] = filter_var($_POST['delete_related_comment_to'],FILTER_SANITIZE_STRING);
            $fields_configuration['delete_related_comment_to_text'] = filter_var($_POST['delete_related_comment_to_text'],FILTER_SANITIZE_STRING);
          break;
          
        case 'fieldtype_entity':
            
            if(isset($_POST['fields_in_popup']))
            {
              $fields_configuration['fields_in_popup'] = implode(',',filter_var_array($_POST['fields_in_popup']));
            }
            else
            {
              $fields_configuration['fields_in_popup'] = '';
            }  
          break;
      }
            
      db_query("update app_fields set configuration='" . db_input(fields_types::prepare_configuration($fields_configuration)) . "' where id='" . db_input($fields_info['id']) . "'");
      
      $alerts->add(TEXT_CONFIGURATION_UPDATED,'success');
      
      redirect_to('entities/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
    break;
}