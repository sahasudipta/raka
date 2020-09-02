<h3 class="page-title"><?php echo TEXT_HEADING_USERS_ACCESS_GROUPS ?></h3>

<?php echo button_tag(TEXT_ADD_NEW_USER_GROUP,url_for('users_groups/form')) ?>
<?php echo ' ' . button_tag(TEXT_SORT_GROUPS,url_for('users_groups/sort')) ?>

<div class="table-scrollable">
<table class="table table-striped table-bordered table-hover">
<thead>
  <tr>
    <th><?php echo TEXT_ACTION ?></th>    
    <th><?php echo TEXT_ID ?></th>
    <th width="100%"><?php echo TEXT_NAME ?></th>
    <th><?php echo TEXT_IS_DEFAULT ?></th>
    <th><?php echo TEXT_IS_LDAP_DEFAULT ?></th>
    <th><?php echo TEXT_SORT_ORDER ?></th>    
  </tr>
</thead>
<tbody>
<?php if(db_count('app_access_groups')==0) echo '<tr><td colspan="5">' . TEXT_NO_RECORDS_FOUND. '</td></tr>'; ?>
<?php
  $groups_query = db_fetch_all('app_access_groups','','sort_order, name');
  while($v = db_fetch_array($groups_query)):
?>
  <tr>
    <td style="white-space: nowrap;"><?php echo button_icon_delete(url_for('users_groups/delete','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING))) . ' ' . button_icon_edit(url_for('users_groups/form','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING))) ?></td>    
    <td><?php echo htmlentities($v['id']) ?></td>
    <td><?php echo link_to(filter_var($v['name'],FILTER_SANITIZE_STRING), url_for('users_groups/pivot_access_table','id=' . filter_var($v['id'],FILTER_SANITIZE_STRING))) ?></td>
    <td><?php echo render_bool_value(filter_var($v['is_default'],FILTER_SANITIZE_STRING)) ?></td>
    <td><?php echo render_bool_value(filter_var($v['is_ldap_default'],FILTER_SANITIZE_STRING)) ?></td>
    <td><?php echo htmlentities($v['sort_order']) ?></td>
  </tr>
<?php endwhile?>  
</tbody>
</table>
</div>