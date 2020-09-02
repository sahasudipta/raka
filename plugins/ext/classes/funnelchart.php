<?php

class funnelchart
{
	public static function get_choices_by_entity($entities_id, $add_empty = false)
	{
		$listing_sql_query = '';
		$listing_sql_query_join = '';
		
		//check view assigned only access
		$listing_sql_query = items::add_access_query(filter_var($entities_id,FILTER_SANITIZE_STRING),$listing_sql_query);
	
		//include access to parent records
		$listing_sql_query .= items::add_access_query_for_parent_entities(filter_var($entities_id,FILTER_SANITIZE_STRING));
	
		$listing_sql_query .= items::add_listing_order_query_by_entity_id(filter_var($entities_id,FILTER_SANITIZE_STRING));
	
		//build query
		$listing_sql = "select e.* from app_entity_" . filter_var($entities_id,FILTER_SANITIZE_STRING) . " e "  . $listing_sql_query_join . "where e.id>0 " . $listing_sql_query;
		$items_query = db_query($listing_sql);
	
		$choices = array();
	
		if($add_empty)
		{
			$choices[''] = '';
		}
	
		while($item = db_fetch_array($items_query))
		{
			$path_info = items::get_path_info($entities_id,filter_var($item['id'],FILTER_SANITIZE_STRING));
	
			//print_r($path_info);
	
			$parent_name = '';
			if(strlen($path_info['parent_name'])>0)
			{
				$parent_name = str_replace('<br>',' / ',$path_info['parent_name']) . ' / ';
			}
	
			$choices[filter_var($item['id'])] = $parent_name . items::get_heading_field($entities_id, filter_var($item['id'],FILTER_SANITIZE_STRING));
		}
	
		return $choices;
	}	
	
}