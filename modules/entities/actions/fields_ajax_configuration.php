<?php

if(isset($_POST['field_type']))
{
  $field_type = new $_POST['field_type'];
    
  if(method_exists($field_type,'get_ajax_configuration'))
  {  
    echo fields_types::render_configuration($field_type->get_ajax_configuration(filter_var($_POST['name'],FILTER_SANITIZE_STRING),filter_var($_POST['value'],FILTER_SANITIZE_STRING)),filter_var($_POST['id'],FILTER_SANITIZE_STRING));    
  }
  
}

exit();