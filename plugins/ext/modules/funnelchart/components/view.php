<?php

//create default entity report for logged user
$reports_info_query = db_query("select * from app_reports where entities_id='" . db_input($reports['entities_id']). "' and reports_type='funnelchart" . $reports['id']. "' and created_by='" . $app_logged_users_id . "'");
if(!$reports_info = db_fetch_array($reports_info_query))
{
	$sql_data = array('name'=>'',
			'entities_id'=>$reports['entities_id'],
			'reports_type'=>'funnelchart' . $reports['id'],
			'in_menu'=>0,
			'in_dashboard'=>0,
			'listing_order_fields'=>'',
			'created_by'=>$app_logged_users_id,
	);
	
	

	db_perform('app_reports',$sql_data);
	$fiters_reports_id = db_insert_id();

	reports::auto_create_parent_reports($fiters_reports_id);

	$reports_info = db_find('app_reports',$fiters_reports_id);
}
else
{
	$fiters_reports_id = $reports_info['id'];
}

if (!app_session_is_registered('funnelchart_type'))
{
	$funnelchart_type = array();	
	app_session_register('funnelchart_type');
}

if(!isset($funnelchart_type[$reports['id']]))
{
	$funnelchart_type[$reports['id']] = $reports['type'];
}

//start display report

if($app_module_path=='ext/funnelchart/view')
{
	$filters_preivew = new filters_preivew($fiters_reports_id);
	$filters_preivew->redirect_to = 'funnelchart' . $reports['id'];
	
	if(isset($_GET['path']))
	{
		$filters_preivew->path = filter_var($_GET['path'],FILTER_SANITIZE_STRING);
		$filters_preivew->include_paretn_filters = false;
	}
	
	echo $filters_preivew->render();
}


$entity_info = db_find('app_entities',$reports['entities_id']);

$field = db_find('app_fields',$reports['group_by_field']);

$cfg = new fields_types_cfg($field['configuration']);

if(in_array($field['type'],['fieldtype_entity','fieldtype_entity_ajax','fieldtype_entity_multilevel']))
{
	$funnel_choices = funnelchart::get_choices_by_entity($cfg->get('entity_id'));
}
elseif($field['type'] == 'fieldtype_parent_item_id')
{
	$funnel_choices = funnelchart::get_choices_by_entity($entity_info['parent_id']);
}
elseif($field['type'] == 'fieldtype_users')
{		
	$funnel_choices = users::get_choices_by_entity($reports['entities_id']);
}
elseif($field['type'] == 'fieldtype_created_by')
{
	$funnel_choices = users::get_choices_by_entity($reports['entities_id'],'create');
}
else 
{	
	//use global lists if exsit
	if($cfg->get('use_global_list')>0)
	{
		$funnel_choices = global_lists::get_choices($cfg->get('use_global_list'),false);
	}
	else
	{
		$funnel_choices = fields_choices::get_choices($field['id'],false);
	}
}

//print_r($funnel_choices);

$funnel_info_choices = array();

foreach($funnel_choices as $id=>$value)
{
	//exclude choices
	if(in_array($id,explode(',',$reports['exclude_choices']))) continue;
	
	$funnel_info_choices[$id]['count'] = 0;
	 
	if(strlen($reports['sum_by_field']))
	{
		foreach(explode(',',$reports['sum_by_field']) as $k)
		{
			$funnel_info_choices[$id]['field_' . $k] = 0;
		}
	}
}

//build items listing
$listing_sql_query = '';
$listing_sql_query_select = '';
$listing_sql_query_having = '';
$sql_query_having = array();

//filter items by parent
/*if($parent_entity_item_id>0)
 {
 $listing_sql_query .= " and e.parent_item_id='" . db_input($parent_entity_item_id) . "'";
 }*/

//prepare forumulas query
$listing_sql_query_select = fieldtype_formula::prepare_query_select(filter_var($reports['entities_id'],FILTER_SANITIZE_STRING), $listing_sql_query_select);

