<?

/***************************************************************************
 *
 * Copyright (c) by Andreas Unterkircher, unki@netshadow.at
 * All rights reserved
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 ***************************************************************************/

define('MS_CHAINS', 1);
define('MS_PIPES', 2);
define('MS_SERVICE_LEVELS', 3);
define('MS_TARGETS', 4);
define('MS_MONITOR', 5);
define('MS_OPTIONS', 6);
define('MS_RULES', 7);
define('MS_FILTERS', 8);
define('MS_PORTS', 9);
define('MS_PROTOCOLS', 10);
define('MS_ABOUT', 11);
define('MS_CONFIG_SAVE', 12);
define('MS_CONFIG_RESTORE', 13);
define('MS_CONFIG_RESET', 14);
define('MS_UPDATE_L7', 15);
define('MS_USERS', 16);
define('MS_LOGOUT', 97);
define('MS_LOGIN', 98);
define('MS_OVERVIEW', 99);

include "shaper_config.php";
include "shaper_chains.php";
include "shaper_pipes.php";
include "shaper_options.php";
include "shaper_setup.php";
include "shaper_filters.php";
include "shaper_targets.php";
include "shaper_monitor.php";
include "shaper_service_levels.php";
include "shaper_ports.php";
include "shaper_protocols.php";
include "shaper_overview.php";
include "shaper_users.php";
include "shaper_about.php";

class MS {

   var $db;
   var $ms_chains;
   var $ms_pipes;
   var $ms_options;
   var $ms_setup;
   var $ms_filters;
   var $ms_targets;
   var $ms_ports;
   var $ms_protocols;
   var $ms_config;
   var $version;
   var $fromcmd;
   var $self;
   var $mode;
   var $screen;

   /* Class constructor */
   function MS($version, $fromcmd)
   {
      $this->version = $version;
      $this->fromcmd = $fromcmd;
      $this->self    = $_SERVER['PHP_SELF'];

      if(is_numeric($_GET['mode']))
	 $this->mode    = $_GET['mode'];
      if(is_numeric($_GET['screen']))
	 $this->screen  = $_GET['screen'];

      /* Read config, if not exists, send browser to MasterShaper Installer */
      $this->ms_config = new MSCONFIG($this);
      $this->ms_config->readCfg("config.dat") or Header("Location: setup/");
      $this->ms_config->readCfg("icons.dat");

      /* initalize database communication */
      $this->db = new MSDB($this);

      /* If software version not equal database stored version */
      if($this->db->getVersion() != $this->version)
	 Header("Location: setup/");

      /* If not all definitions are available */
      if(!defined('MYSQL_HOST') || !defined('MYSQL_DB') || !defined('MYSQL_USER') || !defined('MYSQL_PASS') ||
	 !defined('SHAPER_PATH') || !defined('SHAPER_WEB') || !defined('TC_BIN') || !defined('IPT_BIN') ||
	 !defined('TEMP_PATH') || !defined('SUDO_BIN')) {

	 Header("Location: setup/");

      }

      $this->ms_chains         = new MSCHAINS($this);
      $this->ms_pipes          = new MSPIPES($this);
      $this->ms_options        = new MSOPTIONS($this);
      $this->ms_filters        = new MSFILTERS($this);
      $this->ms_targets        = new MSTARGETS($this);
      $this->ms_setup          = new MSSETUP($this);
      $this->ms_monitor        = new MSMONITOR($this);
      $this->ms_service_levels = new MSSERVICELEVELS($this);
      $this->ms_ports          = new MSPORTS($this);
      $this->ms_protocols      = new MSPROTOCOLS($this);
      $this->ms_overview       = new MSOVERVIEW($this);
      $this->ms_about	       = new MSABOUT($this);
      $this->ms_users          = new MSUSERS($this);

      if($this->getOption("authentication") == "Y") 
         $this->startSession();

      /* if we are not called from command line */
      if(!$this->fromcmd)
	 $this->showHtml();

   } // MS()
	
