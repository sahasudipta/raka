<?php $lists_info = db_find('app_global_lists',filter_var($_GET['lists_id'],FILTER_SANITIZE_STRING)) ?>

<h3 class="page-title"><?php echo   filter_var($lists_info['name'],FILTER_SANITIZE_STRING) . ': '.  TEXT_NAV_FIELDS_CHOICES_CONFIG ?></h3>

<ul class="page-breadcrumb breadcrumb">
  <li><?php echo link_to(TEXT_HEADING_GLOBAL_LISTS,url_for('global_lists/lists'))?><i class="fa fa-angle-right"></i></li>  
  <li><?php echo htmlentities($lists_info['name']) ?></li>
</ul>

<p><?php echo htmlentities($lists_info['notes']) ?></p>

<?php 
	echo button_tag(TEXT_BUTTON_ADD_NEW_VALUE,url_for('global_lists/choices_form','lists_id=' . filter_var($_GET['lists_id'],FILTER_SANITIZE_STRING)),true,array('class'=>'btn btn-primary')) . ' ' . 
			 button_tag(TEXT_BUTTON_SORT,url_for('global_lists/choices_sort','lists_id=' . filter_var($_GET['lists_id'],FILTER_SANITIZE_STRING)),true,array('class'=>'btn btn-default')) . ' ' . 
			 button_tag(TEXT_BUTTON_IMPORT,url_for('global_lists/choices_import','lists_id=' . filter_var($_GET['lists_id'],FILTER_SANITIZE_STRING)),true,array('class'=>'btn btn-default')); 
?>

<div class="btn-group">
	<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" data-hover="dropdown">
	<?php echo TEXT_WITH_SELECTED ?> <i class="fa fa-angle-down"></i>
	</button>
	<ul class="dropdown-menu" role="menu">
		<li>
			<?php echo link_to_modalbox(TEXT_BUTTON_EDIT,url_for('global_lists/choices_multiple_edit','lists_id=' . filer_var($_GET['lists_id'],FILTER_SANITIZE_STRING))) ?>
		</li>
		<li>
			<?php echo link_to_modalbox(TEXT_DELETE,url_for('global_lists/choices_multiple_delete','lists_id=' . filter_var($_GET['lists_id'],FILTER_SANITIZE_STRING))) ?>
		</li> 		
  </ul>
</div> 

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
 		<th><?php echo input_checkbox_tag('select_all_fields','',array('class'=>'select_all_fields'))?></th> 
    <th><?php echo TEXT_ACTION?></th>
    <th>#</th>    
    <th><?php echo TEXT_IS_ACTIVE ?></th>
    <th width="100%"><?php echo TEXT_NAME ?></th>            
    <th><?php echo TEXT_IS_DEFAULT ?></th>
    <th><?php echo TEXT_BACKGROUND_COLOR ?></th>        
    <th><?php echo TEXT_SORT_ORDER ?></th>
  </tr>
</thead>
<tbody>
<?php

$tree = global_lists::get_choices_tree(filter_var($_GET['lists_id'],FILTER_SANITIZE_STRING));

if(count($tree)==0) echo '<tr><td colspan="9">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; 

foreach($tree as $v):
?>
<tr>
	<td><?php echo input_checkbox_tag('choices[]',filter_var($v['id'],FILTER_SANITIZE_STRING),array('class'=>'fields_checkbox'))?></td>
  <td style="white-space: nowrap;"><?php 
      echo button_icon_delete(url_for('global_lists/choices_delete','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '&lists_id=' . filter_var($_GET['lists_id'],FILTER_SANITIZE_STRING))); 
      echo ' ' . button_icon_edit(url_for('global_lists/choices_form','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '&lists_id=' . filter_var($_GET['lists_id'],FILTER_SANITIZE_STRING)));
      echo ' ' . button_icon(TEXT_BUTTON_CREATE_SUB_VALUE,'fa fa-plus',url_for('global_lists/choices_form','parent_id=' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '&lists_id=' . filter_var($_GET['lists_id'],FILTER_SANITIZE_STRING))); 
  ?></td>  
  <td><?php echo htmlentities($v['id']) ?></td>
  <td><?php echo render_bool_value($v['is_active']) ?></td>  
  <td><?php echo str_repeat('&nbsp;-&nbsp;',filter_var($v['level'],FILTER_SANITIZE_STRING)) . filter_var($v['name'],FILTER_SANITIZE_STRING) . ' ' . tooltip_icon(filter_var($v['notes'],FILTER_SANITIZE_STRING),'right')  ?></td>
  <td><?php echo render_bool_value($v['is_default']) ?></td>
  <td><?php echo render_bg_color_block(filter_var($v['bg_color'],FILTER_SANITIZE_STRING)) ?></td>
  <td><?php echo htmlentities($v['sort_order']) ?></td>      
</tr>  
<?php endforeach ?>
</tbody>
</table>
</div>

<?php echo '<a href="' . url_for('global_lists/lists') . '" class="btn btn-default"><i class="fa fa-angle-left"></i> ' . TEXT_BUTTON_BACK .'</a>';?>

<script>
  $('#select_all_fields').click(function(){
    select_all_by_classname('select_all_fields','fields_checkbox')    
  })
</script>






