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

class MSOPTIONS {

   var $parent;
   var $db;
   var $string;

   /* Class constructor */
   function MSOPTIONS($parent)
   {
      $this->parent = $parent;
      $this->db = $parent->db;
   } // MSOPTIONS()

   /* interface output */
   function showHtml()
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_manage_options")) {

	 $this->parent->printError("<img src=\"". ICON_OPTIONS ."\" alt=\"options icon\" />&nbsp;Manage Options", "You do not have enough permissions to access this module!");
	 return 0;

      }

      if(!isset($_GET['saveit'])) {

         $this->parent->startTable("<img src=\"". ICON_OPTIONS ."\" alt=\"option icon\" />&nbsp;Change MasterShaper Options");
?>
  <form action="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>&amp;saveit=1" method="post">
   <table style="width: 100%;" class="withborder2">
    <tr>
<?
         if(isset($_GET['saved'])) {
?>
     <td colspan="3" style="text-align: center;" class="sysmessage">Your settings have been saved!</td>
<?
         }
         else {
?>
     <td colspan="3">&nbsp;</td>
<?
         }
?>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_OPTIONS; ?>" alt="option icon" />&nbsp;Bandwidth
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Inbound Bandwidth:</td>
     <td style="white-space: nowrap;"><input type="text" name="bw_inbound" size="28" value="<? print $this->parent->getOption("bw_inbound"); ?>" /></td>
     <td>Maximum amount of inbound bandwidth in kbit/s.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Outbound Bandwidth:</td>
     <td style="white-space: nowrap;"><input type="text" name="bw_outbound" size="28" value="<? print $this->parent->getOption("bw_outbound"); ?>" /></td>
     <td>Maximum amount of outbound bandwidth in kbit/s.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_OPTIONS; ?>" alt="option icon" />&nbsp;Interfaces
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Incoming Interface:</td>
     <td style="white-space: nowrap;"><input type="text" name="in_interface" size="28" value="<? print $this->parent->getOption("in_interface"); ?>" /></td>
     <td>On this interface incoming traffic occurs which should be shaped.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Outgoing Interface:</td>
     <td style="white-space: nowrap;"><input type="text" name="out_interface" size="28" value="<? print $this->parent->getOption("out_interface"); ?>" /></td>
     <td>On this interface outgoing traffic occurs which should be shaped.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">IMQ:</td>
     <td>
      <input type="radio" name="imq_if" value="Y" <? if($this->parent->getOption("imq_if") == "Y") print "checked=\"checked\""; ?> />Yes
      <input type="radio" name="imq_if" value="N" <? if($this->parent->getOption("imq_if") != "Y") print "checked=\"checked\""; ?> />No
     </td>
     <td>
      Do you use IMQ (Intermediate Queuing Device) for shaping?
     </td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_OPTIONS; ?>" alt="option icon" />&nbsp;MasterShaper QoS Options
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">ACK packets:</td>
     <td style="white-space: nowrap;">
      <select name="ack_sl">
       <option value="0">Ignore</option>
<?
         $result = $this->db->db_query("SELECT sl_idx, sl_name FROM shaper_service_levels ORDER BY sl_name");
         while($row = $result->fetchRow()) {
?>
       <option value="<? print $row->sl_idx; ?>" <? if($this->parent->getOption("ack_sl") == $row->sl_idx) print "selected=\"selected\"";?>><? print $row->sl_name; ?></option>
<?
         }
?>
      </select>
     </td>
     <td>
      Should ACK- and other small packets (&lt;128byte) get a special service level? This is helpfull if you have a small upload bandwidth. There is no much needing for a high bandwidth for this (ex. 32kbit/s), but it should have a higher priority then other bulk traffic.<br />
      Be aware, that this may bypass some packets from later rules because smaller packets get matched here - so the traffic limits may not be strictly enforced. 
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      Classifier:
     </td>
     <td>
      <select name="classifier">
       <option value="HTB" <? if($this->parent->getOption("classifier") == "HTB") print "selected=\"selected\""; ?>>HTB</option>
       <option value="HFSC" <? if($this->parent->getOption("classifier") == "HFSC") print "selected=\"selected\""; ?>>HFSC</option>
       <option value="CBQ" <? if($this->parent->getOption("classifier") == "CBQ") print "selected=\"selected\""; ?>>CBQ</option>
     </td>
     <td>
      Choose HTB if you want to shape on base of maximum bandwidth rates, traffic bursts.
      Use HFSC for realtime application where network packets should not be delayed more such a specified value (VoIP).
      CBQ is the predecessor of HTB. Maybe on some systems you have only CBQ support.
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      Default Queuing Discipline:
     </td>
     <td>
      <select name="qdisc">
       <option value="SFQ" <? if($this->parent->getOption("qdisc") == "SFQ") print "selected=\"selected\""; ?>>SFQ</option>
       <option value="ESFQ" <? if($this->parent->getOption("qdisc") == "ESFQ") print "selected=\"selected\""; ?>>ESFQ</option>
       <option value="HFSC" <? if($this->parent->getOption("qdisc") == "HFSC") print "selected=\"selected\""; ?>>HFSC</option>
      </select>
     </td>
     <td>
      This specifies the default qdisc for pipes. It's generally not a good idea to mix between different qdiscs.
      However, MasterShaper supports to specify different qdiscs for pipes.
     </td>
    </tr>
<?
         if($this->parent->getOption("qdisc") == "ESFQ") {
?>
    <tr>
     <td>
      ESFQ Perturb:
     </td>
     <td>
      <input type="text" name="esfq_default_perturb" value="<? if($this->parent->getOption("esfq_default_perturb") == "") print "20"; else print $this->parent->getOption("esfq_default_perturb"); ?>" size="28" />
     </td>
     <td>
      Default ESFQ perturb value. See Service Level for more informations.
     </td>
    </tr>
    <tr>
     <td>
      ESFQ Limit:
     </td>
     <td>
      <input type="text" name="esfq_default_limit" value="<? if($this->parent->getOption("esfq_default_limit") == "") print "128"; else print $this->parent->getOption("esfq_default_limit"); ?>" size="28" />
     </td>
     <td>
      Default ESFQ limit value. See Service Level for more informations.
     </td>
    </tr>
    <tr>
     <td>
      ESFQ Depth:
     </td>
     <td>
      <input type="text" name="esfq_default_depth" value="<? if($this->parent->getOption("esfq_default_depth") == "") print "128"; else print $this->parent->getOption("esfq_default_depth"); ?>" size="28" />
     </td>
     <td>
      Default ESFQ depth value. See Service Level for more informations.
     </td>
    </tr>
    <tr>
     <td>
      ESFQ Divisor:
     </td>
     <td>
      <input type="text" name="esfq_default_divisor" value="<? if($this->parent->getOption("esfq_default_divisor") == "") print "10"; else print $this->parent->getOption("esfq_default_divisor"); ?>" size="28" />
     </td>
     <td>
      Default ESFQ divisor value. See Service Level fore more informations.
     </td>
    </tr>
    <tr>
     <td>
      ESFQ Hash:
     </td>
     <td>
      <select name="esfq_default_hash">
       <option value="classic" <? if($this->parent->getOption("esfq_default_hash") == "classic") print "selected=\"selected\""; ?>>Classic</option>
       <option value="src" <? if($this->parent->getOption("esfq_default_hash") == "src") print "selected=\"selected\""; ?>>Src</option>
       <option value="dst" <? if($this->parent->getOption("esfq_default_hash") == "dst") print "selected=\"selected\""; ?>>Dst</option>
       <option value="fwmark" <? if($this->parent->getOption("esfq_default_hash") == "fwmark") print "selected=\"selected\""; ?>>Fwmark</option>
       <option value="src_direct" <? if($this->parent->getOption("esfq_default_hash") == "src_direct") print "selected=\"selected\""; ?>>Src_direct</option>
       <option value="dst_direct" <? if($this->parent->getOption("esfq_default_hash") == "dst_direct") print "selected=\"selected\""; ?>>Dst_direct</option>
       <option value="fwmark_direct" <? if($this->parent->getOption("esfq_default_hash") == "fwmark_direct") print "selected=\"selected\""; ?>>Fwmark_direct</option>
      </select>
     </td>
     <td>
      Default ESFQ hash. See Service Level fore more informations.
     </td>
    </tr>
<?
         }
?>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_OPTIONS; ?>" />&nbsp;MasterShaper Options
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      Traffic filter:
     </td>
     <td>
      <input type="radio" name="filter" value="tc"  <? if($this->parent->getOption("filter") == "tc")  print "checked=\"checked\""; ?> />tc-filter
      <input type="radio" name="filter" value="ipt" <? if($this->parent->getOption("filter") == "ipt") print "checked=\"checked\""; ?> />iptables
     </td>
     <td>
      Mechanism which filters your traffic. tc-filter is the tc-builtin filter technic. Good performance, but less options. iptables has many options for matching traffic, l7 protocols, and many more things. But this will add a second needed subsystem for shaping. Make tests if your Linux machine is powerful enough for this.
     </td>
    </tr>
    <tr>
     <td>
      Mode:
     </td>
     <td>
      <input type="radio" name="msmode" value="router" <? if($this->parent->getOption("msmode") == "router") print "checked=\"checked\""; ?> />Router
      <input type="radio" name="msmode" value="bridge" <? if($this->parent->getOption("msmode") == "bridge") print "checked=\"checked\""; ?> />Bridge
     </td>
     <td>
      This option tells MasterShaper if it is used on a router (between networks) or on a bridge (transparent in the network). This setting is very important if you use iptables as traffic filter to match network packets on the correct network interfaces. 
     </td>
    </tr>
    <tr>
     <td>
      Authentication:
     </td>
     <td>
      <input type="radio" name="authentication" value="Y" <? if($this->parent->getOption("authentication") == "Y") print "checked=\"checked\""; ?> />Yes
      <input type="radio" name="authentication" value="N" <? if($this->parent->getOption("authentication") != "Y") print "checked=\"checked\""; ?> />No
     </td>
     <td>
      Enable or disable MasterShaper's authentication mechanism. If enabled you can configure user &amp; rights in the webinterface. If disabled, no permission management will be done per MasterShaper and everyone has full control in the webinterface.
     </td>
    </tr>
    <tr>
     <td colspan="3">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td>&nbsp;</td>
     <td><input type="submit" value="Save" /></td>
     <td>Save your settings.</td>
    </tr>
   </table>
  </form>
<?
         $this->parent->closeTable();
      }
      else {
         if($_POST['bw_inbound'] == "")
            $this->parent->printError("<img src=\"". ICON_OPTIONS ."\" alt=\"option icon\" />&nbsp;Change Settings", "Enter in inbound bandwidth!");
         elseif($_POST['bw_outbound'] == "")
            $this->parent->printError("<img src=\"". ICON_OPTIONS ."\" alt=\"option icon\" />&nbsp;Change Settings", "Enter in outbound bandwidth!");
	 elseif($_POST['in_interface'] != "" && strtolower($_POST['in_interface']) == strtolower($_POST['out_interface'])) 
	    $this->parent->printError("<img src=\"". ICON_OPTIONS ."\" alt=\"option icon\" />&nbsp;Change Settings", "Inbound &amp; Outbound Interface can not be the same!");
	 elseif($_POST['in_interface'] == "" && $_POST['out_interface'] == "")
	    $this->parent->printError("<img src=\"". ICON_OPTIONS ."\" alt=\"option icon\" />&nbsp;Change Settings", "Please specify at least one incoming or outgoing interface!");
         else {
            $this->parent->setOption("bw_inbound", $_POST['bw_inbound']);
            $this->parent->setOption("bw_outbound", $_POST['bw_outbound']);
            $this->parent->setOption("in_interface", $_POST['in_interface']);
            $this->parent->setOption("out_interface", $_POST['out_interface']);
            $this->parent->setOption("ack_sl", $_POST['ack_sl']);
	    $this->parent->setOption("classifier", $_POST['classifier']);
            $this->parent->setOption("qdisc", $_POST['qdisc']);
	    $this->parent->setOption("filter", $_POST['filter']);
	    $this->parent->setOption("authentication", $_POST['authentication']);
	    $this->parent->setOption("msmode", $_POST['msmode']);
	    $this->parent->setOption("imq_if", $_POST['imq_if']);

            if($_POST['qdisc'] == "ESFQ") {

	       $this->parent->setOption("esfq_default_perturb", $_POST['esfq_default_perturb']);
	       $this->parent->setOption("esfq_default_limit", $_POST['esfq_default_limit']);
	       $this->parent->setOption("esfq_default_depth", $_POST['esfq_default_depth']);
	       $this->parent->setOption("esfq_default_divisor", $_POST['esfq_default_divisor']);
	       $this->parent->setOption("esfq_default_hash", $_POST['esfq_default_hash']);

	    }
	    
            $this->parent->goBack();
         }
      }

   } // showHtml()

   /* make mastershaper configuration downloadable */
   function saveConfig()
   {
      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_manage_options")) {

	 print "You do not have enough permissions to access this module!";
	 return 0;

      }
					     
      /* Settings */
      $result = $this->db->db_query("SELECT setting_key, setting_value FROM shaper_settings");
      while($row = $result->fetchRow()) {
         $this->Add("Settings", $row);
      }

      /* GUI users */
      $result = $this->db->db_query("SELECT user_idx, user_name, user_pass, user_manage_chains, "
                   ."user_manage_pipes, user_manage_filters, user_manage_ports, user_manage_protocols, "
		   ."user_manage_targets, user_manage_users, user_manage_options, user_manage_servicelevels, "
		   ."user_show_rules, user_load_rules, user_show_monitor, user_active FROM shaper_users");
      while($row = $result->fetchRow()) {
         $this->Add("Users", $row);
      }

      /* User definied protocols */
      $result = $this->db->db_query("SELECT proto_name, proto_number, proto_desc FROM shaper_protocols WHERE proto_user_defined='Y'");
      while($row = $result->fetchRow()) {
         $this->Add("Protocols", $row);
      }

      /* User definied ports */
      $result = $this->db->db_query("SELECT port_name, port_desc, port_number FROM shaper_ports "
                                   ."WHERE port_user_defined='Y'");
      while($row = $result->fetchRow()) {
         $this->Add("Ports", $row);
      }

      /* Service Levels */
      $result = $this->db->db_query("SELECT sl_name, sl_htb_bw_in_rate, sl_htb_bw_in_ceil, sl_htb_bw_in_burst, "
                                   ."sl_htb_bw_out_rate, sl_htb_bw_out_ceil, sl_htb_bw_out_burst, sl_htb_priority, "
				   ."sl_hfsc_in_umax, sl_hfsc_in_dmax, sl_hfsc_in_rate, sl_hfsc_in_ulrate, "
				   ."sl_hfsc_out_umax, sl_hfsc_out_dmax, sl_hfsc_out_rate, sl_hfsc_out_ulrate, "
				   ."sl_cbq_in_rate, sl_cbq_in_priority, sl_cbq_out_rate, sl_cbq_out_priority, "
				   ."sl_cbq_bounded, sl_netem_delay, sl_netem_jitter, sl_netem_random, "
				   ."sl_qdisc, sl_netem_distribution, sl_netem_loss, sl_netem_duplication, "
				   ."sl_netem_gap, sl_netem_reorder_percentage, sl_netem_reorder_correlation, "
				   ."sl_esfq_perturb, sl_esfq_limit, sl_esfq_depth, sl_esfq_divisor, sl_esfq_hash "
				   ."FROM shaper_service_levels");
      while($row = $result->fetchRow()) {
         $this->Add("Servicelevels", $row);
      }

      /* Targets, reverse order so groups are on the last position! */
      $result = $this->db->db_query("SELECT target_idx, target_name, target_match, target_ip, target_mac FROM "
                                   ."shaper_targets ORDER BY target_match DESC");
      while($row = $result->fetchRow()) {

         $members = $this->db->db_query("SELECT a.target_name FROM shaper_targets a, shaper_assign_target_groups b "
	                                     ."WHERE b.atg_group_idx='". $row->target_idx ."' AND b.atg_target_idx=a.target_idx");

         $string = '';
	 while($member = $members->fetchRow()) {
	    $string.= $member->target_name ."#";
	 }
	 $string = substr($string, 0, strlen($string)-1);
	 $row->target_members = $string;
         $this->Add("Targets", $row);
      }

      /* L7 Protocol definitions */
      $result = $this->db->db_query("SELECT l7proto_idx, l7proto_name FROM shaper_l7_protocols "
                                   ."ORDER BY l7proto_name ASC");

      while($row = $result->fetchRow()) {

         $this->Add("L7Proto", $row);

      }
		
      /* Filters */
      $result = $this->db->db_query("SELECT filter_idx, filter_name, filter_protocol_id, filter_TOS, "
                                   ."filter_tcpflag_syn, filter_tcpflag_ack, filter_tcpflag_fin, "
				   ."filter_tcpflag_rst, filter_tcpflag_urg, filter_tcpflag_psh, "
				   ."filter_packet_length, filter_p2p_edk, filter_p2p_kazaa, "
				   ."filter_p2p_dc, filter_p2p_gnu, filter_p2p_bit, filter_p2p_apple, "
				   ."filter_p2p_soul, filter_p2p_winmx, filter_p2p_ares, "
				   ."filter_time_use_range, filter_time_start, filter_time_stop, "
				   ."filter_time_day_mon, filter_time_day_tue, filter_time_day_wed, "
				   ."filter_time_day_thu, filter_time_day_fri, filter_time_day_sat, "
				   ."filter_time_day_sun, filter_match_ftp_data, filter_src_target, "
				   ."filter_dst_target, filter_direction, filter_active "
				   ."FROM shaper_filters");

      while($row = $result->fetchRow()) {

         $row->filter_protocol_id = $this->parent->getProtocolById($row->filter_protocol_id);
         $ports = $this->db->db_query("SELECT b.port_name FROM shaper_assign_ports a, shaper_ports b "
	                             ."WHERE a.afp_filter_idx='". $row->filter_idx ."' AND b.port_idx=a.afp_port_idx");

         $l7protos = $this->db->db_query("SELECT b.l7proto_name FROM shaper_assign_l7_protocols a, shaper_l7_protocols b "
	                                ."WHERE a.afl7_filter_idx='". $row->filter_idx ."' AND b.l7proto_idx=a.afl7_l7proto_idx");
         $string = '';
         while($port = $ports->fetchRow()) {
            $string .= $port->port_name ."#";
         }
         $string = substr($string, 0, strlen($string)-1);
         $row->filter_ports = $string;
	 $string = '';
	 while($l7proto = $l7protos->fetchRow()) {
	    $string .= $l7proto->l7proto_name ."#";
	 }
	 $string = substr($string, 0, strlen($string)-1);
	 $row->l7_protocols = $string;
         $this->Add("Filters", $row);
      }

      /* Chains */
      $result = $this->db->db_query("SELECT chain_name, chain_sl_idx, chain_fallback_idx, chain_src_target, "
                                   ."chain_dst_target, chain_direction, chain_position, chain_active FROM shaper_chains");
      while($row = $result->fetchRow()) {
         $row->sl_name  = $this->parent->getServiceLevelName($row->chain_sl_idx);
         $row->fb_name  = $this->parent->getServiceLevelName($row->chain_fallback_idx);
         $row->src_name = $this->parent->getTargetName($row->chain_src_target);
         $row->dst_name = $this->parent->getTargetName($row->chain_dst_target);
         $this->Add("Chains", $row);
      }

      /* Pipes */
      $result = $this->db->db_query("SELECT pipe_idx, pipe_name, pipe_chain_idx, pipe_sl_idx, pipe_direction, "
                                   ."pipe_position, pipe_active FROM shaper_pipes");
      while($row = $result->fetchRow()) {
         $string = "";
         $filters = $this->db->db_query("SELECT b.filter_name FROM shaper_assign_filters a, shaper_filters b "
	                                ."WHERE a.apf_pipe_idx='". $row->pipe_idx ."' AND a.apf_filter_idx=b.filter_idx");
         while($filter = $filters->fetchRow()) {
            $string .= $filter->filter_name ."#";
         }
         $string = substr($string, 0, strlen($string)-1);
         $row->chain_name = $this->parent->getChainName($row->pipe_chain_idx);
         $row->sl_name    = $this->parent->getServiceLevelName($row->pipe_sl_idx);
         $row->filters   = $string;
         $this->Add("Pipes", $row);
      }
		
      /* create output */
      $this->string = "# MasterShaper ". $this->parent->version ." configuration\n" 
                     ."# Andreas Unterkircher, unki@netshadow.at\n"
                     ."# \n"
                     ."# dumped on ". strftime("%Y-%m-%d %H:%M") ."\n\n" . $this->string;
		
      Header("Content-Type: application/octet-stream");
      Header("Content-Transfer-Encoding: binary\n");
      $user_agent = strtolower ($_SERVER["HTTP_USER_AGENT"]);
      if ((is_integer (strpos($user_agent, "msie"))) && (is_integer (strpos($user_agent, "win"))))
         Header("Content-Disposition: inline; filename=\"ms_config_". strftime("%Y%m%d") .".cfg\"");
      else
         Header("Content-Disposition: attachement; filename=\"ms_config_". strftime("%Y%m%d") .".cfg\"");
      Header("Content-Length: ". strlen($this->string));
      Header("Content-Description: PHP4 Download Data" );
      Header("Accept-Ranges: bytes");
      Header("Pragma: no-cache");
      Header("Cache-Control: no-cache, must-revalidate");
      Header("Cache-Control: post-check=0, pre-check=0", false);
      Header("Cache-Control: private");
      Header("Connection: close");

      print $this->string;
   } // saveConfig()

   /* restore configuration from user upload */
   function restoreConfig()
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_manage_options")) {

	 $this->parent->printError("<img src=\"". ICON_OPTIONS ."\" alt=\"options icon\" />&nbsp;Manage Options", "You do not have enough permissions to access this module!");
	 return 0;

      }

      if(!isset($_GET['restoreit'])) {

         $this->parent->startTable("<img src=\"". ICON_OPTIONS ."\" alt=\"option icon\" />&nbsp;Restore MasterShaper Configuration");

?>
  <form enctype="multipart/form-data" action="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>&amp;restoreit=1" method="post">
   <table style="width: 100%;" class="withborder2">
    <tr>
     <td class="sysmessage" style="text-align: center;">
      Your current settings are lost after uploaded MasterShaper configuration!
     </td>
    </tr>
    <tr>
     <td>&nbsp;</td>
    </tr>
    <tr>
     <td style="text-align: center;">
      <input type="file" name="ms_config" />
      <input type="submit" value="Restore" />
     </td>
    </tr>
   </table>
  </form>
<?
         $this->parent->closeTable();
      }
      else {

         $this->resetConfig(1);

         $config = Array();

         if($_FILES['ms_config']) {

            if($config = fopen($_FILES['ms_config']['tmp_name'], "r")) {

               while($line = fgets($config, 2048)) {

                  $line = trim($line);

                  if(($line != "") && (!preg_match("/^#/", $line))) {

                     list($set, $parameters) = split(":", $line, 2);

                     $object = unserialize(stripslashes($parameters));

                     $this->loadConfig($set, $object);
                  }
               }
               fclose($config);
            }
         }	

         $this->parent->goStart();

      }

   } // restoreConfig()

   /* write configuration into database */
   function loadConfig($set, $object)
   {
      switch($set) {
         case 'Settings':
            $this->db->db_query("INSERT INTO shaper_settings (setting_key, setting_value) "
                               ."VALUES ('". $object->setting_key ."', '". $object->setting_value ."')");
            break;
	 case 'Users':
	    $this->db->db_query("INSERT INTO shaper_users (user_idx, user_name, user_pass, user_manage_chains, "
	       ."user_manage_pipes, user_manage_filters, user_manage_ports, user_manage_protocols, "
               ."user_manage_targets, user_manage_users, user_manage_options, user_manage_servicelevels, "
               ."user_show_rules, user_load_rules, user_show_monitor, user_active) VALUES ("
	       ."'". $object->user_idx ."', "
	       ."'". $object->user_name ."', "
	       ."'". $object->user_pass ."', "
	       ."'". $object->user_manage_chains ."', "
	       ."'". $object->user_manage_pipes ."', "
	       ."'". $object->user_manage_filters ."', "
	       ."'". $object->user_manage_ports ."', "
	       ."'". $object->user_manage_protocols ."', "
	       ."'". $object->user_manage_targets ."', "
	       ."'". $object->user_manage_users ."', "
	       ."'". $object->user_manage_options ."', "
	       ."'". $object->user_manage_servicelevels ."', "
	       ."'". $object->user_show_rules ."', "
	       ."'". $object->user_load_rules ."', "
	       ."'". $object->user_show_monitor ."', "
	       ."'". $object->user_active ."')");
	    break;
         case 'Protocols':
            $this->db->db_query("INSERT INTO shaper_protocols (proto_name, proto_number, "
                               ."proto_desc, proto_user_defined) VALUES ('". $object->proto_name ."', "
                               ."'". $object->proto_name ."', '". $object->proto_desc ."', 'Y')");
            break;
         case 'Ports':
            $this->db->db_query("INSERT INTO shaper_ports (port_name, port_desc, port_number, "
                               ."port_user_defined) VALUES ('". $object->port_name
                               ."', '". $object->port_desc ."', '". $object->port_number 
                               ."', 'Y')");
            break;
         case 'Servicelevels':
            $this->db->db_query("INSERT INTO shaper_service_levels (sl_name, sl_htb_bw_in_rate, "
                               ."sl_htb_bw_in_ceil, sl_htb_bw_in_burst, sl_htb_bw_out_rate, "
                               ."sl_htb_bw_out_ceil, sl_htb_bw_out_burst, sl_htb_priority, "
                               ."sl_hfsc_in_umax, sl_hfsc_in_dmax, sl_hfsc_in_rate, sl_hfsc_in_ulrate, "
                               ."sl_hfsc_out_umax, sl_hfsc_out_dmax, sl_hfsc_out_rate, sl_hfsc_out_ulrate, "
			       ."sl_cbq_in_rate, sl_cbq_in_priority, sl_cbq_out_rate, sl_cbq_out_priority, "
			       ."sl_cbq_bounded, sl_qdisc, sl_netem_delay, sl_netem_jitter, sl_netem_random, "
			       ."sl_netem_distribution, sl_netem_loss, sl_netem_duplication, sl_netem_gap, "
                               ."sl_netem_reorder_percentage, sl_netem_reorder_correlation, sl_esfq_perturb, "
			       ."sl_esfq_limit, sl_esfq_depth, sl_esfq_divisor, sl_esfq_hash) "
                               ."VALUES ('". $object->sl_name ."', '". $object->sl_htb_bw_in_rate 
                               ."', '". $object->sl_htb_bw_in_ceil ."', '". $object->sl_htb_bw_in_burst 
                               ."', '". $object->sl_htb_bw_out_rate ."', '". $object->sl_htb_bw_out_ceil 
                               ."', '". $object->sl_htb_bw_out_burst ."', '". $object->sl_htb_priority 
                               ."', '". $object->sl_hfsc_in_umax ."', '". $object->sl_hfsc_in_dmax 
                               ."', '". $object->sl_hfsc_in_rate ."', '". $object->sl_hfsc_in_ulrate 
                               ."', '". $object->sl_hfsc_out_umax ."', '". $object->sl_hfsc_out_dmax 
                               ."', '". $object->sl_hfsc_out_rate ."', '". $object->sl_hfsc_out_ulrate 
			       ."', '". $object->sl_cbq_in_rate ."', '". $object->sl_cbq_in_priority
			       ."', '". $object->sl_cbq_out_rate ."', '". $object->sl_cbq_out_priority
			       ."', '". $object->sl_cbq_bounded ."', "
			       ."'". $object->sl_qdisc ."', "
			       ."'". $object->sl_netem_delay ."', "
			       ."'". $object->sl_netem_jitter ."', "
			       ."'". $object->sl_netem_random ."', "
			       ."'". $object->sl_netem_distribution ."', "
			       ."'". $object->sl_netem_loss ."', "
			       ."'". $object->sl_netem_duplication ."', "
			       ."'". $object->sl_netem_gap ."', "
			       ."'". $object->sl_netem_reorder_percentage ."', "
			       ."'". $object->sl_netem_reorder_correlation ."', "
			       ."'". $object->sl_esfq_perturb ."', "
			       ."'". $object->sl_esfq_limit ."', "
			       ."'". $object->sl_esfq_depth ."', "
			       ."'". $object->sl_esfq_divisor ."', "
			       ."'". $object->sl_esfq_hash ."')");
            break;
         case 'Targets':
            $this->db->db_query("INSERT INTO shaper_targets (target_name, target_match, target_ip, target_mac) "
                               ."VALUES ('". $object->target_name ."', '". $object->target_match ."', "
			       ."'". $object->target_ip ."', '". $object->target_mac ."')");

            $id = $this->db->db_getid();
	    $members = split('#', $object->target_members);
	    foreach($members as $member) {
	       $this->db->db_query("INSERT INTO shaper_assign_target_groups (atg_group_idx, atg_target_idx) "
				    ."VALUES ('". $id ."', '". $this->parent->getTargetByName($member) ."')");
	    }
            break;
	 case 'L7Proto':
	    $this->db->db_query("INSERT INTO shaper_l7_protocols (l7proto_idx, l7proto_name) "
	                       ."VALUES ('". $object->l7proto_idx ."', '". $object->l7proto_name ."')");
	    break;
         case 'Filters':
            $this->db->db_query("INSERT INTO shaper_filters (filter_idx, filter_name, filter_protocol_id, filter_TOS, "
                               ."filter_tcpflag_syn, filter_tcpflag_ack, filter_tcpflag_fin, "
			       ."filter_tcpflag_rst, filter_tcpflag_urg, filter_tcpflag_psh, "
			       ."filter_packet_length, filter_p2p_edk, filter_p2p_kazaa, "
			       ."filter_p2p_dc, filter_p2p_gnu, filter_p2p_bit, filter_p2p_apple, "
			       ."filter_p2p_soul, filter_p2p_winmx, filter_p2p_ares, "
	 		       ."filter_time_use_range, filter_time_start, filter_time_stop, "
			       ."filter_time_day_mon, filter_time_day_tue, filter_time_day_wed, "
			       ."filter_time_day_thu, filter_time_day_fri, filter_time_day_sat, "
			       ."filter_time_day_sun, filter_match_ftp_data, filter_src_target, "
			       ."filter_dst_target, filter_direction, filter_active) "
                               ."VALUES ('". $object->filter_idx ."', "
			       ."'". $object->filter_name ."', "
			       ."'". $this->parent->getProtocolByName($object->filter_protocol_id) ."', "
			       ."'". $object->filter_tos ."', "
			       ."'". $object->filter_tcpflag_syn ."', "
			       ."'". $object->filter_tcpflag_ack ."', "
			       ."'". $object->filter_tcpflag_fin ."', "
			       ."'". $object->filter_tcpflag_rst ."', "
			       ."'". $object->filter_tcpflag_urg ."', "
			       ."'". $object->filter_tcpflag_psh ."', "
			       ."'". $object->filter_packet_length ."', "
			       ."'". $object->filter_p2p_edk ."', "
			       ."'". $object->filter_p2p_kazaa ."', "
			       ."'". $object->filter_p2p_dc ."', "
			       ."'". $object->filter_p2p_gnu ."', "
			       ."'". $object->filter_p2p_bit ."', "
			       ."'". $object->filter_p2p_apple ."', "
			       ."'". $object->filter_p2p_soul ."', "
			       ."'". $object->filter_p2p_winmx ."', "
			       ."'". $object->filter_p2p_ares ."', "
			       ."'". $object->filter_time_use_range. "', "
			       ."'". $object->filter_time_start ."', "
			       ."'". $object->filter_time_stop ."', "
			       ."'". $object->filter_time_day_mon ."', "
			       ."'". $object->filter_time_day_tue ."', "
			       ."'". $object->filter_time_day_wed ."', "
			       ."'". $object->filter_time_day_thu ."', "
			       ."'". $object->filter_time_day_fri ."', "
			       ."'". $object->filter_time_day_sat ."', "
			       ."'". $object->filter_time_day_sun ."', "
			       ."'". $object->filter_match_ftp_data ."', "
			       ."'". $object->filter_src_target ."', "
			       ."'". $object->filter_dst_target ."', "
			       ."'". $object->filter_direction ."', "
			       ."'". $object->filter_active ."')");

            $id = $this->db->db_getid();
            $ports = split('#', $object->filter_ports);
            foreach($ports as $port) {
               $this->db->db_query("INSERT INTO shaper_assign_ports (afp_filter_idx, afp_port_idx) "
                                  ."VALUES ('". $id ."', '". $this->parent->getPortByName($port) ."')");
            }
	    $l7protos = split('#', $object->l7_protocols);
	    foreach($l7protos as $l7proto) {
	       $this->db->db_query("INSERT INTO shaper_assign_l7_protocols (afl7_filter_idx, afl7_l7proto_idx) "
	                          ."VALUES ('". $id ."', '". $this->parent->getL7ProtocolByName($l7proto) ."')");
            }
            break;
         case 'Chains':
            $this->db->db_query("INSERT INTO shaper_chains (chain_name, chain_active, chain_sl_idx, "
                               ."chain_src_target, chain_dst_target, chain_position, chain_direction, "
                               ."chain_fallback_idx) VALUES ('". $object->chain_name 
                               ."', '". $object->chain_active 
                               ."', '". $this->parent->getServiceLevelByName($object->sl_name) 
                               ."', '". $this->parent->getTargetByName($object->src_name) 
                               ."', '". $this->parent->getTargetByName($object->dst_name) 
                               ."', '". $object->chain_position ."', '". $object->chain_direction 
                               ."', '". $this->parent->getServiceLevelByName($object->fb_name) ."')");
            break;
         case 'Pipes':
            $this->db->db_query("INSERT INTO shaper_pipes (pipe_name, pipe_chain_idx, pipe_sl_idx, "
                               ."pipe_position, pipe_direction, pipe_active) VALUES ('". $object->pipe_name 
                               ."', '". $this->parent->getChainByName($object->chain_name) 
                               ."', '". $this->parent->getServiceLevelByName($object->sl_name) 
                               ."', '". $object->pipe_position ."', '". $object->pipe_direction 
                               ."', '". $object->pipe_active ."')");
            $id = $this->db->db_getid();
            $filters = split('#', $object->filters);
            foreach($filters as $filter) {
               $this->db->db_query("INSERT INTO shaper_assign_filters (apf_pipe_idx, apf_filter_idx) "
                                  ."VALUES ('". $id ."', '". $this->parent->getFilterByName($filter) ."')");
            }
            break;
      }

   } // loadConfig()

   /* remove existing configuration */
   function resetConfig($doit = 0)
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_manage_options")) {

	 $this->parent->printError("<img src=\"". ICON_OPTIONS ."\" alt=\"options icon\" />&nbsp;Manage Options", "You do not have enough permissions to access this module!");
	 return 0;

      }
					     
      if(!isset($_GET['doit']) && !$doit) {
         $this->parent->printYesNo("<img src=\"". ICON_OPTIONS ."\" alt=\"option icon\" />&nbsp;Reset MasterShaper Configuration",
	                           "Do you really want to RESET MasterShaper's configuration?"
				  ."<br />"
				  ."This will DELETE all your settings, rules, chains, pipes, ... !!!"
				  ."<br /><br />"
				  ."Of course this will also reset the version information of MasterShaper, you will be<br />"
				  ."forwarded to MasterShaper Installer after you have confirmed this procedure.");
      }
      else {

         $this->db->db_truncate_table("shaper_assign_ports");
         $this->db->db_truncate_table("shaper_assign_filters");
         $this->db->db_truncate_table("shaper_assign_target_groups");
         $this->db->db_truncate_table("shaper_chains");
         $this->db->db_truncate_table("shaper_pipes");
         $this->db->db_truncate_table("shaper_service_levels");
         $this->db->db_truncate_table("shaper_filters");
         $this->db->db_truncate_table("shaper_settings"); 
         $this->db->db_truncate_table("shaper_stats");
         $this->db->db_truncate_table("shaper_targets");
         $this->db->db_truncate_table("shaper_tc_ids");
	 $this->db->db_truncate_table("shaper_l7_protocols");
	 $this->db->db_truncate_table("shaper_assign_l7_protocols");
	 $this->db->db_truncate_table("shaper_users");
         $this->db->db_query("DELETE FROM shaper_ports WHERE port_user_defined='Y'");
         $this->db->db_query("DELETE FROM shaper_protocols WHERE proto_user_defined='Y'");

         /* If invoked by "Reset Configuration" and not "Restore Configuration" */
         if(isset($_GET['doit']))
	    $this->parent->goBack();

      }

   } // resetConfig()

   function Add($option, $object)
   {
      $object = addslashes(serialize($object));
      $this->string.= $option .":". $object ."\n";
   } // Add()

   function updateL7Protocols()
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_manage_options")) {

	 $this->parent->printError("<img src=\"". ICON_OPTIONS ."\" alt=\"options icon\" />&nbsp;Manage Options", "You do not have enough permissions to access this module!");
	 return 0;

      }

      if(!isset($_GET['doit'])) {

         $this->parent->startTable("<img src=\"". ICON_UPDATE ."\" alt=\"option icon\" />&nbsp;Update Layer7 Protocls");
?>
   <form action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;doit=1"; ?>" method="POST">
   <table style="width: 100%; text-align: center;" class="withborder2">
    <tr>
     <td>
      Please enter the path in the local filesystem, where to find .pat files of layer7 iptables match.
     </td>
    </tr>
    <tr>
     <td>
      <input type="text" name="basedir" size="30" value="/etc/l7-protocols">
      <input type="submit" value="Submit">
     </td>
    </tr>
   </table>
   </form>
<?
	 $this->parent->closeTable();

      }
      else {

	 $this->parent->startTable("<img src=\"". ICON_UPDATE ."\" alt=\"option icon\" />&nbsp;Update Layer7 Protocls");
?>
   <table style="width: 100%; text-align: center;" class="withborder2">
    <tr>
     <td>
<?

	 $protocols = Array();

	 $retval = $this->findPatFiles(&$protocols, $_POST['basedir']);

	 if($retval == "") {
?>
      Updateing...<br />
      <br />
<?
	    $new = 0;
	    $deleted = 0;

	    foreach($protocols as $protocol) {

	       // Check if already in database
	       if(!$this->db->db_fetchSingleRow("SELECT l7proto_idx FROM shaper_l7_protocols WHERE "
					      ."l7proto_name LIKE '". $protocol ."'")) {

		  $this->db->db_query("INSERT INTO shaper_l7_protocols (l7proto_name) VALUES "
				     ."('". $protocol ."')");

		  $new++;

	       }
	    }

	    if(count($protocols) > 0) {

	       $result = $this->db->db_query("SELECT l7proto_idx, l7proto_name FROM shaper_l7_protocols");
	       while($row = $result->fetchRow()) {

		  if(!in_array($row->l7proto_name, $protocols)) {

		     $this->db->db_query("DELETE FROM shaper_l7_protocols WHERE l7proto_idx='". $row->l7proto_idx ."'");
		     $deleted++;

		  }
	       }
	    }
?>
      <? print $new; ?> Protocols have been added.<br />
      <? print $deleted; ?> Protocols have been deleted.<br />
<?
	 }
	 else {
?>
      <? print $retval; ?>
<?
	 }
?>
      <br />
      <a href="<? print $this->parent->self; ?>">Back</a>
     </td>
    </tr>
   </table>
<?
	 $this->parent->closeTable();

      }

   } // updateL7Protocols()

   function findPatFiles($files, $path)
   {

      if(is_dir($path) && $dir = opendir($path)) {

         while($file = readdir($dir)) {

           if($file != "." && $file != "..") {

              if(is_dir($path ."/". $file)) {
	      
                 $this->findPatFiles(&$files, $path ."/". $file);

	      }

	      if(preg_match("/\.pat$/", $file)) {

                 array_push($files, str_replace(".pat", "", $file));
		 
	      }

	   }

	 }

         return "";

      }
      else {

         return "<font style=\"color: '#FF0000';\">Can't access directory ". $path ."!</font><br />\n";

      }
      
   } // findPatFiles()
}

?>