   /* interface output */
   function showHtml()
   {
      if(!isset($this->mode))
         $this->mode = MS_OVERVIEW;

      /* If authentication is enabled, we have to login first! */
      if($this->getOption("authentication") == "Y") {
         
	 /* If user requests the "About"-Site, it can be serverd without login */
	 if(!$this->isSession() && $this->mode != MS_ABOUT) {

	    $this->mode = MS_LOGIN;

	 }
      }

      /* If we are saving the current config we do not show the header */
      if($this->mode != MS_CONFIG_SAVE)
	 $this->showMSHeader();

      switch($this->mode) {
	
	 case MS_CHAINS:
	    $this->ms_chains->showHtml();
	    break;
	 case MS_PIPES:
	    $this->ms_pipes->showHtml();	
	    break;
	 case MS_SERVICE_LEVELS:
	    $this->ms_service_levels->showHtml();
	    break;
	 case MS_TARGETS:
	    $this->ms_targets->showHtml();
	    break;
	 case MS_MONITOR:
	    $this->ms_monitor->showHtml();
	    break;
	 case MS_OPTIONS:
	    $this->ms_options->showHtml();
	    break;
	 case MS_RULES:
	    $this->ms_setup->enableConfig();
	    break;
	 case MS_FILTERS:
	    $this->ms_filters->showHtml();
	    break;
	 case MS_PORTS:
	    $this->ms_ports->showHtml();
	    break;
	 case MS_PROTOCOLS:
	    $this->ms_protocols->showHtml();
	    break;
	 case MS_ABOUT:
	    $this->ms_about->showHtml();
	    break;
	 case MS_CONFIG_SAVE:
	    $this->ms_options->saveConfig();
	    break;
	 case MS_CONFIG_RESTORE:
	    $this->ms_options->restoreConfig();
	    break;
	 case MS_CONFIG_RESET:
	    $this->ms_options->resetConfig();
	    break;
	 case MS_UPDATE_L7:
	    $this->ms_options->updateL7Protocols();
	    break;
	 case MS_USERS:
	    $this->ms_users->showHtml();
	    break;
	 case MS_LOGOUT:
	    $this->destroySession();
	    break;
	 case MS_LOGIN:
	    $this->showLogin();
	    break;
	 default:
	 case MS_OVERVIEW:
	    $this->ms_overview->showHtml();
	    break;
      }

      /* If we are saving the current config we do not show the footer */
      if($this->mode != MS_CONFIG_SAVE)
	 $this->showMSFooter();

   } // showHtml()

   function cleanup()
   {
      $this->db->db_disconnect();
   } // cleanup()

