<?php
    
  $msg = entities::check_before_delete(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
        
  if(strlen($msg)>0)
  {
    $heading = TEXT_WARNING;
    $content = $msg;
    $button_title = 'hide-save-button';
  }
  else
  {
    $heading = TEXT_HEADING_DELETE; 
    $content =  sprintf(TEXT_DEFAULT_DELETE_CONFIRMATION,entities::get_name_by_id($_GET['id']));
    $button_title = TEXT_BUTTON_DELETE;
  }