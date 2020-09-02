<?php

app_reset_selected_items();

if(isset($field_entity_items_position))
{	
	$fields_query = db_query("select id, name, configuration, entities_id from app_fields where entities_id!='" . db_input(filter_var($current_entity_id,FILTER_SANITIZE_STRING))  . "' and type in ('fieldtype_entity','fieldtype_entity_ajax','fieldtype_entity_multilevel')");
	while($fields = db_fetch_array($fields_query))
	{
		$field_cfg = new fields_types_cfg(filter_var($fields['configuration'],FILTER_SANITIZE_STRING));
	
		if(filter_var($field_cfg->get('entity_id'),FILTER_SANITIZE_STRING)==filter_var($current_entity_id,FILTER_SANITIZE_STRING))
		{						
			if($app_user['group_id']==0)
			{
				$entities_query = db_query("select e.* from app_entities e where id='" . db_input(filter_var($fields['entities_id'],FILTER_SANITIZE_STRING)) . "' order by e.sort_order, e.name");
			}
			else
			{
				$entities_query = db_query("select e.* from app_entities e, app_entities_access ea where e.id=ea.entities_id and length(ea.access_schema)>0 and ea.access_groups_id='" . db_input(filter_var($app_user['group_id'],FILTER_SANITIZE_STRING)) . "' and e.id = '" . db_input(filter_var($fields['entities_id'],FILTER_SANITIZE_STRING)) . "' order by e.sort_order, e.name");
			}
						
			if($entities = db_fetch_array($entities_query))
			{								
				if($entity_cfg->get('item_page_field' . filter_var($fields['id'],FILTER_SANITIZE_STRING) . '_entity' . filter_var($entities['id'],FILTER_SANITIZE_STRING) . '_position')==$field_entity_items_position)
				{
										
					//try to get report type parent_item_info_page
					$subentity_report_query = db_query("select * from app_reports where entities_id='" . db_input(filter_var($entities['id'],FILTER_SANITIZE_STRING)). "' and reports_type='field" . db_input(filter_var($fields['id'],FILTER_SANITIZE_STRING)) . "_entity_item_info_page'");
					if(!$subentity_report = db_fetch_array($subentity_report_query))
					{
						$sql_data = array('name'=>'',
								'entities_id'=>filter_var($entities['id'],FILTER_SANITIZE_STRING),
								'reports_type'=>'field' . filter_var($fields['id'],FILTER_SANITIZE_STRING) . '_entity_item_info_page',
								'in_menu'=>0,
								'in_dashboard'=>0,
								'created_by'=>0,
						);
		
						db_perform('app_reports',$sql_data);
		
						$reports_id = db_insert_id();
		
						$subentity_report = db_find('app_reports',filter_var($reports_id,FILTER_SANITIZE_STRING));
					}
						
					$subentity_cfg = new entities_cfg(filter_var($entities['id'],FILTER_SANITIZE_STRING));
						
					$listing_container = 'entity_items_listing' . filter_var($subentity_report['id'],FILTER_SANITIZE_STRING) . '_' .  filter_var($subentity_report['entities_id'],FILTER_SANITIZE_STRING);
						
						
					//get report entity access schema
					$access_schema = users::get_entities_access_schema(filter_var($subentity_report['entities_id'],FILTER_SANITIZE_STRING),filter_var($app_user['group_id'],FILTER_SANITIZE_STRING));
						
					$add_button = '';					
					if(users::has_access('create',$access_schema))
					{
						$field_entity_info = db_find('app_entities',filter_var($entities['id'],FILTER_SANITIZE_STRING));
						
						switch(true)
						{
							case $field_entity_info['parent_id']==0:
								$url = url_for('items/form','path=' . filter_var($entities['id'],FILTER_SANITIZE_STRING) . '&fields[' . filter_var($fields['id'],FILTER_SANITIZE_STRING) . ']=' . filter_var($current_item_id,FILTER_SANITIZE_STRING). '&redirect_to=item_info_page' . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . '-'. filter_var($current_item_id,FILTER_SANITIZE_STRING));
								break;
							case ($field_entity_info['parent_id']>0 and $field_entity_info['parent_id']!=$entity_info['parent_id']):
								$url = url_for('reports/prepare_add_item','reports_id=' . filter_var($subentity_report['id'],FILTER_SANITIZE_STRING) . '&fields[' . filter_var($fields['id'] ,FILTER_SANITIZE_STRING). ']=' . filter_var($current_item_id,FILTER_SANITIZE_STRING). '&redirect_to=item_info_page' . filter_var($current_entity_id ,FILTER_SANITIZE_STRING). '-'. filter_var($current_item_id,FILTER_SANITIZE_STRING));								
								break;
							case ($field_entity_info['parent_id']>0 and $field_entity_info['parent_id']==$entity_info['parent_id']):
								$path_info = items::parse_path($app_path);
								$url = url_for('items/form','path=' . filter_var($path_info['parent_entity_id'],FILTER_SANITIZE_STRING) . '-' . filter_var($path_info['parent_entity_item_id'],FILTER_SANITIZE_STRING) . '/'. filter_var($entities['id'],FILTER_SANITIZE_STRING) . '&fields[' . filter_var($fields['id'],FILTER_SANITIZE_STRING) . ']=' . filter_var($current_item_id,FILTER_SANITIZE_STRING). '&redirect_to=item_info_page' . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . '-'. filter_var($current_item_id,FILTER_SANITIZE_STRING));
								break;
						}
											
						$add_button = button_tag((strlen($subentity_cfg->get('insert_button'))>0 ? $subentity_cfg->get('insert_button') : TEXT_ADD), $url,true,array('class'=>'btn btn-primary btn-sm')) . ' ';
					}
										
					$with_selected_menu = '';
						
					if(users::has_access('export_selected',$access_schema) and users::has_access('export',$access_schema))
					{
						$with_selected_menu .= '<li>' . link_to_modalbox('<i class="fa fa-file-excel-o"></i> ' . TEXT_EXPORT,url_for('items/export','path=' . filter_var($subentity_report["entities_id"],FILTER_SANITIZE_STRING)  . '&reports_id=' . filter_var($subentity_report['id'],FILTER_SANITIZE_STRING) )) . '</li>';
					}
		
					$with_selected_menu .=  plugins::include_dashboard_with_selected_menu_items(filter_var($subentity_report['id'],FILTER_SANITIZE_STRING),'&path=' . $app_path . '/' .  filter_var($subentity_report['entities_id'],FILTER_SANITIZE_STRING) . '&redirect_to=parent_item_info_page');
		
					$html = '
					
					<div class="row info-page-reports-container" id="' . $listing_container . '_info_block" ' . ($entity_cfg->get('hide_item_page_field' . filter_var($fields['id'],FILTER_SANITIZE_STRING) . '_if_empty')==1 ? 'style="display:none"':''). '>
			      <div class="col-md-12">
		
			      <div class="portlet">
							<div class="portlet-title">
								<div class="caption">
				          ' .(strlen($entity_cfg->get('item_page_field' . filter_var($fields['id'],FILTER_SANITIZE_STRING) . '_entity' . filter_var($entities['id'],FILTER_SANITIZE_STRING) . '_heading')) ? $entity_cfg->get('item_page_field' . filter_var($fields['id'],FILTER_SANITIZE_STRING) . '_entity' . filter_var($entities['id'],FILTER_SANITIZE_STRING) . '_heading') : (strlen($subentity_cfg->get('listing_heading'))>0 ? $subentity_cfg->get('listing_heading') : filter_var($entities['name'],FILTER_SANITIZE_STRING))) . '
				        </div>
				        <div class="tools">
									<a href="javascript:;" class="collapse"></a>
								</div>
							</div>
							<div class="portlet-body">
			   
			      <div class="row">
			        <div class="col-sm-6">
			             ' . $add_button . '
			             ' . (strlen($with_selected_menu) ? '
			            <div class="btn-group">
			      				<button class="btn btn-default dropdown-toggle btn-sm" type="button" data-toggle="dropdown" data-hover="dropdown">
			      				' . TEXT_WITH_SELECTED . '<i class="fa fa-angle-down"></i>
			      				</button>
			      				<ul class="dropdown-menu" role="menu">
			      					' . $with_selected_menu . '
			      				</ul>
			      			</div>': '') .
		
			      			'</div>
			        <div class="col-sm-6">
			         ' . render_listing_search_form(filter_var($subentity_report["entities_id"],FILTER_SANITIZE_STRING),$listing_container,filter_var($subentity_report['id'],FILTER_SANITIZE_STRING),'input-small') . '
			        </div>
			      </div>
			      
			      <div id="' . $listing_container . '" class="entity_items_listing"></div>
			      ' . input_hidden_tag($listing_container . '_order_fields',$subentity_report['listing_order_fields']) .
			      input_hidden_tag($listing_container . '_has_with_selected',(strlen($with_selected_menu) ? 1:0)) .
			      input_hidden_tag($listing_container . '_force_filter_by', filter_var($fields['id'],FILTER_SANITIZE_STRING) . ':' . filter_var($current_item_id,FILTER_SANITIZE_STRING)) . 
			      input_hidden_tag($listing_container . '_redirect_to', 'item_info_page' .filter_var( $current_entity_id,FILTER_SANITIZE_STRING) . '-'. filter_var($current_item_id,FILTER_SANITIZE_STRING)) . '
			    
			      
				        </div>
				    	</div>
			      
			      </div>
			    </div>
							
						<script>
				      $(function() {
				        load_items_listing("' . $listing_container . '",1);
				      });
				    </script>
					';
			      	
			      echo $html;
			      	
				}
			}
		}
	}
}
