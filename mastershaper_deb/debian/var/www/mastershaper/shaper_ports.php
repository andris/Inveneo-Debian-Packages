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

class MSPORTS {

   var $db;
   var $parent;

   /* Class constructor */
   function MSPORTS($parent)
   {
      $this->parent = $parent;
      $this->db = $parent->db;
   } // MSPORTS()

   /* interface output */
   function showHtml()
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_manage_ports")) {

	 $this->parent->printError("<img src=\"". ICON_PORTS ."\" alt=\"port icon\" />&nbsp;Manage Ports", "You do not have enough permissions to access this module!");
	 return 0;

      }

      if(!isset($this->parent->screen))
         $this->parent->screen = 0;

      switch($this->parent->screen) {

         default:
         case 0:

	    if(!isset($_GET['orderby']))
	       $_GET['orderby'] = "port_name";
	    if(!isset($_GET['sortorder']))
	       $_GET['sortorder'] = "ASC";
	    if(!isset($_GET['breaker']))
	       $_GET['breaker'] = 'A';
	  
	    $this->parent->startTable("<img src=\"". ICON_PORTS ."\" alt=\"port icon\" />&nbsp;Manage Ports");
?>
  <table style="width: 100%;" class="withborder">
   <tr>
<?
            if(isset($_GET['saved'])) {
?>
    <td colspan="5" style="text-align: center;" class="sysmessage">You have made changes to the ruleset. Don't forget to reload them.</td>
<?
            } else {
?>
    <td colspan="5">&nbsp;</td>
<?
            }
?>
   </tr>
   <tr>
    <td style="text-align: center;" colspan="5">
     <img src="<? print ICON_NEW; ?>" alt="new icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>&amp;screen=1">
      Create a new Port
     </a>
    </td>
   </tr>
   <tr>
    <td colspan="5">&nbsp;</td>
   </tr>
   <tr>
    <td colspan="5" style="text-align: center;">
<?
	    
	    /* Display alphabetical port select */
	    foreach(range('A', 'Z') as $letter)
	    {
	       if(isset($_GET['breaker']) && $letter == strtoupper($_GET['breaker'][0])) {
?>
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;breaker=". $letter ."&amp;orderby=". $_GET['orderby'] ."&amp;sortorder=". $_GET['sortorder']; ?>" style="color: #AF0000;"><? print $letter ?></a>
<?
               }
	       else {
?>
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;breaker=". $letter ."&amp;orderby=". $_GET['orderby'] ."&amp;sortorder=". $_GET['sortorder']; ?>"><? print $letter ?></a>
<?
               }
	    }

	    foreach(range('0', '9') as $letter)
	    {
	       if(isset($_GET['breaker']) && $letter == strtoupper($_GET['breaker'][0])) {
?>
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;breaker=". $letter ."&amp;orderby=". $_GET['orderby'] ."&amp;sortorder=". $_GET['sortorder']; ?>" style="color: #AF0000;"><? print $letter ?></a>
<?
               }
	       else {
?>
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;breaker=". $letter ."&amp;orderby=". $_GET['orderby'] ."&amp;sortorder=". $_GET['sortorder']; ?>"><? print $letter ?></a>
<?
               }
	    }

	    $letter = "#";

	    if(isset($_GET['breaker']) && $letter == urldecode(strtoupper($_GET['breaker'][0]))) {
?>
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;breaker=". urlencode($letter) ."&amp;orderby=". $_GET['orderby'] ."&amp;sortorder=". $_GET['sortorder']; ?>" style="color: #AF0000;"><? print $letter ?></a>
<?
	    }
	    else {
?>
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;breaker=". urlencode($letter) ."&amp;orderby=". $_GET['orderby'] ."&amp;sortorder=". $_GET['sortorder']; ?>"><? print $letter ?></a>
<?
	    }
?>
    </td>
   </tr>
   <tr>
    <td colspan="5">&nbsp;</td>
   </tr>
   <tr>
    <td>
     <img src="<? print ICON_PORTS; ?>" alt="port icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;breaker=". urlencode($_GET['breaker']) ."&amp;orderby=port_name&amp;sortorder="; if($_GET['sortorder'] == "ASC") print "DESC"; if($_GET['sortorder'] == 'DESC') print "ASC"; ?>">
      <i>Name</i>
     </a>
    </td>
    <td>
     <img src="<? print ICON_PORTS; ?>" alt="port icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;breaker=". urlencode($_GET['breaker']) ."&amp;orderby=port_desc&amp;sortorder="; if($_GET['sortorder'] == "ASC") print "DESC"; if($_GET['sortorder'] == 'DESC') print "ASC"; ?>">
      <i>Description</i>
     </a>
    </td>
    <td>
     <img src="<? print ICON_PORTS; ?>" alt="port icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;breaker=". urlencode($_GET['breaker']) ."&amp;orderby=port_number&amp;sortorder="; if($_GET['sortorder'] == "ASC") print "DESC"; if($_GET['sortorder'] == 'DESC') print "ASC"; ?>">
      <i>Port-Number</i>
     </a>
    </td>
    <td style="text-align: center;"><i>Options</i></td>
   </tr>
<?

            if(isset($_GET['breaker']) && $_GET['breaker'] != "#") {

               $result = $this->db->db_query("SELECT port_idx, port_name, port_desc, port_number, "
	                                    ."port_user_defined FROM shaper_ports "
					    ."WHERE port_name REGEXP '^". $_GET['breaker'] ."' ORDER BY "
					    .$_GET['orderby'] ." ". $_GET['sortorder']);
            }
	    else {

               $result = $this->db->db_query("SELECT port_idx, port_name, port_desc, port_number, "
	                                    ."port_user_defined FROM shaper_ports "
					    ."ORDER BY ". $_GET['orderby'] ." ". $_GET['sortorder']);
	    }
	
            while($row = $result->fetchrow()) {

?>
   <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
    <td>
     <img src="<? print ICON_PORTS; ?>" alt="port icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=2&amp;idx=". $row->port_idx; ?>">
      <? if($row->port_user_defined == 'Y') print "<img src=\"". ICON_USERS ."\" alt=\"User defined port\" />"; ?>
      <? print htmlentities($row->port_name); ?>
     </a>
    </td>
    <td><? if($row->port_desc != "") print htmlentities($row->port_desc); else print "&nbsp;"; ?></td>
    <td><? if($row->port_number != "") print $row->port_number; else print "&nbsp;"; ?></td>
    <td style="text-align: center;">
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=3&amp;idx=". $row->port_idx ."&amp;name=". urlencode($row->port_name); ?>">
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

               $this->parent->startTable("<img src=\"". ICON_PORTS ."\" alt=\"port icon\" />&nbsp;Create a new Port");

?>
  <form action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen; ?>&amp;saveit=1" method="post">
   <table style="width: 100%;" class="withborder2">
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PORTS; ?>" alt="port icon" />&nbsp;General
     </td>
    </tr>
    <tr>
     <td>Name:</td>
     <td><input type="text" name="port_name" size="30" /></td>
     <td>Name of the port.</td>
    </tr>
    <tr>
     <td>Description:</td>
     <td><input type="text" name="port_desc" size="30" /></td>
     <td>Short description of the port.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PORTS; ?>" alt="port icon" />&nbsp;Details
     </td>
    </tr>
    <tr>
     <td>Number:</td>
     <td>
      <input type="text" name="port_number" size="30" />
     </td>
     <td>Add multiple port splitted with ',' or lists like 22-25</td>
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

               /* check if port name is supplied */	
               if($_POST['port_name'] == "")
                  $this->parent->printError("<img src=\"". ICON_PORTS ."\" alt=\"port icon\" />&nbsp;Create a new Port", "Please enter a port name!");
               else {

                  $is_numeric = 1;

		  /* check if port with this name already exists */
                  if(!$this->db->db_fetchSingleRow("SELECT port_idx FROM shaper_ports WHERE port_name like '". $_POST['port_name'] ."'")) {

		     /* only one or several ports */
                     if(preg_match("/,/", $_POST['port_number']) || preg_match("/-/", $_POST['port_number'])) {

                        $temp_ports = split(",", $_POST['port_number']);

                        foreach($temp_ports as $port) {
					
                           $port = trim($port); 

                           if(preg_match("/-/", $port)) {

                              list($lower, $higher) = split("-", $port);

                              if(!is_numeric($lower) || $lower <= 0 || $lower >= 65536)
                                 $is_numeric = 0;

                              if(!is_numeric($higher) || $higher <= 0 || $higher >= 65536)
                                 $is_numeric = 0;

                           }
                           else {
                              if(!is_numeric($port) || $port <= 0 || $port >= 65536)
                                 $is_numeric = 0;
                           }
                        }
                     }
                     else
                        if(!is_numeric($_POST['port_number']) || $_POST['port_number'] <= 0 || $_POST['port_number'] >= 65536)
                           $is_numeric = 0;
			   
                     if(!$is_numeric)
                        $this->parent->printError("<img src=\"". ICON_PORTS ."\" alt=\"port icon\" />&nbsp;Create a new Port", "Please enter a decimal port number within 1 - 65535!");
                     else {
                        $this->db->db_query("INSERT INTO shaper_ports (port_name, port_desc, port_number, "
			                   ."port_user_defined) VALUES ('". $_POST['port_name'] ."', "
                                           ."'". $_POST['port_desc'] ."', '". $_POST['port_number'] ."', "
					   ."'Y')");
                        $this->parent->goBack();
                     }
                  }
                  else {
                     $this->parent->printError("<img src=\"". ICON_PORTS ."\" alt=\"port icon\" />&nbsp;Create a new Port", "A port with the name ". $_POST['port_name'] ." already exists. Please choose another name!");
                  }
               }
            }
            break;

         case 2:

            if(!isset($_GET['saveit'])) {
				
               $port = $this->db->db_fetchSingleRow("SELECT port_name, port_desc, port_number FROM "
	                                           ."shaper_ports WHERE port_idx='". $_GET['idx'] ."'");

               $this->parent->startTable("<img src=\"". ICON_PORTS ."\" alt=\"port icon\" />&nbsp;Modify Port ". $port->port_name);

?>
  <form action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;idx=". $_GET['idx'] ."&amp;namebefore=". urlencode($port->port_name); ?>&amp;saveit=1" method="post">
   <table style="width: 100%" class="withborder2">
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PORTS; ?>" alt="port icon" />&nbsp;General
     </td>
    </tr>
    <tr>
     <td>Name:</td>
     <td><input type="text" name="port_name" size="30" value="<? print $port->port_name; ?>" /></td>
     <td>Name of the Port.</td>
    </tr>
    <tr>
     <td>Description:</td>
     <td><input type="text" name="port_desc" size="30" value="<? print $port->port_desc; ?>" /></td>
     <td>Short description of the port.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PORTS; ?>" alt="port icon" />&nbsp;Details
     </td>
    </tr>
    <tr>
     <td>Number:</td>
     <td>
      <input type="text" name="port_number" size="30" value="<? print $port->port_number; ?>" />
     </td>
     <td>Add multiple port splitted with ',' or lists like 22-25</td>
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
               if($_POST['port_name'] == "")
                  $this->parent->printError("<img src=\"". ICON_PORTS ."\" alt=\"port icon\" />&nbsp;Modify Port", "Please enter a port name!");
               else {
                     
                  $is_numeric = 1;

                  /* check if port with this name already exists */
                  if(!$this->db->db_fetchSingleRow("SELECT port_idx FROM shaper_ports WHERE port_name like '". $_POST['port_name'] ."'") || $_POST['port_name'] == $_GET['namebefore']) {

		     /* only one or several ports */
                     if(preg_match("/,/", $_POST['port_number']) || preg_match("/-/", $_POST['port_number'])) {

                        $temp_ports = split(",", $_POST['port_number']);

                        foreach($temp_ports as $port) {
					
                           $port = trim($port); 

                           if(preg_match("/-/", $port)) {

                              list($lower, $higher) = split("-", $port);

                              if(!is_numeric($lower) || $lower <= 0 || $lower >= 65536)
                                 $is_numeric = 0;

                              if(!is_numeric($higher) || $higher <= 0 || $higher >= 65536)
                                 $is_numeric = 0;

                           }
			   else {
                              if(!is_numeric($port) || $port <= 0 || $port >= 65536)
                                 $is_numeric = 0;
                           }
                        }
                     }
                     else
			if(!is_numeric($_POST['port_number']) || $_POST['port_number'] <= 0 || $_POST['port_number'] >= 65536)
                           $is_numeric = 0;

                     if(!$is_numeric)
                        $this->parent->printError("<img src=\"". ICON_PORTS ."\" alt=\"port icon\" />&nbsp;Modify Port", "Please enter a decimal port number within 1 - 65535!");
                     else {
                        $this->db->db_query("UPDATE shaper_ports SET port_name='". $_POST['port_name'] ."', "
                                           ."port_desc='". $_POST['port_desc'] ."', "
                                           ."port_number='". $_POST['port_number'] ."', "
                                           ."port_user_defined='Y' WHERE port_idx='". $_GET['idx'] ."'");

                        $this->parent->goBack();
                     }
                  }
                  else {
                     $this->parent->printError("<img src=\"". ICON_PORTS ."\" alt=\"port icon\" />&nbsp;Modify Port", "A port with the name ". $_POST['port_name'] ." already exists. Please choose another name!");
                  }
               }
            }
            break;

         case 3:

            if(!isset($_GET['doit']))
               $this->parent->printYesNo("<img src=\"". ICON_PORTS ."\" alt=\"port icon\" />&nbsp;Delete Port", "Delete Port ". $_GET['name'] ."?");

            else {

               if(isset($_GET['idx'])) {

                  $this->db->db_query("DELETE FROM shaper_ports WHERE port_idx='". $_GET['idx'] ."'");
		  $this->db->db_query("DELETE FROM shaper_assign_ports WHERE afp_port_idx='". $_GET['idx'] ."'");
                  $this->parent->goBack();

               }
            }
	    break;

      }

   } // showHtml()
}

?>