//prepare filters
$listing_sql_query = reports::add_filters_query($fiters_reports_id,$listing_sql_query);

//prepare having query for formula fields
if(isset($sql_query_having[$reports['entities_id']]))
{
	$listing_sql_query_having  = reports::prepare_filters_having_query($sql_query_having[filter_var($reports['entities_id'],FILTER_SANITIZE_STRING)]);
}

if(isset($_GET['path']))
{
	$path_info = items::parse_path(filter_var($_GET['path'],FILTER_SANITIZE_STRING));
	if($path_info['parent_entity_item_id']>0)
	{
		$listing_sql_query .= " and e.parent_item_id='" . filter_var($path_info['parent_entity_item_id'],FILTER_SANITIZE_STRING) . "'";
	}
}

//check view assigned only access
$listing_sql_query = items::add_access_query(filter_var($reports['entities_id'],FILTER_SANITIZE_STRING),$listing_sql_query);

//exclude choices
if(strlen($reports['exclude_choices']))
{
	if($field['type'] == 'fieldtype_parent_item_id')
	{
		$listing_sql_query .= " and parent_item_id not in (".filter_var($reports['exclude_choices'],FILTER_SANITIZE_STRING).")";
	}
	elseif($field['type'] == 'fieldtype_created_by')
	{
		$listing_sql_query .= " and created_by not in (".filter_var($reports['exclude_choices'],FILTER_SANITIZE_STRING).")";
	}
	else
	{	
		$listing_sql_query .= " and field_".filter_var($reports['group_by_field'],FILTER_SANITIZE_STRING)." not in (".filter_var($reports['exclude_choices'],FILTER_SANITIZE_STRING).")";
	}
}

//add having query
$listing_sql_query .= $listing_sql_query_having;

$items_sql_query = "select * {$listing_sql_query_select} from app_entity_" . filter_var($reports['entities_id'] ,FILTER_SANITIZE_STRING). " e where id>0 " . $listing_sql_query;
$items_query = db_query($items_sql_query);
$count_items = db_num_rows($items_query);

while($item = db_fetch_array($items_query))
{
	//print_r($item);
	//echo '<br>';
	
	if($field['type'] == 'fieldtype_parent_item_id')
	{
		$values = $item['parent_item_id'];
	}	
	elseif($field['type'] == 'fieldtype_created_by')
	{
		$values = $item['created_by'];
	}
	else 
	{
		$values = $item['field_' . $reports['group_by_field']];
	}
		
				
	if(strlen($values))
	{	
		foreach(explode(',',$values) as $value)
		{	
			//exclude choices
			if(in_array($value,explode(',',$reports['exclude_choices']))) continue;
			
			$funnel_info_choices[$value]['count']++;
			 
			if(strlen($reports['sum_by_field']))
			{
				foreach(explode(',',$reports['sum_by_field']) as $k)
				{
					if(strlen($item['field_' . $k]))
						$funnel_info_choices[$value]['field_' . $k] += $item['field_' . $k];
				}
			}
		}
	}
}

//echo '<pre>';
//print_r($funnel_info_choices);
//echo '</pre>';

$choices_backgrounds = fields::get_field_choices_background_data($reports['group_by_field']);

