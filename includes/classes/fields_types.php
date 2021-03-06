<?php

class fields_types
{
  public static function get_reserved_types()
  {
    return array('fieldtype_action',
                 'fieldtype_id',
                 'fieldtype_date_added',
    			 'fieldtype_date_updated',
                 'fieldtype_created_by',
                 'fieldtype_parent_item_id',
                 );
  }
  
  public static function get_reserved_data_types()
  {
    return array('fieldtype_id',
                 'fieldtype_date_added',
                 'fieldtype_created_by',
                 'fieldtype_parent_item_id',
    			 'fieldtype_date_updated',
                 );
  }
  
  public static function get_users_types()
  {
    return array('fieldtype_user_status',
                 'fieldtype_user_accessgroups',
                 'fieldtype_user_firstname',
                 'fieldtype_user_lastname',
                 'fieldtype_user_email',
                 'fieldtype_user_photo',                 
                 'fieldtype_user_username',
                 'fieldtype_user_language',
                 'fieldtype_user_skin',
    			 'fieldtype_user_last_login_date',
                 );
  }
  
  public static function get_attachments_types()
  {
  	return [
  		'fieldtype_input_file',	
  		'fieldtype_attachments',
  		'fieldtype_image',
  	];
  }
  
  
  public static function get_numeric_types()
  {      
      return [
          'fieldtype_input_numeric',
          'fieldtype_input_numeric_comments',
          'fieldtype_formula',
          'fieldtype_js_formula',
          'fieldtype_mysql_query',
          'fieldtype_ajax_request',
      ];
  }
  
  public static function get_types_for_filters()
  {
    return array('fieldtype_checkboxes',
                 'fieldtype_radioboxes', 
                 'fieldtype_created_by',
                 'fieldtype_date_added',
    						 'fieldtype_date_updated',
                 'fieldtype_boolean',
    						 'fieldtype_boolean_checkbox',
                 'fieldtype_dropdown',
    						 'fieldtype_progress',
    						 'fieldtype_autostatus',
                 'fieldtype_dropdown_multiple',                 
    						 'fieldtype_dropdown_multilevel',
                 'fieldtype_formula',
    						 'fieldtype_js_formula',
    						 'fieldtype_mysql_query',
                 'fieldtype_input_date',
                 'fieldtype_input_datetime',
                 'fieldtype_input_numeric',
                 'fieldtype_input_numeric_comments',
                 'fieldtype_grouped_users',
                 'fieldtype_users',
                 'fieldtype_users_ajax',
                 'fieldtype_entity',
                 'fieldtype_related_records',
    						 'fieldtype_image_map',
    						 'fieldtype_hours_difference',
    						 'fieldtype_days_difference',
    						 'fieldtype_years_difference',
    						 'fieldtype_months_difference',
    						 'fieldtype_auto_increment',
    						 'fieldtype_tags',
    						 'fieldtype_entity_ajax',
    						 'fieldtype_user_roles',
    						 'fieldtype_entity_multilevel',
    						 'fieldtype_users_approve',
    						 'fieldtype_dynamic_date',
    						 'fieldtype_access_group',
    						 'fieldtype_stages',
                 );
  }
  
  public static function get_types_for_search()
  {
  	return [
  	    'fieldtype_user_firstname',
  	    'fieldtype_user_lastname',
  	    'fieldtype_user_email',
  			'fieldtype_attachments',
  			'fieldtype_auto_increment',
  			'fieldtype_barcode',  			
  			'fieldtype_id',
  			'fieldtype_image',
  			'fieldtype_input_email',
  			'fieldtype_input_file',
  			'fieldtype_input_masked',
  			'fieldtype_input_url',
  			'fieldtype_input_vpic',
  			'fieldtype_input',
  			'fieldtype_phone',
  			'fieldtype_random_value',
  			'fieldtype_text_pattern_static',
  			'fieldtype_textarea_wysiwyg',
  			'fieldtype_textarea',  			
  			'fieldtype_todo_list'];
  }
  
  public static function get_types_excluded_in_form()
  {
    return array(
		'fieldtype_related_records',
        'fieldtype_formula',                 
		'fieldtype_mysql_query',
        'fieldtype_text_pattern',
		'fieldtype_qrcode',    						 
		'fieldtype_parent_value',
		'fieldtype_days_difference',	
		'fieldtype_hours_difference',
		'fieldtype_years_difference',
		'fieldtype_months_difference',
		'fieldtype_text_pattern_static',
		'fieldtype_user_last_login_date',
		'fieldtype_google_map',
		'fieldtype_google_map_directions',
		'fieldtype_dynamic_date',
		'fieldtype_signature', 
        'fieldtype_digital_signature',
        'fieldtype_items_by_query',
    );
  }  
  
