<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
 <head>
  <!-- to block this stupid msnbot from fetching the whole mastershaper sites -->
  <meta name="msnbot" content="noindex,nofollow" />
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title>MasterShaper Installer</title>
  <link rel="stylesheet" href="../shaper_style.css" type="text/css" />
  <link rel="shortcut icon" href="../favicon.ico" />
 </head>
 <body>
  <table style="width: 100%; height: 100%;">
   <tr>
    <td style="width: 100%; height: 150px;">
     <table style="width: 100%;">
      <tr>
       <td style="background-image: url(../images/ms_header_left.jpg); background-position: right;">
        &nbsp;
       </td>
       <td style="background-image: url(../images/ms_header_right.jpg); width: 796px; height: 150px; vertical-align: top;">
        <table>
         <tr>
          <td style="height: 121px;" colspan="2" />
         </tr>
         <tr>
          <td style="width: 213px;" />
          <td>
           <table style="width: 540px; text-align: center; padding: 5px;">
            <tr>
             <td>
	      MasterShaper Installer
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

   <tr>
    <td style="height: 20px;">
     &nbsp;
    </td>
   </tr>

   <tr>
    <td style="width: 100%;">
     <table>
      <tr>
     
       <td style="width: 40px";>
        &nbsp;
       </td>

       <td style="vertical-align: top; width: 100%;">
        <table style="width: 100%;">
         <tr>
          <td style="width: 30px; height: 65px; background-image: url(../images/ms_table_top_left.jpg);" />
          <td>
           <table style="height: 65px; width: 100%;">
            <tr>
             <td style="height: 10px;" />
            </tr>
            <tr>
             <td style="height: 30px; background-repeat: repeat-x; background-image: url(../images/ms_table_top_middle_middle.jpg);" class="tablehead">
              MasterShaper Installation Options
             </td>
            </tr>
            <tr>
             <td style="background-image: url(../images/ms_table_top_middle_bottom.jpg); height: 25px; background-repeat: repeat-x;" />
            </tr>
           </table>
          </td>
          <td style="width: 30px; height: 65px; background-image: url(../images/ms_table_top_right.jpg)" />
         </tr>
         <tr>
          <td style="width: 30px; background-image: url(../images/ms_table_middle_left.jpg);" />
          <td style="background-image: url(../images/ms_table_middle_middle.jpg);">
           <table class="withborder2" style="width: 100%;">
<?

require_once "../shaper_config.php";
require_once "../shaper_db.php";

class MSINSTALL {

   /* Class constructor */
   function MSINSTALL()
   {

   } // MSINSTALL()

