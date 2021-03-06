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

class MSMONITOR {

   var $db;
   var $parent;

   /* Class constructor */
   function MSMONITOR($parent)
   {
      $this->db = $parent->db;
      $this->parent = $parent;
   } // MSMONITOR()

   /* interface output */
   function showHtml()
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_show_monitor")) {

	 $this->parent->printError("<img src=\"". ICON_HOME ."\" alt=\"home icon\" />&nbsp;Monitoring", "You do not have enough permissions to access this module!");
	 return 0;

      }

      $vars = Array();

      $vars['graphmode'] = 0;
      $vars['scalemode'] = "kbit";

      if(isset($_POST['graphmode']))
	 $vars['graphmode'] = $_POST['graphmode'];
      if(isset($_GET['show']))
	 $vars['show']      = $_GET['show'];
      if(isset($_POST['showchain']))
	 $vars['showchain'] = $_POST['showchain'];
      if(isset($_POST['showif']))
	 $vars['showif']    = $_POST['showif'];
      if(isset($_POST['scalemode']))
         $vars['scalemode'] = $_POST['scalemode'];

      // graph URL
      $image_loc = SHAPER_WEB ."/shaper_stats.php?show=". $vars['show'] ."&graphmode=". $vars['graphmode'];

      switch($vars['show']) {
	 case 'chains':
	    $view = "Chains";
	    break;
	 case 'pipes':
	    $view = "Pipes";
	    if(!isset($vars['showchain']))
	       $showchain = $this->getFirstChain();
	    else
	       $showchain = $vars['showchain'];
	    $image_loc.= "&showchain=". $showchain;
	    break;
	   
	 case 'bandwidth':
	    $view = "Bandwidth";
	    break;
      }

      $vars['incoming'] = $this->getInterface("incoming");
      $vars['outgoing'] = $this->getInterface("outgoing");
	
      /* If no interface is specified, use the incoming interface */
      if(!isset($vars['showif'])) {

         // If the incoming interface is not specified, we use the
	 // outgoing interface

         if($vars['incoming'] != "")
            $vars['showif'] = $vars['incoming'];
	 else
	    $vars['showif'] = $vars['outgoing'];

      }

      $image_loc.= "&showif=". $vars['showif'];

      /* Start HTML Output */
      switch($vars['show']) {
	 case 'chains':
	    $this->parent->startTable("<img src=\"". ICON_CHAINS ."\" alt=\"chain icon\" />&nbsp;Traffic Monitoring - ". $view);
	    break;
	 case 'pipes':
	    $this->parent->startTable("<img src=\"". ICON_PIPES ."\" alt=\"pipe icon\" />&nbsp;Traffic Monitoring - ". $view);
	    break;
	 case 'bandwidth':
	    $this->parent->startTable("<img src=\"". ICON_BANDWIDTH ."\" alt=\"target icon\" />&nbsp;Traffic Monitoring - ". $view);
	    break;
      }

      $image_loc.= "&scalemode=". $vars['scalemode'];
?>
  <form id="monitor" action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&show=". $vars['show']; ?>" method="post">
  <table style="width: 100%;" class="withborder">
   <tr>
    <td class="tablehead" style="width: 180px;">
     Graph Options
    </td>
    <td style="text-align: center; width: 900px; height: 350px" rowspan="10">
     <img src="<? print $image_loc ."&uniqid=". mktime(); ?>" id="monitor_image" alt="monitor image" />
     <script type="text/javascript">
   	function updateimage()
	{
		if(document.forms['monitor'].reload.checked) {
			uniq = new Date();
			uniq = "&uniqid="+uniq.getTime();
			document.forms['monitor'].monitor_image.src="<? print $image_loc; ?>&uniq"+uniq;
		}
		setTimeout("updateimage()", 5000);
	}
	setTimeout("updateimage()", 5000);
     </script>
    </td>
   </tr>
   <tr>
    <td>&nbsp;</td>
   </tr>
