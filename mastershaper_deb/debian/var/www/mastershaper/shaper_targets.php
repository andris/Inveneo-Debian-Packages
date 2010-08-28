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

class MSTARGETS {

   var $db;
   var $parent;

   /* Class constructor */
   function MSTARGETS($parent)
   {
      $this->db = $parent->db;
      $this->parent = $parent;
   } // MSTARGETS()

   /* interface output */
   function showHtml()
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_manage_targets")) {

	 $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Manage Targets", "You do not have enough permissions to access this module!");
	 return 0;

      }

      if(!isset($this->parent->screen))
         $this->parent->screen = 0;

      switch($this->parent->screen) {

	 default:
	 case 0:

	    $this->parent->startTable("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Manage Targets");
?>
  <table style="width: 100%;" class="withborder">
   <tr>
<?
	    if(isset($_GET['saved'])) {
?>
    <td colspan="3" style="text-align: center;" class="sysmessage">You have made changes to the ruleset. Don't forget to reload them.</td>
<?
	    } else {
?>
    <td colspan="3">&nbsp;</td>
<?
	    }
?>
   </tr>
   <tr>
    <td colspan="3" style="text-align: center;">
     <img src="<? print ICON_NEW; ?>" alt="new icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>&amp;screen=1">
      Create a new Target
     </a>
    </td>
   </tr>
   <tr>
    <td colspan="3">&nbsp;</td>
   </tr>
   <tr>
    <td><img src="<? print ICON_TARGETS; ?>" alt="target icon" />&nbsp;<i>Targets</i></td>
    <td><img src="<? print ICON_TARGETS; ?>" alt="target icon" />&nbsp;<i>Details</i></td>
    <td style="text-align: center;"><i>Options</i></td>
   </tr>
<?

	    $result = $this->db->db_query("SELECT target_idx, target_name, target_match FROM shaper_targets ORDER BY target_name ASC");

	    while($row = $result->fetchrow()) {
?>
   <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
    <td>
     <img src="<? print ICON_TARGETS; ?>" alt="target icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=2&amp;idx=". $row->target_idx; ?>">
      <? print $row->target_name; ?>
     </a>
    </td>
    <td>
     <img src="<? print ICON_TARGETS; ?>" alt="target icon" />
<?
	       switch($row->target_match) {

		  case 'IP':
		     print "IP match";
		     break;
		  case 'MAC':
		     print "MAC match";
		     break;
		  case 'GROUP':
		     print "Target Group";
		     break;
	       }
?>
    </td>
    <td style="text-align: center;">
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=3&amp;idx=". $row->target_idx ."&amp;name=". urlencode($row->target_name); ?>" title="Delete"><img src="<? print ICON_DELETE; ?>" alt="delete icon" /></a>
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

	       $this->parent->startTable("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Create a new Target");
?>
  <form action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen; ?>&amp;saveit=1" method="post" id="targets">
   <table style="width: 100%;" class="withborder2">
    <tr>
     <td colspan="3">
      <img src="<? print ICON_TARGETS; ?>" alt="target icon" />&nbsp;General
     </td>
    </tr>
    <tr>
     <td>Name:</td>
     <td><input type="text" name="target_name" size="30" /></td>
     <td>Name of the target.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_TARGETS; ?>" alt="target icon" />&nbsp;Parameters
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Match:</td>
     <td>
      <table class="noborder">
       <tr>
        <td style="white-space: nowrap;">
	 <input type="radio" name="target_match" value="IP" checked="checked" />IP
	</td>
	<td>&nbsp;</td>
	<td>
	 <input type="text" name="target_ip" size="30" />
	</td>
       </tr>
       <tr>
        <td style="white-space: nowrap;">
	 <input type="radio" name="target_match" value="MAC" />MAC
	</td>
	<td>&nbsp;</td>
	<td>
	 <input type="text" name="target_mac" size="30" />
	</td>
       </tr>
       <tr>
        <td style="white-space: nowrap;">
	 <input type="radio" name="target_match" value="GROUP" />Group
	</td>
	<td>&nbsp;</td>
	<td>
	 <table>
	  <tr>
	   <td>
	    <select name="avail[]" size="5" multiple="multiple">
	     <option value="">********* Unused *********</option>
<?
	       $result = $this->db->db_query("SELECT target_idx, target_name FROM shaper_targets "
		                               ."WHERE target_match<>'GROUP' ORDER BY target_name ASC");
	       while($row = $result->fetchRow()) {
?>
             <option value="<? print $row->target_idx; ?>"><? print $row->target_name; ?></option>
<?
	       }
?>	
            </select>
	   </td>
	   <td>&nbsp;</td>
	   <td>
            <input type="button" value="&gt;&gt;" onclick="moveOptions(document.forms['targets'].elements['avail[]'], document.forms['targets'].elements['used[]']);" /><br />
            <input type="button" value="&lt;&lt;" onclick="moveOptions(document.forms['targets'].elements['used[]'], document.forms['targets'].elements['avail[]']);" />
           </td>
	   <td>&nbsp;</td>
	   <td>
	    <select name="used[]" size="5" multiple="multiple">
	     <option value="">********* Used *********</option>
	    </select>
	   </td>
	  </tr>
	 </table>
	</td>
       </tr>
      </table>
     </td>
     <td>
      Specify the target matchting method.<br />
      <br />
      IP: Enter a host (1.1.1.1), host list (1.1.1.1-1.1.1.254) or a network address (1.1.1.0/24).<br />
      <br />
      MAC: Specify the MAC address in format 00:00:00:00:00:00 or 00-00-00-00-00-00.<br />
      <br />
      Group: Group already defined targets as groups together. Group in group is not supported.<br />
      <br />
      <b>Be aware, that MAC match can NOT be used in combination with tc-filter.</b>
     </td>
    </tr>
    <tr>
     <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
     <td style="text-align: center;"><a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>" title="Back"><img src="<? print ICON_ARROW_LEFT; ?>" alt="arrow left icon" /></a></td>
     <td><input type="submit" value="Save" onclick="selectAll(document.forms['targets'].elements['used[]']);" /></td>
     <td>Save settings.</td>
    </tr>
   </table> 
  </form>
<?
               $this->parent->closeTable();
	    }
	    else {

	       if($_POST['target_name'] == "") {

		  $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Create a new Target", "Please enter a name for this target!");
		  $found_error = 1;
	       }

	       if(!$found_error && $this->db->db_fetchSingleRow("SELECT target_idx FROM shaper_targets WHERE target_name like '". $_POST['target_name'] ."'")) {
		  $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Create a new Target", "A target with the name ". $_POST['target_name'] ." already exists. Please choose another name!");
		  $found_error = 1;

	       }

	       if(!$found_error && $_POST['target_match'] == "IP" && $_POST['target_ip'] == "") {

		  $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Create a new Target", "You have selected IP match but didn't entered a IP address!");
		  $found_error = 1;

	       }
	       elseif(!$found_error && $_POST['target_match'] == "IP" && $_POST['target_ip'] != "") {
		  
		  /* Is target_ip a ip range seperated by "-" */
		  if(strstr($_POST['target_ip'], "-") !== false) {

		     $hosts = split("-", $_POST['target_ip']);

		     foreach($hosts as $host) {

			$ipv4 = new Net_IPv4;

			if(!$found_error && !$ipv4->validateIP($host)) {

			   $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Create a new Target", "Incorrect IP address (". $host .") in IP range definition!");
			   $found_error = 1;
			}
		     }
		  }
		  /* Is target_ip a network */
		  elseif(strstr($_POST['target_ip'], "/") !== false) {

		  $ipv4 = new Net_IPv4;
		     $net = $ipv4->parseAddress($_POST['target_ip']);

		     if($net->netmask == "" || $net->netmask == "0.0.0.0") {

			$this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Create a new Target", "Incorrect CIDR address (". $_POST['target_ip'] .")!");
			$found_error = 1;
		     }
		  }
		  /* target_ip is a simple IP */
		  else {

		     $ipv4 = new Net_IPv4;

		     if(!$ipv4->validateIP($_POST['target_ip'])) {

			$this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Create a new Target", "Incorrect IP address (". $_POST['target_ip'] .")!");
			$found_error = 1;
		     }
		  }
	       }

	       /* MAC address specified? */
	       if(!$found_error && $_POST['target_match'] == "MAC" && $_POST['target_mac'] == "") {

		  $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Create a new Target", "You have selected MAC match but didn't entered a MAC address!");
		  $found_error = 1;

	       }
	       elseif(!$found_error && $_POST['target_match'] == "MAC" && $_POST['target_mac'] != "") {

		  if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $_POST['target_mac']) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $_POST['target_mac'])) {
		     $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Create a new Target", "You have selected MAC match but didn't specified a VALID MAC address (". $_POST['target_mac'] .")!");
		     $found_error = 1;
		  }
	       }

	       if(!$found_error && $_POST['target_match'] == "GROUP" && count($_POST['used']) <= 1) {

		  $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Create a new Target", "You have selected Group match but didn't selected at least one target from the list!");
		  $found_error = 1;

	       }
		  
	       if(!$found_error) {

		  $this->db->db_query("INSERT INTO shaper_targets (target_name, target_match, target_ip, target_mac) "
				     ."VALUES ('". $_POST['target_name'] ."', '". $_POST['target_match'] ."', "
				     ."'". $_POST['target_ip'] ."', '". $_POST['target_mac'] ."')");

		  if($_POST['used']) {

		     $idx = $this->db->db_getid();

		     foreach($_POST['used'] as $use) {

			if($use != "") {

			   $this->db->db_query("INSERT INTO shaper_assign_target_groups (atg_group_idx, atg_target_idx) "
					      ."VALUES ('". $idx ."', '". $use ."')");

			}

		     }

		  }

		  $this->parent->goBack();

	       }
	    }
	    break;

	 case 2:

	    if(!isset($_GET['saveit'])) {

	       $target = $this->db->db_fetchSingleRow("SELECT target_name, target_match, target_ip, target_mac FROM "
		                                     ."shaper_targets WHERE target_idx='". $_GET['idx'] ."'");

               $this->parent->startTable("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Modify Target ". $target->target_name);

?>
  <form action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;idx=". $_GET['idx'] ."&amp;namebefore=". urlencode($target->target_name); ?>&amp;saveit=1" method="post" id="targets">
   <table style="width: 100%;" class="withborder2"> 
    <tr>
     <td colspan="3">
      <img src="<? print ICON_TARGETS; ?>" alt="target icon" />&nbsp;General
     </td>
    </tr>
    <tr>
     <td>Name:</td>
     <td><input type="text" name="target_name" size="30" value="<? print $target->target_name; ?>" /></td>
     <td>Name of the target.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_TARGETS; ?>" alt="target icon" />&nbsp;Parameters
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Match:</td>
     <td>
      <table class="noborder">
       <tr>
        <td style="white-space: nowrap;">
	 <input type="radio" name="target_match" value="IP" <? if($target->target_match == "IP") print "checked=\"checked\""; ?> />IP
	</td>
	<td>&nbsp;</td>
	<td>
	 <input type="text" name="target_ip" size="30" value="<? print $target->target_ip; ?>" />
	</td>
       </tr>
       <tr>
        <td style="white-space: nowrap;">
	 <input type="radio" name="target_match" value="MAC" <? if($target->target_match == "MAC") print  "checked=\"checked\""; ?> />MAC
	</td>
	<td>&nbsp;</td>
	<td>
	 <input type="text" name="target_mac" size="30" value="<? print $target->target_mac; ?>" />
	</td>
       </tr>
       <tr>
        <td style="white-space: nowrap;">
	 <input type="radio" name="target_match" value="GROUP" <? if($target->target_match == "GROUP") print "checked=\"checked\""; ?> />Group
	</td>
	<td>&nbsp;</td>
	<td>
	 <table>
	  <tr>
	   <td>
	    <select name="avail[]" size="5" multiple="multiple">
	     <option value="">********* Unused *********</option>
<?
	       $result = $this->db->db_query("SELECT target_idx, target_name FROM shaper_targets "
		                               ."WHERE target_match<>'GROUP' AND target_idx<>'". $_GET['idx'] ."' ORDER BY target_name ASC");
	       while($row = $result->fetchRow()) {
		  
		  if(!$this->db->db_fetchSingleRow("SELECT atg_idx FROM shaper_assign_target_groups WHERE "
						  ."atg_group_idx='". $_GET['idx'] ."' AND "
						  ."atg_target_idx='". $row->target_idx ."'")) {
?>
             <option value="<? print $row->target_idx; ?>"><? print $row->target_name; ?></option>
<?
		  }
	       }
?>	
            </select>
	   </td>
	   <td>&nbsp;</td>
	   <td>
            <input type="button" value="&gt;&gt;" onclick="moveOptions(document.forms['targets'].elements['avail[]'], document.forms['targets'].elements['used[]']);" /><br />
            <input type="button" value="&lt;&lt;" onclick="moveOptions(document.forms['targets'].elements['used[]'], document.forms['targets'].elements['avail[]']);" />
           </td>
	   <td>&nbsp;</td>
	   <td>
	    <select name="used[]" size="5" multiple="multiple">
	     <option value="">********* Used *********</option>
<?
	       $result = $this->db->db_query("SELECT target_idx, target_name FROM shaper_targets "
					    ."WHERE target_match<>'GROUP' AND target_idx<>'". $_GET['idx'] ."' ORDER BY target_name ASC");
	       while($row = $result->fetchRow()) {

		  if($this->db->db_fetchSingleRow("SELECT atg_idx FROM shaper_assign_target_groups WHERE "
						 ."atg_group_idx='". $_GET['idx'] ."' AND "
						 ."atg_target_idx='". $row->target_idx ."'")) {
?>
             <option value="<? print $row->target_idx; ?>"><? print $row->target_name; ?></option>
<?
		  }
	       }
?>
	    </select>
	   </td>
	  </tr>
	 </table>
	</td>
       </tr>
      </table>
     </td>
     <td>
      Specify the target matchting method.<br />
      <br />
      IP: Enter a host (1.1.1.1), host list (1.1.1.1-1.1.1.254) or a network address (1.1.1.0/24).<br />
      <br />
      MAC: Specify the MAC address in format 00:00:00:00:00:00 or 00-00-00-00-00-00.<br />
      <br />
      Group: Group already defined targets as groups together. Group in group is not supported.<br />
      <br />
      <b>Be aware, that MAC match can NOT be used in combination with tc-filter.</b>
     </td>
    </tr>
    <tr>
     <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
     <td style="text-align: center;"><a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>" title="Back"><img src="<? print ICON_ARROW_LEFT; ?>" alt="arrow left icon" /></a></td>
     <td><input type="submit" value="Save" onclick="selectAll(document.forms['targets'].elements['used[]']);" /></td>
     <td>Save settings.</td>
    </tr>
   </table> 
  </form>
<?

               $this->parent->closeTable();

	    }
	    else {

	       /* name provided? */
	       if($_POST['target_name'] == "") {

		  $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Modify Target", "Please enter a name for this target!");
		  $found_error = 1;

	       }

	       /* if name changed, already exist? */
	       if(!$found_error && $this->db->db_fetchSingleRow("SELECT target_idx FROM shaper_targets WHERE target_name like '". $_POST['target_name'] ."'") && $_POST['target_name'] != $_GET['namebefore']) {
		  
		  $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Modify Target", "A target with the name ". $_POST['target_name'] ." already exists. Please choose another name!");
		  $found_error = 1;

	       }
	       
	       if(!$found_error && $_POST['target_match'] == "IP" && $_POST['target_ip'] == "") {

		  $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Modify Target", "You have selected IP match but didn't entered a IP address!");
		  $found_error = 1;

	       }
	       elseif(!$found_error && $_POST['target_match'] == "IP" && $_POST['target_ip'] != "") {
	       
		  /* Is target_ip a ip range seperated by "-" */
		  if(strstr($_POST['target_ip'], "-") !== false) {

		     $hosts = split("-", $_POST['target_ip']);

		     foreach($hosts as $host) {

			$ipv4 = new Net_IPv4;

			if(!$found_error && !$ipv4->validateIP($host)) {

			   $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Modify Target", "Incorrect IP address (". $host .") in IP range definition!");
			   $found_error = 1;
			}
		     }
		  }
		  /* Is target_ip a network */
		  elseif(strstr($_POST['target_ip'], "/") !== false) {

		     $ipv4 = new Net_IPv4;
		     $net = $ipv4->parseAddress($_POST['target_ip']);

		     if($net->netmask == "" || $net->netmask == "0.0.0.0") {

			$this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Modify Target", "Incorrect CIDR address (". $_POST['target_ip'] .")!");
			$found_error = 1;
		     }
		  }
		  /* target_ip is a simple IP */
		  else {

		     $ipv4 = new Net_IPv4;

		     if(!$ipv4->validateIP($_POST['target_ip'])) {

			$this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Modify Target", "Incorrect IP address (". $_POST['target_ip'] .")!");
			$found_error = 1;
		     }
		  }
	       }

	       /* MAC address specified? */
	       if(!$found_error && $_POST['target_match'] == "MAC" && $_POST['target_mac'] == "") {

		  $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Modify Target", "You have selected MAC match but didn't entered a MAC address!");
		  $found_error = 1;

	       }
	       elseif(!$found_error && $_POST['target_match'] == "MAC" && $_POST['target_mac'] != "") {

		  if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $_POST['target_mac']) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $_POST['target_mac'])) {
		     $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Modify Target", "You have selected MAC match but didn't specified a VALID MAC address (". $_POST['target_mac'] .")!");
		     $found_error = 1;
		  }
	       }

	       if(!$found_error && $_POST['target_match'] == "GROUP" && count($_POST['used']) <= 1) {

		  $this->parent->printError("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Modify Target", "You have selected Group match but didn't selected at least one target from the list!");
		  $found_error = 1;

	       }

	       if(!$found_error) {

		  $this->db->db_query("UPDATE shaper_targets SET target_name='". $_POST['target_name'] ."', "
				     ."target_match='". $_POST['target_match'] ."', "
				     ."target_ip='". $_POST['target_ip'] ."', "
				     ."target_mac='". $_POST['target_mac'] ."' "
				     ."WHERE target_idx='". $_GET['idx'] ."'");

		  if($_POST['used']) {

		     $this->db->db_query("DELETE FROM shaper_assign_target_groups WHERE atg_group_idx='". $_GET['idx'] ."'");

		     foreach($_POST['used'] as $use) {

			if($use != "") {

			   $this->db->db_query("INSERT INTO shaper_assign_target_groups (atg_group_idx, atg_target_idx) "
					      ."VALUES ('". $_GET['idx'] ."', '". $use ."')");

			}

		     }
		  }

		  $this->parent->goBack();

	       }
	    }
	    break;

	 case 3:

	    if(!isset($_GET['doit']))
	       $this->parent->printYesNo("<img src=\"". ICON_TARGETS ."\" alt=\"target icon\" />&nbsp;Delete Target", "Do really want to delete target ". $_GET['name'] ."?");
	    else {

	       if(isset($_GET['idx'])) {

		  $this->db->db_query("DELETE FROM shaper_targets WHERE target_idx='". $_GET['idx'] ."'");
		  $this->db->db_query("DELETE FROM shaper_assign_target_groups WHERE atg_group_idx='". $_GET['idx'] ."'");
		  $this->db->db_query("DELETE FROM shaper_assign_target_groups WHERE atg_target_idx='". $_GET['idx'] ."'");
		  $this->parent->goBack();
	       }
	    }
	    break;
      }

   } // showHtml();

}

?>
