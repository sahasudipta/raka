<?php

if(isset($_POST['entities_id']) and isset($_POST['search_keywords']))
{
  
  $items_search = new items_search(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING));
  $items_search->set_search_keywords(filter_var($_POST['search_keywords'],FILTER_SANITIZE_STRING));
  
  if(isset($_GET['path']))
  {
    $items_search->set_path(filter_var($_GET['path'],FILTER_SANITIZE_STRING));  
  }
  
  $choices = $items_search->get_choices();  
  
  if(count($choices)==1)
  {
    $path_info = items::get_path_info(filter_var($_POST['entities_id'],FILTER_SANITIZE_STRING),key(filter_var_array($choices)));
    
    $html =  '
      <div class="alert alert-info"><a href="' . url_for('items/info','path=' . filter_var($path_info['full_path'],FILTER_SANITIZE_STRING)) . '" target="_blank">' . current(filter_var_array($choices)). '</a></div>
      <p>' . submit_tag(TEXT_BUTTON_LINK) . '</p>' . input_hidden_tag('items[]',key(filter_var_array($choices)));
  }
  elseif(count($choices)>1)
  {
    $attributes = array('class'=>'form-control chosen-select required',
                          'multiple'=>'multiple',
                          'data-placeholder'=>TEXT_SELECT_SOME_VALUES);
    $html = select_tag('items[]',filter_var_array($choices),'',$attributes) . 
            '<br><br><p>' . submit_tag(TEXT_BUTTON_LINK) . '</p>';
  }
  else    
  {
    $html = '<div class="alert alert-warning">' . TEXT_NO_RECORDS_FOUND . '</div>';
  }

  $html = '
  <div class="form-group">  	
    <div class="col-md-12">	  	        
      ' . $html . '
    </div>			
  </div>';
  
  echo $html;
}

exit();