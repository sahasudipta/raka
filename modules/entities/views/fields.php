
<?php require(component_path('entities/navigation')) ?>

<?php echo button_tag(TEXT_BUTTON_ADD_NEW_FIELD,url_for('entities/fields_form','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)),true) ?>

<div class="btn-group">
	<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" data-hover="dropdown">
	<?php echo TEXT_WITH_SELECTED ?> <i class="fa fa-angle-down"></i>
	</button>
	<ul class="dropdown-menu" role="menu">
		<li>
			<?php echo link_to_modalbox(TEXT_FIELDS_EXPORT,url_for('entities/fields_export_form','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) ?>
		</li>
		<li>
			<?php echo link_to_modalbox(TEXT_COPY_FIELDS,url_for('entities/fields_copy_form','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) ?>
		</li> 
		<li>
			<?php echo link_to_modalbox(TEXT_EDIT_FIELDS,url_for('entities/fields_mulitple_edit','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) ?>
		</li>
  </ul>
</div> 

<?php echo button_tag('<i class="fa fa-upload"></i>',url_for('entities/fields_import_form','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)),true,['class'=>'btn btn-default','title'=>TEXT_IMPORT_FIELDS]) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo input_checkbox_tag('select_all_fields','',array('class'=>'select_all_fields'))?></th>
    <th><?php echo TEXT_ACTION?></th>
    <th>#</th>
    <th><?php echo TEXT_FORM_TAB ?></th>
    <th width="100%"><?php echo TEXT_NAME ?></th>    
    <th><?php echo TEXT_SHORT_NAME ?></th>
    <th><?php echo TEXT_NOTE ?></th>    
    <th><?php echo TEXT_IS_HEADING ?></th>
    <th><?php echo TEXT_IS_REQUIRED ?></th>        
    <th><?php echo TEXT_TYPE ?></th>
  </tr>
</thead>
<tbody>
<?php

$fields_sql_query = '';

$entity_info = db_find('app_entities',filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));

//include fieldtype_parent_item_id only for sub entities
if($entity_info['parent_id']==0)
{
	$fields_sql_query .= " and f.type not in ('fieldtype_parent_item_id')";
}

$reserverd_fields_types = array_merge(fields_types::get_reserved_data_types(),fields_types::get_users_types());
$reserverd_fields_types_list = "'" . implode("','", $reserverd_fields_types). "'";

$fields_query = db_query("select f.*, t.name as tab_name, if(f.type in (" . $reserverd_fields_types_list . "),-1,t.sort_order) as tab_sort_order from app_fields f, app_forms_tabs t where f.type not in ('fieldtype_action') and f.entities_id='" . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . "' and f.forms_tabs_id=t.id {$fields_sql_query} order by tab_sort_order, t.name, f.sort_order, f.name");

if(db_num_rows($fields_query)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

while($v = db_fetch_array($fields_query)):
?>
<tr>

<?php if(in_array($v['type'],$reserverd_fields_types)){ ?>
	
	<td></td>
	<td style="white-space: nowrap;" align="center"><?php echo button_icon_edit(url_for('entities/fields_form_internal','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING). '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) ?></td>
	<td><?php echo (in_array(filter_var($v['type'],FILTER_SANITIZE_STRING),fields_types::get_reserved_types()) ? tooltip_icon('[' . str_replace('fieldtype_','',$v['type']). ']') : htmlentities($v['id'])) ?></td>
	<td></td>
	<td><?php echo (strlen($v['name']) ? htmlentities($v['name']):fields_types::get_title(filter_var($v['type'],FILTER_SANITIZE_STRING))) ?></td>
	<td><?php echo htmlentities($v['short_name'])?></td>
	<td></td>
	<td><?php echo render_bool_value($v['is_heading'],true) ?></td>
	<td><?php echo render_bool_value(1,true) ?></td>
	<td class="nowrap"><?php echo fields_types::get_title($v['type']) ?></td>
	
<?php }else{ ?>
  
  <td><?php echo input_checkbox_tag('fields[]',filter_var($v['id'],FILTER_SANITIZE_STRING),array('class'=>'fields_checkbox'))?></td>
  <td style="white-space: nowrap;">
  	<?php echo button_icon_delete(url_for('entities/fields_delete','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) . ' ' . button_icon_edit(url_for('entities/fields_form','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING). '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) ?>  	
  </td>
  <td><?php echo htmlentities($v['id']) ?></td>
  <td><?php echo htmlentities($v['tab_name']) ?></td>    
  <td><?php echo fields_types::render_field_name(filter_var($v['name'],FILTER_SANITIZE_STRING),filter_var($v['type'],FILTER_SANITIZE_STRING),filter_var($v['id'],FILTER_SANITIZE_STRING)) ?></td>
  <td><?php echo htmlentities($v['short_name'])?></td>
  <td><?php echo tooltip_icon($v['notes'],'left') ?></td>
  <td><?php echo render_bool_value($v['is_heading'],true) ?></td>
  <td><?php echo render_bool_value($v['is_required'],true) ?></td>  
  <td class="nowrap"><?php echo fields_types::get_title($v['type']) ?></td>

<?php }?>
    
</tr>  
<?php endwhile ?>
</tbody>
</table>
</div>

<script>
  $('#select_all_fields').click(function(){
    select_all_by_classname('select_all_fields','fields_checkbox')    
  })
</script>