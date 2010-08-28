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

class MSUSERS {

   var $db;
   var $parent;

   /* Class constructor */
   function MSUSERS($parent)
   {
      $this->parent = $parent;
      $this->db = $parent->db;
   } // MSUSERS()

  
   /* interface output */
   function showHtml()
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_manage_users")) {

	 $this->parent->printError("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />&nbsp;Manage Users", "You do not have enough permissions to access this module!");
	 return 0;

      }

      if(!isset($this->parent->screen))
         $this->parent->screen = 0;

      switch($this->parent->screen) {

	 default:
	 case 0:
			   
            $this->parent->startTable("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />&nbsp;Manage Users");
?>
  <table style="width: 100%;" class="withborder"> 
   <tr>
    <td style="text-align: center;" colspan="2">
     <img src="<? print ICON_NEW; ?>" alt="new icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>&amp;screen=1&amp;new=1">
      Create a new User
     </a>
    </td>
   </tr>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr>
    <td><img src="<? print ICON_USERS; ?>" alt="user icon" />&nbsp;<i>Name</i></td>
    <td style="text-align: center;"><i>Options</i></td>
   </tr>
<?

	    $result = $this->db->db_query("SELECT user_idx, user_name, user_active FROM shaper_users "
	                 ."ORDER BY user_name ASC");
	
	    while($row = $result->fetchrow()) {

	       $permissions = $this->getPermissions($row->user_idx);

?>
   <script type="text/javascript">
   <!--
      staticTip.tips.tipUser<? print $row->user_idx; ?> = new Array(20, 5, 150, '<i><font color=\"#000000\" style=\"font-size: 12px\";>' +
         '<img src=\"<? print ICON_USERS; ?>\" style=\"text-align: center;\" alt=\"user icon\" />&nbsp;' +
         'Permissions of User <? print $row->user_name; ?></font></i>' +
         '<br /><br />' +
	 '<? print $permissions; ?>');
   -->
   </script>
   <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
    <td>
     <img src="<? print ICON_USERS; ?>" alt="user icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=1&amp;idx=". $row->user_idx; ?>" onmouseover="staticTip.show('tipUser<? print $row->user_idx; ?>');" onmouseout="staticTip.hide();">
      <? print $row->user_name; ?>
     </a>
    </td>
    <td style="text-align: center;">
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=2&amp;idx=". $row->user_idx ."&amp;name=". urlencode($row->user_name); ?>">
      <img src="<? print ICON_DELETE; ?>" alt="delete icon" />
     </a>
<?
               if($row->user_active == 'Y') {
?>
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=3&amp;idx=". $row->user_idx ."&amp;to=0"; ?>">
      <img src="<? print ICON_ACTIVE; ?>" alt="active icon" />
     </a>
<?
               }
	       else {
?>
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=3&amp;idx=". $row->user_idx ."&amp;to=1"; ?>">
      <img src="<? print ICON_INACTIVE; ?>" alt="inactive icon" />
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

		  $this->parent->startTable("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />&nbsp;Create a new User");
		  $form_url = $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;saveit=1&amp;new=1";

               }
	       else {

		  $current = $this->db->db_fetchSingleRow("SELECT * FROM shaper_users WHERE user_idx='". $_GET['idx'] ."'");
		  $this->parent->startTable("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />&nbsp;Modify User ". $current->user_name);
		  $form_url = $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;saveit=1&amp;idx=". $_GET['idx'];

               }


?>
  <form action="<? print $form_url; ?>" method="post">
   <table style="width: 100%;" class="withborder2">
    <tr>
     <td colspan="3">
      <img src="<? print ICON_USERS; ?>" alt="user icon" />
      General
     </td>
    </tr>
    <tr>
     <td>
      Name:
     </td>
     <td>
      <input type="text" name="user_name" size="30" value="<? print $current->user_name; ?>" />
     </td>
     <td>
      Enter the user/login name.
     </td>
    </tr>
    <tr>
     <td>
      Password:
     </td>
     <td>
      <input type="password" name="user_pass1" size="30" value="<? if(!isset($_GET['new'])) print "nochangeMS"; ?>" />
     </td>
     <td>
      Enter password of the user.
     </td>
    </tr>
    <tr>
     <td>
      (again)
     </td>
     <td>
      <input type="password" name="user_pass2" size="30" value="<? if(!isset($_GET['new'])) print "nochangeMS"; ?>" />
     </td>
     <td>
      &nbsp;
     </td>
    </tr>
    <tr>
     <td>
      Status:
     </td>
     <td>
      <input type="radio" name="user_active" value="Y" <? if($current->user_active == "Y") print "checked=\"checked\""; ?> />Enabled
      <input type="radio" name="user_active" value="N" <? if($current->user_active != "Y") print "checked=\"checked\""; ?> />Disabled
     </td>
     <td>
      Enable or disable user account.
     </td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_USERS; ?>" alt="user icon" />
      Global Permissions:
     </td>
    </tr>
    <tr>
     <td>
      Rights:
     </td>
     <td>
      <table class="noborder">
       <tr>
        <td>
	 <input type="checkbox" value="Y" name="user_manage_chains" <? if($current->user_manage_chains == "Y") print "checked=\"checked\""; ?> />&nbsp;Manage Chains<br />
	 <input type="checkbox" value="Y" name="user_manage_pipes" <? if($current->user_manage_pipes == "Y") print "checked=\"checked\""; ?> />&nbsp;Manage Pipes<br />
	 <input type="checkbox" value="Y" name="user_manage_filters" <? if($current->user_manage_filters == "Y") print "checked=\"checked\""; ?> />&nbsp;Manage Filters<br />
	 <input type="checkbox" value="Y" name="user_manage_ports" <? if($current->user_manage_ports == "Y") print "checked=\"checked\""; ?> />&nbsp;Manage Ports<br />
	 <input type="checkbox" value="Y" name="user_manage_protocols" <? if($current->user_manage_protocols == "Y") print "checked=\"checked\""; ?> />&nbsp;Manage Protocols<br />
	 <input type="checkbox" value="Y" name="user_manage_targets" <? if($current->user_manage_targets == "Y") print "checked=\"checked\""; ?> />&nbsp;Manage Targets<br />
	 <input type="checkbox" value="Y" name="user_manage_users" <? if($current->user_manage_users == "Y") print "checked=\"checked\""; ?> />&nbsp;Manage User<br />
	 <input type="checkbox" value="Y" name="user_manage_options" <? if($current->user_manage_options == "Y") print "checked=\"checked\""; ?> />&nbsp;Manage Options<br />
	 <input type="checkbox" value="Y" name="user_manage_servicelevels" <? if($current->user_manage_servicelevels == "Y") print "checked=\"checked\""; ?> />&nbsp;Manage Service Levels<br />
	 <input type="checkbox" value="Y" name="user_load_rules" <? if($current->user_load_rules == "Y") print "checked=\"checked\""; ?> />&nbsp;Load &amp; Unload Ruleset<br />
	 <input type="checkbox" value="Y" name="user_show_rules" <? if($current->user_show_rules == "Y") print "checked=\"checked\""; ?> />&nbsp;Show Ruleset &amp; Overview<br />
	 <input type="checkbox" value="Y" name="user_show_monitor" <? if($current->user_show_monitor == "Y") print "checked=\"checked\""; ?> />&nbsp;Show Monitor<br />
	</td>
       </tr>
      </table>
     <td>Permissions of the user.</td>
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

	       if($_POST['user_name'] == "") {

		  $this->parent->printError("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />&nbsp;Modify User", "Please enter a user name!");
		  $error = true;

               }

	       if(!$error && isset($_GET['new']) && $this->db->db_fetchSingleRow("SELECT user_idx FROM shaper_users WHERE user_name like '". $_POST['user_name'] ."'")) {

                  $this->parent->printError("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />/&nbsp;Modify User", "A user with such a user name already exist!");
		  $error = true;

	       }

	       if(!$error && $_POST['user_pass1'] == "") {

		  $this->parent->printError("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />&nbsp;Modify User", "Empty passwords are not allowed!");
		  $error = true;

               }

	       if(!$error && $_POST['user_pass1'] != $_POST['user_pass2']) {

		  $this->parent->printError("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />&nbsp;Modify User", "The two entered passwords are not equal!");
		  $error = true;

               }	       

	       if(!$error) {

                  if(isset($_GET['new'])) {

		     $this->db->db_query("INSERT INTO shaper_users (user_name, user_pass, user_manage_chains, "
			."user_manage_pipes, user_manage_filters, user_manage_ports, user_manage_protocols, "
			."user_manage_targets, user_manage_users, user_manage_options, user_manage_servicelevels, "
			."user_load_rules, user_show_rules, user_show_monitor, user_active) VALUES ("
			."'". $_POST['user_name'] ."', "
			."'". md5($_POST['user_pass1']) ."', "
			."'". $_POST['user_manage_chains'] ."', "
			."'". $_POST['user_manage_pipes'] ."', "
			."'". $_POST['user_manage_filters'] ."', "
			."'". $_POST['user_manage_ports'] ."', "
			."'". $_POST['user_manage_protocols'] ."', "
			."'". $_POST['user_manage_targets'] ."', "
			."'". $_POST['user_manage_users'] ."', "
			."'". $_POST['user_manage_options'] ."', "
			."'". $_POST['user_manage_servicelevels'] ."', "
			."'". $_POST['user_load_rules'] ."', "
			."'". $_POST['user_show_rules'] ."', "
			."'". $_POST['user_show_monitor'] ."', "
			."'". $_POST['user_active'] ."')");

                  }
		  else {

		     $this->db->db_query("UPDATE shaper_users SET "
			."user_name='". $_POST['user_name'] ."', "
			."user_manage_chains='". $_POST['user_manage_chains'] ."', "
			."user_manage_pipes='". $_POST['user_manage_pipes'] ."', "
			."user_manage_filters='". $_POST['user_manage_filters'] ."', "
			."user_manage_ports='". $_POST['user_manage_ports'] ."', "
			."user_manage_protocols='". $_POST['user_manage_protocols'] ."', "
			."user_manage_targets='". $_POST['user_manage_targets'] ."', "
			."user_manage_users='". $_POST['user_manage_users'] ."', "
			."user_manage_options='". $_POST['user_manage_options'] ."', "
			."user_manage_servicelevels='". $_POST['user_manage_servicelevels'] ."', "
			."user_load_rules='". $_POST['user_load_rules'] ."', "
			."user_show_rules='". $_POST['user_show_rules'] ."', "
			."user_show_monitor='". $_POST['user_show_monitor'] ."', "
			."user_active='". $_POST['user_active'] ."'"
			."WHERE user_idx='". $_GET['idx'] ."'");


		     if($_POST['user_pass1'] != "nochangeMS") {

			$this->db->db_query("UPDATE shaper_users SET user_pass='". md5($_POST['user_pass1']) ."' "
			   ."WHERE user_idx='". $_GET['idx'] ."'");

		     }

		  }
		  
		  $this->parent->goBack();
		     
	       }

	    }
	    break;

	 case 2:
	    
	    if(!$_GET['doit'])
	       $this->parent->printYesNo("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />&nbsp;Delete User", "Delete User ". $_GET['name'] ."?");
	    else {
	       
	       if($_GET['idx'])
		  $this->db->db_query("DELETE FROM shaper_users WHERE user_idx='". $_GET['idx'] ."'");
	       $this->parent->goBack();
	    }
	    break;

	 case 3:

	    if($_GET['idx']) {

	       if($_GET['to'] == 1) $this->db->db_query("UPDATE shaper_users SET user_active='Y' WHERE user_idx='". $_GET['idx'] ."'");
	       elseif($_GET['to'] == 0) $this->db->db_query("UPDATE shaper_users SET user_active='N' WHERE user_idx='". $_GET['idx'] ."'");

	    }

	    $this->parent->goBack();
	    break;

      }

   } // showHtml()

   function getPermissions($user_idx)
   {

      $string = "";

      if($user = $this->db->db_fetchSingleRow("SELECT user_manage_chains, user_manage_pipes, user_manage_filters, user_manage_ports, "
                    ."user_manage_protocols, user_manage_targets, user_manage_users, user_manage_options, user_manage_servicelevels, "
		    ."user_load_rules, user_show_rules, user_show_monitor FROM shaper_users WHERE user_idx='". $user_idx ."'")) {

         if($user->user_manage_chains == "Y")
	    $string.= "Chains, ";
         if($user->user_manage_pipes == "Y")
	    $string.= "Pipes, ";
         if($user->user_manage_filters == "Y")
	    $string.= "Filters, ";
         if($user->user_manage_ports == "Y")
	    $string.= "Ports, ";
         if($user->user_manage_protocols == "Y")
	    $string.= "Protocols, ";
         if($user->user_manage_targets == "Y")
	    $string.= "Targets, ";
         if($user->user_manage_users == "Y")
	    $string.= "Users, ";
         if($user->user_manage_options == "Y")
	    $string.= "Options, ";
         if($user->user_manage_servicelevels == "Y")
	    $string.= "Service Levels, ";
         if($user->user_load_rules == "Y")
	    $string.= "Load Rules, ";
         if($user->user_show_rules == "Y")
	    $string.= "Show Rules, ";
         if($user->user_show_monitor == "Y")
	    $string.= "Show Monitoring, ";

      }

      return substr($string, 0, strlen($string)-2);

   } // getPermissions()

}

?>
