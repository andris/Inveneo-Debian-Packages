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

class MSFILTERS {

   var $db;
   var $parent;

   /* Class constructor */
   function MSFILTERS($parent)
   {
      $this->db = $parent->db;
      $this->parent = $parent;
   } // MSFILTERS()

   /* interface output */
   function showHtml()
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_manage_filters")) {

	 $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Manage Filters", "You do not have enough permissions to access this module!");
	 return 0;

      }

      if(!isset($this->parent->screen))
         $this->parent->screen = 0;

      switch($this->parent->screen) {

	 default:
	 case 0:

	    $this->parent->startTable("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Manage Filters");
?>
  <table style="width: 100%;" class="withborder">
   <tr>
<?
        if(isset($_GET['saved'])) {
?>
    <td colspan="2" style="text-align: center;" class="sysmessage">You have made changes to the ruleset. Don't forget to reload them.</td>
<?
	} else {
?>
    <td colspan="2">&nbsp;</td>
<?
        }
?>
   </tr>
   <tr>
    <td colspan="2" style="text-align: center;">
     <img src="<? print ICON_NEW; ?>" alt="new icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>&amp;screen=1">Create a new Filter</a>
    </td>
   </tr>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr>
    <td><img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;<i>Filters</i></td> 
    <td style="text-align: center;"><i>Options</i></td>
   </tr>
<?
	    $result = $this->db->db_query("SELECT filter_idx, filter_name, filter_active FROM shaper_filters ORDER BY filter_name ASC");

	    while($row = $result->fetchrow()) {

?>
   <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
    <td>
     <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=2&amp;idx=". $row->filter_idx ."&amp;name=". urlencode($row->filter_name); ?>">
      <? print $row->filter_name; ?>
     </a>
    </td>
    <td style="text-align: center;">
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=3&amp;idx=". $row->filter_idx ."&amp;name=". urlencode($row->filter_name); ?>" title="Delete">
      <img src="<? print ICON_DELETE; ?>" alt="filter icon" />
     </a>
<?
	       if($row->filter_active == "Y") {
?>
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=4&amp;idx=". $row->filter_idx ."&amp;to=0"; ?>" title="Disable filter <? print $row->filter_name; ?>">
      <img src="<? print ICON_ACTIVE; ?>" alt="filter icon" />
     </a>
<?
	       }
	       else {
?>
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=4&amp;idx=". $row->filter_idx ."&amp;to=1"; ?>" title="Enable filter <? print $row->filter_name; ?>">
      <img src="<? print ICON_INACTIVE; ?>" alt="filter icon" />
     </a>
<?
	       }
?>
    </td>
   </tr>
<?
				}

?>
  </table>
<?
            $this->parent->closeTable();
	    break;

	 case 1:

	    if(!isset($_GET['saveit'])) {

	       $this->parent->startTable("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Create a new Filter");
?>
  <form action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen; ?>&amp;saveit=1" method="post" id="filters">
   <table style="width: 100%;" class="withborder2">
    <tr>
     <td colspan="3">
      <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;General
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Name:</td>
     <td><input type="text" name="filter_name" size="30" /></td>
     <td>Name of the filter.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Status:</td>
     <td>
      <input type="radio" name="filter_active" value="Y" checked="checked" />Active
      <input type="radio" name="filter_active" value="N" />Inactive
     </td>
     <td>
      Will these filter be used or not.
     </td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;Match protocols
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      Protocols:
     </td>
     <td>
      <select name="filter_protocol_id">
       <option value="-1">--- Ignore ---</option>
<?
               $result = $this->db->db_query("SELECT proto_idx, proto_name FROM shaper_protocols "
	                                    ."ORDER BY proto_name ASC");

               while($row = $result->fetchRow()) {
?>
       <option value="<? print $row->proto_idx; ?>"><? print $row->proto_name; ?></option>
<?
	       }
?>
      </select>
     </td>
     <td>
      Match on this protocol. Select TCP or UDP if you want to use port definitions! If you want
      to match both TCP &amp; UDP use IP as protocol. Be aware that tc-filter can not differ
      between TCP &amp; UDP. It will match both at the same time!
     </td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;Match ports
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Ports:</td>
     <td>
      <table class="noborder">
       <tr>
        <td>
	 <select size="10" name="avail[]" multiple="multiple">
	  <option value="">********* Unused *********</option>
<?
	       $ports = $this->db->db_query("SELECT port_idx, port_name, port_number FROM shaper_ports "
	                                   ."ORDER BY port_name ASC");
	       while($port = $ports->fetchRow()) {
		  print "<option value=\"". $port->port_idx ."\">". $port->port_name ."</option>\n";
	       }
?>
         </select>
	</td>
	<td>&nbsp;</td>
        <td>
	 <input type="button" value="&gt;&gt;" onclick="moveOptions(document.forms['filters'].elements['avail[]'], document.forms['filters'].elements['used[]']);" /><br />
         <input type="button" value="&lt;&lt;" onclick="moveOptions(document.forms['filters'].elements['used[]'], document.forms['filters'].elements['avail[]']);" />
	</td>
	<td>&nbsp;</td>
	<td>
	 <select size="10" name="used[]" multiple="multiple">
	  <option value="">********* Used *********</option>
	 </select>
        </td>
       </tr>
      </table>
     </td>
     <td>Match on specific ports. Be aware that this will only work for TCP/UDP protocols!</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;Match protocol flags
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      TOS flags:
     </td>
     <td>
      <select name="filter_tos">
       <option value="-1">Ignore</option>
       <option value="0x10">Minimize-Delay 16 (0x10)</option>
       <option value="0x08">Maximize-Throughput 8 (0x08)</option>
       <option value="0x04">Maximize-Reliability 4 (0x04)</option>
       <option value="0x02">Minimize-Cost 2 (0x02)</option>
       <option value="0x00">Normal-Service 0 (0x00)</option>
      </select>
     </td>
     <td>
      Match a specific TOS flag.
     </td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;Match targets
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      Target:
     </td>
     <td>
      <table class="noborder">
       <tr>
        <td>Source</td>
        <td>&nbsp;</td>
        <td style="text-align: right;">Destination</td>
       </tr>
       <tr>
        <td>
         <select name="filter_src_target">
          <option value="0">any</option>
<?
	       $result = $this->db->db_query("SELECT target_idx, target_name FROM shaper_targets ORDER BY target_name");
	       while($row = $result->fetchRow()) {
		  print "<option value=\"". $row->target_idx ."\">". $row->target_name ."</option>\n";
	       }
?>
         </select>
	</td>
	<td>
	 <select name="filter_direction">
	  <option value="1">--&gt;</option>
	  <option value="2" selected="selected">&lt;-&gt;</option>
	 </select>
	</td>
	<td>
	 <select name="filter_dst_target">
	  <option value="0">any</option>
<?
	       $result = $this->db->db_query("SELECT target_idx, target_name FROM shaper_targets ORDER BY target_name");
	       while($row = $result->fetchRow()) {
		  print "<option value=\"". $row->target_idx ."\">". $row->target_name ."</option>\n";
	       }
?>
         </select>
        </td>
       </tr>
      </table>
     </td>
     <td>
      Match a source and destination targets.
     </td>
    </tr>
      
<?
               if($this->parent->getOption("filter") == "ipt") {
?>
    <tr>
     <td style="white-space: nowrap;">
      TCP flags:
     </td>
     <td>
      <table class="noborder">
       <tr>
        <td><input type="checkbox" name="filter_tcpflag_syn" value="Y" />SYN</td>
	<td><input type="checkbox" name="filter_tcpflag_ack" value="Y" />ACK</td>
        <td><input type="checkbox" name="filter_tcpflag_fin" value="Y" />FIN</td>
       </tr>
       <tr>
        <td><input type="checkbox" name="filter_tcpflag_rst" value="Y" />RST</td>
        <td><input type="checkbox" name="filter_tcpflag_urg" value="Y" />URG</td>
        <td><input type="checkbox" name="filter_tcpflag_psh" value="Y" />PSH</td>
       </tr>
      </table>
     </td>
     <td>
      Match on specific TCP flags combinations.
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      Packet length:
     </td>
     <td>
      <input type="text" name="filter_packet_length" size="30" />
     </td>
     <td>
      Match a packet against a defined size. Enter a size "64" or a range "64:128".
     </td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;Other matches
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      IPP2P:
     </td>
     <td>
      <table class="noborder">
       <tr>
        <td><input type="checkbox" name="filter_p2p_edk" value="Y" />Edonkey</td>
	<td><input type="checkbox" name="filter_p2p_kazaa" value="Y" />Kazaa</td>
        <td><input type="checkbox" name="filter_p2p_dc" value="Y" />Direct Connect (DC)</td>
       </tr>
       <tr>
	<td><input type="checkbox" name="filter_p2p_gnu" value="Y" />Gnutella</td>
	<td><input type="checkbox" name="filter_p2p_bit" value="Y" />Bittorent</td>
	<td><input type="checkbox" name="filter_p2p_apple" value="Y" />AppleJuice</td>
       </tr>
       <tr>
        <td><input type="checkbox" name="filter_p2p_soul" value="Y" />SoulSeek</td>
	<td><input type="checkbox" name="filter_p2p_winmx" value="Y" />WinMX</td>
        <td><input type="checkbox" name="filter_p2p_ares" value="Y" />Ares</td>
       </tr>
      </table>
     </td>
     <td>
      Match on specific filesharing protocols. This uses the ipp2p iptables module.
      It has to be available on your iptables installation. Refer 
      <a href="http://www.ipp2p.org" onclick="window.open('http://www.ipp2p.org'); return false;">www.ipp2p.org</a> for more
      informations.
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      layer7:
     </td>
     <td>
      <table class="noborder">
       <tr>
        <td>
	 <select size="10" name="filter_l7_avail[]" multiple="multiple">
	  <option value="">********* Unused *********</option>
<?
	       $l7protos = $this->db->db_query("SELECT l7proto_idx, l7proto_name FROM shaper_l7_protocols "
	                                   ."ORDER BY l7proto_name ASC");
	       while($l7proto = $l7protos->fetchRow()) {
		  print "<option value=\"". $l7proto->l7proto_idx ."\">". $l7proto->l7proto_name ."</option>\n";
	       }
?>
         </select>
	</td>
	<td>&nbsp;</td>
        <td>
	 <input type="button" value="&gt;&gt;" onclick="moveOptions(document.forms['filters'].elements['filter_l7_avail[]'], document.forms['filters'].elements['filter_l7_used[]']);" /><br />
         <input type="button" value="&lt;&lt;" onclick="moveOptions(document.forms['filters'].elements['filter_l7_used[]'], document.forms['filters'].elements['filter_l7_avail[]']);" />
	</td>
	<td>&nbsp;</td>
	<td>
	 <select size="10" name="filter_l7_used[]" multiple="multiple">
	  <option value="">********* Used *********</option>
	 </select>
        </td>
       </tr>
      </table>
     </td>
     <td>
      Match on specific protocols. This uses the layer7 iptables module.
      It has to be available on your iptables installation. Refer
      <a href="http://l7-filter.sourceforge.net" onclick="window.open('http://l7-filter.sourceforge.net'); return false;">l7-filter.sf.net</a> for more
      informations.<br />
      <br />
      Use Other-&gt;Update L7 Protocols to load current available l7 pat files.
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      Time:
     </td>
     <td>
      <table class="noborder">
       <tr> 
        <td colspan="2">
	 <input type="checkbox" name="filter_time_use_range" value="Y" />Use time range:
	</td>
       </tr>
       <tr>
        <td colspan="2">
	 &nbsp;
	</td>
       </tr>
       <tr>
        <td>
         Start:
	</td>
	<td>
         <select name="filter_time_start_year">
          <? print $this->parent->getYearList(); ?>
         </select>
         -
         <select name="filter_time_start_month">
          <? print $this->parent->getMonthList(); ?>
         </select>
         -
         <select name="filter_time_start_day">
          <? print $this->parent->getDayList(); ?>
         </select>
         &nbsp;
         <select name="filter_time_start_hour">
          <? print $this->parent->getHourList(); ?>
         </select>
         :
         <select name="filter_time_start_minute">
          <? print $this->parent->getMinuteList(); ?>
         </select>
	</td>
       </tr>
       <tr>
        <td>
         Stop:
	</td>
	<td>
         <select name="filter_time_stop_year">
          <? print $this->parent->getYearList(); ?>
          </select>
         -
         <select name="filter_time_stop_month">
          <? print $this->parent->getMonthList(); ?>
         </select>
         -
         <select name="filter_time_stop_day">
          <? print $this->parent->getDayList(); ?>
         </select>
         &nbsp;
         <select name="filter_time_stop_hour">
          <? print $this->parent->getHourList(); ?>
         </select>
         :
         <select name="filter_time_stop_minute">
          <? print $this->parent->getMinuteList(); ?>
         </select>
	</td>
       </tr>
       <tr>
        <td colspan="2">
	 &nbsp;
	</td>
       </tr>
       <tr>
        <td>
	 Days:
	</td>
	<td>
         <input type="checkbox" name="filter_time_day_mon" value="Y" />Mon
         <input type="checkbox" name="filter_time_day_tue" value="Y" />Tue
         <input type="checkbox" name="filter_time_day_wed" value="Y" />Wed
         <input type="checkbox" name="filter_time_day_thu" value="Y" />Thu
         <input type="checkbox" name="filter_time_day_fri" value="Y" />Fri
         <input type="checkbox" name="filter_time_day_sat" value="Y" />Sat
         <input type="checkbox" name="filter_time_day_sun" value="Y" />Sun
	</td>
       </tr>
      </table>
     </td>
     <td>
      Match if the packet is within a defined timerange. Nice for file transfer operations,
      which you want to limit during the day, but have full bandwidth in the night for backup.
      This uses the time iptables match which has to be available on your iptables installation
      and supported by your running kernel.
     </td>
    </tr>
    <tr>
     <td>
      FTP data:
     </td>
     <td>
      <input type="checkbox" name="filter_match_ftp_data" value="Y" />Match FTP data channel
     </td>
     <td>
      A FTP file transfer needs two connections: command channel (21/tcp) and a data channel. If you use active FTP the port for data channel is 20/tcp. If you use passive FTP, the port of the data channel is not predictable and is choosen by the ftp server (high port). But with the help of the iptables kernel module ip_conntrack_ftp you get the data channel which belongs to the command channel! Don't forget to load the ip_conntrack_ftp module!
     </td>
    </tr>
<?
               }
?>
    <tr>
     <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
     <td style="text-align: center;"><a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>" title="Back"><img src="<? print ICON_ARROW_LEFT; ?>" alt="arrow left icon" /></a></td>
     <td><input type="submit" value="Save" onclick="selectAll(document.forms['filters'].elements['used[]']); selectAll(document.forms['filters'].elements['filter_l7_used[]']);" /></td>
     <td>Save settings.</td>
    </tr>
   </table> 
  </form>
<?
               $this->parent->closeTable();
	    }
	    else {

               /* Name provided? */
	       if($_POST['filter_name'] == "") {
		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Create a new Filter", "Please enter a filter name!");
		  $error = true;
               }
	        
	       /* Filter with this name already exists? */
	       if(!isset($error) && $this->db->db_fetchSingleRow("SELECT filter_idx FROM shaper_filters WHERE "
					       ."filter_name LIKE '". $_POST['filter_name'] ."'")) {

		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Create a new Filter", "A filter with the name ". $_POST['filter_name'] ." already exists. Please choose another name!");

		  $error = true;
               }

               /* A useful filter? */
               if(!isset($error) && $_POST['filter_protocol_id'] == -1 && count($_POST['used']) <= 1 && $_POST['filter_tos'] == -1 &&
	          !$_POST['filter_tcpflag_syn'] && !$_POST['filter_tcpflag_ack'] && !$_POST['filter_tcpflag_fin'] &&
		  !$_POST['filter_tcpflag_rst'] && !$_POST['filter_tcpflag_urg'] && !$_POST['filter_tcpflag_psh'] &&
		  !$_POST['filter_packet_length'] && !$_POST['filter_p2p_edk'] && !$_POST['filter_p2p_kazaa'] &&
		  !$_POST['filter_p2p_dc'] && !$_POST['filter_p2p_gnu'] && !$_POST['filter_p2p_bit'] &&
		  !$_POST['filter_p2p_apple'] && !$_POST['filter_p2p_soul'] && !$_POST['filter_p2p_winmx'] &&
		  !$_POST['filter_p2p_ares'] && !$_POST['filter_time_use_range'] && !$_POST['filter_time_day_mon'] &&
		  !$_POST['filter_time_day_tue'] && !$_POST['filter_time_day_wed'] && !$_POST['filter_time_day_thu'] &&
		  !$_POST['flter_time_day_fri'] && !$_POST['filter_time_day_sat'] && !$_POST['filter_time_day_sun'] && 
		  count($_POST['filter_l7_used']) <= 1 && $_POST['filter_src_target'] == 0 && $_POST['filter_dst_target'] == 0) {

		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Create a new Filter", "This filter has nothing to do. Please select at least one match!");
		  $error = true;
               }

	       /* Ports can only be used with TCP, UDP or IP protocol */
	       if(!isset($error) && count($_POST['used']) > 1 && ($this->parent->getProtocolNumberById($_POST['filter_protocol_id']) != 4 &&
		  $this->parent->getProtocolNumberById($_POST['filter_protocol_id']) != 17 &&
		  $this->parent->getProtocolNumberById($_POST['filter_protocol_id']) != 6)) {

		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Create a new Filter", "Ports can only be used in combination with IP, TCP or UDP protocol!");

		  $error = true;
	       }

	       /* TCP-flags can only be used with TCP protocol */
	       if(!isset($error) && ($_POST['filter_tcpflag_syn'] || $_POST['filter_tcpflag_ack'] || $_POST['filter_tcpflag_fin'] ||
		      $_POST['filter_tcpflag_rst'] || $_POST['filter_tcpflag_urg'] || $_POST['filter_tcpflag_psh']) &&
		      $this->parent->getProtocolNumberById($_POST['filter_protocol_id']) != 6) {

		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Create a new Filter", "TCP-Flags can only be used in combination with TCP protocol!");

		  $error = true;
	       }

	       /* ipp2p can only be used with no ports and only with tcp &| udp protocol or without any protocol */
	       if(!isset($error) && ($_POST['filter_p2p_edk'] || $_POST['filter_p2p_kazaa'] || $_POST['filter_p2p_dc'] ||
		  $_POST['filter_p2p_gnu'] || $_POST['filter_p2p_bit'] || $_POST['filter_p2p_apple'] ||
		  $_POST['filter_p2p_soul'] || $_POST['filter_p2p_winmx'] || $_POST['filter_p2p_ares']) &&
		  (count($_POST['used']) > 1 || (($this->parent->getProtocolNumberById($_POST['filter_protocol_id']) != 17 &&
		  $this->parent->getProtocolNumberById($_POST['filter_protocol_id']) != 6) &&
		  $_POST['filter_protocol_id'] != -1)  || count($_POST['filter_l7_used']) > 1)) {
		  
		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Create a new Filter", "IPP2P match can only be used with no ports select and only with protocols TCP or UDP or completly ignoring protocols!<br />Also IPP2P can not be used in combination with layer7 filters.");

		  $error = true;
	       }

	       /* layer7 protocol match can only be used with no ports and no tcp &| udp protocols */
	       if(!isset($error) && count($_POST['filter_l7_used']) > 1 && $_POST['filter_protocol_id'] != -1) {

		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Create a new Filter", "Layer7 match can only be used with no ports select and no protocol definitions!");
		  
		  $error = true;
               }


	       /* no errors yet? */
	       if(!isset($error)) {

		  $start_time = strtotime(sprintf("%04d-%02d-%02d %02d:%02d:00", 
					  $_POST['filter_time_start_year'],
					  $_POST['filter_time_start_month'],
					  $_POST['filter_time_start_day'],
					  $_POST['filter_time_start_hour'], 
					  $_POST['filter_time_start_minute']));
		  $stop_time = strtotime(sprintf("%04d-%02d-%02d %02d:%02d:00",
					  $_POST['filter_time_stop_year'],
					  $_POST['filter_time_stop_month'],
					  $_POST['filter_time_stop_day'],
					  $_POST['filter_time_stop_hour'],
					  $_POST['filter_time_stop_minute']));

		  $this->db->db_query("INSERT INTO shaper_filters (filter_name, filter_protocol_id, "
				     ."filter_TOS, filter_tcpflag_syn, filter_tcpflag_ack, filter_tcpflag_fin, "
				     ."filter_tcpflag_rst, filter_tcpflag_urg, filter_tcpflag_psh, "
				     ."filter_packet_length, filter_p2p_edk, filter_p2p_kazaa, filter_p2p_dc, "
				     ."filter_p2p_gnu, filter_p2p_bit, filter_p2p_apple, filter_p2p_soul, "
				     ."filter_p2p_winmx, filter_p2p_ares, filter_time_use_range, "
				     ."filter_time_start, filter_time_stop, filter_time_day_mon, "
				     ."filter_time_day_tue, filter_time_day_wed, filter_time_day_thu, "
				     ."filter_time_day_fri, filter_time_day_sat, filter_time_day_sun, "
				     ."filter_match_ftp_data, filter_src_target, filter_dst_target, "
				     ."filter_direction, filter_active) "
				     ."VALUES ('". $_POST['filter_name'] ."', '". $_POST['filter_protocol_id'] ."', "
				     ."'". $_POST['filter_tos'] ."', '". $_POST['filter_tcpflag_syn'] ."', "
				     ."'". $_POST['filter_tcpflag_ack'] ."', '". $_POST['filter_tcpflag_fin'] ."', "
				     ."'". $_POST['filter_tcpflag_rst'] ."', '". $_POST['filter_tcpflag_urg'] ."', "
				     ."'". $_POST['filter_tcpflag_psh'] ."', '". $_POST['filter_packet_length'] ."', "
				     ."'". $_POST['filter_p2p_edk'] ."', '". $_POST['filter_p2p_kazaa'] ."', "
				     ."'". $_POST['filter_p2p_dc'] ."', '". $_POST['filter_p2p_gnu'] ."', "
				     ."'". $_POST['filter_p2p_bit'] ."', '". $_POST['filter_p2p_apple'] ."', "
				     ."'". $_POST['filter_p2p_soul'] ."', '". $_POST['filter_p2p_winmx'] ."', "
				     ."'". $_POST['filter_p2p_ares'] ."', '". $_POST['filter_time_use_range'] ."', "
				     ."'". $start_time ."', '". $stop_time ."', "
				     ."'". $_POST['filter_time_day_mon'] ."', '". $_POST['filter_time_day_tue'] ."', "
				     ."'". $_POST['filter_time_day_wed'] ."', '". $_POST['filter_time_day_thu'] ."', "
				     ."'". $_POST['filter_time_day_fri'] ."', '". $_POST['filter_time_day_sat'] ."', "
				     ."'". $_POST['filter_time_day_sun'] ."', '". $_POST['filter_match_ftp_data'] ."', "
				     ."'". $_POST['filter_src_target'] ."', '". $_POST['filter_dst_target'] ."', "
				     ."'". $_POST['filter_direction'] ."', '". $_POST['filter_active'] ."')");
     
		  $idx = $this->db->db_getid();

		  if($_POST['used']) {
		     foreach($_POST['used'] as $use) 
			if($use != "")
			   $this->db->db_query("INSERT INTO shaper_assign_ports (afp_filter_idx, afp_port_idx) VALUES ('". $idx ."', '". $use ."')");
		  }

		  if($_POST['filter_l7_used']) {
		     foreach($_POST['filter_l7_used'] as $use)
		        if($use != "")
			   $this->db->db_query("INSERT INTO shaper_assign_l7_protocols (afl7_filter_idx, afl7_l7proto_idx) "
			                      ."VALUES ('". $idx ."', '". $use ."')");
                  }

		  $this->parent->goBack();
	       }
	    }
	    break;
	    
	 case 2:

	    if(!isset($_GET['saveit'])) {

	       $filter = $this->db->db_fetchSingleRow("SELECT * FROM shaper_filters WHERE filter_idx='". $_GET['idx'] ."'");

               $this->parent->startTable("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Modify Filter ". $filter->filter_name);

?>
  <form action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;idx=". $_GET['idx'] ."&amp;namebefore=". urlencode($filter->filter_name); ?>&amp;saveit=1" method="post" id="filters">
   <table style="width: 100%;" class="withborder2"> 
    <tr>
     <td colspan="3">
      <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;General
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Name:</td>
     <td><input type="text" name="filter_name" size="30" value="<? print $filter->filter_name; ?>" /></td>
     <td>Name of the filter.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Status:</td>
     <td>
      <input type="radio" name="filter_active" value="Y" <? if($filter->filter_active == 'Y') print "checked=\"checked\""; ?> />Active
      <input type="radio" name="filter_active" value="N" <? if($filter->filter_active != 'Y') print "checked=\"checked\""; ?> />Inactive
     </td>
     <td>
      Will these filter be used or not.
     </td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;Match protocols
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      Protocols:
     </td>
     <td>
      <select name="filter_protocol_id">
       <option value="-1">--- Ignore ---</option>
<?
               $result = $this->db->db_query("SELECT proto_idx, proto_name FROM shaper_protocols "
	                                    ."ORDER BY proto_name ASC");

               while($row = $result->fetchRow()) {
?>
       <option value="<? print $row->proto_idx; ?>" <? if($row->proto_idx == $filter->filter_protocol_id) print "selected=\"selected\""; ?>><? print $row->proto_name; ?></option>
<?
	       }
?>
     </td>
     <td>
      Match on this protocol. Select TCP or UDP if you want to use port definitions! If you want
      to match both TCP &amp; UDP use IP as protocol. Be aware that tc-filter can not differ
      between TCP &amp; UDP. It will match both at the same time!
     </td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;Match ports
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Ports:</td>
     <td>
      <table class="noborder">
       <tr>
        <td>
	 <select size="10" name="avail[]" multiple="multiple">;
	  <option value="">********* Unused *********</option>
<?
	$ports = $this->db->db_query("SELECT port_idx, port_name, port_number FROM shaper_ports ORDER BY port_name ASC");
	while($port = $ports->fetchRow()) {
		if(!$this->db->db_fetchSingleRow("SELECT afp_idx FROM shaper_assign_ports WHERE afp_filter_idx='". $_GET['idx'] ."' AND afp_port_idx='". $port->port_idx ."'"))
			print "<option value=\"". $port->port_idx ."\">". $port->port_name ."</option>\n";
	}
?>
         </select>
	</td>
	<td>&nbsp;</td>
        <td>
	 <input type="button" value="&gt;&gt;" onclick="moveOptions(document.forms['filters'].elements['avail[]'], document.forms['filters'].elements['used[]']);" /><br />
         <input type="button" value="&lt;&lt;" onclick="moveOptions(document.forms['filters'].elements['used[]'], document.forms['filters'].elements['avail[]']);" />
	</td>
	<td>&nbsp;</td>
	<td>
	 <select size="10" name="used[]" multiple="multiple">
	  <option value="">********* Used *********</option>
<?
	$ports = $this->db->db_query("SELECT port_idx, port_name, port_number FROM shaper_ports ORDER BY port_name ASC");
	while($port = $ports->fetchRow()) {
		if($this->db->db_fetchSingleRow("SELECT afp_idx FROM shaper_assign_ports WHERE afp_filter_idx='". $_GET['idx'] ."' AND afp_port_idx='". $port->port_idx ."'"))
			print "<option value=\"". $port->port_idx ."\">". $port->port_name ."</option>\n";
	}
?>
	 </select>
        </td>
       </tr>
      </table>
     </td>
     <td>Match on specific ports. Be aware that this will only work for TCP/UDP protocols!</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;Match protocol flags
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      TOS flags:
     </td>
     <td>
      <select name="filter_tos">
       <option value="-1"   <? if($filter->filter_tos == "-1")   print "selected=\"selected\"";?>>Ignore</option>
       <option value="0x10" <? if($filter->filter_tos == "0x10") print "selected=\"selected\"";?>>Minimize-Delay 16 (0x10)</option>
       <option value="0x08" <? if($filter->filter_tos == "0x08") print "selected=\"selected\"";?>>Maximize-Throughput 8 (0x08)</option>
       <option value="0x04" <? if($filter->filter_tos == "0x04") print "selected=\"selected\"";?>>Maximize-Reliability 4 (0x04)</option>
       <option value="0x02" <? if($filter->filter_tos == "0x02") print "selected=\"selected\"";?>>Minimize-Cost 2 (0x02)</option>
       <option value="0x00" <? if($filter->filter_tos == "0x00") print "selected=\"selected\"";?>>Normal-Service 0 (0x00)</option>
      </select>
     </td>
     <td>
      Match a specific TOS flag.
     </td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;Match targets
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      Target:
     </td>
     <td>
      <table class="noborder">
       <tr>
        <td>Source</td>
        <td>&nbsp;</td>
        <td style="text-align: right;">Destination</td>
       </tr>
       <tr>
        <td>
         <select name="filter_src_target">
          <option value="0">any</option>
<?
	       $result = $this->db->db_query("SELECT target_idx, target_name FROM shaper_targets ORDER BY target_name");
	       while($row = $result->fetchRow()) {
		  print "<option value=\"". $row->target_idx ."\" ";
		  if($filter->filter_src_target == $row->target_idx)
		     print " selected=\"selected\"";
		  print ">". $row->target_name ."</option>\n";
	       }
?>
         </select>
	</td>
	<td>
	 <select name="filter_direction">
	  <option value="1" <? if($filter->filter_direction == 1) print "selected=\"selected\""; ?>>--&gt;</option>
	  <option value="2" <? if($filter->filter_direction == 2) print "selected=\"selected\""; ?>>&lt;-&gt;</option>
	 </select>
	</td>
	<td>
	 <select name="filter_dst_target">
	  <option value="0">any</option>
<?
	       $result = $this->db->db_query("SELECT target_idx, target_name FROM shaper_targets ORDER BY target_name");
	       while($row = $result->fetchRow()) {
		  print "<option value=\"". $row->target_idx ."\" ";
		  if($filter->filter_dst_target == $row->target_idx)
		     print "selected=\"selected\"";
		  print ">". $row->target_name ."</option>\n";
	       }
?>
         </select>
        </td>
       </tr>
      </table>
     </td>
     <td>
      Match a source and destination targets.
     </td>
    </tr>
<?
               if($this->parent->getOption("filter") == "ipt") {
?>
    <tr>
     <td style="white-space: nowrap;">
      TCP flags:
     </td>
     <td>
      <table class="noborder">
       <tr>
        <td><input type="checkbox" name="filter_tcpflag_syn" value="Y" <? if($filter->filter_tcpflag_syn =="Y") print "checked=\"checked\""; ?> />SYN</td>
	<td><input type="checkbox" name="filter_tcpflag_ack" value="Y" <? if($filter->filter_tcpflag_ack =="Y") print "checked=\"checked\""; ?> />ACK</td>
        <td><input type="checkbox" name="filter_tcpflag_fin" value="Y" <? if($filter->filter_tcpflag_fin =="Y") print "checked=\"checked\""; ?> />FIN</td>
       </tr>
       <tr>
        <td><input type="checkbox" name="filter_tcpflag_rst" value="Y" <? if($filter->filter_tcpflag_rst =="Y") print "checked=\"checked\""; ?> />RST</td>
        <td><input type="checkbox" name="filter_tcpflag_urg" value="Y" <? if($filter->filter_tcpflag_urg =="Y") print "checked=\"checked\""; ?> />URG</td>
        <td><input type="checkbox" name="filter_tcpflag_psh" value="Y" <? if($filter->filter_tcpflag_psh =="Y") print "checked=\"checked\""; ?> />PSH</td>
       </tr>
      </table>
     </td>
     <td>
      Match on specific TCP flags combinations.
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      Packet length:
     </td>
     <td>
      <input type="text" name="filter_packet_length" size="30" value="<? print $filter->filter_packet_length; ?>" />
     </td>
     <td>
      Match a packet against a defined size. Enter a size "64" or a range "64:128".
     </td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;Other matches
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      IPP2P:
     </td>
     <td>
      <table class="noborder">
       <tr>
        <td><input type="checkbox" name="filter_p2p_edk" value="Y" <? if($filter->filter_p2p_edk == "Y") print "checked=\"checked\""; ?> />Edonkey</td>
	<td><input type="checkbox" name="filter_p2p_kazaa" value="Y" <? if($filter->filter_p2p_kazaa == "Y") print "checked=\"checked\""; ?> />Kazaa</td>
        <td><input type="checkbox" name="filter_p2p_dc" value="Y" <? if($filter->filter_p2p_dc == "Y") print "checked=\"checked\""; ?> />Direct Connect (DC)</td>
       </tr>
       <tr>
	<td><input type="checkbox" name="filter_p2p_gnu" value="Y" <? if($filter->filter_p2p_gnu == "Y") print "checked=\"checked\""; ?> />Gnutella</td>
	<td><input type="checkbox" name="filter_p2p_bit" value="Y" <? if($filter->filter_p2p_bit == "Y") print "checked=\"checked\""; ?> />Bittorent</td>
	<td><input type="checkbox" name="filter_p2p_apple" value="Y" <? if($filter->filter_p2p_apple == "Y") print "checked=\"checked\""; ?> />AppleJuice</td>
       </tr>
       <tr>
        <td><input type="checkbox" name="filter_p2p_soul" value="Y" <? if($filter->filter_p2p_soul == "Y") print "checked=\"checked\""; ?> />SoulSeek</td>
	<td><input type="checkbox" name="filter_p2p_winmx" value="Y" <? if($filter->filter_p2p_winmx == "Y") print "checked=\"checked\""; ?> />WinMX</td>
        <td><input type="checkbox" name="filter_p2p_ares" value="Y" <? if($filter->filter_p2p_ares == "Y") print "checked=\"checked\""; ?> />Ares</td>
       </tr>
      </table>
     </td>
     <td>
      Match on specific filesharing protocols. This uses the ipp2p iptables module.
      It has to be available on your iptables installation. Refer 
      <a href="http://www.ipp2p.org" onclick="window.open('http://www.ipp2p.org'); return false;">www.ipp2p.org</a> for more
      informations.
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">
      layer7:
     </td>
     <td>
      <table class="noborder">
       <tr>
        <td>
	 <select size="10" name="filter_l7_avail[]" multiple="multiple">
	  <option value="">********* Unused *********</option>
<?
	       $l7protos = $this->db->db_query("SELECT l7proto_idx, l7proto_name FROM shaper_l7_protocols "
	                                   ."ORDER BY l7proto_name ASC");
	       while($l7proto = $l7protos->fetchRow()) {

	          if(!$this->db->db_fetchSingleRow("SELECT afl7_l7proto_idx FROM shaper_assign_l7_protocols "
		                                  ."WHERE afl7_filter_idx='". $_GET['idx'] ."' AND "
						  ."afl7_l7proto_idx='". $l7proto->l7proto_idx ."'")) {
		     print "<option value=\"". $l7proto->l7proto_idx ."\">". $l7proto->l7proto_name ."</option>\n";
		  }
	       }
?>
         </select>
	</td>
	<td>&nbsp;</td>
        <td>
	 <input type="button" value="&gt;&gt;" onclick="moveOptions(document.forms['filters'].elements['filter_l7_avail[]'], document.forms['filters'].elements['filter_l7_used[]']);"/><br />
         <input type="button" value="&lt;&lt;" onclick="moveOptions(document.forms['filters'].elements['filter_l7_used[]'], document.forms['filters'].elements['filter_l7_avail[]']);"/>
	</td>
	<td>&nbsp;</td>
	<td>
	 <select size="10" name="filter_l7_used[]" multiple="multiple">
	  <option value="">********* Used *********</option>
<?
	       $l7protos = $this->db->db_query("SELECT l7proto_idx, l7proto_name FROM shaper_l7_protocols "
	                                   ."ORDER BY l7proto_name ASC");
	       while($l7proto = $l7protos->fetchRow()) {

	          if($this->db->db_fetchSingleRow("SELECT afl7_l7proto_idx FROM shaper_assign_l7_protocols "
		                                 ."WHERE afl7_filter_idx='". $_GET['idx'] ."' AND "
						 ."afl7_l7proto_idx='". $l7proto->l7proto_idx ."'")) {
		     print "<option value=\"". $l7proto->l7proto_idx ."\">". $l7proto->l7proto_name ."</option>\n";
		  }
	       }
?>
	 </select>
        </td>
       </tr>
      </table>
     </td>
     <td>
      Match on specific protocols. This uses the layer7 iptables module.
      It has to be available on your iptables installation. Refer
      <a http="http://l7-filter.sourceforge.net" onclick="window.open('http://l7-filter.sourceforge.net'); return false;">l7-filter.sf.net</a> for more
      informations. <br />
      <br />
      Use Other-&gt;Update L7 Protocols to load current available l7 pat files.
     </td>
    </tr>
<?
       /* recalculate from timestamps */
       $filter_time_start_year   = date("Y", $filter->filter_time_start);
       $filter_time_start_month  = date("n", $filter->filter_time_start);
       $filter_time_start_day    = date("d", $filter->filter_time_start);
       $filter_time_start_hour   = date("H", $filter->filter_time_start);
       $filter_time_start_minute = date("i", $filter->filter_time_start);
       $filter_time_stop_year    = date("Y", $filter->filter_time_stop);
       $filter_time_stop_month   = date("n", $filter->filter_time_stop);
       $filter_time_stop_day     = date("d", $filter->filter_time_stop);
       $filter_time_stop_hour    = date("H", $filter->filter_time_stop);
       $filter_time_stop_minute  = date("i", $filter->filter_time_stop);
?>
    <tr>
     <td style="white-space: nowrap;">
      Time:
     </td>
     <td>
      <table class="noborder">
       <tr>
        <td colspan="2">
	 <input type="checkbox" name="filter_time_use_range" value="Y" <? if($filter->filter_time_use_range == "Y") print "checked=\"checked\""; ?> />Use time range:
	</td>
       </tr>
       <tr>
        <td colspan="2">&nbsp;</td>
       </tr>
       <tr>
        <td>
	 Start:
	</td>
	<td>
         <select name="filter_time_start_year">
          <? print $this->parent->getYearList($filter_time_start_year); ?>
         </select>
         -
         <select name="filter_time_start_month">
          <? print $this->parent->getMonthList($filter_time_start_month); ?>
         </select>
         -
         <select name="filter_time_start_day">
          <? print $this->parent->getDayList($filter_time_start_day); ?>
         </select>
         &nbsp;
         <select name="filter_time_start_hour">
          <? print $this->parent->getHourList($filter_time_start_hour); ?>
         </select>
         :
         <select name="filter_time_start_minute">
          <? print $this->parent->getMinuteList($filter_time_start_minute); ?>
         </select>
	</td>
       </tr>
       <tr>
        <td>
         Stop:
	</td>
        <td>
         <select name="filter_time_stop_year">
          <? print $this->parent->getYearList($filter_time_stop_year); ?>
         </select>
         -
         <select name="filter_time_stop_month">
          <? print $this->parent->getMonthList($filter_time_stop_month); ?>
         </select>
         -
         <select name="filter_time_stop_day">
          <? print $this->parent->getDayList($filter_time_stop_day); ?>
         </select>
         &nbsp;
         <select name="filter_time_stop_hour">
          <? print $this->parent->getHourList($filter_time_stop_hour); ?>
         </select>
         :
         <select name="filter_time_stop_minute">
          <? print $this->parent->getMinuteList($filter_time_stop_minute); ?>
         </select>
	</td>
       </tr>
       <tr>
        <td colspan="2">&nbsp;</td>
       </tr>
       <tr>
        <td>
	 Days:
	</td>
	<td>
         <input type="checkbox" name="filter_time_day_mon" value="Y" <? if($filter->filter_time_day_mon == "Y") print "checked=\"checked\"";?> />Mon
         <input type="checkbox" name="filter_time_day_tue" value="Y" <? if($filter->filter_time_day_tue == "Y") print "checked=\"checked\"";?> />Tue
         <input type="checkbox" name="filter_time_day_wed" value="Y" <? if($filter->filter_time_day_wed == "Y") print "checked=\"checked\"";?> />Wed
         <input type="checkbox" name="filter_time_day_thu" value="Y" <? if($filter->filter_time_day_thu == "Y") print "checked=\"checked\"";?> />Thu
         <input type="checkbox" name="filter_time_day_fri" value="Y" <? if($filter->filter_time_day_fri == "Y") print "checked=\"checked\"";?> />Fri
         <input type="checkbox" name="filter_time_day_sat" value="Y" <? if($filter->filter_time_day_sat == "Y") print "checked=\"checked\"";?> />Sat
         <input type="checkbox" name="filter_time_day_sun" value="Y" <? if($filter->filter_time_day_sun == "Y") print "checked=\"checked\"";?> />Sun
	</td>
       </tr>
      </table>
     </td>
     <td>
      Match if the packet is within a defined timerange. Nice for file transfer operations,
      which you want to limit during the day, but have full bandwidth in the night for backup.
      This uses the time iptables match which has to be available on your iptables installation
      and supported by your running kernel.
     </td>
    </tr>
    <tr>
     <td>
      FTP data:
     </td>
     <td>
      <input type="checkbox" name="filter_match_ftp_data" value="Y" <? if($filter->filter_match_ftp_data == "Y") print "checked=\"checked\""; ?> />Match FTP data channel
     </td>
     <td>
      A FTP file transfer needs two connections: command channel (21/tcp) and a data channel. If you use active FTP the port for data channel is 20/tcp. If you use passive FTP, the port of the data channel is not predictable and is choosen by the ftp server (high port). But with the help of the iptables kernel module ip_conntrack_ftp you get the data channel which belongs to the command channel! Don't forget to load the ip_conntrack_ftp module!
     </td>
    </tr>
<?
               }
?>
    <tr>
     <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
     <td style="text-align: center;"><a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>" title="Back"><img src="<? print ICON_ARROW_LEFT; ?>" alt="arrow left icon" /></a></td>
     <td><input type="submit" value="Save" onclick="selectAll(document.forms['filters'].elements['used[]']); selectAll(document.forms['filters'].elements['filter_l7_used[]']);" /></td>
     <td>Save settings.</td>
    </tr>
   </table> 
  </form>
<?
               $this->parent->closeTable();
	    }
	    else {

	       /* Name provided? */
	       if($_POST['filter_name'] == "") {
		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Modify Filter", "Please enter a filter name!");
		  $error = true;
               }

               /* Filter with such a name already exists? */
	       if(!isset($error) && $this->db->db_fetchSingleRow("SELECT filter_idx FROM shaper_filters WHERE "
					       ."filter_name like '". $_POST['filter_name'] ."'") 
			&& $_POST['filter_name'] != $_GET['namebefore']) {
		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Modify Filter", "A filter with the name ". $_POST['filter_name'] ." already exists. Please choose another name!");
		  $error = true;

               }

               /* A useful filter? */
               if(!isset($error) && $_POST['filter_protocol_id'] == -1 && count($_POST['used']) <= 1 && $_POST['filter_tos'] == -1 &&
	          !$_POST['filter_tcpflag_syn'] && !$_POST['filter_tcpflag_ack'] && !$_POST['filter_tcpflag_fin'] &&
		  !$_POST['filter_tcpflag_rst'] && !$_POST['filter_tcpflag_urg'] && !$_POST['filter_tcpflag_psh'] &&
		  !$_POST['filter_packet_length'] && !$_POST['filter_p2p_edk'] && !$_POST['filter_p2p_kazaa'] &&
		  !$_POST['filter_p2p_dc'] && !$_POST['filter_p2p_gnu'] && !$_POST['filter_p2p_bit'] &&
		  !$_POST['filter_p2p_apple'] && !$_POST['filter_p2p_soul'] && !$_POST['filter_p2p_winmx'] &&
		  !$_POST['filter_p2p_ares'] && !$_POST['filter_time_use_range'] && !$_POST['filter_time_day_mon'] &&
		  !$_POST['filter_time_day_tue'] && !$_POST['filter_time_day_wed'] && !$_POST['filter_time_day_thu'] &&
		  !$_POST['flter_time_day_fri'] && !$_POST['filter_time_day_sat'] && !$_POST['filter_time_day_sun'] &&
		  count($_POST['filter_l7_used']) <= 1 && $_POST['filter_src_target'] == 0 && $_POST['filter_dst_target'] == 0) {

		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Modify Filter", "This filter has nothing to do. Please select at least one match!");
		  $error = true;
               }

	       /* Ports can only be used with TCP, UDP or IP protocol */
	       if(!isset($error) && count($_POST['used']) > 1 && ($this->parent->getProtocolNumberById($_POST['filter_protocol_id']) != 4 &&
		  $this->parent->getProtocolNumberById($_POST['filter_protocol_id']) != 17 &&
		  $this->parent->getProtocolNumberById($_POST['filter_protocol_id']) != 6)) {

		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Modify Filter", "Ports can only be used in combination with IP, TCP or UDP protocol!");

		  $error = true;
	       }

	       /* TCP-flags can only be used with TCP protocol */
	       if(!isset($error) && ($_POST['filter_tcpflag_syn'] || $_POST['filter_tcpflag_ack'] || $_POST['filter_tcpflag_fin'] ||
		      $_POST['filter_tcpflag_rst'] || $_POST['filter_tcpflag_urg'] || $_POST['filter_tcpflag_psh']) &&
		      $this->parent->getProtocolNumberById($_POST['filter_protocol_id']) != 6) {

		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Modify Filter", "TCP-Flags can only be used in combination with TCP protocol!");

		  $error = true;
	       }

	       /* ipp2p can only be used with no ports, no l7 filters and tcp &| udp protocol */
	       if(!isset($error) && ($_POST['filter_p2p_edk'] || $_POST['filter_p2p_kazaa'] || $_POST['filter_p2p_dc'] ||
		  $_POST['filter_p2p_gnu'] || $_POST['filter_p2p_bit'] || $_POST['filter_p2p_apple'] ||
		  $_POST['filter_p2p_soul'] || $_POST['filter_p2p_winmx'] || $_POST['filter_p2p_ares']) &&
		  (count($_POST['used']) > 1 || (($this->parent->getProtocolNumberById($_POST['filter_protocol_id']) != 17 &&
		  $this->parent->getProtocolNumberById($_POST['filter_protocol_id']) != 6) &&
		  $_POST['filter_protocol_id'] != -1) || count($_POST['filter_l7_used']) > 1)) {

		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Modify Filter", "IPP2P match can only be used with no ports select and only with protocols TCP or UDP or completly ignoring protocols!<br />Also IPP2P can not be used in combination with layer7 filters.");

		  $error = true;

	       }

	       /* layer7 protocol match can only be used with no ports and no tcp &| udp protocols */
	       if(!isset($error) && count($_POST['filter_l7_used']) > 1 && $_POST['filter_protocol_id'] != -1) {

		  $this->parent->printError("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Modify Filter", "Layer7 match can only be used with no ports select and no protocol definitions!");
		  
		  $error = true;
               }

	       if(!isset($error)) {

		  $start_time = strtotime(sprintf("%04d-%02d-%02d %02d:%02d:00", 
					  $_POST['filter_time_start_year'],
					  $_POST['filter_time_start_month'],
					  $_POST['filter_time_start_day'],
					  $_POST['filter_time_start_hour'], 
					  $_POST['filter_time_start_minute']));
		  $stop_time = strtotime(sprintf("%04d-%02d-%02d %02d:%02d:00",
					  $_POST['filter_time_stop_year'],
					  $_POST['filter_time_stop_month'],
					  $_POST['filter_time_stop_day'],
					  $_POST['filter_time_stop_hour'],
					  $_POST['filter_time_stop_minute']));

		  switch($this->parent->getOption("filter")) {

		     case 'ipt':
			$this->db->db_query("UPDATE shaper_filters SET filter_name='". $_POST['filter_name'] ."', "
					   ."filter_protocol_id='". $_POST['filter_protocol_id'] ."', "
					   ."filter_tos='". $_POST['filter_tos'] ."', "
					   ."filter_tcpflag_syn='". $_POST['filter_tcpflag_syn'] ."', "
					   ."filter_tcpflag_ack='". $_POST['filter_tcpflag_ack'] ."', "
					   ."filter_tcpflag_fin='". $_POST['filter_tcpflag_fin'] ."', "
					   ."filter_tcpflag_rst='". $_POST['filter_tcpflag_rst'] ."', "
					   ."filter_tcpflag_urg='". $_POST['filter_tcpflag_urg'] ."', "
					   ."filter_tcpflag_psh='". $_POST['filter_tcpflag_psh'] ."', "
					   ."filter_packet_length='". $_POST['filter_packet_length'] ."', "
					   ."filter_p2p_edk='". $_POST['filter_p2p_edk'] ."', "
					   ."filter_p2p_kazaa='". $_POST['filter_p2p_kazaa'] ."', "
					   ."filter_p2p_dc='". $_POST['filter_p2p_dc'] ."', "
					   ."filter_p2p_gnu='". $_POST['filter_p2p_gnu'] ."', "
					   ."filter_p2p_bit='". $_POST['filter_p2p_bit'] ."', "
					   ."filter_p2p_apple='". $_POST['filter_p2p_apple'] ."', "
					   ."filter_p2p_soul='". $_POST['filter_p2p_soul'] ."', "
					   ."filter_p2p_winmx='". $_POST['filter_p2p_winmx'] ."', "
					   ."filter_p2p_ares='". $_POST['filter_p2p_ares'] ."', "
					   ."filter_time_use_range='". $_POST['filter_time_use_range'] ."', "
					   ."filter_time_start='". $start_time ."', "
					   ."filter_time_stop='". $stop_time ."', "
					   ."filter_time_day_mon='". $_POST['filter_time_day_mon'] ."', "
					   ."filter_time_day_tue='". $_POST['filter_time_day_tue'] ."', "
					   ."filter_time_day_wed='". $_POST['filter_time_day_wed'] ."', "
					   ."filter_time_day_thu='". $_POST['filter_time_day_thu'] ."', "
					   ."filter_time_day_fri='". $_POST['filter_time_day_fri'] ."', "
					   ."filter_time_day_sat='". $_POST['filter_time_day_sat'] ."', "
					   ."filter_time_day_sun='". $_POST['filter_time_day_sun'] ."', "
					   ."filter_match_ftp_data='". $_POST['filter_match_ftp_data'] ."', "
					   ."filter_src_target='". $_POST['filter_src_target'] ."', "
					   ."filter_dst_target='". $_POST['filter_dst_target'] ."', "
					   ."filter_direction='". $_POST['filter_direction'] ."', "
					   ."filter_active='". $_POST['filter_active'] ."' "
					   ."WHERE filter_idx='". $_GET['idx'] ."'");
			break;

		     case 'tc':
			$this->db->db_query("UPDATE shaper_filters SET filter_name='". $_POST['filter_name'] ."', "
					   ."filter_protocol_id='". $_POST['filter_protocol_id'] ."', "
					   ."filter_tos='". $_POST['filter_tos'] ."', "
					   ."filter_src_target='". $_POST['filter_src_target'] ."', "
					   ."filter_dst_target='". $_POST['filter_dst_target'] ."', "
					   ."filter_direction='". $_POST['filter_direction'] ."', "
					   ."filter_active='". $_POST['filter_active'] ."' "
					   ."WHERE filter_idx='". $_GET['idx'] ."'");
			break;
		  }

		  if($_POST['used']) {
		     $this->db->db_query("DELETE FROM shaper_assign_ports WHERE afp_filter_idx='". $_GET['idx'] ."'");
		     foreach($_POST['used'] as $use) 
			if($use != "")
			   $this->db->db_query("INSERT INTO shaper_assign_ports (afp_filter_idx, afp_port_idx) "
					      ."VALUES ('". $_GET['idx'] ."', '". $use ."')");
		  }

		  if($_POST['filter_l7_used']) {
		     $this->db->db_query("DELETE FROM shaper_assign_l7_protocols WHERE afl7_filter_idx='". $_GET['idx'] ."'");
		     foreach($_POST['filter_l7_used'] as $use)
		        if($use != "")
			   $this->db->db_query("INSERT INTO shaper_assign_l7_protocols (afl7_filter_idx, afl7_l7proto_idx) "
			                      ."VALUES ('". $_GET['idx'] ."', '". $use ."')");
                  }
		  $this->parent->goBack();
	       }
	    }
	    break;

	 case 3:

	    if(!isset($_GET['doit']))
	       $this->parent->printYesNo("<img src=\"". ICON_FILTERS ."\" alt=\"filter icon\" />&nbsp;Delete Filter", "Delete Filter ". $_GET['name'] ."?");
	    else {
	       if($_GET['idx']) {
		  $this->db->db_query("DELETE FROM shaper_filters WHERE filter_idx='". $_GET['idx'] ."'");
                  $this->db->db_query("DELETE FROM shaper_assign_ports WHERE afp_filter_idx='". $_GET['idx'] ."'");
                  $this->db->db_query("DELETE FROM shaper_assign_l7_protocols WHERE afl7_filter_idx='". $_GET['idx'] ."'");
		  $this->db->db_query("DELETE FROM shaper_assign_filters WHERE apf_filter_idx='". $_GET['idx'] ."'");
               }
	       $this->parent->goBack();
	    }
	    break;

	 case 4:

	    if(isset($_GET['idx'])) {
	       
	       if($_GET['to'] == 0) 
	          $this->db->db_query("UPDATE shaper_filters SET filter_active='N' WHERE filter_idx='". $_GET['idx'] ."'");
	       elseif($_GET['to'] == 1) 
	          $this->db->db_query("UPDATE shaper_filters SET filter_active='Y' WHERE filter_idx='". $_GET['idx'] ."'");
	    }
	    $this->parent->goBack();
	    break;

      }

   } // showHtml()

}

?>
