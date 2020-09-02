<?php
class common_filters
{
	public $entities_id, $reports_id, $parent_item_id;
	
	function __construct($entities_id, $reports_id)
	{
		$this->entities_id = $entities_id;
		$this->reports_id = $reports_id;
		$this->parent_item_id=0;
	}
	
	function render($title)
	{		
		global $app_path, $app_current_users_filter, $app_user;
		
		$reports_query = db_query("select id, name from app_reports where entities_id='" . db_input($this->entities_id) . "' and reports_type='common_filters' and in_dashboard_counter=0 and (length(users_groups)=0 or find_in_set(" . filter_var($app_user['group_id'],FILTER_SANITIZE_STRING). ",users_groups)) order by dashboard_sort_order");
				
		$html = '<h3 class="page-title">' . $title . '</h3>';
		
		if(db_num_rows($reports_query))
		{			
			$html = '
					<nav class="navbar navbar-default">
	  				<div class="container-fluid">
					
							<div class="navbar-header">
					      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-common_filters">
				    		<span class="sr-only"></span>
				    		<span class="fa fa-bar "></span>
				    		<span class="fa fa-bar fa-align-justify"></span>
				    		<span class="fa fa-bar"></span>
				  		</button>
					      <div class="navbar-brand">' . $title . '</div>
					    </div>
					  	<div class="collapse navbar-collapse" id="navbar-common_filters">
	      				<ul class="nav navbar-nav">';
			
			$script = '';
			
			while($reports = db_fetch_array($reports_query))
			{
				$is_selected = false;
							
				if(isset($app_current_users_filter[$this->reports_id]))
				{
					$is_selected = ($app_current_users_filter[$this->reports_id]==filter_var($reports['name'],FILTER_SANITIZE_STRING) ? true:false);
				}
				
				$redirect_to = (strlen($app_path)>0 ? 'listing':'report');
				
				$html .= '<li class="' . ($is_selected ? 'selected':''). '"><a href="' . url_for('reports/common_filters','action=use&redirect_to=' . $redirect_to . '&reports_id=' . filter_var($this->reports_id,FILTER_SANITIZE_STRING) . '&use_filters=' . filter_var($reports['id'],FILTER_SANITIZE_STRING) . (strlen($app_path) ? '&path=' . $app_path:'')) . '">' . filter_var($reports['name'],FILTER_SANITIZE_STRING) . '<span id="common_filters_' . filter_var($reports['id'],FILTER_SANITIZE_STRING) . '_count"></span></a></li>';
				
				$script .= '
						$("#common_filters_' . filter_var($reports['id'],FILTER_SANITIZE_STRING) . '_count").load("' . url_for("dashboard/common_filters_count","reports_id=" . filter_var($reports['id'],FILTER_SANITIZE_STRING) . '&parent_item_id=' . filter_var($this->parent_item_id,FILTER_SANITIZE_STRING)) . '");
					';
			}
			
			$html .= '
								</ul>
					    </div>
					  </div>
					</nav>
					';
			
			$html .= '<script>' . $script .'</script>';
		}
		
		$reports_counter = new reports_counter();
		$reports_counter->titile='';
		$reports_counter->common_filter_reports_id=filter_var($this->reports_id,FILTER_SANITIZE_STRING);
		$reports_counter->parent_item_id = filter_var($this->parent_item_id,FILTER_SANITIZE_STRING);
		$reports_counter->reports_query = "select * from app_reports where entities_id='" . db_input($this->entities_id) . "' and reports_type='common_filters' and in_dashboard_counter=1 and (length(users_groups)=0 or find_in_set(" . filter_var($app_user['group_id'],FILTER_SANITIZE_STRING). ",users_groups)) order by dashboard_sort_order";
		$html .= $reports_counter->render();
		
		return $html;
	}
	
}