   /* load installer */
   function load()
   {

      if(!isset($_GET['step']))
         $_GET['step'] = 0;

      switch($_GET['step']) {

	 default:
	 case 0:

	    $shaper_path = str_replace("/setup", "", getcwd());
	    $shaper_web  = str_replace("/setup/index.php", "", $_SERVER['SCRIPT_NAME']);
	    $mysql_host  = "localhost";
	    $mysql_db    = "mastershaper";
	    $mysql_user  = "mastershaper";
	    $mysql_pass  = "password";
	    $tc_bin      = "/sbin/tc";
	    $ipt_bin     = "/sbin/iptables";
	    $sudo_bin    = "/usr/bin/sudo";
	    $temp_path   = "/tmp";

	    if(function_exists("posix_getpwuid") && function_exists("posix_getuid")) {

	       $user_info    = posix_getpwuid(posix_getuid());
	       $running_user = $user_info['name'];

	    }
	    else
	       $running_user = "apache, www-data, ...";

	    $config = new MSCONFIG(0);
	    if($config->readCfg($shaper_path ."/config.dat")) {

	       $shaper_path	= SHAPER_PATH;
	       $shaper_web	= SHAPER_WEB;
	       $mysql_host	= MYSQL_HOST;
	       $mysql_db	= MYSQL_DB;
	       $mysql_user	= MYSQL_USER;
	       $mysql_pass	= MYSQL_PASS;
	       $tc_bin		= TC_BIN;
	       $ipt_bin	        = IPT_BIN;
	       $sudo_path       = SUDO_BIN;
	       $temp_path       = TEMP_PATH;
	    
	    }

	    $config->readCfg($shaper_path ."/icons.dat");
?>
            <form action="<? print $_SERVER['PHP_SELF']; ?>?step=1" method="POST">
	     <tr>
	      <td colspan="3" style="text-align: center">
	       This installer will guide you to setup MasterShaper or upgrade from a previous installation.
	      </td>
	     </tr>
	     <tr>
	      <td colspan="3" style="text-align: center" class="sysmessage">
	       Read all comments &amp; informations carefully here! They will help you understanding what you are doing here!
	      </td>
	     </tr>
	     <tr>
	      <td colspan="3" style="text-align: center"> 
               You will be also redirected to the MasterShaper Installer if the configuration file (config.dat) is not available or accessable. The upgrade process is capable of altering existing database tables to fit the needings of newer MasterShaper versions.
	      </td>
	     </tr>
	     <tr>
	      <td colspan="3" style="text-align: center; font-style: italic;" class="sysmessage">
               THIS INSTALLER SCRIPT IS A SECURITY RISK IF REACHABLE FOR EVERYONE! THEREFOR MasterShaper Installer WILL SET FILE PERMISSIONS TO 0000 AFTER IT HAS DONE IT'S JOB! PROBABLY YOU WILL SEE SOME ERROR MESSAGES (permission denied, ...) IF YOU TRY TO ENTER MASTERSHAPER INSTALLER AGAIN. IN THIS CASE CORRECT THE PERMISSIONS FIRST!
	      </td>
	     </tr>
	     <tr>
	      <td colspan="3">
	       &nbsp;
	      </td>
	     </tr>
	     <tr>
	      <td colspan="3">
	       <img src="../<? print ICON_OPTIONS; ?>" alt="option icon" />&nbsp;Paths
	      </td>
	     </tr>
	     <tr>
	      <td noWrap>
	       Filesystem path:
	      </td>
	      <td>
	       <input type="text" name="shaper_path" size="40" value="<? print $shaper_path; ?>" />
	      </td>
	      <td>
	       Filesystem path of your MasterShaper installation (ex. /var/www/shaper). This directory <font style="color: #AF0000;">MUST BE WRITEABLE</font> for the user which runs the webserver (<? print $running_user; ?>), so MasterShaper Installer can write the configuration file! Enter path without trailing slash. Under normal conditions the path should be auto-detected correctly.
	      </td>
	     </tr>
	     <tr>
	      <td noWrap>
	       Web path:
	      </td>
	      <td>
	       <input type="text" name="shaper_web" size="40" value="<? print $shaper_web; ?>" />
	      </td>
	      <td>
	       Relative web path of your MasterShaper installation (ex. /shaper for http://host/shaper). Enter path without trailing slash. Under normal conditions the path should be auto-detected correctly.
	      </td>
	     </tr>
	     <tr>
	      <td colspan="3">
	       <img src="../<? print ICON_OPTIONS; ?>" alt="option icon" />&nbsp;MySQL parameters
	      </td>
	     </tr>
	     <tr>
	      <td noWrap>
	       MySQL Host:
	      </td>
	      <td>
	       <input type="text" name="mysql_host" size="40" value="<? print $mysql_host; ?>" />
	      </td>
	      <td>
	       MySQL Host (localhost, ...) on which a running instance is available.
	      </td>
	     </tr>
	     <tr>
	      <td noWrap>
	       MySQL Database:
	      </td>
	      <td>
	       <input type="text" name="mysql_db" size="40" value="<? print $mysql_db; ?>" />
	      </td>
	      <td>
	       MySQL Database which will hold the MasterShaper tables (has to already exist).
	      </td>
	     </tr>
	     <tr>
	      <td noWrap>
	       MySQL User:
	      </td>
	      <td>
	       <input type="text" name="mysql_user" size="40" value="<? print $mysql_user; ?>" />
	      </td>
	      <td>
	       MySQL User on the above entered host which has access to the above entered MySQL database (has to already exist).
	      </td>
	     </tr>
	     <tr>
	      <td noWrap>
	       MySQL Pass:
	      </td>
	      <td>
	       <input type="text" name="mysql_pass" size="40" value="<? print $mysql_pass; ?>" />
	      </td>
	      <td>
	       MySQL Password of the above entered MySQL user (cleartext!).
	      </td>
	     </tr>
	     <tr>
	      <td colspan="3">
	       <img src="../<? print ICON_OPTIONS; ?>" alt="option icon" />&nbsp;Other parameters
	      </td>
	     </tr>
	     <tr>
	      <td noWrap>
	       sudo:
	      </td>
	      <td>
	       <input type="text" name="sudo_bin" size="40" value="<? print $sudo_bin; ?>" />
	      </td>
	      <td>
	       Location of the sudo binary.
	      </td>
	     </tr>
	     </tr>
	     <tr>
	      <td noWrap>
	       tc:
	      </td>
	      <td>
	       <input type="text" name="tc_bin" size="40" value="<? print $tc_bin; ?>" />
	      </td>
	      <td>
	       Location of the tc binary provided by the iproute utilities.
	      </td>
	     </tr>
	     <tr>
	      <td noWrap>
	       iptables:
	      </td>
	      <td>
	       <input type="text" name="ipt_bin" size="40" value="<? print $ipt_bin; ?>" />
	      </td>
	      <td>
	       Location of the iptables binary.
	      </td>
	     </tr>
	     <tr>
	      <td noWrap>
	       Temp-Path:
	      </td>
	      <td>
	       <input type="text" name="temp_path" size="40" value="<? print $temp_path; ?>" />
	      </td>
	      <td>
	       Path for temporary files which MUST be writeable by running user of your webserver (<? print $running_user; ?>).
	      </td>
	     </tr>
	     <tr>
	      <td colspan="3">
	       <img src="../<? print ICON_OPTIONS; ?>" alt="option icon" />&nbsp;Prestaging
	      </td>
	     </tr>
	     <tr>
	      <td>
	       Prefill:
	      </td>
	      <td>
	       <input type="checkbox" name="prefill_ports" value="Y" checked="checked" />port numbers
	       <br />
	       <input type="checkbox" name="prefill_protocols" value="Y" checked="checked" />protocol numbers
	      </td>
	      <td>
	       This option can prefill your port &amp; protocol definition with IANA defined numbers. Prefilling port numbers
	       can take some minutes on slower machines!
	      </td>
	     </tr>
	     <tr>
	      <td colspan="3">
	       &nbsp;
	      </td>
	     </tr>
	     <tr>
	      <td>&nbsp;</td>
	      <td><input type="submit" value="Next Step" /></td>
	      <td>In the next step, MasterShaper will check your input and try to make a test connection to database.</td>
	     </tr>
	     </form>
<?
	    break;
	 case 1:

	    if(!file_exists($_POST['shaper_path'])) {
?>
             <tr>
	      <td style="text-align: center;" class="errormessage">
	       MasterShaper Install can't locate directory <? print $_POST['shaper_path']; ?>!
              </td>
	     </tr>
	     <tr>
	      <td style="text-align: center;">
               Please go back to the previous step and check your input!<br />
	      </td>
	     </tr>
	     <tr>
	      <td style="text-align: center;">
               <a href="javascript:history.go(-1)" title="go back">[Previous Step]</a>
	      </td>
	     </tr>
<?
	    }
	    else {
	       define('MYSQL_HOST', $_POST['mysql_host']);
	       define('MYSQL_DB', $_POST['mysql_db']);
	       define('MYSQL_USER', $_POST['mysql_user']);
	       define('MYSQL_PASS', $_POST['mysql_pass']);
	       define('DB_NOERROR', true);
	       $db = new MSDB(0);
	       if($db->getConnStatus() != true) {
?>
             <tr>
	      <td style="text-align: center;" class="errormessage">
	       MasterShaper Installer can't connected to MySQL database with your entered MySQL parameters.
	      </td>
	     </tr>
	     <tr>
	      <td style="text-align: center; font-style: italic; font-size: small;">
               <? print $db->last_error; ?>
	      </td>
	     </tr>
	     <tr>
	      <td style="text-align: center; font-style: italic;">
               (Host: <? print $_POST['mysql_host']; ?>, User: <? print $_POST['mysql_user']; ?>, Pass: <? print $_POST['mysql_pass']; ?>, DB: <? print $_POST['mysql_db']; ?>)
	      </td>
	     </tr>
	     <tr>
	      <td>
	       &nbsp;
	      </td>
	     </tr>
	     <tr>
	      <td style="text-align: center;">
               Please go back to the previous step and check your input!<br />
	      </td>
	     </tr>
	     <tr>
	      <td style="text-align: center;">
               <a href="javascript:history.go(-1)" title="go back">[Previous Step]</a>
	      </td>
	     </tr>
<?
	       }
	       else {

		  if($config = @fopen($_POST['shaper_path'] ."/config.dat", "w")) {
						
		     fputs($config, "##### MasterShaper Config, created on ". strftime("%Y-%m-%d %H:%M") ." ####\n\n");
		     fputs($config, "##### MYSQL HOST #####\n");
		     fputs($config, "MYSQL_HOST=\"". $_POST['mysql_host'] ."\"\n\n");
		     fputs($config, "##### MYSQL_USER #####\n");
		     fputs($config, "MYSQL_USER=\"". $_POST['mysql_user'] ."\"\n\n");
		     fputs($config, "##### MYSQL_PASS #####\n");
		     fputs($config, "MYSQL_PASS=\"". $_POST['mysql_pass'] ."\"\n\n");
		     fputs($config, "##### MYSQL_DB   #####\n");
		     fputs($config, "MYSQL_DB=\"". $_POST['mysql_db'] ."\"\n\n");
		     fputs($config, "##### SHAPER PATH ####\n");
		     fputs($config, "SHAPER_PATH=\"". $_POST['shaper_path'] ."\"\n\n");
		     fputs($config, "##### SHAPER WEB #####\n");
		     fputs($config, "SHAPER_WEB=\"". $_POST['shaper_web'] ."\"\n\n");
		     fputs($config, "##### TC BIN #########\n");
		     fputs($config, "TC_BIN=\"". $_POST['tc_bin'] ."\"\n\n");
		     fputs($config, "##### IPT BIN ########\n");
		     fputs($config, "IPT_BIN=\"". $_POST['ipt_bin'] ."\"\n\n");
		     fputs($config, "##### SUDO BIN #######\n");
		     fputs($config, "SUDO_BIN=\"". $_POST['sudo_bin'] ."\"\n\n");
		     fputs($config, "##### TEMP PATH ######\n");
		     fputs($config, "TEMP_PATH=\"". $_POST['temp_path'] ."\"\n\n");

		     fclose($config);
		     
		     if(!@chmod($_POST['shaper_path'] ."/config.dat", 0600))
			print "<font style=\"color: #AF0000;\">Can't set mode 0600 for config.dat!</font>\n";

?>
             <form action="<? print $_SERVER['PHP_SELF']; ?>?step=2" method="POST">
             <tr>
	      <td style="text-align: center;" class="okmessage">
               MasterShaper Installer has successfully connected to your MySQL database!
	      </td>
	     </tr>
	     <tr>
	      <td style="text-align: center;">
               Settings have been stored in configuration file (<? print $_POST['shaper_path']; ?>/config.dat)
	      </td>
	     </tr>
	     <tr>
	      <td style="text-align: center;">
	       In the next step, MasterShaper Installer will check for existing, upgradeable tables or will create them as needed.<br />
	      </td>
	     </tr>
	     <tr>
	      <td style="text-align: center;">
               <input type="hidden" name="prefill_ports" value="<? print $_POST['prefill_ports']; ?>">
               <input type="hidden" name="prefill_protocols" value="<? print $_POST['prefill_protocols']; ?>">
               <input type="submit" value="Next">
	      </td>
	     </tr>
             </form>
<?
		  }
		  else {
?>
             <tr>
	      <td style="text-align: center;" class="errormessage">
	       MasterShaper Installer can't write configuration <? print $_POST['shaper_path']; ?>/config.dat.
	      </td>
	     </tr>
	     <tr>
	      <td style="text-align: center;">
               Make sure the MasterShaper directory is WRITEABLE and no other config.dat file already exists!
	      </td>
	     </tr>
	     <tr>
	      <td style="text-align: center;">
               <a href="javascript:history.go(-1)" title="Back">[Previous Step]</a>&nbsp;<a href="javascript:location.reload()" title="reload">[Click here to reload this page]</a>
	      </td>
	     </tr>
<?
		  }
	       }
	    }
?>
        </td>
       </tr>
<?
	    break;
	 case 2:
?>
       <tr>
        <td style="font-style: italic;">
<?

	    $config = new MSCONFIG($this);
	    $config->readCfg("../config.dat");
	    $db = new MSDB(0);
	
	    print "Table shaper_assign_ports... ";
	    if(!$db->db_check_table_exists("shaper_assign_ports")) {

	       $db->db_query("CREATE TABLE IF NOT EXISTS `shaper_assign_ports` (
			      `afp_idx` int(11) NOT NULL auto_increment,
			      `afp_filter_idx` int(11) default NULL,
			      `afp_port_idx` int(11) default NULL,
			      PRIMARY KEY  (`afp_idx`)
			      )");
	       print "created<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    print "Table shaper_assign_filters... ";
	    if(!$db->db_check_table_exists("shaper_assign_filters")) {
				
	       $db->db_query("CREATE TABLE shaper_assign_filters (
			      apf_idx int(11) NOT NULL auto_increment,
			      apf_pipe_idx int(11) default NULL,
			      apf_filter_idx int(11) default NULL,
			      PRIMARY KEY  (apf_idx)
			      )");
						
	       print "created<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    print "Table shaper_chains... ";
	    if(!$db->db_check_table_exists("shaper_chains")) {

	       $db->db_query("CREATE TABLE IF NOT EXISTS `shaper_chains` (
			      `chain_idx` int(11) NOT NULL auto_increment,
			      `chain_name` varchar(255) default NULL,
			      `chain_active` char(1) default NULL,
			      `chain_sl_idx` int(11) default NULL,
			      `chain_src_target` int(11) default NULL,
			      `chain_dst_target` int(11) default NULL,
			      `chain_position` int(11) default NULL,
			      `chain_direction` int(11) default NULL,
			      `chain_fallback_idx` int(11) default NULL,
			      `chain_tc_id` varchar(16) default NULL,
			      PRIMARY KEY  (`chain_idx`)
			      )");
	       print "created<br />\n";
	    }
	    else
	       print "already exists<br />\n";
	
	    print "Table shaper_pipes... ";
	    if(!$db->db_check_table_exists("shaper_pipes")) {
	       
	       $db->db_query("CREATE TABLE IF NOT EXISTS `shaper_pipes` (
			      `pipe_idx` int(11) NOT NULL auto_increment,
			      `pipe_chain_idx` int(11) default NULL,
			      `pipe_name` varchar(255) default NULL,
			      `pipe_sl_idx` int(11) default NULL,
			      `pipe_position` int(11) default NULL,
			      `pipe_direction` int(11) default NULL,
			      `pipe_active` char(1) default NULL,
			      `pipe_tc_id` varchar(16) default NULL,
			      PRIMARY KEY  (`pipe_idx`)
			      )");
	       print "created<br />\n";
	    }	
	    else
	       print "already exists<br />\n";

	    print "Table shaper_ports... \n";
	    if(!$db->db_check_table_exists("shaper_ports")) {
	       
	       $db->db_query("CREATE TABLE IF NOT EXISTS `shaper_ports` (
			      `port_idx` int(11) NOT NULL auto_increment,
			      `port_name` varchar(255) default NULL,
			      `port_desc` varchar(255) default NULL,
			      `port_number` varchar(255) default NULL,
			      `port_user_defined` char(1) default NULL,
			      PRIMARY KEY  (`port_idx`)
			      )");

               if($_POST['prefill_ports'] == 'Y') {
                  /* load IANA ports */
	          $this->loadPortsfromFile(&$db);
               }

	       print "created and filled with data<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    print "Table shaper_protocols... \n";
	    if(!$db->db_check_table_exists("shaper_protocols")) {
	       $db->db_query("CREATE TABLE IF NOT EXISTS `shaper_protocols` (
			      `proto_idx` int(11) NOT NULL auto_increment,
			      `proto_number` varchar(255) default NULL,
			      `proto_name` varchar(255) default NULL,
			      `proto_desc` varchar(255) default NULL,
			      `proto_user_defined` char(1) default NULL,
			      PRIMARY KEY  (`proto_idx`)
			      )");
						
               if($_POST['prefill_protocols'] == 'Y') {
                  /* load IANA protocols */
	          $this->loadProtocolsfromFile(&$db);
               }

	       print "created and filled with data<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    print "Table shaper_service_levels... ";
	    if(!$db->db_check_table_exists("shaper_service_levels")) {
	       
	       $db->db_query("CREATE TABLE `shaper_service_levels` (
			      `sl_idx` int(11) NOT NULL auto_increment,
			      `sl_name` varchar(255) default NULL,
			      `sl_htb_bw_in_rate` varchar(255) default NULL,
			      `sl_htb_bw_in_ceil` varchar(255) default NULL,
			      `sl_htb_bw_in_burst` varchar(255) default NULL,
			      `sl_htb_bw_out_rate` varchar(255) default NULL,
			      `sl_htb_bw_out_ceil` varchar(255) default NULL,
			      `sl_htb_bw_out_burst` varchar(255) default NULL,
			      `sl_htb_priority` varchar(255) default NULL,
			      `sl_hfsc_in_umax` varchar(255) default NULL,
			      `sl_hfsc_in_dmax` varchar(255) default NULL,
			      `sl_hfsc_in_rate` varchar(255) default NULL,
			      `sl_hfsc_in_ulrate` varchar(255) default NULL,
			      `sl_hfsc_out_umax` varchar(255) default NULL,
			      `sl_hfsc_out_dmax` varchar(255) default NULL,
			      `sl_hfsc_out_rate` varchar(255) default NULL,
			      `sl_hfsc_out_ulrate` varchar(255) default NULL,
			      `sl_cbq_in_rate` varchar(255) default NULL,
			      `sl_cbq_in_priority` varchar(255) default NULL,
			      `sl_cbq_out_rate` varchar(255) default NULL,
			      `sl_cbq_out_priority` varchar(255) default NULL,
			      `sl_cbq_bounded` char(1) default NULL,
			      `sl_qdisc` varchar(255) default NULL,
			      `sl_netem_delay` varchar(255) default NULL,
			      `sl_netem_jitter` varchar(255) default NULL,
			      `sl_netem_random` varchar(255) default NULL,
			      `sl_netem_distribution` varchar(255) default NULL,
			      `sl_netem_loss` varchar(255) default NULL,
			      `sl_netem_duplication` varchar(255) default NULL,
			      `sl_netem_gap` varchar(255) default NULL,
			      `sl_netem_reorder_percentage` varchar(255) default NULL,
			      `sl_netem_reorder_correlation` varchar(255) default NULL,
			      `sl_esfq_perturb` varchar(255) default NULL,
			      `sl_esfq_limit` varchar(255) default NULL,
			      `sl_esfq_depth` varchar(255) default NULL,
			      `sl_esfq_divisor` varchar(255) default NULL,
			      `sl_esfq_hash` varchar(255) default NULL,
			      PRIMARY KEY  (`sl_idx`)
			      )");
	       print "created<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    print "Table shaper_filters... ";
	    if(!$db->db_check_table_exists("shaper_filters")) {

	       $db->db_query("CREATE TABLE `shaper_filters` (
			      `filter_idx` int(11) NOT NULL auto_increment,
			      `filter_name` varchar(255) default NULL,
			      `filter_protocol_id` int(11) default NULL,
			      `filter_TOS` varchar(4) default NULL,
			      `filter_tcpflag_syn` char(1) default NULL,
			      `filter_tcpflag_ack` char(1) default NULL,
			      `filter_tcpflag_fin` char(1) default NULL,
			      `filter_tcpflag_rst` char(1) default NULL,
			      `filter_tcpflag_urg` char(1) default NULL,
			      `filter_tcpflag_psh` char(1) default NULL,
			      `filter_packet_length` varchar(255) default NULL,
			      `filter_p2p_edk` char(1) default NULL,
			      `filter_p2p_kazaa` char(1) default NULL,
			      `filter_p2p_dc` char(1) default NULL,
			      `filter_p2p_gnu` char(1) default NULL,
			      `filter_p2p_bit` char(1) default NULL,
			      `filter_p2p_apple` char(1) default NULL,
			      `filter_p2p_soul` char(1) default NULL,
			      `filter_p2p_winmx` char(1) default NULL,
			      `filter_p2p_ares` char(1) default NULL,
			      `filter_time_use_range` char(1) default NULL,
			      `filter_time_start` int(11) default NULL,
			      `filter_time_stop` int(11) default NULL,
			      `filter_time_day_mon` char(1) default NULL,
			      `filter_time_day_tue` char(1) default NULL,
			      `filter_time_day_wed` char(1) default NULL,
			      `filter_time_day_thu` char(1) default NULL,
			      `filter_time_day_fri` char(1) default NULL,
			      `filter_time_day_sat` char(1) default NULL,
			      `filter_time_day_sun` char(1) default NULL,
                              `filter_match_ftp_data` char(1) default NULL,
			      `filter_src_target` int(11) default NULL,
			      `filter_dst_target` int(11) default NULL,
			      `filter_direction` int(11) default NULL,
			      `filter_active` char(1) default NULL,
			      PRIMARY KEY  (`filter_idx`))");

	       print "created<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    print "Table shaper_settings... ";
	    if(!$db->db_check_table_exists("shaper_settings")) {
					
	       $db->db_query("CREATE TABLE `shaper_settings` (
			      `setting_key` varchar(255) NOT NULL default '',
			      `setting_value` varchar(255) default NULL,
			      PRIMARY KEY  (`setting_key`)
			      )");

               $db->db_query("INSERT INTO shaper_settings (setting_key, setting_value) "
	                    ." VALUES ('filter', 'tc')");
               $db->db_query("INSERT INTO shaper_settings (setting_key, setting_value) "
	                    ." VALUES ('classifier', 'HTB')");
               $db->db_query("INSERT INTO shaper_settings (setting_key, setting_value) "
	                    ." VALUES ('qdisc', 'SFQ')");
               $db->db_query("INSERT INTO shaper_settings (setting_key, setting_value) "
	                    ." VALUES ('msmode', 'router')");
               $db->db_query("INSERT INTO shaper_settings (setting_key, setting_value) "
	                    ." VALUES ('imq_if', 'N')");

	       print "created<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    print "Table shaper_stats... ";
	    if(!$db->db_check_table_exists("shaper_stats")) {
					
	       $db->db_query("CREATE TABLE `shaper_stats` (
			      `stat_time` int(11) NOT NULL default '0',
			      `stat_data` text,
			      PRIMARY KEY  (`stat_time`)
			      )");

	       print "created<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    print "Table shaper_targets... ";
	    if(!$db->db_check_table_exists("shaper_targets")) {
					
	       $db->db_query("CREATE TABLE `shaper_targets` (
			      `target_idx` int(11) NOT NULL auto_increment,
			      `target_name` varchar(255) default NULL,
			      `target_match` varchar(16) default NULL,
			      `target_ip` varchar(255) default NULL,
			      `target_mac` varchar(255) default NULL,
			      PRIMARY KEY  (`target_idx`)
			      )");

	       print "created<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    print "Table shaper_assign_target_groups... ";
	    if(!$db->db_check_table_exists("shaper_assign_target_groups")) {

	       $db->db_query("CREATE TABLE `shaper_assign_target_groups` (
	                      `atg_idx` int(11) NOT NULL auto_increment,
			      `atg_group_idx` int(11) NOT NULL,
			      `atg_target_idx` int(11) NOT NULL,
			      PRIMARY KEY (`atg_idx`)
			      )");

               print "created<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    print "Table shaper_tc_ids... ";
	    if(!$db->db_check_table_exists("shaper_tc_ids")) {

	       $db->db_query("CREATE TABLE `shaper_tc_ids` (
			      `id_pipe_idx` int(11) default NULL,
			      `id_chain_idx` int(11) default NULL,
			      `id_if` varchar(255) default NULL,
			      `id_tc_id` varchar(255) default NULL,
			      `id_color` varchar(7) default NULL,
			      KEY `id_pipe_idx` (`id_pipe_idx`),
			      KEY `id_chain_idx` (`id_chain_idx`),
			      KEY `id_if` (`id_if`),
			      KEY `id_tc_id` (`id_tc_id`),
			      KEY `id_color` (`id_color`)
			      ) ENGINE=MEMORY");

	       print "created<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    print "Table shaper_l7_protocols... ";
	    if(!$db->db_check_table_exists("shaper_l7_protocols")) {

	       $db->db_query("CREATE TABLE `shaper_l7_protocols` (
	                      `l7proto_idx` int(11) NOT NULL auto_increment,
			      `l7proto_name` varchar(255) default NULL,
			      PRIMARY KEY (`l7proto_idx`)
			      )");

	       print "created<br />\n";
            }
	    else
	       print "already exists<br />\n";
	      
	    print "Table shaper_assign_l7_protocols... ";
	    if(!$db->db_check_table_exists("shaper_assign_l7_protocols")) {

	       $db->db_query("CREATE TABLE `shaper_assign_l7_protocols` (
	                      `afl7_idx` int(11) NOT NULL auto_increment,
			      `afl7_filter_idx` int(11) NOT NULL,
			      `afl7_l7proto_idx` int(11) NOT NULL,
			      PRIMARY KEY(`afl7_idx`)
			      )");

               print "created<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    print "Table shaper_users... ";
	    if(!$db->db_check_table_exists("shaper_users")) {

	       $db->db_query("CREATE TABLE `shaper_users` (
                              `user_idx` int(11) NOT NULL auto_increment,
                              `user_name` varchar(32) default NULL,
		              `user_pass` varchar(32) default NULL,
		              `user_manage_chains` char(1) default NULL,
		              `user_manage_pipes` char(1) default NULL,
		   	      `user_manage_filters` char(1) default NULL,
			      `user_manage_ports` char(1) default NULL,
			      `user_manage_protocols` char(1) default NULL,
			      `user_manage_targets` char(1) default NULL,
			      `user_manage_users` char(1) default NULL,
			      `user_manage_options` char(1) default NULL,
			      `user_manage_servicelevels` char(1) default NULL,
			      `user_show_rules` char(1) default NULL,
			      `user_load_rules` char(1) default NULL,
			      `user_show_monitor` char(1) default NULL,
			      `user_active` char(1) default NULL,
			      PRIMARY KEY  (`user_idx`)
			      )");

               $db->db_query("INSERT INTO shaper_users (user_name, user_pass, user_manage_chains, "
	          ."user_manage_pipes, user_manage_filters, user_manage_ports, user_manage_protocols, "
		  ."user_manage_targets, user_manage_users, user_manage_options, user_manage_servicelevels, "
		  ."user_show_rules, user_load_rules, user_show_monitor, user_active) VALUES ("
		  ."'admin', "
		  ."'". md5("changeme") ."', "
		  ."'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y')");

               print "created<br />\n";
	    }
	    else
	       print "already exists<br />\n";

	    /* if no version is set, set one */
	    if(!$db->getVersion())
	       $db->setVersion(VERSION);

	    /************* UPDATE PROCEDURES **************/

	    // v0.22
	    if($db->getVersion() < 0.22) {

	       $db->db_alter_table("shaper_protocols", "add", "proto_user_defined", "char(1) default NULL AFTER proto_desc");
	       $db->db_query("UPDATE shaper_protocols SET proto_user_defined='N' WHERE proto_user_defined<>'Y'");
	       $db->setVersion("0.22");
	
	       print "Tables upgraded to version 0.22!<br />\n";
	    
	    }

	    // v0.23
	    if($db->getVersion() < 0.23) {
	       
	       $db->db_alter_table("shaper_service_levels", "change", "sl_bw_in_rate", "sl_htb_bw_in_rate varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "change", "sl_bw_in_ceil", "sl_htb_bw_in_ceil varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "change", "sl_bw_in_burst", "sl_htb_bw_in_burst varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "change", "sl_bw_out_rate", "sl_htb_bw_out_rate varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "change", "sl_bw_out_ceil", "sl_htb_bw_out_ceil varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "change", "sl_bw_out_burst", "sl_htb_bw_out_burst varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "change", "sl_priority", "sl_htb_priority varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_hfsc_in_umax", "varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_hfsc_in_dmax", "varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_hfsc_in_rate", "varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_hfsc_in_ulrate", "varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_hfsc_out_umax", "varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_hfsc_out_dmax", "varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_hfsc_out_rate", "varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_hfsc_out_ulrate", "varchar(255) default NULL");

	       $db->setVersion("0.23");
					
	       print "Tables upgraded to version 0.23!<br />\n";

	    }

	    // v0.24
	    if($db->getVersion() < 0.24) {

	       $db->db_alter_table("shaper_service_levels", "add", "sl_cbq_in_rate", "varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_cbq_in_priority", "varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_cbq_out_rate", "varchar(255) default NULL");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_cbq_out_priority", "varchar(255) default NULL");

	       if(!$db->db_fetchSingleRow("SELECT setting_value FROM shaper_settings WHERE setting_key LIKE 'qdisc'"))
		  $db->db_query("INSERT INTO shaper_settings (setting_key, setting_value) "
			       ."VALUES ('qdisc', 'HTB');");

	       $db->setVersion("0.24");

	       print "Tables upgraded to version 0.24!<br />\n";

	    }

	    // v0.31
	    if($db->getVersion() < 0.31) {

               /* if the installer already created the shaper_filters table, we drop it here,
	          because we want to migrate the data from shaper_services to shaper_filters */
	       $db->db_drop_table("shaper_filters");

	       $db->db_rename_table("shaper_services", "shaper_filters");
	       $db->db_alter_table("shaper_filters", "change", "serv_idx", "filter_idx int(11) NOT NULL auto_increment");
	       $db->db_alter_table("shaper_filters", "change", "serv_name", "filter_name varchar(255) default NULL");
	       $db->db_alter_table("shaper_filters", "change", "serv_active", "filter_active char(1) default NULL");
	       $db->db_alter_table("shaper_filters", "add", "filter_protocol_id", "int(11) default NULL after filter_name");
	       $db->db_alter_table("shaper_filters", "add", "filter_TOS", "varchar(4) default NULL after filter_protocol_id");
	       $db->db_alter_table("shaper_filters", "add", "filter_tcpflag_syn", "char(1) default NULL after filter_TOS");
	       $db->db_alter_table("shaper_filters", "add", "filter_tcpflag_ack", "char(1) default NULL after filter_tcpflag_syn");
	       $db->db_alter_table("shaper_filters", "add", "filter_tcpflag_fin", "char(1) default NULL after filter_tcpflag_ack");
	       $db->db_alter_table("shaper_filters", "add", "filter_tcpflag_rst", "char(1) default NULL after filter_tcpflag_fin");
	       $db->db_alter_table("shaper_filters", "add", "filter_tcpflag_urg", "char(1) default NULL after filter_tcpflag_rst");
	       $db->db_alter_table("shaper_filters", "add", "filter_tcpflag_psh", "char(1) default NULL after filter_tcpflag_urg");
	       $db->db_alter_table("shaper_filters", "add", "filter_packet_length", "varchar(255) default NULL after filter_tcpflag_psh");
	       $db->db_alter_table("shaper_filters", "add", "filter_p2p_edk", "char(1) default NULL after filter_packet_length");
	       $db->db_alter_table("shaper_filters", "add", "filter_p2p_kazaa", "char(1) default NULL after filter_p2p_edk");
	       $db->db_alter_table("shaper_filters", "add", "filter_p2p_dc", "char(1) default NULL after filter_p2p_kazaa");
	       $db->db_alter_table("shaper_filters", "add", "filter_p2p_gnu", "char(1) default NULL after filter_p2p_dc");
	       $db->db_alter_table("shaper_filters", "add", "filter_p2p_bit", "char(1) default NULL after filter_p2p_gnu");
	       $db->db_alter_table("shaper_filters", "add", "filter_p2p_apple", "char(1) default NULL after filter_p2p_bit");
	       $db->db_alter_table("shaper_filters", "add", "filter_p2p_soul", "char(1) default NULL after filter_p2p_apple");
	       $db->db_alter_table("shaper_filters", "add", "filter_p2p_winmx", "char(1) default NULL after filter_p2p_soul");
	       $db->db_alter_table("shaper_filters", "add", "filter_p2p_ares", "char(1) default NULL after filter_p2p_winmx");
	       $db->db_alter_table("shaper_filters", "add", "filter_time_use_range", "char(1) default NULL after filter_p2p_ares");
	       $db->db_alter_table("shaper_filters", "add", "filter_time_start", "int(11) default NULL after filter_time_use_range");
	       $db->db_alter_table("shaper_filters", "add", "filter_time_stop", "int(11) default NULL after filter_time_start");
	       $db->db_alter_table("shaper_filters", "add", "filter_time_day_mon", "char(1) default NULL after filter_time_stop");
	       $db->db_alter_table("shaper_filters", "add", "filter_time_day_tue", "char(1) default NULL after filter_time_day_mon");
	       $db->db_alter_table("shaper_filters", "add", "filter_time_day_wed", "char(1) default NULL after filter_time_day_tue");
	       $db->db_alter_table("shaper_filters", "add", "filter_time_day_thu", "char(1) default NULL after filter_time_day_wed");
	       $db->db_alter_table("shaper_filters", "add", "filter_time_day_fri", "char(1) default NULL after filter_time_day_thu");
	       $db->db_alter_table("shaper_filters", "add", "filter_time_day_sat", "char(1) default NULL after filter_time_day_fri");
	       $db->db_alter_table("shaper_filters", "add", "filter_time_day_sun", "char(1) default NULL after filter_time_day_sat");

               /* if the installer already created the shaper_assign_filters table, we drop it here,
	          because we want to migrate the data from shaper_assign_services to shaper_filters */
	       $db->db_drop_table("shaper_assign_filters");

	       $db->db_rename_table("shaper_assign_services", "shaper_assign_filters");
	       $db->db_alter_table("shaper_assign_filters", "change", "aps_idx", "apf_idx int(11) NOT NULL auto_increment");
	       $db->db_alter_table("shaper_assign_filters", "change", "aps_pipe_idx", "apf_pipe_idx int(11) default NULL");
	       $db->db_alter_table("shaper_assign_filters", "change", "aps_serv_idx", "apf_filter_idx int(11) default NULL");

	       $db->db_alter_table("shaper_assign_ports", "change", "asp_serv_idx", "asp_filter_idx int(11) default NULL");

	       $db->db_alter_table("shaper_ports", "drop", "port_TOS");
	       $db->db_alter_table("shaper_ports", "drop", "port_protocol_id");

	       if(!$db->db_fetchSingleRow("SELECT setting_value FROM shaper_settings WHERE setting_key LIKE 'filter'"))
		  $db->db_query("INSERT INTO shaper_settings (setting_key, setting_value) "
			       ."VALUES ('filter', 'tc');");

	       $db->setVersion("0.31");

	       print "Tables upgraded to version 0.31!<br />\n";

	    }

	    // v0.32
	    if($db->getVersion() < 0.32) {

	       $db->db_alter_table("shaper_targets", "change", "target_hosts", "target_ip varchar(255) default NULL");
	       $db->db_alter_table("shaper_targets", "add", "target_match", "varchar(16) default NULL after target_name");
	       $db->db_alter_table("shaper_targets", "add", "target_mac", "varchar(255) default NULL after target_ip");
	       $db->db_query("UPDATE shaper_targets SET target_match='IP'");

	       $db->db_alter_table("shaper_assign_ports", "change", "asp_idx", "afp_idx int(11) NOT NULL auto_increment");
	       $db->db_alter_table("shaper_assign_ports", "change", "asp_filter_idx", "afp_filter_idx int(11) default NULL");
	       $db->db_alter_table("shaper_assign_ports", "change", "asp_port_idx", "afp_port_idx int(11) default NULL");

	       $db->setVersion("0.32");

	       print "Tables upgraded to version 0.32!<br />\n";

	    }

	    // v0.40
	    if($db->getVersion() < 0.40) {

	       $db->db_alter_table("shaper_service_levels", "add", "sl_cbq_bounded", "char(1) default NULL after sl_cbq_out_priority");
	       $db->db_alter_table("shaper_filters", "add", "filter_match_ftp_data", "char(1) default NULL after filter_time_day_sun");

	       $db->db_query("UPDATE shaper_chains SET chain_direction='1' WHERE chain_direction='2'");
	       $db->db_query("UPDATE shaper_chains SET chain_direction='2' WHERE chain_direction='3'");

               $db->db_query("INSERT INTO shaper_settings (setting_key, setting_value) "
	                    ." VALUES ('msmode', 'router')");
               $db->db_query("INSERT INTO shaper_settings (setting_key, setting_value) "
	                    ." VALUES ('imq_if', 'N')");

	       print "Tables upgraded to version 0.40!<br />\n";

	    }

	    // v0.41
	    if($db->getVersion() < 0.41) {

	       $db->db_alter_table("shaper_stats", "dropidx", "stat_if");
	       $db->db_alter_table("shaper_stats", "drop", "stat_if");
	       $db->db_alter_table("shaper_filters", "add", "filter_src_target", "int(11) default NULL after filter_match_ftp_data");
	       $db->db_alter_table("shaper_filters", "add", "filter_dst_target", "int(11) default NULL after filter_src_target");
	       $db->db_alter_table("shaper_filters", "add", "filter_direction", "int(11) default NULL after filter_dst_target");

	       $db->db_alter_table("shaper_service_levels", "add", "sl_netem_delay", "varchar(255) default NULL after sl_cbq_bounded");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_netem_jitter", "varchar(255) default NULL after sl_netem_delay");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_netem_random", "varchar(255) default NULL after sl_netem_jitter");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_netem_distribution", "varchar(255) default NULL after sl_netem_random");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_netem_loss", "varchar(255) default NULL after sl_netem_distribution");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_netem_duplication", "varchar(255) default NULL after sl_netem_loss");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_netem_gap", "varchar(255) default NULL after sl_netem_duplication");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_netem_reorder_percentage", "varchar(255) default NULL after sl_netem_gap");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_netem_reorder_correlation", "varchar(255) default NULL after sl_netem_reorder_percentage");

	       print "Tables upgraded to version 0.41!<br />\n";

	    }

	    // v0.42
	    if($db->getVersion() < 0.42) {

	       $db->db_query("UPDATE shaper_settings SET setting_key='classifier' WHERE setting_key='qdisc'");
	       $db->db_query("ALTER TABLE shaper_tc_ids ENGINE=MEMORY");

               $db->db_alter_table("shaper_service_levels", "add", "sl_qdisc", "varchar(255) default NULL after sl_cbq_bounded");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_esfq_perturb", "varchar(255) default NULL after sl_netem_reorder_correlation");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_esfq_limit", "varchar(255) default NULL after sl_esfq_perturb");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_esfq_depth", "varchar(255) default NULL after sl_esfq_limit");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_esfq_divisor", "varchar(255) default NULL after sl_esfq_depth");
	       $db->db_alter_table("shaper_service_levels", "add", "sl_esfq_hash", "varchar(255) default NULL after sl_esfq_divisor");

	       print "Tables upgraded to version 0.42!<br />\n";

	    }

	    // v0.43
	    if($db->getVersion() < 0.43) {

	       $db->db_alter_table("shaper_targets", "change", "target_mac", "target_mac varchar(255) default NULL");

	       print "Table upgraded to version 0.43!<br />\n";

	    }
?>
        </td>
       </tr>
<?

	    /* Now set the current version */
	    if($db->getVersion() < VERSION)
	       $db->setVersion(VERSION);
	
	    if(!@chmod($_SERVER['SCRIPT_FILENAME'], "0000")) {
?>
       <tr>
        <td class="errormessage">
         MasterShaper is unable to set permission mode 0000 for setup/index.php!<br />
	 Ensure yourself that the MasterShaper setup isn't reachable for everyone!
	</td>
       </tr>
<?
            }
	    else {
?>
       <tr>
        <td class="okmessage">
	 MasterShaper chmoded the Installer files so they can't be executed anymore!
	</td>
       </tr>
<?
	    }
?>
       <tr>
        <td>
	 MasterShaper Installer has finished setup now!<br />
         <a href="../" title="MasterShaper">[Switch to MasterShaper Web Interface]</a>
        </td>
       </tr>
<?
	    break;

      }
   } // load()

   function loadPortsfromFile($db)
   {

      /* open file */
      if($file = fopen("port-numbers", "r")) {

         $i = 1;

         /* read line by line */
         while($line = fgets($file, 255)) {

            $line = trim($line);

            /* no empty lines or comments */
            if($line != "" && !preg_match("/^#/", $line)) {

               /* only line with udp & tcp are needed */
	       if(strstr($line, "udp") !== false || strstr($line, "tcp") !== false) {

                  $fields = preg_split('/[\s,]+/', $line);

                  $port_name   = trim($fields[0]);
		  $port_number = trim($fields[1]);
		  $port_desc   = "";

		  for($j = 2; $j < count($fields); $j++)
		     $port_desc.= $fields[$j] ." ";
		  $port_desc = addslashes(trim($port_desc));

		  list($port_number, $protocol) = split('/', $port_number);

                  /* if the port number is still numerical */
		  if(is_numeric($port_number)) {
		   
	             /* check if already exists */	  
		     if(!$db->db_fetchSingleRow("SELECT port_idx FROM shaper_ports WHERE "
		                               ."port_number LIKE '". $port_number ."'")
                         && strstr($query, "'". $port_number ."'") === false) {
		      
		        if($i == 1)
			   $query = "INSERT INTO shaper_ports (port_name, port_desc, port_number, "
			           ."port_user_defined) VALUES ";

			$query.= "('". $port_name ."', '". $port_desc ."', '". $port_number ."', 'N'),";

			if($i == 100) {
			   $query = substr($query, 0, strlen($query)-1);
			   $db->db_query($query);
			   $i = 0;
			}
		     
		        $i++;

		     }
                  }
               }
	    }

	 }
	 
	 $query = substr($query, 0, strlen($query)-1);
         $db->db_query($query);

         fclose($file);
      }

      return $i;

   } // loadPortsfromFile()

   function loadProtocolsfromFile($db)
   {

      /* open file */
      if($file = fopen("protocol-numbers", "r")) {

         $i = 0;

         /* read line by line */
         while($line = fgets($file, 255)) {

            $line = trim($line);

            /* no empty lines or comments */
            if($line != "" && !preg_match("/^#/", $line)) {

               /* only line with udp & tcp are needed */
	       if(preg_match("/^[0-9]/", $line)) {

                  $fields = preg_split('/[\s,]+/', $line);

                  $proto_number = trim($fields[0]);
		  $proto_name   = trim($fields[1]);
		  $proto_desc   = "";

		  for($j = 2; $j < count($fields); $j++)
		     $desc.= $fields[$j] ." ";

		  $desc = addslashes(trim($desc));

                  if($proto_number != "" && $proto_name != "") {
		     /* check if already exists */	  
		     if(!$db->db_fetchSingleRow("SELECT proto_idx FROM shaper_protocols WHERE proto_number "
					       ."LIKE '". $proto_number ."'")) {
			 
			$db->db_query("INSERT INTO shaper_protocols (proto_number, proto_name, proto_desc, proto_user_defined) "
				     ."VALUES ('". $proto_number ."', '". $proto_name ."', '". $proto_desc ."', 'N')");

		        $i++;
		     }
		  }
               }
	    }
	 }

         fclose($file);
      }

      return $i;

   } // loadPortsfromFile()
   function cleanup()
   {
   } // cleanup()

}

$installer = new MSINSTALL;
$installer->load();
$installer->cleanup();

?>
           </table>
	  </td>
          <td style="width: 30px; background-image: url(../images/ms_table_middle_right.jpg);" />
	 </tr>
         <tr>
          <td style="width: 30px; height: 65px; background-image: url(../images/ms_table_bottom_left.jpg);" />
          <td style="height: 65px; background-repeat: repeat-x; background-image: url(../images/ms_table_bottom_middle.jpg);" />
          <td style="width: 30px; height: 65px; background-image: url(../images/ms_table_bottom_right.jpg);" />
         </tr>
        </table>
       </td>

       <td style="width: 40px";>
        &nbsp;
       </td>

      </tr>

     </table>
    </td>
   </tr>
  </table>
 </body>
</html>
