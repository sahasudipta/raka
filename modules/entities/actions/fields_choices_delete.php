<?php
    
  $msg = fields_choices::check_before_delete($_GET['id']);
        
  if(strlen($msg)>0)
  {
    $heading = TEXT_WARNING;
    $content = $msg;
    $button_title = false;
  }
  else
  {
    $heading = TEXT_HEADING_DELETE; 
    $content =  sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION,fields_choices::get_name_by_id(filter_var($_GET['id'],FILTER_SANITIZE_STRING)));
    $button_title = TEXT_BUTTON_DELETE;
  }