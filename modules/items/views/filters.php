<h3 class="page-title"><?php echo TEXT_HEADING_FILTERS_FOR  . ' ' . link_to($entity_listing_heading,url_for('items/','path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING))) ?></h3>

<?php echo button_tag(TEXT_BUTTON_ADD_NEW_REPORT_FILTER,url_for('items/filters_form','reports_id=' . filter_var($reports_info['id'],FILTER_SANITIZE_STRING) . '&path=' .filter_var($_GET['path'],FILTER_SANITIZE_STRING))) ?>


<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>        
    <th width="100%"><?php echo TEXT_FIELD ?></th>
    <th><?php echo TEXT_FILTERS_CONDITION ?></th>
    <th><?php echo TEXT_VALUES ?></th>
            
  </tr>
</thead>
<tbody>

<?php
  $filters_query = db_query("select rf.*, f.name from app_reports_filters rf, app_fields f  where rf.fields_id=f.id and rf.reports_id='" . db_input($reports_info['id']) . "' order by rf.id");
  
  if(db_num_rows($filters_query)==0) echo '<tr><td colspan="4">' . TEXT_NO_RECORDS_FOUND. '</td></tr>';
  
  while($v = db_fetch_array($filters_query)):
?>
  <tr>
    <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('items/filters_delete','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '&reports_id=' . filter_var($reports_info['id'],FILTER_SANITIZE_STRING) . '&path=' .filter_var($_GET['path'],FILTER_SANITIZE_STRING))) . ' ' . button_icon_edit(url_for('items/filters_form','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '&reports_id=' . filter_var($reports_info['id'],FILTER_SANITIZE_STRING) . '&path=' .filter_var($_GET['path'],FILTER_SANITIZE_STRING)))  ?></td>    
    <td><?php echo htmlentities($v['name']) ?></td>
    <td><?php echo reports::get_condition_name_by_key(filter_var($v['filters_condition'],FILTER_SANITIZE_STRING)) ?></td>
    <td class="nowrap"><?php echo reports::render_filters_values(filter_var($v['fields_id'],FILTER_SANITIZE_STRING),filter_var($v['filters_values'],FILTER_SANITIZE_STRING),'<br>',filter_var($v['filters_condition'],FILTER_SANITIZE_STRING)) ?></td>            
  </tr>
<?php endwhile?>  
</tbody>
</table>
</div>