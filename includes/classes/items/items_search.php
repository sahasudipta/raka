<?php

class items_search
{
  public $access_schema;
  
  public $search_fields;
  
  public $entities_id;
  
  public $parent_entities_id;
  
  public $search_keywords;
  
  public $path;
  
  function __construct($entities_id)
  {
    global $app_user;
    
    $this->entities_id = $entities_id;
    
    $entities_info = db_find('app_entities',$this->entities_id);
    
    $this->parent_entities_id = $entities_info['parent_id'];
    
    //get entity access schema
    $this->access_schema = users::get_entities_access_schema($this->entities_id, $app_user['group_id']);
    
    $this->search_fields = array();
    
    //set search by Name by default
    if($id = fields::get_heading_id($this->entities_id))
    {
      $this->search_fields[] = array('id'=>$id);
    }
    
    if($this->entities_id==1)
    {
      $this->search_fields[] = array('id'=>7);
      $this->search_fields[] = array('id'=>8);
      $this->search_fields[] = array('id'=>9);
    }
    
    $this->path = false;
  }
  
  function set_path($path)
  {
    $this->path = $path;
  }
  
  function set_search_keywords($keywords)
  {
    $this->search_keywords = $keywords;
  }
  
  function build_search_sql_query($search_operator = 'or')
  {
  	global $app_fields_cache;
  	
    $listing_sql_query = '';
    
    if(app_parse_search_string($this->search_keywords, $search_keywords, $search_operator))
    {
      //print_r($search_keywords);
      
      $sql_query = array();
      
      /**
       *  search in fields
       */         
      foreach(filter_var_array($this->search_fields) as $field)
      {        
        if (isset($search_keywords) && (sizeof($search_keywords) > 0)) 
        {
          $where_str = "(";
          for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) 
          {
            switch ($search_keywords[$i]) 
            {
              case '(':
              case ')':
              case 'and':
              case 'or':
                $where_str .= " " . $search_keywords[$i] . " ";
                break;
              default:
                $keyword = $search_keywords[$i];
                
                switch($app_fields_cache[filter_var($this->entities_id,FILTER_SANITIZE_STRING)][$field['id']]['type'])
                {
                	case 'fieldtype_id':
                		$where_str .= "e.id like '%" . db_input(filter_var($keyword,FILTER_SANITIZE_STRING)) . "%'";
                		break;
                	default:
                		$where_str .= "e.field_" . filter_var($field['id'],FILTER_SANITIZE_STRING) . " like '%" . db_input(filter_var($keyword,FILTER_SANITIZE_STRING)) . "%'";
                		break;
                }
                
                break;
            }
          }
          $where_str .= ")";
          
          $sql_query[] = $where_str;
        }
      }
      
      /**
       *  add search by record ID if vlaue is numeric
       */        
      if(count($search_keywords)==1 and is_numeric($search_keywords[0]))
      {
        $sql_query[] = "e.id='" . db_input(filter_var($search_keywords[0],FILTER_SANITIZE_STRING)) . "'";
      }
      
    
      if(count($sql_query)>0)
      {                  
        //print_r($sql_query);
        
        $listing_sql_query .= ' and (' . implode(' or ',$sql_query) . ')';
      }        
    }
        
    //check parent item
    if($this->path and $this->parent_entities_id>0)
    {
      $path_array = items::parse_path($this->path);
                   
      if($this->parent_entities_id==$path_array['parent_entity_id'])
      {
        $listing_sql_query .= " and e.parent_item_id='" . db_input(filter_var($path_array['parent_entity_item_id'],FILTER_SANITIZE_STRING)) . "'";                
      }      
    }
    
    return $listing_sql_query;
  }
  
  function get_choices()
  {  	  
    $choices = array();
    
    //add search sql query
    $listing_sql_query = $this->build_search_sql_query();        
    
    //check view assigned only access
    $listing_sql_query = items::add_access_query(filter_var($this->entities_id,FILTER_SANITIZE_STRING),$listing_sql_query);
  
    //include access to parent records
    $listing_sql_query .= items::add_access_query_for_parent_entities(filter_var($this->entities_id,FILTER_SANITIZE_STRING));
    
    $listing_sql_query .= items::add_listing_order_query_by_entity_id(filter_var($this->entities_id,FILTER_SANITIZE_STRING));
    
    $items_sql_query = "select e.* from app_entity_" . filter_var($this->entities_id,FILTER_SANITIZE_STRING) . " e where e.id>0 " . $listing_sql_query; 
    $items_query = db_query($items_sql_query);
    
    while($items = db_fetch_array($items_query))
    {       
      //add paretn item name if exist
      $parent_name = '';
      
      if($this->path and $this->parent_entities_id>0)
      {
        $path_array = items::parse_path($this->path);
                   
        if($this->parent_entities_id!=$path_array['parent_entity_id'] and filter_var($items['parent_item_id'],FILTER_SANITIZE_STRING)>0)
        {                    
          $parent_name = items::get_heading_field(filter_var($this->parent_entities_id,FILTER_SANITIZE_STRING),filter_var($items['parent_item_id'],FILTER_SANITIZE_STRING)) . ' > ';          
        }
      } 
            
      $name = items::get_heading_field(filter_var($this->entities_id,FILTER_SANITIZE_STRING),filter_var($items['id'],FILTER_SANITIZE_STRING));
          
      $choices[filter_var($items['id'],FILTER_SANITIZE_STRING)] = $parent_name . $name;
                  
    } 
    
    return $choices; 
  
  }
  
  
}