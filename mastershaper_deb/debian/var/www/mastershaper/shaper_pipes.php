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

class MSPIPES {

   var $db;
   var $parent;

   /* Class constructor */
   function MSPIPES($parent)
   {
      $this->parent = $parent;
      $this->db = $parent->db;
   }

   /* interface output */
   function showHtml()
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_manage_pipes")) {

	 $this->parent->printError("<img src=\"". ICON_PIPES ."\" alt=\"pipe icon\" />&nbsp;Manage Pipes", "You do not have enough permissions to access this module!");
	 return 0;

      }

      if(!isset($this->parent->screen))
         $this->parent->screen = 0;

      switch($this->parent->screen) {

	 default:
	 case 0:
	    
	    $this->parent->startTable("<img src=\"". ICON_PIPES ."\" alt=\"pipe icon\" />&nbsp;Manage Pipes");

?>
  <table style="width: 100%;" class="withborder"> 
   <tr>
<?
	    if(isset($_GET['saved'])) {
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
    <td style="text-align: center;" colspan="4">
     <img src="<? print ICON_NEW; ?>" alt="new icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>&amp;screen=1">
      Create a new Pipe
     </a>
    </td>
   </tr>
   <tr>
    <td colspan="4">&nbsp;</td>
   </tr>
   <tr>
    <td><img src="<? print ICON_PIPES; ?>" alt="pipe icon" />&nbsp;<i>Pipes</i></td>
    <td><img src="<? print ICON_CHAINS; ?>" alt="chain icon" />&nbsp;<i>Chains</i></td>
    <td><img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;<i>Filters</i></td>
    <td style="text-align: center;"><i>Options</i></td>
   </tr>
<?

	    $result = $this->db->db_query("SELECT pipe_idx, pipe_name, pipe_chain_idx, pipe_active FROM "
	                                 ."shaper_pipes ORDER BY pipe_chain_idx ASC, pipe_name ASC");
	
	    while($row = $result->fetchrow()) {

	       $chain = $this->db->db_fetchSingleRow("SELECT chain_name FROM shaper_chains WHERE "
	                                            ."chain_idx='". $row->pipe_chain_idx ."'");
?>
   <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
    <td>
     <img src="<? print ICON_PIPES; ?>" alt="pipe icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=2&amp;idx=". $row->pipe_idx; ?>">
      <? print $row->pipe_name; ?>
     </a>
    </td>
    <td>
     <img src="<? print ICON_CHAINS; ?>" alt="chain icon" />
     <a href="<? print $this->parent->self ."?mode=1&amp;screen=1&amp;idx=". $row->pipe_chain_idx; ?>">
      <? print $chain->chain_name; ?>
     </a>
    </td>
    <td>
<?
	       $filters = $this->db->db_query("SELECT apf_filter_idx FROM shaper_assign_filters WHERE "
	                                      ."apf_pipe_idx='". $row->pipe_idx ."'");

	       while($filter = $filters->fetchRow()) {

		  $name = $this->db->db_fetchSingleRow("SELECT filter_name FROM shaper_filters WHERE "
		                                      ."filter_idx='". $filter->apf_filter_idx ."'");
?>
     <img src="<? print ICON_FILTERS; ?>" alt="filter icon" />
     <a href="<? print $this->parent->self ."?mode=8&amp;screen=2&amp;idx=". $filter->apf_filter_idx; ?>">
      <? print $name->filter_name; ?>
     </a>
<?
	       }
?>
    </td>
    <td style="text-align: center;">
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=3&amp;idx=". $row->pipe_idx ."&amp;name=". urlencode($row->pipe_name); ?>">
      <img src="<? print ICON_DELETE; ?>" alt="delete icon" />
     </a>
<?
	       if($row->pipe_active == "Y") {
?>
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=4&amp;idx=". $row->pipe_idx ."&amp;to=0"; ?>" title="Disable pipe <? print $row->pipe_name; ?>">
      <img src="<? print ICON_ACTIVE; ?>" alt="status icon" />
     </a>
<?
	       } else {
?>
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=4&amp;idx=". $row->pipe_idx ."&amp;to=1"; ?>" title="Enable pipe <? print $row->pipe_name; ?>">
      <img src="<? print ICON_INACTIVE; ?>" alt="status icon" />
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

               $this->parent->startTable("<img src=\"". ICON_PIPES ."\" alt=\"pipe icon\" />&nbsp;Create a new Pipe");

?>
  <form id="pipes" action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen; ?>&amp;saveit=1" method="post">
   <table style="width: 100%;" class="withborder2">
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PIPES; ?>" alt="pipe icon" />&nbsp;General
     </td>
    </tr>
    <tr>
     <td>Name:</td>
     <td><input type="text" name="pipe_name" size="30" /></td>
     <td>Specify a name for the pipe.</td>
    </tr>
    <tr>
     <td>Status:</td>
     <td><input type="radio" name="pipe_active" value="Y" checked="checked" />Active <input type="radio" name="pipe_active" value="N" />Inactive</td>
     <td>With this option the status of this chain is specified. Disabled pipes are ignored when reloading the ruleset.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PIPES; ?>" alt="pipe icon" />&nbsp;Parameters
     </td>
    </tr>
    <tr>
     <td>Chain:</td>
     <td>
      <select name="pipe_chain_idx">
<?
	       $result = $this->db->db_query("SELECT * FROM shaper_chains");
	       while($row = $result->fetchrow()) {
?>
       <option value="<? print $row->chain_idx; ?>"><? print $row->chain_name; ?></option>
<?
	       }
?>
      </select>
     </td>
     <td>Select a chain which the pipe will be assigned to. Only chains which use fallback service levels are able to contain pipes.</td>
    </tr>
    <tr>
     <td>Direction:</td>
     <td>
      Source
      <select name="pipe_direction">
       <option value="1">--&gt;</option>
       <option value="2">&lt;--</option>
       <option value="3" selected="selected">&lt;-&gt;</option>
      </select>
      Destination
     </td>
     <td>In which direction the network traffic should match the filters.</td>
    </tr>
    <tr>
     <td>Filters:</td>
     <td>
      <table class="noborder">
       <tr>
        <td>
	 <select size="10" name="avail[]" multiple="multiple">
	  <option value="">********* Unused *********</option>
<?
	       $result = $this->db->db_query("SELECT filter_idx, filter_name FROM shaper_filters ORDER BY filter_name ASC");
	       while($row = $result->fetchrow()) {
?>
       <option value="<? print $row->filter_idx; ?>"><? print $row->filter_name; ?></option>
<?
	       }
?>
         </select>
	</td>
        <td>&nbsp;</td>
	<td>
	 <input type="button" value="&gt;&gt;" onclick="moveOptions(document.forms['pipes'].elements['avail[]'], document.forms['pipes'].elements['used[]']);" /><br />
	 <input type="button" value="&lt;&lt;" onclick="moveOptions(document.forms['pipes'].elements['used[]'], document.forms['pipes'].elements['avail[]']);" />
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
     <td>Select the filters this pipe will shape.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PIPES; ?>" alt="pipe icon" />&nbsp;Bandwidth
     </td>
    </tr>
    <tr>
     <td>Service-Level:</td>
     <td>
      <select name="pipe_sl_idx">
<?
	       $result = $this->db->db_query("SELECT sl_idx, sl_name FROM shaper_service_levels ORDER BY sl_name ASC");
	       while($row = $result->fetchRow()) {
		  print "<option value=\"". $row->sl_idx ."\">". $row->sl_name ."</option>\n";
	       }
?>
      </select>
     </td>
     <td>Bandwidth limit for this pipe.</td>
    </tr>
    <tr>
     <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
     <td style="text-align: center;"><a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>" title="Back"><img src="<? print ICON_ARROW_LEFT; ?>" alt="arrow left icon" /></a></td>
     <td><input type="submit" value="Save" onclick="selectAll(document.forms['pipes'].elements['used[]']);" /></td>
     <td>Save settings.</td>
    </tr>
   </table>
  </form>
<?
               $this->parent->closeTable();
	    }
	    else {
	       
	       if($_POST['pipe_name'] != "") {

		  if(!$this->db->db_fetchSingleRow("SELECT pipe_idx FROM shaper_pipes WHERE pipe_name "
		                                  ."LIKE '". $_POST['pipe_name'] ."' AND "
						  ."pipe_chain_idx='". $_POST['pipe_chain_idx'] ."'")) {
		     
		     $max_pos = $this->db->db_fetchSingleRow("SELECT MAX(pipe_position) as pos FROM shaper_pipes "
		                                            ."WHERE pipe_chain_idx='". $_POST['pipe_chain_idx'] ."'");

		     $this->db->db_query("INSERT INTO shaper_pipes (pipe_name, pipe_chain_idx, pipe_sl_idx, "
		                        ."pipe_position, pipe_direction, pipe_active) "
					."VALUES ('". $_POST['pipe_name'] ."', '". $_POST['pipe_chain_idx'] ."', "
					."'". $_POST['pipe_sl_idx'] ."', '". ($max_pos->pos+1) ."', "
					."'". $_POST['pipe_direction'] ."', '". $_POST['pipe_active'] ."')");

		     if($_POST['used']) {
			$idx = $this->db->db_getid();
			foreach($_POST['used'] as $use)
			   if($use != "")
			      $this->db->db_query("INSERT INTO shaper_assign_filters (apf_pipe_idx, "
			                         ."apf_filter_idx) VALUES ('". $idx ."', '". $use ."')");
		     }
		     $this->parent->goBack();
		  }
		  else {
		     $this->parent->printError("<img src=\"". ICON_PIPES ."\" alt=\"pipe icon\" />&nbsp;Create a new Pipe", " A pipe with the name ". $_POST['pipe_name'] ." already exists. Please choose another name!");
		  }
	       }
	       else {
		  $this->parent->printError("<img src=\"". ICON_PIPES ."\" alt=\"pipe icon\" />&nbsp;Create a new Pipe", "Please enter a pipe name!");
	       }
	    }
	    break;

	 case 2:
	    
	    if(!isset($_GET['saveit'])) {
					
	       $pipe = $this->db->db_fetchSingleRow("SELECT pipe_name, pipe_chain_idx, pipe_sl_idx, "
	                                           ."pipe_direction, pipe_active FROM shaper_pipes "
						   ."WHERE pipe_idx='". $_GET['idx'] ."'");

               $this->parent->startTable("<img src=\"". ICON_PIPES ."\" alt=\"pipe icon\" />&nbsp;Modfiy pipe ". $pipe->pipe_name);

?>
  <form id="pipes" action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;idx=". $_GET['idx'] ."&amp;namebefore=". urlencode($pipe->pipe_name); ?>&amp;saveit=1" method="post">
   <table style="width: 100%;" class="withborder2">
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PIPES; ?>" alt="pipe icon" />&nbsp;General
     </td>
    </tr>
    <tr>
     <td>Name:</td>
     <td><input type="text" name="pipe_name" size="30" value="<? print $pipe->pipe_name; ?>" /></td>
     <td>Specify a name for the pipe.</td>
    </tr>
    <tr>
     <td>Status:</td>
     <td><input type="radio" name="pipe_active" value="Y" <? if($pipe->pipe_active == "Y") print "checked=\"checked\""; ?> />Active <input type="radio" name="pipe_active" value="N" <? if($pipe->pipe_active != "Y") print "checked=\"checked\""; ?> />Inactive</td>
     <td>With this option the status of this chain is specified. Disabled pipes are ignored when reloading the ruleset.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PIPES; ?>" alt="pipe icon" />&nbsp;Parameters
     </td>
    </tr>
    <tr>
     <td>Chain:</td>
     <td>
      <select name="pipe_chain_idx">
<?
	       $result = $this->db->db_query("SELECT * FROM shaper_chains");
	       while($row = $result->fetchrow()) {
		  print "<option value='". $row->chain_idx ."'";
		  if($row->chain_idx == $pipe->pipe_chain_idx)
		     print " selected=\"selected\"";
		  print ">". $row->chain_name ."</option>\n";
	       }
?>
      </select>
     </td>
     <td>Select a chain which the pipe will be assigned to. Only chains which use fallback service levels are able to contain pipes.</td>
    </tr>
    <tr>
     <td>Direction:</td>
     <td>
      Source
      <select name="pipe_direction">
       <option value="1" <? if($pipe->pipe_direction == 1) print "selected=\"selected\""; ?>>--&gt;</option>
       <option value="2" <? if($pipe->pipe_direction == 2) print "selected=\"selected\""; ?>>&lt;--</option>
       <option value="3" <? if($pipe->pipe_direction == 3) print "selected=\"selected\""; ?>>&lt;-&gt;</option>
      </select>
      Destination
     </td>
     <td>In which direction the network traffic should match the filters.</td>
    </tr>
    <tr>
     <td>Filters:</td>
     <td>
      <table class="noborder">
       <tr>
        <td>
	 <select size="10" name="avail[]" multiple="multiple">
	  <option value="">********* Unused *********</option>
<?
	       $result = $this->db->db_query("SELECT filter_idx, filter_name FROM shaper_filters ORDER BY filter_name ASC");
	       while($row = $result->fetchrow()) {

		  if(!$this->db->db_fetchSingleRow("SELECT apf_idx FROM shaper_assign_filters WHERE "
		                                  ."apf_pipe_idx='". $_GET['idx'] ."' AND "
						  ."apf_filter_idx='". $row->filter_idx ."'")) {
?>
       <option value="<? print $row->filter_idx; ?>"><? print $row->filter_name; ?></option>
<?
		  }
	       }
?>
         </select>
	</td>
        <td>&nbsp;</td>
	<td>
	 <input type="button" value="&gt;&gt;" onclick="moveOptions(document.forms['pipes'].elements['avail[]'], document.forms['pipes'].elements['used[]']);" /><br />
	 <input type="button" value="&lt;&lt;" onclick="moveOptions(document.forms['pipes'].elements['used[]'], document.forms['pipes'].elements['avail[]']);" />
	</td>
	<td>&nbsp;</td>
	<td>
         <select size="10" name="used[]" multiple="multiple">
          <option value="">********* Used *********</option>
<?
	       $result = $this->db->db_query("SELECT filter_idx, filter_name FROM shaper_filters ORDER BY filter_name ASC");
	       while($row = $result->fetchrow()) {
		  if($this->db->db_fetchSingleRow("SELECT apf_idx FROM shaper_assign_filters "
		                                 ."WHERE apf_pipe_idx='". $_GET['idx'] ."' AND "
						 ."apf_filter_idx='". $row->filter_idx ."'")) {
?>
       <option value="<? print $row->filter_idx; ?>"><? print $row->filter_name; ?></option>
<?
		  }
	       }
?>
         </select>
        </td>
       </tr>
      </table>
     </td>
     <td>Select the filters this pipe will shape.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_PIPES; ?>" alt="pipe icon" />&nbsp;Bandwidth
     </td>
    </tr>
    <tr>
     <td>Service-Level:</td>
     <td>
      <select name="pipe_sl_idx">
<?
	       $result = $this->db->db_query("SELECT sl_idx, sl_name FROM shaper_service_levels ORDER BY sl_name ASC");
	       while($row = $result->fetchRow()) {
		  print "<option value=\"". $row->sl_idx ."\"";
		  if($pipe->pipe_sl_idx == $row->sl_idx) 
		     print " selected=\"selected\"";	
		  print ">". $row->sl_name ."</option>\n";
	       }
?>
      </select>
     </td>
     <td>Bandwidth limit for this pipe.</td>
    </tr>
    <tr>
     <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
     <td style="text-align: center;"><a href="<? print $this->parent->self ."?mode=". $this->parent->mode; ?>" title="Back"><img src="<? print ICON_ARROW_LEFT; ?>" alt="arrow left icon" /></a></td>
     <td><input type="submit" value="Save" onclick="selectAll(document.forms['pipes'].elements['used[]']);" /></td>
     <td>Save settings.</td>
    </tr>
   </table>
  </form>
<?
               $this->parent->closeTable();

	    }
	    else {
	       
	       if($_POST['pipe_name'] != "") {

		  if(!$this->db->db_fetchSingleRow("SELECT pipe_idx FROM shaper_pipes WHERE pipe_name LIKE "
		                                  ."'". $_POST['pipe_name'] ."' AND pipe_chain_idx="
						  ."'". $_POST['pipe_chain_idx'] ."'") 
                     || $_POST['pipe_name'] == $_GET['namebefore']) {

		     $this->db->db_query("UPDATE shaper_pipes SET pipe_name='". $_POST['pipe_name'] ."', "
		                        ."pipe_chain_idx='". $_POST['pipe_chain_idx'] ."', "
					."pipe_sl_idx='". $_POST['pipe_sl_idx'] ."', "
					."pipe_direction='". $_POST['pipe_direction'] ."', "
					."pipe_active='". $_POST['pipe_active'] ."' "
					."WHERE pipe_idx='". $_GET['idx'] ."'");

		     if($_POST['used']) {
			$this->db->db_query("DELETE FROM shaper_assign_filters WHERE "
			                   ."apf_pipe_idx='". $_GET['idx'] ."'");
			
			foreach($_POST['used'] as $use)
			   if($use != "")
			      $this->db->db_query("INSERT INTO shaper_assign_filters (apf_pipe_idx, "
			                         ."apf_filter_idx) VALUES ('". $_GET['idx'] ."', '". $use ."')");
		     }
		     $this->parent->goBack();
		  }
		  else {
		     $this->parent->printError("<img src=\"". ICON_PIPES ."\" alt=\"pipe icon\" />&nbsp;Modify pipe", " A pipe with the name ". $_POST['pipe_name'] ." already exists. Please choose another name!");
		  }
	       }
	       else {
		  $this->parent->printError("<img src=\"". ICON_PIPES ."\" alt=\"pipe icon\" />&nbsp;Modify pipe", "Please enter a pipe name!");
	       }
	    }
	    break;

	 case 3:

	    if(!isset($_GET['doit'])) {
	       $this->parent->printYesNo("Delete Pipe", "Do really want to delete pipe ". $_GET['name'] ."?");
	    } else {
	       if($_GET['idx']) {
		  $this->db->db_query("DELETE FROM shaper_pipes WHERE pipe_idx='". $_GET['idx'] ."'");
		  $this->db->db_query("DELETE FROM shaper_assign_filters WHERE apf_pipe_idx='". $_GET['idx'] ."'");
               }
	       $this->parent->goBack();
	    }
	    break;

	 case 4:

	    if(isset($_GET['idx'])) {

	       if($_GET['to'] == 1)
		  $this->db->db_query("UPDATE shaper_pipes SET pipe_active='Y' WHERE pipe_idx='". $_GET['idx'] ."'");
	       elseif($_GET['to'] == 0)
	          $this->db->db_query("UPDATE shaper_pipes SET pipe_active='N' WHERE pipe_idx='". $_GET['idx'] ."'");

	    }
	    $this->parent->goBack();
	    break;

      }

   } // showHtml()

}

?>