   /* show header of webpages (incl. menu) */
   function showMSHeader()
   {

      if(!is_dir(SHAPER_PATH ."/phplayersmenu"))
        die("Can't locate phplayersmenu in &lt;". SHAPER_PATH ."/phplayersmenu&gt;<br />Read MasterShaper Documentation about System Requirements!");
      
      if(!is_dir(SHAPER_PATH ."/jpgraph"))
        die("Can't locate jpgraph in &lt;". SHAPER_PATH ."/jpgraph&gt;<br />Read MasterShaper Documentation about System Requirements!");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
 <head>
  <!-- to block this stupid msnbot from fetching the whole mastershaper sites -->
  <meta name="msnbot" content="noindex,nofollow" />
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title>MasterShaper <? print $this->version; ?> - Traffic Shaping and QoS</title>
  <link rel="stylesheet" href="layersmenu.css" type="text/css" />
  <link rel="stylesheet" href="shaper_style.css" type="text/css" />
  <link rel="shortcut icon" href="favicon.ico" />
  <script type="text/javascript">
  <!--
  <? require_once SHAPER_PATH ."/phplayersmenu/libjs/layersmenu-browser_detection.js"; ?>
  // -->
  </script>
  <script type="text/javascript" src="<?php print SHAPER_WEB; ?>/phplayersmenu/libjs/layersmenu-library.js"></script>
  <script type="text/javascript" src="<?php print SHAPER_WEB; ?>/phplayersmenu/libjs/layersmenu.js"></script>
  <script type="text/javascript" src="<?php print SHAPER_WEB; ?>/shaper.js"></script>
  <script type="text/javascript" src="<?php print SHAPER_WEB; ?>/tipster.js"></script>
  <script type="text/javascript">
  <!--
     var staticTip = new TipObj('staticTip');
     with (staticTip) {

        template = '<table class="tipClass" style="width: %2%; border: 2px solid #FFFFFF;">' +
	           ' <tr> ' +
		   '  <td style="padding: 5px;">' +
		   '   <table class="noborder" style="width: 100%;">' +
		   '    <tr>' +
		   '     <td style="white-space: nowrap;">'+
		   '      %3%' +
		   '     </td>' +
		   '    </tr>' +
		   '   </table>' +
		   '  </td>' +
		   ' </tr>' +
		   '</table>';

        tipStick = 0;
     }
  -->
  </script>
<?

      require_once SHAPER_PATH ."/phplayersmenu/lib/PHPLIB.php";
      require_once SHAPER_PATH ."/phplayersmenu/lib/layersmenu-common.inc.php";
      require_once SHAPER_PATH ."/phplayersmenu/lib/layersmenu.inc.php";

      $mid = new LayersMenu(3, 9, 2, 1);      // Galaxy-like

      /* TO USE ABSOLUTE PATHS: */
      $mid->setDirroot(SHAPER_PATH ."/phplayersmenu/");
      $mid->setImgdir(SHAPER_PATH .'/images/');
      $mid->setImgwww(SHAPER_WEB .'/images/');
      $mid->setIcondir(SHAPER_PATH .'/icons/');
      $mid->setIconwww(SHAPER_WEB .'/icons/');
      $mid->setVerticalMenuTpl('layersmenu-vertical_menu.ihtml');
      $mid->setSubMenuTpl('layersmenu-sub_menu.ihtml');
      $mid->setForwardArrowImg('right-arrow-white.png');
      $mid->setMenuStructureFile('ms_menu.txt');
      $mid->setIconsize(16, 16);
      $mid->parseStructureForMenu('msmenu1');
      $mid->newVerticalMenu('msmenu1');
      $mid->printHeader();

?>
 </head>
<?

      /* If we are drawing the login dialog, set the focus on the user name field */
      if($this->mode != MS_LOGIN) {
?>
 <body>
<?
      }
      else {
?>
 <body onload="document.forms['login'].elements['user_name'].focus();">
<?
      }
?>
  <!-- tipster layer -->
  <div id="staticTipLayer" style="position: absolute; z-index: 1000; visibility: hidden; left: 0px; top: 0px; width: 10px">&nbsp;</div>

  <!-- main table -->
  <table style="width: 100%; height: 100%;">

   <!-- header cell -->
   <tr>
    <td style="width: 100%; height: 150px;">
     <table style="width: 100%;">
      <tr>
       <td style="background-image: url(images/ms_header_left.jpg); background-position: right;">
        &nbsp;
       </td>
       <td style="background-image: url(images/ms_header_right.jpg); width: 796px; height: 150px; vertical-align: top;">
        <table>
         <tr>
          <td style="height: 119px;" colspan="2" />
         </tr>
         <tr>
          <td style="width: 213px;" />
          <td>
	   <table style="width: 540px; text-align: center; padding: 5px;">
	    <tr>
	     <td>
	      <img src="<? print ICON_HOME; ?>" alt="button" />&nbsp;
	      <a href="<? print $this->self ."?mode=". MS_OVERVIEW; ?>" title="MasterShaper Ruleset Overview" onclick="if(this.blur) this.blur();">
               Overview
	      </a>
	     </td>
	     <td>
	      <img src="<? print ICON_MONITOR; ?>" alt="button" />&nbsp;
	      <a href="<? print $this->self ."?mode=". MS_MONITOR ."&amp;show=pipes"; ?>" title="Pipes Monitoring" onclick="if(this.blur) this.blur();">
               Monitoring
	      </a>
	     </td>
	     <td>
	      <img src="<? print ICON_SHAPER_START; ?>" alt="button" />&nbsp;
	      <a href="<? print $this->self ."?mode=". MS_RULES ."&amp;screen=2"; ?>" title="Load ruleset" onclick="if(this.blur) this.blur();">
	       Load Ruleset
	      </a>
	     </td>
	     <td>
	      <img src="<? print ICON_SHAPER_STOP; ?>" alt="button" />&nbsp;
	      <a href="<? print $this->self ."?mode=". MS_RULES ."&amp;screen=4"; ?>" title="Unload ruleset" onclick="if(this.blur) this.blur();">
	       Unload Ruleset
	      </a>
	     </td>
	     <td>
	      <img src="<? print ICON_HOME; ?>" alt="button" />&nbsp;
	      <a href="<? print $this->self ."?mode=". MS_ABOUT; ?>" onclick="if(this.blur) this.blur();">
	       About
	      </a>
	     </td>
	    </tr>
	   </table>
          </td>
         </tr>
        </table>
       </td>
      </tr>
     </table>
    </td>
   </tr>
   <!-- /header cell -->

   <tr>
    <td style="height: 20px;">
     &nbsp;
    </td>
   </tr>

   <tr>
    <td style="width: 100%; vertical-align: top;">
     <table style="width: 100%;">
      <tr>

       <td style="width: 20px;"> </td>

       <!-- left cell -->
       <td style="width: 200px; vertical-align: top;">
        <table style="width: 200px;">
         <tr>
          <td style="background-image: url(images/ms_menu_top.jpg); height: 65px;">
           &nbsp;
          </td>
         </tr>
         <tr>
          <td style="background-image: url(images/ms_menu_middle.jpg); background-repeat: repeat-y;">
           <table>
	    <tr>
	     <td colspan="2">
	      &nbsp;
	     </td>
	    </tr>
	    <tr>
	     <td style="width: 30px">
	      &nbsp;
	     </td>
	     <td>
<?

      $mid->printMenu('msmenu1');
      $mid->printFooter();

?>
             </td>
            </tr>
	    <tr>
	     <td colspan="2">
	      &nbsp;
	     </td>
	    </tr>
           </table>
          </td>
         </tr>
         <tr>
          <td style="background-image: url(images/ms_menu_bottom.jpg); height: 65px;">
           &nbsp;
          </td>
         </tr>
        </table>
	<? $this->startTable("<img src=\"". ICON_CHAINS ."\" alt=\"Setup\" />&nbsp;Setup"); ?>
	<table>
	 <tr>
	  <td colspan="2">
	   Qdisc:
	  </td>
	 </tr>
	 <tr>
	  <td style="width: 5px;"></td>
	  <td>
<?
     
      switch($this->getOption("classifier")) {
	 default:
	 case 'HTB':
	    print "&nbsp;<i>HTB</i>";
	    break;
	 case 'HFSC':
	    print "&nbsp;<i>HFSC</i>";
	    break;
	 case 'CBQ':
	    print "&nbsp;<i>CBQ</i>";
	    break;
      }
?>
          </td>
	 </tr>
	 <tr>
	  <td colspan="2">
	   Bandwidth:
	  </td>
	 </tr>
	 <tr>
	  <td style="width: 5px;"></td>
	  <td>
<?
      if(!$this->getOption("bw_inbound") || !$this->getOption("bw_outbound"))
	 print "<i>Please define bandwidth in <a href=\"". $this->self ."?mode=". MS_OPTIONS ."\">Settings-&gt;Options</a></i>";
      else
	 print "<i>". $this->getOption("bw_inbound") ."/". $this->getOption("bw_outbound") ."kbit/s</i>";
?>
          </td>
	 </tr>
<?
      if($this->getOption("ack_sl")) {
?>
         <tr> 
	  <td colspan="2">
	   ACK-Packets:
	  </td>
	 </tr>
	 <tr>
	  <td style="width: 5px;"></td>
	  <td>
<?
	 print "<i>". $this->getServiceLevelName($this->getOption("ack_sl")) ."</i>";
?>
          </td>
	 </tr>
<?
      }
?>
         <tr>
	  <td colspan="2">
	   Filter:
	  </td>
	 </tr>
	 <tr>
	  <td style="width: 5px;"></td>
	  <td>
<?
      switch($this->getOption("filter")) {
         case 'tc':
	    print "<i>tc-filter</i>";
	    break;
	 case 'ipt':
	    print "<i>iptables-match</i>";
	    break;
      }
?>
	  </td>
	 </tr>
	 <tr>
	  <td colspan="2">
	   Shaper Status:
	  </td>
	 </tr>
	 <tr>
	  <td style="width: 5px;"></td>
	  <td>
<?
      switch($this->getShaperStatus()) {

         case true:
	    print "<i>active</i>";
	    break;
	 case false:
	    print "<i>inactive</i>";
	    break;

      }
?>
	  </td>
	 </tr>
<?
      if($this->getOption("authentication") == "Y") {

         if($this->isSession()) {
?>
         <tr>
	  <td colspan="2">
	   Current User:
	  </td>
	 </tr>
	 <tr>
	  <td style="width: 5px;"></td>
	  <td>
	   <i><? print $_SESSION['user_name']; ?></i>
	   <a href="<? print $this->self ."?mode=". MS_LOGOUT; ?>">(logout)</a>
	  </td>
	 </tr>
<?
         }
      }
?>
        </table>
	<? $this->closeTable(); ?>
       </td>
       <!-- /left cell -->

       <td style="width: 40px;">
        &nbsp;
       </td>

       <!-- main cell -->
       <td rowspan="2" style="vertical-align: top;">
        <table style="width: 100%;">
         <tr>
          <td style="width: 100%;">
<?

   } // showMSHeader

