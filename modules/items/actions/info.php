<?php

//check if item it not empty
if($current_item_id==0 and !strlen($app_module_action))
{
	redirect_to('dashboard/page_not_found');
}

//keep current listing page
if(isset($_GET['gotopage']))
{
	$listing_page_keeper[key($_GET['gotopage'])] = current($_GET['gotopage']);
}

$entity_info = db_find('app_entities',$current_entity_id);
$entity_cfg = new entities_cfg($current_entity_id);
$item_info = db_find('app_entity_' . $current_entity_id,$current_item_id);

if($app_redirect_to=='subentity' and $entity_cfg->get('redirect_after_click_heading','subentity')=='subentity')
{  	
  $entity_query = db_query("select id from app_entities where parent_id='" . db_input($current_entity_id) . "' order by sort_order, name");    
  while($entity = db_fetch_array($entity_query))
  {
  	if(isset($app_users_access[$entity['id']]) or $app_user['group_id']==0)
  	{	
    	redirect_to('items/items','path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING) . '/' . filter_var($entity['id'],FILTER_SANITIZE_STRING));
  	}
  }
}

//reset users notifications
users_notifications::reset($current_entity_id,$current_item_id);

$app_title = app_set_title($app_breadcrumb[count($app_breadcrumb)-1]['title']); 

switch($app_module_action)
{
    
  case 'preview_user_photo':
  		$file = attachments::parse_filename(base64_decode(filter_var($_GET['file'],FILTER_SANITIZE_STRING)));
  		
  		if(is_file(DIR_WS_USERS . $file['file_sha1']))
  		{
  			$size = getimagesize(DIR_WS_USERS . $file['file_sha1']);
  			echo '<img width="' . filter_var($size[0],FILTER_SANITIZE_STRING) . '" height="' . filter_var($size[1],FILTER_SANITIZE_STRING) . '" src="' . DIR_WS_USERS . filter_var($file['file_sha1'],FILTER_SANITIZE_STRING) . '">';
  		}
  		exit();
  	break;
  case 'download_user_photo':
  	$file = attachments::parse_filename(base64_decode(filter_var($_GET['file'],FILTER_SANITIZE_STRING)));
    		  	
  	if(is_file(DIR_WS_USERS . $file['file_sha1']))
  	{  		
	  	header('Content-Description: File Transfer');
	  	header('Content-Type: application/octet-stream');
	  	header('Content-Disposition: attachment; filename='.filter_var($file['name'],FILTER_SANITIZE_STRING));
	  	header('Content-Transfer-Encoding: binary');
	  	header('Expires: 0');
	  	header('Cache-Control: must-revalidate');
	  	header('Pragma: public');
	  	
	  	flush();
	  		  	
	  	readfile(DIR_WS_USERS . filter_var($file['file_sha1'],FILTER_SANITIZE_STRING));
  	}
  	
  	exit();
  	break;
  case 'preview_attachment_image':
      $file = attachments::parse_filename(base64_decode($_GET['file']));
                                                                                                                                      
      if(is_file($file['file_path']))
      {
        $size = getimagesize($file['file_path']);
        echo '<img width="' . filter_var($size[0],FILTER_SANITIZE_STRING) . '" height="' . filter_var($size[1],FILTER_SANITIZE_STRING) . '"  src="' . url_for('items/info&path=' . filter_var($_GET['path'],FILTER_SANITIZE_STRING)  ,'&action=download_attachment&preview=1&file=' . urlencode(filter_var($_GET['file'],FILTER_SANITIZE_STRING))) . '">';
      }
      
      exit();
    break;
  case 'download_attachment':
      $file = attachments::parse_filename(base64_decode(filter_var($_GET['file'],FILTER_SANITIZE_STRING)));
      
      //check if using file storage for feild
      if(class_exists('file_storage') and isset($_GET['field']))
      {      	
      	file_storage::download_file(_get::int('field'), base64_decode(filter_var($_GET['file'],FILTER_SANITIZE_STRING)));      	
      }
                       
      if(is_file($file['file_path']))
      {
        if($file['is_image'] and isset($_GET['preview']))
        {                          
          $size = getimagesize(filter_var($file['file_path'],FILTER_SANITIZE_STRING));                    
          header("Content-type: " . $size['mime']);
          header('Content-Disposition: filename="' . filter_var($file['name'],FILTER_SANITIZE_STRING) . '"');
          
          flush();
          
          readfile(filter_var($file['file_path'],FILTER_SANITIZE_STRING));
        }
        elseif($file['is_pdf'] and isset($_GET['preview']))
        {                                                        
          header("Content-type: application/pdf");
          header('Content-Disposition: filename="' . filter_var($file['name'],FILTER_SANITIZE_STRING) . '"');
          
          flush();
          
          readfile(filter_var($file['file_path'],FILTER_SANITIZE_STRING));
        }
        else
        {                     
          header('Content-Description: File Transfer');
          header('Content-Type: application/octet-stream');
          header('Content-Disposition: attachment; filename='.filter_var($file['name'],FILTER_SANITIZE_STRING));
          header('Content-Transfer-Encoding: binary');
          header('Expires: 0');
          header('Cache-Control: must-revalidate');
          header('Pragma: public');
          header('Content-Length: ' . filesize($file['file_path']));
          
          flush();
                
          readfile(filter_var($file['file_path'],FILTER_SANITIZE_STRING));          
        }
      }
      else
      {
        echo TEXT_FILE_NOT_FOUD;
      }
        
      exit();
    break;
    
  case 'download_all_attachments':
      $item_info = db_find('app_entity_' . $current_entity_id, $current_item_id);
      
      //check if attachments exist
      if(strlen($attachments = $item_info['field_' . $_GET['id']])>0)
      {
      	//check if using file storage for feild
      	if(class_exists('file_storage'))
      	{
      		file_storage::download_files(_get::int('id'), $attachments);
      	}
      	
        $zip = new ZipArchive();
        $zip_filename = "attachments-{$current_item_id}.zip";
        $zip_filepath = DIR_FS_UPLOADS . $zip_filename;                
        
        //open zip archive        
        $zip->open($zip_filepath, ZipArchive::CREATE);
                        
        //add files to archive                
        foreach(explode(',',$attachments) as $filename)
        {
          $file = attachments::parse_filename($filename);                                                                    
          $zip->addFile($file['file_path'],"/" . $file['name']);                                      
        }
        
        $zip->close();
        
        //check if zip archive created
        if (!is_file($zip_filepath)) 
        {
            exit("Error: cannot create zip archive in " . $zip_filepath );
        }
        
        //download file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$zip_filename);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zip_filepath));
        
        flush();
              
        readfile($zip_filepath);   
        
        //delete temp zip archive file
        @unlink($zip_filepath);                      
      }
            
      exit();
    break;
  
}  