  public static function get_types_excluded_in_sorting()
  {
  	return array(
  			'fieldtype_action',
  			'fieldtype_text_pattern',
  			'fieldtype_qrcode',
  			'fieldtype_mapbbcode',
  			'fieldtype_parent_value',
  			'fieldtype_google_map',
  			'fieldtype_google_map_directions',
  	        'fieldtype_items_by_query',
  	);
  }
  
  public static function get_types_excluded_in_email()
  {
  	return array(  			
  			'fieldtype_image_map',
  			'fieldtype_mind_map',
  			'fieldtype_mapbbcode',
  			'fieldtype_google_map',
  			'fieldtype_google_map_directions',
  	);
  }
  
  public static function skip_import_field_types()
  {  	
  	//skip reserved
  	$skip_fields = fields_types::get_reserverd_types_list();
  	
  	//skip not allowed
  	$skip_fields .= ",'fieldtype_access_group','fieldtype_user_roles','fieldtype_users_approve','fieldtype_autostatus','fieldtype_google_map','fieldtype_mysql_query','fieldtype_formula','fieldtype_days_difference','fieldtype_hours_difference','fieldtype_users','fieldtype_input_numeric_comments','fieldtype_input_file','fieldtype_attachments','fieldtype_related_records','fieldtype_parent_value'";
  	
  	//skip users fields
  	$skip_fields .= ",'fieldtype_user_status','fieldtype_user_accessgroups','fieldtype_user_photo','fieldtype_user_language','fieldtype_user_skin'";
  	
  	return $skip_fields;
  }
  
  public static function get_types_for_filters_list()
  {
    return "'" . implode("','", fields_types::get_types_for_filters()) . "'";
  }
  
  public static function get_users_types_list()
  {
    return "'" . implode("','", fields_types::get_users_types()) . "'";
  }
  
  public static function get_reserverd_types_list()
  {
    return "'" . implode("','", fields_types::get_reserved_types()) . "'";
  }
  
  public static function get_attachments_types_list()
  {
      return "'" . implode("','", fields_types::get_attachments_types()) . "'";
  }
  
  public static function get_reserverd_data_types_list()
  {
    return "'" . implode("','", fields_types::get_reserved_data_types()) . "'";
  }
  
  public static function get_type_list_excluded_in_form()
  {
    return "'" . implode("','", fields_types::get_types_excluded_in_form())   . "',". fields_types::get_reserverd_types_list();
  }
  
  public static function get_type_list_excluded_in_sorting()
  {
  	return "'" . implode("','", fields_types::get_types_excluded_in_sorting())   . "'";
  }
  
  public static function get_types_for_search_list()
  {
  	return "'" . implode("','", fields_types::get_types_for_search()) . "'";
  }
  
  public static function  get_reserved_filed_name_by_type($type)
  {
    $field_name = '';
    
    switch($type)
    {
      case 'fieldtype_id':
          $field_name = 'id';
        break;
      case 'fieldtype_date_added':
          $field_name = 'date_added';
        break;
      case 'fieldtype_created_by':
          $field_name = 'created_by';
        break; 
    }
    
    return $field_name;
  }
  