   function showMSFooter()
   {
?>
          </td>
         </tr>
        </table>
       </td>
       <!-- /main cell -->

       <td style="width: 40px;">
        &nbsp;
       </td>
      </tr>
      <tr>
       <td colspan="3"> 
        &nbsp;
       </td>
       <td>
        &nbsp;
       </td>
      </tr>
     </table>
    </td>
   </tr>
  </table>
 </body>
</html>
<?
   } // showMSFooter()

   function goBack()
   {
?>
 <script>
 <!--
    location.href='<? print $this->self ."?mode=". $this->mode ."&saved=1"; ?>';
 -->
 </script>
<?
   } // goBack()

   function goStart()
   {
?>
 <script>
 <!--
    location.href='<? print $this->self; ?>';
 -->
 </script>
<?
   } // goStart()

   function printError($title, $text)
   {
      $this->startTable($title);
?>
  <table style="width: 100%;" class="withborder2">
   <tr>
    <td class="sysmessage">
     <? print $text; ?>
    </td>
   </tr>
   <tr>
    <td style="text-align: center;">
     <a href="javascript:history.go(-1);">Back</a>
    </td>
   </tr>
  </table>
<?
      $this->closeTable();

   } // printError()

   function printYesNo($title, $text)
   {
      $this->startTable($title);
?>
  <table style="width: 100%;" class="withborder2"> 
   <tr>
    <td class="sysmessage">
     <? print $text; ?>
    </td>
   </tr>
   <tr>
    <td style="text-align: center;">
     <a href="<? print htmlentities($_SERVER['REQUEST_URI']) ."&amp;doit=1"; ?>">Yes</a>
      &nbsp;
     <a href="<? print $this->self ."?mode=". $this->mode; ?>">No</a>
    </td>
   </tr>
  </table>
<?
      $this->closeTable();

   } // printYesNo()

