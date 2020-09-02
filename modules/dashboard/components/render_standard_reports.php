<?php

//get report entity info
$entity_info = db_find('app_entities',filter_var($reports['entities_id'],FILTER_SANITIZE_STRING));
$entity_cfg = new entities_cfg(filter_var($reports['entities_id'],FILTER_SANITIZE_STRING));

//check if parent reports was not set
if($entity_info['parent_id']>0 and $reports['parent_id']==0)
{
	reports::auto_create_parent_reports(filter_var($reports['id'],FILTER_SANITIZE_STRING));
}

//get report entity access schema
$access_schema = users::get_entities_access_schema(filter_var($reports['entities_id'],FILTER_SANITIZE_STRING),filter_var($app_user['group_id'],FILTER_SANITIZE_STRING));
$user_access_schema = users::get_entities_access_schema(1,filter_var($app_user['group_id'],FILTER_SANITIZE_STRING));

$add_button = '';
if(users::has_access('create',$access_schema) and $entity_cfg->get('reports_hide_insert_button')!=1)
{
	if($entity_info['parent_id']==0)
	{
		$url = url_for('items/form','path=' . filter_var($reports['entities_id'],FILTER_SANITIZE_STRING) . '&redirect_to=report_' . filter_var($reports['id'],FILTER_SANITIZE_STRING));
	}
	elseif($entity_info['parent_id']==1 and !users::has_access('view',$user_access_schema))
	{
		$url = url_for('items/form','path=1-' . filter_var($app_user['id'],FILTER_SANITIZE_STRING) . '/' . filter_var($reports['entities_id'],FILTER_SANITIZE_STRING) . '&redirect_to=report_' . filter_var($reports['id'],FILTER_SANITIZE_STRING));
	}
	else
	{
		$url = url_for('reports/prepare_add_item','reports_id=' . filter_var($reports['id'],FILTER_SANITIZE_STRING));
	}
	$add_button = button_tag((strlen($entity_cfg->get('insert_button'))>0 ? $entity_cfg->get('insert_button') : TEXT_ADD), $url) . ' ';
}



$listing_container = 'entity_items_listing' . filter_var($reports['id'],FILTER_SANITIZE_STRING) . '_' . filter_var($reports['entities_id'],FILTER_SANITIZE_STRING);

$gotopage = (isset($_GET['gotopage'][filter_var($reports['id'],FILTER_SANITIZE_STRING)]) ? (int)filter_var($_GET['gotopage'],FILTER_SANITIZE_STRING)[filter_var($reports['id'],FILTER_SANITIZE_STRING)]:1);

$with_selected_menu = '';

if(users::has_access('export_selected',$access_schema) and users::has_access('export',$access_schema))
{
	$with_selected_menu .= '<li>' . link_to_modalbox('<i class="fa fa-file-excel-o"></i> ' . TEXT_EXPORT,url_for('items/export','path=' . filter_var($reports["entities_id"],FILTER_SANITIZE_STRING)  . '&reports_id=' . filter_var($reports['id'],FILTER_SANITIZE_STRING) ))  . '</li>';
}

$with_selected_menu .= plugins::include_dashboard_with_selected_menu_items(filter_var($reports['id'],FILTER_SANITIZE_STRING));

$report_title_html = '
  		<div class="row">
        <div class="col-md-12"><h3 class="page-title"><a href="' . url_for('reports/view','reports_id=' . filter_var($reports['id'],FILTER_SANITIZE_STRING)) . '">' . filter_var($reports['name'],FILTER_SANITIZE_STRING) . '</a></h3></div>
      </div>
  		';

if(!strlen($add_button) and !strlen($with_selected_menu))
{
	$add_button = $report_title_html;
	$report_title_html = (!$has_reports_on_dashboard ? '<br><br>':'');
}


$listing = new items_listing(filter_var($reports['id'],FILTER_SANITIZE_STRING));
$curren_listing_type = $listing->get_listing_type();
$select_all_html = '';
if(in_array($curren_listing_type,['grid','mobile']))
{
	if(listing_types::has_action_field($curren_listing_type,filter_var($reports['entities_id'],FILTER_SANITIZE_STRING)))
	{
		$select_all_html = '
			  			<label>' . input_checkbox_tag('select_all_items',filter_var($reports['id'],FILTER_SANITIZE_STRING),array('class'=>$listing_container . '_select_all_items_force','data-container-id'=>$listing_container))  . ' ' . TEXT_SELECT_ALL. '</label>
		  			';
	}
}


$listing_search_form = render_listing_search_form(filter_var($reports["entities_id"],FILTER_SANITIZE_STRING),$listing_container,filter_var($reports['id'],FILTER_SANITIZE_STRING));

echo '

    <div class="row dashboard-reports-container" id="dashboard-reports-container">
      <div class="col-md-12">

      ' . $report_title_html . '

      <div class="row">
        <div class="' . (strlen($listing_search_form) ? 'col-sm-6':'col-sm-12') . '">
      		<div class="entitly-listing-buttons-left">
             ' . $add_button . '

            ' . (strlen($with_selected_menu) ? '
            <div class="btn-group">
      				<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" data-hover="dropdown">
      				' . TEXT_WITH_SELECTED . '<i class="fa fa-angle-down"></i>
      				</button>
      				<ul class="dropdown-menu" role="menu">
      					' . $with_selected_menu. '
      				</ul>
      			</div>':'') .
      			$select_all_html .
      			'
      		</div>
      	</div> 
      	' . (strlen($listing_search_form) ? '				
		        <div class="col-sm-6">
		         ' . $listing_search_form . '
		        </div>':'') . '
      </div>

      <div id="' . $listing_container . '" class="entity_items_listing"></div>
      ' . input_hidden_tag($listing_container . '_order_fields',$reports['listing_order_fields']) . '
      ' . input_hidden_tag($listing_container . '_has_with_selected',(strlen($with_selected_menu) ? 1:0)) . '

      </div>
    </div>


    <script>
      $(function() {
        load_items_listing("' . $listing_container . '",' . $gotopage . ');
      });
    </script>
  ';