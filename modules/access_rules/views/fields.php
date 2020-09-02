
<?php require(component_path('entities/navigation')) ?>

<h3 class="page-title"><?php echo  TEXT_ACCESS_ALLOCATION_RULES ?></h3>

<p><?php echo TEXT_ACCESS_ALLOCATION_RULES_INFO ?></p>

<?php 
$form_fields_query = db_query("select r.*, f.name, f.type, f.id as fields_id, f.configuration from app_access_rules_fields r, app_fields f where r.fields_id=f.id and r.entities_id='" . _get::int('entities_id'). "'");

if(db_num_rows($form_fields_query)==0)
{	
	echo button_tag(TEXT_ADD_FIELD,url_for('access_rules/fields_form','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)),true);
}

?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    
    <th><?php echo TEXT_ACTION?></th>
    <th>#</th>    
    <th width="100%"><?php echo TEXT_RULE_FOR_FIELD ?></th>           
  </tr>
</thead>
<tbody>
<?php

if(db_num_rows($form_fields_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

while($v = db_fetch_array($form_fields_query)):
?>
<tr>  
  <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('access_rules/fields_delete','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) . ' ' . button_icon_edit(url_for('access_rules/fields_form','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING). '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) ?></td>
  <td><?php echo htmlentities($v['id']) ?></td>
  <td><?php echo '<a href="' . url_for('access_rules/rules','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&fields_id=' . filter_var($v['fields_id'],FILTER_SANITIZE_STRING)) . '">' . fields_types::get_option(filter_var($v['type'],FILTER_SANITIZE_STRING),'name',filter_var($v['name'],FILTER_SANITIZE_STRING)) . '</a>' ?></td>   
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>

<?php 
$entities_info = db_find('app_entities',filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
if($entities_info['parent_id']!=0)
{
	$parent_entities_info = db_find('app_entities',filter_var($entities_info['parent_id'],FILTER_SANITIZE_STRING));
	
	$reports_info_query = db_query("select * from app_reports where entities_id='" . db_input(filter_var($parent_entities_info['id'],FILTER_SANITIZE_STRING)). "' and reports_type='hide_add_button_rules" . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . "'");
	if(!$reports_info = db_fetch_array($reports_info_query))
	{
		$sql_data = array('name'=>'',
				'entities_id'=>filter_var($parent_entities_info['id'],FILTER_SANITIZE_STRING),
				'reports_type'=>'hide_add_button_rules'. filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING),
				'in_menu'=>0,
				'in_dashboard'=>0,
				'created_by'=>0,
		);
		db_perform('app_reports',$sql_data);
		$reports_id = db_insert_id();
		
		$reports_info = db_find('app_reports',$reports_id);
	}
	
	require(component_path('access_rules/hide_add_button_rules'));
}
?>