   function getOption($object)
   {
      $result = $this->db->db_fetchSingleRow("SELECT setting_value FROM shaper_settings WHERE setting_key like '". $object ."'");
      return $result->setting_value;
   } // getOption()

   function setOption($key, $value)
   {
      $this->db->db_query("REPLACE INTO shaper_settings (setting_key, setting_value) VALUES ('". $key ."', '". $value ."')");
   } // setOption()	

   function getServiceLevelName($sl_idx)
   {
      $result = $this->db->db_fetchSingleRow("SELECT sl_name FROM shaper_service_levels WHERE sl_idx='". $sl_idx ."'");
      return $result->sl_name;
   } // getServiceLevelName()

   function getTargetName($target_idx)
   {
      if($target_idx != 0) {
	 $result = $this->db->db_fetchSingleRow("SELECT target_name FROM shaper_targets WHERE target_idx='". $target_idx ."'");
	 return $result->target_name;
      }
      else
         return "any";

   } // getTargetName()

   function getPipeDirectionName($direction)
   {

      switch($direction) {

         case 1: return "inbound"; break;
	 case 2: return "outbound"; break;
	 case 3: return "inbound &amp; outbound"; break;
	
      }
 
   } // getPipeDirectionName()

   function getChainDirectionName($direction)
   {

      switch($direction) {

         case 1: return "Unidirectional"; break;
	 case 2: return "Bidirectional"; break;
	
      }
 
   } // getChainDirectionName()

