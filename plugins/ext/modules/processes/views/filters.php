<?php require(component_path('ext/processes/navigation')) ?>
    
<h3 class="page-title"><?php echo  TEXT_NAV_LISTING_FILTERS_CONFIG ?></h3>

<p><?php echo TEXT_EXT_PROCESS_FILTERS_INFO ?></p>

<?php echo button_tag(TEXT_BUTTON_ADD_NEW_REPORT_FILTER,url_for('ext/processes/filters_form','process_id=' . filter_var($_GET['process_id'],FILTER_SANITIZE_STRING))) ?>

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
<?php if(db_count('app_reports_filters',$reports_info['id'],'reports_id')==0) echo '<tr><td colspan="5">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; ?>
<?php  
  $filters_query = db_query("select rf.*, f.name, f.type from app_reports_filters rf left join app_fields f on rf.fields_id=f.id where rf.reports_id='" . db_input($reports_info['id']) . "' order by rf.id");
  while($v = db_fetch_array($filters_query)):
?>
  <tr>
    <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/processes/filters_delete','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '&process_id=' . filter_var($_GET['process_id'],FILTER_SANITIZE_STRING))) . ' ' . button_icon_edit(url_for('ext/processes/filters_form','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '&process_id=' . filter_var($_GET['process_id'],FILTER_SANITIZE_STRING)))  ?></td>    
    <td><?php echo fields_types::get_option(filter_var($v['type'],FILTER_SANITIZE_STRING),'name',filter_var($v['name'],FILTER_SANITIZE_STRING)) ?></td>
    <td><?php echo reports::get_condition_name_by_key(filter_var($v['filters_condition'],FILTER_SANITIZE_STRING)) ?></td>    
    <td class="nowrap"><?php echo reports::render_filters_values(filter_var($v['fields_id'],FILTER_SANITIZE_STRING),filter_var($v['filters_values'],FILTER_SANITIZE_STRING),'<br>',filter_var($v['filters_condition'],FILTER_SANITIZE_STRING)) ?></td>            
  </tr>
<?php endwhile?>  
</tbody>
</table>
</div>

<?php echo '<a href="' . url_for('ext/processes/processes') . '" class="btn btn-default">' . TEXT_BUTTON_BACK. '</a>' ?>

