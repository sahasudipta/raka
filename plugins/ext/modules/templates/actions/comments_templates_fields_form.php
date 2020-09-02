<?php

$template_info_query = db_query("select ep.*, e.name as entities_name from app_ext_comments_templates ep, app_entities e where e.id=ep.entities_id and ep.id='" . db_input(filter_var($_GET['templates_id'],FILTER_SANITIZE_STRING)) . "' order by e.id, ep.sort_order, ep.name");
if(!$template_info = db_fetch_array($template_info_query))
{  
  redirect_to('ext/templates/comments_templates');
}

$obj = array();

if(isset($_GET['id']))
{
  $obj = db_find('app_ext_comments_templates_fields',filter_var($_GET['id'],FILTER_SANITIZE_STRING));      
}
else
{
  $obj = db_show_columns('app_ext_comments_templates_fields');
}
