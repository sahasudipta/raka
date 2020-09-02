<?php require(component_path('entities/navigation')) ?>

<h3 class="page-title"><?php echo TEXT_NAV_FIELDS_ACCESS ?></h3>

<?php echo form_tag('cfg', url_for('entities/fields_access','action=set_access&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) ?>
<?php echo input_hidden_tag('ui_accordion_active',0) ?>
<?php
  $fields_list = array();
  $fields_query = db_query("select f.*, t.name as tab_name,if(f.type in ('fieldtype_id','fieldtype_date_added','fieldtype_created_by','fieldtype_date_updated'),-1,t.sort_order) as tab_sort_order from app_fields f, app_forms_tabs t where f.type not in ('fieldtype_action') and f.entities_id='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "' and f.forms_tabs_id=t.id order by tab_sort_order, t.name, f.sort_order, f.name");
  while($v = db_fetch_array($fields_query))
  {
    $fields_list[filter_var($v['id'],FILTER_SANITIZE_STRING)] = array(
    		'name' => fields_types::get_option(filter_var($v['type'],FILTER_SANITIZE_STRING),'name',filter_var($v['name'],FILTER_SANITIZE_STRING)),
    		'type' => filter_var($v['type'],FILTER_SANITIZE_STRING) 
    		);
  }
?>


<div id="accordion">  
  <h3><?php echo TEXT_ADMINISTRATOR ?></h3>
  <div>
    <?php echo TEXT_ADMINISTRATOR_FULL_ACCESS ?>
  </div>
<?php
  
  $access_choices_default = array('yes'=>TEXT_YES,'view'=>TEXT_VIEW_ONLY,'hide'=>TEXT_HIDE);
  $access_choices_internal = array('yes'=>TEXT_YES,'hide'=>TEXT_HIDE);
  
  $count = 0;
  $groups_query = db_fetch_all('app_access_groups','','sort_order, name');
  while($groups = db_fetch_array($groups_query))
  {     
    $entities_access_schema = users::get_entities_access_schema(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING),filter_var($groups['id'],FILTER_SANITIZE_STRING));
    
    if(!in_array('view',$entities_access_schema) and  !in_array('view_assigned',$entities_access_schema) and $_GET['entities_id']!=1) continue;
                  
    $count++;
  
    $html = '
      <div class="table-scrollable">
      <table class="table table-striped table-bordered table-hover">
        <tr>
          <th>' . TEXT_FIELDS . '</th>
          <th>' . TEXT_ACCESS . ': ' . select_tag('access_' . filter_var($groups['id'],FILTER_SANITIZE_STRING),array_merge(array(''=>''),$access_choices_default),'',array('class'=>'form-control input-medium ','onChange'=>'set_access_to_all_fields(this.value,' . filter_var($groups['id'],FILTER_SANITIZE_STRING) . ')')) . '</th>
        </tr>
      ';
      
    $access_schema = users::get_fields_access_schema(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING),filter_var($groups['id'],FILTER_SANITIZE_STRING));
    
      
    foreach(filter_var_array($fields_list) as $id=>$field)
    {
      $value = (isset($access_schema[$id]) ? filter_var($access_schema[$id],FILTER_SANITIZE_STRING) : 'yes');
      
      $access_choices = (in_array($field['type'],array('fieldtype_id','fieldtype_date_added','fieldtype_date_updated','fieldtype_created_by')) ? $access_choices_internal : $access_choices_default);
      
      $html .= '
        <tr>
          <td>' . filter_var($field['name'],FILTER_SANITIZE_STRING) . '</td>
          <td>' . select_tag('access[' . filter_var($groups['id'],FILTER_SANITIZE_STRING). '][' . filter_var($id,FILTER_SANITIZE_STRING) . ']',$access_choices, $value,array('class'=>'form-control input-medium access_group_' . filter_var($groups['id'],FILTER_SANITIZE_STRING))). '</td>
        </tr>
      ';
    }
    
    $html .= '</table></div>';
    
    echo '
      <h3>' . filter_var($groups['name'],FILTER_SANITIZE_STRING) . '</h3>
      <div>
        ' . $html . '
      </div>
    ';
  } 
?>

</div>
<br>
<?php if($count>0) echo submit_tag(TEXT_BUTTON_SAVE) ?>

</form>

<script>
  $(function() {
    $( "#accordion" ).accordion({heightStyle:'content', active: <?php echo (isset($_GET["ui_accordion_active"]) ? filter_var($_GET["ui_accordion_active"],FILTER_SANITIZE_STRING):"0") ?>,
        activate: function( event, ui ) {          
          active = $('#accordion').accordion('option', 'active');
          $('#ui_accordion_active').val(active)
        
        }
    });
  });
  </script>