  public static function get_tooltip($fieldtype)  
  {
    $tooltip = '';
    
    switch($fieldtype)
    {
      case 'fieldtype_input':
          $tooltip = TEXT_FIELDTYPE_INPUT_TOOLTIP;
        break;
      case 'fieldtype_input_numeric':
          $tooltip = TEXT_FIELDTYPE_INPUT_NUMERIC_TOOLTIP;
        break;
      case 'fieldtype_input_numeric_comments':
          $tooltip = TEXT_FIELDTYPE_INPUT_NUMERIC_COMMENTS_TOOLTIP;
        break;  
      case 'fieldtype_input_url':
          $tooltip = TEXT_FIELDTYPE_INPUT_URL_TOOLTIP;
        break;
      case 'fieldtype_input_date':
          $tooltip = TEXT_FIELDTYPE_INPUT_DATE_TOOLTIP;
        break;
      case 'fieldtype_input_datetime':
          $tooltip = TEXT_FIELDTYPE_INPUT_DATETIME_TOOLTIP;
        break;
      case 'fieldtype_input_file':
          $tooltip = TEXT_FIELDTYPE_INPUT_FILE_TOOLTIP;
        break;
      case 'fieldtype_attachments':
          $tooltip = TEXT_FIELDTYPE_ATTACHMENTS_TOOLTIP;
        break;
      case 'fieldtype_image':
          $tooltip = TEXT_FIELDTYPE_IMAGE_TOOLTIP;
        break;
      case 'fieldtype_textarea':
          $tooltip = TEXT_FIELDTYPE_TEXTAREA_TOOLTIP;
        break;
      case 'fieldtype_textarea_wysiwyg':
          $tooltip = TEXT_FIELDTYPE_TEXTAREA_WYSIWYG_TOOLTIP;
        break;
      case 'fieldtype_text_pattern':
          $tooltip = TEXT_FIELDTYPE_TEXT_PATTERN_TOOLTIP;
        break;
      case 'fieldtype_boolean_checkbox':
      case 'fieldtype_boolean':
          $tooltip = TEXT_FIELDTYPE_BOOLEAN_TOOLTIP;
        break;  
      case 'fieldtype_dropdown':
          $tooltip = TEXT_FIELDTYPE_DROPDOWN_TOOLTIP;
        break;
      case 'fieldtype_dropdown_multiple':
          $tooltip = TEXT_FIELDTYPE_DROPDOWN_MULTIPLE_TOOLTIP;
        break;
      case 'fieldtype_dropdown_multilevel':
        	$tooltip = TEXT_FIELDTYPE_DROPDOWN_MULTILEVEL_TOOLTIP;
        	break;
      case 'fieldtype_checkboxes':
          $tooltip = TEXT_FIELDTYPE_CHECKBOXES_TOOLTIP;
        break;
      case 'fieldtype_radioboxes':
          $tooltip = TEXT_FIELDTYPE_RADIOBOXES_TOOLTIP;
        break;
      case 'fieldtype_formula':
          $tooltip = TEXT_FIELDTYPE_FORMULA_TOOLTIP;
        break;
      case 'fieldtype_users':
          $tooltip = TEXT_FIELDTYPE_USERS_TOOLTIP;
        break;
      case 'fieldtype_grouped_users':
          $tooltip = TEXT_FIELDTYPE_GROUPEDUSERS_TOOLTIP;
        break;
      case 'fieldtype_entity':
          $tooltip = TEXT_FIELDTYPE_ENTITY_TOOLTIP;
        break;
      case 'fieldtype_progress':
          $tooltip = TEXT_FIELDTYPE_PROGRESS_TOOLTIP;
        break;
      case 'fieldtype_related_records':
          $tooltip = TEXT_FIELDTYPE_RELATED_RECORDS_TOOLTIP . TEXT_FIELDTYPE_RELATED_RECORDS_TOOLTIP_EXTRA;
        break;
      case 'fieldtype_input_masked':
          $tooltip = TEXT_FIELDTYPE_INPUT_MASKED_TOOLTIP;
        break;
      case 'fieldtype_input_vpic':
        	$tooltip = TEXT_FIELDTYPE_INPUT_VPIC_TOOLTIP;
        break;
      case 'fieldtype_mapbbcode':
       		$tooltip = TEXT_FIELDTYPE_MAPBBCODE_TOOLTIP;
      	break;
      case 'fieldtype_barcode':
      		$tooltip = TEXT_FIELDTYPE_BARCODE_TOOLTIP;
      		break;
      case 'fieldtype_qrcode':
      		$tooltip = TEXT_FIELDTYPE_QRCODE_TOOLTIP;
      		break;
      case 'fieldtype_input_email':
      		$tooltip = TEXT_FIELDTYPE_INPUT_EMAIL_TOOLTIP;
      		break;
      case 'fieldtype_section':
      		$tooltip = TEXT_FIELDTYPE_SECTION_TOOLTIP;
      		break;
      case 'fieldtype_random_value':
      		$tooltip = TEXT_FIELDTYPE_RANDOM_VALUE_TOOLTIP;
      		break;
      case 'fieldtype_autostatus':
      		$tooltip = TEXT_FIELDTYPE_AUTOSTATUS_TOOLTIP;
      		break;
      case 'fieldtype_js_formula':
      		$tooltip = TEXT_FIELDTYPE_JS_FORMULA_TOOLTIP;
      		break;
      case 'fieldtype_todo_list':
      		$tooltip = TEXT_FIELDTYPE_TODO_LIST_TOOLTIP;
      		break;
      case 'fieldtype_parent_value':
      		$tooltip = TEXT_FIELDTYPE_PARENT_VALUE_TOOLTIP;
      		break;      
      case 'fieldtype_mysql_query':
      		$tooltip = TEXT_FIELDTYPE_MYSQL_QUERY_TOOLTIP;
      		break;
      case 'fieldtype_image_map':
        	$tooltip = TEXT_FIELDTYPE_IMAGE_MAP_TOOLTIP;
      		break;
      case 'fieldtype_mind_map':
      		$tooltip = TEXT_FIELDTYPE_MIND_MAP_TOOLTIP;
      		break;
      case 'fieldtype_days_difference':
     			$tooltip = TEXT_FIELDTYPE_DAYS_DIFFERENCE_TOOLTIP;
     			break;
     	case 'fieldtype_hours_difference':
     			$tooltip = TEXT_FIELDTYPE_HOURS_DIFFERENCE_TOOLTIP;
     			break;
     	case 'fieldtype_auto_increment':
     			$tooltip = TEXT_FIELDTYPE_AUTO_INCREMENT_TOOLTIP;
     			break;
     	case 'fieldtype_text_pattern_static':
     			$tooltip = TEXT_FIELDTYPE_TEXT_PATTERN_STATIC_TOOLTIP;
     			break;
     	case 'fieldtype_years_difference':
     			$tooltip = TEXT_FIELDTYPE_YEARS_DIFFERENCE_TOOLTIP;
   				break;
   		case 'fieldtype_phone':
   				$tooltip = TEXT_FIELDTYPE_PHONE_TOOLTIP;
   				break;
   		case 'fieldtype_google_map':
   				$tooltip = TEXT_FIELDTYPE_GOOGLE_MAP_TOOLTIP;
   				break;
   		case 'fieldtype_input_protected':
   				$tooltip = TEXT_FIELDTYPE_INPUT_PROTECTED_TOOLTIP;
   				break;
   		case 'fieldtype_tags':
   				$tooltip = TEXT_FIELDTYPE_TAGS_TOOLTIP;
   				break;
   		case 'fieldtype_entity_ajax':
   				$tooltip = TEXT_FIELDTYPE_ENTITY_AJAX_TOOLTIP;
   				break;
   		case 'fieldtype_user_roles':
   				$tooltip = TEXT_FIELDTYPE_USER_ROLES_TOOLTIP;
   				break;
   		case 'fieldtype_entity_multilevel':
   				$tooltip = TEXT_FIELDTYPE_ENTITY_MULTILEVEL_TOOLTIP;
   				break;
   		case 'fieldtype_months_difference':
   				$tooltip = TEXT_FIELDTYPE_MONTHS_DIFFERENCE_TOOLTIP;
   				break;
   		case 'fieldtype_users_approve':
   				$tooltip = TEXT_FIELDTYPE_USERS_APPROVE_TOOLTIP;
   				break;
   		case 'fieldtype_google_map_directions':
   				$tooltip = TEXT_FIELDTYPE_GOOGLE_MAP_DIRETIONS_TOOLTIP;
   				break;
   		case 'fieldtype_dynamic_date':
   				$tooltip = TEXT_FIELDTYPE_DYNAMIC_DATE_TOOLTIP;
   				break;
   		case 'fieldtype_access_group':
   				$tooltip = TEXT_FIELDTYPE_ACCESS_GROUP_TOOLTIP;
   				break;
   		case 'fieldtype_signature':
   				$tooltip = TEXT_FIELDTYPE_SIGNATURE_TOOLTIP;
   				break;
   		case 'fieldtype_stages':
   				$tooltip = TEXT_FIELDTYPE_STAGES_TOOLTIP;
   				break;
   		case 'fieldtype_iframe':
   				$tooltip = TEXT_FIELDTYPE_IFRAME_TOOLTIP;
   				break;
   		case 'fieldtype_time':
   				$tooltip = TEXT_FIELDTYPE_TIME_TOOLTIP;
   				break;
   		case 'fieldtype_digital_signature':
   		    $tooltip = TEXT_FIELDTYPE_DIGITAL_SIGNATURE_TOOLTIP;
   		    break;
   		case 'fieldtype_ajax_request':
   		    $tooltip = TEXT_FIELDTYPE_AJAX_REQUEST_TOOLTIP;
   		    break;
   		case 'fieldtype_users_ajax':
   		    $tooltip = TEXT_FIELDTYPE_USERS_AJAX_TOOLTIP;
   		    break;
   		case 'fieldtype_items_by_query':
   		    $tooltip = TEXT_FIELDTYPE_ITEMS_BY_QUERY_TOOLTIP;
   		    break;
   		    
   		    
   				
   		   				
    }
    
    return $tooltip;
  }
   
    
  public static function get_choices()
  {  
    $fieldtypes[TEXT_FIELDS_TYPES_GROUP_INPUT_FIELDS] = array(
    		'fieldtype_input',                                                             
    		'fieldtype_input_masked',
    		'fieldtype_input_protected',
        'fieldtype_input_url',
    		'fieldtype_iframe',
    		'fieldtype_input_email',                                                      
    		'fieldtype_phone',
    );
    
    $fieldtypes[TEXT_FIELDS_TYPES_GROUP_NUMERIC] = array(  
    		'fieldtype_input_numeric',
    		'fieldtype_input_numeric_comments',
    		'fieldtype_formula',
    		'fieldtype_js_formula',
    		'fieldtype_mysql_query',
            'fieldtype_ajax_request',
    );
                                                              
    $fieldtypes[TEXT_FIELDS_TYPES_GROUP_DATES] = array(
    		'fieldtype_input_date',
        'fieldtype_input_datetime',
    		'fieldtype_time',
    		'fieldtype_dynamic_date',
    		'fieldtype_years_difference',
    		'fieldtype_months_difference',
    		'fieldtype_days_difference',
    		'fieldtype_hours_difference',
    );
                                                                                                                   
    $fieldtypes[TEXT_FIELDS_TYPES_GROUP_TEXT] = array(
    		'fieldtype_textarea',
        'fieldtype_textarea_wysiwyg',
        'fieldtype_text_pattern',
    		'fieldtype_text_pattern_static',
    );
    
    $fieldtypes[TEXT_FIELDS_TYPES_GROUP_UPLOAD] = array(
    		'fieldtype_attachments',
        'fieldtype_input_file',
        'fieldtype_image',
    );                                                  
                                                      
    $fieldtypes[TEXT_FIELDS_TYPES_GROUP_LIST] = array(    		
        'fieldtype_dropdown',
        'fieldtype_dropdown_multiple',                                                      
    		'fieldtype_dropdown_multilevel',
    		'fieldtype_tags',
        'fieldtype_checkboxes',
        'fieldtype_radioboxes',
    		'fieldtype_boolean',
    		'fieldtype_boolean_checkbox',
        'fieldtype_progress',                                                          		
    );
                                                                              
    $fieldtypes[TEXT_FIELDS_TYPES_GROUP_USERS] = array(
		'fieldtype_users',
        'fieldtype_users_ajax',
        'fieldtype_grouped_users',
		'fieldtype_access_group',
		'fieldtype_user_roles',
		'fieldtype_users_approve',
        'fieldtype_digital_signature',
    );
                                                       
    $fieldtypes[TEXT_FIELDS_TYPES_GROUP_ENTITY] = array(
    		'fieldtype_entity',
    		'fieldtype_entity_ajax',
    		'fieldtype_entity_multilevel',
            'fieldtype_related_records',
    		'fieldtype_parent_value',
            'fieldtype_items_by_query'
    );
    
    $fieldtypes[TEXT_MAPS] = array(
    		'fieldtype_mapbbcode',
    		'fieldtype_google_map',
    		'fieldtype_google_map_directions',
    		'fieldtype_image_map',
    		'fieldtype_mind_map',    		
    );
    
    $fieldtypes[TEXT_FIELDS_TYPES_GROUP_SPCEIAL_FIELDS] = array(    		
    		'fieldtype_section',
    		'fieldtype_random_value',
    		'fieldtype_auto_increment',
    		'fieldtype_autostatus',
    		'fieldtype_stages',
    		'fieldtype_todo_list',
    		'fieldtype_input_vpic',    		
    		'fieldtype_barcode',
    		'fieldtype_qrcode',
    		'fieldtype_signature',
    );
    
    foreach($fieldtypes as $group=>$fields) 
    {           
      foreach($fields as $class)
      {       
        $fieldtype = new $class;
      
        $choices[$group][$class] = $fieldtype->options['title'];
      }          
    }        
                 
    return $choices;
  }
  