   function getChainName($chain_idx)
   {
      $result = $this->db->db_fetchSingleRow("SELECT chain_name FROM shaper_chains WHERE chain_idx='". $chain_idx ."'");
      return $result->chain_name;
   } // getChainName()

   function getProtocolById($proto_idx)
   {
      if($proto = $this->db->db_fetchSingleRow("SELECT proto_name FROM shaper_protocols WHERE proto_idx LIKE '". $proto_idx ."'"))
         return $proto->proto_name;
   } // getProtocolById()

   function getProtocolNumberById($proto_idx)
   {
      if($proto = $this->db->db_fetchSingleRow("SELECT proto_number FROM shaper_protocols WHERE proto_idx LIKE '". $proto_idx ."'"))
         return $proto->proto_number;
      else
         return 0;
   } // getProtocolNumberById()
	 
   function getProtocolByName($proto_name)
   {
      if($proto = $this->db->db_fetchSingleRow("SELECT proto_idx FROM shaper_protocols WHERE proto_name LIKE '". $proto_name ."'"))
	 return $proto->proto_idx;
      else
	 return false;
   } // getProtocolByName()

   function getPortByName($port_name)
   {
      if($port = $this->db->db_fetchSingleRow("SELECT port_idx FROM shaper_ports WHERE port_name LIKE '". $port_name ."'"))
	 return $port->port_idx;
      else
	 return false;
   } // getPortByName()

   function getServiceLevelByName($sl_name)
   {
      if($sl = $this->db->db_fetchSingleRow("SELECT sl_idx FROM shaper_service_levels WHERE sl_name LIKE '". $sl_name ."'"))
	 return $sl->sl_idx;
      else
	 return false;
   } // getServiceLevelByName()

   function getTargetByName($target_name)
   {
      if($target = $this->db->db_fetchSingleRow("SELECT target_idx FROM shaper_targets WHERE target_name LIKE '". $target_name ."'"))
	 return $target->target_idx;
      else
	 return false;
   } // getTargetByName()

   function getChainByName($chain_name)
   {
      if($chain = $this->db->db_fetchSingleRow("SELECT chain_idx FROM shaper_chains WHERE chain_name LIKE '". $chain_name ."'"))
	 return $chain->chain_idx;
      else
	 return false;
   } // getChainByName()

   function getFilterByName($filter_name)
   {
      if($serv = $this->db->db_fetchSingleRow("SELECT filter_idx FROM shaper_filters WHERE filter_name LIKE '". $filter_name ."'"))
	 return $serv->filter_idx;
      else
	 return false;
   } // getFilterByName()

   function extract_tc_stat($line, $limit_to = "")
   {
      $pairs = Array();
      $pairs = split(',', $line);

      foreach($pairs as $pair) {
	 list($key, $value) = split('=', $pair);

	 if(preg_match("/". $limit_to ."/", $key)) {

	    $key = preg_replace("/". $limit_to ."/", "", $key);

	    if($key == "")
	       $key = 0;
				
	    if($value >= 0)
	       $data[$key] = $value;
	    else
	       $data[$key] = 0;
	 }
      }

      return $data;

   } // extract_tc_stat()

