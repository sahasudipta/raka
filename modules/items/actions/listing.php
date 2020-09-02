<?php

$reports_info = db_find('app_reports',filter_var($_POST['reports_id'],FILTER_SANITIZE_STRING));

//print_rr($reports_info);

$fields_access_schema = users::get_fields_access_schema(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($app_user['group_id'],FILTER_SANITIZE_STRING));
$current_entity_info = db_find('app_entities',filter_var($current_entity_id,FILTER_SANITIZE_STRING));
$entity_cfg = new entities_cfg(filter_var($current_entity_id,FILTER_SANITIZE_STRING));

$listing = new items_listing(filter_var($_POST['reports_id'],FILTER_SANITIZE_STRING),$entity_cfg);

$user_has_comments_access = users::has_comments_access('view');
      
$html = '';

$listing_sql_query_select = '';
$listing_sql_query = '';
$listing_sql_query_join = '';
$listing_sql_query_from = '';
$listing_sql_query_having = '';
$sql_query_having = array();

if(!isset($_POST['search_keywords'])) $_POST['search_keywords'] = '';
if(!isset($_POST['search_reset'])) $_POST['search_reset'] = '';
if(!isset($_POST['force_display_id'])) $_POST['force_display_id'] = '';
if(!isset($_POST['force_popoup_fields'])) $_POST['force_popoup_fields'] = '';
if(!isset($_POST['force_filter_by'])) $_POST['force_filter_by'] = '';

//prepare forumulas query
$listing_sql_query_select = fieldtype_formula::prepare_query_select(filter_var($current_entity_id,FILTER_SANITIZE_STRING), $listing_sql_query_select,false,array('reports_id'=>filter_var($_POST['reports_id'],FILTER_SANITIZE_STRING)));

//prepare count of related items in listing
$listing_sql_query_select = fieldtype_related_records::prepare_query_select(filter_var($current_entity_id,FILTER_SANITIZE_STRING), $listing_sql_query_select, filter_var_array($reports_info));


//add search query and skip filters to search in all items
if(strlen(filter_var($_POST['search_keywords'],FILTER_SANITIZE_STRING))>0)
{
  $html .= '<div class="note note-info search-notes">' . sprintf(TEXT_SEARCH_RESULT_FOR,htmlspecialchars($_POST['search_keywords'])) . ' <span onClick="listing_reset_search(\'' . filter_var($_POST['listing_container'],FILTER_SANITIZE_STRING) . '\')" class="reset_search">' . TEXT_RESET_SEARCH . '</span></div>';
  require(component_path('items/add_search_query')); 
}

if(strlen(filter_var($_POST['search_keywords'],FILTER_SANITIZE_STRING))>0 or filter_var($_POST['search_reset'],FILTER_SANITIZE_STRING)=='true')
{
	//save search settings for current report
	listing_search::save(filter_var($_POST['reports_id'],FILTER_SANITIZE_STRING));
}

//default search include reports fitlers
//if flga "search_in_all" = true we exlude fitlers from search
if((strlen(filter_var($_POST['search_keywords'],FILTER_SANITIZE_STRING))>0 and filter_var($_POST['search_in_all'],FILTER_SANITIZE_STRING)=='true') or strlen(filter_var($_POST['force_display_id'],FILTER_SANITIZE_STRING)))
{
	//skip filters if there is search keyworkds and option search_in_all in 
}
else
{
  //add filters query
  if(isset($_POST['reports_id']))
  {  	
    $listing_sql_query = reports::add_filters_query(filter_var($_POST['reports_id'],FILTER_SANITIZE_STRING),$listing_sql_query,'e');
        
    //prepare having query for formula fields
    if(isset($sql_query_having[filter_var($current_entity_id,FILTER_SANITIZE_STRING)]))
    {    	
    	$listing_sql_query_having  = reports::prepare_filters_having_query($sql_query_having[filter_var($current_entity_id,FILTER_SANITIZE_STRING)]);
    }
  }
}


//filter items by parent
if(filter_var($parent_entity_item_id,FILTER_SANITIZE_STRING)>0)
{
  $listing_sql_query .= " and e.parent_item_id='" . db_input(filter_var($parent_entity_item_id,FILTER_SANITIZE_STRING)) . "'";
}

//exclude admin users from listing for not admin users
if($current_entity_id==1 and filter_var($app_user['group_id'],FILTER_SANITIZE_STRING)>0)
{
	$listing_sql_query .= " and e.field_6>0";
}

