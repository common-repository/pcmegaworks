<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) ){
  die;
}
//==============================================================================
//================ USE CAUTION WHEN MODIFYING THIS =============================
//==============================================================================
//get this from the stored session            

@define('PCMW_SHOWFORMINDICATORS',TRUE);
@define('PCMW_YEAR',date('Y'));

//we'll define the user roles here
@define('PCMW_SUSPENDED',1);
@define('PCMW_BASICUSER',10);  //read only
@define('PCMW_HANDLER',15);
@define('PCMW_PREMIUMUSER',20);
@define('PCMW_MODERATOR',30);  //read/write
@define('PCMW_ADMINISTRATOR',40);
@define('PCMW_SUPERUSERS',50);//superuser
@define('PCMW_DEVUSERS',60);//devuser   

//define user group permissions
@define('PCMW_USERSUSPENDED',0);
@define('PCMW_USERREAD',10);
@define('PCMW_USERREADWRITE',20);
@define('PCMW_USERADMIN',30);

//define our datelength values
define('PCMW_DATE_LENGTH',10);
define('PCMW_DATE_NULL','');
define('PCMW_DATETIME_LENGTH',12);

//chat constants
@define('PCMW_NEW',10);
@define('PCMW_TAKEN',20);
@define('PCMW_UNREAD',30);
@define('PCMW_OFFLINE',40);
@define('PCMW_CLOSED',50);

define('PCMW_SUPPORT','support@progressivecoding.net');
define('PCMW_HOSTADDRESS','www.progressivecoding.net/PCPluginStub.php');
define('PCMW_HELPDESKURL','tm.progressivecoding.net/TaskServerAPIStub.php');   
if(!defined('PCMW_THEMENAME'))
    define('PCMW_THEMENAME','pcmegatheme');
?>