<?php

$app_reports_query = db_query("select * from app_ext_track_changes where id='" . db_input(filter_var(_get::int('reports_id'),FILTER_SANITIZE_STRING)) . "'");
if(!$app_reports = db_fetch_array($app_reports_query))
{
	redirect_to('ext/track_changes/reports');
}


switch($app_module_action)
{
	case 'save':
		$sql_data = array(
		'reports_id'=>filter_var($app_reports['id'],FILTER_SANITIZE_STRING),
		'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),				
		'track_fields'=>(isset($_POST['track_fields']) ? implode(',',filter_var_array($_POST['track_fields'])) : ''),		
		);
	
		if(isset($_GET['id']))
		{
			db_perform('app_ext_track_changes_entities',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
		}
		else
		{
			db_perform('app_ext_track_changes_entities',$sql_data);
	
			$insert_id = db_insert_id();
		}
	
		redirect_to('ext/track_changes/entities','reports_id=' . $app_reports['id']);
		break;
	
	case 'delete':
		if(isset($_GET['id']))
		{				
			db_query("delete from app_ext_track_changes_entities where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");	
	
			redirect_to('ext/track_changes/entities','reports_id=' . $app_reports['id']);
		}
		break;	
	case 'get_available_fields':
		
		$check_query = db_query("select e.name from app_ext_track_changes_entities ce left join app_entities e on e.id=ce.entities_id where ce.entities_id='" . _post::int('entities_id') . "'" . (isset($_GET['id']) ? " and ce.id!='" . $_GET['id'] . "'":""));
		if($check = db_fetch_array($check_query))
		{
			$html = '
					<div class="alert alert-warning">' . sprintf(TEXT_EXT_ENTITY_ALREADY_USED,filter_var($check['name'],FILTER_SANITIZE_STRING)) . '</div>
						<script>
							$("#entities_id").val("")
						</script>
					';
		}
		else
		{		
			$choices = array();
			$fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type not in (" . fields_types::get_type_list_excluded_in_form(). ",'fieldtype_section') and f.entities_id='" . _post::int('entities_id') . "' and f.forms_tabs_id=t.id order by t.sort_order, t.name, f.sort_order, f.name");
			while($fields = db_fetch_array($fields_query))
			{
				$choices[filter_var($fields['tab_name'],FILTER_SANITIZE_STRING)][filter_var($fields['id'],FILTER_SANITIZE_STRING)] = filter_var($fields['name'],FILTER_SANITIZE_STRING);
			}
			 
			$obj = db_find('app_ext_track_changes_entities',_get::int('id'));
							
			$html = '
		  					
		  			<div class="form-group">
					  	<label class="col-md-3 control-label" for="hidden_fields">' . TEXT_FIELDS  . '</label>
					    <div class="col-md-9">' . select_tag('track_fields[]',$choices,$obj['track_fields'],array('class'=>'form-control input-xlarge chosen-select','multiple'=>'multiple'))  . '
					    	' . tooltip_text(TEXT_EXT_TRACK_FIELDS_INFO) . '	
					    </div>
					  </div>
		
					  
					  <script>
					    appHandleChosen()
					    $(\'[data-toggle="tooltip"]\').tooltip()
					  </script>
		  		';
		}
	
		echo $html;
		exit();
		break;

}