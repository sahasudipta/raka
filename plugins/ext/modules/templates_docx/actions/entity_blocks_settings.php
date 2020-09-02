<?php

$field_query = db_query("select * from app_fields where id=" . db_input(filter_var(_POST('fields_id'),FILTER_SANITIZE_STRING)));
if(!$field = db_fetch_array($field_query))
{
    exit();
}

if(isset($_POST['id']))
{
    $obj = db_find('app_ext_items_export_templates_blocks',filter_var($_POST['id'],FILTER_SANITIZE_STRING));
}
else
{
    $obj = db_show_columns('app_ext_items_export_templates_blocks');
}


$cfg = new settings($field['configuration']);

$settings = new settings(filter_var($obj['settings'],FILTER_SANITIZE_STRING));

$html = '';


switch(filter_var($field['type'],FILTER_SANITIZE_STRING))
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
    case 'fieldtype_attachments':
    case 'fieldtype_image':
    case 'fieldtype_user_photo':
        $html = '
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_WIDHT . '</label>
                <div class="col-md-9">' . input_tag('settings[width]',$settings->get('width'),['class'=>'form-control input-small number']) . '</div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label" for="fields_id">' . TEXT_HEIGHT . '</label>
                    <div class="col-md-9">' . input_tag('settings[height]',$settings->get('height'),['class'=>'form-control input-small number']) . '</div>
            </div>
         ';
        
        if($field['type']=='fieldtype_attachments')
        {
            $html .= '
                <div class="form-group">
                    <label class="col-md-3 control-label" for="fields_id">' . TEXT_GRID . '</label>
                    <div class="col-md-9">' . input_tag('settings[grid]',$settings->get('grid',1),['class'=>'form-control input-xsmall number']) . '</div>
                </div>';
        }
        
        break;
}

echo $html;