   function getPriorityName($prio)
   {
      switch($prio) {
	 case 0: return "Ignored"; break;
	 case 1: return "Highest"; break;
	 case 2: return "High";    break;
	 case 3: return "Normal";  break;
	 case 4: return "Low";     break;
	 case 5: return "Lowest";  break;
      }
   } // getPriorityName()

   function getYearList($current = "")
   {
      $string = "";
      for($i = date("Y"); $i <= date("Y")+2; $i++) {
	 $string.= "<option value=\"". $i ."\"";
         if($i == $current)
	    $string.= " selected=\"selected\"";
	 $string.= ">". $i ."</option>";	
      }
      return $string;
   } // getYearList()

   function getMonthList($current = "")
   {
      $string = "";
      for($i = 1; $i <= 12; $i++) {
	 $string.= "<option value=\"". $i ."\"";
	 if($i == $current)
	    $string.= " selected=\"selected\"";
	 if(date("m") == $i && $current == "")
	    $string.= " selected=\"selected\"";
	 $string.= ">". $i ."</option>";	
      }
      return $string;
   } // getMonthList()

   function getDayList($current = "")
   {
      $string = "";
      for($i = 1; $i <= 31; $i++) {
         $string.= "<option value=\"". $i ."\"";
         if($i == $current)
	    $string.= " selected=\"selected\"";
	 if(date("d") == $i && $current == "")
	    $string.= " selected=\"selected\"";
	 $string.= ">". $i ."</option>";
      }      
      return $string;
   } // getDayList()
       
   function getHourList($current = "")
   {
      $string = "";
      for($i = 0; $i <= 23; $i++) {
         $string.= "<option value=\"". $i ."\"";
	 if($i == $current)
	    $string.= " selected=\"selected\"";
	 if(date("H") == $i && $current == "") 
	    $string.= " selected=\"selected\"";
	 $string.= ">". sprintf("%02d", $i) ."</option>";
      }
      return $string;
   } // getHourList()

   function getMinuteList($current = "")
   {
      $string = "";
      for($i = 0; $i <= 59; $i++) {
         $string.= "<option value=\"". $i ."\"";
	 if($i == $current)
	    $string.= " selected=\"selected\"";
	 if(date("i") == $i && $current == "")
	    $string.= " selected=\"selected\"";
	 $string.= ">". sprintf("%02d", $i)  ."</option>";
      }
      return $string;
   } // getMinuteList()

   function getL7ProtocolByName($l7proto_name)
   {

      if($l7proto = $this->db->db_fetchSingleRow("SELECT l7proto_idx FROM shaper_l7_protocols WHERE l7proto_name LIKE '". $l7proto_name ."'"))
	 return $l7proto->l7proto_idx;
      else
	 return false;

   } // getL7ProtocolByName()

   function startTable($header_text)
   {
?>
  <table style="width: 100%;">
   <tr>
    <td style="width: 30px; height: 65px; background-image: url(images/ms_table_top_left.jpg);" />
    <td>
     <table style="height: 65px; width: 100%;">
      <tr>
       <td style="height: 10px;" />
      </tr>
      <tr>
       <td style="height: 30px; background-repeat: repeat-x; background-image: url(images/ms_table_top_middle_middle.jpg);" class="tablehead">
        <? print $header_text; ?>
       </td>
      </tr>
      <tr>
       <td style="background-image: url(images/ms_table_top_middle_bottom.jpg); height: 25px; background-repeat: repeat-x;" />
      </tr>
     </table>
    </td>
    <td style="width: 30px; height: 65px; background-image: url(images/ms_table_top_right.jpg)" />
   </tr>
   <tr>
    <td style="width: 30px; background-image: url(images/ms_table_middle_left.jpg);" />
    <td style="background-image: url(images/ms_table_middle_middle.jpg);">
<?
   } // startTable()