//buld data_js
$data_js = array();
foreach($funnel_info_choices as $choices_id=>$value)
{
	$sum_js_tip = '';
	 
	$conversion = ($value['count']>0 ? floor($value['count']/$count_items*100) : 0);
	 
	$data = "
  		name: '" . addslashes(trim(strip_tags($funnel_choices[$choices_id]))) . "',
  		y: " . $value['field_367'] . ",
  		" . (isset($choices_backgrounds[$choices_id]) ? "color: '" .  $choices_backgrounds[$choices_id]['background'] . "',":'') . "
  		filter_by: '" . $reports['group_by_field'] . ":" . $choices_id . "',
  		conversion: '" . $conversion . "%'";

	if(strlen($reports['sum_by_field']))
	foreach(explode(',',$reports['sum_by_field']) as $k)
	{

		$field_value = $value['field_' .$k];

		$field = db_find('app_fields', $k);
		$cfg = new fields_types_cfg($field['configuration']);
			
		if(strlen($cfg->get('number_format'))>0)
		{
			$format = explode('/',str_replace('*','',$cfg->get('number_format')));
				
			$field_value = number_format($field_value,$format[0],$format[1],$format[2]);
				
			//add prefix and sufix
			$field_value = (strlen($field_value) ? $cfg->get('prefix') . $field_value . $cfg->get('suffix') : '');
		}

		$data .= ",field_{$k}: '" . $field_value . "'";

		$sum_js_tip .= "+'<br>" . addslashes($field['name']) . ": '+this.point.field_{$k}";
	}
	 
	$data_js[] = "{" . $data . "}";
}

//print_r($data_js);



if($app_module_path=='ext/funnelchart/view')
{
	$btn = '
	  		<div class="funnelchart-type-switch">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default ' . ($funnelchart_type[$reports['id']]=='funnel' ? 'active':'') . '">
						<input name="funnelchart_view_mode" type="radio" value="funnel" class="toggle funnelchart_view_mode">' . TEXT_EXT_FUNNEL_CHART. '</label>
						<label class="btn btn-default ' . ($funnelchart_type[$reports['id']]=='bars' ? 'active':'') . '">
						<input name="funnelchart_view_mode" type="radio" value="bars" class="toggle funnelchart_view_mode">' . TEXT_EXT_BARS_CAHRT . '</label>
						<label class="btn btn-default ' . ($funnelchart_type[$reports['id']]=='table' ? 'active':'') . '">
						<input name="funnelchart_view_mode" type="radio" value="table" class="toggle funnelchart_view_mode">' . TEXT_EXT_TABLE . '</label>
					</div>
				</div>';
	
	echo $btn;
}
?>

<div id="funnelchart_container_<?php echo $reports['id'] ?>" class="funnelchart"></div>

<!-- handle listing -->
<?php $listing_container = 'entity_items_listing' . filter_var($reports_info['id'],FILTER_SANITIZE_STRING) . '_' . filter_var($reports_info['entities_id'],FILTER_SANITIZE_STRING); ?>

<?php
	if($funnelchart_type[$reports['id']]=='table')
	{	
		require(component_path('ext/funnelchart/table_view'));
	}
	else
	{
		require(component_path('ext/funnelchart/chart_view'));
	}
	
	echo '<br>';
	
	if($app_module_path=='ext/funnelchart/view')
	{	
?>

<h3 class="page-title" id="listing_title"></h3>
<div id="<?php echo htmlentities($listing_container);  ?>" class="entity_items_listing"><?php echo htmlentities(TEXT_EXT_CLICK_CHART_BAR) ?></div>

<?php echo input_hidden_tag($listing_container . '_order_fields',filter_var($reports_info['listing_order_fields'],FILTER_SANITIZE_STRING)) ?>
<?php echo input_hidden_tag($listing_container . '_has_with_selected',0) ?>
<?php echo input_hidden_tag($listing_container . '_force_filter_by','') ?>
<?php echo (isset($_GET['path']) ? input_hidden_tag('entity_items_listing_path',$app_path) : '') ?>

<?php require(component_path('items/load_items_listing.js')); ?>


<script>
$(function(){
	$('.funnelchart_view_mode').change(function(){
		location.href="<?php echo url_for('ext/funnelchart/view','id=' . $reports['id'] . '&action=set_view_mode' . (isset($_GET['path']) ? '&path=' . $app_path:'')) ?>&view_mode="+$(this).val();
  })		
})

function funnelchart_items_listin(name, filter_by)
{
	$('#listing_title').html(name)
  $('#<?php echo htmlentities($listing_container) ?>_force_filter_by').val(filter_by)
	load_items_listing('<?php echo htmlentities($listing_container)  ?>',1);

	return false;
}
</script>

<?php 
	} 
?>