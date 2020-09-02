<?php

//field fields heading
$thead = '';
if(strlen($reports['sum_by_field']))
foreach(explode(',',filter_var($reports['sum_by_field'],FILTER_SANITIZE_STRING)) as $id)
{
	$field = db_find('app_fields', $id);
	$thead .= '<th>' . filter_var($field['name'],FILTER_SANITIZE_STRING) . '</th>';
	$totals['field_' . $id] = 0;
}

//build table heading
$field = db_find('app_fields', $reports['group_by_field']);
$html = '	
	<div class="table-scrollable">	
		<table class="table table-striped table-bordered table-hover">
			<thead>
					<th>' . filter_var($field['name'],FILTER_SANITIZE_STRING) . '</th>
					' . $thead . '
			</thead>
			<tbody>';

//build table body
foreach(filter_var_array($funnel_info_choices) as $choices_id=>$value)
{
	
	
	//fields values and totals
	$tbody = '';
	if(strlen($reports['sum_by_field']))
	foreach(explode(',',filter_var($reports['sum_by_field'],FILTER_SANITIZE_STRING)) as $id)
	{							
		$tbody .= '<td>' . fieldtype_input_numeric::number_format($value['field_' .$id],$app_fields_cache[filter_var($reports['entities_id'],FILTER_SANITIZE_STRING)][$id]['configuration']) . '</td>';
		
		$totals['field_' .$id] += $value['field_' .$id];
	}
	
	$html .= '
			<tr>
				<td><a href="#" onclick="return funnelchart_items_listin(\'' . '\',\'' . $reports['group_by_field'] . ':' . $choices_id . '\')">' . $funnel_choices[$choices_id] . '</a></td>
				' . $tbody . '
			</tr>
			';	
}

$tfoot = '';
if(strlen($reports['sum_by_field']))
foreach(explode(',',filter_var($reports['sum_by_field'],FILTER_SANITIZE_STRING)) as $id)
{
	$tfoot .= '<td>' . fieldtype_input_numeric::number_format($totals['field_' .$id],$app_fields_cache[filter_var($reports['entities_id'],FILTER_SANITIZE_STRING)][$id]['configuration']) . '</td>';	
}

$html .= '
		</tbody>
		<tfoot>
			<tr>
				<th>' . $totals['count'] . '</th>

				' . $tfoot . '		
			</tr>
		</tfoot>
		</table>
	</div>					
		';

echo $html;