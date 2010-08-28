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

class MSCHAINS {

   var $db;
   var $parent;

   /* Class constructor */
   function MSCHAINS($parent)
   {
      $this->parent = $parent;
      $this->db = $parent->db;
   } // MSCHAIN()

   /* interface output */
   function showHtml()
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" && 
         !$this->parent->checkPermissions("user_manage_chains")) {

         $this->parent->printError("<img src=\"". ICON_CHAINS ."\" alt=\"chain icon\" />&nbsp;Manage Chains", "You do not have enough permissions to access this module!");
	 return 0;

      }
   
      if(!isset($this->parent->screen))
         $this->parent->screen = 0;

      switch($this->parent->screen) {

	 default:
	 case 0:
	    $this->parent->startTable("<img src=\"". ICON_CHAINS ."\" alt=\"chain icon\" />&nbsp;Manage Chains");
?>
     <table style="width: 100%;" class="withborder">
      <tr>
<?
	    if(isset($_GET['saved']) && $_GET['saved']) {
?>
       <td colspan="4" style="text-align: center;" class="sysmessage">You have made changes to the ruleset. Don't forget to reload them.</td>
<?
	    } else {
?>
       <td colspan="4">&nbsp;</td>
<?
	    }
?>
      </tr>
      <tr>
       <td colspan="4" style="text-align: center;">
        <img src="<? print ICON_NEW; ?>" alt="new icon" />
        <a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>&amp;screen=1&amp;new=1" title="Create a new Chain">
         Create a new Chain
        </a>
       </td>
      </tr>
      <tr>
       <td colspan="4">&nbsp;</td>
      </tr>
      <tr>
       <td><img src="<? print ICON_CHAINS; ?>" alt="chain icon" />&nbsp;<i>Chain-Name</i></td>
       <td><img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;<i>Service Level</i></td>
       <td><img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;<i>Fallback</i></td>
       <td style="text-align: center;"><i>Options</i></td>
      </tr>
<?
	    $result = $this->db->db_query("SELECT chain_idx, chain_name, chain_sl_idx, chain_fallback_idx, chain_active FROM shaper_chains");

	    while($row = $result->fetchrow()) {

	       if($row->chain_sl_idx != 0) {

		  $sl = $this->db->db_fetchSingleRow("SELECT sl_name FROM shaper_service_levels WHERE sl_idx='". $row->chain_sl_idx ."'");

                  if($row->chain_fallback_idx != 0) 
		     $fallback = $this->db->db_fetchSingleRow("SELECT sl_name FROM shaper_service_levels WHERE sl_idx='". $row->chain_fallback_idx ."'");
		  else
		     $fallback->sl_name = "No Fallback";

	       }
	       else {
		  $sl->sl_name = "Ignore QoS";
		  $fallback->sl_name = "Ignore QoS";
	       }
?>
      <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
       <td>
        <img src="<? print ICON_CHAINS; ?>" alt="chain icon" />
        <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=1&amp;idx=". $row->chain_idx; ?>" title="Click to modify">
         <? print $row->chain_name; ?>
        </a>
       </td>
       <td>
<?
	       if($row->chain_sl_idx != 0) {
?>
        <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />
        <a href="<? print $this->parent->self ."?mode=3&amp;screen=1&amp;idx=". $row->chain_sl_idx; ?>">
         <? print $sl->sl_name; ?>
        </a>
<?
	       } else {
?>
        <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />
        <? print $sl->sl_name; ?>
<? 
	       }
?>
       </td>
       <td>
<?
	       if($row->chain_sl_idx != 0 && $row->chain_fallback_idx != 0) {
?>
        <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />
        <a href="<? print $this->parent->self ."?mode=3&amp;screen=2&amp;idx=". $row->chain_fallback_idx; ?>">
         <? print $fallback->sl_name; ?>
        </a>
<?
	       } else {
?>
        <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />
        <? print $fallback->sl_name; ?>
<?
	       }
?>
       </td>
       <td style="text-align: center;">
        <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=2&amp;idx=". $row->chain_idx ."&amp;name=". urlencode($row->chain_name); ?>" title="Delete">
         <img src="<? print ICON_DELETE; ?>" alt="delete icon" />
        </a>
<?
	       if($row->chain_active == 'Y') {
?>
        <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=3&amp;idx=". $row->chain_idx ."&amp;to=0"; ?>" title="Disable chain <? print $row->chain_name; ?>">
         <img src="<? print ICON_ACTIVE; ?>" alt="status icon" />
        </a>
<?
	       } else {
?>
        <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=3&amp;idx=". $row->chain_idx ."&amp;to=1"; ?>" title="Enable chain <? print $row->chain_name; ?>">
         <img src="<? print ICON_INACTIVE; ?>" alt="status icon"  />
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

	       if(isset($_GET['new'])) {
               
		  $this->parent->startTable("<img src=\"". ICON_CHAINS ."\" alt=\"chain icon\" />&nbsp;Create a new Chain");
		  $form_url = $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;saveit=1&amp;new=1";

		  /* set defaults */
		  $chain->chain_active = "Y";
		  $chain->chain_sl_idx = -1;
		  $chain->chain_fallback_idx = -1;
		  $chain->chain_direction = 2;

	       }
	       else {

		  $chain = $this->db->db_fetchSingleRow("SELECT * FROM shaper_chains WHERE chain_idx='". $_GET['idx'] ."'");
		  $this->parent->startTable("<img src=\"". ICON_CHAINS ."\" alt=\"chain icon\" />&nbsp;Modify Chain ". $chain->chain_name);
		  $form_url = $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;idx=". $_GET['idx'] ."&amp;namebefore=". urlencode($chain->chain_name) ."&amp;saveit=1";

               }
?>
  <form action="<? print $form_url; ?>" method="post">
  <table style="width: 100%;" class="withborder2">
   <tr>
    <td colspan="3">
     <img src="<? print ICON_CHAINS; ?>" alt="chain icon" />&nbsp;General
    </td>
   </tr>
   <tr>
    <td style="white-space: nowrap;">Chain Name:</td>
    <td style="white-space: nowrap;"><input type="text" name="chain_name" size="40" value="<? print $chain->chain_name; ?>" /></td>
    <td>
     It's useful to enter a hostname (router), network name (OFFICE_LAN, DMZ) here.
    </td>
   </tr>
   <tr>
    <td style="white-space: nowrap;">Status:</td>
    <td style="white-space: nowrap;">
     <input type="radio" name="chain_active" value="Y" <? if($chain->chain_active == "Y") print "checked=\"checked\""; ?> />Active
     <input type="radio" name="chain_active" value="N" <? if($chain->chain_active != "Y") print "checked=\"checked\""; ?> />Inactive
    </td>
    <td>
     With this option the status of this chain is specified. Disabled chains are ignored when reloading the ruleset and do not show up in the overview list.
    </td>
   </tr>
   <tr>
    <td colspan="3">
     <img src="<? print ICON_CHAINS; ?>" alt="chain icon" />&nbsp;Bandwidth
    </td>
   </tr>
   <tr>
    <td style="white-space: nowrap;">Service Level:</td>
    <td style="white-space: nowrap;">
     <select name="chain_sl_idx">
<?
	       $result = $this->db->db_query("SELECT * FROM shaper_service_levels ORDER BY sl_name ASC");
	       
	       while($row = $result->fetchRow()) {

		  print "<option value=\"". $row->sl_idx ."\"";

		  if($row->sl_idx == $chain->chain_sl_idx)
		     print " selected=\"selected\"";

		  switch($this->parent->getOption("classifier")) {
		     case 'HTB':
			print ">". $row->sl_name ." (in: ". $row->sl_htb_bw_in_rate ."kbit/s, out: ". $row->sl_htb_bw_out_rate ."kbit/s)</option>\n";
			break;
		     case 'HFSC':
			print ">". $row->sl_name ." (in: ". $row->sl_hfsc_in_dmax ."ms,". $row->sl_hfsc_in_rate ."kbit/s, out: ". $row->sl_hfsc_out_dmax ."ms,". $row->sl_hfsc_bw_out_rate ."kbit/s)</option>\n";
			break;
		     case 'CBQ':
			print ">". $row->sl_name ." (in: ". $row->sl_cbq_in_rate ."kbit/s, out: ". $row->sl_cbq_out_rate ."kbit/s)</option>\n";
			break;

		  }
	       }
?>
      <option value="0" <? if($chain->chain_sl_idx == 0) print "selected=\"selected\""; ?>>--- Ignore QoS ---</option>
     </select>
    </td>
    <td>
     Specify the maximum bandwidth this chain provides. A chain contain one or more pipes which shape available chain bandwidth.
    </td>
   </tr>
   <tr>
    <td style="white-space: nowrap;">Fallback:</td>
    <td style="white-space: nowrap;">
     <select name="chain_fallback_idx">
<?
	       $result = $this->db->db_query("SELECT * FROM shaper_service_levels ORDER BY sl_name ASC");

	       while($row = $result->fetchRow()) {

		  print "<option value=\"". $row->sl_idx ."\"";

		  if($row->sl_idx == $chain->chain_fallback_idx)
		     print " selected=\"selected\"";

		  switch($this->parent->getOption("classifier")) {

		     case 'HTB':
			print ">". $row->sl_name ." (in: ". $row->sl_htb_bw_in_rate ."kbit/s, out: ". $row->sl_htb_bw_out_rate ."kbit/s)</option>\n";
			break;
		     case 'HFSC':
			print ">". $row->sl_name ." (in: ". $row->sl_hfsc_in_dmax ."ms,". $row->sl_hfsc_in_rate ."kbit/s, out: ". $row->sl_hfsc_out_dmax ."ms,". $row->sl_hfsc_bw_out_rate ."kbit/s)</option>\n";
			break;
		     case 'CBQ':
			print ">". $row->sl_name ." (in: ". $row->sl_cbq_in_rate ."kbit/s, out: ". $row->sl_cbq_out_rate ."kbit/s)</option>\n";
			break;
		  }
	       }
?>
      <option value="0" <? if($chain->chain_fallback_idx == 0) print "selected=\"selected\""; ?>>--- No Fallback ---</option>
     </select>
    </td>
    <td>
     Every traffic which get not matched against a chain's pipe can only use the fallback bandwidth. If no fallback service level is defined the chain is unable to contain pipes. Every traffic which will then get into this chain will be able use the chain's available bandwidth. 
    </td>
   </tr>
   <tr>
    <td colspan="3">
     <img src="<? print ICON_CHAINS; ?>" alt="chain icon" />&nbsp;Targets
    </td>
   </tr>
   <tr>
    <td style="white-space: nowrap;">
     Affecting:
    </td>
    <td style="white-space: nowrap;">
     <table class="noborder">
      <tr>
       <td>Source</td>
       <td>&nbsp;</td>
       <td style="text-align: right;">Destination</td>
      </tr>
      <tr>
       <td>
        <select name="chain_src_target">
         <option value="0">any</option>
<?
	       $result = $this->db->db_query("SELECT target_idx, target_name FROM shaper_targets ORDER BY target_name ASC");
	       while($row = $result->fetchRow()) {
		  
		  print "<option value=\"". $row->target_idx ."\"";

		  if($row->target_idx == $chain->chain_src_target)
		     print " selected=\"selected\"";

		  print ">". $row->target_name ."</option>\n";
	       }
?>
        </select>
       </td>
       <td>
        <select name="chain_direction">
         <option value="1" <? if($chain->chain_direction == 1) print "selected=\"selected\""; ?>>--&gt;</option>
         <option value="2" <? if($chain->chain_direction == 2) print "selected=\"selected\""; ?>>&lt;-&gt;</option>
        </select>
       </td>
       <td>
        <select name="chain_dst_target">
         <option value="0">any</option>
<?
	       $result = $this->db->db_query("SELECT target_idx, target_name FROM shaper_targets ORDER BY target_name ASC");
	       while($row = $result->fetchRow()) {

		  print "<option value=\"". $row->target_idx ."\"";

		  if($row->target_idx == $chain->chain_dst_target)
		     print " selected=\"selected\"";

		  print ">". $row->target_name ."</option>\n";
	       }
?>
        </select>
       </td>
      </tr>
     </table>
    </td>
    <td>
     Which traffic should get in this chain.
    </td>
   </tr>
   <tr>
    <td colspan="3">&nbsp;</td>
   </tr>
   <tr>
    <td style="text-align: center;"><a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>" title="Back"><img src="<? print ICON_ARROW_LEFT; ?>" alt="arrow left icon" /></a></td>
    <td><input type="submit" value="Save" /></td>
    <td>Save settings.</td>
   </tr>
  </table>
  </form>
<?
	       $this->parent->closeTable();
	    }
	    else {

               $error = 0;

               if(!isset($_POST['chain_name']) || $_POST['chain_name'] == "") {

	          $this->parent->printError("<img src=\"". ICON_CHAINS ."\" alt=\"chain icon\" />&nbsp;Chains", "Please enter a chain name!");
		  $error = 1;

               }

	       if(!$error && $_POST['chain_name'] != $_GET['namebefore'] && 
	          $this->db->db_fetchSingleRow("SELECT chain_idx FROM shaper_chains WHERE chain_name like '". $_POST['chain_name'] ."'")) {

		  $this->parent->printError("<img src=\"". ICON_CHAINS ."\" alt=\"chain icon=\" />&nbsp;Chains", "A chain with such a name already exists!");
		  $error = 1;

               }
						
               if(!$error) {

		  $max_pos = $this->db->db_fetchSingleRow("SELECT MAX(chain_position) as pos FROM shaper_chains");

                  if(isset($_GET['new'])) {

		     $this->db->db_query("INSERT INTO shaper_chains (chain_name, chain_sl_idx, chain_src_target, "
			."chain_dst_target, chain_position, chain_direction, chain_active, "
			."chain_fallback_idx) VALUES ('". $_POST['chain_name'] ."', "
			."'". $_POST['chain_sl_idx'] ."', "
			."'". $_POST['chain_src_target'] ."', "
			."'". $_POST['chain_dst_target'] ."', "
			."'". ($max_pos->pos+1) ."', "
			."'". $_POST['chain_direction'] ."', "
			."'". $_POST['chain_active'] ."', "
			."'". $_POST['chain_fallback_idx'] ."')");

		  }
		  else {

		     $this->db->db_query("UPDATE shaper_chains SET chain_name='". $_POST['chain_name'] ."', "
			."chain_sl_idx='". $_POST['chain_sl_idx'] ."', "
			."chain_src_target='". $_POST['chain_src_target'] ."', "
			."chain_dst_target='". $_POST['chain_dst_target'] ."', "
			."chain_direction='". $_POST['chain_direction'] ."', "
			."chain_active='". $_POST['chain_active'] ."', "
			."chain_fallback_idx='". $_POST['chain_fallback_idx'] ."' "
			."WHERE chain_idx='". $_GET['idx'] ."'");

		  }

		  $this->parent->goBack();

	       }
	    }
	    break;

	 case 2:

	    if(!isset($_GET['doit']))
	       $this->parent->printYesNo("<img src=\"". ICON_CHAINS ."\" alt=\"chain icon\" />&nbsp;Delete Chain", "Delete Chain ". $_GET['name'] ."?");
	    else {
	       if($_GET['idx']) 
		  $this->db->db_query("DELETE FROM shaper_chains WHERE chain_idx='". $_GET['idx'] ."'");
	       $this->parent->goBack();
	    }
	    break;

	 case 3:
	    
	    if(isset($_GET['idx']) && isset($_GET['to'])) {

	       if($_GET['to'] == 1)
	          $this->db->db_query("UPDATE shaper_chains SET chain_active='Y' WHERE chain_idx='". $_GET['idx'] ."'");
	       elseif($_GET['to'] == 0)
	          $this->db->db_query("UPDATE shaper_chains SET chain_active='N' WHERE chain_idx='". $_GET['idx'] ."'");

	    }

	    $this->parent->goBack();
	    break;
      }

   } // showHtml()

}

?>
