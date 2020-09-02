<?php

$html = '
	<div class="table-scrollable">
		<table class="table table-striped table-bordered table-hover">
		<thead>  
		  <tr>	
				<th>' . input_checkbox_tag('select_all_items','',array('class'=>'select_all_items')) . '</th>
		    <th width="100%">' . TEXT_DESCRIPTION . '</th>
		    <th>' . TEXT_CREATED_BY . '</th>
		    <th>' . TEXT_DATE_ADDED . '</th>
		  </tr>
		</thead>
		<tbody> 		
';


$listing_sql = "select * from app_users_notifications where users_id='" . $app_user['id'] . "' order by id desc";
$listing_split = new split_page($listing_sql,'users_notifications_listing');
$items_query = db_query($listing_split->sql_query);
while($item = db_fetch_array($items_query))
{
	$path_info = items::get_path_info(filter_var($item['entities_id'],FILTER_SANITIZE_STRING),filter_var($item['items_id'],FILTER_SANITIZE_STRING));
	
	$html .= '
			<tr>
				<td>' . input_checkbox_tag('items_' . filter_var($item['id'],FILTER_SANITIZE_STRING),filter_var($item['id'],FILTER_SANITIZE_STRING),array('class'=>'items_checkbox','checked'=>in_array(filter_var($item['id'],FILTER_SANITIZE_STRING),$app_selected_notification_items))) . '</td>
				<td style="white-space: normal;"><a href="' . url_for('items/info','path=' . filter_var($path_info['full_path'],FILTER_SANITIZE_STRING)) . '">' . users_notifications::render_icon_by_type(filter_var($item['type'],FILTER_SANITIZE_STRING)) . ' ' . filter_var($item['name'],FILTER_SANITIZE_STRING) . '</a></td>
				<td>' . (isset($app_users_cache[filter_var($item['created_by'],FILTER_SANITIZE_STRING)]) ? $app_users_cache[filter_var($item['created_by'],FILTER_SANITIZE_STRING)]['name'] : '') . '</td>		
				<td>' . format_date_time(filter_var($item['date_added'],FILTER_SANITIZE_STRING)) . '</td>
			</tr>
	';
}


if($listing_split->number_of_rows==0)
{
	$html .= '
    <tr>
      <td colspan="4">' . TEXT_NO_RECORDS_FOUND . '</td>
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
