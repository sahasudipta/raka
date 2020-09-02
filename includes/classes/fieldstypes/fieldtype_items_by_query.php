<?php

class fieldtype_items_by_query
{
    public $options;
    
    function __construct()
    {
        $this->options = array('title' => TEXT_FIELDTYPE_ITEMS_BY_QUERY_TITLE);
    }
    
    function get_configuration()
    {
        $cfg = array();
                        
        $cfg[] = array(
            'title'=>TEXT_FIELDTYPE_MYSQL_QUERY_SELECT_ENTITY,
            'name'=>'entity_id',
            'tooltip_icon'=>TEXT_FIELDTYPE_MYSQL_QUERY_SELECT_ENTITY_TOOLTIP,
            'type'=>'dropdown',
            'choices'=>entities::get_choices(),
            'params'=>array('class'=>'form-control input-xlarge chosen-select','onChange'=>'fields_types_ajax_configuration(\'fields_for_search_box\',this.value)'));
                        
        $cfg[] = array('name'=>'fields_for_search_box','type'=>'ajax','html'=>'<script>fields_types_ajax_configuration(\'fields_for_search_box\',$("#fields_configuration_entity_id").val())</script>');
        
        $cfg[] = array('title'=>TEXT_DEBUG_MODE, 'name'=>'debug_mode','type'=>'checkbox');
        
        $cfg[] = array('title'=>TEXT_DISPLAY_AS,
            'name'=>'display_as',
            'type'=>'dropdown',
            'choices'=>array('number'=>TEXT_NUMBER_OF_RECORDS, 'list'=>TEXT_LIST),
            'default' => 'dropdown',
            'params'=>array('class'=>'form-control input-xlarge'));
        
        $cfg[] = array('title'=>TEXT_DEFAULT_TEXT, 'name'=>'default_text', 'type'=>'input', 'tooltip'=>TEXT_DEFAULT . ': ' . TEXT_TOTAL . ' (#)', 'params'=>array('class'=>'form-control input-medium'));
                        
        return $cfg;
    }
    
    
    function get_ajax_configuration($name, $value)
    {
        $cfg = array();
        
        switch($name)
        {
            case 'fields_for_search_box':
                $entities_id = $value;
                                                                                             
                $cfg[] = array(
                    'title'=>TEXT_FIELDTYPE_MYSQL_QUERY_WHERE_QUERY .
                    fields::get_available_fields_helper($entities_id, 'fields_configuration_where_query',entities::get_name_by_id($entities_id)) .
                    '<div style="padding-top: 2px;">' . fields::get_available_fields_helper(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING), 'fields_configuration_where_query',entities::get_name_by_id(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING))) . '</div>',
                    'name'=>'where_query','type'=>'textarea','tooltip'=>TEXT_FIELDTYPE_MYSQL_QUERY_WHERE_QUERY_TIP,'params'=>array('class'=>'form-control input-xlarge code')
                );
                
                $cfg[] = array('title'=>TEXT_HEADING_TEMPLATE . fields::get_available_fields_helper($entities_id, 'fields_configuration_heading_template'), 'name'=>'heading_template','type'=>'textarea','tooltip_icon'=>TEXT_HEADING_TEMPLATE_INFO,'tooltip'=>TEXT_ENTER_TEXT_PATTERN_INFO,'params'=>array('class'=>'form-control input-xlarge code'));
                
