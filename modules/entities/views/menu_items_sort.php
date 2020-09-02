<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	<h4 class="modal-title"><?php echo TEXT_SORT ?></h4>
</div>

<?php echo form_tag('menu', url_for('entities/menu')) ?>
<div class="modal-body">
  

<div class="cfg_forms_fields">
<ul id="sort_items" class="sortable">
<?php
$menu_query = db_query("select * from app_entities_menu where id='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'");
if($menu = db_fetch_array($menu_query))
{
	$entities_query = db_query("select * from app_entities e where e.id in (" . filter_var($menu['entities_list'],FILTER_SANITIZE_STRING). ") order by field(e.id," . filter_var($menu['entities_list'],FILTER_SANITIZE_STRING) . ")");
	while($entities = db_fetch_array($entities_query))
	{
	  echo '
	    <li id="item_' . filter_var($entities['id'],FILTER_SANITIZE_STRING) .'"><div>' . filter_var($entities['name'],FILTER_SANITIZE_STRING) . '</div></li>
	  ';
	}
}

?>
</ul>
</div>

</div>

<?php echo ajax_modal_template_footer() ?>

</form>

<script>
  $(function() {         
    	$( "ul.sortable" ).sortable({
    		connectWith: "ul",
    		update: function(event,ui){  
          data = '';  
          $( "ul.sortable" ).each(function() {data = data +'&'+$(this).attr('id')+'='+$(this).sortable("toArray") });                            
          data = data.slice(1)                      
          $.ajax({type: "POST",url: '<?php echo url_for("entities/menu","action=sort_items&id=" . filter_var($_GET['id'],FILTER_SANITIZE_STRING))?>',data: data});
        }
    	});
      

  });  
</script> 