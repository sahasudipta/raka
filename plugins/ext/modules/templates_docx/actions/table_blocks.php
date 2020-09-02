<?php

switch($app_module_action)
{
    case 'save':
        $sql_data = [
        'templates_id' => filter_var($template_info['id'],FILTER_SANITIZE_STRING),
        'block_type' => 'body_cell',
        'parent_id' => filter_var($parent_block['id'],FILTER_SANITIZE_STRING),
        'fields_id' => filter_var(_POST('fields_id'),FILTER_SANITIZE_STRING),
        'settings' => (isset($_POST['settings']) ? json_encode(filter_var($_POST['settings'],FILTER_SANITIZE_STRING)) : ''),
        'sort_order' => filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),
        ];
        
        if(isset($_GET['id']))
        {
            db_perform('app_ext_items_export_templates_blocks',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        }
        else
        {
            db_perform('app_ext_items_export_templates_blocks',$sql_data);
        }
        
        redirect_to('ext/templates_docx/table_blocks','templates_id=' . filter_var($template_info['id'],FILTER_SANITIZE_STRING) . '&parent_block_id=' . filter_var($parent_block['id'],FILTER_SANITIZE_STRING));
        break;
    case 'delete':
        if(isset($_GET['id']))
        {
            export_templates_blocks::delele_block(filter_var(_GET('id'),FILTER_SANITIZE_STRING));
            
            redirect_to('ext/templates_docx/table_blocks','templates_id=' . filter_var($template_info['id'],FILTER_SANITIZE_STRING) . '&parent_block_id=' . filter_var($parent_block['id'],FILTER_SANITIZE_STRING));
        }
        break;
    case 'get_field_settings':
        $field_query = db_query("select type from app_fields where id=" . filter_var(_POST('fields_id'),FILTER_SANITIZE_STRING));
        if(!$field = db_fetch_array($field_query))
        {
            exit();
        }
        
        if($_GET['id']>0)
        {      
            $obj = db_find('app_ext_items_export_templates_blocks',filter_var(_GET('id'),FILTER_SANITIZE_STRING));
            $settings = new settings(filter_var($obj['settings'],FILTER_SANITIZE_STRING));
        }
        else
        {
            $settings = new settings('');
        }
        
        $html = '';
        
        switch($field['type'])
        {
            case 'fieldtype_input_date':
                $html = '
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="fields_id">' . TEXT_DATE_FORMAT . '</label>
                        <div class="col-md-9">' . input_tag('settings[date_format]',$settings->get('date_format'),['class'=>'form-control input-small'])  . tooltip_text(TEXT_DEFAULT .': ' . CFG_APP_DATE_FORMAT . ', ' . TEXT_DATE_FORMAT_IFNO). '</div>
                    </div>';
                
                break;
            case 'fieldtype_date_added':
            case 'fieldtype_date_updated':
            case 'fieldtype_dynamic_date':
            case 'fieldtype_input_datetime':
                $html = '
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="fields_id">' . TEXT_DATE_FORMAT . '</label>
                        <div class="col-md-9">' . input_tag('settings[date_format]',$settings->get('date_format'),['class'=>'form-control input-small'])  . tooltip_text(TEXT_DEFAULT .': ' . CFG_APP_DATETIME_FORMAT . ', ' . TEXT_DATE_FORMAT_IFNO). '</div>
                    </div>';
                
                break;
            case 'fieldtype_user_photo':
            case 'fieldtype_image':
                $html = '
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="fields_id">' . TEXT_WIDHT . '</label>
                        <div class="col-md-9">' . input_tag('settings[width]',$settings->get('width',100),['class'=>'form-control input-small number']) . '</div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="fields_id">' . TEXT_HEIGHT . '</label>
                            <div class="col-md-9">' . input_tag('settings[height]',$settings->get('height',100),['class'=>'form-control input-small number']) . '</div>
                    </div>
                 ';
                break;
            case 'fieldtype_input_numeric':
            case 'fieldtype_input_numeric_comments':
            case 'fieldtype_formula':
            case 'fieldtype_js_formula':
            case 'fieldtype_mysql_query':
            case 'fieldtype_ajax_request':
                $html = '
                  <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id">' . tooltip_icon(TEXT_NUMBER_FORMAT_INFO) . TEXT_NUMBER_FORMAT . '</label>
                    <div class="col-md-9">' .  input_tag('settings[number_format]',$settings->get('number_format',CFG_APP_NUMBER_FORMAT),['class'=>'form-control input-small input-masked','data-mask'=>'9/~/~']) . '</div>			
                  </div>  
                  <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id">' . TEXT_PREFIX . '</label>
                    <div class="col-md-9">' .  input_tag('settings[content_value_prefix]',$settings->get('content_value_prefix',''),['class'=>'form-control input-medium']) . '</div>			
                  </div>
                  
                  <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="fields_id">' .  TEXT_SUFFIX . '</label>
                    <div class="col-md-9">' .  input_tag('settings[content_value_suffix]',$settings->get('content_value_suffix',''),['class'=>'form-control input-medium']) . '</div>			
                  </div>
                  
                  <div class="form-group settings-list">
                    <label class="col-md-3 control-label" for="settings_calculate_totals">' .  TEXT_CALCULATE_TOTALS . '</label>
                    <div class="col-md-9"><p class="form-control-static">' .  input_checkbox_tag('settings[calculate_totals]',1,['checked'=>$settings->get('calculate_totals')]) . '</p></div>			
                  </div>';
                break;
        }
        
        echo $html;
        
        exit();
        
        break;
}