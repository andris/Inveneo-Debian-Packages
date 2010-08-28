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

class MSPROTOCOLS {

   var $db;
   var $parent;

   /* Class constructor */
   function MSPROTOCOLS($parent)
   {
      $this->parent = $parent;
      $this->db = $parent->db;
   } // MSPROTOCOLS()

  
   /* interface output */
   function showHtml()
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_manage_protocols")) {

	 $this->parent->printError("<img src=\"". ICON_PROTOCOLS ."\" alt=\"protocol icon\" />&nbsp;Manage Protocols", "You do not have enough permissions to access this module!");
	 return 0;

      }

      if(!isset($this->parent->screen))
         $this->parent->screen = 0;

      switch($this->parent->screen) {

	 default:
	 case 0:
			   
            $this->parent->startTable("<img src=\"". ICON_PROTOCOLS ."\" alt=\"protocol icon\" />&nbsp;Manage Protocols");
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
    <td style="text-align: center;" colspan="3">
     <img src="<? print ICON_NEW; ?>" alt="new icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>&amp;screen=1">
      Create a new Protocol
     </a>
    </td>
   </tr>
   <tr>
    <td colspan="3">&nbsp;</td>
   </tr>
   <tr>
    <td><img src="<? print ICON_PROTOCOLS; ?>" alt="protocol icon" />&nbsp;<i>Name</i></td>
    <td style="text-align: center;"><i>Number</i></td>
    <td style="text-align: center;"><i>Options</i></td>
   </tr>
<?

	    $result = $this->db->db_query("SELECT proto_idx, proto_name, proto_number, proto_user_defined FROM shaper_protocols ORDER BY proto_name ASC");
	
	    while($row = $result->fetchrow()) {

?>
   <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
    <td>
     <img src="<? print ICON_PROTOCOLS; ?>" alt="protocol icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=2&amp;idx=". $row->proto_idx; ?>">
      <? if($row->proto_user_defined == 'Y') print "<img src=\"". ICON_USERS ."\" alt=\"User defined protocol\" />"; ?>
      <? print $row->proto_name; ?>
     </a>
    </td>
    <td style="text-align: center;"><? print $row->proto_number; ?></td>
    <td style="text-align: center;">
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=3&amp;idx=". $row->proto_idx ."&amp;name=". urlencode($row->proto_name); ?>">
      <img src="<? print ICON_DELETE; ?>" alt="delete icon" />
     </a>
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

               $this->parent->startTable("<img src=\"". ICON_PROTOCOLS ."\" alt=\"protocol icon\" />&nbsp;Create a new Protocol");

?>
  <form action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen; ?>&amp;saveit=1" method="post">
   <table style="width: 100%;" class="withborder2">
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PROTOCOLS; ?>" alt="protocol icon" />
      General
     </td>
    </tr>
    <tr>
     <td>Name:</td>
     <td><input type="text" name="proto_name" size="30" /></td>
     <td>The protocol name.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PROTOCOLS; ?>" alt="protocol icon" />
      Details
     </td>
    </tr>
    <tr>
     <td>Number:</td>
     <td>
      <input type="text" name="proto_number" size="30" />
     </td>
     <td>The IANA portocol number.</td>
    </tr>
    <tr>
     <td colspan="3">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td style="text-align: center;"><a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>" title="Back"><img src="<? print ICON_ARROW_LEFT; ?>" alt="arrow left icon" /></a></td>
     <td><input type="submit" value="Save" /></td>
     <td>Save your settings.</td>
    </tr>
   </table>
  </form>
<?
               $this->parent->closeTable();

	    }
	    else {

	       if($_POST['proto_name'] == "")
		  $this->parent->printError("<img src=\"". ICON_PROTOCOLS ."\" alt=\"protocol icon\" />&nbsp;Create a new Protocol", "Please enter a protocol name!");
	       else {
		  
		  if($_POST['proto_number'] == "" || !is_numeric($_POST['proto_number'])) 
		     $this->parent->printError("<img src=\"". ICON_PROTOCOLS ."\" alt=\"protocol icon\" />&nbsp;Create a new Protocol", "Please enter a numerical protocol number!");
		  else {

		     $this->db->db_query("INSERT INTO shaper_protocols (proto_name, proto_number, "
		                        ."proto_user_defined) VALUES ('". $_POST['proto_name'] ."', "
					."'". $_POST['proto_number'] ."', 'Y')");
		     $this->parent->goBack();
		     
		  }
	       }
	    }
	    break;
	 case 2:
	    
	    if(!isset($_GET['saveit'])) {
					
	       $proto = $this->db->db_fetchSingleRow("SELECT proto_name, proto_number FROM "
	                                           ."shaper_protocols WHERE proto_idx='". $_GET['idx'] ."'");

               $this->parent->startTable("<img src=\"". ICON_PROTOCOLS ."\" alt=\"protocol icon\" />&nbsp;Modify Protocol ". $proto->proto_name);
?>
  <form action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;idx=". $_GET['idx']; ?>&amp;saveit=1" method="post">
   <table style="width: 100%;" class="withborder2">
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PROTOCOLS; ?>" alt="protocol icon" />
      General
     </td>
    </tr>
    <tr>
     <td>Name:</td>
     <td><input type="text" name="proto_name" size="30" value="<? print $proto->proto_name; ?>" /></td>
     <td>The protocol name.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PROTOCOLS; ?>" alt="protocol icon" />
      Details
     </td>
    </tr>
    <tr>
     <td>Number:</td>
     <td>
      <input type="text" name="proto_number" size="30" value="<? print $proto->proto_number; ?>" />
     </td>
     <td>The IANA portocol number.</td>
    </tr>
    <tr>
     <td colspan="3">
      &nbsp;
     </td>
    </tr>
    <tr>
     <td style="text-align: center;"><a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>" title="Back"><img src="<? print ICON_ARROW_LEFT; ?>" alt="protocol icon" /></a></td>
     <td><input type="submit" value="Save" /></td>
     <td>Save your settings.</td>
    </tr>
   </table>
  </form>
<?

               $this->parent->closeTable();
	    }
	    else {

	       if($_POST['proto_name'] == "")
		  $this->parent->printError("<img src=\"". ICON_PROTOCOLS ."\" alt=\"protocol icon\" />&nbsp;Modify Protocol", "Please enter a protocol name!");
	       else {
		  
		  if($_POST['proto_number'] == "" || !is_numeric($_POST['proto_number'])) 
		     $this->parent->printError("<img src=\"". ICON_PROTOCOLS ."\" alt=\"protocol icon\" />&nbsp;Modify Protocol", "Please enter a numerical protocol number!");
		  else {

		     $this->db->db_query("UPDATE shaper_protocols SET proto_name='". $_POST['proto_name'] 
		                        ."', proto_number='". $_POST['proto_number'] ."', proto_user_defined='Y' "
					."WHERE proto_idx='". $_GET['idx'] ."'");
		     $this->parent->goBack();
		  }
	       }
	    }
	    break;
	 case 3:
	    
	    if(!isset($_GET['doit']))
	       $this->parent->printYesNo("<img src=\"". ICON_PROTOCOLS ."\" alt=\"protocol icon\" />&nbsp;Delete Protocol", "Delete Protocol ". $_GET['name']);
	    else {
	       
	       if($_GET['idx'])
		  $this->db->db_query("DELETE FROM shaper_protocols WHERE proto_idx='". $_GET['idx'] ."'");
	       $this->parent->goBack();
	    }
	    break;
      }

   } // showHtml()

}

?>
