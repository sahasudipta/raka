<?php

require('plugins/ext/application_core.php');

define('PLUGIN_EXT_VERSION','2.7.1');
define('PLUGIN_EXT_REQUIRED_RUKOVODITEL_VERSION','2.7.1');

//check required Rukovoditel version
if(PROJECT_VERSION!=PLUGIN_EXT_REQUIRED_RUKOVODITEL_VERSION and !in_array($app_module_path,array('tools/check_version')) )
{
  $alerts->add(sprintf(TEXT_EXT_REQUIRED_RUKOVODITEL_VERSION,PLUGIN_EXT_REQUIRED_RUKOVODITEL_VERSION,PLUGIN_EXT_VERSION),'warning');
        
  redirect_to('tools/check_version');
} 
 
require('plugins/ext/classes/license.php'); 
license::check();

if (!app_session_is_registered('plugin_ext_current_version')) 
{
  $plugin_ext_current_version = '';
  app_session_register('plugin_ext_current_version');    
} 

if(CFG_DISABLE_CHECK_FOR_UPDATES==1)
{
	$plugin_ext_current_version = '';
}

//$app_chat = new app_chat();

