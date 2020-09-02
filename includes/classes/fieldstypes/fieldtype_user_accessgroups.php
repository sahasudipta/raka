<?php

class fieldtype_user_accessgroups
{
  public $options;
  
  function __construct()
  {
    $this->options = array('name' => TEXT_FIELDTYPE_USER_ACCESSGROUP_TITLE,'title' => TEXT_FIELDTYPE_USER_ACCESSGROUP_TITLE);
  }
  
  function render($field,$obj,$params = array())
  {     
  	global $app_user, $app_module_path;
  	
    if(($default_group_id = access_groups::get_default_group_id())>0 and strlen($obj['field_' . $field['id']])==0)
    {
      $value = $default_group_id;
    }
    else
    {
      $value = $obj['field_' . $field['id']];
    }
    
    if($app_module_path=='users/registration')
    {
    	$choices = array();
    	$choices[''] = TEXT_SELECT_SOME_VALUES;
    	$groups_query = db_fetch_all('app_access_groups',(strlen(CFG_PUBLIC_REGISTRATION_USER_GROUP) ? 'id in (' . CFG_PUBLIC_REGISTRATION_USER_GROUP . ')' : ''),'sort_order, name');
    	while($v = db_fetch_array($groups_query))
    	{
    		$choices[$v['id']] = $v['name'];
    	}
    }
    else
    {
                        
        if(!$choices = self::get_choices_by_rules())
        {
            $include_administrator = ($app_user['group_id']>0 ? false : true);
            $choices = access_groups::get_choices($include_administrator);
        }
    }
            
    if($obj['id']==$app_user['id'] and $obj['id']>0)
    {
        return '<p class="form-control-static">' . access_groups::get_name_by_id($app_user['group_id']) . '</p>' . input_hidden_tag('fields[' . $field['id'] . ']',$value);
    }
    
    return select_tag('fields[' . $field['id'] . ']',$choices,$value,array('class'=>'form-control input-medium required field_' . $field['id']));
  }
  
  function process($options)
  {
    return $options['value'];
  }
  
  function output($options)
  {
    return access_groups::get_name_by_id($options['value']);
  }
  
  function reports_query($options)
  {
  	$filters = $options['filters'];
  	$sql_query = $options['sql_query'];
  
  	$sql = array();
  
  	if(strlen($filters['filters_values'])>0)
  	{
  		$sql_query[] = "(e.field_6 " . ($filters['filters_condition']=='include' ? 'in' : 'not in') . " (" . $filters['filters_values'] . "))";
  	}
  
  	return $sql_query;
  }  
  
  static function get_choices_by_rules()
  {
      global $app_user;
      
      if($app_user['group_id']==0) return false;
      
      $rules_query = db_query("select * from app_records_visibility_rules where entities_id='1' and find_in_set(" . $app_user['group_id'] . ",users_groups)");
      if($rules = db_fetch_array($rules_query))
      {
          $reports_query = db_query("select * from app_reports where entities_id=1 and reports_type='records_visibility" . db_input(filter_var($rules['id'],FILTER_SANITIZE_STRING)). "'");                    
          if($reports_query = db_fetch_array($reports_query))
          {                  
              $filters_query = db_query("select rf.*, f.name, f.type from app_reports_filters rf left join app_fields f on rf.fields_id=f.id where rf.fields_id=6 and rf.reports_id='" . db_input(filter_var($reports_query['id'],FILTER_SANITIZE_STRING)) . "' and length(filters_values)>0 order by rf.id");
              if(db_num_rows($filters_query))
              {
                  $include = [];
                  $exclude = [];
                  while($filters = db_fetch_array($filters_query))
                  {
                      if($filters['filters_condition']=='include')
                      {
                          $include = array_merge($include,explode(',',filter_var($filters['filters_values'],FILTER_SANITIZE_STRING)));
                      }
                      else
                      {
                          $exclude = array_merge($exclude,explode(',',filter_var($filters['filters_values'],FILTER_SANITIZE_STRING)));
                      }                      
                  }
                  
                  $choices = [];
                  $choices[''] = TEXT_SELECT_SOME_VALUES;
                  $groups_query = db_query("select id,name from app_access_groups where id>0 " . (count($include) ? " and id in (" . implode(',',$include). ")":"") . (count($exclude) ? " and id not in (" . implode(',',$exclude). ")":""). " order by sort_order, name",false);
                  while($groups = db_fetch_array($groups_query))
                  {
                      $choices[$groups['id']] = $groups['name'];
                  }
                  
                  //print_rr($choices);
                  
                  return $choices;
              }
          }
          
      }
      
      return false;
      
  }
}