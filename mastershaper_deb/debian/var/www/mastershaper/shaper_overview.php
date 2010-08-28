<?

/***************************************************************************
 *
 * Copyright (c) by Andreas Unterkircher
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

class MSOVERVIEW {

   var $db;
   var $parent;

   /* Class constructor */
   function MSOVERVIEW($parent)
   {
      $this->db = $parent->db;
      $this->parent = $parent;
   } //MSOVERVIEW()

   /* interface output */
   function showHtml()
   {
      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_show_rules")) {

	 $this->parent->printError("<img src=\"". ICON_HOME ."\" alt=\"home icon\" />&nbsp;MasterShaper Ruleset Overview", "You do not have enough permissions to access this module!");
	 return 0;

      }

      if(!isset($this->parent->screen))
         $this->parent->screen = 0;

      $this->parent->startTable("<img src=\"". ICON_HOME ."\" alt=\"home icon\" />&nbsp;MasterShaper Ruleset Overview");

      switch($this->parent->screen) {
         default:
?>
     <table style="width: 100%;" class="withborder">
      <tr>
       <td colspan="4" style="text-align: center;">
	<img src="<? print ICON_CHAINS; ?>" alt="chains icon" />
	<a href="<? print $this->parent->self; ?>?mode=1&amp;screen=1" title="Add a new chain">Add a new chain</a>
	&nbsp;
	<img src="<? print ICON_PIPES; ?>" alt="pipes icon" />
	<a href="<? print $this->parent->self; ?>?mode=2&amp;screen=1" title="Add a new pipe">Add a new pipe</a>
	&nbsp;
	<img src="<? print ICON_FILTERS; ?>" alt="filter icon" />
	<a href="<? print $this->parent->self; ?>?mode=8&amp;screen=1" title="Add a new filter">Add a new filter</a>
       </td>
      </tr>
<?
	    $sum_bw_in = 0;
	    $sum_bw_out = 0;

	    $chains = $this->db->db_query("SELECT chain_idx, chain_name, chain_position, chain_sl_idx, chain_src_target, "
	                                 ."chain_dst_target, chain_direction, chain_fallback_idx "
					 ."FROM shaper_chains WHERE chain_active='Y' ORDER BY chain_position ASC");

	    while($chain = $chains->fetchRow()) {

	       $sum_sl_in = 0;
	       $sum_sl_out = 0;
?>
      <script type="text/javascript">
      <!--
         staticTip.tips.tipChain<? print $chain->chain_idx; ?> = new Array(20, 5, 150, '<i><font color=\"#000000\" style=\"font-size: 12px\";>' +
            '<img src=\"<? print ICON_CHAINS; ?>\" style=\"text-align: center;\" alt=\"chain icon\" />&nbsp;' + 
            'Chain <? print $chain->chain_name; ?></font></i>' +
            '<br /><br />' +
            '<table>' +
            '<tr><td>Source:</td><td style=\"white-space: nowrap;\"><? print $this->parent->getTargetName($chain->chain_src_target); ?></td></tr>' +
            '<tr><td>Destination:</td><td style=\"white-space: nowrap;\"><? print $this->parent->getTargetName($chain->chain_dst_target); ?></td></tr>' +
            '<tr><td>Direction:</td><td style=\"white-space: nowrap;\"><? print $this->parent->getChainDirectionName($chain->chain_direction); ?></td></tr>' +
            '<tr><td>Fallback:</td><td style=\"white-space: nowrap;\"><? print $this->parent->getServiceLevelName($chain->chain_fallback_idx); ?></td></tr>' +
            '</table>');
      -->
      </script>
      <tr>
       <td colspan="4">&nbsp;</td>
      </tr>
      <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
       <td colspan="2">
        <img src="<? print ICON_CHAINS; ?>" alt="chain icon" />&nbsp;
        <a href="<? print $this->parent->self; ?>?mode=1&amp;screen=1&amp;idx=<? print $chain->chain_idx; ?>" title="Modify chain <? print $chain->chain_name; ?>" onmouseover="staticTip.show('tipChain<? print $chain->chain_idx; ?>');" onmouseout="staticTip.hide();"><? print $chain->chain_name; ?></a>,&nbsp;
<?
	       if($chain->chain_sl_idx != 0) {

	          if($chain->chain_fallback_idx != 0) {

		     if($chain_sl = $this->db->db_fetchSingleRow("SELECT * FROM shaper_service_levels WHERE sl_idx='". $chain->chain_sl_idx ."'")) {

			switch($this->parent->getOption("classifier")) {

			   case 'HTB':
?>
	<a href="<? print $this->parent->self; ?>?mode=3&amp;screen=2&amp;idx=<? print $chain->chain_sl_idx; ?>" title="Modify service level <? print $chain_sl->sl_name; ?>">
	 <? print "[". $chain_sl->sl_htb_bw_in_rate ."/". $chain_sl->sl_htb_bw_out_rate ."kbit] ". $chain_sl->sl_name; ?>
	</a>
<?
			      $sum_bw_in+=  $chain_sl->sl_htb_bw_in_rate;
			      $sum_bw_out+= $chain_sl->sl_htb_bw_out_rate;
			      break;

			   case 'HFSC':
?>
        <a href="<? print $this->parent->self; ?>?mode=3&amp;screen=2&amp;idx=<? print $chain->chain_sl_idx; ?>" title="Modify service level <? print $chain_sl->sl_name; ?>">
<?

			      print "[In: ";
	   
			      if($chain_sl->sl_hfsc_in_dmax)
				 print $chain_sl->sl_hfsc_in_dmax ."ms/";
			      if($chain_sl->sl_hfsc_in_rate)
				 print $chain_sl->sl_hfsc_in_rate ."kbit";
	     
			      print ", Out: ";
	
			      if($chain_sl->sl_hfsc_out_dmax)
				 print $chain_sl->sl_hfsc_out_dmax ."ms/";
			      if($chain_sl->sl_hfsc_out_rate)
				 print $chain_sl->sl_hfsc_out_rate ."kbit";
	     
			      print "] ". $chain_sl->sl_name; 
?>
	</a>
<?
			      $sum_bw_in+=  $chain_sl->sl_hfsc_in_rate;
			      $sum_bw_out+= $chain_sl->sl_hfsc_out_rate;
			      break;

			   case 'CBQ':
?>
        <a href="<? print $this->parent->self; ?>?mode=3&amp;screen=2&amp;idx=<? print $chain->chain_sl_idx; ?>" title="Modify service level <? print $chain_sl->sl_name; ?>">
<?
			      print "[". $chain_sl->sl_cbq_in_rate ."/". $chain_sl->sl_cbq_out_rate ."kbit] ". $chain_sl->sl_name;

			      $sum_bw_in+= $chain_sl->sl_cbq_in_rate;
			      $sum_bw_out+= $chain_sl->sl_cbq_out_rate;
			      break;

			}	
		     }
		  }
		  else {
?>
        No Pipes
<?
                  }
	       }
	       else {
?>
        Ignore QoS
<?
	       }
?>
       </td>
       <td style="text-align: center;">
        <a href="<? print $this->parent->self."?mode=". $this->parent->mode; ?>&amp;screen=1&amp;chain_idx=<? print $chain->chain_idx; ?>&amp;to=0"><img src="<? print ICON_CHAINS_ARROW_DOWN; ?>" alt="Move chain down" /></a>
        <a href="<? print $this->parent->self."?mode=". $this->parent->mode; ?>&amp;screen=1&amp;chain_idx=<? print $chain->chain_idx; ?>&amp;to=1"><img src="<? print ICON_CHAINS_ARROW_UP; ?>" alt="Move chain up" /></a>
       </td>
      </tr>
<?	

               if($chain->chain_sl_idx != 0 && $chain->chain_fallback_idx != 0) {
 
		  $pipes = $this->db->db_query("SELECT * FROM shaper_pipes WHERE pipe_chain_idx='". $chain->chain_idx ."' "
					      ."AND pipe_active='Y' ORDER BY pipe_position ASC");

		  while($pipe = $pipes->fetchRow()) {

		     $sl = $this->db->db_fetchSingleRow("SELECT * FROM shaper_service_levels WHERE sl_idx='". $pipe->pipe_sl_idx ."'");
?>
      <script type="text/javascript">
      <!--
         staticTip.tips.tipPipe<? print $pipe->pipe_idx; ?> = new Array(20, 5, 150, '<i><font color=\"#000000\" style=\"font-size: 12px\";>' +
            '<img src=\"<? print ICON_PIPES; ?>\" style=\"text-align: center;\" alt=\"pipe icon\" />&nbsp;' + 
            'Chain <? print $pipe->pipe_name; ?></font></i>' +
            '<br /><br />' +
            '<table>' +
            '<tr><td>Direction:</td><td style=\"white-space: nowrap;\"><? print $this->parent->getPipeDirectionName($pipe->pipe_direction); ?></td></tr>' +
            '</table>');
      -->
      </script>
      <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
       <td>
        <img src="images/tree_end.gif" alt="tree" />
        <img src="<? print ICON_PIPES; ?>" alt="pipes icon" />&nbsp;
	<a href="<? print $this->parent->self; ?>?mode=2&amp;screen=2&amp;idx=<? print $pipe->pipe_idx; ?>" title="Modify pipe <? print $pipe->pipe_name; ?>" onmouseover="staticTip.show('tipPipe<? print $pipe->pipe_idx; ?>');" onmouseout="staticTip.hide();">
	 <? print $pipe->pipe_name; ?>
	</a>
       </td>
       <td>
        &nbsp;<img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />
<?
		     /* Display the service level of this chain */
		     switch($this->parent->getOption("qdisc")) {

			default:
			case 'HTB':
?>
        <script type="text/javascript">
        <!--
           staticTip.tips.tipSL<? print $pipe->pipe_sl_idx; ?> = new Array(20, 5, 150, '<i><font color=\"#000000\" style=\"font-size: 12px\";>' +
               '<img src=\"<? print ICON_SERVICELEVELS; ?>\" style=\"text-align: center;\" alt=\"servicelevel icon\" />&nbsp;Service-Level <? print $sl->sl_name; ?></font></i>' +
               '<br /><br />' +
               '<table>' +
	       '<tr><td style="white-space: nowrap;">In-Rate:</td><td style="white-space: nowrap;"><? print $sl->sl_htb_bw_in_rate; ?>kbit/s</td></tr>' +
               '<tr><td style="white-space: nowrap;">In-Ceil:</td><td style="white-space: nowrap;"><? print $sl->sl_htb_bw_in_ceil; ?>kbit/s</td></tr>' +
	       '<tr><td style="white-space: nowrap;">Out-Rate:</td><td style="white-space: nowrap;"><? print $sl->sl_htb_bw_out_rate; ?>kbit/s</td></tr>' +
	       '<tr><td style="white-space: nowrap;">Out-Ceil:</td><td style="white-space: nowrap;"><? print $sl->sl_htb_bw_out_ceil; ?>kbit/s</td></tr>' +
	       '<tr><td style="white-space: nowrap;">Priority:</td><td style="white-space: nowrap;"><? print $this->parent->getPriorityName($sl->sl_htb_priority); ?></td></tr>' +
	       '</table>');
        -->
        </script>
	<a href="<? print $this->parent->self; ?>?mode=3&amp;screen=2&amp;idx=<? print $pipe->pipe_sl_idx; ?>" title="Modify service level <? print $sl->sl_name; ?>" onmouseover="staticTip.show('tipSL<? print $pipe->pipe_sl_idx; ?>');" onmouseout="staticTip.hide();">
	 <? print $sl->sl_name; ?>
	</a>
<?	
			   break;

			case 'HFSC':
?>
        <script type="text/javascript">
        <!--
           staticTip.tips.tipSL<? print $pipe->pipe_sl_idx; ?> = new Array(20, 5, 150, '<i><font color=\"#000000\" style=\"font-size: 12px\";>' +
               '<img src=\"<? print ICON_SERVICELEVELS; ?>\" style=\"text-align: center;\" alt=\"servicelevel icon\" />&nbsp;Service-Level <? print $sl->sl_name; ?></font></i>' +
               '<br /><br />' +
               '<table>' +
	       '<tr><td style="white-space: nowrap;">In-Delay max:</td><td style="white-space: nowrap;"><? print $sl->sl_hfsc_in_dmax; ?>ms</td></tr>' +
               '<tr><td style="white-space: nowrap;">In-Rate (guar.):</td><td style="white-space: nowrap;"><? print $sl->sl_hfsc_in_rate; ?>kbit/s</td></tr>' +
               '<tr><td style="white-space: nowrap;">In-Rate (max.):</td><td style="white-space: nowrap;"><? print $sl->sl_hfsc_in_ulrate; ?>kbit/s</td></tr>' +
	       '<tr><td style="white-space: nowrap;">Out-Delay max:</td><td style="white-space: nowrap;"><? print $sl->sl_hfsc_out_dmax; ?>ms</td></tr>' +
	       '<tr><td style="white-space: nowrap;">Out-Rate (guar.):</td><td style="white-space: nowrap;"><? print $sl->sl_hfsc_out_rate; ?>kbit/s</td></tr>' +
	       '<tr><td style="white-space: nowrap;">Out-Rate (max.):</td><td style="white-space: nowrap;"><? print $sl->sl_hfsc_out_ulrate; ?>kbit/s</td></tr>' +
	       '</table>');
        -->
        </script>
	<a href="<? print $this->parent->self; ?>?mode=3&amp;screen=2&amp;idx=<? print $pipe->pipe_sl_idx; ?>" title="Modify service level <? print $sl->sl_name; ?>" onmouseover="staticTip.show('tipSL<? print $pipe->pipe_sl_idx; ?>');" onmouseout="staticTip.hide();">
	 <? print $sl->sl_name; ?>
        </a>
<?
			   break;

			case 'CBQ':
?>
        <script type="text/javascript">
        <!--
           staticTip.tips.tipSL<? print $pipe->pipe_sl_idx; ?> = new Array(20, 5, 150, '<i><font color=\"#000000\" style=\"font-size: 12px\";>' +
               '<img src=\"<? print ICON_SERVICELEVELS; ?>\" style=\"text-align: center;\" alt=\"servicelevel icon\" />&nbsp;Service-Level <? print $sl->sl_name; ?></font></i>' +
               '<br /><br />' +
               '<table>' +
               '<tr><td style="white-space: nowrap;">In-Rate:</td><td style="white-space: nowrap;"><? print $sl->sl_cbq_in_rate; ?>kbit/s</td></tr>' +
               '<tr><td style="white-space: nowrap;">In-Prio:</td><td style="white-space: nowrap;"><? print $this->parent->getPriorityName($sl->sl_cbq_in_priority); ?></td></tr>' +
	       '<tr><td style="white-space: nowrap;">Out-Rate:</td><td style="white-space: nowrap;"><? print $sl->sl_cbq_out_rate; ?>kbit/s</td></tr>' +
	       '<tr><td style="white-space: nowrap;">Out-Prio:</td><td style="white-space: nowrap;"><? print $this->parent->getPriorityName($sl->sl_cbq_out_priority); ?></td></tr>' +
	       '</table>');
        -->
        </script>
	<a href="<? print $this->parent->self; ?>?mode=3&amp;screen=2&amp;idx=<? print $pipe->pipe_sl_idx; ?>" title="Modify service level <? print $sl->sl_name; ?>" onmouseover="staticTip.show('tipSL<? print $pipe->pipe_sl_idx; ?>');" onmouseout="staticTip.hide();">
	 <? print $sl->sl_name; ?>
        </a>
<? 
			   break;
		     }
?>
       </td>
       <td style="text-align: center;">
        <a href="<? print $this->parent->self."?mode=". $this->parent->mode; ?>&amp;screen=2&amp;pipe_idx=<? print $pipe->pipe_idx; ?>&amp;to=0"><img src="<? print ICON_PIPES_ARROW_DOWN; ?>" alt="Move pipe down" /></a>
        <a href="<? print $this->parent->self."?mode=". $this->parent->mode; ?>&amp;screen=2&amp;pipe_idx=<? print $pipe->pipe_idx; ?>&amp;to=1"><img src="<? print ICON_PIPES_ARROW_UP; ?>" alt="Move pipe up" /></a>
       </td>
      </tr>
<? 
	$filters = $this->db->db_query("SELECT a.filter_idx as filter_idx, a.filter_name as filter_name FROM shaper_filters a, shaper_assign_filters b WHERE b.apf_pipe_idx='". $pipe->pipe_idx ."' AND b.apf_filter_idx=a.filter_idx AND a.filter_active='Y'");
	while($filter = $filters->fetchRow()) {
		
?>
      <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
       <td colspan="2">
        &nbsp;&nbsp;&nbsp;
	<img src="images/tree_end.gif" alt="tree" />
	<img src="<? print ICON_FILTERS; ?>" alt="filter icon" />&nbsp;
        <a href="<? print $this->parent->self; ?>?mode=8&amp;screen=2&amp;idx=<? print $filter->filter_idx; ?>" title="Modify filter <? print $filter->filter_name; ?>">
         <? print $filter->filter_name; ?>
	</a>
       </td>
       <td> 
        &nbsp; 
       </td>
      </tr>
<?
        
		  }

		  switch($this->parent->getOption("qdisc")) {

		     default:
		     case 'HTB':
			$sum_sl_in += $sl->sl_htb_bw_in_rate;
			$sum_sl_out += $sl->sl_htb_bw_out_rate;
			break;

		     case 'HFSC':
			$sum_sl_in += $sl->sl_hfsc_in_rate;
			$sum_sl_out += $sl->sl_hfsc_out_rate;
			break;

		     case 'CBQ':
			$sum_sl_in += $sl->sl_cbq_in_rate;
			$sum_sl_out += $sl->sl_cbq_out_rate;
			break;

		  }
	       }

	       if(isset($chain_sl)) {

		  switch($this->parent->getOption("qdisc")) {

		     default:
		     case 'HTB':
			$chain_sl_in_rate  = $chain_sl->sl_htb_bw_in_rate;
			$chain_sl_out_rate = $chain_sl->sl_htb_bw_out_rate;
			break;

		     case 'HFSC':
			$chain_sl_in_rate  = $chain_sl->sl_hfsc_in_rate;
			$chain_sl_out_rate = $chain_sl->sl_hfsc_out_rate;
			break;

		     case 'CBQ':

			$chain_sl_in_rate  = $chain_sl->sl_cbq_in_rate;
			$chain_sl_out_rate = $chain_sl->sl_cbq_out_rate;
			break; 
		  }
	       }

	       if(isset($chain_sl_in_rate) && $chain_sl_in_rate < $sum_sl_in) {
?>
      <tr>
       <td colspan="3" class="sysmessage" style="text-align: right;">
        The summary of all guaranteed inbound rates is higher (<? print $sum_sl_in; ?>kbit/s) then the available chain rate (<? print $chain_sl_in_rate; ?>kbit/s)!
       </td>
      </tr>
<?
	       }

	       if(isset($chain_sl_out_rate) && $chain_sl_out_rate < $sum_sl_out) {
?>
      <tr>
       <td colspan="3" style="text-align: right;" class="sysmessage">
        The summary of all guaranteed outbound rates is higher (<? print $sum_sl_out; ?>kbit/s) then the available chain rate (<? print $chain_sl_out_rate; ?>kbit/s)!
       </td>
      </tr>
<?
	       }
	    }
         }
?>
      <tr>
       <td colspan="3">
        &nbsp;
       </td>
      </tr>
<?
	 if($this->parent->getOption("bw_inbound") < $sum_bw_in) {
?>
      <tr>
       <td colspan="3" class="sysmessage" style="text-align: right;">
        &nbsp;The summary of all guaranteed inbound rates is higher (<? print $sum_bw_in; ?>kbit/s) then the available bandwidth rate (<? print $this->parent->getOption("bw_inbound"); ?>kbit/s)!
       </td>
      </tr>
<?
	 }
	 if($this->parent->getOption("bw_outbound") < $sum_bw_out) {
?>
      <tr>
       <td colspan="3" class="sysmessage" style="text-align: right;">
        &nbsp;The summary of all guaranteed outbound rates is higher (<? print $sum_bw_out; ?>kbit/s) then the available bandwidth rate (<? print $this->parent->getOption("bw_outbound"); ?>kbit/s)!
       </td>
      </tr>
<?
	 }
?>
     </table>
<?
	    break;

	 case 1:

            if($_GET['chain_idx']) {
					
               // get my current position
               $my_pos = $this->db->db_fetchSingleRow("SELECT chain_position FROM shaper_chains WHERE chain_idx='". $_GET['chain_idx'] ."'");
               if($_GET['to'] == 1) 
                  $new_pos = $my_pos->chain_position - 1;
               else
                  $new_pos = $my_pos->chain_position + 1;

               $this->db->db_query("UPDATE shaper_chains SET chain_position='". $my_pos->chain_position ."' WHERE chain_position='". $new_pos ."'");
               $this->db->db_query("UPDATE shaper_chains SET chain_position='". $new_pos ."' WHERE chain_idx='". $_GET['chain_idx'] ."'");
            }
            $this->parent->goBack();
            break;

         case 2:
            if($_GET['pipe_idx']) {

               $my_pos = $this->db->db_fetchSingleRow("SELECT pipe_position FROM shaper_pipes WHERE pipe_idx='". $_GET['pipe_idx'] ."'");
               if($_GET['to'] == 1)
                  $new_pos = $my_pos->pipe_position - 1;
               else
                  $new_pos = $my_pos->pipe_position + 1;

               $this->db->db_query("UPDATE shaper_pipes SET pipe_position='". $my_pos->pipe_position ."' WHERE pipe_position='". $new_pos ."'");
               $this->db->db_query("UPDATE shaper_pipes SET pipe_position='". $new_pos ."' WHERE pipe_idx='". $_GET['pipe_idx'] ."'");

            }
            $this->parent->goBack();
            break;
      }

      $this->parent->closeTable();

   } // showHtml()

}

?>
