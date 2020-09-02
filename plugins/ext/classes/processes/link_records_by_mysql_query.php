<?php

class link_records_by_mysql_query
{
    public $current_entity_id, $action_entity_id, $where_query, $current_item_id;
    
    function __construct($current_entity_id,$current_item_id, $action_entity_id, $where_query)
    {
        $this->current_entity_id = $current_entity_id;
        $this->current_item_id = $current_item_id;
        $this->action_entity_id = $action_entity_id;                        
        $this->where_query = $where_query;        
    }
    
    function process($sql_data, $choices_values)
    {
        global $app_user;
        
        //cacnel if empty query
        if(!strlen($this->where_query)) return false;
        
        //get current item info and prepare sql query
        $item_info_query = db_query("select * from app_entity_" . filter_var($this->current_entity_id,FILTER_SANITIZE_STRING) . " where id='" . db_input(filter_var($this->current_item_id,FILTER_SANITIZE_STRING)) . "'");
        if($item_info = db_fetch_array($item_info_query))
        {            
            if(preg_match_all('/\[(\w+)\]/',$this->where_query,$matches))
            {
                foreach($matches[1] as $matches_key=>$fields_id)
                {                                         
                    if(isset($item_info['field_' . $fields_id]))
                    {
                        $this->where_query = str_replace('[' . filter_var($fields_id,FILTER_SANITIZE_STRING) . ']',$item_info['field_' . filter_var($fields_id,FILTER_SANITIZE_STRING)],$this->where_query);
                    }
                    
                    if(isset($item_info[$fields_id]))
                    {
                        $this->where_query = str_replace('[' . filter_var($fields_id,FILTER_SANITIZE_STRING) . ']',$item_info[filter_var($fields_id,FILTER_SANITIZE_STRING)],$this->where_query);
                    }
                }
            }
        }
        
        $this->where_query = str_replace('[TODAY]',get_date_timestamp(date('Y-m-d')),$this->where_query);
        $this->where_query = str_replace('[current_user_id]',filter_var($app_user['id'],FILTER_SANITIZE_STRING),$this->where_query);
        
        //echo $this->where_query;
        //exit();
        
        $items_query = db_query("select id from app_entity_".db_input(filter_var($this->action_entity_id,FILTER_SANITIZE_STRING))." where " . filter_var($this->where_query,FILTER_SANITIZE_STRING));
        while($items = db_fetch_array($items_query))
        {
            $table_info = related_records::get_related_items_table_name($this->current_entity_id,$this->action_entity_id);
            
            $sql_data_related = array(
                'entity_' . $this->current_entity_id. '_items_id' => $this->current_item_id,
                'entity_' . $this->action_entity_id . $table_info['sufix'] . '_items_id' => $items['id']);
            
            //check if related item exist
            $check_query = db_query("select id from " . $table_info['table_name'] . " where entity_" . $this->current_entity_id. "_items_id=" . filter_var($this->current_item_id,FILTER_SANITIZE_STRING) . " and entity_" . $this->action_entity_id . $table_info['sufix'] . "_items_id=" . filter_var($items['id'],FILTER_SANITIZE_STRING));
            if(!$check = db_fetch_array($check_query))
            {
                //create related item
                db_perform($table_info['table_name'],$sql_data_related);
                        
                //check if there are fields to update related records
                if(count($sql_data))
                {
                    //update record
                    db_perform('app_entity_' . $this->action_entity_id,$sql_data,'update',"id=" . $items['id']);
    
                    //insert choices values for fields with multiple values
                    if(count($choices_values->choices_values_list))
                    {                    
                        $choices_values->process($items['id']);
                        
                        //atuoset fieldtype autostatus
                        fieldtype_autostatus::set($this->action_entity_id, $items['id']);                    
                    }
                }
            }
        }
    }
}