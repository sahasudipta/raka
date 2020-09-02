<?php

class processes{
	
	public $entities_id;
	
	public $rdirect_to;
	
	public $items_id;
	
	function __construct($entities_id)
	{
		$this->entities_id = $entities_id;
		$this->rdirect_to = 'items_info';
		$this->items_id = 0;
	}
	
	function run_after_insert($items_id)
	{	    	    	    	    
	    $this->items_id = $items_id;
	    	  	    
	    foreach($this->get_buttons_list('run_after_insert') as $process_info)
	    {	  
	        if($this->check_buttons_filters($process_info))
	        {
	            $_post_fields = $_POST['fields']; //save post fields
	            $_POST['fields'] = []; //reset post fields
	            
	            $this->run($process_info,false,true);
	            
	            $_POST['fields'] = $_post_fields; //restore post fields;
	        }
	    }
	}
	
	public function render_buttons($position, $reports_id=0)
	{
		global $app_path;
				
		$buttons_list = $this->get_buttons_list($position);
		
		$html = '';
		
		switch($position)
		{
			case 'default':
								
				foreach($buttons_list as $buttons)
				{
					if($this->check_buttons_filters($buttons))
					{
						if(strlen($buttons['payment_modules']))
						{
							$html .= '<li>' . button_tag($buttons['button_title'],url_for('items/processes_checkout','id=' . filter_var($buttons['id'],FILTER_SANITIZE_STRING) .  '&path=' . $app_path . '&redirect_to=' . $this->rdirect_to),true,array('class'=>'btn btn-primary btn-sm btn-process-' . filter_var($buttons['id'],FILTER_SANITIZE_STRING)),$buttons['button_icon']). '</li>';
						}
						else
						{
							$is_dialog = ((strlen($buttons['confirmation_text']) or $buttons['allow_comments']==1 or $buttons['preview_prcess_actions']==1 or $this->has_enter_manually_fields(filter_var($buttons['id'],FILTER_SANITIZE_STRING))) ? true:false);
							$params = (!$is_dialog ? '&action=run':'');
							$css = (!$is_dialog ? ' prevent-double-click':'');
							$html .= '<li>' . button_tag($buttons['button_title'],url_for('items/processes','id=' . filter_var($buttons['id'],FILTER_SANITIZE_STRING) .  '&path=' . $app_path . '&redirect_to=' . $this->rdirect_to . $params),$is_dialog,array('class'=>'btn btn-primary btn-sm btn-process-' . filter_var($buttons['id'],FILTER_SANITIZE_STRING) . $css),$buttons['button_icon']). '</li>';							
						}
						
						$html .= $this->prepare_button_css($buttons);
					}
				}
				
				$html .=  $this->render_buttons_by_buttons_groups_default_menu();
				
				break;
			case 'menu_more_actions':
				foreach($buttons_list as $buttons)
				{
					if($this->check_buttons_filters($buttons))
					{
						$title = (strlen($buttons['button_icon'])? app_render_icon($buttons['button_icon']) . ' ' : '') . $buttons['button_title'];
						
						$style = (strlen($buttons['button_color']) ? 'color: ' . $buttons['button_color'] :'');
						
						if(strlen($buttons['payment_modules']))
						{
							$url = url_for('items/processes_checkout','id=' . $buttons['id']. '&entity_id=' . $this->entities_id . '&path=' . $app_path . '&redirect_to=' . $this->rdirect_to );
							$html .= '<li>' . link_to_modalbox($title, $url,['style'=>$style]) . '</li>';
						}
						else
						{							
							$is_dialog = ((strlen($buttons['confirmation_text']) or $buttons['allow_comments']==1 or $buttons['preview_prcess_actions']==1 or $this->has_enter_manually_fields($buttons['id'])) ? true:false);
							$params = (!$is_dialog ? '&action=run':'');
							$url = url_for('items/processes','id=' . $buttons['id']. '&entity_id=' . $this->entities_id . '&path=' . $app_path . '&redirect_to=' . $this->rdirect_to . $params);
							
							if($is_dialog)
							{
								$html .= '<li>' . link_to_modalbox($title, $url,['style'=>$style]) . '</li>';
							}
							else
							{
								$html .= '<li>' . link_to($title, $url,['style'=>$style]) . '</li>';
							}	
						}
					}
				}
				break;
			case 'menu_with_selected':					
					if(!strlen($app_path))
					{	
						$reports_info = db_find('app_reports',$reports_id);
						$app_path = $reports_info['entities_id'];
					}
					
					foreach($buttons_list as $buttons)
					{				
						if(!strlen($buttons['payment_modules']))
						{
							$title = (strlen($buttons['button_icon'])? app_render_icon($buttons['button_icon']) . ' ' : '') . $buttons['button_title'];
							$params = '&reports_id=' . $reports_id;
							$style = (strlen($buttons['button_color']) ? 'color: ' . $buttons['button_color'] :'');
							
							$html .= '<li>' . link_to_modalbox($title, url_for('items/processes','id=' . $buttons['id']. '&entity_id=' . $this->entities_id . '&path=' . $app_path . '&redirect_to=' . $this->rdirect_to  . $params),['style'=>$style]) . '</li>';
						}
					}	
					
					$html .=  $this->render_buttons_by_buttons_groups_with_selected_menu($app_path,$reports_id);
				break;
			case 'comments_section':
				
				foreach($buttons_list as $buttons)
				{
					if($this->check_buttons_filters($buttons))
					{
						if(strlen($buttons['payment_modules']))
						{
							$html .= button_tag($buttons['button_title'],url_for('items/processes_checkout','id=' . $buttons['id'] .  '&path=' . $app_path . '&redirect_to=' . $this->rdirect_to),true,array('class'=>'btn btn-primary btn-sm btn-process-' . $buttons['id']),$buttons['button_icon']);
						}
						else
						{
							$is_dialog = ((strlen($buttons['confirmation_text']) or $buttons['allow_comments']==1 or $buttons['preview_prcess_actions']==1 or $this->has_enter_manually_fields($buttons['id'])) ? true:false);
							$params = (!$is_dialog ? '&action=run':'');
							$css = (!$is_dialog ? ' prevent-double-click':'');
							$html .=  button_tag($buttons['button_title'],url_for('items/processes','id=' . $buttons['id'] . '&path=' . $app_path . '&redirect_to=' . $this->rdirect_to . $params),$is_dialog,array('class'=>'btn btn-primary btn-sm btn-process-' . $buttons['id'] . $css),$buttons['button_icon']);
						}
						$html .= $this->prepare_button_css($buttons);
					}
				}
				break;
		}
		
		return $html;
	}
	
	public function render_buttons_by_buttons_groups_default_menu()
	{
		global $app_path;
		
		$buttons_html = '';
		$buttons_groups_query = db_query("select * from app_ext_processes_buttons_groups where entities_id='" . db_input(filter_var($this->entities_id,FILTER_SANITIZE_STRING)) . "' and find_in_set('default',button_position) order by sort_order, name");
		while($buttons_groups = db_fetch_array($buttons_groups_query))
		{				
			$html = '';
			
			$buttons_list = $this->get_buttons_list('buttons_groups_' . filter_var($buttons_groups['id'],FILTER_SANITIZE_STRING));
									
			foreach($buttons_list as $buttons)
			{												
				if($this->check_buttons_filters($buttons))
				{
					$title = app_render_icon($buttons['button_icon']) . $buttons['button_title'];
					$is_dialog = ((strlen($buttons['confirmation_text']) or $buttons['allow_comments']==1 or $buttons['preview_prcess_actions']==1 or $this->has_enter_manually_fields(filter_var($buttons['id'],FILTER_SANITIZE_STRING))) ? true:false);
					$params = (!$is_dialog ? '&action=run':'');
					$url = url_for('items/processes','id=' . filter_var($buttons['id'],FILTER_SANITIZE_STRING). '&entity_id=' . filter_var($this->entities_id,FILTER_SANITIZE_STRING) . '&path=' . $app_path . '&redirect_to=' . $this->rdirect_to . $params);
					
					$style = (strlen($buttons['button_color']) ? 'color: ' . $buttons['button_color'] :'');
					
					if($is_dialog)
					{
						$html .= '<li>' . link_to_modalbox($title, $url,['style'=>$style]) . '</li>';
					}
					else
					{
						$html .= '<li>' . link_to($title, $url,['style'=>$style]) . '</li>';
					}
				}
			}
									
			if(strlen($html))
			{									
				$buttons_html .= '
						<div class="btn-group">
							<button class="btn  btn-sm dropdown-toggle btn-primary btn-process-groups-' . filter_var($buttons_groups['id'],FILTER_SANITIZE_STRING) .'" type="button" data-toggle="dropdown" data-hover="dropdown" aria-expanded="false">
							' . (strlen(filter_var($buttons_groups['button_icon'],FILTER_SANITIZE_STRING)) ? app_render_icon(filter_var($buttons_groups['button_icon'],FILTER_SANITIZE_STRING)) . ' ':'') . filter_var($buttons_groups['name'],FILTER_SANITIZE_STRING) . ' <i class="fa fa-angle-down"></i>
							</button>
							<ul class="dropdown-menu" role="menu">                                       
								' . $html . '									
							</ul>
						</div>
						';
				
				$buttons_html .= $this->prepare_button_css(filter_var_array($buttons_groups),'groups-');
								
			}	
		}
		
		return $buttons_html;
	}
	
	
	public function render_buttons_by_buttons_groups_with_selected_menu($path,$reports_id)
	{
		global $app_path;
	
		$buttons_html = '';
		$buttons_groups_query = db_query("select * from app_ext_processes_buttons_groups where entities_id='" . filter_var($this->entities_id,FILTER_SANITIZE_STRING) . "' and find_in_set('menu_with_selected',button_position) order by sort_order, name");
		while($buttons_groups = db_fetch_array($buttons_groups_query))
		{
			$html = '';
				
			$buttons_list = $this->get_buttons_list('buttons_groups_' . filter_var($buttons_groups['id'],FILTER_SANITIZE_STRING));
				
			foreach($buttons_list as $buttons)
			{			
				$title = app_render_icon($buttons['button_icon']) . $buttons['button_title'];
				$params = '&reports_id=' . $reports_id;
				$url = url_for('items/processes','id=' . $buttons['id']. '&entity_id=' . $this->entities_id . '&path=' . $path . '&redirect_to=' . $this->rdirect_to . $params);
					
				$style = (strlen($buttons['button_color']) ? 'color: ' . $buttons['button_color'] :'');
									
				$html .= '<li>' . link_to_modalbox($title, $url,['style'=>$style]) . '</li>';				
				
			}
				
			if(strlen($html))
			{				
				$buttons_html .= '
						<li class="dropdown-submenu">
							<a href="#" ' . (strlen(filter_var($buttons_groups['button_color'],FILTER_SANITIZE_STRING)) ? 'style="color: ' . filter_var($buttons_groups['button_color'],FILTER_SANITIZE_STRING) . '"' :'') . '>' . (strlen(filter_var($buttons_groups['button_icon'],FILTER_SANITIZE_STRING)) ? app_render_icon(filter_var($buttons_groups['button_icon'],FILTER_SANITIZE_STRING)) . ' ':'') . filter_var($buttons_groups['name'],FILTER_SANITIZE_STRING) . '</a>
							<ul class="dropdown-menu">
									' . $html . '
							</ul>
						</li>
						';		
			}
		}
	
		return $buttons_html;
	}	
	