   function closeTable()
   {
?>
    </td>
    <td style="width: 30px; background-image: url(images/ms_table_middle_right.jpg);" />
   </tr>
   <tr>
    <td style="width: 30px; height: 65px; background-image: url(images/ms_table_bottom_left.jpg);" />
    <td style="height: 65px; background-repeat: repeat-x; background-image: url(images/ms_table_bottom_middle.jpg);" />
    <td style="width: 30px; height: 65px; background-image: url(images/ms_table_bottom_right.jpg);" />
   </tr>
  </table>
<?
   } // closeTable()

   function isSession()
   {

      if(session_is_registered('user_name') && session_is_registered('user_idx')) 
         return true;
      else
         return false;

   } // isSession()

   function startSession()
   {

      session_name("MASTERSHAPER");
      session_start();

   } // startSession()

   function destroySession()
   {
      session_unregister("user_name");
      session_unregister("user_idx");

      session_destroy();

      $this->goStart();

   } // destroySession()

   function showLogin()
   {

      if(!isset($_GET['proceed'])) {

         $this->startTable("<img src=\"". ICON_HOME ."\" alt=\"home icon\" />&nbsp;MasterShaper Login");
?>
     <form action="<? print $this->self ."?proceed=1"; ?>" method="POST" id='login'>
     <table style="width: 100%;">
      <tr>
       <td>
        <table class="withborder2" style="margin-left:auto; margin-right:auto; text-align: center;">
	 <tr>
	  <td>
	   User:
          </td>
          <td>
           <input type="text" name="user_name" size="15" />
          </td>
         </tr>
         <tr>
          <td>
           Password:
          </td>
          <td>
           <input type="password" name="user_pass" size="15" />
          </td>
         </tr>
	 <tr>
	  <td>
	   &nbsp;
	  </td>
	  <td>
	   <input type="submit" value="Login" />
	  </td>
	 </tr>
        </table>
       </td>
      </tr>
     </table>
     </form>
<?
	 $this->closeTable();

      }
      else {

         if(isset($_POST['user_name']) && $_POST['user_name'] != "" &&
	    isset($_POST['user_pass']) && $_POST['user_pass'] != "") {

            if($user = $this->getUserDetails($_POST['user_name'])) {

	       if($user->user_pass == md5($_POST['user_pass'])) {

	          $_SESSION['user_name'] = $_POST['user_name'];
		  $_SESSION['user_idx'] = $user->user_idx;

		  session_register("user_name", "user_idx");

		  $this->goStart();

	       }
	       else
	          $this->printError("<img src=\"". ICON_HOME ."\" alt=\"home icon\" />&nbsp;MasterShaper Login", "Invalid Password.");

            }
	    else
	       $this->printError("<img src=\"". ICON_HOME ."\" alt=\"home icon\" />&nbsp;MasterShaper Login", "Invalid or inactive User.");

	 }
	 else {

	    $this->printError("<img src=\"". ICON_HOME ."\" alt=\"home icon\" />&nbsp;MasterShaper Login", "Please enter Username and Password.");

	 }
      
      }

   } // showLogin()

   function getUserDetails($user_name)
   {

      if($user = $this->db->db_fetchSingleRow("SELECT user_idx, user_pass FROM shaper_users WHERE "
                    ."user_name LIKE '". $user_name ."' AND "
		    ."user_active='Y'"))

         return $user;
      else
         return NULL;

   } // getUserDetails()

   function checkPermissions($permission)
   {

      $user = $this->db->db_fetchSingleRow("SELECT ". $permission ." FROM shaper_users WHERE "
         ."user_idx='". $_SESSION['user_idx'] ."'");

      if($user->$permission == "Y")
         return true;
      else
         return false;

   } // checkPermissions()

   function setShaperStatus($status)
   {

      $this->setOption("status", $status);

   } // setShaperStatus()

   function getShaperStatus()
   {

      return $this->getOption("status");

   } // getShaperStatus()

}

?>