<?
      /* Traffic direction selection is not necessarry in bandwidth display */
      if($vars['show'] != "bandwidth") {
?>
   <tr>
    <td>
     <table class="noborder" style="width: 100%; text-align: center;">
      <tr>
       <td>
        Traffic direction:
       </td>
      </tr>
      <tr>
       <td>
<?

         if($this->parent->getOption("in_interface") != "") {
?>
        <input type="radio" name="showif" value="<? print $vars['incoming']; ?>" <? if($vars['showif'] == $vars['incoming']) print "checked=\"checked\"";?> onclick="if(this.blur) this.blur();" class="radio" />Incoming<br /> 
<?
         }
         if($this->parent->getOption("out_interface") != "") {
?>
        <input type="radio" name="showif" value="<? print $vars['outgoing']; ?>" <? if($vars['showif'] == $vars['outgoing']) print "checked=\"checked\"";?> onclick="if(this.blur) this.blur();" class="radio" />Outgoing<br /> 
<?
	}
?>
       </td>
      </tr>
     </table>
    </td>
   </tr>
<?
      }

      /* Chain selector for pipe view */
      if($vars['show'] == "pipes") {

	 $chains = $this->db->db_query("SELECT chain_idx, chain_name FROM shaper_chains WHERE chain_active='Y' ORDER BY chain_position ASC");
?>
   <tr>
    <td style="text-align: center;">
     <table class="noborder" style="width: 100%; text-align: center;">
      <tr>
       <td>
        Chain:
       </td>
      </tr>
      <tr>
       <td>
        <select name="showchain">
<?
	 while($chain = $chains->fetchRow()) {
?>
         <option value="<? print $chain->chain_idx; ?>" <? if($showchain == $chain->chain_idx) print "selected=\"selected\""; ?>><? print $chain->chain_name; ?></option>
<?
	 }
?>
        </select>
       </td>
      </tr>
     </table>
    </td>
   </tr>
<?
      }     

      if($vars['show'] == "pipes" || $vars['show'] == "chains") {
?>
   <tr>
    <td>
     <table class="noborder" style="width: 100%; text-align: center">
      <tr>
       <td>
        Graph Mode:
       </td>
      </tr>
      <tr>
       <td>
        <input type="radio" name="graphmode" value="0" <? if($vars['graphmode'] == 0) print "checked=\"checked\""; ?> onclick="if(this.blur) this.blur();" class="radio" />Accumulated Lines<br />
        <input type="radio" name="graphmode" value="1" <? if($vars['graphmode'] == 1) print "checked=\"checked\""; ?> onclick="if(this.blur) this.blur();" class="radio" />Lines<br />
        <input type="radio" name="graphmode" value="2" <? if($vars['graphmode'] == 2) print "checked=\"checked\""; ?> onclick="if(this.blur) this.blur();" class="radio" />Bars<br />
        <input type="radio" name="graphmode" value="3" <? if($vars['graphmode'] == 3) print "checked=\"checked\""; ?> onclick="if(this.blur) this.blur();" class="radio" />Pie plot<br />
       </td>
      </tr>
     </table>
    </td>
   </tr>
<?
      }
?>
   <tr>
    <td style="text-align: center;">
     <table class="noborder" style="width: 100%; text-align: center">
      <tr>
       <td>Scale:</td>
      </tr>
      <tr>
       <td>
        <select name="scalemode">
         <option value="bit" <? if($vars['scalemode'] == "bit") print "selected=\"selected\""; ?>>bit/s</option>
         <option value="byte" <? if($vars['scalemode'] == "byte") print "selected=\"selected\""; ?>>byte/s</option>
         <option value="kbit" <? if($vars['scalemode'] == "kbit") print "selected=\"selected\""; ?>>kbit/s</option>
         <option value="kbyte" <? if($vars['scalemode'] == "kbyte") print "selected=\"selected\""; ?>>kbyte/s</option>
         <option value="mbit" <? if($vars['scalemode'] == "mbit") print "selected=\"selected\""; ?>>mbit/s</option>
         <option value="mbyte" <? if($vars['scalemode'] == "mbyte") print "selected=\"selected\""; ?>>mbyte/s</option>
        </select>
       </td>
      </tr>
     </table>
    </td>
   </tr>
   <tr>
    <td style="text-align: center;">
     <input type="submit" value="Reload Graph" />
    </td>
   </tr>
   <tr>
    <td style="text-align: center;">
     <input type="checkbox" name="reload" value="Y" checked="checked" onclick="if(this.blur) this.blur();" class="radio" />Auto reload
    </td>
   </tr>
  </table>
  </form>
<?

      $this->parent->closeTable();

   } // showHtml()

   function getInterface($if)
   {
      switch($if) {

	 case "incoming":

	    if($this->parent->getOption("imq_if") == "Y")
	       return $this->parent->getOption("in_interface");
	    else
	       return $this->parent->getOption("out_interface");
	    break;

	 case "outgoing":

	    if($this->parent->getOption("imq_if") == "Y")
	       return $this->parent->getOption("out_interface");
	    else
	       return $this->parent->getOption("in_interface");

	    break;

      }

   } // getInterfaces()
	
   function getFirstChain()
   {
      $chain = $this->db->db_fetchSingleRow("SELECT chain_idx FROM shaper_chains WHERE chain_active='Y' ORDER BY chain_position ASC LIMIT 0,1");
      return $chain->chain_idx;

   } // getFirstChain()

}

?>
