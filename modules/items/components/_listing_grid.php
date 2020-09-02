<?php

$listing_type = $listing->get_listing_type_info('grid');

$html .= '<div class="hidden">' . input_checkbox_tag('select_all_items',filter_var(_post::int('reports_id'),FILTER_SANITIZE_STRING),array('class'=>'select_all_items')) . '</div>';

$html .= '
		
		<style>
		ul.listing-grid{
			grid-template-columns: repeat(auto-fit, minmax(' . (strlen($listing_type['width']) ? $listing_type['width'] : '200').  'px, 1fr));
			margin-top: 10px;
		}
		</style>										
		
		<ul class="listing-grid">';
while($item = db_fetch_array($items_query))
{
	$html .= '
			<li class="' . $listing_highlight->apply(filter_var_array($item)) . '">
				<table style="width: 100%" ' . (($users_notifications->has(filter_var($item['id'],FILTER_SANITIZE_STRING)) and $entity_cfg->get('disable_highlight_unread')!=1) ? 'class="unread-item-row"':''). '>
			';
	
	
	//perpare selected checkbox
	$hide_actions_buttons = false;
	
	if($has_with_selected)
	{
		$checkbox_html = '<div class="listing-section-checkbox">' . input_checkbox_tag('items_' . filter_var($item['id'],FILTER_SANITIZE_STRING),filter_var($item['id'],FILTER_SANITIZE_STRING),array('class'=>'items_checkbox','checked'=>in_array(filter_var($item['id'],FILTER_SANITIZE_STRING),$app_selected_items[filter_var($_POST['reports_id'],FILTER_SANITIZE_STRING)]))) . '</div>';
			
		//check access to action with assigned only
		if(users::has_users_access_name_to_entity('action_with_assigned',filter_var($current_entity_id,FILTER_SANITIZE_STRING)))
		{
			if(!users::has_access_to_assigned_item(filter_var($current_entity_id,FILTER_SANITIZE_STRING),filter_var($item['id'],FILTER_SANITIZE_STRING)))
			{
				$checkbox_html = '';
					
				$hide_actions_buttons = true;
			}
		}		
	}
	//end prepare selected checkbox
			
	$path_info_in_report = array();
	
	if($reports_entities_id>0  and $current_entity_info['parent_id']>0)
	{
		$path_info_in_report = items::get_path_info(filter_var($_POST['reports_entities_id'],FILTER_SANITIZE_STRING),filter_var($item['id'],FILTER_SANITIZE_STRING),filter_var($item,FILTER_SANITIZE_STRING));
	}
	
	foreach(filter_var_array($listing_type['sections']) as $section)
	{
		$html .= '
				<tr>
					<td class="listing-section-align-' . $section['align'] . '">';
		
		if(strlen($section['name']))
		{
			$html .= '
					<div class="listing-section-heading">' . filter_var($section['name'],FILTER_SANITIZE_STRING) . '</div>
					';
		}
	
		$section_fields = [];
	
		foreach($section['fields'] as $field)
		{
			//check field access
			if(isset($fields_access_schema[$field['id']]))
			{
				if($fields_access_schema[$field['id']]=='hide') continue;
			}
	
			//prepare field value
			$value = items::prepare_field_value_by_type(filter_var_array($field), filter_var_array($item));
				
			$output_options = array(
					'class'       => filter_var($field['type'],FILTER_SANITIZE_STRING),
					'value'       => $value,
					'field'       => $field,
					'item'        => $item,
					'is_listing'  => true,
					'redirect_to' => $app_redirect_to,
					'reports_id'  => ($reports_entities_id>0 ? filter_var($_POST['reports_id'],FILTER_SANITIZE_STRING):0),
					'path'        => (isset($path_info_in_report['full_path']) ? $path_info_in_report['full_path']  :$current_path),
					'path_info'   => $path_info_in_report,
					'hide_actions_buttons' => $hide_actions_buttons,
			);
				
				
			if($field['is_heading']==1)
			{
				//get fields in popup
				$popup_html = '';
				if(strlen($_POST['force_popoup_fields']))
				{
					$fields_in_popup = fields::get_items_fields_data_by_id($item,filter_var($_POST['force_popoup_fields']),$current_entity_id,$fields_access_schema);
						
					if(count($fields_in_popup))
					{
						$popup_html = app_render_fields_popup_html($fields_in_popup);
					}
				}
					
				$path = (isset($path_info_in_report['full_path']) ? $path_info_in_report['full_path']  :$current_path . '-' . filter_var($item['id'],FILTER_SANITIZE_STRING));
					
				$value = '<a ' . $popup_html . ' class="item_heading_link" href="' . url_for('items/info', 'path=' . filter_var($path,FILTER_SANITIZE_STRING) . '&redirect_to=subentity&gotopage[' . filter_var($_POST['reports_id'],FILTER_SANITIZE_STRING) . ']=' . filter_var($_POST['page'],FILTER_SANITIZE_STRING)) . '">' . fields_types::output($output_options) . '</a>';
					
				if($entity_cfg->get('use_comments')==1 and $user_has_comments_access and $entity_cfg->get('display_last_comment_in_listing',1))
				{
					$value .= comments::get_last_comment_info($current_entity_id,$item['id'],$path, $fields_access_schema);
				}
					
				$value .= '</td>';
			}
			else
			{
				$value = fields_types::output($output_options);
			}
			
			if($field['type']=='fieldtype_action' and !$hide_actions_buttons)
			{
				$value .= $checkbox_html;
			}
				
			$section_fields[] = [
					'name' => fields_types::get_option(filter_var($field['type'],FILTER_SANITIZE_STRING),'name',filter_var($field['name'],FILTER_SANITIZE_STRING)),
					'short_name' => filter_var($field['short_name'],FILTER_SANITIZE_STRING),
					'long_name' => filter_var($field['long_name'],FILTER_SANITIZE_STRING),
					'value' => $value,
			];
				
		}
	
	
		if($section['display_as']=='list')
		{
			$html .= '<table class="listing-section-table" style="width: 100%">';
				
			foreach($section_fields as $field)
			{
				$html .= '
						<tr>
							' . ($section['display_field_names'] ? '<th ' . (strlen(filter_var($field['short_name'],FILTER_SANITIZE_STRING)) ? 'title="' . htmlspecialchars($field['long_name']) . '"':'' ) . '>' . filter_var($field['name'],FILTER_SANITIZE_STRING) . ': </th>' : '') . '
							<td class="' . ($section['display_field_names'] ? 'with_th' : '') . '">' . filter_var($field['value'],FILTER_SANITIZE_STRING) . '</td>
						</tr>
						';
			}
				
			$html .= '</table>';
		}
		else
		{
			$html .= '<ul class="list-inline">';
				
			foreach($section_fields as $field)
			{
				$html .= '
						<li>
							' . ($section['display_field_names'] ? filter_var($field['name'],FILTER_SANITIZE_STRING) . ': ' : '') . filter_var($field['value'],FILTER_SANITIZE_STRING) . '
						</li>
						';
			}
				
			$html .= '</ul>';
		}
	
	
	
		$html .= '				
					</td>
				</tr>';
	}
	
	$html .= '					
				</table>
			</li>';  
}

if($listing_split->number_of_rows==0)
{
	$html .= '
    <li>' . TEXT_NO_RECORDS_FOUND . '</li>
  ';
}

$html .= '</ul>';

//add pager
$html .= '
<div class="row">
  <div class="col-md-4 col-sm-12">' . $listing_split->display_count() . '</div>
  <div class="col-md-8 col-sm-12">' . $listing_split->display_links(). '</div>
</div>
';

//show hidden blocks on info page
if($listing_split->number_of_rows>0)
{
    $html .= '
        <script>
          $("#' . filter_var($_POST['listing_container'],FILTER_SANITIZE_STRING) . '_info_block").show();
        </script>';
}

echo $html;
