<?php

$panels_id = filter_var(_get::int('panels_id'),FILTER_SANITIZE_STRING);
$entities_id = filter_var(_get::int('entities_id'),FILTER_SANITIZE_STRING);

switch($app_module_action)
{		
	case 'save':
						
		$sql_data = array(
			'panels_id' => filter_var($panels_id,FILTER_SANITIZE_STRING),
			'entities_id' => filter_var($entities_id,FILTER_SANITIZE_STRING),
			'fields_id'=>filter_var($_POST['fields_id'],FILTER_SANITIZE_STRING),
			'width'=>(isset($_POST['width']) ? filter_var($_POST['width'],FILTER_SANITIZE_STRING):''),
			'exclude_values'=>(isset($_POST['exclude_values']) ? implode(',',filter_var_array($_POST['exclude_values'])):''),
			'display_type'=>(isset($_POST['display_type']) ? filter_var_array($_POST['display_type']):''),
			'search_type_match'=>(isset($_POST['search_type_match']) ? filter_var_array($_POST['search_type_match']):''),
			'height'=>(isset($_POST['height']) ? filter_var($_POST['height'],FILTER_SANITIZE_STRING):''),
			
		);

		if(isset($_GET['id']))
		{						
			db_perform('app_filters_panels_fields',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'])) . "'");
		}
		else
		{
			$fields_query = db_query("select max(sort_order) as max_sort_order from app_filters_panels_fields where panels_id='" . db_input(filter_var(_get::int('panels_id'),FILTER_SANITIZE_STRING)). "'");
			$fields = db_fetch_array($fields_query);
				
			$sql_data['sort_order'] = $fields['max_sort_order']+1;
			
			db_perform('app_filters_panels_fields',$sql_data);
		}

		redirect_to('ext/filters_panels/fields','panels_id=' . $panels_id . '&redirect_to=' . $app_redirect_to . '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));

		break;

	case 'delete':

		db_delete_row('app_filters_panels_fields',_get::int('id'));

		redirect_to('ext/filters_panels/fields','panels_id=' . $panels_id . '&redirect_to=' . $app_redirect_to . '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
		break;
		
	case 'sort':
		$choices_sorted = filter_var($_POST['choices_sorted'],FILTER_SANITIZE_STRING);
	
		if(strlen($choices_sorted)>0)
		{
			$choices_sorted = json_decode(stripslashes($choices_sorted),true);

			$sort_order = 0;
			foreach($choices_sorted as $v)
			{
				db_query("update app_filters_panels_fields set sort_order={$sort_order} where id='".filter_var($v['id'],FILTER_SANITIZE_STRING)."'");
				$sort_order++;
			}
		}
		 
		redirect_to('ext/filters_panels/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&redirect_to=' . filter_var($app_redirect_to,FILTER_SANITIZE_STRING) . '&panels_id=' . filter_var($panels_id,FILTER_SANITIZE_STRING));
		break;		

}

