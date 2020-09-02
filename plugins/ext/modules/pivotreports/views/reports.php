<h3 class="page-title"><?php echo TEXT_EXT_PIVOTREPORTS ?></h3>


<?php echo button_tag(TEXT_BUTTON_CREATE,url_for('ext/pivotreports/reports_form'),true) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>        
    <th><?php echo TEXT_REPORT_ENTITY ?></th>
    <th width="100%"><?php echo TEXT_NAME ?></th>    
    <th><?php echo TEXT_SORT_ORDER ?></th>           
  </tr>
</thead>
<tbody>
<?php
$reports_query = db_query("select * from app_ext_pivotreports order by sort_order, name");

$entities_cache = entities::get_name_cache();
$fields_cahce = fields::get_name_cache();


if(db_num_rows($reports_query)==0) echo '<tr><td colspan="4">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

while($reports = db_fetch_array($reports_query)):
?>
<tr>
  <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/pivotreports/reports_delete','id=' . filter_var($reports['id'],FILTER_SANITIZE_STRING))) . ' ' . button_icon_edit(url_for('ext/pivotreports/reports_form','id=' . filter_var($reports['id'],FILTER_SANITIZE_STRING))) . ' ' . button_icon(TEXT_EXT_PIVOTREPORTS_FIELDS,'fa fa-cogs',url_for('ext/pivotreports/fields','id=' . filter_var($reports['id'],FILTER_SANITIZE_STRING)),false) ?></td>
  <td><?php echo htmlentities($entities_cache[filter_var($reports['entities_id'],FILTER_SANITIZE_STRING)]) ?></td>
  <td><?php echo link_to(filter_var($reports['name'],FILTER_SANITIZE_STRING),url_for('ext/pivotreports/view','id=' . htmlentities(filter_var($reports['id'],FILTER_SANITIZE_STRING)))) ?></td>  
  <td><?php echo filter_var($reports['sort_order'] ,FILTER_SANITIZE_STRING)?></td>   
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>