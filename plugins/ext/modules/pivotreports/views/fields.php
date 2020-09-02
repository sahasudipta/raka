<?php 

  $breadcrumb = array();
  
  $breadcrumb[] = '<li>' . link_to(TEXT_EXT_PIVOTREPORTS,url_for('ext/pivotreports/reports')) . '<i class="fa fa-angle-right"></i></li>';
  
  $breadcrumb[] = '<li>' . link_to($reports['name'],url_for('ext/pivotreports/view','id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING))) . '<i class="fa fa-angle-right"></i></li>';
    
  $breadcrumb[] = '<li>' . link_to(TEXT_EXT_PIVOTREPORTS_FIELDS,url_for('ext/pivotreports/fields','id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING))) . '</li>';
  
?>

<ul class="page-breadcrumb breadcrumb">
  <?php echo implode('',filter_var_array($breadcrumb)) ?>  
</ul>

<h3 class="page-title"><?php echo TEXT_EXT_PIVOTREPORTS_FIELDS ?></h3>

<div class="alert alert-info"><?php echo TEXT_EXT_PIVOTREPORTS_FIELDS_TIP ?></div>

<?php 
	$entities_list = array();
	$entities_list[] = filter_var($reports['entities_id'],FILTER_SANITIZE_STRING);
	
	$parrent_entities = entities::get_parents(filter_var($reports['entities_id'],FILTER_SANITIZE_STRING));
	
	if(count($parrent_entities)>0)
	{
		$parrent_entities = array_reverse($parrent_entities);
		$entities_list = array_merge($parrent_entities,$entities_list);
	}
	
	//print_r($entities_list);
	
	
	$allowed_fields_types = array(
			'fieldtype_date_added',
			'fieldtype_dropdown',
			'fieldtype_dropdown_multiple',
			'fieldtype_stages',
			'fieldtype_input',
			'fieldtype_boolean',
			'fieldtype_progress',
			'fieldtype_input_date',
			'fieldtype_input_datetime',
			'fieldtype_input_numeric',
			'fieldtype_input_numeric_comments',
			'fieldtype_formula',
			'fieldtype_js_formula',
			'fieldtype_entity',
			'fieldtype_entity_ajax',
			'fieldtype_entity_multilevel',
			'fieldtype_users',
			'fieldtype_grouped_users',
			'fieldtype_created_by',
			'fieldtype_radioboxes',
			'fieldtype_checkboxes',
			'fieldtype_mysql_query',
			'fieldtype_days_difference',
			'fieldtype_hours_difference',
			'fieldtype_years_difference',
			'fieldtype_months_difference'
			
	);
	
	
	$reports_fields = array();
	$reports_fields_cfg = array();
	$pivotreports_fields_query = db_query("select * from app_ext_pivotreports_fields where pivotreports_id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
	while($pivotreports_fields = db_fetch_array($pivotreports_fields_query))
	{
		$reports_fields[] = $pivotreports_fields['fields_id'];
		$reports_fields_cfg[$pivotreports_fields['fields_id']] = array('name'=>$pivotreports_fields['fields_name'],'date_format'=>$pivotreports_fields['cfg_date_format']);
	}
	
	$html = form_tag('fiels_form',url_for('ext/pivotreports/fields','id=' . filter_var($_GET['id'],FILTER_SANITIZE_STRING) . '&action=save')) . '
  	<div class="table-scrollable">	
  		<table class="table table-striped table-bordered table-hover">
  			<thead>
	  			<tr>
	  				<th style="width: 30px;"></th>
	  				<th width="100%">' . TEXT_FIELDS . '</th>
			  		<th>' . TEXT_EXT_PIVOTREPORTS_FIELDS_NAME . '</th>
			  		<th>' . TEXT_DATE_FORMAT . '</th>
	  			</tr>
				</thead>
				<tbody>';
	
	$entities_heading = '';
	
	foreach(filter_var_array($entities_list) as $entities_id)
	{	
		$entities_info = db_find('app_entities',filter_var($entities_id,FILTER_SANITIZE_STRING));
		if($entities_heading!=filter_var($entities_info['name'],FILTER_SANITIZE_STRING))
		{
			$entities_heading = filter_var($entities_info['name'],FILTER_SANITIZE_STRING);
			
			$html .= '
				<tr>
					<td colspan="4" style="padding-top: 15px;"><b>' . htmlentities($entities_heading) .'</b></td>
				</tr>
			';
		}
		
		$fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type in ('" . implode('\',\'',$allowed_fields_types) . "') and f.entities_id='" . db_input(filter_var($entities_id,FILTER_SANITIZE_STRING)) . "' and f.forms_tabs_id=t.id order by t.sort_order, t.name, f.sort_order, f.name");
		while($fields = db_fetch_array($fields_query))
		{
			$html .= '
				<tr>
					<td>' . input_checkbox_tag('fields[' . filter_var($entities_id,FILTER_SANITIZE_STRING) . '][' . filter_var($fields['id'],FILTER_SANITIZE_STRING) . ']',filter_var($fields['id'],FILTER_SANITIZE_STRING),array('checked'=>in_array(filter_var($fields['id'],FILTER_SANITIZE_STRING),$reports_fields))). '</td>
					<td><label for="fields_' . filter_var($entities_id) . '_' . filter_var($fields['id'],FILTER_SANITIZE_STRING) . '">' . fields_types::get_option(filter_var($fields['type'],FILTER_SANITIZE_STRING),'name',filter_var($fields['name'],FILTER_SANITIZE_STRING)) . '</label></td>
					<td>' . input_tag('fields_name[' . filter_var($entities_id,FILTER_SANITIZE_STRING) . '][' . filter_var($fields['id'],FILTER_SANITIZE_STRING) . ']',(isset($reports_fields_cfg[filter_var($fields['id'],FILTER_SANITIZE_STRING)]) ? $reports_fields_cfg[filter_var($fields['id'],FILTER_SANITIZE_STRING)]['name']:''),array('class'=>'form-control input-medium')). '</td>
					<td>' . (in_array(filter_var($fields['type'],FILTER_SANITIZE_STRING),array('fieldtype_date_added','fieldtype_input_date','fieldtype_input_datetime')) ? 
									input_tag('fields_date_format[' . filter_var($entities_id,FILTER_SANITIZE_STRING) . '][' . filter_var($fields['id'],FILTER_SANITIZE_STRING) . ']',(isset($reports_fields_cfg[filter_var($fields['id'],FILTER_SANITIZE_STRING)]) ? $reports_fields_cfg[filter_var($fields['id'],FILTER_SANITIZE_STRING)]['date_format']:''),array('class'=>'form-control input-small')):'') . '</td>
				</tr>
			';
		}
		
	}
	
	$html .= '
					</tbody>
				</table>
			</div>
			' . submit_tag(TEXT_BUTTON_SAVE) . ' <a href="' . url_for('ext/pivotreports/reports') . '" class="btn btn-default">' . TEXT_BUTTON_BACK . '</a>
		</form>';
	
	echo $html;
?>
