<?php
/**
 *add graphic reports to menu
 */
$reports_query = db_query("select * from app_ext_graphicreport order by name");
while($reports = db_fetch_array($reports_query))
{
	if(in_array($app_user['group_id'],explode(',',$reports['allowed_groups'])) or $app_user['group_id']==0)
	{
		$check_query = db_query("select id from app_entities_menu where find_in_set('graphicreport" . filter_var($reports['id'],FILTER_SANITIZE_STRING). "',reports_list)");
		if(!$check = db_fetch_array($check_query))
		{
			$app_plugin_menu['reports'][] = array('title'=>$reports['name'],'url'=>url_for('ext/graphicreport/view','id=' . $reports['id']));
		}
	}
}


/**
 *add chart reports to items menu
 */


if(isset($_GET['path']))
{       
	$entities_list = items::get_sub_entities_list_by_path(filter_var($_GET['path'],FILTER_SANITIZE_STRING));

	if(count($entities_list))
	{		
		$reports_query = db_query("select g.* from app_ext_graphicreport g, app_entities e  where e.id=g.entities_id and e.id in (" . implode(',',filter_var_array($entities_list)) . ") " . (filter_var($app_user['group_id'],FILTER_SANITIZE_STRING)>0 ? " and find_in_set(" . filter_var($app_user['group_id'],FILTER_SANITIZE_STRING) . ",g.allowed_groups)":""). " order by g.name");

		while($reports = db_fetch_array($reports_query))
		{
			$path = app_get_path_to_report(filter_var($reports['entities_id'],FILTER_SANITIZE_STRING));

			$app_plugin_menu['items_menu_reports'][] = array('title'=>filter_var($reports['name'],FILTER_SANITIZE_STRING),'url'=>url_for('ext/graphicreport/view','id=' . filter_var($reports['id'],FILTER_SANITIZE_STRING) . '&path=' . filter_var($path,FILTER_SANITIZE_STRING)));
		}
	}
}