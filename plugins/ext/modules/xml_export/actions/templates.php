<?php

if (!app_session_is_registered('xml_templates_filter'))
{
	$xml_templates_filter = 0;
	app_session_register('xml_templates_filter');
}

switch($app_module_action)
{
	case 'set_xml_templates_filter':
      $xml_templates_filter = $_POST['xml_templates_filter'];
      
      redirect_to('ext/xml_export/templates');
    break;
	case 'save':
		$sql_data = array(
			'name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
			'template_filename'=>filter_var($_POST['template_filename'],FILTER_SANITIZE_STRING),
			'transliterate_filename'=>(isset($_POST['transliterate_filename']) ? 1:0),			
			'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),
			'button_title'=>filter_var($_POST['button_title'],FILTER_SANITIZE_STRING),
			'button_position'=>(isset($_POST['button_position']) ? implode(',',filter_var_array($_POST['button_position'])) : ''),
			'button_color'=>filter_var($_POST['button_color'],FILTER_SANITIZE_STRING),
			'button_icon'=>filter_var($_POST['button_icon'],FILTER_SANITIZE_STRING),
			'is_active' => (isset($_POST['is_active']) ? 1:0),
			'sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),
			'users_groups' => (isset($_POST['users_groups']) ? implode(',',filter_var_array($_POST['users_groups'])):''),
			'assigned_to' => (isset($_POST['assigned_to']) ? implode(',',filter_var_array($_POST['assigned_to'])):''),
			'template_header'=>filter_var($_POST['template_header'],FILTER_SANITIZE_STRING),
			'template_body'=>filter_var($_POST['template_body'],FILTER_SANITIZE_STRING),
			'template_footer'=>filter_var($_POST['template_footer'],FILTER_SANITIZE_STRING),	
			'is_public'=>filter_var($_POST['is_public'],FILTER_SANITIZE_STRING),			
		);
	
		if(isset($_GET['id']))
		{
			db_perform('app_ext_xml_export_templates',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
		}
		else
		{
			db_perform('app_ext_xml_export_templates',$sql_data);
		}
	
		redirect_to('ext/xml_export/templates');
		break;
	case 'delete':
		if(isset($_GET['id']))
		{
			db_query("delete from app_ext_xml_export_templates where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
			
			$report_info_query = db_query("select * from app_reports where reports_type='xml_export" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)). "'");
			if($report_info = db_fetch_array($report_info_query))
			{
				reports::delete_reports_by_id($report_info['id']);
			}
	
			$alerts->add(TEXT_EXT_WARN_DELETE_TEMPLATE_SUCCESS,'success');
			 
			redirect_to('ext/xml_export/templates');
		}
		break;	
	case 'get_fields':
		
		$obj = db_find('app_ext_xml_export_templates',filter_var($_POST['id'],FILTER_SANITIZE_STRING));
		
		$html = '
				<div class="form-group">
			  	<label class="col-md-3 control-label" for="users_groups">' . TEXT_START . '</label>
			    <div class="col-md-9">	
			  	  ' . textarea_tag('template_header',$obj['template_header'],['class'=>'form-control textarea-small','style'=>'font-size:13px;']) . '
			  	  ' . tooltip_text(TEXT_EXT_XML_EXPORT_START_TIP) . '
			    </div>			
			  </div> 
			  
			  <div class="form-group">
			  	<label class="col-md-3 control-label" for="users_groups">' .  TEXT_BODY . fields::get_available_fields_helper(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING), 'template_body') . '</label>
			    <div class="col-md-9">	
			  	  ' . textarea_tag('template_body',$obj['template_body'],['class'=>'form-control','style'=>'min-height: 260px; font-size:13px;'])  . '
			  	  ' . tooltip_text(TEXT_EXT_PREPARE_TEMPLATE_FOR_SINGLE_ITEM . '<br>' . TEXT_ENTER_TEXT_PATTERN_INFO_SHORT) . '
			    </div>			
			  </div>
			  
			  <div class="form-group">
			  	<label class="col-md-3 control-label" for="users_groups">' . TEXT_END . '</label>
			    <div class="col-md-9">	
			  	  ' . textarea_tag('template_footer',$obj['template_footer'],['class'=>'form-control textarea-small','style'=>'font-size:13px;']) . '      
			    </div>			
			  </div>
			  	  		
			  <p>' . TEXT_EXT_XML_EXPORT_BODY_TIP . '</p>
				';
		
		echo $html;
		
		exit();
		break;
}