                break;
        }
        
        return $cfg;
    }
    
    function render($field,$obj,$params = array())
    {
        return false;
    }
    
    function process($options)
    {
        return false;
    }
    
    function output($options)
    {
        $cfg = new fields_types_cfg($options['field']['configuration']);
        
        $mysql_query = $this->build_query(filter_var_array($options));
        
        $debug_query = ($cfg->get('debug_mode')==1 ? '<div class="alert alert-warning" style="font-size: 11px; margin: 5px; padding: 3px; font-family:monospace;">' . htmlspecialchars($mysql_query) . '</div>' :'');
        
        $items_query = db_query($mysql_query);
        $count_items = db_num_rows($items_query);
        
        //output export
        if(isset($options['is_export']) or isset($options['is_email']))
        {
            $html = (strlen($cfg->get('default_text')) ? $cfg->get('default_text') : TEXT_TOTAL . ' (#)');
            $html = (strstr($html,'#') ? str_replace('#',$count_items,$html) : $html . $count_items);
            
            return $html;
        }
                        
        //displya number of records
        if($cfg->get('display_as')=='number' or isset($options['is_listing']))
        {                        
            $html = (strlen($cfg->get('default_text')) ? $cfg->get('default_text') : TEXT_TOTAL . ' (#)');
            $html = (strstr($html,'#') ? str_replace('#',$count_items,$html) : $html . $count_items);
            
            if(isset($options['is_listing'])) $options['path'] = $options['path'] .'-' . $options['item']['id'];
            
            return $debug_query . link_to_modalbox($html, url_for('items/items_by_query','path=' . filter_var($options['path'],FILTER_SANITIZE_STRING) . '&fields_id=' . filter_var($options['field']['id'],FILTER_SANITIZE_STRING)));            
        }
                     
        //display list of records
        
        if($count_items==0) return '';
        
        $html = $this->get_items_list($items_query,$cfg);
                
        return $debug_query . $html;
    }
    
    function get_items_list($items_query,$cfg)
    {
        $html = '<ul class="list">';
        while($item = db_fetch_array($items_query))
        {
            if(strlen($heading_template = $cfg->get('heading_template')))
            {
                $fieldtype_text_pattern = new fieldtype_text_pattern();
                $item_name = $fieldtype_text_pattern->output_singe_text($heading_template, $cfg->get('entity_id'), filter_var_array($item));
            }
            else
            {
                $item_name = items::get_heading_field($cfg->get('entity_id'), filter_var($item['id'],FILTER_SANITIZE_STRING), filter_var_array($item));
            }
            
            $html .= '
                <li>' . $item_name . '</li>
                ';
        }
        $html .= '</ul>';
        
        return $html;
    }
    
    function build_query($options)
    {
        global $app_user, $app_entities_cache, $app_fields_cache;
        
        $cfg = new fields_types_cfg($options['field']['configuration']);
        
        //print_rr($options);
                
        $mysql_query_where = trim($cfg->get('where_query'));
        
        $parent_entity_item_id = filter_var($options['item']['parent_id'],FILTER_SANITIZE_STRING);
        
        //prepare parent values
        if($parent_entity_item_id>0 and $app_entities_cache[$field['entities_id']]['parent_id']>0)
        {
            $item_info_query = db_query("select * from app_entity_" . filter_var($app_entities_cache[$field['entities_id']]['parent_id'],FILTER_SANITIZE_STRING) . " where id='" . db_input($parent_entity_item_id)."'");
            if($item_info = db_fetch_array($item_info_query))
            {
                foreach(filter_var_array($item_info) as $k=>$v)
                {
                    $k = str_replace('field_','',$k);
                    $mysql_query_where = str_replace('[' . $k . ']',$v,$mysql_query_where);
                }
                
                //check next parent
                $parent_entity_id = $app_entities_cache[$field['entities_id']]['parent_id'];
                
                if($app_entities_cache[$parent_entity_id]['parent_id']>0 and $item_info['parent_item_id']>0)
                {
                    $item_info_query = db_query("select * from app_entity_" . filter_var($app_entities_cache[$parent_entity_id]['parent_id'],FILTER_SANITIZE_STRING) . " where id='" . db_input(filter_var($item_info['parent_item_id'],FILTER_SANITIZE_STRING)))."'";
                    if($item_info = db_fetch_array($item_info_query))
                    {
                        foreach($item_info as $k=>$v)
                        {
                            $k = str_replace('field_','',$k);
                            $mysql_query_where = str_replace('[' . $k . ']',$v,$mysql_query_where);
                        }
                    }
                }
            }
        }
        
        $mysql_query_where = str_replace('[current_user_id]',filter_var($app_user['id'],FILTER_SANITIZE_STRING),$mysql_query_where);
        $mysql_query_where = str_replace('[TODAY]',get_date_timestamp(date('Y-m-d')),$mysql_query_where);
        
        //replace current item value
        foreach(filter_var_array($options['item']) as $field_key=>$field_value)
        {
            $field_key = str_replace('field_','',$field_key);
            $mysql_query_where = str_replace('[' . $field_key . ']',$field_value,$mysql_query_where);
        }
        
        //prepare entity fields
        foreach(filter_var_array($app_fields_cache[$cfg->get('entity_id')]) as $fields_id=>$field)
        {
            $mysql_query_where = str_replace('[' . $fields_id . ']','e.field_' . $fields_id,$mysql_query_where);
        }
        
        $sql = "select e.* " . fieldtype_formula::prepare_query_select(filter_var($cfg->get('entity_id'),FILTER_SANITIZE_STRING), '') . " from app_entity_" . filter_var($cfg->get('entity_id'),FILTER_SANITIZE_STRING) . " e " . (strlen($mysql_query_where) ? " where " . $mysql_query_where : '');
        
        return $sql;
    }
    
    
    
}