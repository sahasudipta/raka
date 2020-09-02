<?php

switch($app_module_action)
{
    case 'save':
        $sql_data = [
        'templates_id' => filter_var($template_info['id'],FILTER_SANITIZE_STRING),
        'block_type' => 'entity',
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
        
        redirect_to('ext/templates_docx/entity_blocks','templates_id=' . $template_info['id'] . '&parent_block_id=' . $parent_block['id']);
        break;
    case 'delete':
        if(isset($_GET['id']))
        {
            export_templates_blocks::delele_block(_GET('id'));
            
            redirect_to('ext/templates_docx/entity_blocks','templates_id=' . $template_info['id'] . '&parent_block_id=' . $parent_block['id']);
        }
        break;
}