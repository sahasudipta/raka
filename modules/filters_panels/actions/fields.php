<?php

$panels_id = filter_var(_get::int('panels_id'),FILTER_SANITIZE_STRING);
$entities_id = filter_var(_get::int('entities_id'),FILTER_SANITIZE_STRING);

switch($app_module_action)
{		
	case 'save':
						
		$sql_data = array(
			'panels_id' => $panels_id,
			'entities_id' => $entities_id,
			'fields_id'=>filter_var($_POST['fields_id'],FILTER_SANITIZE_STRING),
			'title'=>filter_var($_POST['title'],FILTER_SANITIZE_STRING),
			'width'=>(isset($_POST['width']) ? filter_var($_POST['width'],FILTER_SANITIZE_STRING):''),
			'exclude_values'=>(isset($_POST['exclude_values']) ? implode(',',filter_var_array($_POST['exclude_values'])):''),
			'display_type'=>(isset($_POST['display_type']) ? filter_var($_POST['display_type'],FILTER_SANITIZE_STRING):''),
			'search_type_match'=>(isset($_POST['search_type_match']) ? filter_var($_POST['search_type_match'],FILTER_SANITIZE_STRING):''),
			'height'=>(isset($_POST['height']) ? filter_var($_POST['height'],FILTER_SANITIZE_STRING):''),
			
		);

		if(isset($_GET['id']))
		{						
			db_perform('app_filters_panels_fields',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
		}
		else
		{
			$fields_query = db_query("select max(sort_order) as max_sort_order from app_filters_panels_fields where panels_id='" . _get::int('panels_id'). "'");
			$fields = db_fetch_array($fields_query);
				
			$sql_data['sort_order'] = $fields['max_sort_order']+1;
			
			db_perform('app_filters_panels_fields',$sql_data);
		}

		redirect_to('filters_panels/fields','panels_id=' . filter_var($panels_id,FILTER_SANITIZE_STRING) . '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));

		break;

	case 'delete':

		db_delete_row('app_filters_panels_fields',_get::int('id'));

		redirect_to('filters_panels/fields','panels_id=' . filter_var($panels_id,FILTER_SANITIZE_STRING) . '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
		break;
		
	case 'sort':
		$choices_sorted = filter_var_array($_POST['choices_sorted']);
	
		if(strlen($choices_sorted)>0)
		{
			$choices_sorted = json_decode(stripslashes($choices_sorted),true);

			$sort_order = 0;
			foreach($choices_sorted as $v)
			{
				db_query("update app_filters_panels_fields set sort_order={$sort_order} where id='".db_input($v['id'])."'");
				$sort_order++;
			}
		}
		 
		redirect_to('filters_panels/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&panels_id=' . filter_var($panels_id,FILTER_SANITIZE_STRING));
		break;		
	case 'load_panels_fields':
		$types_for_filters_list = fields_types::get_types_for_filters_list();
				
		//include special filters for Users
		if($entities_id==1)
		{
			$types_for_filters_list .= ", 'fieldtype_user_accessgroups', 'fieldtype_user_status'";
		}
		
		//include input fields
		$types_for_filters_list .= "," . fields_types::get_types_for_search_list();
		
		//include parent item id
		if($app_entities_cache[$entities_id]['parent_id']>0)
		{
			$types_for_filters_list .= ",'fieldtype_parent_item_id'";
		}
		
		$choices = array();
		$choices[''] = '';
		$where_sql = " and f.id not in (select fields_id from app_filters_panels_fields where panels_id={$panels_id} and entities_id={$entities_id} " . (isset($_GET['id']) ? " and id!=" . filter_var($_GET['id'],FILTER_SANITIZE_STRING) : "") . ")";
		$fields_query = db_query("select f.*, t.name as tab_name, if(f.type in ('fieldtype_id','fieldtype_date_added','fieldtype_date_updated','fieldtype_created_by'),-1,t.sort_order) as tab_sort_order from app_fields f, app_forms_tabs t where f.type in (" . $types_for_filters_list . ") {$where_sql} and f.entities_id='" . db_input($entities_id) . "' and f.forms_tabs_id=t.id order by tab_sort_order, t.name, f.sort_order, f.name");
		while($fields = db_fetch_array($fields_query))
		{
		    $choices[$app_entities_cache[$entities_id]['name']][$fields['id']] = fields_types::get_option($fields['type'],'name',$fields['name']);
		}
		
		foreach(entities::get_parents(filter_var($entities_id,FILTER_SANITIZE_STRING)) as $parent_entity_id)
		{		    
		    $fields_query = db_query("select f.*, t.name as tab_name, if(f.type in ('fieldtype_id','fieldtype_date_added','fieldtype_date_updated','fieldtype_created_by'),-1,t.sort_order) as tab_sort_order from app_fields f, app_forms_tabs t where f.type in (" . $types_for_filters_list . ") {$where_sql} and f.entities_id='" . db_input($parent_entity_id) . "' and f.forms_tabs_id=t.id order by tab_sort_order, t.name, f.sort_order, f.name");
		    while($fields = db_fetch_array($fields_query))
		    {
		        $choices[$app_entities_cache[filter_var($parent_entity_id,FILTER_SANITIZE_STRING)]['name']][filter_var($fields['id'],FILTER_SANITIZE_STRING)] = fields_types::get_option($fields['type'],'name',$fields['name']);
		    }
		}
		
		if(isset($_GET['id']))
		{
			$obj = db_find('app_filters_panels_fields',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
		}
		else
		{
			$obj = db_show_columns('app_filters_panels_fields');
		}
		
		$html = '
		  <div class="form-group">
			<label class="col-md-3 control-label" for="fields_id">' . TEXT_FIELD . '</label>
		    <div class="col-md-9">	
		  	  ' . select_tag('fields_id',$choices ,filter_var($obj['fields_id'],FILTER_SANITIZE_STRING),array('class'=>'form-control required chosen-select','onChange'=>'load_panels_fields_settings()')) . '
		    </div>			
		  </div>		 
		 ';
		
		echo $html;
		
		exit();
		break;
	case 'load_panels_fields_settings':
		
		$fields_id = filter_var(_get::int('fields_id'),FILTER_SANITIZE_STRING);
		$field_info = db_find('app_fields',$fields_id);
		
		$panels_info = db_find('app_filters_panels',filter_var($panels_id,FILTER_SANITIZE_STRING));
		
		
		$obj = isset($_GET['id']) ? db_find('app_filters_panels_fields',filter_var($_GET['id'],FILTER_SANITIZE_STRING)) : db_show_columns('app_filters_panels_fields');
		
		$html = '';
		
		
		if($fields_id>0)
		{
		    $field_name = (strlen($field_info['short_name']) ? filter_var($field_info['short_name'],FILTER_SANITIZE_STRING) : fields_types::get_option(filter_var($field_info['type'],FILTER_SANITIZE_STRING),'name',filter_var($field_info['name'],FILTER_SANITIZE_STRING)));
    		
    		$html .= '
        		<div class="form-group">
            		<label class="col-md-3 control-label" for="fields_id">' . TEXT_HEADING . '</label>
            		<div class="col-md-9">
            		' . input_tag('title',filter_var($obj['title'],FILTER_SANITIZE_STRING),array('class'=>'form-control','placeholder'=> TEXT_DEFAULT . ': ' . $field_name)) . '
            		</div>
    		    </div>';
		}
				
		if(in_array($field_info['type'],array(
			'fieldtype_image_map',
			'fieldtype_autostatus',
			'fieldtype_checkboxes',
			'fieldtype_radioboxes',
			'fieldtype_dropdown',
			'fieldtype_dropdown_multiple',
			'fieldtype_dropdown_multilevel',
			'fieldtype_grouped_users',
			'fieldtype_tags',	
			'fieldtype_stages',
		)))
		{
			$cfg = new fields_types_cfg($field_info['configuration']);
			
			if($cfg->get('use_global_list')>0)
			{
				$choices = global_lists::get_choices($cfg->get('use_global_list'),true);				
			}
			else
			{
				$choices = fields_choices::get_choices($field_info['id'],true);				
			}
			
			$html .= '
				<div class="form-group">
				<label class="col-md-3 control-label" for="fields_id">' . TEXT_EXCLUDE_VALUES . '</label>
			    <div class="col-md-9">
			  	  ' . select_tag('exclude_values[]',$choices ,filter_var($obj['exclude_values'],FILTER_SANITIZE_STRING),array('class'=>'form-control chosen-select','multiple'=>'multiple')) . '
			    </div>
			  </div>
			 ';
		}	
		
		
		$choices = [];
		
		if(in_array($field_info['type'],array(
				'fieldtype_autostatus',
				'fieldtype_entity_multilevel',
				'fieldtype_user_roles',
				'fieldtype_entity_ajax',
				'fieldtype_tags',
				'fieldtype_stages',
				'fieldtype_created_by',
				'fieldtype_user_status',
				'fieldtype_user_accessgroups',
				'fieldtype_dropdown',
				'fieldtype_radioboxes',
				'fieldtype_grouped_users',
				'fieldtype_checkboxes',
				'fieldtype_dropdown_multiple',
				'fieldtype_entity',
				'fieldtype_users',				
		        'fieldtype_users_ajax',
				'fieldtype_dropdown_multilevel',
				'fieldtype_parent_item_id',
				'fieldtype_access_group',
		)))
		{
			$choices['dropdown'] = TEXT_FIELDTYPE_DROPDOWN_TITLE;
			$choices['dropdown_multiple'] = TEXT_FIELDTYPE_DROPDOWN_MULTIPLE_TITLE;
			
			if($panels_info['position']=='vertical')
			{
				$choices['checkboxes'] = TEXT_FIELDTYPE_CHECKBOXES_TITLE;
				$choices['radioboxes'] = TEXT_FIELDTYPE_RADIOBOXES_TITLE;
			}		
		}
						
		if(count($choices))
		{	
			$html .= '
				<div class="form-group">
				<label class="col-md-3 control-label" for="fields_id">' . TEXT_DISPLAY_AS . '</label>
			    <div class="col-md-9">
			  	  ' . select_tag('display_type',$choices ,filter_var($obj['display_type'],FILTER_SANITIZE_STRING),array('class'=>'form-control required')) . '
			    </div>
			  </div>
			 ';
			
			if($panels_info['position']=='horizontal')
			{
				
				$html .= '
					<div class="form-group">
					<label class="col-md-3 control-label" for="width">' . TEXT_WIDHT . '</label>
				    <div class="col-md-9">	
				  	  ' . select_tag('width',filters_panels::get_field_width_choices(),filter_var($obj['width'],FILTER_SANITIZE_STRING),array('class'=>'form-control input-medium')) . '
				    </div>			
				  </div>
				 ';
			}
		}
		
		if(in_array($field_info['type'],['fieldtype_input','fieldtype_text_pattern_static']))
		{
			$html .= '
				<div class="form-group">
				<label class="col-md-3 control-label" for="search_type_match">' . TEXT_SEARCH . '</label>
			    <div class="col-md-9">			  	  
			  	  <p class="form-control-static">' . input_checkbox_tag('search_type_match',1,['checked'=>filter_var($obj['search_type_match'],FILTER_SANITIZE_STRING)]) . ' ' . TEXT_SEARCH_TYPE_MATCH  . '</p>
			    </div>
			  </div>
			  <div class="form-group">
				<label class="col-md-3 control-label" for="width">' . TEXT_WIDHT . '</label>
			    <div class="col-md-9">	
			  	  ' . select_tag('width',filters_panels::get_field_width_choices(),filter_var($obj['width'],FILTER_SANITIZE_STRING),array('class'=>'form-control input-medium')) . '
			    </div>			
			  </div>
			 ';
		}
		
		echo $html;
		
		exit();
		break;
}

