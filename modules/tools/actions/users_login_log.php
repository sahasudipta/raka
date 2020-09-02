<?php

switch($app_module_action)
{
	case 'reset':
		db_query("delete from app_users_login_log");
		
		redirect_to('tools/users_login_log');
		
		break;
	case 'listing':
		$html = '
			<div class="table-scrollable">
				<table class="table table-striped table-bordered table-hover">
				<thead>
				  <tr>
						<th>' . TEXT_TYPE . '</th>
						<th>' . TEXT_DATE_ADDED . '</th>
						<th>' . TEXT_IP . '</th>		
						<th>' . TEXT_USERNAME . '</th>										    				    
						<th width="100%">' . TEXT_NAME . '</th>
						<th>' . TEXT_USERS_GROUPS . '</th>
						<th>' . TEXT_EMAIL . '</th>
				  </tr>
				</thead>
				<tbody>
		';
		
		$where_sql = '';
		
		foreach(filter_var_array($_POST['filters']) as $filter)
		{
			if(strlen($filter['value'])>0)
			{
				switch($filter['name'])
				{					
					case 'type':
						$where_sql .= " and is_success='" . filter_var($filter['value'],FILTER_SANITIZE_STRING) . "'";
						break;
					case 'users_id':
						$where_sql .= " and users_id='" . filter_var($filter['value'],FILTER_SANITIZE_STRING) . "'";
						break;
			
				}
			}
		}
									
		$listing_sql = "select * from app_users_login_log where id>0 {$where_sql} order by date_added desc";
		$listing_split = new split_page($listing_sql,'users_login_log_listing','',CFG_APP_ROWS_PER_PAGE);
		$items_query = db_query($listing_split->sql_query);
		while($item = db_fetch_array($items_query))
		{
			$html .= '
							<tr>
							  <td>' . ($item['is_success']==1 ? '<span class="label label-success">' . TEXT_SUCCESSFUL_LOGIN . '</span>' : '<span class="label label-warning">' . TEXT_LOGIN_ATTEMPT . '</span>') . '</td>			
							  <td>' . format_date_time($item['date_added']) . '</td>
								<td>' . filter_var($item['identifier'],FILTER_SANITIZE_STRING) . '</td>
								<td>' . htmlspecialchars($item['username']) . '</td>
								<td>' . (isset($app_users_cache[filter_var($item['users_id'],FILTER_SANITIZE_STRING)]) ? $app_users_cache[filter_var($item['users_id'],FILTER_SANITIZE_STRING)]['name'] : ''). '</td>		
								<td>' . (isset($app_users_cache[filter_var($item['users_id'],FILTER_SANITIZE_STRING)]) ? ($app_users_cache[filter_var($item['users_id'],FILTER_SANITIZE_STRING)]['group_id']>0 ? $app_users_cache[filter_var($item['users_id'],FILTER_SANITIZE_STRING)]['group_name'] : TEXT_ADMINISTRATOR) : ''). '</td>
								<td>' . (isset($app_users_cache[filter_var($item['users_id'],FILTER_SANITIZE_STRING)]) ? $app_users_cache[filter_var($item['users_id'],FILTER_SANITIZE_STRING)]['email'] : ''). '</td>
							</tr>
					';
		}
		
		if($listing_split->number_of_rows==0)
		{
			$html .= '
				    <tr>
				      <td colspan="6">' . TEXT_NO_RECORDS_FOUND . '</td>
				    </tr>
				  ';
		}
		
		$html .= '
		  </tbody>
		</table>
		</div>
		';
		
		//add pager
		$html .= '
		  <table width="100%">
		    <tr>
		      <td>' . $listing_split->display_count() . '</td>
		      <td align="right">' . $listing_split->display_links(). '</td>
		    </tr>
		  </table>
		';
		
		echo $html;
		
		exit();
		
		break;
}