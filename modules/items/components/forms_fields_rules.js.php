<?php
$form_fields_query = db_query("select r.*, f.name, f.id as fields_id, f.type from app_forms_fields_rules r, app_fields f where f.type in ('fieldtype_dropdown','fieldtype_dropdown_multiple','fieldtype_checkboxes','fieldtype_radioboxes','fieldtype_user_accessgroups','fieldtype_grouped_users','fieldtype_boolean_checkbox','fieldtype_boolean') and r.fields_id=f.id and r.entities_id='" . db_input(filter_var($current_entity_id,FILTER_SANITIZE_STRING)) . "'");

if(db_num_rows($form_fields_query)>0)
{	
	$html = '';
	
	$rules_for_fields = array();
	
	while($form_fields = db_fetch_array($form_fields_query))
	{		
		if(strlen($form_fields['visible_fields']) and strlen($form_fields['choices']))
		{
			$html .= '
				<input class="disply-fields-rules-' . filter_var($form_fields['fields_id'],FILTER_SANITIZE_STRING) . '" type="hidden" data-type="visible" data-choices="' . filter_var($form_fields['choices'],FILTER_SANITIZE_STRING) . '" value="' . filter_var($form_fields['visible_fields'],FILTER_SANITIZE_STRING) . '">';
		}
		
		if(strlen($form_fields['hidden_fields']) and strlen($form_fields['choices']))
		{
			$html .= '
				<input class="disply-fields-rules-' . filter_var($form_fields['fields_id'],FILTER_SANITIZE_STRING) . '" type="hidden" data-type="hidden" data-choices="' . filter_var($form_fields['choices'],FILTER_SANITIZE_STRING) . '" value="' . filter_var($form_fields['hidden_fields'],FILTER_SANITIZE_STRING) . '">';
		}
				
		$rules_for_fields[filter_var($form_fields['fields_id'],FILTER_SANITIZE_STRING)] = filter_var($form_fields['type'],FILTER_SANITIZE_STRING);
					
	}
		
//include form rules if form exist	
if(isset($app_items_form_name))
{	
	$html .= '
		<script>
			$(function(){
			';
	
	$container = (IS_AJAX ? 'ajax-modal':'');
	
	foreach(filter_var_array($rules_for_fields) as $fields_id=>$fields_type)
	{
		$html .= '
			$(".field_' . $fields_id . '").change(function(){					
				app_handle_forms_fields_display_rules(\'' . $container . '\',' . $fields_id . ',\'' . $fields_type . '\',false,false)						
			})	
			
			' . (($app_module_path!='items/info' and $app_module_path!='items/comments_form' and $app_module_path!='items/processes') ? 'app_handle_forms_fields_display_rules(\'' . $container . '\',' . $fields_id . ',\'' . $fields_type . '\',false,false)' : '') . '
		';
		
		//handle comments and process forms
		if(($app_module_path=='items/comments_form' or $app_module_path=='items/processes') and isset($item_info))
		{			
			$field = db_find('app_fields', $fields_id);
			$cfg = new fields_types_cfg($field['configuration']);
			
			$is_multiple = false;
			 
			if(in_array($field['type'], ['fieldtype_dropdown_multiple','fieldtype_checkboxes']))
			{
				$is_multiple = true;
			}
			 
			if($field['type']=='fieldtype_grouped_users' and in_array($cfg->get('display_as'),['checkboxes','dropdown_muliple']))
			{
				$is_multiple = true;
			}
			 
			$value = items::prepare_field_value_by_type($field, $item_info);
			
			$html .=  'app_handle_forms_fields_display_rules(\'\',' . filter_var($field['id'],FILTER_SANITIZE_STRING) . ',"","' . (strlen($value) ? $value : '0') . '",' . (int)$is_multiple . '); ';
		}
	}
	
	$html .= '
			})
		</script>
			';
}
	
	echo $html;
			
}