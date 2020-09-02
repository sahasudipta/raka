<?php

$reports_query = db_query("select id, entities_id from app_reports where id='" . _get::int('reports_id') . "' and reports_type='common_filters'");
if($report_info = db_fetch_array($reports_query))
{
	$listing_sql_query_select = '';
	$listing_sql_query = '';
	$listing_sql_query_join = '';
	$listing_sql_query_having = '';
	$sql_query_having = array();
	
	//prepare formulas query
	$listing_sql_query_select = fieldtype_formula::prepare_query_select(filter_var($report_info['entities_id'],FILTER_SANITIZE_STRING), $listing_sql_query_select,false,array('reports_id'=>filter_var($report_info['id'],FILTER_SANITIZE_STRING)));
		
	//prepare listing query
	$listing_sql_query = reports::add_filters_query(filter_var($report_info['id'],FILTER_SANITIZE_STRING),$listing_sql_query);
	
	//prepare having query for formula fields
	if(isset($sql_query_having[$report_info['entities_id']]))
	{
		$listing_sql_query_having  = reports::prepare_filters_having_query($sql_query_having[filter_var($report_info['entities_id'],FILTER_SANITIZE_STRING)]);
	}
	
	$parent_item_id = filter_var(_get::int('parent_item_id'),FILTER_SANITIZE_STRING);
	
	if($parent_item_id>0)
	{
		$listing_sql_query .= ' and e.parent_item_id=' . db_input(filter_var($parent_item_id,FILTER_SANITIZE_STRING));
	}
	
	//check view assigned only access
	$listing_sql_query = items::add_access_query(filter_var($report_info['entities_id'],FILTER_SANITIZE_STRING),$listing_sql_query);
	
	//add having query
	$listing_sql_query .= $listing_sql_query_having;
		
	$listing_sql = "select e.* " . $listing_sql_query_select . " from app_entity_" . filter_var($report_info['entities_id'],FILTER_SANITIZE_STRING) . " e "  . $listing_sql_query_join . " where e.id>0 " . $listing_sql_query . " ";
	$items_query = db_query($listing_sql,false);
	$items_count = db_num_rows($items_query);
	
	if($items_count>0)
	{
		echo ' (' . $items_count . ')';
	}
}

exit();