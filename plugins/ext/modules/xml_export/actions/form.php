<?php
$obj = array();

if(isset($_GET['id']))
{
	$obj = db_find('app_ext_xml_export_templates',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
}
else
{
	$obj = db_show_columns('app_ext_xml_export_templates');

	if($xml_templates_filter>0)
	{
	    $obj['entities_id'] = $xml_templates_filter;
	}

	$obj['is_active'] = 1;
	$obj['is_public'] = 0;
	
}