	public function has_enter_manually_fields($process_id)
	{
		$check_query = db_query("select count(*) as total from app_ext_processes_actions_fields af where af.enter_manually in (1,2) and af.actions_id in (select pa.id from app_ext_processes_actions pa where pa.process_id='" . $process_id . "')");
		$check = db_fetch_array($check_query);
								
		return (($check['total']>0 or $this->has_move_action($process_id) or $this->has_copy_action($process_id) or $this->has_clone_action_to_nested_entity($process_id)) ? true:false);
	}
	
	public function has_move_action($process_id)
	{
		$check_qeury = db_query("select count(*) as total  from app_ext_processes_actions where process_id='" . $process_id . "' and locate('move_item_entity_',type)>0");
		$check = db_fetch_array($check_qeury);
						
		return ($check['total']>0 ? true:false);
	}
	
	public function has_clone_action_to_nested_entity($process_id)
	{
		global $app_entities_cache;
		
		$actions_qeury = db_query("select settings  from app_ext_processes_actions where process_id='" . $process_id . "' and locate('clone_item_entity_',type)>0");
		while($actions = db_fetch_array($actions_qeury))
		{
						
			$settigns = new settings($actions['settings']);
			
			if(is_array($settigns->get('clone_to_entity')))
			{
				if($app_entities_cache[current($settigns->get('clone_to_entity'))]['parent_id']>0)
				{
					return true;
				}
			}					
		}
	
		return false;
	}
	
	public function has_copy_action($process_id,$check_parent = true)
	{
		global $app_entities_cache;
		
		$check_qeury = db_query("select count(*) as total  from app_ext_processes_actions where process_id='" . $process_id . "' and locate('copy_item_entity_',type)>0");
		$check = db_fetch_array($check_qeury);
		
		if($check_parent)
		{
			$process_query = db_query("select entities_id from app_ext_processes where id = '" . $process_id . "'");
			$process = db_fetch_array($process_query);
			
			return (($app_entities_cache[$process['entities_id']]['parent_id']>0 and $check['total']>0) ? true:false);			
		}
		else
		{
			return ($check['total']>0 ? true:false);
		}
	}
	
	public function get_buttons_list($position='')
	{
		global $app_user, $app_fields_cache;
		
		$buttons_list = array();
		
		$buttons_query = db_query("select *, if(length(button_title)>0,button_title,name) as button_title from app_ext_processes where " . (strlen($position) ? "find_in_set('" . $position . "',button_position) and ":'') . " entities_id='"  . filter_var($this->entities_id,FILTER_SANITIZE_STRING) . "' and is_active=1 order by sort_order, name");
		while($buttons = db_fetch_array($buttons_query))
		{			
			$has_access = false;
			
			//check access to assigned groups
			if(strlen($buttons['users_groups']))
			{
				$has_access = in_array($app_user['group_id'],explode(',',filter_var($buttons['users_groups'],FILTER_SANITIZE_STRING)));
			}
			
			//check access to assigned users
			if(strlen(filter_var($buttons['assigned_to'])) and !$has_access)
			{
				$has_access = in_array($app_user['id'],explode(',',filter_var($buttons['assigned_to'],FILTER_SANITIZE_STRING)));
			}
			
			//check assess to assigned users in item
			if(strlen(filter_var($buttons['access_to_assigned'])) and $this->items_id>0 and !$has_access)
			{								
			    $item_info_query = db_query("select e.* from app_entity_" . filter_var($this->entities_id,FILTER_SANITIZE_STRING) . " e  where e.id='" . db_input(filter_var($this->items_id,FILTER_SANITIZE_STRING)) . "'");
				if($item_info = db_fetch_array($item_info_query))
				{
					foreach(explode(',',filter_var($buttons['access_to_assigned'])) as $field_id)
					{														
						$field_info_query = db_query("select type, configuration from app_fields where id='" . db_input(filter_var($field_id,FILTER_SANITIZE_STRING)). "'");
						if($field_info = db_fetch_array($field_info_query))
						{				
							$cfg = new fields_types_cfg(filter_var($field_info['configuration'],FILTER_SANITIZE_STRING));
							
							switch($field_info['type'])
							{
								case 'fieldtype_grouped_users':
									if(strlen($item_info['field_' . $field_id]))
									{										
										foreach(explode(',',$item_info['field_' . $field_id]) as $choices_id)
										{
											if($cfg->get('use_global_list')>0)
											{
												$choice_query = db_query("select * from app_global_lists_choices where id='" . db_input(filter_var($choices_id,FILTER_SANITIZE_STRING)) . "' and lists_id = '" . db_input(filter_var($cfg->get('use_global_list'),FILTER_SANITIZE_STRING)) . "' and length(users)>0 and find_in_set(" . filter_var($app_user['id'],FILTER_SANITIZE_STRING) . ",users)");
											}
											else 
											{
												$choice_query = db_query("select * from app_fields_choices where id='" . db_input(filter_var($choices_id,FILTER_SANITIZE_STRING)) . "' and length(users)>0 and find_in_set(" . filter_var($app_user['id'],FILTER_SANITIZE_STRING) . ",users)");												
											}
											
											if($choice = db_fetch_array($choice_query))
											{
												$has_access = true;
											}											
										}
									}
									break;
								case 'fieldtype_created_by':									
									$has_access = ($app_user['id']==$item_info['created_by'] ? true:false);
									break;
								default:
									if(strlen($item_info['field_' . $field_id]))
									{										
										$has_access = in_array($app_user['id'],explode(',',$item_info['field_' . $field_id]));
									}
									break;
							}
						}
						
						//stop checking if has access;
						if($has_access) break;
																				
					}
				}								
			}
			
			if($has_access)
			{
				$buttons_list[] = filter_var_array($buttons);
			}
		}	
						
		return $buttons_list;
	}
	
