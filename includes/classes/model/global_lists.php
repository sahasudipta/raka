<?php

class global_lists
{
  static function check_before_delete($id)
  {
    return '';
  }
  
  public static function check_before_delete_choices($id)
  {     
    return '';
  }
  
  static function get_lists_choices($add_empty=true)
  {
    $choices = array();
    
    if($add_empty)
    {
      $choices[''] = '';
    }
    
    $groups_query = db_fetch_all('app_global_lists','','name');
    while($v = db_fetch_array($groups_query))
    {
      $choices[$v['id']] = $v['name'];
    }   
    
    return $choices; 
  }
  
  static function get_name_by_id($id)
  {
    $item = db_find('app_global_lists',$id);
    
    return $item['name'];  
  }
  
  static function get_choices_name_by_id($id)
  {
    $item = db_find('app_global_lists_choices',$id);
    
    return $item['name'];  
  }
  
  public static function get_choices_default_id($lists_id)
  {
    $obj_query = db_query("select * from app_global_lists_choices where lists_id = '" . db_input($lists_id). "' and is_default=1 limit 1");
    
    if($obj = db_fetch_array($obj_query))
    {
      return $obj['id'];
    }
    else
    {
      return 0;
    } 
        
  } 
    
  static function get_choices_tree($lists_id,$parent_id = 0,$tree = array(),$level=0,$selected_values = '', $check_status = false)
  {
  	$where_sql = '';
  	 
  	if($check_status)
  	{
  		$where_sql = " and (is_active=1 " . (strlen($selected_values) ? " or id in (" . implode(',',array_map(function($v){return (int)$v; },explode(',',filter_var($selected_values,FILTER_SANITIZE_STRING)))) . ")":'') . ") ";
  	}
  	
    $choices_query = db_query("select * from app_global_lists_choices where lists_id = '" . db_input(filter_var($lists_id,FILTER_SANITIZE_STRING)). "' and parent_id='" . db_input(filter_var($parent_id,FILTER_SANITIZE_STRING)). "' {$where_sql} order by sort_order, name");
    
    while($v = db_fetch_array($choices_query))
    {
      $tree[] = array_merge($v,array('level'=>$level));
      
      $tree = self::get_choices_tree($lists_id,$v['id'],$tree,$level+1,$selected_values,$check_status);
    }
    
    return $tree;
  }
  
  public static function get_js_level_tree($lists_id,$parent_id = 0,$tree = array(),$level=0,$selected_values = '')
  {
  	$choices_query = db_query("select * from app_global_lists_choices where lists_id = '" . db_input($lists_id). "' and parent_id='" . db_input($parent_id). "' and (is_active=1 " . (strlen($selected_values) ? " or id in (" . implode(',',array_map(function($v){return (int)$v; },explode(',',$selected_values))) . ")":'') . ") order by sort_order, name");
  	 
  
  	while($v = db_fetch_array($choices_query))
  	{
  		if($parent_id>0)
  		{
  			$tree[$parent_id][] = '
  					$(update_field).append($("<option>", {value: ' . $v['id'] . ',text: "' . addslashes(strip_tags($v['name'])). '"}));';
  		}
  
  		$tree = self::get_js_level_tree($lists_id,$v['id'],$tree,$level+1,$selected_values);
  	}
  		
  	return $tree;
  }  
  
  static function get_choices_html_tree($lists_id,$parent_id = 0,$tree = '')
  {
    $count_query = db_query("select count(*) as total from app_global_lists_choices where lists_id = '" . db_input($lists_id). "' and parent_id='" . db_input($parent_id). "' order by sort_order, name");
    $count = db_fetch_array($count_query);
    
    if($count['total']>0)
    {
      $tree .= '<ol class="dd-list">';
      
      $choices_query = db_query("select * from app_global_lists_choices where lists_id = '" . db_input($lists_id). "' and parent_id='" . db_input($parent_id). "' order by sort_order, name");
      
      while($v = db_fetch_array($choices_query))
      {        
        $tree .= '<li class="dd-item" data-id="' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '"><div class="dd-handle">' . filter_var($v['name'],FILTER_SANITIZE_STRING) . '</div>'; 
        
        $tree = self::get_choices_html_tree($lists_id,filter_var($v['id'],FILTER_SANITIZE_STRING),$tree);
        
        $tree .= '</li>'; 
      }
      
      $tree .= '</ol>';
    }
    
    return $tree;
  }
  
  public static function get_choices($lists_id,$add_empty = true, $empty_text = '', $selected_values = '', $check_status = false)
  {
    $choices = array();
    
    $tree = self::get_choices_tree($lists_id,0,[],0,$selected_values,$check_status);
            
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
  
  static function choices_sort_tree($lists_id,$tree,$parent_id=0)
  {
    $sort_order = 0;
    foreach($tree as $v)
    {
      db_query("update app_global_lists_choices set parent_id='" . $parent_id . "', sort_order='" . $sort_order . "' where id='" . db_input($v['id']) . "' and lists_id='" . db_input($lists_id) . "'");
      
      if(isset($v['children']))
      {
        self::choices_sort_tree($lists_id,$v['children'],$v['id']);
      }
        
      $sort_order++;
    }
  }
  
  public static function get_cache()
  {
    $list = array();
    
    $choices_query = db_query("select * from app_global_lists_choices");
    
    while($v = db_fetch_array($choices_query))
    {
      $list[$v['id']] = $v;
    }
    
    return $list;
  }
  
  public static function render_value($values = array(), $is_export=false)
  {
    global $app_global_choices_cache;
    
    if(!is_array($values))
    {
      $values = explode(',',$values);
    }
    
    $html  = '';
    foreach($values as $id)
    {
      if(isset($app_global_choices_cache[$id]))
      {
        if($is_export)
        {
          $html .= (strlen($html)==0 ? $app_global_choices_cache[$id]['name'] : ', ' . $app_global_choices_cache[$id]['name']);
        }
        elseif(strlen($app_global_choices_cache[$id]['bg_color'])>0)
        {
          $html .= render_bg_color_block($app_global_choices_cache[$id]['bg_color'],$app_global_choices_cache[$id]['name']);
        }
        else
        {
          $html .= '<div>' . $app_global_choices_cache[$id]['name'] . '</div>';
        } 
      }
    }
    
    return $html;
  }  
  
  public static function get_paretn_ids($id,$parents=array())
  {
  	$choices_query = db_query("select * from app_global_lists_choices where id = '" . db_input(filter_var($id,FILTER_SANITIZE_STRING)). "' order by sort_order, name");
  	 
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