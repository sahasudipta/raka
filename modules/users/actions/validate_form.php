<?php

$msg = array();

if(CFG_ALLOW_REGISTRATION_WITH_THE_SAME_EMAIL==0 and isset($_POST['useremail']))
{
  $check_query = db_query("select count(*) as total from app_entity_1 where field_9='" . db_input(filter_var($_POST['useremail'],FILTER_SANITIZE_STRING)) . "' " . (isset($_GET['id']) ? " and id!='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'":''));
  $check = db_fetch_array($check_query);
  if($check['total']>0)
  {
    $msg[] = TEXT_ERROR_USEREMAL_EXIST;
  }
}

if(isset($_POST['username']))
{	
	$check_query = db_query("select count(*) as total from app_entity_1 where field_12='" . db_input(filter_var($_POST['username'],FILTER_SANITIZE_STRING)) . "' " . (isset($_GET['id']) ? " and id!='" . db_input(filter_var($_GET['id'],FILTER_SANITIZE_STRING)) . "'":''));
	$check = db_fetch_array($check_query);
	if($check['total']>0)
	{
	  $msg[] = TEXT_ERROR_USERNAME_EXIST;
	}
}

if(count($msg)==0)
{
  echo 'success';
}
else
{
  echo implode('<br>',$msg);
}

exit();