	public function check_buttons_filters($buttons)
	{
		global $sql_query_having;
		
		$reports_info_query = db_query("select * from app_reports where entities_id='" . db_input(filter_var($buttons['entities_id'],FILTER_SANITIZE_STRING)). "' and reports_type='process" . filter_var($buttons['id'],FILTER_SANITIZE_STRING) . "'");
		if($reports_info = db_fetch_array($reports_info_query))
		{
			$listing_sql_query = '';
			$listing_sql_query_select = '';
			$listing_sql_query_having = '';
			$sql_query_having = array();
			
			//prepare forumulas query
			$listing_sql_query_select = fieldtype_formula::prepare_query_select(filter_var($this->entities_id,FILTER_SANITIZE_STRING), $listing_sql_query_select);
			
			$listing_sql_query = reports::add_filters_query(filter_var($reports_info['id'],FILTER_SANITIZE_STRING),$listing_sql_query);
			
			//prepare having query for formula fields
			if(isset($sql_query_having[$this->entities_id]))
			{
			    $listing_sql_query_having  = reports::prepare_filters_having_query($sql_query_having[filter_var($this->entities_id,FILTER_SANITIZE_STRING)]);
			}
			
			$listing_sql_query .= $listing_sql_query_having;
			
			$item_info_sql = "select e.* " . $listing_sql_query_select . " from app_entity_" . filter_var($buttons['entities_id'],FILTER_SANITIZE_STRING) . " e  where e.id='" . db_input(filter_var($this->items_id,FILTER_SANITIZE_STRING)) . "' " . $listing_sql_query;
			
			$item_info_query = db_query($item_info_sql);
			if($item_info = db_fetch_array($item_info_query))
			{
				return true;
			}
			else
			{
				return false;
			}
			
		}
		else 
		{			
			return true;
		}
	}
	
	public function prepare_button_css($buttons,$css_class = '')
	{
		$css = '';
		
		if(strlen($buttons['button_color']))
		{
			$rgb = convert_html_color_to_RGB($buttons['button_color']);
			$rgb[0] = $rgb[0]-25;
			$rgb[1] = $rgb[1]-25;
			$rgb[2] = $rgb[2]-25;
			$css = '
					<style>
						.btn-process-' . $css_class. $buttons['id'] . '{
							background-color: ' . $buttons['button_color'] . '; 
						  border-color: ' . $buttons['button_color'] . ';
						}
						.btn-primary.btn-process-' . $css_class . $buttons['id'] . ':hover,
						.btn-primary.btn-process-' . $css_class . $buttons['id'] . ':focus,
						.btn-primary.btn-process-' . $css_class . $buttons['id'] . ':active,
						.btn-primary.btn-process-' . $css_class . $buttons['id'] . '.active,								
						.open .dropdown-toggle.btn-process-' . $css_class . $buttons['id'] . '
						{							
						  background-color: rgba(' . $rgb[0] . ',' . $rgb[1] . ',' . $rgb[2] . ',1); 
						  border-color: rgba(' . $rgb[0] . ',' . $rgb[1] . ',' . $rgb[2] . ',1);
						}
					</style>		
			';
		}
		
		return $css;
	}
	
	public function preapre_values_from_current_item($sql_data, $process_info,$item_id)
	{
		global $sql_data_holder, $item_info_holder, $app_user;
						
		$check = false;

		//check if there are values to replace
		foreach($sql_data as $k=>$v)
		{			
			if(isset($sql_data_holder[$k]))
			{
				$v = $sql_data_holder[$k];
			}
			
			if(preg_match('/\[\d+\]/', $v) or strstr($v,'[created_by]') or strstr($v,'[current_user_id]'))
			{
				$check = true; 
			}	
		}	
		
		//print_r($sql_data);
		//echo $check;
		//exit();
		
		
		if($check)
		{
			if(!isset($item_info_holder[$item_id]))
			{
				$item_info_query = db_query("select e.* " . fieldtype_formula::prepare_query_select(filter_var($process_info['entities_id'],FILTER_SANITIZE_STRING), '') . " from app_entity_" . filter_var($process_info['entities_id'],FILTER_SANITIZE_STRING) . " e  where e.id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");
				$item_info_holder[$item_id] = $item_info = db_fetch_array($item_info_query);
			}
			else
			{
				$item_info = $item_info_holder[$item_id]; 
			}
			
			//echo 'item_id=' . $item_id;
			//print_r($item_info);
						
			foreach($sql_data as $k=>$v)
			{
				//hold first sql data and use it for next items
				if(!isset($sql_data_holder[$k]))
				{
					$sql_data_holder[$k] = $v;
				}
				else
				{
					$v = $sql_data_holder[$k];
				}
				
				if(preg_match_all('/\[(\d+)\]/', $v,$matches))
				{												
					foreach($matches[1] as $matches_key=>$fields_id)
					{
					  $v = str_replace('[' . $fields_id . ']',$item_info['field_' . $fields_id], $v);
					}																		
					
					$sql_data[$k] = $v;
				}
				
				//use created_by value for users
				if(strstr($v,'[created_by]'))
				{
					$v = trim(str_replace('[created_by]',$item_info['created_by'], $v));
					$sql_data[$k] = $v;
				}
				
				//use current user ID
				if(strstr($v,'[current_user_id]'))
				{
					$v = trim(str_replace('[current_user_id]',$app_user['id'], $v));
					$sql_data[$k] = $v;
				}
				
			}				
		}
		
		//print_r($sql_data);		
		//exit();
					
		return $sql_data;
	}
	
	public function apply_button_filter_to_selected_items($process_info, $selected_items)
	{
		global $sql_query_having;
		
		$reports_info_query = db_query("select * from app_reports where entities_id='" . db_input(filter_var($process_info['entities_id'],FILTER_SANITIZE_STRING)). "' and reports_type='process" . filter_var($process_info['id'],FILTER_SANITIZE_STRING) . "'");
		if($reports_info = db_fetch_array($reports_info_query))
		{
			$current_entity_id = $process_info['entities_id'];
			
			$listing_sql_query = '';
			$listing_sql_query_select = '';
			$listing_sql_query_having = '';
			$sql_query_having = array();
				
			//prepare forumulas query
			$listing_sql_query_select = fieldtype_formula::prepare_query_select(filter_var($current_entity_id,FILTER_SANITIZE_STRING), $listing_sql_query_select);
				
			$listing_sql_query = reports::add_filters_query(filter_var($reports_info['id'],FILTER_SANITIZE_STRING),$listing_sql_query);
				
			//prepare having query for formula fields
			if(isset($sql_query_having[$current_entity_id]))
			{
				$listing_sql_query_having  = reports::prepare_filters_having_query($sql_query_having[filter_var($current_entity_id,FILTER_SANITIZE_STRING)]);
			}
										
			$listing_sql_query .= $listing_sql_query_having;
				
			$item_info_sql = "select e.* " . $listing_sql_query_select . " from app_entity_" . filter_var($current_entity_id,FILTER_SANITIZE_STRING) . " e  where e.id in (" . implode(',', filter_var_array($selected_items)) . ")" . $listing_sql_query;
				
			$filtered_selected_items = array();
			$items_query = db_query($item_info_sql,false);
			while($items = db_fetch_array($items_query))
			{
				$filtered_selected_items[] = $items['id'];
			}
			
			$selected_items = $filtered_selected_items; 						
		}
		
		//print_r($selected_items);
		//exit();
		
		return $selected_items;
	}
		
