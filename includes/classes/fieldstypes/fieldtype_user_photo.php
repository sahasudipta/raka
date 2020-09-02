<?php

class fieldtype_user_photo
{
  public $options;
  
  function __construct()
  {
    $this->options = array('name' => TEXT_FIELDTYPE_USER_PHOTO_TITLE,'title' => TEXT_FIELDTYPE_USER_PHOTO_TITLE);
  }
  
  function render($field,$obj,$params = array())
  {
    $filename = $obj['field_' . $field['id']];
    $html = '';
    if(strlen($filename)>0)
    {
      $file = attachments::parse_filename($filename);
      $html = '
        			
        <div style="padding: 5px;">'  . image_tag(DIR_WS_USERS . $file['file_sha1'],array('width'=>50)) . '</div>
        <span class="help-block">' . $file['name'] . '<label class="checkbox">' . input_checkbox_tag('delete_files[' . $field['id'] . ']',1) . ' ' . TEXT_DELETE . '</label></span>
        ' . input_hidden_tag('files[' . $field['id'] . ']',$filename); 
        
    }
        
   return input_file_tag('fields[' . $field['id'] . ']') . $html;   
   
  }
  
  function process($options)
  {    
    global $alerts;
          
    $field_id = filter_var($options['field']['id'],FILTER_SANITIZE_STRING);  
    
    if(isset($_POST['delete_files'][$field_id]))
    {
      $file = attachments::parse_filename(filter_var($_POST['files'][$field_id],FILTER_SANITIZE_STRING));
      
      if(is_file(DIR_FS_USERS . $file['file_sha1']))
      {
        unlink(DIR_FS_USERS . filter_var($file['file_sha1'],FILTER_SANITIZE_STRING));
      }
            
      return '';
    }
    
    
    if(strlen($_FILES['fields']['name'][$field_id])>0)
    { 
      if(is_image($_FILES['fields']['tmp_name'][$field_id]))
      {      
        $file = attachments::prepare_filename(filter_var($_FILES['fields']['name'][$field_id],FILTER_SANITIZE_STRING));
                      
        if(move_uploaded_file(filter_var($_FILES['fields']['tmp_name'][$field_id],FILTER_SANITIZE_STRING), DIR_FS_USERS  . filter_var($file['file'],FILTER_SANITIZE_STRING)))
        {         
          image_resize(DIR_FS_USERS  . $file['file'],DIR_FS_USERS  . $file['file']);
               
          return $file['name'];
        }
        else
        {
          return '';
        }
      }
      else
      {
        return '';
      }                        
    }
    elseif(isset($_POST['files'][$field_id]))
    {
        return str_replace(['..','/','\/'],'',$_POST['files'][$field_id]);
    }
    else
    {
      return '';
    }    
  }
  
  function output($options)
  {
    if(strlen($options['value'])>0)
    {  
      $file = attachments::parse_filename($options['value']);
      
      $filename = $file['file'];
      
      $filepath = (is_file(DIR_WS_USERS . $file['file_sha1']) ? DIR_WS_USERS . $file['file_sha1'] : 'images/no_photo.png');
      
      if(isset($options['is_print']))
      {
          return '<img width=120 height=120 src=' . $filepath . ' class="user-profile-photo">';
      }
      elseif(isset($options['is_export']))
      {
        return $file['name'];    
      }
      elseif(isset($options['is_listing']))
      {
      	return  image_tag(DIR_WS_USERS . $file['file_sha1'],array('width'=>50));
      }
      else
      {        
        return  '
        		<div class="attachments-gallery">
        			<ul>
        				<li>
        					<div class="gallery-image"><a class="fancybox" href="' . url_for('items/info&path=' . $options['path']  ,'&action=preview_user_photo&file=' . urlencode(base64_encode($filename))) . '">' . image_tag(DIR_WS_USERS . $file['file_sha1']) . '</a></div>
        					<div class="gallery-download-link">' . link_to('<i class="fa fa-download"></i> ' . TEXT_DOWNLOAD,url_for('items/info&path=' . $options['path'] ,'&action=download_user_photo&file=' . urlencode(base64_encode($filename)))). '</div>
        				</li>
        			</ul>
        		</div>
        		<script type="text/javascript">
            	$(document).ready(function() {
            		$(".fancybox").fancybox({type: "ajax"});
            	});
            </script>';
      }
    }
    else
    {
      return '<img  src="images/no_photo.png" class="user-profile-photo">';
    }
  }
}