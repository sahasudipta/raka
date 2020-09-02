<?php

if (!app_session_is_registered('export_templates_filter')) 
{
  $export_templates_filter = 0;
  app_session_register('export_templates_filter');    
} 

switch($app_module_action)
{
	case 'copy':
		$templates_id = _get::int('templates_id');
		$templates_query = db_query("select * from app_ext_export_templates where id='" . $templates_id . "'");
		if($templates = db_fetch_array($templates_query))
		{
			unset($templates['id']);
			$templates['name'] = $templates['name'] . ' (' . TEXT_EXT_NAME_COPY. ')';
			db_perform('app_ext_export_templates', $templates);
		}
		redirect_to('ext/templates/export_templates');
		break;
  case 'save_description':
      $sql_data = array('description'=>filter_var($_POST['export_templates_description'],FILTER_SANITIZE_STRING));
      
      db_perform('app_ext_export_templates',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
      
      redirect_to('ext/templates/export_templates');
    break;
  case 'set_export_templates_filter':
      $export_templates_filter = $_POST['export_templates_filter'];
      
      redirect_to('ext/templates/export_templates');
    break;
  case 'sort_templates':
        if(isset($_POST['templates'])) 
        {
          $sort_order = 0;
          foreach(explode(',',filter_var($_POST['templates'],FILTER_SANITIZE_STRING)) as $v)
          {
            $sql_data = array('sort_order'=>$sort_order);
            db_perform('app_ext_export_templates',$sql_data,'update',"id='" . db_input(str_replace('template_','',$v)) . "'");
            $sort_order++;
          }
        }
      exit();
    break;  
  case 'save':
            
      $sql_data = array('name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),
                      'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),
                      'type'=>filter_var($_POST['type'],FILTER_SANITIZE_STRING),                      
                      'button_title'=>filter_var($_POST['button_title'],FILTER_SANITIZE_STRING),
                      'button_position'=>(isset($_POST['button_position']) ? implode(',',filter_var_array($_POST['button_position'])) : ''),
                      'button_color'=>filter_var($_POST['button_color'],FILTER_SANITIZE_STRING),
                      'button_icon'=>filter_var($_POST['button_icon'],FILTER_SANITIZE_STRING),
                      'is_active' => (isset($_POST['is_active']) ? 1:0),
                      'sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),
                      'users_groups' => (isset($_POST['users_groups']) ? implode(',',filter_var_array($_POST['users_groups'])):''),                                            
                      'assigned_to' => (isset($_POST['assigned_to']) ? implode(',',filter_var_array($_POST['assigned_to'])):''),
                      'template_filename'=>filter_var($_POST['template_filename'],FILTER_SANITIZE_STRING),
                      'template_css'=>filter_var($_POST['template_css'],FILTER_SANITIZE_STRING),
                      'page_orientation'=>filter_var($_POST['page_orientation'],FILTER_SANITIZE_STRING), 
                      'split_into_pages'=>filter_var($_POST['split_into_pages'],FILTER_SANITIZE_STRING),                      
                      'template_header'=>filter_var($_POST['template_header'],FILTER_SANITIZE_STRING),
                      'template_footer'=>filter_var($_POST['template_footer'],FILTER_SANITIZE_STRING),
                      );
        
    if(isset($_GET['id']))
    {                     
        $export_templates = db_find('app_ext_export_templates',_GET('id'));
        if($export_templates['entities_id']!=_POST('entities_id'))
        {
            reports::delete_reports_by_type('export_templates'._GET('id'));
            
            export_templates_blocks::delele_blocks_by_template_id(_GET('id'));
        }
        
        db_perform('app_ext_export_templates',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        $template_id = _GET('id');
    }
    else
    {                     
      db_perform('app_ext_export_templates',$sql_data); 
      $template_id = db_insert_id();
    }
    
        
    //upload file
    if(strlen($_FILES['filename']['name'])>0 and in_array($_FILES['filename']['type'],['application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
    {                
        $filename = $template_id . '-' . filter_var($_FILES['filename']['name'],FILTER_SANITIZE_STRING);
        if(move_uploaded_file($_FILES['filename']['tmp_name'], DIR_WS_TEMPLATES . $filename))
        {
            db_query("update app_ext_export_templates set filename = '" . db_input($filename) . "' where id='"  . $template_id . "'");            
        }
    }
               
    redirect_to('ext/templates/export_templates');      
  break;
  case 'delete':
      if(isset($_GET['id']))
      {                    
        db_query("delete from app_ext_export_templates where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        
        reports::delete_reports_by_type('export_templates'.filter_var(_GET('id'),FILTER_SANITIZE_STRING));
        
        export_templates_blocks::delele_blocks_by_template_id(filter_var(_GET('id'),FILTER_SANITIZE_STRING));
                                    
        $alerts->add(TEXT_EXT_WARN_DELETE_TEMPLATE_SUCCESS,'success');
                     
        redirect_to('ext/templates/export_templates');  
      }
    break;
  
  case 'get_parent_fields':
  	$html = '';
  	
  	if(($entities_id = $app_entities_cache[filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING)]['parent_id'])>0)
  	{
  		$html = export_templates::get_available_fields_for_all_entities(filter_var($entities_id,FILTER_SANITIZE_STRING),filter_var($_POST['editor'],FILTER_SANITIZE_STRING));
  		
  		if(filter_var($_POST['editor'],FILTER_SANITIZE_STRING)=='template_header')
  		{
  			$html .= "
	  			<script>
	  				$('.template_header').click(function(){
					    html = $(this).html().trim();
					    CKEDITOR.instances.template_header.insertText(html);
					  })
	  			</script>
	  			";
  		}
  		
  		if(filter_var($_POST['editor'],FILTER_SANITIZE_STRING)=='template_footer')
  		{	
	  		$html .= "
	  			<script>	
					  $('.template_footer').click(function(){
					    html = $(this).html().trim();  				
					    CKEDITOR.instances.template_footer.insertText(html);
					  })
	  			</script>
	  			";
  		}
  		
  	}
  	
  	echo $html;
  	
  	exit();
  	break;
}