	public function run($process_info, $reports_id = false, $is_ipn = false)
	{
		global $app_path, $app_redirect_to, $app_user, $app_selected_items, $alerts, $sql_data_holder, $item_info_holder, $current_item_id, $app_entities_chace;
		
		if(!$reports_id)
		{
			$selected_items = array($this->items_id);
		}
		else 
		{
			if(count($app_selected_items[$reports_id]))
			{
				$selected_items = $app_selected_items[$reports_id];
				
				//apply filters if setup
				$selected_items = $this->apply_button_filter_to_selected_items($process_info, $selected_items);
			}
			else
			{
				die(TEXT_PLEASE_SELECT_ITEMS);
			}			
		}	
		
		//include sms modules
		$modules = new modules('sms');
		$modules = new modules('mailing');
		
		$actions_query = db_query("select pa.*, p.name as process_name, p.entities_id from app_ext_processes_actions pa, app_ext_processes p where pa.process_id='" . filter_var($process_info['id'],FILTER_SANITIZE_STRING). "' and  p.id=pa.process_id order by pa.sort_order");
		while($actions = db_fetch_array($actions_query))
		{						
			$action_entity_id = self::get_entity_id_from_action_type(filter_var($actions['type'],FILTER_SANITIZE_STRING));
			$action_entity_cfg = new entities_cfg(filter_var($action_entity_id,FILTER_SANITIZE_STRING));
			
			//check fields access
			$fields_access_schema = users::get_fields_access_schema(filter_var($action_entity_id,FILTER_SANITIZE_STRING),filter_var($app_user['group_id'],FILTER_SANITIZE_STRING));
															
			$sql_data = array();
			$sql_data_holder = array();
			
			$actions_fields_list = array();
			
			$actions_fields_query = db_query("select af.enter_manually, af.id, af.fields_id, af.value, f.name, f.type from app_ext_processes_actions_fields af, app_fields f left join app_forms_tabs t on f.forms_tabs_id=t.id  where f.id=af.fields_id and af.actions_id='" . db_input(filter_var($actions['id'],FILTER_SANITIZE_STRING)) ."' order by t.sort_order, t.name, f.sort_order, f.name");
			while($actions_fields = db_fetch_array($actions_fields_query))
			{	
				//skip fields if no edit access
				if(isset($fields_access_schema[filter_var($actions_fields['fields_id'],FILTER_SANITIZE_STRING)]) and $process_info['apply_fields_access_rules']==1 and in_array($actions_fields['enter_manually'],[1,2])) continue;
				
				//handle manually entered field
				if(isset($_POST['fields'][$actions_fields['fields_id']]) or isset($_FILES['fields']['name'][$actions_fields['fields_id']]))
				{
					$field = db_find('app_fields',filter_var($actions_fields['fields_id'],FILTER_SANITIZE_STRING));
					$value = isset($_POST['fields'][$actions_fields['fields_id']]) ? filter_var($_POST['fields'],FILTER_SANITIZE_STRING)[$actions_fields['fields_id']] : '';
					
					//prepare process options
					$process_options = array(
							'class'          => filter_var($field['type'],FILTER_SANITIZE_STRING),
							'value'          => filter_var($value,FILTER_SANITIZE_STRING),
							'fields_cache'   => array(),
							'field'          => filter_var_array($field),
							'is_new_item'    => true,
							'current_field_value' => '',
					);
					
					$actions_fields['value'] = fields_types::process($process_options);
				}
				else 
				{					
					//handle dates
					if($actions_fields['type'] == 'fieldtype_input_date')
					{
						$actions_fields['value'] = ($actions_fields['value']==' ' ? 0 : (strlen($actions_fields['value'])<5 ? get_date_timestamp(date('Y-m-d',strtotime($actions_fields['value'] . ' day'))) : $actions_fields['value']));
					}
					elseif($actions_fields['type']=='fieldtype_input_datetime')
					{					
						$actions_fields['value'] = ($actions_fields['value']==' ' ? 0 : (strlen($actions_fields['value'])<5 ? strtotime($actions_fields['value'] . ' day') : $actions_fields['value']));
					}			
				}
				
				switch($actions_fields['type'])
				{
				    case 'fieldtype_users_approve':
				        if(strlen($actions_fields['value']))
				        {
				            db_query("delete from app_approved_items where entities_id='" . db_input(filter_var($action_entity_id,FILTER_SANITIZE_STRING)) . "' and items_id in (" . implode(',',filter_var_array($selected_items)) . ") and fields_id='" . db_input(filter_var($actions_fields['fields_id'],FILTER_SANITIZE_STRING)) . "' and users_id not in (" . db_input(filter_var($actions_fields['value'],FILTER_SANITIZE_STRING)) . ")");
				        }
				        else
				        {
				            db_query("delete from app_approved_items where entities_id='" . db_input(filter_var($action_entity_id,FILTER_SANITIZE_STRING)) . "' and items_id in (" . implode(',',filter_var_array($selected_items)) . ") and fields_id='" . db_input(filter_var($actions_fields['fields_id'],FILTER_SANITIZE_STRING)) . "'");
				        }
				        $sql_data['field_' . filter_var($actions_fields['fields_id'],FILTER_SANITIZE_STRING)] = $actions_fields['value'];
				        break;
					case 'fieldtype_created_by':
						$sql_data['created_by'] = filter_var($actions_fields['value'],FILTER_SANITIZE_STRING);
						break;
					default:
						$sql_data['field_' . filter_var($actions_fields['fields_id'],FILTER_SANITIZE_STRING)] = filter_var($actions_fields['value'],FILTER_SANITIZE_STRING);
						break;
				}
												
				
				//prepare choices values for fields with multiple values
				$actions_fields_list[] = $actions_fields;
								
			}
			
			//paretn item for move action
			if(isset($_POST['parent_item_id']) and strstr($actions['type'],'move_item_entity_'))
			{
				$sql_data['parent_item_id'] = filter_var(_post::int('parent_item_id'),FILTER_SANITIZE_STRING);
			}
				
			
			//print_rr($_POST);
			//print_rr($actions_fields_list);
			//print_rr($sql_data);
			//exit();
			
			//print_r($selected_items);
			
			if(count($sql_data) or strstr($actions['type'],'edit_item_entity_') or strstr($actions['type'],'copy_item_entity_') or strstr($actions['type'],'clone_subitems_linked_entity_') or strstr($actions['type'],'clone_item_entity_') or strstr($actions['type'],'link_records_by_mysql_query_')) 
			{ 
				foreach($selected_items as $item_id)
				{	
					//echo '<pre>';
					//echo 'item=' . $item_id . ' - acton id = ' . $actions['id'];
					
					//handle values from current item
					$sql_data = $this->preapre_values_from_current_item($sql_data, $process_info, filter_var($item_id,FILTER_SANITIZE_STRING));
					
					//prepare choices values for fields with multiple values
					$choices_values = new choices_values($action_entity_id);
					
					foreach(filter_var_arry($actions_fields_list) as $actions_fields)
					{
						if(isset($sql_data['field_' . $actions_fields['fields_id']]))
						{							
							$process_options = array(
									'class'=>$actions_fields['type'],
									'field'=>array('id'=>$actions_fields['fields_id']),
									'value'=>explode(',',$sql_data['field_' . $actions_fields['fields_id']])
							);
								
							$choices_values->prepare($process_options);
						}
					}
					
					//echo '<pre>';
					//print_r($actions_fields_list);
					//print_r($choices_values);
					//print_r($sql_data);
					//exit();
					//continue;	
													
									
					switch(true)
					{
						case strstr($actions['type'],'move_item_entity_'):
						case strstr($actions['type'],'edit_parent_item_entity_'):
						case strstr($actions['type'],'edit_item_entity_'):
							 
							//redefine $item_id, get parent_item_id value from selected item 
							 if(strstr($actions['type'],'edit_parent_item_entity_'))
							 {							 	 
							 	 $item_info_query = db_query("select parent_item_id from app_entity_" . filter_var($process_info['entities_id'],FILTER_SANITIZE_STRING) . " where id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");
							 	 $item_info = db_fetch_array($item_info_query);
							 	 $item_id = filter_var($item_info['parent_item_id'],FILTER_SANITIZE_STRING);
							 }
							 
							 $has_comment = false;
								
							 //get previous item info
							 $item_info_query = db_query("select * from app_entity_" . filter_var($action_entity_id,FILTER_SANITIZE_STRING) . " where id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");
							 $item_info = db_fetch_array($item_info_query);
							 																				 
							 if(count($sql_data))
							 {							 	 							 	
								 //update item
							 	 $sql_data['date_updated'] = time();
								 db_perform('app_entity_' . $action_entity_id,$sql_data,'update',"id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");
								 
								 //insert choices values for fields with multiple values
								 $choices_values->process(filter_var($item_id,FILTER_SANITIZE_STRING));
								 
								 //prepare user roles
								 fieldtype_user_roles::set_user_roles_to_items(filter_var($action_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
								 								 								 
								 //autoupdate all field types
      					 fields_types::update_items_fields(filter_var($action_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
								 								 								 								 								
								 //check public form notification
								 public_forms::send_client_notification(filter_var($action_entity_id,FILTER_SANITIZE_STRING), $item_info);
								 
								 $has_comment = true;
							 }
							 else 
							 {							 
								 //atuoset fieldtype autostatus
								 fieldtype_autostatus::set(filter_var($action_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
							 }
							
							//send sms notification
							$sms = new sms(filter_var($action_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
							$sms->send_to = false;
							$sms->send_edit_msg($item_info);
							
							//email rules
							$email_rules = new email_rules(filter_var($action_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
							$email_rules->send_edit_msg($item_info);
							
							//reset signatures
							fieldtype_digital_signature::reset_signature_if_data_changed(filter_var($action_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING), $item_info);
							 							 							 							 							 									 									 		
					 	  $attachments = '';
					 		$description = (isset($_POST['description']) ? $_POST['description'] : '');
					 		
						 	if(isset($_POST['fields']['attachments']) or strlen($description))
						 	{
						 		$attachments = (isset($_POST['fields']['attachments']) ? $_POST['fields']['attachments'] : '');								 
						 		$description = $_POST['description'];
						 		
						 		$has_comment = true;
						 	}
						 	
						 	//check if there are fields to update in comments
						 	if(isset($_POST['fields']))
						 	{
							 	foreach(filter_var_array($_POST['fields']) as $k=>$v)
							 	{
							 		if(is_array($v))
							 		{
							 			if(count($v)) $has_comment = true;							 			
							 		}
							 		elseif(strlen($v))
							 		{
							 			$has_comment = true;
							 		}
							 	}
						 	}
						 	
						 	//disable comments
						 	if(($process_info['disable_comments']==1 and !strlen($description)) or $action_entity_cfg->get('use_comments')!=1) $has_comment = false;
						 	
						 	if($has_comment)
						 	{								 	
						 		$sql_data_comments = array(
						 				'description'=>db_prepare_html_input($description),
						 				'entities_id'=>filter_var($action_entity_id,FILTER_SANITIZE_STRING),
						 				'items_id'=>filter_var($item_id,FILTER_SANITIZE_STRING),
						 				'attachments'=>fields_types::process(array('class'=>'fieldtype_attachments','value'=>$attachments)),
						 		);
						 
						 		$sql_data_comments['date_added'] = time();
						 		$sql_data_comments['created_by'] = filter_var($app_user['id'],FILTER_SANITIZE_STRING);
						 			
						 		db_perform('app_comments',$sql_data_comments);
						 
						 		$comments_id = db_insert_id();
						 		
						 		//insert comments history						 		
						 		$track_fields = array();
						 		foreach($sql_data as $field=>$value)
						 		{							 			
						 			db_perform('app_comments_history',array('comments_id'=>filter_var($comments_id,FILTER_SANITIZE_STRING),'fields_id'=>str_replace('field_','',$field),'fields_value'=>$value));
						 			
						 			$track_fields[str_replace('field_','',$field)] = $value;
						 		}
						 		
						 		//
						 		if(strstr($actions['type'],'move_item_entity_'))
						 		{
						 			$field_query = db_query("select id from app_fields where type='fieldtype_parent_item_id' and entities_id='" . filter_var($action_entity_id,FILTER_SANITIZE_STRING) . "'");
						 			$field = db_fetch_array($field_query);
						 			
						 			db_perform('app_comments_history',array('comments_id'=>filter_var($comments_id,FILTER_SANITIZE_STRING),'fields_id'=>filter_var($field['id'],FILTER_SANITIZE_STRING),'fields_value'=>filter_var(_post::int('parent_item_id'),FILTER_SANITIZE_STRING)));
						 		}
						 									 		
						 		//prepare input numeric in comments						 		
						 		$sql_data_item = array();
						 		$fields_query = db_query("select f.* from app_fields f where f.type  in ('fieldtype_input_numeric_comments','fieldtype_time') and  f.entities_id='" . db_input(filter_var($action_entity_id,FILTER_SANITIZE_STRING)) . "' and f.comments_status=1 order by f.comments_sort_order, f.name");
						 		while($v = db_fetch_array($fields_query))
						 		{
						 			$value = (isset($_POST['fields'][filter_var($v['id'],FILTER_SANITIZE_STRING)]) ? filter_var($_POST['fields'],FILTER_SANITIZE_STRING)[filter_var($v['id'],FILTER_SANITIZE_STRING)] : 0);
						 			
						 			if($value>0)
						 			{									 				
						 				db_perform('app_comments_history',array('comments_id'=>filter_var($comments_id,FILTER_SANITIZE_STRING),'fields_id'=>filter_var($v['id'],FILTER_SANITIZE_STRING),'fields_value'=>$value));
						 				
						 				$filed_type = new fieldtype_input_numeric_comments;
						 				$sql_data_item['field_' . filter_var($v['id'],FILTER_SANITIZE_STRING)] = $filed_type->get_fields_sum(filter_var($action_entity_id,FILTER_SANITIZE_STRING),filter_var($item_id,FILTER_SANITIZE_STRING),filter_var($v['id'],FILTER_SANITIZE_STRING));
						 				
						 				$track_fields[filter_var($v['id'],FILTER_SANITIZE_STRING)] = $value;
						 			}
						 		}
						 									 									 		
						 		//update item
						 		if(count($sql_data_item))
						 		{						 									 									 			
						 			db_perform('app_entity_' . filter_var($action_entity_id,FILTER_SANITIZE_STRING),$sql_data_item,'update',"id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");						 									 			
						 		}
						 
						 		//send notificaton
						 		app_send_new_comment_notification(filter_var($comments_id,FILTER_SANITIZE_STRING),filter_var($item_id,FILTER_SANITIZE_STRING),filter_var($action_entity_id,FILTER_SANITIZE_STRING));	
						 								 								 		
						 		//track changes
						 		$log = new track_changes(filter_var($action_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING));
						 		
						 		if(strstr($actions['type'],'move_item_entity_'))
						 		{
						 			$log->log_move(_post::int('parent_item_id'));
						 		}
						 		
						 		if(strlen($description))
						 		{
						 			$log->log_comment($comments_id,$track_fields);
						 		}
						 		elseif(count($track_fields))
						 		{
						 			$log->log_update($item_info);						 			
						 		}
						 		
						 	}
						 							 								 							 							 							 
							break;
							
						case strstr($actions['type'],'copy_item_entity_'):								
								$settigns = new settings($actions['settings']);
								
								$copy_process = new items_copy(filter_var($action_entity_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING),$settigns->get_settings());
								
								//set paretn
								if(isset($_POST['parent_item_id']))
								{
									if($_POST['parent_item_id']>0)
									{
										$copy_process->set_parent_item_id(filter_var(_post::int('parent_item_id'),FILTER_SANITIZE_STRING));
									}
								}
								
								//set sql data
								$copy_process->set_sql_data($sql_data);
								
								if($new_item_id = $copy_process->run() and count($selected_items)==1)
								{								
									$app_redirect_to=='items_info';
									$app_path = filter_var($action_entity_id,FILTER_SANITIZE_STRING) . '-' . $new_item_id;
								}
								
								//autoupdate all field types
								fields_types::update_items_fields(filter_var($action_entity_id,FILTER_SANITIZE_STRING), $new_item_id);
																						
							break;
							
							
						case strstr($actions['type'],'clone_item_entity_'):
							
							clone_subitems::clone_process(filter_var($actions['id'],FILTER_SANITIZE_STRING),0, filter_var($item_id,FILTER_SANITIZE_STRING), (isset($_POST['parent_item_id']) ? filter_var(_post::int('parent_item_id'),FILTER_SANITIZE_STRING):0),'id');
																			
							break;
							
						case strstr($actions['type'],'clone_subitems_linked_entity_'):
							$value = explode('_',str_replace('clone_subitems_linked_entity_','',$actions['type']));
							$field_info_query = db_query("select id, configuration,type from app_fields where id='"  . db_input(filter_var($value[1],FILTER_SANITIZE_STRING)) . "'");
							if($field_info = db_fetch_array($field_info_query))
							{								
								$use_field_name = "field_" . $field_info['id'];
								$item_info_query = db_query("select parent_item_id, ".filter_var($use_field_name,FILTER_SANITIZE_STRING)." from app_entity_" . filter_var($actions['entities_id'],FILTER_SANITIZE_STRING) . " where id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");
								if($item_info = db_fetch_array($item_info_query))
								{
									if(strlen($item_info[$use_field_name]))
									{
										foreach(explode(',',filter_var($item_info[$use_field_name],FILTER_SANITIZE_STRING)) as $linked_item_id)
										{	
											clone_subitems::clone_process($actions['id'],0, $linked_item_id, $item_id);
										}
									}
								}
							}
							
							//exit();
							
							break;
																														
						case strstr($actions['type'],'edit_item_linked_entity_'):
							$value = explode('_',str_replace('edit_item_linked_entity_','',$actions['type']));
							$field_info_query = db_query("select id, configuration,type from app_fields where id='"  . filter_var($value[1],FILTER_SANITIZE_STRING) . "'");
							if($field_info = db_fetch_array($field_info_query))
							{			
								$use_field_name = ($field_info['type']=='fieldtype_created_by' ? 'created_by' : "field_" . $field_info['id'] );
								$item_info_query = db_query("select " . filter_var($use_field_name,FILTER_SANITIZE_STRING)." from app_entity_" . filter_var($actions['entities_id'],FILTER_SANITIZE_STRING) . " where id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");
								if($item_info = db_fetch_array($item_info_query))
								{									
									if(strlen($item_info[$use_field_name]))
									{																				
										foreach(explode(',',$item_info[$use_field_name]) as $linked_item_id)
										{	
											//get previous item info
											$item_info_query = db_query("select * from app_entity_" . filter_var($action_entity_id,FILTER_SANITIZE_STRING) . " where id='" . db_input(filter_var($linked_item_id,FILTER_SANITIZE_STRING)) . "'");
											$item_info = db_fetch_array($item_info_query);
											
											//update item
											db_perform('app_entity_' . $action_entity_id,$sql_data,'update',"id='" . db_input($linked_item_id) . "'");
											$choices_values->process($linked_item_id);
											
											//autoupdate all field types
											fields_types::update_items_fields($action_entity_id, $linked_item_id);
																																	
											//send sms notification
											$sms = new sms($action_entity_id, $linked_item_id);
											$sms->send_to = false;
											$sms->send_edit_msg($item_info);
												
											//email rules
											$email_rules = new email_rules($action_entity_id, $linked_item_id);
											$email_rules->send_edit_msg($item_info);																						
										}
									}
								}
																
							}														
							break;
							
						case strstr($actions['type'],'insert_item_linked_entity_'):
							$value = explode('_',str_replace('insert_item_linked_entity_','',$actions['type']));
							$field_info_query = db_query("select type, id, configuration from app_fields where id='"  . filter_var($value[1],FILTER_SANITIZE_STRING) . "'");
							if($field_info = db_fetch_array($field_info_query))
							{
								$item_info_query = db_query("select parent_item_id, field_" . filter_var($field_info['id'],FILTER_SANITIZE_STRING) . " from app_entity_" . filter_var($actions['entities_id'],FILTER_SANITIZE_STRING) . " where id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'");
								if($item_info = db_fetch_array($item_info_query))
								{
									//prepare data before insert
									$sql_data['parent_item_id'] = ($app_entities_chace[$actions['entities_id']]['parent_id']==$app_entities_chace[$action_entity_id]['parent_id'] ? $item_info['parent_item_id']: 0);
									$sql_data['created_by'] = $app_user['id'];
									$sql_data['date_added'] = time();
										
									$sql_data = $this->prepare_field_type_random_value($sql_data, $action_entity_id);
										
									//insert new item
									db_perform('app_entity_' . $action_entity_id,$sql_data);
									
									//insert choices values for fields with multiple values
									$new_item_id = db_insert_id();
									$choices_values->process($new_item_id);
										
									//autoupdate all field types
									fields_types::update_items_fields($action_entity_id, $new_item_id);
									
									//run actions after item insert
									$processes = new processes($action_entity_id);
									$processes->run_after_insert($new_item_id);
										
									//send nofitication
									items::send_new_item_nofitication($action_entity_id, $new_item_id);																		
										
									//log changeds
									$log = new track_changes($action_entity_id, $new_item_id);
									$log->log_insert();
									
									//subscribe									
									$mailing = new mailing($action_entity_id, $new_item_id);
									$mailing->subscribe();
																		
									//update current item value
									$value = (strlen($item_info['field_' . $field_info['id']]) ? $item_info['field_' . $field_info['id']] . ',' : '') . $new_item_id;
									$sql_data = ['field_' . $field_info['id'] => $value];
								
									$cv = new choices_values($actions['entities_id']);
									$process_options = array(
											'class'=>$field_info['type'],
											'field'=>array('id'=>$field_info['id']),
											'value'=>explode(',',$value)
									);
									
									$cv->prepare($process_options);
									
									//update item
									db_perform('app_entity_' . $actions['entities_id'],$sql_data,'update',"id='" . db_input($item_id) . "'");
									$cv->process($item_id);
									
								}
						
							}
							break;
																																		
						case strstr($actions['type'],'edit_item_subentity_'):
							
							//get filtered items and skip and if no items found
							if(($filtered_items = $this->include_filtered_items($actions,$item_id))===false)
							{
								break;
							}
							
							db_perform('app_entity_' . filter_var($action_entity_id,FILTER_SANITIZE_STRING),$sql_data,'update',"parent_item_id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'" . (strlen(filter_var($filtered_items,FILTER_SANITIZE_STRING)) ? ' and id in (' . filter_var($filtered_items ,FILTER_SANITIZE_STRING). ')':''));
							
							//autoupdate time diff
							$items_query = db_query("select id from app_entity_" . filter_var($action_entity_id,FILTER_SANITIZE_STRING) . " where parent_item_id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'" . (strlen(filter_var($filtered_items,FILTER_SANITIZE_STRING)) ? ' and id in (' . filter_var($filtered_items,FILTER_SANITIZE_STRING) . ')':''));
							while($items = db_fetch_array($items_query))
							{
								self::autoupdate_datetime_diff($action_entity_id, $items['id']);
							}
							
							//insert choices values for fields with multiple values
							if(count($choices_values->choices_values_list))
							{
								$subitems_query = db_query("select * from app_entity_" . filter_var($action_entity_id,FILTER_SANITIZE_STRING) . " where parent_item_id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'" . (strlen(filter_var($filtered_items,FILTER_SANITIZE_STRING)) ? ' and id in (' . filter_var($filtered_items,FILTER_SANITIZE_STRING) . ')':''));
								while($subitems = db_fetch_array($subitems_query))
								{
									$choices_values->process($subitems['id']);
									
									//atuoset fieldtype autostatus
									fieldtype_autostatus::set($action_entity_id, $subitems['id']);
								}
							}
							
							break;
							
						case strstr($actions['type'],'insert_item_subentity_'):
							
							//prepare data before insert
							$sql_data['parent_item_id'] = filter_var($item_id,FILTER_SANITIZE_STRING);
							$sql_data['created_by'] = filter_var($app_user['id'],FILTER_SANITIZE_STRING);
							$sql_data['date_added'] = time();
							
							$sql_data = $this->prepare_field_type_random_value($sql_data, filter_var($action_entity_id,FILTER_SANITIZE_STRING));
							
							//insert new item
							db_perform('app_entity_' . filter_var($action_entity_id,FILTER_SANITIZE_STRING),$sql_data);
														
							//insert choices values for fields with multiple values
							$new_item_id = db_insert_id();
							$choices_values->process(filter_var($new_item_id,FILTER_SANITIZE_STRING));
							
							//autoupdate all field types
							fields_types::update_items_fields(filter_var($action_entity_id,FILTER_SANITIZE_STRING), $new_item_id);
							
							//run actions after item insert
							$processes = new processes(filter_var($action_entity_id,FILTER_SANITIZE_STRING));
							$processes->run_after_insert($new_item_id);
							
							//send nofitication
							items::send_new_item_nofitication(filter_var($action_entity_id,FILTER_SANITIZE_STRING), $new_item_id);
							
							//log changeds
							$log = new track_changes(filter_var($action_entity_id,FILTER_SANITIZE_STRING), $new_item_id);
							$log->log_insert();
							
							//subscribe							
							$mailing = new mailing(filter_var($action_entity_id,FILTER_SANITIZE_STRING), $new_item_id);
							$mailing->subscribe();
							
							break;
							
						case strstr($actions['type'],'link_records_by_mysql_query_'):
						    $settigns = new settings($actions['settings']);
						    $records = new link_records_by_mysql_query(filter_var($this->entities_id,FILTER_SANITIZE_STRING), filter_var($item_id,FILTER_SANITIZE_STRING), filter_var($action_entity_id,FILTER_SANITIZE_STRING), $settigns->get('where_query'));
						    $records->process($sql_data, $choices_values);
						    break;
						    
						case strstr($actions['type'],'edit_item_related_entity_'):
							$table_info = related_records::get_related_items_table_name(filter_var($this->entities_id,FILTER_SANITIZE_STRING),$action_entity_id);
							$where_sql = "select entity_" . filter_var($action_entity_id,FILTER_SANITIZE_STRING) . filter_var($table_info['sufix'],FILTER_SANITIZE_STRING). "_items_id as item_id from " . filter_var($table_info['table_name'],FILTER_SANITIZE_STRING) . " where entity_" . filter_var($this->entities_id,FILTER_SANITIZE_STRING). "_items_id='" . db_input(filter_var($item_id,FILTER_SANITIZE_STRING)) . "'";
							
							//get filtered items and skip and if no items found
							if(($filtered_items = $this->include_filtered_items($actions,0,$where_sql))===false)
							{
								break;
							}
																					
							db_perform('app_entity_' . $action_entity_id,$sql_data,'update',"id in ({$where_sql})" . (strlen($filtered_items) ? ' and id in (' .$filtered_items  . ')':''));
							
							
							//autoupdate time diff
							$items_query = db_query("select id from app_entity_" . filter_var($action_entity_id,FILTER_SANITIZE_STRING) . " where id in ({$where_sql})" . (strlen(filter_var($filtered_items,FILTER_SANITIZE_STRING)) ? ' and id in (' .filter_var($filtered_items,FILTER_SANITIZE_STRING)  . ')':''));
							while($items = db_fetch_array($items_query))
							{
								self::autoupdate_datetime_diff($action_entity_id, $items['id']);
							}
							
							//insert choices values for fields with multiple values
							if(count($choices_values->choices_values_list))
							{	
								if(strlen($filtered_items))
								{
									foreach(explode(',',$filtered_items) as $item_id)
									{
										$choices_values->process($item_id);
										
										//atuoset fieldtype autostatus
										fieldtype_autostatus::set($action_entity_id, $item_id);
									}
								}	
								else 
								{	
									$subitems_query = db_query($where_sql);
									while($subitems = db_fetch_array($subitems_query))
									{
										$choices_values->process($subitems['item_id']);
										
										//atuoset fieldtype autostatus
										fieldtype_autostatus::set($action_entity_id, $subitems['item_id']);
									}
								}
							}
							break;
						case strstr($actions['type'],'insert_item_related_entity_'):
							
							//prepare data before insert
							$sql_data['created_by'] = $app_user['id'];
							$sql_data['date_added'] = time();
							
							$sql_data = $this->prepare_field_type_random_value($sql_data, $action_entity_id);
							
							$action_entity_info = db_find('app_entities',$action_entity_id);
							
							if($action_entity_info['parent_id']>0)
							{
								$item_info = db_find('app_entity_' . $this->entities_id, $item_id);
								$sql_data['parent_item_id'] = $item_info['parent_item_id'];
							}
							
							//print_r($sql_data);
							//exit();
							
							//insert new item
							db_perform('app_entity_' . $action_entity_id,$sql_data);
							$related_items_id = db_insert_id();
							
							//insert choices values for fields with multiple values							
							$choices_values->process($related_items_id);
							
							//autoupdate all field types
							fields_types::update_items_fields($action_entity_id, $related_items_id);
							
							//send nofitication
							items::send_new_item_nofitication($action_entity_id, $related_items_id);
							
							//log changeds
							$log = new track_changes($action_entity_id, $related_items_id);
							$log->log_insert();
														
							$table_info = related_records::get_related_items_table_name($this->entities_id,$action_entity_id);
							
							$sql_data_related = array(
									'entity_' . $this->entities_id . '_items_id' => $item_id,
									'entity_' . $action_entity_id . $table_info['sufix'] . '_items_id' => $related_items_id);
							
							db_perform($table_info['table_name'],$sql_data_related);														
							
							break;
							
					}
				}
				
			}
			
		}
				
		//exit();
		
		if(!$is_ipn)
		{	
			//prepare success msg
			if(strlen($process_info['success_message']))
			{
				$alerts->add($process_info['success_message'],'success');
			}
			else
			{
				$alerts->add(sprintf(TEXT_EXT_PROCESS_COMPLETED,$process_info['name'],count($selected_items)),'success');
			}
			
			//echo $app_redirect_to;
			//exit();
			
			$gotopage = '';
			if(isset($_GET['gotopage']))
			{
				$gotopage = '&gotopage[' . key(filter_var_array($_GET['gotopage'])). ']=' . current(filter_var_array($_GET['gotopage']));
			}
			
			switch($app_redirect_to)
			{				
				case 'parent_item_info_page':
					redirect_to('items/info','path=' . $app_path);
					break;
				case 'dashboard':
					redirect_to('dashboard/',substr($gotopage,1));
					break;
				case 'reports':
					redirect_to('reports/view','reports_id=' . $reports_id);
					break;
				case 'items':
					redirect_to('items/items','path=' . ($current_item_id==0 ? $app_path : substr($app_path,0,-(strlen($current_item_id)+1)) ) . $gotopage);
					break;
				case 'items_info':
					if($process_info['redirect_to_items_listing']==1)
					{
						$path_array = explode('-',$app_path);
						$path_info = items::get_path_info($path_array[0],$path_array[1]);
						
						redirect_to('items/items','path=' . substr($path_info['full_path'],0,strrpos($path_info['full_path'],'-')));
					}					
					else 
					{	
						redirect_to('items/info','path=' . $app_path);
					}
					break;
			}
			
			if(strstr($app_redirect_to,'report_'))
			{
				redirect_to('reports/view','reports_id=' . str_replace('report_','',$app_redirect_to) . $gotopage);
			}
			
			//redirect to reports group dashboard
			if(strstr($app_redirect_to,'reports_groups_'))
			{
				redirect_to('dashboard/reports','id=' . str_replace('reports_groups_','',$app_redirect_to));
			}
		}
	}
	
	static function autoupdate_datetime_diff($entities_id, $item_id)
	{		
		fieldtype_days_difference::update_items_fields($entities_id, $item_id);
		fieldtype_hours_difference::update_items_fields($entities_id, $item_id);
		fieldtype_years_difference::update_items_fields($entities_id, $item_id);		
		fieldtype_months_difference::update_items_fields($entities_id, $item_id);
		
		//autoupdate static text pattern
		fieldtype_text_pattern_static::set($entities_id, $item_id);
	}
	
	public function include_filtered_items($action_info, $parent_item_id=0, $related_items_sql = '')
	{
		global $sql_query_having;
		
		$items_list = array();
		
		$action_entity_id = self::get_entity_id_from_action_type($action_info['type']);
		
		//check if there report for aciton
		$reports_info_query = db_query("select * from app_reports where entities_id='" . db_input(filter_var($action_entity_id,FILTER_SANITIZE_STRING)). "' and reports_type='process_action" . filter_var($action_info['id'],FILTER_SANITIZE_STRING) . "'");
		if(!$reports_info = db_fetch_array($reports_info_query))
		{
			$sql_data = array('name'=>'',
					'entities_id'=>$action_entity_id,
					'reports_type'=>'process_action' . $action_info['id'],
					'in_menu'=>0,
					'in_dashboard'=>0,
					'created_by'=>0,
			);
			
			db_perform('app_reports',$sql_data);
			$reports_id = db_insert_id();
			$reports_info = db_find('app_reports',$reports_id);
		}
		
		//check if there are filters for report and then include sql query
		//or include query if user has access "view_assigned" or "action_with_assigned"
		$filters_query = db_query("select count(*) as total from app_reports_filters rf left join app_fields f on rf.fields_id=f.id where rf.reports_id='" . db_input(filter_var($reports_info['id'],FILTER_SANITIZE_STRING)) . "'");
		$filters = db_fetch_array($filters_query);
		if($filters['total']>0 or users::has_users_access_name_to_entity('view_assigned',$action_entity_id) or ($force_access_check = users::has_users_access_name_to_entity('action_with_assigned',$action_entity_id)))
		{	
			$listing_sql_query_select = '';
			$listing_sql_query = '';
			$listing_sql_query_having = '';
			$sql_query_having = array();
			
			
			//prepare forumulas query
			$listing_sql_query_select = fieldtype_formula::prepare_query_select(filter_var($action_entity_id,FILTER_SANITIZE_STRING), $listing_sql_query_select);
								
			$listing_sql_query = reports::add_filters_query(filter_var($reports_info['id'],FILTER_SANITIZE_STRING),$listing_sql_query);
			
			//prepare having query for formula fields
			if(isset($sql_query_having[$action_entity_id]))
			{
				$listing_sql_query_having  = reports::prepare_filters_having_query($sql_query_having[fiter_var($action_entity_id,FILTER_SANITIZE_STRING)]);
			}
			
			//check view assigned only access			
			$listing_sql_query = items::add_access_query(filter_var($action_entity_id,FILTER_SANITIZE_STRING),$listing_sql_query,$force_access_check);
			
			$listing_sql_query .= $listing_sql_query_having;
			
			//build itesm query
			$items_sql = "select e.* " . $listing_sql_query_select . " from app_entity_" . filter_var($action_entity_id,FILTER_SANITIZE_STRING) . " e "  . " where e.id>0 " . $listing_sql_query;
			
			//include parent if exist
			if($parent_item_id>0)
			{
				$items_sql .= " and e.parent_item_id='" . db_input(filter_var($parent_item_id ,FILTER_SANITIZE_STRING)). "'";
			}
			
			//include related items if exist
			if(strlen($related_items_sql)>0)
			{
				$items_sql .=" and e.id in ({$related_items_sql})";
			}
			
			//echo $items_sql;
							
			//build items list
			$items_query = db_query($items_sql);
			while($items = db_fetch_array($items_query))
			{
				$items_list[] = $items['id'];
			}
										
			//echo print_r($items_list);
			//exit();
			
			//return false if no items
			if(!count($items_list))
			{
				return false;					
			}
			
		}
		
		
		return implode(',',$items_list);
		
	}
	
	public static function get_entity_id_from_action_type($type)
	{
		$value = str_replace(array('link_records_by_mysql_query_','clone_item_entity_','clone_subitems_linked_entity_','move_item_entity_','edit_item_users_entity_1','insert_item_linked_entity_','edit_item_linked_entity_','edit_parent_item_entity_','edit_item_entity_','copy_item_entity_','edit_item_subentity_','insert_item_subentity_','edit_item_related_entity_','insert_item_related_entity_'),'',$type);
		$value = explode('_',$value);
		return $value[0];
	}
	
	public static function get_actions_types_choices($entities_id)
	{
		global $app_entities_cache;
		
		$choices = array();
		
		$entity_info = db_find('app_entities',$entities_id);
		
		$choices['edit_item_entity_' . $entity_info['id']] = sprintf(TEXT_EXT_PROCESS_ACTION_EDIT_ITEM,$entity_info['name']);
		
		$choices['copy_item_entity_' . $entity_info['id']] = sprintf(TEXT_EXT_PROCESS_ACTION_COPY_ITEM,$entity_info['name']);
		
		$choices['clone_item_entity_' . $entity_info['id']] = sprintf(TEXT_EXT_PROCESS_ACTION_CLONE_ITEM,$entity_info['name']);
						
		if($entity_info['parent_id']>0)
		{
			$choices['move_item_entity_' . $entity_info['id']] = sprintf(TEXT_EXT_PROCESS_ACTION_MOVE_ITEM,$entity_info['name']);			
			$choices['edit_parent_item_entity_' . $entity_info['parent_id']] = sprintf(TEXT_EXT_PROCESS_ACTION_EDIT_PARENT_ITEM,$app_entities_cache[$entity_info['parent_id']]['name']);
		}
		
		$entities_query = db_query("select * from app_entities where parent_id='" . filter_var($entity_info['id'],FILTER_SANITIZE_STRING) . "'");
		while($entities = db_fetch_array($entities_query))
		{
			$choices['edit_item_subentity_' . filter_var($entities['id'],FILTER_SANITIZE_STRING)] = sprintf(TEXT_EXT_PROCESS_ACTION_EDIT_ITEM_SUBENTITY,filter_var($entities['name'],FILTER_SANITIZE_STRING));
			$choices['insert_item_subentity_' . filter_var($entities['id'],FILTER_SANITIZE_STRING)] = sprintf(TEXT_EXT_PROCESS_ACTION_INSERT_ITEM_SUBENTITY,filter_var($entities['name'],FILTER_SANITIZE_STRING));
		}
		
		$fields_query = db_query("select * from app_fields where entities_id='" . filter_var($entity_info['id'],FILTER_SANITIZE_STRING) . "' and type in ('fieldtype_related_records', 'fieldtype_entity','fieldtype_entity_ajax','fieldtype_entity_multilevel','fieldtype_users','fieldtype_users_ajax','fieldtype_created_by')");
		while($fields = db_fetch_array($fields_query))
		{
			$cfg = new fields_types_cfg(filter_var($fields['configuration'],FILTER_SANITIZE_STRING));
			switch(filter_var($fields['type']))
			{
				case 'fieldtype_related_records':
					$entity_id = (int)$cfg->get('entity_id');
					if($entity_id)
					{
						$choices['edit_item_related_entity_' . $entity_id] = sprintf(TEXT_EXT_PROCESS_ACTION_EDIT_ITEM_RELATED_ENTITY,filter_var($fields['name'],FILTER_SANITIZE_STRING));
						
						//Check parent_id 
						//Note: related items should be top entity or have the same parenet_id to insert new related item
						$related_entity_info = db_find('app_entities',$entity_id);
						if($related_entity_info['parent_id']==0 or $related_entity_info['parent_id']==$entity_info['parent_id'])
						{					
							$choices['insert_item_related_entity_' . $entity_id] = sprintf(TEXT_EXT_PROCESS_ACTION_INSERT_ITEM_RELATDENTITY,filter_var($fields['name'],FILTER_SANITIZE_STRING));							
						}
						
						$choices['link_records_by_mysql_query_' . $entity_id] = sprintf(TEXT_EXT_PROCESS_ACTION_LINK_RECORDS_BY_MYSQL_QUERY,filter_var($fields['name'],FILTER_SANITIZE_STRING));
					}
					break;
					
				case 'fieldtype_entity':
				case 'fieldtype_entity_ajax':
				case 'fieldtype_entity_multilevel':	
					$entity_id = (int)$cfg->get('entity_id');
					if($entity_id)
					{
						$choices['edit_item_linked_entity_' . $entity_id . '_' . filter_var($fields['id'],FILTER_SANITIZE_STRING)] = sprintf(TEXT_EXT_PROCESS_ACTION_EDIT_ITEM_LINKED_ENTITY,$app_entities_cache[$entity_id]['name'],filter_var($fields['name'],FILTER_SANITIZE_STRING));
					
						//Check parent_id
						//Note: related items should be top entity or have the same parenet_id to insert new related item
						$related_entity_info = db_find('app_entities',$entity_id);
						if($related_entity_info['parent_id']==0 or $related_entity_info['parent_id']==$entity_info['parent_id'])
						{
							$choices['insert_item_linked_entity_' . $entity_id . '_' . filter_var($fields['id'],FILTER_SANITIZE_STRING)] = sprintf(TEXT_EXT_PROCESS_ACTION_INSERT_ITEM_LINKED_ENTITY,$app_entities_cache[$entity_id]['name'],filter_var($fields['name'],FILTER_SANITIZE_STRING));
						}
						
						//prepare clone action
						$check_query = db_query("(select count(*) as total from app_entities where parent_id='" . $entities_id . "')");
						$check = db_fetch_array($check_query);
						
						$check_query = db_query("(select count(*) as total from app_entities where parent_id='" . $entity_id . "')");
						$check2 = db_fetch_array($check_query);
						
						if($check['total']>0 and $check2['total']>0)
						{
							$choices['clone_subitems_linked_entity_' . $entity_id . '_' . filter_var($fields['id'],FILTER_SANITIZE_STRING)] = sprintf(TEXT_EXT_PROCESS_ACTION_CLONE_SUBITEMS_LINKED_ENTITY,$app_entities_cache[$entity_id]['name'],filter_var($fields['name'],FILTER_SANITIZE_STRING));
						}
					}
					break;
				case 'fieldtype_created_by':	
					$choices['edit_item_linked_entity_1_' . filter_var($fields['id'],FILTER_SANITIZE_STRING)] = sprintf(TEXT_EXT_PROCESS_ACTION_EDIT_ITEM_LINKED_ENTITY,$app_entities_cache[1]['name'],fields_types::get_option(filter_var($fields['type'],FILTER_SANITIZE_STRING),'name',filter_var($fields['name'],FILTER_SANITIZE_STRING)));
			    break;
				case 'fieldtype_users_ajax':
				case 'fieldtype_users':
					$choices['edit_item_linked_entity_1_' . filter_var($fields['id'],FILTER_SANITIZE_STRING)] = sprintf(TEXT_EXT_PROCESS_ACTION_EDIT_ITEM_LINKED_ENTITY,$app_entities_cache[1]['name'],filter_var($fields['name'],FILTER_SANITIZE_STRING));
					break;
			}
		}
		
		return $choices;
	}
	
	public static function get_actions_fields_choices($entity_id)
	{
		$available_types = array('fieldtype_checkboxes',
				'fieldtype_radioboxes',
				'fieldtype_boolean',
		        'fieldtype_boolean_checkbox',
				'fieldtype_dropdown',
				'fieldtype_dropdown_multiple',
				'fieldtype_input_date',
				'fieldtype_input_datetime',
				'fieldtype_input_numeric',
				'fieldtype_input',
				'fieldtype_input_email',
				'fieldtype_input_url',
				'fieldtype_input_file',
				'fieldtype_input_masked',
				'fieldtype_attachments',
				'fieldtype_image',
				'fieldtype_textarea',
				'fieldtype_textarea_wysiwyg',
				'fieldtype_input_masked',
				'fieldtype_entity',
				'fieldtype_entity_ajax',
				'fieldtype_users',
		        'fieldtype_users_ajax',
				'fieldtype_grouped_users',
				'fieldtype_progress',
				'fieldtype_todo_list',
				'fieldtype_auto_increment',
				'fieldtype_tags',
				'fieldtype_user_roles',
				'fieldtype_users_approve',
				'fieldtype_user_accessgroups',
				'fieldtype_user_status',
				'fieldtype_created_by',
				'fieldtype_phone',
				'fieldtype_stages',
				'fieldtype_entity_multilevel',
		        'fieldtype_ajax_request',
		);
		
		$choices = array();
		$fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type in (\"" . implode('","',$available_types). "\")  and f.entities_id='" . db_input($entity_id) . "' and f.forms_tabs_id=t.id order by t.sort_order, t.name, f.sort_order, f.name");
		while($v = db_fetch_array($fields_query))
		{
			$choices[filter_var($v['id'],FILTER_SANITIZE_STRING)] = fields_types::get_option(filter_var($v['type'],FILTER_SANITIZE_STRING),'name',filter_var($v['name'],FILTER_SANITIZE_STRING));
		}
	
		return $choices;
	}
	
	public static function output_action_field_value($actions_fields)
	{
		if(!isset($actions_fields['enter_manually'])) $actions_fields['enter_manually'] = 0;
		
		if($actions_fields['enter_manually']==1)
		{
			return TEXT_EXT_MANUALLY_ENTERED;
		}
		
		$field = db_find('app_fields',filter_var($actions_fields['fields_id'],FILTER_SANITIZE_STRING));
		
		$output_options = array('class'=>filter_var($field['type'],FILTER_SANITIZE_STRING),
				'value'=>$actions_fields['value'],
				'field'=>filter_var_array($field),
				'is_listing'=>true,
                                'is_export'=>true,
		);
		
		if(in_array($actions_fields['field_type'],array('fieldtype_users','fieldtype_users_ajax','fieldtype_created_by','fieldtype_dropdown','fieldtype_entity','fieldtype_entity_ajax','fieldtype_entity_multilevel')))
		{
			if(strstr($actions_fields['value'],'['))
			{
				return $actions_fields['value'];
			}
			else
			{
				return fields_types::output($output_options);
			}
		}
		elseif(in_array($actions_fields['field_type'],array('fieldtype_input_date','fieldtype_input_datetime')))
		{
			if(strlen($actions_fields['value'])<10)
			{
				return $actions_fields['value'];
			}
			else
			{
				return fields_types::output($output_options);
			}
		}
		elseif(in_array($actions_fields['field_type'],array('fieldtype_input_file','fieldtype_attachments','fieldtype_image')))
		{
			return $actions_fields['value'];
		}
		elseif(in_array($actions_fields['field_type'],array('fieldtype_input_numeric')) and strstr($actions_fields['value'],'['))
		{
			return $actions_fields['value'];
		}
		else
		{
			return fields_types::output($output_options);
		}		
	}
	
	function prepare_field_type_random_value($sql_data, $action_entity_id)
	{
		$fields_query = db_query("select * from app_fields where type='fieldtype_random_value' and entities_id='" . filter_var($action_entity_id,FILTER_SANITIZE_STRING) . "'");
		while($field = db_fetch_array($fields_query))
		{
			//prepare process options
			$process_options = array(
					'class'          => $field['type'],
					'value'          => '',
					'fields_cache'   => array(),
					'field'          => $field,
					'is_new_item'    => true,
					'current_field_value' => '',
			);
				
			$sql_data['field_' . $field['id']] = fields_types::process($process_options);
		}
		
		return $sql_data;
	}
}