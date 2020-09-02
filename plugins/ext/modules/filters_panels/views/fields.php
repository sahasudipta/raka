<?php 
	if(strstr($app_redirect_to,'item_pivot_tables_'))
	{
		require(component_path('ext/item_pivot_tables/filters_panels_breadcrumb'));		
	}
?>

<h3 class="page-title"><?php echo  TEXT_FIELDS_CONFIGURATION ?></h3>

<p><?php echo TEXT_PANES_FILTERS_FIELDS_CONFIGURATION_INFO ?></p>

<?php echo button_tag(TEXT_BUTTON_ADD,url_for('ext/filters_panels/fields_form','panels_id=' . filter_var($panels_id,FILTER_SANITIZE_STRING) . '&redirect_to=' . $app_redirect_to . '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) . ' ' .  button_tag(TEXT_BUTTON_SORT,url_for('ext/filters_panels/fields_sort','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&redirect_to=' . $app_redirect_to . '&panels_id=' . filter_var($_GET['panels_id'],FILTER_SANITIZE_STRING)),true,array('class'=>'btn btn-default'))  ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>            
    <th width="100%"><?php echo TEXT_FIELD ?></th>                   
    <th><?php echo TEXT_DISPLAY_AS ?></th>    
  </tr>
</thead>
<tbody>
<?php

$fields_query = db_query("select pf.*, f.name as field_name, f.type as field_type from app_filters_panels_fields pf, app_fields f where pf.fields_id=f.id and pf.panels_id='" . _get::int('panels_id') . "' order by pf.sort_order");

if(db_num_rows($fields_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

while($fields = db_fetch_array($fields_query)):
?>
<tr>
  <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('ext/filters_panels/fields_delete','panels_id=' . filter_var($panels_id,FILTER_SANITIZE_STRING) . '&redirect_to=' . $app_redirect_to . '&id=' . htmlentities(filter_var($fields['id'],FILTER_SANITIZE_STRING)) . '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) . ' ' . button_icon_edit(url_for('ext/filters_panels/fields_form','panels_id=' . filter_var($panels_id,FILTER_SANITIZE_STRING) . '&redirect_to=' . $app_redirect_to . '&id=' . htmlentities(filter_var($fields['id'],FILTER_SANITIZE_STRING)). '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) ?></td>    
  <td><?php echo fields_types::get_option(filter_var($fields['field_type'],FILTER_SANITIZE_STRING),'name',filter_var($fields['field_name'],FILTER_SANITIZE_STRING)) ?></td>     
  <td><?php echo filters_panels::get_field_display_type_name(filter_var($fields['display_type'],FILTER_SANITIZE_STRING)) ?></td>  
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>

<?php 

if(strstr($app_redirect_to,'item_pivot_tables_'))
{
	echo link_to(TEXT_BUTTON_BACK, url_for('ext/item_pivot_tables/reports'),array('class'=>'btn btn-default'));
}	
?>