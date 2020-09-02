<?php

class fields_choices
{

  public static function check_before_delete($id)
  {     
    return '';
  }
  
  public static function get_name_by_id($id)
  {
    $obj = db_find('app_fields_choices',$id);
    
    return $obj['name'];
  }
  
  public static function get_default_id($fields_id)
  {
    $obj_query = db_query("select * from app_fields_choices where fields_id = '" . db_input($fields_id). "' and is_default=1 limit 1");
    
    if($obj = db_fetch_array($obj_query))
    {
      return $obj['id'];
    }
    else
    {
      return 0;
    } 
        
  }  
  
  public static function get_tree($fields_id, $parent_id = 0, $tree = array(), $level=0, $display_choices_values='',$selected_values = '', $check_status = false)
  {  	
  	$where_sql = '';
  	
  	if($check_status)
  	{
  		$where_sql = " and (is_active=1 " . (strlen(filter_var($selected_values,FILTER_SANITIZE_STRING)) ? " or id in (" . implode(',',array_map(function($v){return (int)$v; },explode(',',filter_var($selected_values,FILTER_SANITIZE_STRING)))) . ")":'') . ") ";
  	}
  	
    $choices_query = db_query("select * from app_fields_choices where fields_id = '" . db_input(filter_var($fields_id,FILTER_SANITIZE_STRING)). "' and parent_id='" . db_input(filter_var($parent_id,FILTER_SANITIZE_STRING)). "' {$where_sql} order by sort_order, name");
    
    while($v = db_fetch_array($choices_query))
    {
    	if($display_choices_values==1)
    	{
    		$v['name'] = filter_var($v['name'],FILTER_SANITIZE_STRING) . (strlen(filter_var($v['value'],FILTER_SANITIZE_STRING)) ? ' (' . (filter_var($v['value'],FILTER_SANITIZE_STRING)>=0 ? '+':'') . filter_var($v['value'],FILTER_SANITIZE_STRING) . ')' : '');
    	}
    	
      $tree[] = array_merge(filter_var_array($v),array('level'=>$level));
      
      $tree = fields_choices::get_tree(filter_var($fields_id,FILTER_SANITIZE_STRING),filter_var($v['id'],FILTER_SANITIZE_STRING),filter_var_array($tree),$level+1,$display_choices_values,$selected_values,$check_status);
    }
    
    return $tree;
  }
  
  public static function get_js_level_tree($fields_id,$parent_id = 0,$tree = array(),$level=0,$display_choices_values='',$selected_values = '')
  {
  	$choices_query = db_query("select * from app_fields_choices where fields_id = '" . db_input($fields_id). "' and parent_id='" . db_input($parent_id). "' and (is_active=1 " . (strlen($selected_values) ? " or id in (" . implode(',',array_map(function($v){return (int)$v; },explode(',',$selected_values))) . ")":'') . ") order by sort_order, name");
  	  	 
  	while($v = db_fetch_array($choices_query))
  	{  
  		if($parent_id>0)
  		{
  			if($display_choices_values==1)
  			{
  				$v['name'] = $v['name'] . (strlen($v['value']) ? ' (' . ($v['value']>=0 ? '+':'') . $v['value'] . ')' : '');  				
  			}
  			
  			$tree[$parent_id][] = '
  					$(update_field).append($("<option>", {value: ' . $v['id'] . ',text: "' . addslashes(strip_tags($v['name'])). '"}));';
  		}
  		
  		$tree = fields_choices::get_js_level_tree($fields_id,$v['id'],$tree,$level+1,$display_choices_values, $selected_values);
  	}
  	  
  	return $tree;
  }
  
  
  static function get_html_tree($fields_id,$parent_id = 0,$tree = '')
  {
    $count_query = db_query("select count(*) as total from app_fields_choices where fields_id = '" . db_input(filter_var($fields_id,FILTER_SANITIZE_STRING)). "' and parent_id='" . db_input(filter_var($parent_id,FILTER_SANITIZE_STRING)). "' order by sort_order, name");
    $count = db_fetch_array($count_query);
    
    if($count['total']>0)
    {
      $tree .= '<ol class="dd-list">';
      
      $choices_query = db_query("select * from app_fields_choices where fields_id = '" . db_input(filter_var($fields_id,FILTER_SANITIZE_STRING)). "' and parent_id='" . db_input(filter_var($parent_id,FILTER_SANITIZE_STRING)). "' order by sort_order, name");
      
      while($v = db_fetch_array($choices_query))
      {        
        $tree .= '<li class="dd-item" data-id="' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '"><div class="dd-handle">' . filter_var($v['name'],FILTER_SANITIZE_STRING) . '</div>'; 
        
        $tree = self::get_html_tree($fields_id,filter_var($v['id'],FILTER_SANITIZE_STRING),$tree);
        
        $tree .= '</li>'; 
      }
      
      $tree .= '</ol>';
    }
    
    return $tree;
  }
  
