<?php require(component_path('entities/navigation')) ?>
    
<h3 class="page-title"><?php echo  TEXT_NAV_LISTING_FILTERS_CONFIG . ' (' . $fields_info['name'] . ')' ?></h3>

<p><?php echo TEXT_LISTING_FILTERS_CFG_INFO ?></p>

<?php echo button_tag(TEXT_BUTTON_ADD_NEW_REPORT_FILTER,url_for('entities/entityfield_filters_form','reports_id=' . filter_var($reports_info['id'],FILTER_SANITIZE_STRING) . '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&fields_id=' . filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING))) . ' ' . button_tag(TEXT_BUTTON_CONFIGURE_SORTING,url_for('reports/sorting','reports_id=' . filter_var($reports_info['id'],FILTER_SANITIZE_STRING) . '&redirect_to=entityfield_filters&fields_id=' . filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING))) ?>

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
    <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('entities/entityfield_filters_delete','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '&reports_id=' . filter_var($reports_info['id'],FILTER_SANITIZE_STRING). '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING). '&fields_id=' . filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING))) . ' ' . button_icon_edit(url_for('entities/entityfield_filters_form','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '&reports_id=' . filter_var($reports_info['id'],FILTER_SANITIZE_STRING). '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING). '&fields_id=' . filter_var($_GET['fields_id'],FILTER_SANITIZE_STRING)))  ?></td>    
    <td><?php echo fields_types::get_option(filter_var($v['type'],FILTER_SANITIZE_STRING),'name',filter_var($v['name'],FILTER_SANITIZE_STRING)) ?></td>
    <td><?php echo reports::get_condition_name_by_key($v['filters_condition']) ?></td>    
    <td class="nowrap"><?php echo htmlentities(reports::render_filters_values($v['fields_id'],$v['filters_values'],'<br>',$v['filters_condition'])) ?></td>            
  </tr>
<?php endwhile?>  
</tbody>
</table>
</div>

<?php echo htmlentities('<a class="btn btn-default" href="' . url_for('entities/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . '">' . TEXT_BUTTON_BACK. '</a>'); ?>