//force display items by ID
if(strlen(filter_var($_POST['force_display_id'],FILTER_SANITIZE_STRING)))
{
	$listing_sql_query .= " and e.id in (" . filter_var($_POST['force_display_id'],FILTER_SANITIZE_STRING) . ")";
}

//force extra filter
if(strlen(filter_var($_POST['force_filter_by'],FILTER_SANITIZE_STRING)))
{
	$listing_sql_query .= reports::force_filter_by(filter_var($_POST['force_filter_by'],FILTER_SANITIZE_STRING));
}	

//check view assigned only access
$listing_sql_query = items::add_access_query(filter_var($current_entity_id,FILTER_SANITIZE_STRING),$listing_sql_query,$listing->force_access_query);

//add having query
$listing_sql_query .= $listing_sql_query_having;

//add order_query
$listing_order_fields_id = array();
$listing_order_fields = array();
$listing_order_clauses = array();

if(strlen(filter_var($_POST['listing_order_fields'],FILTER_SANITIZE_STRING))>0)
{  
  $info = reports::add_order_query(filter_var($_POST['listing_order_fields'],FILTER_SANITIZE_STRING),filter_var($current_entity_id,FILTER_SANITIZE_STRING));
      
  $listing_order_fields_id = filter_var($info['listing_order_fields_id'],FILTER_SANITIZE_STRING);
  $listing_order_fields = filter_var($info['listing_order_fields'],FILTER_SANITIZE_STRING);
  $listing_order_clauses = filter_var($info['listing_order_clauses'],FILTER_SANITIZE_STRING);
  
  $listing_sql_query .= filter_var($info['listing_sql_query'],FILTER_SANITIZE_STRING);
  $listing_sql_query_join .= filter_var($info['listing_sql_query_join'],FILTER_SANITIZE_STRING);  
  $listing_sql_query_from .= filter_var($info['listing_sql_query_from'],FILTER_SANITIZE_STRING);
    
  if(isset($_POST['listing_order_fields_changed']))
  if(filter_var($_POST['listing_order_fields_changed'],FILTER_SANITIZE_STRING)==1 and filter_var($reports_info['reports_type'],FILTER_SANITIZE_STRING)!='default')
  {
  	db_query("update app_reports set listing_order_fields = '" . db_input(filter_var($_POST['listing_order_fields'],FILTER_SANITIZE_STRING)) . "' where id='" . db_input(filter_var($_POST['reports_id'],FILTER_SANITIZE_STRING )). "'");  	
  }
}

$reports_entities_id = (isset($_POST['reports_entities_id']) ? filter_var($_POST['reports_entities_id'],FILTER_SANITIZE_STRING) : 0);

$has_with_selected = (isset($_POST['has_with_selected']) ? filter_var($_POST['has_with_selected'],FILTER_SANITIZE_STRING) : 0);

if(!isset($app_selected_items[filter_var($_POST['reports_id'],FILTER_SANITIZE_STRING)]))
{
	$app_selected_items[filter_var($_POST['reports_id'],FILTER_SANITIZE_STRING)] = array();
}

//setup unread items
$users_notifications = new users_notifications(filter_var($current_entity_id,FILTER_SANITIZE_STRING));

//render listing body
$listing_sql = "select e.* " . $listing_sql_query_select . " from app_entity_" . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . " e "  . $listing_sql_query_join . $listing_sql_query_from . " where e.id>0 " . $listing_sql_query;

//if there is having query then use db_num_rows function to calculate num rows
if(strlen($listing_sql_query_having)>0)
{
	$count_sql = 'query_num_rows';
}
else
{
	$count_sql = "select count(e.id) as total from app_entity_" . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . " e "  . $listing_sql_query_join . " where e.id>0 " . $listing_sql_query;
}

//$count_sql = 'query_num_rows';

$listing_split = new split_page($listing_sql,filter_var($_POST['listing_container'],FILTER_SANITIZE_STRING),$count_sql, filter_var($listing->rows_per_page,FILTER_SANITIZE_STRING));

$items_query = db_query($listing_split->sql_query,false);

//listing highlight rules
$listing_highlight = new listing_highlight(filter_var($current_entity_id,FILTER_SANITIZE_STRING));
echo $listing_highlight->render_css();

switch($listing->get_listing_type())
{
	case 'list':
		require(component_path('items/_listing_list'));
		break;
	case 'grid':
		require(component_path('items/_listing_grid'));
		break;
	case 'mobile':
		require(component_path('items/_listing_mobile'));
		break;
	case 'table':
		require(component_path('items/_listing_table'));
		break;
}


