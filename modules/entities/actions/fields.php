<?php

switch($app_module_action)
{
  case 'set_heading_field_id':
       //reset heading
       db_query("update app_fields set is_heading=0 where entities_id ='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "'");
       
       //set new heading
       db_query("update app_fields set is_heading=1 where id='" . filter_var($_POST['heading_field_id'],FILTER_SANITIZE_STRING) . "' and entities_id ='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "'");
       
       exit(); 
    break;
   case 'set_heading_field_width':
    	entities::set_cfg('heading_width_based_content',filter_var($_POST['heading_width_based_content'],FILTER_SANITIZE_STRING),filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
    	exit();
    	break;
  case 'set_number_fixed_field_in_listing':
      entities::set_cfg('number_fixed_field_in_listing',filter_var($_POST['number_fields'],FILTER_SANITIZE_STRING),filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
      exit(); 
    break;
  case 'set_change_col_width_in_listing':
    	entities::set_cfg('change_col_width_in_listing',filter_var($_POST['change_col_width_in_listing'],FILTER_SANITIZE_STRING),filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
    	exit();
    	break;
  case 'sort_fields':
        if(isset($_POST['fields_in_listing'])) 
        {
          $sort_order = 0;
          foreach(explode(',',filter_var($_POST['fields_in_listing'],FILTER_SANITIZE_STRING)) as $v)
          {
            $sql_data = array('listing_status'=>1,'listing_sort_order'=>$sort_order);
            db_perform('app_fields',$sql_data,'update',"id='" . db_input(str_replace('form_fields_','',$v)) . "'");
            $sort_order++;
          }
        }
        
        if(isset($_POST['fields_excluded_from_listing'])) 
        {          
          foreach(explode(',',filter_var($_POST['fields_excluded_from_listing'],FILTER_SANITIZE_STRING)) as $v)
          {
            $sql_data = array('listing_status'=>0,'listing_sort_order'=>0);
            db_perform('app_fields',$sql_data,'update',"id='" . db_input(str_replace('form_fields_','',$v)) . "'");            
          }
        }
      exit();
    break;
  case 'save_internal':
	  	$sql_data = array(
	  			'name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),	  			
	  			'short_name'=>filter_var($_POST['short_name'],FILTER_SANITIZE_STRING),	  			
	  			'is_heading'=>(isset($_POST['is_heading']) ? filter_var($_POST['is_heading'],FILTER_SANITIZE_STRING):0),
	  			'sort_order'=>filter_var($_POST['sort_order'],FILTER_SANITIZE_STRING),
	  			'configuration'=> (isset($_POST['fields_configuration']) ? fields_types::prepare_configuration(filter_var($_POST['fields_configuration'],FILTER_SANITIZE_STRING)):''),
	  			);
	  		  	
	  	//reset heading fields, only one field can be heading
	  	if(isset($_POST['is_heading']))
	  	{
	  		db_query("update app_fields set is_heading=0 where entities_id ='" . db_input(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING)) . "'");
	  	}
	  	
	  	if(isset($_GET['id']))
	  	{	  	
	  		db_perform('app_fields',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");	  
	  	}
	  	
	  	redirect_to('entities/fields','entities_id=' . filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING));
  	break;
  case 'save':
  	
  	  $fields_configuration = filter_var_array($_POST['fields_configuration']);
  	  
  	  //prepare upload
  	  if(isset($_FILES['fields_configuration']))
  	  {
  	  	$upload_configuration = array();
  	  	
  	  	foreach(filter_var_array($_FILES['fields_configuration']['name']) as $k=>$v)
  	  	{  	
  	  		$upload_folder = '';
  	  		
  	  		//prepare upload folder
  	  		if(strstr($k,'icon_'))
  	  		{
  	  		  $upload_folder = 'icons/';	  		
  	  		}
  	  		
  	  		//check if delete file
  	  		if(isset($_POST['delete_file'][$k]))
  	  		{
  	  			unlink(DIR_WS_UPLOADS  . $upload_folder . filter_var($_POST['delete_file'][$k],FILTER_SANITIZE_STRING));
  	  			$upload_configuration[$k] = '';
  	  		}
  	  			
  	  		//upload file
  	  		if(strlen($v))
  	  		{
	  	  		$filename = str_replace(' ','_',$v);
	  	  		if(move_uploaded_file(filter_var($_FILES['fields_configuration']['tmp_name'][$k],FILTER_SANITIZE_STRING), DIR_WS_UPLOADS  . $upload_folder . $filename))
	  	  		{
	  	  			$upload_configuration[$k] = $filename;
	  	  		}
  	  		}
  	  		
  	  	}
  	  	
  	  	if(count($upload_configuration))
  	  	{
  	  		$fields_configuration = array_merge($fields_configuration,$upload_configuration);  	  		
  	  	}
  	  }
  	    	  
      $sql_data = array('forms_tabs_id'=>filter_var($_POST['forms_tabs_id'],FILTER_SANITIZE_STRING),
                        'name'=>filter_var($_POST['name'],FILTER_SANITIZE_STRING),                        
                        'type'=>filter_var($_POST['type'],FILTER_SANITIZE_STRING),
                        'short_name'=>filter_var($_POST['short_name'],FILTER_SANITIZE_STRING),
                        'notes' => strip_tags(filter_var($_POST['notes'],FILTER_SANITIZE_STRING)),
                        'is_heading'=>(isset($_POST['is_heading']) ? filter_var($_POST['is_heading'],FILTER_SANITIZE_STRING):0),
                        'is_required'=>(isset($_POST['is_required']) ? filter_var($_POST['is_required'],FILTER_SANITIZE_STRING):0),
                        'required_message'=>filter_var($_POST['required_message'],FILTER_SANITIZE_STRING),
                        'tooltip'=>filter_var($_POST['tooltip'],FILTER_SANITIZE_STRING),
                        'tooltip_display_as'=>(isset($_POST['tooltip_display_as']) ? filter_var($_POST['tooltip_display_as'],FILTER_SANITIZE_STRING):''),
                        'tooltip_in_item_page'=>(isset($_POST['tooltip_in_item_page']) ? filter_var($_POST['tooltip_in_item_page'],FILTER_SANITIZE_STRING):''),
                        'tooltip_item_page'=>filter_var($_POST['tooltip_item_page'],FILTER_SANITIZE_STRING),
                        'configuration'=> (isset($_POST['fields_configuration']) ? fields_types::prepare_configuration($fields_configuration):''),        
                        'entities_id'=>filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING));
                        
      
      //reset heading fields, only one field can be heading                  
      if(isset($_POST['is_heading']))
      {
        db_query("update app_fields set is_heading=0 where entities_id ='" . db_input(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING)) . "'");
      }                   
      
      if(isset($_GET['id']))
      {        
        //check if field type changed and do action required when field type changed
        fields::check_if_type_changed(filter_var($_GET['id'],FILTER_SANITIZE_STRING),filter_var($_POST['type'],FILTER_SANITIZE_STRING));
        
        db_perform('app_fields',$sql_data,'update',"id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
        
        $fields_id = filter_var($_GET['id'],FILTER_SANITIZE_STRING);
      }
      else
      {     
        $sql_data['sort_order'] = (fields::get_last_sort_number(filter_var($_POST['forms_tabs_id'],FILTER_SANITIZE_STRING))+1);
                  
        db_perform('app_fields',$sql_data);
        $fields_id = db_insert_id();
        
        entities::prepare_field($_POST['entities_id'],filter_var($fields_id,FILTER_SANITIZE_STRING),filter_var($_POST['type'],FILTER_SANITIZE_STRING));                
      }
      
      //create app_related_items_#_# table
      related_records::prepare_entities_related_items_table(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING), filter_var($fields_id,FILTER_SANITIZE_STRING));
      
      //set field access
      if(isset($_POST['access']))
      {          
          foreach(filter_var_array($_POST['access']) as $access_groups_id=>$access)
          {              
              if(in_array($access,array('view','hide')))
              {
                  $sql_data = array('access_schema'=>$access);
                  
                  $acess_info_query = db_query("select access_schema from app_fields_access where entities_id='" . db_input(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING)) . "' and access_groups_id='" . db_input(filter_var($access_groups_id,FILTER_SANITIZE_STRING)) . "' and fields_id='" . db_input(filter_var($fields_id,FILTER_SANITIZE_STRING)) . "'");
                  if($acess_info = db_fetch_array($acess_info_query))
                  {
                      db_perform('app_fields_access',$sql_data,'update',"entities_id='" . db_input(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING)) . "' and access_groups_id='" . db_input(filter_var($access_groups_id,FILTER_SANITIZE_STRING)). "'  and fields_id='" . db_input(filter_var($fields_id,FILTER_SANITIZE_STRING)) . "'");
                  }
                  else
                  {
                      $sql_data['entities_id'] = filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING);
                      $sql_data['access_groups_id'] = filter_var($access_groups_id,FILTER_SANITIZE_STRING);
                      $sql_data['fields_id'] = filter_var($fields_id,FILTER_SANITIZE_STRING);
                      db_perform('app_fields_access',$sql_data);
                  }
              }
              else
              {
                  db_query("delete from app_fields_access where entities_id='" . db_input(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING)) . "' and access_groups_id='" . db_input(filter_var($access_groups_id,FILTER_SANITIZE_STRING)). "'  and fields_id='" . db_input(filter_var($fields_id,FILTER_SANITIZE_STRING)) . "'");
              }
          }                          
      }
      
      if(isset($_POST['redirect_to']))
      {
        switch(filter_var($_POST['redirect_to'],FILTER_SANITIZE_STRING))
        {
          case 'forms':
              redirect_to('entities/forms','entities_id=' . filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING));
            break;
        }
      }
      
      redirect_to('entities/fields','entities_id=' . filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING));      
    break;
  case 'delete':
      if(isset($_GET['id']))
      {      
        $msg = fields::check_before_delete(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
        
        if(strlen($msg)>0)
        {
          $alerts->add($msg,'error');
        }
        else
        {
          $name = fields::get_name_by_id(filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          
          db_delete_row('app_fields',filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          
          db_delete_row('app_fields_choices',filter_var($_GET['id'],FILTER_SANITIZE_STRING),'fields_id');
          
          db_delete_row('app_reports_filters',filter_var($_GET['id'],FILTER_SANITIZE_STRING),'fields_id');
                              
          choices_values::delete_by_field_id(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING),filter_var($_GET['id'],FILTER_SANITIZE_STRING));
                              
          entities::delete_field(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING),filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          
          db_query("delete from app_reports_filters_templates where fields_id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) ."'");
          
          db_query("delete from app_forms_fields_rules where fields_id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) ."'");
          
          //delete approved records
          db_query("delete from app_approved_items where entities_id='" . db_input(filter_var(_get::int('entities_id'),FILTER_SANITIZE_STRING)) . "' and fields_id='" . db_input(filter_var(_get::int('id'),FILTER_SANITIZE_STRING)) . "'");
          
          //access rules
          db_query("delete from app_access_rules where fields_id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) ."'");
          db_query("delete from app_access_rules_fields where fields_id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) ."'");
          
          mind_map::delete_by_fields_id(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING), filter_var($_GET['id'],FILTER_SANITIZE_STRING));
          
          db_delete_row('app_listing_highlight_rules',filter_var($_GET['id'],FILTER_SANITIZE_STRING),'fields_id');
          
          if(is_ext_installed())
          {
          	db_delete_row('app_ext_processes_actions_fields',filter_var($_GET['id'],FILTER_SANITIZE_STRING),'fields_id');
          }	
          
          $alerts->add(sprintf(TEXT_WARN_DELETE_SUCCESS,$name),'success');
        }
        
        if(isset($_POST['redirect_to']))
        {
          switch($_POST['redirect_to'])
          {
            case 'forms':
                redirect_to('entities/forms','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
              break;
          }
        }
        
        redirect_to('entities/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));  
      }
    break;    
  case 'get_entities_form_tabs':
      $choices = forms_tabs::get_choices($_POST['entities_id']);
      
      if(count($choices)==1)
      {
        $html = input_hidden_tag('copy_to_form_tabs_id',key(filter_var_array($choices)));
      }
      else
      {
        $html = '
         <div class="form-group">
          	<label class="col-md-4 control-label" for="type">' . TEXT_SELECT_FORM_TAB . '</label>
            <div class="col-md-8">	
          	  ' . select_tag('copy_to_form_tabs_id',filter_var_array($choices),'',array('class'=>'form-control')) . '        
            </div>			
          </div>        
        ';
      }
      
      echo $html;
      
      exit();
    break; 
  case 'mulitple_edit':
  	if(strlen($_POST['selected_fields']))
  	{
  		$fields_query = db_query("select * from app_fields where entities_id='" . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . "' and id in (" . filter_var($_POST['selected_fields'],FILTER_SANITIZE_STRING) . ")");
  		while($fields = db_fetch_array($fields_query))
  		{
  			if($_POST['is_required']=='yes')
  			{
  				db_query("update app_fields set is_required=1 where id='" . filter_var($fields['id'],FILTER_SANITIZE_STRING) . "'");
  			}
  			elseif($_POST['is_required']=='no')
  			{
  				db_query("update app_fields set is_required=0 where id='" . filter_var($fields['id'],FILTER_SANITIZE_STRING) . "'");
  			}
  		}
  	}
  	
  	redirect_to('entities/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
  	break;
  case 'copy_selected':  
    if(strlen($_POST['selected_fields'])>0 and $_POST['copy_to_entities_id']>0)
    {
            
      $fields_query = db_query("select * from app_fields where entities_id='" . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . "' and id in (" . filter_var($_POST['selected_fields'],FILTER_SANITIZE_STRING) . ")");
      while($fields = db_fetch_array($fields_query))
      {
        //prepare sql data
        $sql_data = $fields;
        unset($sql_data['id']);
        $sql_data['entities_id'] = $_POST['copy_to_entities_id'];
        $sql_data['forms_tabs_id'] = $_POST['copy_to_form_tabs_id'];        
        $sql_data['is_heading'] = 0;
         
        db_perform('app_fields',$sql_data);        
        $new_fields_id = db_insert_id();
        
        entities::prepare_field($_POST['copy_to_entities_id'],$new_fields_id, $fields['type']);
        
        //create app_related_items_#_# table
        related_records::prepare_entities_related_items_table(filter_var($_POST['copy_to_entities_id'],FILTER_SANITIZE_STRING), filter_var($new_fields_id,FILTER_SANITIZE_STRING));
                      
        $choices_parent_id_to_replace = array();
        
        //check fields choices
        $fields_choices_query = db_query("select * from app_fields_choices where fields_id='" . filter_var($fields['id'],FILTER_SANITIZE_STRING) . "'");
        while($fields_choices = db_fetch_array($fields_choices_query))
        {
          //prepare sql data
          $sql_data = $fields_choices;
          unset($sql_data['id']);
          $sql_data['fields_id'] = $new_fields_id;
          
          db_perform('app_fields_choices',$sql_data);
          $new_fields_choices_id = db_insert_id();
          
          $choices_parent_id_to_replace[$fields_choices['id']] = $new_fields_choices_id;
        }                
        
        foreach($choices_parent_id_to_replace as $from_id=>$to_id)
        {
          db_query("update app_fields_choices set parent_id='" . $to_id . "' where parent_id='" . $from_id . "' and fields_id='" . $new_fields_id . "'");
        }
      }
      
      $alerts->add(TEXT_FIELDS_COPY_SUCCESS,'success');      
    } 
    
    redirect_to('entities/fields','entities_id=' . filter_var($_POST['copy_to_entities_id'],FILTER_SANITIZE_STRING));
    
    break;
    
  case 'import':
  	
  	//rename file (issue with HTML.php:495 if file have UTF symbols)
  	$filepath  = DIR_WS_UPLOADS . 'import_fields.xml';
  	
  	if(move_uploaded_file($_FILES['filename']['tmp_name'], $filepath))
  	{
 			
  		$data = file_get_contents($filepath);
  		$xml = simplexml_load_string($data);
  		$json = json_encode($xml);
  		$fields = json_decode($json,TRUE);
  	
  		unlink($filepath);
  		
  		//print_rr($fields);
  		//exit();
  		
  		$entities_id = _get::int('entities_id');
  		$imported_fields = 0;
  		
  		$tab_query = db_query("select forms_tabs_id from app_fields where entities_id='" . db_input($entities_id). "' and type='fieldtype_id'");
  		$tab = db_fetch_array($tab_query);
  		$default_forms_tabs_id = $tab['forms_tabs_id'];
  		
  		if(isset($fields['Field']))
  		{  			  
  			$fields_list = [];
  			if(isset($fields['Field']['forms_tabs_id']))
  			{ 
  				$fields_list[] = $fields['Field'];
  			}
  			else
  			{
  				$fields_list = $fields['Field'];
  			}
  			  			  			
  			foreach($fields_list as $field)
  			{
  				//print_rr($field);
  				
  				$sql_data = ['entities_id'=>$entities_id];
  				
  				foreach($field as $k=>$v)
  				{
  					if(!is_array($v))
  					{
  						$sql_data[$k] = $v;
  					}
  				}
  				
  				//check if tab id exist for this entity
  				$tab_query = db_query("select id from app_forms_tabs where entities_id='" . db_input($entities_id). "'  and id='" . $sql_data['forms_tabs_id'] . "'");
  				if(!$tab = db_fetch_array($tab_query))
  				{
  					$sql_data['forms_tabs_id'] = $default_forms_tabs_id;
  				}
  				
  				//print_rr($sql_data);
  				//exit();  				  			
  				
  				db_perform('app_fields',$sql_data);
  				$fields_id = db_insert_id();
  				
  				$imported_fields++;
  				
  				entities::prepare_field($sql_data['entities_id'],$fields_id,$sql_data['type']);
  				  				
  				//create app_related_items_#_# table
  				related_records::prepare_entities_related_items_table($sql_data['entities_id'], $fields_id);
  				
  				//check choices
  				if(isset($field['Choices']))
  				{
  					$choices_list = [];
  					if(isset($field['Choices']['Choice']['name']))
  					{
  						$choices_list[] = $field['Choices']['Choice'];
  					}
  					else
  					{
  						$choices_list = $field['Choices']['Choice'];
  					}
  					
  					foreach($choices_list as $choice)
  					{
  						$sql_data = ['fields_id'=>$fields_id];
  						
  						foreach($choice as $k=>$v)
  						{
  							if(!is_array($v))
  							{
  								$sql_data[$k] = $v;
  							}
  						}
  						
  						db_perform('app_fields_choices',$sql_data);
  					}
  					
  				}
  			}  			  			
  		}
  		

  		$alerts->add(sprintf(TEXT_IMPORTED_FIELDS,$imported_fields),'success');
  		redirect_to('entities/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
  		
  	}
  	else
  	{
  		$alerts->add(TEXT_FILE_NOT_LOADED,'warning');
  		redirect_to('entities/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
  	}
  	break;
        
  case 'export':
  	if(strlen(filter_var($_POST['selected_fields'],FILTER_SANITIZE_STRING)))
  	{
  		$writer = new XMLWriter();  		
  		$writer->openMemory();
  		$writer->setIndent(true);
  		$writer->startDocument('1.0', 'UTF-8');
  		
  		$writer->startElement('Fields');
  		$fields_query = db_query("select * from app_fields where entities_id='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "' and id in (" . filter_var($_POST['selected_fields'],FILTER_SANITIZE_STRING) . ")");
  		while($fields = db_fetch_array($fields_query))
  		{
  			//export field data
  			$writer->startElement('Field');
  			
  			foreach($fields as $k=>$v)
  			{
  				if(in_array($k,['id','entities_id','is_heading'])) continue;
  				
  				$writer->writeElement($k, $v);  				
  			}
  			
  			//export field choices data
  			$choices_query = db_query("select * from app_fields_choices where fields_id='" . db_input(filter_var($fields['id'],FILTER_SANITIZE_STRING)) . "'");
  			
  			if(db_num_rows($choices_query))
  			{
  				$writer->startElement('Choices');
  				
  				while($choices = db_fetch_array($choices_query))
  				{
  					$writer->startElement('Choice');
  					
  					foreach($choices as $k=>$v)
  					{
  						if(in_array($k,['id','fields_id','filename','parent_id'])) continue;
  					
  						$writer->writeElement($k, $v);
  					}
  					
  					$writer->endElement();
  				}
  				
  				$writer->endElement();
  			}
  			  			
  			$writer->endElement();
  		}
  		
  		$writer->endElement();
  		  		  		  		
  		$filename = str_replace(array(" ",","),"_",trim(filter_var($_POST['filename'],FILTER_SANITIZE_STRING)));
  		
  		header('Content-Type: application/xml; charset=utf-8');
  		header('Content-Disposition: attachment;filename="' . filter_var($filename,FILTER_SANITIZE_STRING) . '.xml"');
  		header('Cache-Control: max-age=0');
  		  		
  		echo $writer->outputMemory();
  		  		  	
  		exit();
  	}
  	
  	redirect_to('entities/fields','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING));
  	break;
}


require(component_path('entities/check_entities_id'));  