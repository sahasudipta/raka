<?php require(component_path('entities/navigation')) ?>


<h3 class="page-title"><?php echo  TEXT_NAV_FORM_CONFIG ?></h3>

<p><?php echo TEXT_FORM_CONFIG_INFO ?></p>

<?php echo button_tag(TEXT_BUTTON_ADD_FORM_TAB,url_for('entities/forms_tabs_form','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))) . '&nbsp;' ?>
<?php echo button_tag(TEXT_ADD_JAVASCRIPT,url_for('entities/forms_custom_js','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)),true,['class'=>'btn btn-default'])?>

<div class="forms_tabs" style="max-width: 960px;">
<ol id="forms_tabs_ol" class="sortable_tabs sortable">
  <?php 
  $count_tabs = db_count('app_forms_tabs',filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING),"entities_id");

  $tabs_query = db_fetch_all('app_forms_tabs',"entities_id='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "' order by  sort_order, name");
  while($tabs = db_fetch_array($tabs_query)):
  
  $tab_is_reserved = forms_tabs::is_reserved($tabs['id']);
  
  ?>
  <li id="forms_tabs_<?php echo htmlentities($tabs['id']) ?>" > <div>
  <div class="cfg_form_tab">
    
      <?php if($count_tabs>0): ?>
      <div class="cfg_form_tab_heading">
        <table width="100%">
          <tr>
            <td>
              <b><?php echo htmlentities($tabs['name']) ?></b>
              <?php 
                if($tab_is_reserved)
                { 
                  echo tooltip_text(TEXT_RESERVED_FORM_TAB);
                } 
              ?>                              
            </td>
            <td class="align-right">
              <?php 
                echo  button_icon_edit(url_for('entities/forms_tabs_form','id=' . filter_var($tabs['id'],FILTER_SANITIZE_STRING). '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING))); 
                
                if(!$tab_is_reserved)
                { 
                  echo ' ' . button_icon_delete(url_for('entities/forms_tabs_delete','id=' . filter_var($tabs['id'],FILTER_SANITIZE_STRING) . '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)));
                } 
              ?>
            </td>
          </tr>
        </table>
      </div>
      <?php endif ?>
      
      <div class="cfg_forms_fields">
<?php  
echo '
  <ul id="forms_tabs_' . filter_var($tabs['id'],FILTER_SANITIZE_STRING) . '" class="sortable">
';
$fields_query = db_query("select f.*, t.name as tab_name from app_fields f, app_forms_tabs t where f.type not in (" . fields_types::get_reserverd_types_list(). ") and  f.entities_id='" . db_input(filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING)) . "' and f.forms_tabs_id=t.id and f.forms_tabs_id='" . db_input(filter_var($tabs['id'],FILTER_SANITIZE_STRING)) . "' order by t.sort_order, t.name, f.sort_order, f.name");
while($v = db_fetch_array($fields_query))
{
  echo '
    <li id="form_fields_' . $v['id'] . '" class="' . $v['type'] . '">
      <div>
        <table width="100%">
          <tr>
            <td>' . fields_types::get_option($v['type'],'name',$v['name']) . '</td>
            <td class="align-right">' . (!in_array(filter_var($v['type'],FILTER_SANITIZE_STRING),fields_types::get_users_types()) ? button_icon_edit(url_for('entities/fields_form','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING). '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING). '&redirect_to=forms')) . ' ' . button_icon_delete(url_for('entities/fields_delete','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING) . '&entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING). '&redirect_to=forms')):'') . '</td>
          </tr>
        </table>
      </div>
    </li>';
}
echo '
  </ul>
';  
?>            
      </div>      
      <div ><?php echo button_tag(TEXT_BUTTON_ADD_NEW_FIELD,url_for('entities/fields_form','entities_id=' . filter_var($_GET['entities_id'],FILTER_SANITIZE_STRING) . '&forms_tabs_id=' . filter_var($tabs['id'],FILTER_SANITIZE_STRING) . '&redirect_to=forms'),true,array('class'=>'btn btn-primary')) ?></div>          
  </div>
   </div></li>       
  <?php endwhile ?>
  </ol>
</div>



<script>
  $(function() {         
    	$( "ul.sortable" ).sortable({
    		connectWith: "ul",
    		update: function(event,ui){  
          data = '';  
          $( "ul.sortable" ).each(function() {data = data +'&'+$(this).attr('id')+'='+$(this).sortable("toArray") });                            
          data = data.slice(1)                      
          $.ajax({type: "POST",url: '<?php echo url_for("entities/forms","action=sort_fields&entities_id=" . filter_var($_GET["entities_id"],FILTER_SANITIZE_STRING))?>',data: data});
        }
    	});
      
      $( "ol.sortable_tabs" ).sortable({
        handle: '.cfg_form_tab_heading',  		
    		update: function(event,ui){ 
        
          data = '';  
          $( "ol.sortable_tabs" ).each(function() {data = data +'&'+$(this).attr('id')+'='+$(this).sortable("toArray") });                            
          data = data.slice(1)                      
          $.ajax({type: "POST",url: '<?php echo url_for("entities/forms","action=sort_tabs")?>',data: data});
        }
    	});
  });  
</script> 




