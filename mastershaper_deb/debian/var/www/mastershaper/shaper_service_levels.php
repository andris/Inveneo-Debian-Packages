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

class MSSERVICELEVELS {

   var $db;
   var $parent;

   /* Class constructor */
   function MSSERVICELEVELS($parent)
   {
      $this->db = $parent->db;
      $this->parent = $parent;
   } // MSSERVICELEVELS()

   /* interface output */
   function showHtml()
   {

      /* If authentication is enabled, check permissions */
      if($this->parent->getOption("authentication") == "Y" &&
	 !$this->parent->checkPermissions("user_manage_servicelevels")) {

	 $this->parent->printError("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"service level icon\" />&nbsp;Manage Service Levels", "You do not have enough permissions to access this module!");
	 return 0;

      }

      if(!isset($this->parent->screen))
         $this->parent->screen = 0;

      switch($this->parent->screen) {

         /* Display list off all service levels with some details */
         default:
	 case 0:

	    $this->parent->startTable("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Manage Service Levels");
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
      Create a new Service Level
     </a>
    </td>
   </tr>
   <tr>
    <td colspan="3">&nbsp;</td>
   </tr>
   <tr>
    <td><img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;<i>Service Levels</i></td>
    <td><img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;<i>Qdisc Parameters</i></td>
    <td style="text-align: center;"><i>Options</i></td>
   </tr>
<?

            $result = $this->db->db_query("SELECT sl_idx, sl_name, sl_htb_bw_in_rate, sl_htb_bw_out_rate, sl_htb_priority, "
	                                 ."sl_hfsc_in_dmax, sl_hfsc_out_dmax, sl_hfsc_in_rate, sl_hfsc_out_rate, "
					 ."sl_cbq_in_rate, sl_cbq_in_priority, sl_cbq_out_rate, sl_cbq_out_priority, "
					 ."sl_cbq_bounded FROM shaper_service_levels ORDER BY sl_name ASC");

            while($sl = $result->fetchrow()) {
?>
   <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
    <td>
     <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=2&amp;idx=". $sl->sl_idx; ?>">
      <? print $sl->sl_name; ?>
     </a>
    </td>
    <td>
     <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />
<?
               switch($this->parent->getOption("classifier")) {

                  default:
		  case 'HTB':
		  
		     $sl_text = "";

		     if($sl->sl_htb_bw_in_rate != "")
		        $sl_text.= "In: ". $sl->sl_htb_bw_in_rate ."kbit/s, ";
                     if($sl->sl_htb_bw_out_rate != "")
		        $sl_text.= "Out: ". $sl->sl_htb_bw_out_rate ."kbit/s, ";
                     $sl_text.= "Prio: ". $this->parent->getPriorityName($sl->sl_htb_priority);

		     break;

		  case 'HFSC':

                     $sl_text = "";

                     if($sl->sl_hfsc_in_dmax != "" || $sl->sl_hfsc_in_rate != "") {
		        $sl_text.= "In: ";
		        if($sl->sl_hfsc_in_dmax != "")
                           $sl_text.= $sl->sl_hfsc_in_dmax ."ms,";
                        if($sl->sl_hfsc_in_rate != "")
                           $sl_text.= $sl->sl_hfsc_in_rate ."kbit/s";
                     }

		     $sl_text.= " ";

		     if($sl->sl_hfsc_out_dmax != "" || $sl->sl_hfsc_out_rate != "") {
		        $sl_text.= "Out: ";
                        if($sl->sl_hfsc_out_dmax != "")
                           $sl_text.= $sl->sl_hfsc_out_dmax ."ms,";
                        if($sl->sl_hfsc_out_rate != "")
                           $sl_text.= $sl->sl_hfsc_out_rate ."kbit/s";
	             }
                     break;

		  case 'CBQ':

		     $sl_text = "In: ". $sl->sl_cbq_in_rate ."kbit/s, Prio: ". $this->parent->getPriorityName($sl->sl_cbq_in_priority) .", ";
		     $sl_text.= "Out: ". $sl->sl_cbq_out_rate ."kbit/s, Prio: ". $this->parent->getPriorityName($sl->sl_cbq_out_priority);
		     break;

		  case 'NETEM':

		     $sl_text = "NETEM";
		     break;
               }
	       
	       print $sl_text."\n";
?>
    </td>
    <td style="text-align: center;">
     <a href="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=3&amp;idx=". $sl->sl_idx ."&amp;name=". urlencode($sl->sl_name); ?>" title="Delete"><img src="<? print ICON_DELETE; ?>" alt="delete icon" /></a>
    </td>
   </tr>
<?
            }
?>
  </table>
<?
            $this->parent->closeTable();
            break;

	 /* Create a new Service Level */
         case 1:

            if(!isset($_GET['saveit'])) {

               if(!isset($_GET['classifiermode']))
                  $_GET['classifiermode'] = $this->parent->getOption("classifier");

               if(!isset($_GET['qdiscmode']))
                  $_GET['qdiscmode'] = $this->parent->getOption("qdisc");
		  
	       $this->parent->startTable("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Create a new Service Level");

?>
  <form action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;classifiermode=". $_GET['classifiermode']; ?>&amp;saveit=1" method="post" name="sl">
   <table style="width: 100%;" class="withborder2">
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;General
     </td>
    </tr>
    <tr>
     <td>Name:</td>
     <td><input type="text" name="sl_name" size="30" /></td>
     <td>Name of the service level.</td>
    </tr>
    <tr>
     <td>
      Classifier:
     </td>
     <td>
      <select name="classifier" onchange="location.href='<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;classifiermode="; ?>'+(document.sl.classifier.options[document.sl.classifier.selectedIndex].value)+'&qdiscmode='+(document.sl.sl_qdisc.options[document.sl.sl_qdisc.selectedIndex].value);">
       <option value="HTB"  <? if($_GET['classifiermode'] == "HTB") print "selected=\"selected\""; ?>>HTB</option>
       <option value="HFSC" <? if($_GET['classifiermode'] == "HFSC") print "selected=\"selected\""; ?>>HFSC</option>
       <option value="CBQ"  <? if($_GET['classifiermode'] == "CBQ") print "selected=\"selected\""; ?>>CBQ</option>
      </select>
     </td>
     <td>
      Save your service level settings first before you change the classifier.
     </td>
    </tr>
<?

               switch($_GET['classifiermode']) {

                  default:
                  case 'HTB':
		   

?>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;HTB-Inbound
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Bandwidth:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_htb_bw_in_rate" size="25" />&nbsp;kbit/s</td>
     <td>Inbound bandwidth rate. This is the guaranteed inbound bandwidth.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Bandwidth ceil:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_htb_bw_in_ceil" size="25" />&nbsp;kbit/s</td>
     <td>If the chain has bandwidth to spare, this is the maximum rate which can be lend to this service. The default value is the inbound bandwidth rate, which implies no borrowing from the chain.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Bandwidth burst:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_htb_bw_in_burst" size="25" />&nbsp;kbit/s</td>
     <td>Amount of kbit/s that can be burst at ceil speed, in excess of the configured rate. Should be at least as high as the highest burst of all children. This is useful for interactive traffic.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;HTB-Outbound
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Bandwidth:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_htb_bw_out_rate" size="25" />&nbsp;kbit/s</td>
     <td>Outbound bandwidth rate. This is the guaranteed outbound bandwidth.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Bandwidth ceil:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_htb_bw_out_ceil" size="25" />&nbsp;kbit/s</td>
     <td>If the chain has bandwidth to spare, this is the maximum rate which can be lend to this service. The default value is the inbound bandwidth rate, which implies no borrowing from the chain.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Bandwidth burst:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_htb_bw_out_burst" size="25" />&nbsp;kbit/s</td>
     <td>Amount of kbit/s that can be burst at ceil speed, in excess of the configured rate. Should be at least as high as the highest burst of all children. This is useful for interactive traffic.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;HTB-Parameters
     </td>
    </tr>
    <tr>
     <td>Priority:</td>
     <td>
      <select name="sl_htb_priority">
       <option value="1">Highest (1)</option>
       <option value="2">High (2)</option>
       <option value="3" selected="selected">Normal (3)</option>
       <option value="4">Low (4)</option>
       <option value="5">Lowest (5)</option>
       <option value="0">Ignore</option>
      </select>
     </td>
     <td>The service levels with a higher priority are favoured by the scheduler. Also pipes with service levels with higher priority can lean more unused bandwidth from their chains. If priority is specified without in- or outbound rate, the maximum interface bandwidth can be used.</td>
    </tr>
<?
                     break;

                  case 'HFSC':
?>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;HFSC-Inbound
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Work-Unit:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_in_umax" size="25" value="1500" />&nbsp;bytes</td>
     <td>Maximum unit of work. A value around your MTU (ex. 1500) is a good value.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Max-Delay:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_in_dmax" size="25" />&nbsp;ms</td>
     <td>Maximum delay of a packet within this Qdisc in milliseconds (ms)</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Rate:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_in_rate" size="25" />&nbsp;kbit/s</td>
     <td>Guaranteed rate of bandwidth in kbit/s</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-ul-Rate:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_in_ulrate" size="25" />&nbsp;kbit/s</td>
     <td>Maximum rate of bandwidth in kbit/s</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;HFSC-Outbound
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Work-Unit:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_out_umax" size="25" value="1500" />&nbsp;bytes</td>
     <td>Maximum unit of work. A value around your MTU (ex. 1500) is a good value.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Max-Delay:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_out_dmax" size="25" />&nbsp;ms</td>
     <td>Maximum delay of a packet within this Qdisc in milliseconds (ms)</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Rate:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_out_rate" size="25" />&nbsp;kbit/s</td>
     <td>Guaranteed rate of bandwidth in kbit/s</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-ul-Rate:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_out_ulrate" size="25" />&nbsp;kbit/s</td>
     <td>Maximum rate of bandwidth in kbit/s</td>
    </tr>
<?
                     break;
		  case 'CBQ':

?>
    <tr>
     <td>Bounded:</td>
     <td>
      <input type="radio" name="sl_cbq_bounded" value="Y" checked="checked" />Yes
      <input type="radio" name="sl_cbq_bounded" value="N" />No
     </td>
     <td>
      If the CBQ class is bounded, it will not borrow unused bandwidth from it parent classes. If disabled the maximum rates are probably not enforced.
     </td>
    </tr> 
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;CBQ-Inbound
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Bandwidth:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_cbq_in_rate" size="25" />&nbsp;kbit/s</td>
     <td>Maximum rate a chain or pipe can send at.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Priority:</td>
     <td style="white-space: nowrap;">
      <select name="sl_cbq_in_priority">
       <option value="1">Highest (1)</option>
       <option value="2">High (2)</option>
       <option value="3" selected="selected">Normal (3)</option>
       <option value="4">Low (4)</option>
       <option value="5">Lowest (5)</option>
      </select>
     </td>
     <td>In the round-robin process, classes with the lowest priority field are tried for packets first.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;CBQ-Outbound
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Bandwidth:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_cbq_out_rate" size="25" />&nbsp;kbit/s</td>
     <td>Maximum rate a chain or pipe can send at.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Priority:</td>
     <td style="white-space: nowrap;">
      <select name="sl_cbq_out_priority">
       <option value="1">Highest (1)</option>
       <option value="2">High (2)</option>
       <option value="3" selected="selected">Normal (3)</option>
       <option value="4">Low (4)</option>
       <option value="5">Lowest (5)</option>
      </select>
     </td>
     <td>In the round-robin process, classes with the lowest priority field are tried for packets first.</td>
    </tr>
<?
                     break;

               }
?>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Queuing Discipline
     </td>
    </tr>
    <tr>
     <td>
      Queuing Discipline:
     </td>
     <td>
      <select name="sl_qdisc"  onchange="location.href='<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;qdiscmode="; ?>'+(document.sl.sl_qdisc.options[document.sl.sl_qdisc.selectedIndex].value)+'&classifiermode='+(document.sl.classifier.options[document.sl.classifier.selectedIndex].value);">
       <option value="SFQ" <? if($_GET['qdiscmode'] == "SFQ") print "selected=\"selected\""; ?>>SFQ</option>
       <option value="ESFQ" <? if($_GET['qdiscmode'] == "ESFQ") print "selected=\"selected\""; ?>>ESFQ</option>
       <option value="HFSC" <? if($_GET['qdiscmode'] == "HFSC") print "selected=\"selected\""; ?>>HFSC</option>
       <option value="NETEM" <? if($_GET['qdiscmode'] == "NETEM") print "selected=\"selected\""; ?>>NETEM</option>
      </select>
     </td>
     <td>
      Queuing Discipline.
     </td>
    </tr>
<?

               switch($_GET['qdiscmode']) {

	          case 'SFQ':
		  case 'HFSC':
		     break;

		  case 'ESFQ':
?>
    <tr>
     <td>
      Perturb:
     </td>
     <td>
      <input type="text" name="sl_esfq_perturb" size="25" value="<? print $this->parent->getOption("esfq_default_perturb"); ?>" />
     </td>
     <td>
      Causes the flows to be redistributed so there are no collosions on sharing a queue.
      Default is 0. Recommeded 10.
     </td> 
    </tr>
    <tr>
     <td>
      Limit:
     </td>
     <td>
      <input type="text" name="sl_esfq_limit" size="25" value="<? print $this->parent->getOption("esfq_default_limit"); ?>" />
     </td>
     <td>
      The total number of packets that will be queued by this ESFQ before packets start 
      getting dropped.  Limit must be less than or equal to depth. Default is 128.
     </td>
    </tr>
    <tr>
     <td>
      Depth:
     </td>
     <td>
      <input type="text" name="sl_esfq_depth" size="25" value="<? print $this->parent->getOption("esfq_default_depth"); ?>" />
     </td>
     <td>
      No description available. Set like Limit.
     </td>
    </tr>
    <tr>
     <td>
      Divisor:
     </td>
     <td>
      <input type="text" name="sl_esfq_divisor" size="25" value="<? print $this->parent->getOption("esfq_default_divisor"); ?>" />
     </td>
     <td>
      Divisor sets the number of bits to use for the hash table. A larger hash table
      decreases the likelihood of collisions but will consume more memory.
     </td>
    </tr>
    <tr>
     <td>
      Hash:
     </td>
     <td>
      <select name="sl_esfq_hash">
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
      Howto seperate traffic into queues. Classisc equals to SFQ handling. Src and Dst
      per direction. Fwmark uses the connection mark which can be set by iptables. If
      less then 16384 (2^14) simultaneous connections occurs use one of the _direct
      sibling which uses an fast algorithm.
     </td>
    </tr>
<?
		     break;

		  case 'NETEM':
?>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Network delays
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Delay:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_delay" size="25" />&nbsp;ms</td>
     <td>Fixed amount of delay to all packets.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Jitter:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_jitter" size="25" />&nbsp;ms</td>
     <td>Random variation around the delay value (= delay &#177; Jitter).
    </tr>
    <tr>
     <td style="white-space: nowrap;">Correlation:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_random" size="25" />&nbsp;&#37;</td>
     <td>Limits the randomness to simulate a real network. So the next packets delay will be within % of the delay of the packet before.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Distribution:</td>
     <td style="white-space: nowrap;">
      <select name="sl_netem_distribution">
       <option value="ignore">Ignore</option>
       <option value="normal">normal</option>
       <option value="pareto">pareto</option>
       <option value="paretonormal">paretonormal</option>
      </select>
     </td>
     <td>How the delays are distributed over a longer delay periode.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Others functions
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Packetloss:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_loss" size="25" />&nbsp;&#37;</td>
     <td>Packetloss in percent. Smallest value is .0000000232% ( = 1 / 2^32).
    </tr>
    <tr>
     <td style="white-space: nowrap;">Duplication:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_duplication" size="25" />&nbsp;&#37;</td>
     <td>Duplication in percent. Smallest value is .0000000232% ( = 1 / 2^32).
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Re-Ordering
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Gap:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_gap" size="25" /></td>
     <td>Packet re-ordering causes 1 out of N packets to be delayed. For a value of 5 every 5th (10th, 15th, ...) packet will get delayed by 10ms and the others will pass straight out.
    </tr>
    <tr>
    <tr>
     <td style="white-space: nowrap;">Reorder percentage:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_reorder_percentage" size="25" />&nbsp;&#37;</td>
     <td>Percentage of packets the get reordered.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Reorder correlation:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_reorder_correlation" size="25" />&nbsp;&#37;</td>
     <td>Percentage of packets the are correlate each others.</td>
    </tr>
<?
                     break;
               }
?>
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

               if($_POST['sl_name'] != "") {

		  $is_numeric = 1;

                  switch($_GET['classifiermode']) {

                     case 'HTB':

                        if($_POST['sl_htb_priority'] == 0 && $_POST['sl_htb_bw_in_rate'] == "" && $_POST['sl_htb_bw_out_rate'] == "") {

                           $this->parent->printError("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Create a new Service Level", "A service level which ignores priority AND also has not specified inbound and outbound rate is not possible!");
			   return 0;

                        }
                        else {
								
                           if($_POST['sl_htb_bw_in_rate'] != "" && !is_numeric($_POST['sl_htb_bw_in_rate']))
                              $is_numeric = 0;
		 	
                           if($_POST['sl_htb_bw_out_rate'] != "" && !is_numeric($_POST['sl_htb_bw_out_rate']))
                              $is_numeric = 0;
										
                           if($_POST['sl_htb_bw_in_ceil'] != "" && !is_numeric($_POST['sl_htb_bw_in_ceil']))
                              $is_numeric = 0;
			
                           if($_POST['sl_htb_bw_in_burst'] != "" && !is_numeric($_POST['sl_htb_bw_in_burst']))
                              $is_numeric = 0;
			
                           if($_POST['sl_htb_bw_out_ceil'] != "" && !is_numeric($_POST['sl_htb_bw_out_ceil']))
                              $is_numeric = 0;

                           if($_POST['sl_htb_bw_out_burst'] != "" && !is_numeric($_POST['sl_htb_bw_out_burst']))
                              $is_numeric = 0;
		
                        }
                        break;

                     case 'HFSC':
								
                        /* If umax is specifed, also umax is necessary */
			if(($_POST['sl_hfsc_in_umax'] != "" && $_POST['sl_hfsc_in_dmax'] == "") ||
			   ($_POST['sl_hfsc_out_umax'] != "" && $_POST['sl_hfsc_out_dmax'] == "")) {

			   $this->parent->printError("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Create a new Service Level", "Please enter a \"Max-Delay\" value if you have defined a \"Work-Unit\" value!");
			   return 0;

                        }
			else {

			   if($_POST['sl_hfsc_in_umax'] != "" && !is_numeric($_POST['sl_hfsc_in_umax']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_in_dmax'] != "" && !is_numeric($_POST['sl_hfsc_in_dmax']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_in_rate'] != "" && !is_numeric($_POST['sl_hfsc_in_rate']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_in_ulrate'] != "" && !is_numeric($_POST['sl_hfsc_in_ulrate']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_out_umax'] != "" && !is_numeric($_POST['sl_hfsc_out_umax']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_out_dmax'] != "" && !is_numeric($_POST['sl_hfsc_out_dmax']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_out_rate'] != "" && !is_numeric($_POST['sl_hfsc_out_rate']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_out_ulrate'] != "" && !is_numeric($_POST['sl_hfsc_out_ulrate']))
			      $is_numeric = 0;

                        }
			break;

                     case 'CBQ':

			if($_POST['sl_cbq_in_rate'] == "" || $_POST['sl_cbq_out_rate'] == "") {

			   $this->parent->printError("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Create a new Service Level", "Please enter a input and output rate!");
			   return 0;

			}
			else {

			   if($_POST['sl_cbq_in_rate'] != "" && !is_numeric($_POST['sl_cbq_in_rate']))
			      $is_numeric = 0;

			   if($_POST['sl_cbq_out_rate'] != "" && !is_numeric($_POST['sl_cbq_out_rate']))
			      $is_numeric = 0;

			}

			break;

                     case 'NETEM':
		        break;

                  }

		  if(!$is_numeric) {

		     $this->parent->printError("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Create a new Service Level", "Please enter only numerical values for bandwidth parameters!");

		  }
		  else {

		     $this->db->db_query("INSERT INTO shaper_service_levels (sl_name, sl_htb_bw_in_rate,"
			                ."sl_htb_bw_in_ceil, sl_htb_bw_in_burst, sl_htb_bw_out_rate, "
					."sl_htb_bw_out_ceil, sl_htb_bw_out_burst, sl_htb_priority, "
                                        ."sl_hfsc_in_umax, sl_hfsc_in_dmax, sl_hfsc_in_rate, sl_hfsc_in_ulrate, "
					."sl_hfsc_out_umax, sl_hfsc_out_dmax, sl_hfsc_out_rate, sl_hfsc_out_ulrate, "
					."sl_cbq_in_rate, sl_cbq_in_priority, sl_cbq_out_rate, sl_cbq_out_priority, "
					."sl_cbq_bounded, sl_qdisc, sl_netem_delay, sl_netem_jitter, sl_netem_random, "
					."sl_netem_distribution, sl_netem_loss, sl_netem_duplication, "
					."sl_netem_gap, sl_netem_reorder_percentage, sl_netem_reorder_correlation, "
					."sl_esfq_perturb, sl_esfq_limit, sl_esfq_depth, sl_esfq_divisor, sl_esfq_hash) "
					."VALUES ('". $_POST['sl_name'] ."', "
					."'". $_POST['sl_htb_bw_in_rate'] ."', "
					."'". $_POST['sl_htb_bw_in_ceil'] ."', "
					."'". $_POST['sl_htb_bw_in_burst'] ."', "
					."'". $_POST['sl_htb_bw_out_rate'] ."', "
					."'". $_POST['sl_htb_bw_out_ceil'] ."', "
					."'". $_POST['sl_htb_bw_out_burst'] ."', "
					."'". $_POST['sl_htb_priority'] ."', "
					."'". $_POST['sl_hfsc_in_umax'] ."', "
					."'". $_POST['sl_hfsc_in_dmax'] ."', "
					."'". $_POST['sl_hfsc_in_rate'] ."', "
					."'". $_POST['sl_hfsc_in_ulrate'] ."', "
					."'". $_POST['sl_hfsc_out_umax'] ."', "
					."'". $_POST['sl_hfsc_out_dmax'] ."', "
					."'". $_POST['sl_hfsc_out_rate'] ."', "
					."'". $_POST['sl_hfsc_out_ulrate'] ."', "
					."'". $_POST['sl_cbq_in_rate'] ."', "
					."'". $_POST['sl_cbq_in_priority'] ."', "
					."'". $_POST['sl_cbq_out_rate'] ."', "
					."'". $_POST['sl_cbq_out_priority'] ."', "
					."'". $_POST['sl_cbq_bounded'] ."', "
					."'". $_POST['sl_qdisc'] ."', "
					."'". $_POST['sl_netem_delay'] ."', "
					."'". $_POST['sl_netem_jitter'] ."', "
					."'". $_POST['sl_netem_random'] ."', "
					."'". $_POST['sl_netem_distribution'] ."', "
					."'". $_POST['sl_netem_loss'] ."', "
					."'". $_POST['sl_netem_duplication'] ."', "
					."'". $_POST['sl_netem_gap'] ."', "
					."'". $_POST['sl_netem_reorder_percentage']."', "
					."'". $_POST['sl_netem_reorder_correlation'] ."', "
					."'". $_POST['sl_esfq_perturb'] ."', "
					."'". $_POST['sl_esfq_limit'] ."', "
					."'". $_POST['sl_esfq_depth'] ."', "
					."'". $_POST['sl_esfq_divisor'] ."', "
					."'". $_POST['sl_esfq_hash'] ."')");

		     $this->parent->goBack();
                  }
               }
               else {

                  $this->parent->printError("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Create a new Service Level", "Please enter a service level name!");

               }
            }
            break;
            
	 /* Modify existing service level */
	 case 2:

            if(!isset($_GET['saveit'])) {

               $sl = $this->db->db_fetchSingleRow("SELECT * FROM shaper_service_levels WHERE sl_idx='". $_GET['idx'] ."'");

               if(!isset($_GET['classifiermode'])) 
                  $_GET['classifiermode'] = $this->parent->getOption("classifier");

               if(!isset($_GET['qdiscmode'])) 
                  $_GET['qdiscmode'] = $sl->sl_qdisc;


	       $this->parent->startTable("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Modify Service Level ". $sl->sl_name);
?>
  <form action="<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;idx=". $_GET['idx']; ?>&amp;classifiermode=<? print $_GET['classifiermode']; ?>&amp;saveit=1" method="post" name="sl">
   <table style="width: 100%;" class="withborder2">
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;General
     </td>
    </tr>
    <tr>
     <td>Name:</td>
     <td><input type="text" name="sl_name" size="30" value="<? print $sl->sl_name; ?>" /></td>
     <td>Name of the service level.</td>
    </tr>
    <tr>
     <td>
      Classifier:
     </td>
     <td>
      <select name="classifier" onchange="location.href='<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;idx=". $_GET['idx'] ."&amp;classifiermode="; ?>'+(document.sl.classifier.options[document.sl.classifier.selectedIndex].value)+'&qdiscmode='+(document.sl.sl_qdisc.options[document.sl.sl_qdisc.selectedIndex].value);">
       <option value="HTB"  <? if($_GET['classifiermode'] == "HTB") print "selected=\"selected\""; ?>>HTB</option>
       <option value="HFSC" <? if($_GET['classifiermode'] == "HFSC") print "selected=\"selected\""; ?>>HFSC</option>
       <option value="CBQ"  <? if($_GET['classifiermode'] == "CBQ") print "selected=\"selected\""; ?>>CBQ</option>
      </select>
     </td>
     <td>
      Save your service level settings first before you change the classifier.
<?

               switch($_GET['classifiermode']) {

                  default:
                  case 'HTB':
?>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Inbound
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Bandwidth:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_htb_bw_in_rate" size="25" value="<? print $sl->sl_htb_bw_in_rate; ?>" />&nbsp;kbit/s</td>
     <td>Inbound bandwidth rate. This is the guaranteed inbound bandwidth.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Bandwidth ceil:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_htb_bw_in_ceil" size="25" value="<? print $sl->sl_htb_bw_in_ceil; ?>" />&nbsp;kbit/s</td>
     <td>If the chain has bandwidth to spare, this is the maximum rate which can be lend to this service. The default value is the inbound bandwidth rate, which implies no borrowing from the chain.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Bandwidth burst:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_htb_bw_in_burst" size="25" value="<? print $sl->sl_htb_bw_in_burst; ?>" />&nbsp;kbit/s</td>
     <td>Amount of kbit/s that can be burst at ceil speed, in excess of the configured rate. Should be at least as high as the highest burst of all children. This is useful for interactive traffic.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Outbound
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Bandwidth:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_htb_bw_out_rate" size="25" value="<? print $sl->sl_htb_bw_out_rate; ?>" />&nbsp;kbit/s</td>
     <td>Outbound bandwidth rate. This is the guaranteed outbound bandwidth.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Bandwidth ceil:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_htb_bw_out_ceil" size="25" value="<? print $sl->sl_htb_bw_out_ceil; ?>" />&nbsp;kbit/s</td>
     <td>If the chain has bandwidth to spare, this is the maximum rate which can be lend to this service. The default value is the inbound bandwidth rate, which implies no borrowing from the chain.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Bandwidth burst:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_htb_bw_out_burst" size="25" value="<? print $sl->sl_htb_bw_out_burst; ?>" />&nbsp;kbit/s</td>
     <td>Amount of kbit/s that can be burst at ceil speed, in excess of the configured rate. Should be at least as high as the highest burst of all children. This is useful for interactive traffic.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Parameters
     </td>
    </tr>
    <tr>
     <td>Priority:</td>
     <td>
      <select name="sl_htb_priority">
       <option value="1" <? if($sl->sl_htb_priority == 1) print "selected=\"selected\"";?>>Highest (1)</option>
       <option value="2" <? if($sl->sl_htb_priority == 2) print "selected=\"selected\"";?>>High (2)</option>
       <option value="3" <? if($sl->sl_htb_priority == 3) print "selected=\"selected\"";?>>Normal (3)</option>
       <option value="4" <? if($sl->sl_htb_priority == 4) print "selected=\"selected\"";?>>Low (4)</option>
       <option value="5" <? if($sl->sl_htb_priority == 5) print "selected=\"selected\"";?>>Lowest (5)</option>
       <option value="0" <? if($sl->sl_htb_priority == 0) print "selected=\"selected\"";?>>Ignore</option>
      </select>
     </td>
     <td>The service levels with a higher priority are favoured by the scheduler. Also pipes with service levels with a higher priority can lean more unused bandwidth from their chains. If priority is specified without in- or outbound rate, the maximum interface bandwidth can be used.</td>
    </tr>
<?
                     break;

                  case 'HFSC':

?>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Inbound
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Work-Unit:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_in_umax" size="25" value="<? print $sl->sl_hfsc_in_umax; ?>" />&nbsp;bytes</td>
     <td>Maximum unit of work. A value around your MTU (ex. 1500) is a good value.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Max-Delay:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_in_dmax" size="25" value="<? print $sl->sl_hfsc_in_dmax; ?>" />&nbsp;ms</td>
     <td>Maximum delay of a packet within this Qdisc in milliseconds (ms)</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Rate:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_in_rate" size="25" value="<? print $sl->sl_hfsc_in_rate; ?>" />&nbsp;kbit/s</td>
     <td>Guaranteed rate of bandwidth in kbit/s</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-ul-Rate:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_in_ulrate" size="25" value="<? print $sl->sl_hfsc_in_ulrate; ?>" />&nbsp;kbit/s</td>
     <td>Maximum rate of bandwidth in kbit/s</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Outbound
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Work-Unit:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_out_umax" size="25" value="<? print $sl->sl_hfsc_out_umax; ?>" />&nbsp;bytes</td>
     <td>Maximum unit of work. A value around your MTU (ex. 1500) is a good value.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Max-Delay:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_out_dmax" size="25" value="<? print $sl->sl_hfsc_out_dmax; ?>" />&nbsp;ms</td>
     <td>Maximum delay of a packet within this Qdisc in milliseconds (ms)</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Rate:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_out_rate" size="25" value="<? print $sl->sl_hfsc_out_rate; ?>" />&nbsp;kbit/s</td>
     <td>Guaranteed rate of bandwidth in kbit/s</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-ul-Rate:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_hfsc_out_ulrate" size="25" value="<? print $sl->sl_hfsc_out_ulrate; ?>" />&nbsp;kbit/s</td>
     <td>Maximum rate of bandwidth in kbit/s</td>
    </tr>
<?
                     break;

		  case 'CBQ':

?>
    <tr>
     <td>Bounded:</td>
     <td>
      <input type="radio" name="sl_cbq_bounded" value="Y" <? if($sl->sl_cbq_bounded == "Y") print "checked=\"checked\""; ?> />Yes
      <input type="radio" name="sl_cbq_bounded" value="N" <? if($sl->sl_cbq_bounded != "Y") print "checked=\"checked\""; ?> />No
     </td>
     <td>
      If the CBQ class is bounded, it will not borrow unused bandwidth from it parent classes. If disabled the maximum rates are probably not enforced.
     </td>
    </tr> 
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Inbound
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Bandwidth:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_cbq_in_rate" size="25" value="<? print $sl->sl_cbq_in_rate; ?>" />&nbsp;kbit/s</td>
     <td>Maximum rate a chain or pipe can send at.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">In-Priority:</td>
     <td style="white-space: nowrap;">
      <select name="sl_cbq_in_priority">
       <option value="1" <? if($sl->sl_cbq_in_priority == 1) print "selected=\"selected\""; ?>>Highest (1)</option>
       <option value="2" <? if($sl->sl_cbq_in_priority == 2) print "selected=\"selected\""; ?>>High (2)</option>
       <option value="3" <? if($sl->sl_cbq_in_priority == 3) print "selected=\"selected\""; ?>>Normal (3)</option>
       <option value="4" <? if($sl->sl_cbq_in_priority == 4) print "selected=\"selected\""; ?>>Low (4)</option>
       <option value="5" <? if($sl->sl_cbq_in_priority == 5) print "selected=\"selected\""; ?>>Lowest (5)</option>
      </select>
     </td>
     <td>In the round-robin process, classes with the lowest priority field are tried for packets first.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Outbound
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Bandwidth:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_cbq_out_rate" size="25" value="<? print $sl->sl_cbq_out_rate; ?>" />&nbsp;kbit/s</td>
     <td>Maximum rate a chain or pipe can send at.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Out-Priority:</td>
     <td style="white-space: nowrap;">
      <select name="sl_cbq_out_priority">
       <option value="1" <? if($sl->sl_cbq_out_priority == 1) print "selected=\"selected\""; ?>>Highest (1)</option>
       <option value="2" <? if($sl->sl_cbq_out_priority == 2) print "selected=\"selected\""; ?>>High (2)</option>
       <option value="3" <? if($sl->sl_cbq_out_priority == 3) print "selected=\"selected\""; ?>>Normal (3)</option>
       <option value="4" <? if($sl->sl_cbq_out_priority == 4) print "selected=\"selected\""; ?>>Low (4)</option>
       <option value="5" <? if($sl->sl_cbq_out_priority == 5) print "selected=\"selected\""; ?>>Lowest (5)</option>
      </select>
     </td>
     <td>In the round-robin process, classes with the lowest priority field are tried for packets first.</td>
    </tr>
<?
                  break;

	    }
?>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Queuing Discipline
     </td>
    </tr>
    <tr>
     <td>
      Queuing Discipline:
     </td>
     <td>
      <select name="sl_qdisc"  onchange="location.href='<? print $this->parent->self ."?mode=". $this->parent->mode ."&amp;screen=". $this->parent->screen ."&amp;idx=". $_GET['idx'] ."&amp;qdiscmode="; ?>'+(document.sl.sl_qdisc.options[document.sl.sl_qdisc.selectedIndex].value)+'&classifiermode='+(document.sl.classifier.options[document.sl.classifier.selectedIndex].value);">
       <option value="SFQ" <? if($_GET['qdiscmode'] == "SFQ") print "selected=\"selected\""; ?>>SFQ</option>
       <option value="ESFQ" <? if($_GET['qdiscmode'] == "ESFQ") print "selected=\"selected\""; ?>>ESFQ</option>
       <option value="HFSC" <? if($_GET['qdiscmode'] == "HFSC") print "selected=\"selected\""; ?>>HFSC</option>
       <option value="NETEM" <? if($_GET['qdiscmode'] == "NETEM") print "selected=\"selected\""; ?>>NETEM</option>
      </select>
     </td>
     <td>
      Queuing Discipline.
     </td>
    </tr>
<?

               switch($_GET['qdiscmode']) {

	          case 'SFQ':
		  case 'HFSC':
		     break;

		  case 'ESFQ':
?>
    <tr>
     <td>
      Perturb:
     </td>
     <td>
      <input type="text" name="sl_esfq_perturb" size="25" value="<? print $sl->sl_esfq_perturb; ?>" />
     </td>
     <td>
      Causes the flows to be redistributed so there are no collosions on sharing a queue.
      Default is 0. Recommeded 10.
     </td> 
    </tr>
    <tr>
     <td>
      Limit:
     </td>
     <td>
      <input type="text" name="sl_esfq_limit" size="25" value="<? print $sl->sl_esfq_limit; ?>" />
     </td>
     <td>
      The total number of packets that will be queued by this ESFQ before packets start 
      getting dropped.  Limit must be less than or equal to depth. Default is 128.
     </td>
    </tr>
    <tr>
     <td>
      Depth:
     </td>
     <td>
      <input type="text" name="sl_esfq_depth" size="25" value="<? print $sl->sl_esfq_depth; ?>" />
     </td>
     <td>
      No description available. Set like Limit.
     </td>
    </tr>
    <tr>
     <td>
      Divisor:
     </td>
     <td>
      <input type="text" name="sl_esfq_divisor" size="25" value="<? print $sl->sl_esfq_divisor; ?>" />
     </td>
     <td>
      Divisor sets the number of bits to use for the hash table. A larger hash table
      decreases the likelihood of collisions but will consume more memory.
     </td>
    </tr>
    <tr>
     <td>
      Hash:
     </td>
     <td>
      <select name="sl_esfq_hash">
       <option value="classic" <? if($sl->sl_esfq_hash == "classic") print "selected=\"selected\""; ?>>Classic</option>
       <option value="src" <? if($sl->sl_esfq_hash == "src") print "selected=\"selected\""; ?>>Src</option>
       <option value="dst" <? if($sl->sl_esfq_hash == "dst") print "selected=\"selected\""; ?>>Dst</option>
       <option value="fwmark" <? if($sl->sl_esfq_hash == "fwmark") print "selected=\"selected\""; ?>>Fwmark</option>
       <option value="src_direct" <? if($sl->sl_esfq_hash == "src_direct") print "selected=\"selected\""; ?>>Src_direct</option>
       <option value="dst_direct" <? if($sl->sl_esfq_hash == "dst_direct") print "selected=\"selected\""; ?>>Dst_direct</option>
       <option value="fwmark_direct" <? if($sl->sl_esfq_hash == "fwmark_direct") print "selected=\"selected\""; ?>>Fwmark_direct</option>
      </select>
     </td>
     <td>
      Howto seperate traffic into queues. Classisc equals to SFQ handling. Src and Dst
      per direction. Fwmark uses the connection mark which can be set by iptables. If
      less then 16384 (2^14) simultaneous connections occurs use one of the _direct
      sibling which uses an fast algorithm.
     </td>
    </tr>
<?
		     break;

	       case 'NETEM':

?>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Network delays
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Delay:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_delay" size="25" value="<? print $sl->sl_netem_delay; ?>" />&nbsp;ms</td>
     <td>Fixed amount of delay to all packets.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Jitter:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_jitter" size="25" value="<? print $sl->sl_netem_jitter; ?>" />&nbsp;ms</td>
     <td>Random variation around the delay value (= delay &#177; Jitter).
    </tr>
    <tr>
     <td style="white-space: nowrap;">Correlation:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_random" size="25" value="<? print $sl->sl_netem_random; ?>" />&nbsp;&#37;</td>
     <td>Limits the randomness to simulate a real network. So the next packets delay will be within % of the delay of the packet before.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Distribution:</td>
     <td style="white-space: nowrap;">
      <select name="sl_netem_distribution">
       <option value="ignore" <? if($sl->sl_netem_distribution == "ignore") print "selected=\"selected\""; ?>>Ignore</option>
       <option value="normal" <? if($sl->sl_netem_distribution == "normal") print "selected=\"selected\""; ?>>normal</option>
       <option value="pareto" <? if($sl->sl_netem_distribution == "pareto") print "selected=\"selected\""; ?>>pareto</option>
       <option value="paretonormal" <? if($sl->sl_netem_distribution == "paretonormal") print "selected=\"selected\""; ?>>paretonormal</option>
      </select>
     </td>
     <td>How the delays are distributed over a longer delay periode.</td>
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Others functions
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Packetloss:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_loss" size="25" value="<? print $sl->sl_netem_loss; ?>" />&nbsp;&#37;</td>
     <td>Packetloss in percent. Smallest value is .0000000232% ( = 1 / 2^32).
    </tr>
    <tr>
     <td style="white-space: nowrap;">Duplication:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_duplication" size="25" value="<? print $sl->sl_netem_duplication; ?>" />&nbsp;&#37;</td>
     <td>Duplication in percent. Smallest value is .0000000232% ( = 1 / 2^32).
    </tr>
    <tr>
     <td colspan="3">
      <img src="<? print ICON_SERVICELEVELS; ?>" alt="servicelevel icon" />&nbsp;Re-Ordering
     </td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Gap:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_gap" size="25" value="<? print $sl->sl_netem_gap; ?>" /></td>
     <td>Packet re-ordering causes 1 out of N packets to be delayed. For a value of 5 every 5th (10th, 15th, ...) packet will get delayed by 10ms and the others will pass straight out.
    </tr>
    <tr>
    <tr>
     <td style="white-space: nowrap;">Reorder percentage:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_reorder_percentage" size="25" value="<? print $sl->sl_netem_reorder_percentage; ?>" />&nbsp;&#37;</td>
     <td>Percentage of packets the get reordered.</td>
    </tr>
    <tr>
     <td style="white-space: nowrap;">Reorder correlation:</td>
     <td style="white-space: nowrap;"><input type="text" name="sl_netem_reorder_correlation" size="25" value="<? print $sl->sl_netem_reorder_correlation; ?>" />&nbsp;&#37;</td>
     <td>Percentage of packets the are correlate each others.</td>
    </tr>
<?
                     break;
               }
?>
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

               if($_POST['sl_name'] != "") {

		  $is_numeric = 1;

                  switch($_GET['classifiermode']) {

                     case 'HTB':

                        if($_POST['sl_htb_priority'] == 0 && $_POST['sl_htb_bw_in_rate'] == "" && $_POST['sl_htb_bw_out_rate'] == "") {

                           $this->parent->printError("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Modify Service Level", "A service level which ignores priority AND also has not specified inbound and outbound rate is not possible!");
			   return 0;

                        }
                        else {
			
                           if($_POST['sl_htb_bw_in_rate'] != "" && !is_numeric($_POST['sl_htb_bw_in_rate']))
                              $is_numeric = 0;

                           if($_POST['sl_htb_bw_out_rate'] != "" && !is_numeric($_POST['sl_htb_bw_out_rate']))
                              $is_numeric = 0;

                           if($_POST['sl_htb_bw_in_ceil'] != "" && !is_numeric($_POST['sl_htb_bw_in_ceil']))
                              $is_numeric = 0;

                           if($_POST['sl_htb_bw_in_burst'] != "" && !is_numeric($_POST['sl_htb_bw_in_burst']))
                              $is_numeric = 0;

                           if($_POST['sl_htb_bw_out_ceil'] != "" && !is_numeric($_POST['sl_htb_bw_out_ceil']))
                              $is_numeric = 0;

                           if($_POST['sl_htb_bw_out_burst'] != "" && !is_numeric($_POST['sl_htb_bw_out_burst']))
                              $is_numeric = 0;

                        }
                        break;

                     case 'HFSC':

                        /* If umax is specifed, also umax is necessary */
			if(($_POST['sl_hfsc_in_umax'] != "" && $_POST['sl_hfsc_in_dmax'] == "") ||
			   ($_POST['sl_hfsc_out_umax'] != "" && $_POST['sl_hfsc_out_dmax'] == "")) {

			   $this->parent->printError("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Modify Service Level", "Please enter a \"Max-Delay\" value if you have defined a \"Work-Unit\" value!");
                           return 0;

			}
			else {

			   if($_POST['sl_hfsc_in_umax'] != "" && !is_numeric($_POST['sl_hfsc_in_umax']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_in_dmax'] != "" && !is_numeric($_POST['sl_hfsc_in_dmax']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_in_rate'] != "" && !is_numeric($_POST['sl_hfsc_in_rate']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_in_ulrate'] != "" && !is_numeric($_POST['sl_hfsc_in_ulrate']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_out_umax'] != "" && !is_numeric($_POST['sl_hfsc_out_umax']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_out_dmax'] != "" && !is_numeric($_POST['sl_hfsc_out_dmax']))
			      $is_numeric = 0;

			   if($_POST['sl_hfsc_out_rate'] != "" && !is_numeric($_POST['sl_hfsc_out_rate']))
			      $is_numeric = 0;
								   
			   if($_POST['sl_hfsc_out_ulrate'] != "" && !is_numeric($_POST['sl_hfsc_out_ulrate']))
			      $is_numeric = 0;

                        }
                        break;

		     case 'CBQ':

			if($_POST['sl_cbq_in_rate'] == "" || $_POST['sl_cbq_out_rate'] == "") {
			
			   $this->parent->printError("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Modify Service Level", "Please enter a input and output rate!");
			   return 0;

			}
			else {

			   if($_POST['sl_cbq_in_rate'] != "" && !is_numeric($_POST['sl_cbq_in_rate']))
			      $is_numeric = 0;

			   if($_POST['sl_cbq_out_rate'] != "" && !is_numeric($_POST['sl_cbq_out_rate']))
			      $is_numeric = 0;

			}

			break;

                     case 'NETEM':

		        break;

		  } 

		  if(!$is_numeric) {

		     $this->parent->printError("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Modify Service Level", "Please enter only numerical values for bandwidth parameters!");

		  }
		  else {

		     $this->db->db_query("UPDATE shaper_service_levels SET "
		                        ."sl_name='". $_POST['sl_name'] ."', "
					."sl_htb_bw_in_rate='". $_POST['sl_htb_bw_in_rate'] ."', "
					."sl_htb_bw_in_ceil='". $_POST['sl_htb_bw_in_ceil'] ."', "
					."sl_htb_bw_in_burst='". $_POST['sl_htb_bw_in_burst'] ."', "
					."sl_htb_bw_out_rate='". $_POST['sl_htb_bw_out_rate'] ."', "
					."sl_htb_bw_out_ceil='". $_POST['sl_htb_bw_out_ceil'] ."', "
					."sl_htb_bw_out_burst='". $_POST['sl_htb_bw_out_burst'] ."', "
					."sl_htb_priority='". $_POST['sl_htb_priority'] ."', "
					."sl_hfsc_in_umax='". $_POST['sl_hfsc_in_umax'] ."', "
					."sl_hfsc_in_dmax='". $_POST['sl_hfsc_in_dmax'] ."', "
					."sl_hfsc_in_rate='". $_POST['sl_hfsc_in_rate'] ."', "
					."sl_hfsc_in_ulrate='". $_POST['sl_hfsc_in_ulrate'] ."', "
					."sl_hfsc_out_umax='". $_POST['sl_hfsc_out_umax'] ."', "
					."sl_hfsc_out_dmax='". $_POST['sl_hfsc_out_dmax'] ."', "
					."sl_hfsc_out_rate='". $_POST['sl_hfsc_out_rate'] ."', "
					."sl_hfsc_out_ulrate='". $_POST['sl_hfsc_out_ulrate'] ."', "
			                ."sl_cbq_in_rate='". $_POST['sl_cbq_in_rate'] ."', "
					."sl_cbq_in_priority='". $_POST['sl_cbq_in_priority'] ."', "
					."sl_cbq_out_rate='". $_POST['sl_cbq_out_rate'] ."', "
					."sl_cbq_out_priority='". $_POST['sl_cbq_out_priority'] ."', "
					."sl_cbq_bounded='". $_POST['sl_cbq_bounded'] ."', "
					."sl_qdisc='". $_POST['sl_qdisc'] ."', "
					."sl_netem_delay='". $_POST['sl_netem_delay'] ."', "
					."sl_netem_jitter='". $_POST['sl_netem_jitter'] ."', "
					."sl_netem_random='". $_POST['sl_netem_random'] ."', "
					."sl_netem_distribution='". $_POST['sl_netem_distribution'] ."', "
					."sl_netem_loss='". $_POST['sl_netem_loss'] ."', "
					."sl_netem_duplication='". $_POST['sl_netem_duplication'] ."', "
					."sl_netem_gap='". $_POST['sl_netem_gap'] ."', "
					."sl_netem_reorder_percentage='". $_POST['sl_netem_reorder_percentage']."', "
					."sl_netem_reorder_correlation='". $_POST['sl_netem_reorder_correlation'] ."', "
					."sl_esfq_perturb='". $_POST['sl_esfq_perturb'] ."', "
					."sl_esfq_limit='". $_POST['sl_esfq_limit'] ."', "
					."sl_esfq_depth='". $_POST['sl_esfq_depth'] ."', "
					."sl_esfq_divisor='". $_POST['sl_esfq_divisor'] ."', "
					."sl_esfq_hash='". $_POST['sl_esfq_hash'] ."' "
					."WHERE sl_idx='". $_GET['idx'] ."'");

		     $this->parent->goBack();
		  }
               }
               else {

                  $this->parent->printError("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Modify Service Level", "Please enter a service level name!");

               }
            }
            break;

      /* Delete existing service level */
      case 3:

         if(!isset($_GET['doit']))

            $this->parent->printYesNo("<img src=\"". ICON_SERVICELEVELS ."\" alt=\"servicelevel icon\" />&nbsp;Delete Service Level", "Really delete Service Level ". $_GET['name'] ."?");

         else {

            $this->db->db_query("DELETE FROM shaper_service_levels WHERE sl_idx='". $_GET['idx'] ."'");
            $this->parent->goBack();

         }
         break;
      }
   } // showHtml()

   // End of class definition
}

?>
