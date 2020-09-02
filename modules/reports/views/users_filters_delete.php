
<?php echo ajax_modal_template_header(TEXT_DELETE_FILTERS) ?>

<?php echo form_tag('users_filters', url_for('reports/users_filters','action=delete&reports_id=' . filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING) ),array('class'=>'form-horizontal')) ?>

<?php echo input_hidden_tag('redirect_to',$app_redirect_to) ?>
<?php if(isset($_GET['path'])) echo input_hidden_tag('path',filter_var($_GET['path'],FILTER_SANITIZE_STRING)) ?>
<?php
  $users_filters = new users_filters(filter_var($_GET['reports_id'],FILTER_SANITIZE_STRING));
?>

<div class="modal-body">
  <div class="form-body">
  
     
  <div class="form-group">
  	<label class="col-md-3 control-label" for="name"><?php echo TEXT_NAME ?></label>
    <div class="col-md-9">	
  	  <?php echo select_checkboxes_tag('filters',$users_filters->get_choices(),'',array('class'=>'form-control required')) ?>
    </div>			
  </div> 
           
   </div>
</div> 
 
<?php echo ajax_modal_template_footer(TEXT_BUTTON_DELETE) ?>

</form> 

<script>
  $(function() { 
    $('#users_filters').validate();                                                                              
  });     
</script>  

    
 
