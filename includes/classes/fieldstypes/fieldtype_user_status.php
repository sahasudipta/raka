<?php

class fieldtype_user_status
{
  public $options;
  
  function __construct()
  {
    $this->options = array('name' => TEXT_FIELDTYPE_USER_STATUS_TITLE,'title' => TEXT_FIELDTYPE_USER_STATUS_TITLE);
  }
  
  function render($field,$obj,$params = array())
  {
      global $app_user;
      
    $value = $obj['field_' . $field['id']];
    if(strlen($value)==0) $value = 1;
    
    if($obj['id']==$app_user['id'] and $obj['id']>0)
    {
        return '<p class="form-control-static">' . TEXT_ACTIVE . '</p>' . input_hidden_tag('fields[' . $field['id'] . ']',$value);
    }
    
    return select_tag('fields[' . $field['id'] . ']',array('1'=>TEXT_ACTIVE,'0'=>TEXT_INACTIVE),$value,array('class'=>'form-control input-medium')) . tooltip_text(TEXT_FIELDTYPE_USER_STATUS_TOOLTIP);
  }
  
  function process($options)
  {
    return $options['value'];
  }
  
  function output($options)
  {
    return ($options['value']==1 ? '<span class="label label-success">' . TEXT_ACTIVE . '</span>' : '<span class="label label-default">' . TEXT_INACTIVE . '</span>');
  }
  
  function reports_query($options)
  {
  	$filters = $options['filters'];
  	$sql_query = $options['sql_query'];
  
  	$sql = array();
  
  	if(strlen($filters['filters_values'])>0)
  	{
  		$sql_query[] = "(e.field_5 " . ($filters['filters_condition']=='include' ? 'in' : 'not in') . " (" . $filters['filters_values'] . "))";
  	}
  
  	return $sql_query;
  }
}