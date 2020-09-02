<?php

//check access
if($app_user['group_id']>0)
{
  redirect_to('dashboard/access_forbidden');
}

switch($app_module_action)
{
  case 'save':
  
      $yaxis = array();
      foreach(filter_var_array($_POST['yaxis']) as $v)
      {
        if($v>0) $yaxis[] = $v; 
      }
      
      $sql_data = array('name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),                        
                        'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),
                        'allowed_groups'=>(isset($_POST['allowed_groups']) ? implode(',',filter_var_array($_POST['allowed_groups'])):''),
                        'xaxis'=>filter_var($_POST['xaxis'],FILTER_SANITIZE_STRING),
                        'yaxis'=>implode(',',filter_var_arry($yaxis)),                        
                        'chart_type'=>filter_var($_POST['chart_type'],FILTER_SANITIZE_STRING),
                        'period'=>filter_var($_POST['period'],FILTER_SANITIZE_STRING),                        
                        );
                        
                                                            
      if(isset($_GET['id']))
      {        
        db_perform('app_ext_graphicreport',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");       
      }
      else
      {                               
        db_perform('app_ext_graphicreport',$sql_data);                    
      }
                                          
      redirect_to('ext/graphicreport/configuration');
      
    break;
  case 'delete':
      $obj = db_find('app_ext_graphicreport',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
      
      db_delete_row('app_ext_graphicreport',filter_var($_GET['id'],FILTER_SANITIZE_STRING));   
      
      $report_info_query = db_query("select * from app_reports where reports_type='graphicreport" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)). "'");
      if($report_info = db_fetch_array($report_info_query))
      {          
        reports::delete_reports_by_id($report_info['id']);                                 
      }                           
      
      $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$obj['name']),'success');
      
      redirect_to('ext/graphicreport/configuration');
    break;      
  case 'get_entities_fields':
      
        $entities_id = filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING);
        
        $obj = array();

        if(isset($_POST['id']))
        {
          $obj = db_find('app_ext_graphicreport',filter_var($_POST['id'],FILTER_SANITIZE_STRING));  
        }
        else
        {
          $obj = db_show_columns('app_ext_graphicreport');
        }
        
        $xaxis_fields = array();
        $fields_query = db_query("select * from app_fields where type in ('fieldtype_date_added','fieldtype_input_date','fieldtype_input_datetime') and entities_id='" . db_input($entities_id) . "'");
        while($fields = db_fetch_array($fields_query))
        {
          $xaxis_fields[filter_var($fields['id'],FILTER_SANITIZE_STRING)] = (filter_var($fields['type'],FILTER_SANITIZE_STRING)=='fieldtype_date_added' ? TEXT_FIELDTYPE_DATEADDED_TITLE : filter_var($fields['name'],FILTER_SANITIZE_STRING)); 
        }
        
        $html = '
         <div class="form-group">
          	<label class="col-md-3 control-label" for="allowed_groups">' . TEXT_EXT_HORIZONTAL_AXIS . '</label>
            <div class="col-md-9">	
          	   ' .  select_tag('xaxis',$xaxis_fields,$obj['xaxis'],array('class'=>'form-control input-large required')) . '
               ' . tooltip_text(TEXT_EXT_HORIZONTAL_AXIS_INFO) . '
            </div>			
          </div>
        ';
        
        
        $yaxis_fields = array();
        $yaxis_fields_select = array(''=>'');
        $fields_query = db_query("select * from app_fields where type in ('fieldtype_input_numeric','fieldtype_input_numeric_comments','fieldtype_formula','fieldtype_js_formula','fieldtype_mysql_query','fieldtype_days_difference','fieldtype_hours_difference') and entities_id='" . db_input($entities_id) . "'");
        while($fields = db_fetch_array($fields_query))
        {
          $yaxis_fields[filter_var($fields['id'],FILTER_SANITIZE_STRING)] =  filter_var($fields['name'],FILTER_SANITIZE_STRING); 
          $yaxis_fields_select[filter_var($fields['id'],FILTER_SANITIZE_STRING)] =  filter_var($fields['name'],FILTER_SANITIZE_STRING);
        }
        
        if(count($yaxis_fields)==0)
        {
          $yaxis_fields = array(''=>'');
        }
       
        $obj_yaxis = explode(',',$obj['yaxis']);        
        $is_required = true;        
        $key = 0;
        foreach($yaxis_fields as $v)
        {
          $html .= '
           <div class="form-group">
            	<label class="col-md-3 control-label" for="allowed_groups">' . TEXT_EXT_VERTICAL_AXIS . ' ' . ($key+1) . '</label>
              <div class="col-md-9">	
            	   ' .  select_tag('yaxis[]',($key==0 ? $yaxis_fields:$yaxis_fields_select),(isset($obj_yaxis[$key]) ? $obj_yaxis[$key]:''),array('class'=>'form-control input-large ' . ($is_required ? 'required':''))) . '
                 ' . tooltip_text(TEXT_EXT_VERTICAL_AXIS_INFO) . '
              </div>			
            </div>
          ';                  
          $is_required = false;
          $key++;
        }
        
        echo $html;
        
      exit();
    break;
}