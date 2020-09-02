<?php

class fieldtype_user_email
{
  public $options;
  
  function __construct()
  {
    $this->options = array('name' => TEXT_FIELDTYPE_USER_EMAIL_TITLE,'title' => TEXT_FIELDTYPE_USER_EMAIL_TITLE);
  }
  
  function render($field,$obj,$params = array())
  {
    return input_tag('fields[' . $field['id'] . ']',$obj['field_' . $field['id']],array('class'=>'form-control input-medium required email'));
  }
  
  function process($options)
  {               
    return db_prepare_input($options['value']);
  }
  
  function output($options)
  {
      
      if(isset($options['is_export']))
      {
          return $options['value'];
      }
      elseif(CFG_PUBLIC_REGISTRATION_USER_ACTIVATION=='email' and CFG_USE_PUBLIC_REGISTRATION==1 and $options['item']['is_email_verified']==0)
      {
          return '<strike title="' . addslashes(TEXT_EMAIL_NOT_VERIFIED) . '">' . $options['value'] . '</strike>';
      }
      else
      {
          return $options['value'];
      }
    
  }
}