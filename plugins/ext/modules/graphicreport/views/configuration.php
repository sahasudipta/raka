<h3 class="page-title"><?php echo TEXT_EXT_GRAPHIC_REPORT ?></h3>


<?php echo button_tag(TEXT_BUTTON_CREATE,url_for('ext/graphicreport/configuration_form'),true) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>        
    <th><?php echo TEXT_REPORT_ENTITY ?></th>
    <th width="100%"><?php echo TEXT_NAME ?></th>    
    <th><?php echo TEXT_EXT_HORIZONTAL_AXIS ?></th>    
    <th><?php echo TEXT_EXT_VERTICAL_AXIS ?></th>    
  </tr>
</thead>
<tbody>
<?php
$reports_query = db_query("select * from app_ext_graphicreport order by name");

$entiti_cache = entities::get_name_cache();
$fields_cahce = fields::get_name_cache();



if(db_num_rows($reports_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

while($reports = db_fetch_array($reports_query)):
?>
<tr>
  <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/graphicreport/configuration_delete','id=' . htmlentities(filter_var($reports['id'],FILTER_SANITIZE_STRING)))) . ' ' . button_icon_edit(url_for('ext/graphicreport/configuration_form','id=' . htmlentities(filter_var($reports['id'],FILTER_SANITIZE_STRING)))) ?></td>
  <td><?php echo htmlentities($entiti_cache[filter_var($reports['entities_id'],FILTER_SANITIZE_STRING)]) ?></td>
  <td><?php echo link_to(filter_var($reports['name'],FILTER_SANITIZE_STRING),url_for('ext/graphicreport/view','id=' . htmlentities(filter_var($reports['id'],FILTER_SANITIZE_STRING)))) ?></td>  
  <td><?php echo htmlentities($fields_cahce[filter_var($reports['xaxis'],FILTER_SANITIZE_STRING)]) ?></td>
  <td>
<?php  
  foreach(filter_var_array(explode(',',$reports['yaxis'])) as $id)
  {
    echo htmlentities($fields_cahce[$id])  . '<br>';
  }  
?>
  </td>
    
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>