  public static function get_title($class)
  {
    $fieldtype = new $class;
    
    return filter_var($fieldtype->options['title'],FILTER_SANITIZE_STRING);
  }
  
  public static function render_field_name($name, $class, $fields_id)
  {
    global $_GET; 
    
    $fieldtype = new $class;
    
    if(!isset($fieldtype->options['has_choices']))
    {
      $fieldtype->options['has_choices'] = false;
    }
    
    if($fieldtype->options['has_choices'])
    {
      return '<a href="' . url_for('entities/fields_choices','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) .  '&fields_id=' . filter_var($fields_id,FILTER_SANITIZE_STRING)). '"><i class="fa fa-list"></i>&nbsp;' . filter_var($name,FILTER_SANITIZE_STRING) . '</a>';
    }
    elseif(in_array($class, array('fieldtype_related_records','fieldtype_entity')))
    {
      return '<a href="' . url_for('entities/fields_settings','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) .  '&fields_id=' . filter_var($fields_id,FILTER_SANITIZE_STRING)). '"><i class="fa fa-gear"></i>&nbsp;' . filter_var($name,FILTER_SANITIZE_STRING) . '</a>';
    }
    elseif(in_array($class, array('fieldtype_entity_ajax','fieldtype_entity_multilevel')))
    {
    	return '<a href="' . url_for('entities/entityfield_filters','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) .  '&fields_id=' . filter_var($fields_id,FILTER_SANITIZE_STRING)). '"><i class="fa fa-gear"></i>&nbsp;' . filter_var($name,FILTER_SANITIZE_STRING) . '</a>';
    }
    elseif(in_array($class, array('fieldtype_user_roles')))
    {
    	return '<a href="' . url_for('entities/user_roles','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) .  '&fields_id=' . filter_var($fields_id,FILTER_SANITIZE_STRING)). '"><i class="fa fa-gear"></i>&nbsp;' . filter_var($name,FILTER_SANITIZE_STRING) . '</a>';
    }
    elseif(in_array($class, array('fieldtype_users_approve','fieldtype_signature','fieldtype_digital_signature')))
    {
    	return '<a href="' . url_for('entities/fields_filters','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) .  '&fields_id=' . filter_var($fields_id,FILTER_SANITIZE_STRING)). '"><i class="fa fa-gear"></i>&nbsp;' . filter_var($name,FILTER_SANITIZE_STRING) . '</a>';
    }
    else
    {
      return $name;
    }
  }
  
  public static function render_configuration($cfg,$id)
  {
  	$html = '';
    $configuration = array();
    
    $obj = db_find('app_fields',filter_var($id,FILTER_SANITIZE_STRING));
    
    if(strlen($obj['configuration'])>0)
    {
      $configuration = fields_types::parse_configuration(filter_var($obj['configuration'],FILTER_SANITIZE_STRING));            
    }
    
    //print_r($configuration);;        
    
    //prepare tabs
    $tabs = [];
    $tabs_cfg = [];
    foreach(filter_var_array($cfg) as $tab_name=>$v)
    {
    	if(!is_numeric($tab_name))
    	{
    		$tab_id = strtolower(str_replace(' ','_',$tab_name)) . '_' . strlen($tab_name);
    		$tabs[$tab_id] = $tab_name;
    		$tabs_cfg[$tab_id] = $v;
    	}
    }
    
    //display tabs if exist
    if(count($tabs))
    {
    	$html .= '<ul class="nav nav-tabs">';
    	$count_tabs = 0;
    	foreach(filter_var_array($tabs) as $tab_id=>$tab_name)
    	{
    		$html .= '<li class="' . ($count_tabs==0 ? 'active':''). '"><a href="#' . $tab_id . '"  data-toggle="tab">' . $tab_name . '</a></li>';
    		$count_tabs++;
    	}
    	$html .= '</ul>';
    }
    else
    {	
    	$tabs_cfg[] = $cfg;
    }
    
    $html .= '<div class="tab-content">';
    
    $count_tabs = 0;
    foreach(filter_var_array($tabs_cfg) as $tab_id=>$cfg)
    {	
    	//prepare tabs content if exist tabs
    	if($count_tabs==0 and count($tabs))
    	{
				$html .= '<div class="tab-pane fade active in" id="' . $tab_id . '">';     		
    	}
    	elseif(count($tabs))
    	{
    		$html .= '<div class="tab-pane fade" id="' . $tab_id . '">';
    	}
    	else
    	{
    		$html .= '<div>';
    	}
    	
	    foreach(filter_var_array($cfg) as $tab_name=>$v)
	    {
	    	//handle tabls
	    	if(!is_numeric($tab_name))
	    	{
	    		if(!in_array($tab_name, $tabs))
	    		{	
	    			$tabs[] = $tab_name;
	    		}
	    	}
	    	
	      //handle default value
	      if(isset($v['name']))
	      if(!isset($configuration[$v['name']]) and isset($v['default']))
	      {
	        $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)] = filter_var($v['default'],FILTER_SANITIZE_STRING); 
	      }
	      
	      $field = '';
	      switch($v['type'])
	      {
	        case 'dropdown':
	            $field = select_tag('fields_configuration[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . ']' . (isset($v['params']['multiple']) ? '[]':''),filter_var($v['choices'],FILTER_SANITIZE_STRING),(isset($configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)]) ? $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)] : ''), (isset($v['params']) ? filter_var($v['params'],FILTER_SANITIZE_STRING):array()));	            
	          break;
	        case 'checkbox':
	            $field = '<div class="checkbox-list"><label class="checkbox-inline">' . input_checkbox_tag('fields_configuration[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . ']',1,array('checked'=>(isset($configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)]) ? $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)] : false))) . '</label></div>';
	          break;
	        case 'colorpicker':
	            $field ='
	              <div class="input-group input-small color colorpicker-default" data-color="' . (isset($configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)]) ? $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)] : '#ff0000') . '" >
	          	   ' . input_tag('fields_configuration[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . ']',(isset($configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)]) ? $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)] : ''),array('class'=>'form-control input-small')) . '
	                <span class="input-group-btn">
	          				<button class="btn btn-default" type="button"><i style="background-color: #3865a8;"></i>&nbsp;</button>
	          			</span>
	          		</div>
	            ';
	          break;           
	        case 'input-with-colorpicker':
	            $field ='              
	              <div class="input-group input-with-colorpicker">
	                ' . input_tag('fields_configuration[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . ']',(isset($configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)]) ? $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)] : ''),array('class'=>'form-control input-xsmall')) . '
	                <div class="input-group-btn">             
	                  <div class="input-group input-small color colorpicker-default" data-color="' . (isset($configuration[filter_var($v['name'],FILTER_SANITIZE_STRING). '_color']) ? $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING). '_color'] : '#ff0000') . '" >                                
	              	   ' . input_tag('fields_configuration[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . '_color]',(isset($configuration[filter_var($v['name'],FILTER_SANITIZE_STRING) . '_color']) ? $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING) . '_color'] : ''),array('class'=>'form-control input-small')) . '
	                    <span class="input-group-btn">
	              				<button class="btn btn-default" type="button"><i style="background-color: #3865a8;"></i>&nbsp;</button>
	              			</span>
	              		</div>                
	                </div>
	              </div>
	            ';
	          break;   
	        case 'input':
	            $field = input_tag('fields_configuration[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . ']',(isset($configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)]) ? $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)] : ''), (isset($v['params']) ? filter_var($v['params'],FILTER_SANITIZE_STRING):array()));
	          break; 
	        case 'file':
	        	$value = (isset($configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)]) ? $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)] : '');
	        	$field = input_file_tag('fields_configuration[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . ']', (isset($v['params']) ? filter_var($v['params'],FILTER_SANITIZE_STRING):array())) . (strlen($value) ? $value . '&nbsp;&nbsp;&nbsp;<label>' . input_checkbox_tag('delete_file[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . ']', $value) . ' '. TEXT_DELETE . '</label>':'');
	          $field .= input_hidden_tag('fields_configuration[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . ']',$value);
	          break;
	        case 'textarea':
	            $field = textarea_tag('fields_configuration[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . ']',(isset($configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)]) ? $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)] : ''), (isset($v['params']) ? filter_var($v['params'],FILTER_SANITIZE_STRING):array()));
	          break;
	        case 'code':
	            $v['params']['style'] = "height:310px;";
	            $field = textarea_tag('fields_configuration[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . ']',(isset($configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)]) ? $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)] : ''), (isset($v['params']) ? filter_var($v['params'],FILTER_SANITIZE_STRING):array()));
	            $field .= app_include_codemirror(['javascript']);
	            $field .= '
	                <script>
	                $(function(){ 
    	                setTimeout(function() {
                          var myCodeMirror1 = CodeMirror.fromTextArea(document.getElementById("fields_configuration_' . $v['name'] . '"), {
                            lineNumbers: true,       
                            autofocus:true,
                            lineWrapping: true,
                            extraKeys: {
                    		     "F11": function(cm) {
                    		       cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                    		     },
                    		     "Esc": function(cm) {
                    		      if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                    		    },                    		    
                    		  }   
                          })                          
    	                 }, 100);
	                })
	                </script>     
	                ';
	            break;
	      }
	      
	      
	      if($v['type']=='hidden')
	      {
	        $html .= input_hidden_tag('fields_configuration[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . ']',(isset($configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)]) ? $configuration[filter_var($v['name'],FILTER_SANITIZE_STRING)] : ''));
	      }
	      elseif($v['type']=='section')
	      {
	      	$html .= '<h3 class="form-section">' . filter_var($v['title'],FILTER_SANITIZE_STRING) . '</h3>' . (isset($v['html']) ? filter_var($v['html'],FILTER_SANITIZE_STRING) : '');
	      }
	      elseif($v['type']=='ajax')
	      {
	      	$html .= '<div id="fields_types_ajax_configuration_' . filter_var($v['name'],FILTER_SANITIZE_STRING) . '"></div>' . filter_var($v['html'],FILTER_SANITIZE_STRING);
	      }
	      elseif($v['type']=='html')
	      {
	      	$html .=  filter_var($v['html'],FILTER_SANITIZE_STRING);
	      }
	      else
	      {      
	        $html .= '
	        
	        <div class="form-group">
	        	<label class="col-md-3 control-label" for="' . generate_id_from_name('fields_configuration[' . filter_var($v['name'],FILTER_SANITIZE_STRING) . ']') . '">' . 
	            (isset($v['tooltip_icon']) ? tooltip_icon($v['tooltip_icon']) : '') . filter_var($v['title'],FILTER_SANITIZE_STRING) . 
	          '</label>
	          <div class="col-md-' . (in_array($v['type'],['code']) ? '12':'9'). '">' .	
	        	   $field . 	        	   
	             (isset($v['tooltip']) ? tooltip_text(filter_var($v['tooltip'],FILTER_SANITIZE_STRING)):'')  . '
	          </div>			
	        </div>
	        ';
	      }
	    }
	    
	    $count_tabs++;
	    
	    $html .= '</div>';
    }
    
    $html .= '</div>';
    
    $html .= '
      <script>
        $(".input-masked").each(function(){
          $.mask.definitions["~"]="[,. *]";
          $(this).mask($(this).attr("data-mask"));
        })
        
      </script>
    ';
    
    return $html;
  }
  
  public static function prepare_configuration($v)
  {    
    return app_json_encode($v);
  }
  
  public static function parse_configuration($v)
  {
    if(strlen($v)>0)
    {    	
      return json_decode($v,true);
    }
    else
    {
      return array();
    }
  } 
  
  public static function render($class,$field,$obj,$params=array())
  {
    $fieldtype = new $class;
    
    return $fieldtype->render($field,$obj,$params);
  }
  
  public static function process($options = array())
  {
    $fieldtype = new $options['class'];
    
    return $fieldtype->process($options);
  }
  
  public static function output($options = array())
  {
    $fieldtype = new $options['class'];
    
    return $fieldtype->output($options);
  }
  
  public static function reports_query($options = array())
  {
    $fieldtype = new $options['class'];
    
    if(method_exists($fieldtype,'reports_query'))
    { 
      return $fieldtype->reports_query($options);
    }
    else
    {
      return $options['sql_query'];
    }                  
  }
  
  public static function get_option($class,$key,$default = '')
  {
  	if(!strlen($class)) return '';
  	
    $fieldtype = new $class;
    
    if($key=='name' and strlen($default)>0)
    {	
    	return filter_var($default,FILTER_SANITIZE_STRING);
    }
    elseif(isset($fieldtype->options[$key]))
    {
      return filter_var($fieldtype->options[$key],FILTER_SANITIZE_STRING) ;
    }
    else
    {
      return filter_var($default,FILTER_SANITIZE_STRING);
    }
  }
  
  public static function recalculate_numeric_comments_sum($entity_id,$item_id)
  {
    $fields_query = db_query("select f.* from app_fields f where f.type  in ('fieldtype_input_numeric_comments') and  f.entities_id='" . db_input($entity_id) . "' and f.comments_status=1 order by f.comments_sort_order, f.name");
    while($fields = db_fetch_array($fields_query))
    {
      $total = 0;
    
      $comments_query = db_query("select * from app_comments where entities_id='" . db_input($entity_id) . "' and items_id='" . db_input($item_id) . "'");
      while($comments = db_fetch_array($comments_query))
      {
        $history_query = db_query("select * from app_comments_history where comments_id='" . db_input(filter_var($comments['id'],FILTER_SANITIZE_STRING)) . "' and fields_id='" . filter_var($fields['id'],FILTER_SANITIZE_STRING). "'");
        while($history = db_fetch_array($history_query))
        {        
          $total +=$history['fields_value'];        
        }      
      }
      
      $sql_data = array('field_' . $fields['id']=>$total);
      
      db_perform('app_entity_' . $entity_id,$sql_data,'update',"id='" . db_input($item_id) . "'");
    }
  }
  
  public static function get_types_wich_choices()
  {
    return array('fieldtype_dropdown','fieldtype_dropdown_multiple','fieldtype_radioboxes','fieldtype_grouped_users','fieldtype_checkboxes','fieldtype_dropdown_multilevel');
  } 
  
  public static function prepare_uniquer_error_msg_param($attributes,$cfg)
  {
  	if($cfg->get('is_unique') and strlen($cfg->get('unique_error_msg')))
  	{
  		$attributes['data-unique-error-msg'] = htmlspecialchars($cfg->get('unique_error_msg'));
  	}
  	
	  return $attributes;	
  }
  
  
  //use update_items_fields form any fields types where it's requred
  public static function update_items_fields($current_entity_id, $item_id)
  {
  	global $fieldtype_mysql_query_force;
  	
  	//autoupdate fields in  fieldtype_mysql_query
  	$fieldtype_mysql_query_force = true;
  	fieldtype_mysql_query::update_items_fields($current_entity_id, $item_id);
  	
  	//dynamic date
  	fieldtype_dynamic_date::update_items_fields($current_entity_id, $item_id);
  	
  	//autoupdate time diff
  	fieldtype_days_difference::update_items_fields($current_entity_id, $item_id);
  	fieldtype_hours_difference::update_items_fields($current_entity_id, $item_id);
  	fieldtype_years_difference::update_items_fields($current_entity_id, $item_id);
  	fieldtype_months_difference::update_items_fields($current_entity_id, $item_id);
  	  	  	
  	//maps
  	fieldtype_google_map::update_items_fields($current_entity_id, $item_id);
  	fieldtype_google_map_directions::update_items_fields($current_entity_id, $item_id);
  	
  	//autoupdate static text pattern
  	fieldtype_text_pattern_static::set($current_entity_id, $item_id);
  	
  	//atuoset fieldtype autostatus
  	fieldtype_autostatus::set($current_entity_id, $item_id);
  }
  
  public static function custom_error_handler($fields_id)
  {
      return '
          <label id="fields_' . $fields_id . '-error" class="error" for="fields_' . $fields_id . '"></label>
          <script>
              $("#fields_' . $fields_id . '").on("change", function(e) { 
                  $("#fields_' . $fields_id . '-error").hide(); 
              });
          </script>';  
  }
  
  public static function is_empty_value($value, $type)
  {
      if(strlen($value)==0 or ($value==0 and in_array($type,['fieldtype_dropdown','fieldtype_radioboxes','fieldtype_created_by','fieldtype_input_date','fieldtype_input_datetime','fieldtype_time','fieldtype_entity_multilevel'])))
      {
          return true;
      }
      else
      {
          return false;
      }      
  }
}