  static function sort_tree($fields_id,$tree,$parent_id=0)
  {
    $sort_order = 0;
    foreach($tree as $v)
    {
      db_query("update app_fields_choices set parent_id='" . filter_var($parent_id,FILTER_SANITIZE_STRING) . "', sort_order='" . filter_var($sort_order,FILTER_SANITIZE_STRING) . "' where id='" . db_input($v['id']) . "' and fields_id='" . db_input($fields_id) . "'");
      
      if(isset($v['children']))
      {
        self::sort_tree($fields_id,$v['children'],$v['id']);
      }
        
      $sort_order++;
    }
  }  
  
  public static function get_choices($fields_id,$add_empty = true, $empty_text = '',$display_choices_values='',$selected_values = '', $check_status = false)
  {
    $choices = array();
    
    $tree = fields_choices::get_tree($fields_id,0,array(),0,$display_choices_values,$selected_values,$check_status);
            
    if(count($tree)>0)
    {
      if($add_empty)
      {
        $choices[''] = $empty_text;
      }
      
      foreach($tree as $v)
      {
        $choices[$v['id']] = str_repeat(' - ',$v['level']) . $v['name'];
      }            
    }
    
    return $choices;        
  }
  
  public static function get_cache()
  {
    $list = array();
    
    $choices_query = db_query("select * from app_fields_choices");
    
    while($v = db_fetch_array($choices_query))
    {
      $list[$v['id']] = $v;
    }
    
    return $list;
  }
  
  public static function render_value($values = array(), $is_export=false)
  {
    global $app_choices_cache;
    
    if(!is_array($values))
    {
      $values = explode(',',$values);
    }
    
    $html  = '';
    foreach($values as $id)
    {
      if(isset($app_choices_cache[$id]))
      {
        if($is_export)
        {
          $html .= (strlen($html)==0 ? $app_choices_cache[$id]['name'] : ', ' . $app_choices_cache[$id]['name']);
        }
        elseif(strlen($app_choices_cache[$id]['bg_color'])>0)
        {
          $html .= render_bg_color_block($app_choices_cache[$id]['bg_color'],$app_choices_cache[$id]['name']);
        }
        else
        {
          $html .= '<div>' . $app_choices_cache[$id]['name'] . '</div>';
        } 
      }
    }
    
    return $html;
  }
  
  public static function get_paretn_ids($id,$parents=array())
  {
  	$choices_query = db_query("select * from app_fields_choices where id = '" . db_input(filter_var($id,FILTER_SANITIZE_STRING)). "' order by sort_order, name");
  	
  	while($v = db_fetch_array($choices_query))
  	{
  		$parents[] = $v['id'];
  	
  		if($v['parent_id']>0)
  		{
  			$parents = self::get_paretn_ids($v['parent_id'],$parents);
  		}
  	}
  	
  	return $parents;
  }
    
}