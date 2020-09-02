<?php

$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_listing_sections',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
}
else
{
	$obj = db_show_columns('app_listing_sections');
	$obj['sort_order'] = listing_types::get_sections_next_order(filter_var($_GET['listing_types_id'],FILTER_SANITIZE_STRING));
}