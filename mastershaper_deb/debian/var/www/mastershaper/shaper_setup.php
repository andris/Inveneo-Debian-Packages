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

define("UNIDIRECTIONAL", 1);
define("BIDIRECTIONAL", 2);
define("TRUE", 1);
define("FALSE", 0);

define("MS_PRE", 10);
define("MS_IN", 11);
define("MS_OUT", 12);
define("MS_POST", 13);

require_once "Net/IPv4.php";

class MSSETUP {

   var $db;
   var $parent;
   var $inbound;
   var $outbound;
   var $in_interface;
   var $out_interface;
   var $ms_in;
   var $ms_out;
   var $ms_pre;
   var $ms_post;
   var $classes;
   var $filters;
   var $ipv4;
   var $ack_sl;
   var $error;

   /* Class constructor */
   function MSSETUP($parent)
   {
      $this->db = $parent->db;
      $this->parent = $parent;
      $this->ms_in  = Array();
      $this->ms_out = Array();
      $this->ms_pre = Array();
      $this->ms_post = Array();
      $this->error  = Array();

      $this->classes = Array();
      $this->filters = Array();

      $this->ipv4 = new Net_IPv4;

   } // MSSETUP()

   /* This function prepares the rule setup according configuration and calls tc with a batchjob */
   function enableConfig($state = 0)
   {
      /* Get some config params */
      $this->inbound       = $this->parent->getOption("bw_inbound");
      $this->outbound      = $this->parent->getOption("bw_outbound");
      
      if($this->parent->getOption("imq_if") != "Y") {

	 /* 
	    Without IMQ we have to shape inbound traffic on the outgoing-interface
	    and outbound traffic on the incoming interface!
	 */
	 $this->in_interface  = $this->parent->getOption("out_interface");
	 $this->out_interface = $this->parent->getOption("in_interface");

      }
      else {
      
	 $this->in_interface  = $this->parent->getOption("in_interface");
	 $this->out_interface = $this->parent->getOption("out_interface");

      }

      $this->ack_sl	   = $this->parent->getOption("ack_sl");

      if($state)
	 $this->parent->screen = $state;

      if(!isset($this->parent->screen))
	 $this->parent->screen = 0;

      switch($this->parent->screen) {

         default:
	    break;

         /* Show ruleset */
         case 1:

	    /* If authentication is enabled, check permissions */
	    if($this->parent->getOption("authentication") == "Y" &&
	       !$this->parent->checkPermissions("user_show_rules")) {

	       $this->parent->printError("<img src=\"". ICON_HOME ."\" alt=\"home icon\" />&nbsp;MasterShaper Ruleset - Show rules", "You do not have enough permissions to access this module!");
	       return 0;

	    }

	    $this->initRules();
	    $this->showIt();
	    break;

         /* Load ruleset */
         case 2:

	    /* If authentication is enabled, check permissions */
	    if(!$this->parent->fromcmd && 
	       $this->parent->getOption("authentication") == "Y" &&
	       !$this->parent->checkPermissions("user_load_rules")) {

	       $this->parent->printError("<img src=\"". ICON_HOME ."\" alt=\"home icon\" />&nbsp;MasterShaper Ruleset - Load rules", "You do not have enough permissions to access this module!");
	       return 0;

	    }

	    if(!$this->parent->fromcmd && !isset($_GET['loading'])) {
      
	       $this->parent->startTable("<img src=\"". ICON_OPTIONS ."\" alt=\"option icon\" />&nbsp;Loading MasterShaper rulset");
?>
   <table style="width: 100%; text-align: center;" class="withborder2">
    <tr>
     <td>
      Please wait...
     </td>
    </tr>
   </table>
   <script type="text/javascript">
      location.href = "<? print $_SERVER['REQUEST_URI'] . "&loading=1"; ?>";
   </script>
<?
	       $this->parent->closeTable(); 
	    }
	    else {

	       $this->initRules();
	       $retval = $this->doIt();

	       if(!$retval)
		  $this->parent->setOption("reload_timestamp", mktime());

            }
	    break;

         /* Load ruleset (debug mode) */
	 case 3:

	    /* If authentication is enabled, check permissions */
	    if($this->parent->getOption("authentication") == "Y" &&
	       !$this->parent->checkPermissions("user_load_rules")) {

	       $this->parent->printError("<img src=\"". ICON_HOME ."\" alt=\"home icon\" />&nbsp;MasterShaper Ruleset - Load rules", "You do not have enough permissions to access this module!");
	       return 0;

	    }

	    $this->initRules();
	    $retval = $this->doItLineByLine();

            if(!$retval)
	       $this->parent->setOption("reload_timestamp", mktime());

	    break;

         /* Unload ruleset */
	 case 4:

	    /* If authentication is enabled, check permissions */
	    if(!$this->parent->fromcmd &&
	       $this->parent->getOption("authentication") == "Y" &&
	       !$this->parent->checkPermissions("user_load_rules")) {

	       $this->parent->printError("<img src=\"". ICON_HOME ."\" alt=\"home icon\" />&nbsp;MasterShaper Ruleset - Unload rules", "You do not have enough permissions to access this module!");
	       return 0;

	    }

            /* Delete current root qdiscs */
	    if($this->in_interface != "")
               $this->delQdisc($this->in_interface);

	    if($this->out_interface != "")
	       $this->delQdisc($this->out_interface);

            $this->delIptablesRules();

            if(!$this->parent->fromcmd) {

	       $this->parent->startTable("<img src=\"". ICON_OPTIONS ."\" alt=\"option icon\" />&nbsp;Unload MasterShape Ruleset");
	       
?>
    <table style="width: 100%; text-align: center;" class="withborder2">
     <tr>
      <td>
       <img src="<? print ICON_ACTIVE; ?>">&nbsp;
       MasterShaper Ruleset has been unloaded.
      </td>
     </tr>
    </table>
<?

	       $this->parent->closeTable();

	    }

	    $this->parent->setShaperStatus(false);

	    break;

      }

   } // enableConfig()

   function iptInitRules()
   {
      $this->addRule(MS_IN, IPT_BIN ." -t mangle -N ms-all");
      $this->addRule(MS_IN, IPT_BIN ." -t mangle -N ms-all-chains");
      $this->addRule(MS_IN, IPT_BIN ." -t mangle -N ms-prerouting");
      $this->addRule(MS_IN, IPT_BIN ." -t mangle -A PREROUTING -j ms-prerouting");

      /* We must restore the connection mark in PREROUTING table first! */
      $this->addRule(MS_IN, IPT_BIN ." -t mangle -A ms-prerouting -j CONNMARK --restore-mark");

      if($this->parent->getOption("msmode") == "router") {

         if($this->in_interface != "") {

            $this->addRule(MS_IN, IPT_BIN ." -t mangle -A FORWARD -o ". $this->in_interface ." -j ms-all");
	    $this->addRule(MS_IN, IPT_BIN ." -t mangle -A POSTROUTING -o ". $this->in_interface ." -j ms-all-chains");

	 }

	 if($this->out_interface != "") {

	    $this->addRule(MS_OUT, IPT_BIN ." -t mangle -A FORWARD -o ". $this->out_interface ." -j ms-all");
	    $this->addRule(MS_OUT, IPT_BIN ." -t mangle -A POSTROUTING -o ". $this->out_interface ." -j ms-all-chains");

	 }

      }
      elseif($this->parent->getOption("msmode") == "bridge") {

	 /* we must mark outgoing packets on the incoming interface first*/

         if($this->in_interface != "") {

	    $this->addRule(MS_IN, IPT_BIN ." -t mangle -A ms-prerouting -m physdev --physdev-in ". $this->out_interface ." -j ms-all");
	    $this->addRule(MS_OUT, IPT_BIN ." -t mangle -A ms-prerouting -m physdev --physdev-in ". $this->in_interface ." -j ms-all");

	 }
	 if($this->out_interface != "") {

	    $this->addRule(MS_IN, IPT_BIN ." -t mangle -A POSTROUTING -m physdev --physdev-out ". $this->in_interface ." -j ms-all-chains");
	    $this->addRule(MS_OUT, IPT_BIN ." -t mangle -A POSTROUTING -m physdev --physdev-out ". $this->out_interface ." -j ms-all-chains");

	 }
      }

   } // iptInitRules()

   function addRuleComment($ruleset, $text)
   {
      $this->addRule($ruleset, "######### ". $text);

   } // addRuleComment()

   function addRule($rule, $cmd)
   {

      switch($rule) {

         case MS_PRE:
	    array_push($this->ms_pre, $cmd);
	    break;
	 case MS_IN:
	    array_push($this->ms_in, $cmd);
	    break;
	 case MS_OUT:
	    array_push($this->ms_out, $cmd);
	    break;
	 case MS_POST:
	    array_push($this->ms_post, $cmd);
	    break;

      }

   } // addRule()

   function getRules($rules)
   {

      switch($rules) {

         case MS_PRE:
	    return $this->ms_pre;
	    break;
	 case MS_IN:
	    return $this->ms_in;
	    break;
	 case MS_OUT:
	    return $this->ms_out;
	    break;
	 case MS_POST:
	    return $this->ms_post;
	    break;

      }
      
   } // getRules()

   function initRules()
   {
      /* The most tc_ids will change, so we delete the current known tc_ids */
      $this->db->db_query("DELETE FROM shaper_tc_ids");

      /* Initial tc setup */
      if($this->in_interface != "")
	 $this->addRootQdisc(MS_IN,  $this->in_interface, "1:", $this->inbound);
      if($this->out_interface != "")
	 $this->addRootQdisc(MS_OUT, $this->out_interface, "1:", $this->outbound);

      /* Initial iptables rules */
      if($this->parent->getOption("filter") == "ipt") 
	 $this->iptInitRules();

      if($this->in_interface != "") {

	 $this->addInitClass(MS_IN,  $this->in_interface, "1:", "1:1", $this->inbound);
	 $this->addInitFilter(MS_IN,  $this->in_interface, "1:0");

      }

      if($this->out_interface != "") {

	 $this->addInitClass(MS_OUT, $this->out_interface, "1:", "1:1", $this->outbound);
	 $this->addInitFilter(MS_OUT, $this->out_interface, "1:0");

      }

      /* ACK options */
      if($this->ack_sl != 0) {

	 if($this->in_interface != "") {

	    $this->addRuleComment(MS_IN, "boost ACK packets");
	    $this->addInClass(MS_IN, $this->in_interface, "1:1", "1:2", $this->getServiceLevel($this->ack_sl));
	    $this->addSubQdisc(MS_IN, $this->in_interface, "2:", "1:2", $this->getServiceLevel($this->ack_sl));
	    $this->addAckFilter(MS_IN, $this->in_interface, "1:1", "ack", "1:2", "1");

	 }
	 else {

	    $this->addRuleComment(MS_OUT, "boost ACK packets");
	    $this->addInClass(MS_OUT, $this->out_interface, "1:1", "1:2", $this->getServiceLevel($this->ack_sl));
	    $this->addSubQdisc(MS_OUT, $this->out_interface, "2:", "1:2", $this->getServiceLevel($this->ack_sl));
	    $this->addAckFilter(MS_OUT, $this->out_interface, "1:1", "ack", "1:2", "1");

	 }
	       
      }

      if($this->in_interface != "") {

	 $this->addRuleComment(MS_IN,  "Incoming Rules");
	 $this->buildInChains(MS_IN,   $this->in_interface);

      }

      if($this->out_interface != "") {

	 $this->addRuleComment(MS_OUT, "Outgoing Rules");
	 $this->buildOutChains(MS_OUT, $this->out_interface);

      }

      /* If we are in iptables mode we have to save the marking in the last rule */
      if($this->parent->getOption("filter") == "ipt") 
	 $this->addRule(MS_OUT, IPT_BIN ." -t mangle -A ms-prerouting -j CONNMARK --save-mark");

   } // initRules()

   /* Delete parent qdiscs */
   function delQdisc($interface)
   {
      $this->runProc("tc", TC_BIN . " qdisc del dev ". $interface ." root", TRUE);

   } // delQdisc()

   function delIptablesRules()
   {
      $this->runProc("cleanup");

   } // delIptablesRules

   /* Adds root qdiscs according qdisc mode to the specified interface */
   function addRootQdisc($ms, $interface, $id, $bandwidth, $mode = "")
   {
      switch($mode) {
         default:
	    switch($this->parent->getOption("classifier")) {
	       default:
	       case 'HTB':
		  $this->addRule($ms, TC_BIN ." qdisc add dev ". $interface ." handle ". $id ." root htb default 1");
		  break;
	       case 'HFSC':
		  $this->addRule($ms, TC_BIN ." qdisc add dev ". $interface ." handle ". $id ." root hfsc default 1");
		  break;
	       case 'CBQ':
		  $this->addRule($ms, TC_BIN ." qdisc add dev ". $interface ." handle ". $id ." root cbq avpkt 1000 bandwidth ". $bandwidth ."Kbit cell 8");
		  break;
	    }
	    break;
      }

   } // addRootQdisc()

   /* Adds qdisc at the end of class for final queuing mechanism */
   function addSubQdisc($ms, $interface, $child, $parent, $sl)
   {
      $string = TC_BIN ." qdisc add dev ". $interface ." handle ". $child ." parent ". $parent ." ";

      switch($sl->sl_qdisc) {

	 default:
	 case 'SFQ':
	    $string.="sfq";
	    break;

	 case 'ESFQ':
	    $string.= "esfq ". $this->getESFQParams($sl);
	    break;

	 case 'HFSC':
	    $string.= "hfsc";
	    break;

	 case 'NETEM':
	    $string.= "netem ". $this->getNETEMParams($sl);
	    break;

      }

      $this->addRule($ms, $string);

   } // addSubQdisc()
	
   /* Adds the top parent class definition */
   function addInitClass($ms, $interface, $parent, $classid, $bw)
   {
      switch($this->parent->getOption("classifier")) {
	 default:
	 case 'HTB':
	    $this->addRule($ms, TC_BIN ." class add dev ". $interface ." parent ". $parent ." classid ". $classid ." htb rate ". $bw ."Kbit");
	    break;
	 case 'HFSC':
	    $this->addRule($ms, TC_BIN ." class add dev ". $interface ." parent ". $parent ." classid ". $classid ." hfsc sc rate ". $bw ."Kbit ul rate ". $bw ."Kbit");
	    break;
	 case 'CBQ':
	    $this->addRule($ms, TC_BIN ." class add dev ". $interface ." parent ". $parent ." classid ". $classid ." cbq bandwidth ". $bw ."Kbit rate ". $bw ."Kbit allot 1000 prio 3 bounded");
	    break;
      }

   } // addInitClass()

   /* Adds a class definition for a inbound chain */
   function addInClass($ms, $interface, $parent, $classid, $sl)
   {
      $string = TC_BIN ." class add dev ". $interface ." parent ". $parent ." classid ". $classid;

      switch($this->parent->getOption("classifier")) {

	 default:
	 case 'HTB':

	    $string.= " htb ";
	
	    if($sl->sl_htb_bw_in_rate != "" && $sl->sl_htb_bw_in_rate > 0) {
	       
	       $string.= " rate ". $sl->sl_htb_bw_in_rate ."Kbit ";
	       
	       if($sl->sl_htb_bw_in_ceil != "" && $sl->sl_htb_bw_in_ceil > 0)
		  $string.= "ceil ". $sl->sl_htb_bw_in_ceil ."Kbit ";
	       if($sl->sl_htb_bw_in_burst != "" && $sl->sl_htb_bw_in_burst > 0)
		  $string.= "burst ". $sl->sl_htb_bw_in_burst ."Kbit ";
	       if($sl->sl_htb_priority > 0) 
		  $string.= "prio ". $sl->sl_htb_priority;

	    }	
	    else {
	       
	       $string.= " rate ". $this->parent->getOption("bw_inbound") ."Kbit ";

	       if($sl->sl_htb_priority > 0)
		  $string.= "prio ". $sl->sl_htb_priority;

	    }
	    $string.= " quantum 1532";
	    break;
				
	 case 'HFSC':

	    $string.= " hfsc sc ";

	    if(isset($sl->sl_hfsc_in_umax) && $sl->sl_hfsc_in_umax != "" && $sl->sl_hfsc_in_umax > 0) 
	       $string.= " umax ". $sl->sl_hfsc_in_umax ."b ";
	    if(isset($sl->sl_hfsc_in_dmax) && $sl->sl_hfsc_in_dmax != "" && $sl->sl_hfsc_in_dmax > 0)
	       $string.= " dmax ". $sl->sl_hfsc_in_dmax ."ms ";
	    if(isset($sl->sl_hfsc_in_rate) && $sl->sl_hfsc_in_rate != "" && $sl->sl_hfsc_in_rate > 0)
	       $string.= " rate ". $sl->sl_hfsc_in_rate ."Kbit ";
	    if(isset($sl->sl_hfsc_in_ulrate) && $sl->sl_hfsc_in_ulrate != "" && $sl->sl_hfsc_in_ulrate > 0)
	       $string.= " ul rate ". $sl->sl_hfsc_in_ulrate ."Kbit";

	    $string.= " rt ";

	    if(isset($sl->sl_hfsc_in_umax) && $sl->sl_hfsc_in_umax != "" && $sl->sl_hfsc_in_umax > 0) 
	       $string.= " umax ". $sl->sl_hfsc_in_umax ."b ";
	    if(isset($sl->sl_hfsc_in_dmax) && $sl->sl_hfsc_in_dmax != "" && $sl->sl_hfsc_in_dmax > 0)
	       $string.= " dmax ". $sl->sl_hfsc_in_dmax ."ms ";
	    if(isset($sl->sl_hfsc_in_rate) && $sl->sl_hfsc_in_rate != "" && $sl->sl_hfsc_in_rate > 0)
	       $string.= " rate ". $sl->sl_hfsc_in_rate ."Kbit ";
	    if(isset($sl->sl_hfsc_in_ulrate) && $sl->sl_hfsc_in_ulrate != "" && $sl->sl_hfsc_in_ulrate > 0)
	       $string.= " ul rate ". $sl->sl_hfsc_in_ulrate ."Kbit";
	    break;

	 case 'CBQ':

	    $string.= " cbq bandwidth ". $this->inbound ."Kbit rate ". $sl->sl_cbq_in_rate ."Kbit allot 1500 prio ". $sl->sl_cbq_in_priority ." avpkt 1000";
	    if($sl->sl_cbq_bounded == "Y")
	       $string.= " bounded";
	    break;

      }
		
	
      $this->addRule($ms, $string);

   } // addInClass()

   /* Adds a class definition for a outbound chain */
   function addOutClass($ms, $interface, $parent, $classid, $sl)
   {
      $string = TC_BIN ." class add dev ". $interface ." parent ". $parent ." classid ". $classid;

      switch($this->parent->getOption("classifier")) {

	 default:
	 case 'HTB':

	    $string.= " htb ";

	    if($sl->sl_htb_bw_out_rate != "" && $sl->sl_htb_bw_out_rate > 0) {
		
	       $string.= " rate ". $sl->sl_htb_bw_out_rate ."Kbit ";
	       if($sl->sl_htb_bw_out_ceil > 0 && $sl->sl_htb_bw_out_ceil != "")
		  $string.= "ceil ". $sl->sl_htb_bw_out_ceil ."Kbit ";
	       if($sl->sl_htb_bw_out_burst > 0 && $sl->sl_htb_bw_out_burst != "")
		  $string.= "burst ". $sl->sl_htb_bw_out_burst ."Kbit ";
	       if($sl->sl_htb_priority > 0)
		  $string.= "prio ". $sl->sl_htb_priority;
	    }		
	    else {
	       $string.= " rate ". $this->parent->getOption("bw_outbound") ."Kbit ";

	       if($sl->sl_htb_priority > 0)
		  $string.= "prio ". $sl->sl_htb_priority;

	    }
	    
	    $string.= " quantum 1532";

	    break;
			
	 case 'HFSC':

	    $string.= " hfsc sc ";

	    if(isset($sl->sl_hfsc_out_umax) && $sl->sl_hfsc_out_umax != "" && $sl->sl_hfsc_out_umax > 0) 
	       $string.= " umax ". $sl->sl_hfsc_out_umax ."b ";
	    if(isset($sl->sl_hfsc_out_dmax) && $sl->sl_hfsc_out_dmax != "" && $sl->sl_hfsc_out_dmax > 0)
	       $string.= " dmax ". $sl->sl_hfsc_out_dmax ."ms ";
	    if(isset($sl->sl_hfsc_out_rate) && $sl->sl_hfsc_out_rate != "" && $sl->sl_hfsc_out_rate > 0)
	       $string.= " rate ". $sl->sl_hfsc_out_rate ."Kbit ";
	    if(isset($sl->sl_hfsc_out_ulrate) && $sl->sl_hfsc_out_ulrate != "" && $sl->sl_hfsc_out_ulrate > 0)
	       $string.= " ul rate ". $sl->sl_hfsc_out_ulrate ."Kbit";

	    $string.= " rt ";

	    if(isset($sl->sl_hfsc_out_umax) && $sl->sl_hfsc_out_umax != "" && $sl->sl_hfsc_out_umax > 0) 
	       $string.= " umax ". $sl->sl_hfsc_out_umax ."b ";
	    if(isset($sl->sl_hfsc_out_dmax) && $sl->sl_hfsc_out_dmax != "" && $sl->sl_hfsc_out_dmax > 0)
	       $string.= " dmax ". $sl->sl_hfsc_out_dmax ."ms ";
	    if(isset($sl->sl_hfsc_out_rate) && $sl->sl_hfsc_out_rate != "" && $sl->sl_hfsc_out_rate > 0)
	       $string.= " rate ". $sl->sl_hfsc_out_rate ."Kbit ";
	    if(isset($sl->sl_hfsc_out_ulrate) && $sl->sl_hfsc_out_ulrate != "" && $sl->sl_hfsc_out_ulrate > 0)
	       $string.= " ul rate ". $sl->sl_hfsc_out_ulrate ."Kbit";

	    break;

	 case 'CBQ':

	    $string.= " cbq bandwidth ". $this->outbound ."Kbit rate ". $sl->sl_cbq_out_rate ."Kbit allot 1500 prio ". $sl->sl_cbq_out_priority ." avpkt 1000";
	    if($sl->sl_cbq_bounded == "Y")
	       $string.= " bounded";
	    break;

      }

      $this->addRule($ms, $string);

   } // addOutClass()

   /* Adds the top level filter which brings traffic into the initClass */
   function addInitFilter($ms, $interface, $parent)
   {
      $this->addRule($ms, TC_BIN ." filter add dev ". $interface ." parent ". $parent ." protocol all u32 match u32 0 0 classid 1:1");

   } // addInitFilter()

   /* create IP/host matching filters */
   function addHostFilter($ms, $interface, $parent, $option, $params1 = "", $params2 = "", $params3 = "", $params4 = "", $params5 = "", $params6 = "")
   {
      switch($this->parent->getOption("filter")) {
	 
	 default:
	 case 'tc':

	    if($params6 == "swap_in_out") {

	       $tmp = $params1->chain_src_target;
	       $params1->chain_src_target = $params1->chain_dst_target;
	       $params1->chain_dst_target = $tmp;

	    }

	    if($params1->chain_src_target != 0 && $params1->chain_dst_target == 0) {

	       $hosts = $this->getTargetHosts($params1->chain_src_target);
	       foreach($hosts as $host) {

		  if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $host) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $host)) {
		     $this->addRule($ms, TC_BIN ." filter add dev ". $interface ." parent ". $parent ." protocol all prio 2 u32 match ip ". $params3 ." ". $host ." flowid ". $params2 ."");
		  }		 
		  else {
		     if(preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $host))
			list($m1, $m2, $m3, $m4, $m5, $m6) = split(":", $host);
                     else
			list($m1, $m2, $m3, $m4, $m5, $m6) = split("-", $host);

                     $this->addRule($ms, TC_BIN ." filter add dev ". $interface ." parent ". $parent ." protocol all prio 2 u32 match u16 0x0800 0xffff at -2 match u16 0x". $m5 . $m6 ." 0xffff at -4 match u32 0x". $m1 . $m2 . $m3 . $m4 ."  0xffffffff at -8 flowid ". $params2 ."");
		  }

	       }
	    }
	    elseif($params1->chain_src_target == 0 && $params1->chain_dst_target != 0) {

	       $hosts = $this->getTargetHosts($params1->chain_dst_target);
	       foreach($hosts as $host) {
	    
	          /* IP match */
		  if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $host) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $host)) {
		     $this->addRule($ms, TC_BIN ." filter add dev ". $interface ." parent ". $parent ." protocol all prio 2 u32 match ip ". $params4 ." ". $host ." flowid ". $params2 ."");
		  }
		  /* MAC match */
		  else {
		     if(preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $host))
			list($m1, $m2, $m3, $m4, $m5, $m6) = split(":", $host);
                     else
			list($m1, $m2, $m3, $m4, $m5, $m6) = split("-", $host);

                     $this->addRule($ms, TC_BIN ." filter add dev ". $interface ." parent ". $parent ." protocol all prio 2 u32 match u16 0x0800 0xffff at -2 match u32 0x". $m3 . $m4 . $m5 .$m6 ." 0xffffffff at -12 match u16 0x". $m1 . $m2 ." 0xffff at -14 flowid ". $params2 ."");
		  }
	       }
	    }
	    elseif($params1->chain_src_target != 0 && $params1->chain_dst_target != 0) {

	       $src_hosts = $this->getTargetHosts($params1->chain_src_target);
	       foreach($src_hosts as $src_host) {

		  /* IP maatch */
		  if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $src_host) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $src_host)) {

		     $string = TC_BIN ." filter add dev ". $interface ." parent ". $parent ." protocol all prio 2 u32 match ip ". $params3 ." ". $src_host ." ";
		  }
		  /* MAC match */
		  else {
		     if(preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $src_host))
			list($m1, $m2, $m3, $m4, $m5, $m6) = split(":", $src_host);
		     else
			list($m1, $m2, $m3, $m4, $m5, $m6) = split("-", $src_host);

		     $string = TC_BIN ." filter add dev ". $interface ." parent ". $parent ." protocol all prio 2 u32 match u16 0x0800 0xffff at -2 match u16 0x". $m5 . $m6 ." 0xffff at -4 match u32 0x". $m1 . $m2 . $m3 . $m4 ." 0xffffffff at -8 ";
		  }

		  $dst_hosts = $this->getTargetHosts($params1->chain_dst_target);
		  foreach($dst_hosts as $dst_host) {

                     /* IP match */
		     if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $dst_host) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $dst_host)) {
			$this->addRule($ms, $string . "match ip ". $params4 ." ". $dst_host ." flowid ". $params2 ."");
		     }
		     /* MAC match */
		     else {

			if(preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $dst_host))
			   list($m1, $m2, $m3, $m4, $m5, $m6) = split(":", $dst_host);
			else
			   list($m1, $m2, $m3, $m4, $m5, $m6) = split("-", $dst_host);

			$this->addRule($ms, $string . "match u16 0x0800 0xffff at -2 match u32 0x". $m3 . $m4 . $m5 .$m6 ." 0xffffffff at -12 match u16 0x". $m1 . $m2 ." 0xffff at -14 flowid ". $params2 ."");
											  
		     }
		  }
	       }
	    }
	    break;

	 case 'ipt':

	    if($this->parent->getOption("msmode") == "router") {
	       $string = IPT_BIN ." -t mangle -A ms-all -o ". $interface;
	    }
	    elseif($this->parent->getOption("msmode") == "bridge") {
	       $string = IPT_BIN ." -t mangle -A ms-all -m physdev --physdev-in ". $params5;
	    }

	    if($params6 == "swap_in_out") {

	       $tmp = $params1->chain_src_target;
	       $params1->chain_src_target = $params1->chain_dst_target;
	       $params1->chain_dst_target = $tmp;

	    }

	    if($params1->chain_src_target != 0 && $params1->chain_dst_target == 0) {

	       $hosts = $this->getTargetHosts($params1->chain_src_target);
	       foreach($hosts as $host) {
	    
		  if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $host) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $host)) {

		     $this->addRule($ms, $string ." -s ". $host ." -j MARK --set-mark ". $this->getConnmarkId($interface, $params2));
		     $this->addRule($ms, $string ." -s ". $host ." -j RETURN");

		  }
		  else {

		     $this->addRule($ms, $string ." -m mac --mac-source ". $host ." -j MARK --set-mark ". $this->getConnmarkId($interface, $params2));
		     $this->addRule($ms, $string ." -m mac --mac-source ". $host ." -j RETURN");

		  }
	       }
	    }
	    elseif($params1->chain_src_target == 0 && $params1->chain_dst_target != 0) {

	       $hosts = $this->getTargetHosts($params1->chain_dst_target);
	       foreach($hosts as $host) {
	    
		  if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $host) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $host)) {
		  
		     $this->addRule($ms, $string ." -d ". $host ." -j MARK --set-mark ". $this->getConnmarkId($interface, $params2));
		     $this->addRule($ms, $string ." -d ". $host ." -j RETURN");
		     
		  }
		  else {
		  
		     $this->addRule($ms, $string ." -m mac --mac-source ". $host ." -j MARK --set-mark ". $this->getConnmarkId($interface, $params2));
		     $this->addRule($ms, $string ." -m mac --mac-source ". $host ." -j RETURN");

		  }
	       }
	    }
	    elseif($params1->chain_src_target != 0 && $params1->chain_dst_target != 0) {

	       $src_hosts = $this->getTargetHosts($params1->chain_src_target);
	       $dst_hosts = $this->getTargetHosts($params1->chain_dst_target);

	       foreach($src_hosts as $src_host) {

		  if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $src_host) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $src_host)) {
		  
		     foreach($dst_hosts as $dst_host) {
	    
			if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $dst_host) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $dst_host)) {
			   $this->addRule($ms, $string ." -s ". $src_host ." -d ". $dst_host ." -j MARK --set-mark ". $this->getConnmarkId($interface, $params2));
			   $this->addRule($ms, $string ." -s ". $src_host ." -d ". $dst_host ." -j RETURN");
			}
			else {
			   $this->addRule($ms, $string ." -m mac --mac-source ". $src_host ." -j MARK --set-mark ". $this->getConnmarkId($interface, $params2));
			   $this->addRule($ms, $string ." -m mac --mac-source ". $dst_host ." -j RETURN");
			}
		     }
		  }
	       }
	    }
	    break;
      }

   } // addHostFilter()

   function addFallbackFilter($ms, $interface, $parent, $option, $params1 = "", $params2 = "", $params3 = "", $params4 = "", $params5 = "", $params6 = "")
   {
      switch($this->parent->getOption("filter")) {

	 default:
	 case 'tc':
	    $this->addRule($ms, TC_BIN ." filter add dev ". $interface ." parent ". $parent ." protocol all prio 5 u32 match u32 0 0 flowid ". $params1);
	    break;
	 case 'ipt':
	    $this->addRule($ms, IPT_BIN ." -t mangle -A ms-chain-". $interface ."-". $parent ." -j CLASSIFY --set-class ". $params1);
	    $this->addRule($ms, IPT_BIN ." -t mangle -A ms-chain-". $interface ."-". $parent ." -j RETURN");
	    break;
      }

   } // addFallbackFilter()

   function addMatchallFilter($ms, $interface, $parent, $option, $params1 = "", $params2 = "", $params3 = "", $params4 = "", $params5 = "", $params6 = "")
   {
      switch($this->parent->getOption("filter")) {
	 case 'tc':
	    $this->addRule($ms, TC_BIN ." filter add dev ". $interface ." parent ". $parent ." protocol all prio 2 u32 match u32 0 0 classid ". $params1);
	    break;
	 case 'ipt':
	    if($this->parent->getOption("msmode") == "router") {
	       $this->addRule($ms, IPT_BIN ." -t mangle -A ms-all -o ". $interface ." -j ms-chain-". $interface ."-". $params1);
	    }
	    elseif($this->parent->getOption("msmode") == "bridge") {
	       $this->addRule($ms, IPT_BIN ." -t mangle -A ms-all -m physdev --physdev-in ". $params2 ." -j MARK --set-mark ". $this->getConnmarkId($interface, $params1));
	       $this->addRule($ms, IPT_BIN ." -t mangle -A ms-all -m physdev --physdev-in ". $params2 ." -j RETURN");
	    }
	    break;
      }

   } // addMatchallFilter()

   function addAckFilter($ms, $interface, $parent, $option, $params1 = "", $params2 = "", $params3 = "", $params4 = "", $params5 = "", $params6 = "")
   {
      switch($this->parent->getOption("filter")) {

	 default:
	 case 'tc':
	    $this->addRule($ms, TC_BIN ." filter add dev ". $interface ." parent ". $parent ." protocol ip prio 1 u32 match ip protocol 6 0xff match u8 0x05 0x0f at 0 match u16 0x0000 0xffc0 at 2 match u8 0x10 0xff at 33 flowid ". $params1);
	    break;
	 case 'ipt':
	    $this->addRule($ms, IPT_BIN ." -t mangle -A ms-all-chains -p tcp -m length --length :64 -j CLASSIFY --set-class ". $params1);
	    $this->addRule($ms, IPT_BIN ." -t mangle -A ms-all-chains -p tcp -m length --length :64 -j RETURN");
	    break;
      }

   } // addAckFilter()
	
   /* Adds a matching filter to get traffic into the pipes */
   function addPipeFilter($ms, $interface, $parent, $option, $params1 = "", $params2 = "", $params3 = "", $params4 = "", $params5 = "", $params6 = "")
   {
      $filter    = $params1;
      $my_id     = $params2;
      $direction = $params3;
      $tmp_str   = "";
      $tmp_array = array();

      switch($this->parent->getOption("filter")) {

	 default:
	 case 'tc':

	    $string = TC_BIN ." filter add dev ". $interface ." parent ". $parent ." protocol all prio 1 [HOST_DEFS] ";

	    if($filter->filter_protocol_id >= 0) {

	       switch($this->getProtocolNumber($filter->filter_protocol_id)) {

		  /* TCP */
		  case 6:
		  /* UDP */
		  case 17:
		  /* IP */
		  case 4:

		     $string.= "match ip ";

		     $str_ports = "";
		     $cnt_ports = 0;
		     $ports = $this->getPorts($filter->filter_idx);

		     if($ports) {

			while($port = $ports->fetchRow()) {

			   $dst_ports = $this->extractPorts($port->port_number);

			   if($dst_ports != 0) {

			      foreach($dst_ports as $dst_port) {

				 $tmp_str = $string ." [DIRECTION] ". $dst_port ." 0xffff ";

				 if($filter->filter_tos >= 0)
				    $tmp_str.= "match ip tos ". $filter->filter_tos ." 0xff ";

				 switch($direction) {
				    case 1:
				       array_push($tmp_array, str_replace("[DIRECTION]", "dport", $tmp_str));
				       break;
				    case 2:
				       array_push($tmp_array, str_replace("[DIRECTION]", "sport", $tmp_str));
				       break;
				    case 3:
				       array_push($tmp_array, str_replace("[DIRECTION]", "dport", $tmp_str));
				       array_push($tmp_array, str_replace("[DIRECTION]", "sport", $tmp_str));
				       break;
				 }
			      }
			   }
			}
		     }
		     break;

		  default:

		     $string.= "match ip protocol ". $this->getProtocolNumber($filter->filter_protocol_id) ." 0xff ";
		     array_push($tmp_array, $string);
		     break;
	       }
	    }
	    else {

	      array_push($tmp_array, $string);

	    }

	    if($params1->filter_src_target != 0 && $params1->filter_dst_target == 0) {

	       $hosts = $this->getTargetHosts($params1->filter_src_target);
	       foreach($hosts as $host) {

		  if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $host) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $host)) {

		     foreach($tmp_array as $tmp_arr) {
		     
			switch($filter->filter_direction) {
			   case 1:
			      $this->addRule($ms, str_replace("[HOST_DEFS]", "u32 match ip src ". $host, $tmp_arr) ." flowid ". $my_id);
			      break;
			   case 2:
			      $this->addRule($ms, str_replace("[HOST_DEFS]", "u32 match ip src ". $host, $tmp_arr) ." flowid ". $my_id);
			      $this->addRule($ms, str_replace("[HOST_DEFS]", "u32 match ip dst ". $host, $tmp_arr) ." flowid ". $my_id);
			      break;
			}

		     }  
		  }		 
		  else {

		     foreach($tmp_array as $tmp_arr) {

			if(preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $host))
			   list($m1, $m2, $m3, $m4, $m5, $m6) = split(":", $host);
			else
			   list($m1, $m2, $m3, $m4, $m5, $m6) = split("-", $host);

		        switch($filter->filter_direction) {
			   case 1:
			      $this->addRule($ms, str_replace("[HOST_DEFS]", "u32 match u16 0x0800 0xffff at -2 match u16 0x". $m5 . $m6 ." 0xffff at -4 match u32 0x". $m1 . $m2 . $m3 . $m4 ." 0xffffffff at -8 ", $tmp_arr) ." flowid ". $my_id);
			      break;
			   case 2:
			      $this->addRule($ms, str_replace("[HOST_DEFS]", "u32 match u16 0x0800 0xffff at -2 match u16 0x". $m5 . $m6 ." 0xffff at -4 match u32 0x". $m1 . $m2 . $m3 . $m4 ." 0xffffffff at -8 ", $tmp_arr) ." flowid ". $my_id);
			      $this->addRule($ms, str_replace("[HOST_DEFS]", "u32 match u16 0x0800 0xffff at -2 match u32 0x". $m3 . $m4 . $m5 .$m6 ." 0xffffffff at -12 match u16 0x". $m1 . $m2 ." 0xffff at -14 ", $tmp_arr) ." flowid ". $my_id);
			      break;

			}
                     }
		  }
	       }
	    }
	    elseif($params1->filter_src_target == 0 && $params1->filter_dst_target != 0) {


	       $hosts = $this->getTargetHosts($params1->filter_dst_target);
	       foreach($hosts as $host) {
	    
		  if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $host) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $host)) {

		     foreach($tmp_array as $tmp_arr) {

			switch($filter->filter_direction) {
			   case 1:
			       $this->addRule($ms, str_replace("[HOST_DEFS]", "u32 match ip dst ". $host, $tmp_arr) ." flowid ". $my_id);
			       break;
			   case 2:
			       $this->addRule($ms, str_replace("[HOST_DEFS]", "u32 match ip dst ". $host, $tmp_arr) ." flowid ". $my_id);
			       $this->addRule($ms, str_replace("[HOST_DEFS]", "u32 match ip src ". $host, $tmp_arr) ." flowid ". $my_id);
			       break;
			}
		     }
		  }
		  else {

		     foreach($tmp_array as $tmp_arr) {

			if(preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $host))
			   list($m1, $m2, $m3, $m4, $m5, $m6) = split(":", $host);
			else
			   list($m1, $m2, $m3, $m4, $m5, $m6) = split("-", $host);

		        switch($filter->filter_direction) {
                           case 1:
			      $this->addRule($ms, str_replace("[HOST_DEFS]", "u32 match u16 0x0800 0xffff at -2 match u32 0x". $m3 . $m4 . $m5 .$m6 ." 0xffffffff at -12 match u16 0x". $m1 . $m2 ." 0xffff at -14 ", $tmp_arr) ." flowid ". $my_id);
			      break;
			   case 2:
			      $this->addRule($ms, str_replace("[HOST_DEFS]", "u32 match u16 0x0800 0xffff at -2 match u32 0x". $m3 . $m4 . $m5 .$m6 ." 0xffffffff at -12 match u16 0x". $m1 . $m2 ." 0xffff at -14 ", $tmp_arr) ." flowid ". $my_id);
			      $this->addRule($ms, str_replace("[HOST_DEFS]", "u32 match u16 0x0800 0xffff at -2 match u16 0x". $m5 . $m6 ." 0xffff at -4 match u32 0x". $m1 . $m2 . $m3 . $m4 ." 0xffffffff at -8 ", $tmp_arr) ." flowid ". $my_id);
			      break;
			     
			}
                     }
		  }  
	       }
	    }
	    elseif($params1->filter_src_target != 0 && $params1->filter_dst_target != 0) {

	       $src_hosts = $this->getTargetHosts($params1->filter_src_target);

	       foreach($src_hosts as $src_host) {

                  /* IP match */
		  if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $src_host) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $src_host)) {
		     $tmp_str = "u32 match ip [DIR1] ". $src_host ." ";
		  }
		  /* MAC match */
		  else {
		     if(preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $src_host))
			list($sm1, $sm2, $sm3, $sm4, $sm5, $sm6) = split(":", $src_host);
		     else
			list($sm1, $sm2, $sm3, $sm4, $sm5, $sm6) = split("-", $src_host);
 
                     $tmp_str = "u32 [DIR1] [DIR2]";

		  }

		  $dst_hosts = $this->getTargetHosts($params1->filter_dst_target);
		  foreach($dst_hosts as $dst_host) {

		     /* IP match */
		     if(!preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $dst_host) && !preg_match("/(.*)-(.*)-(.*)-(.*)-(.*)-(.*)/", $dst_host)) {

			foreach($tmp_array as $tmp_arr) {

			   switch($filter->filter_direction) {

			      case 1:
				 $string = str_replace("[HOST_DEFS]", $tmp_str . "match ip [DIR2] ". $dst_host, $tmp_arr);
				 $string = str_replace("[DIR1]", "src", $string);
				 $string = str_replace("[DIR2]", "dst", $string);
				 $this->addRule($ms, $string ." flowid ". $my_id);
				 break;

			      case 2:
				 $string = str_replace("[HOST_DEFS]", $tmp_str . "match ip [DIR2] ". $dst_host, $tmp_arr);
				 $string = str_replace("[DIR1]", "src", $string);
				 $string = str_replace("[DIR2]", "dst", $string);
				 $this->addRule($ms, $string ." flowid ". $my_id);
				 $string = str_replace("[HOST_DEFS]", $tmp_str . "match ip [DIR2] ". $dst_host, $tmp_arr);
				 $string = str_replace("[DIR1]", "dst", $string);
				 $string = str_replace("[DIR2]", "src", $string);
				 $this->addRule($ms, $string ." flowid ". $my_id);
				 break;

			   }
			}
		     }
		     else {

			if(preg_match("/(.*):(.*):(.*):(.*):(.*):(.*)/", $dst_host))
			   list($dm1, $dm2, $dm3, $dm4, $dm5, $dm6) = split(":", $dst_host);
			else
			   list($dm1, $dm2, $dm3, $dm4, $dm5, $dm6) = split("-", $dst_host);

                        foreach($tmp_array as $tmp_arr) {

			   switch($filter->filter_direction) {

			      case 1:
				 $string = str_replace("[HOST_DEFS]", $tmp_str . "match ip [DIR2] ". $dst_host, $tmp_arr);
				 $string = str_replace("[DIR1]", "src", $string);
				 $string = str_replace("[DIR2]", "dst", $string);
				 $this->addRule($ms, $string ." flowid ". $my_id);
				 break;

			      case 2:
				 $string = str_replace("[HOST_DEFS]", $tmp_str, $tmp_arr);
				 $string = str_replace("[DIR1]", "match u16 0x0800 0xffff at -2 match u16 0x". $sm5 . $sm6 ." 0xffff at -4 match u32 0x". $sm1 . $sm2 . $sm3 . $sm4 ." 0xffffffff at -8", $string);
				 $string = str_replace("[DIR2]", "match u16 0x0800 0xffff at -2 match u32 0x". $dm3 . $dm4 . $dm5 .$dm6 ." 0xffffffff at -12 match u16 0x". $dm1 . $dm2 ." 0xffff at -14", $string);
				 $this->addRule($ms, $string ." flowid ". $my_id);
				 $string = str_replace("[HOST_DEFS]", $tmp_str, $tmp_arr);
				 $string = str_replace("[DIR1]", "match u16 0x0800 0xffff at -2 match u32 0x". $sm3 . $sm4 . $sm5 .$sm6 ." 0xffffffff at -12 match u16 0x". $sm1 . $sm2 ." 0xffff at -14", $string);
				 $string = str_replace("[DIR2]", "match u16 0x0800 0xffff at -2 match u16 0x". $dm5 . $dm6 ." 0xffff at -4 match u32 0x". $dm1 . $dm2 . $dm3 . $dm4 ." 0xffffffff at -8", $string);
				 $this->addRule($ms, $string ." flowid ". $my_id);
				 break;

                           }
			}
		     }
		  }
	       }
	    }
	    else {

	       foreach($tmp_array as $tmp_arr)
		  $this->addRule($ms, str_replace("[HOST_DEFS]", "u32", $tmp_arr) ." flowid ". $my_id);

	    }
	    
	    break;

	 case 'ipt':

            $match_str = "";
	    $cnt_ports = 0;
	    $str_p2p   = "";
	    $match_ary = Array();
	    $proto_ary = Array();

	    /****************************************/
	    /* Construct a string with all used ipt */
	    /* matches.                             */
	    /****************************************/
 
            /* If this filter should match on ftp data connections add the rule here */
	    if($filter->filter_match_ftp_data == "Y") {

	       $this->addRule($ms, IPT_BIN ." -t mangle -A ms-chain-". $interface ."-". $parent ." --match conntrack --ctproto tcp --ctstate RELATED,ESTABLISHED --match helper --helper ftp -j CLASSIFY --set-class ". $my_id);
	       $this->addRule($ms, IPT_BIN ." -t mangle -A ms-chain-". $interface ."-". $parent ." --match conntrack --ctproto tcp --ctstate RELATED,ESTABLISHED --match helper --helper ftp -j RETURN");

	    }

	    /* filter matches on protocols */
	    if($filter->filter_protocol_id >= 0) {

	       switch($this->getProtocolNumber($filter->filter_protocol_id)) {
		  
		  /* IP */
		  case 4:
		     array_push($proto_ary, " -p 6");
		     array_push($proto_ary, " -p 17");
		     break;
		  default:
		     array_push($proto_ary, " -p ". $this->getProtocolNumber($filter->filter_protocol_id));
		     break;
	       }

               /* Select for TCP flags (only valid for TCP protocol) */
	       if($this->getProtocolNumber($filter->filter_protocol_id) == 6) {

		  $str_tcpflags = "";

		  if($filter->filter_tcpflag_syn == "Y")
		     $str_tcpflags.= "SYN,";
		  if($filter->filter_tcpflag_ack == "Y")
		     $str_tcpflags.= "ACK,";
		  if($filter->filter_tcpflag_fin == "Y")
		     $str_tcpflags.= "FIN,";
		  if($filter->filter_tcpflag_rst == "Y")
		     $str_tcpflags.= "RST,";
		  if($filter->filter_tcpflag_urg == "Y")
		     $str_tcpflags.= "URG,";
		  if($filter->filter_tcpflag_psh == "Y")
		     $str_tcpflags.= "PSH,";

		  if($str_tcpflags != "")
		     $match_str.= " --tcp-flags ". substr($str_tcpflags, 0, strlen($str_tcpflags)-1) ." ". substr($str_tcpflags, 0, strlen($str_tcpflags)-1);

	       }

               /* Get all the used ports for IP, TCP or UDP */
	       switch($this->getProtocolNumber($filter->filter_protocol_id)) {

		  /* IP */
		  case 4:
		  /* TCP */
		  case 6:
		  /* UDP */
		  case 17:
		     $all_ports = array();
		     $cnt_ports = 0;

		     /* Which ports are selected for this filter */
		     $ports = $this->getPorts($filter->filter_idx);

		     if($ports) {

			while($port = $ports->fetchRow()) {

                           /* If this port is definied as range or list get all the single ports */
			   $dst_ports = $this->extractPorts($port->port_number);

			   if($dst_ports != 0) {

			      foreach($dst_ports as $dst_port) {

				 array_push($all_ports, $dst_port);
				 $cnt_ports++;

			      }
			   }
			}
		     }

		     break;
	       }
	    }
	    else
	      array_push($proto_ary, "");

            /* Layer7 protocol matching */
	    if($l7protocols = $this->getL7Protocols($filter->filter_idx)) {
		  
	       $l7_cnt = 0;
	       $l7_protos = array();

	       while($l7proto = $l7protocols->fetchRow()) {
	       
		  array_push($l7_protos, $l7proto->l7proto_name);
		  $l7_cnt++;
	       }
	    }

            /* TOS flags matching */
	    if($filter->filter_tos >= 0)
	       $match_str.= " -m tos --tos ". $filter->filter_tos;

            /* packet length matching */
	    if($filter->filter_packet_length > 0)
	       $match_str.= " -m length --length ". $filter->filter_packet_length;

            /* time range matching */
	    if($filter->filter_time_use_range == "Y") {

	       $start = strftime("%Y:%m:%d:%H:%M:00", $filter->filter_time_start);
	       $stop = strftime("%Y:%m:%d:%H:%M:00", $filter->filter_time_stop);
	       $match_str.= " -m time --datestart ". $start ." --datestop ". $stop;

	    }
	    else {

	       $str_days = "";
	       if($filter->filter_time_day_mon == "Y")
		 $str_days.= "Mon,";
	       if($filter->filter_time_day_tue == "Y")
		 $str_days.= "Tue,";
	       if($filter->filter_time_day_wed == "Y")
		 $str_days.= "Wed,";
	       if($filter->filter_time_day_thu == "Y")
		 $str_days.= "Thu,";
	       if($filter->filter_time_day_fri == "Y")
		 $str_days.= "Fri,";
	       if($filter->filter_time_day_sat == "Y")
		 $str_days.= "Sat,";
	       if($filter->filter_time_day_sun == "Y")
		 $str_days.= "Sun,";

	       if($str_days != "")
		  $match_str.= " -m time --days ". substr($str_days, 0, strlen($str_days)-1);

	    }

            /* IPP2P matching */
	    if($filter->filter_p2p_edk == "Y")
	      $str_p2p.= "--edk ";
	    if($filter->filter_p2p_kazaa == "Y")
	      $str_p2p.= "--kazaa ";
	    if($filter->filter_p2p_dc == "Y")
	      $str_p2p.= "--dc ";
	    if($filter->filter_p2p_gnu == "Y")
	      $str_p2p.= "--gnu ";
	    if($filter->filter_p2p_bit == "Y")
	      $str_p2p.= "--bit ";
	    if($filter->filter_p2p_apple == "Y")
	      $str_p2p.= "--apple ";
	    if($filter->filter_p2p_soul == "Y")
	      $str_p2p.= "--soul ";
	    if($filter->filter_p2p_winmx == "Y")
	      $str_p2p.= "--winmx ";
	    if($filter->filter_p2p_ares == "Y")
	      $str_p2p.= "--ares ";

	    if($str_p2p != "")
	       $match_str.= " -m ipp2p ". substr($str_p2p, 0, strlen($str_p2p)-1);

	    /****************************************/
	    /* End of match string.                 */
	    /****************************************/
	 
            /* All port matches will be matched with the iptables multiport */
	    /* (advantage is that src&dst matches can be done with a simple */
	    /* --port */

	    switch($this->getProtocolNumber($filter->filter_protocol_id)) {

	       /* TCP, UDP or IP */
	       case 4:
	       case 6:
	       case 17:
		  
		  if($cnt_ports > 0) {

		     switch($direction) {

			/* 1 = incoming, 2 = outgoing, 3 = both */
			case 1:
			   $match_str.= " -m multiport --dport ";
			   break;
			case 2:
			   $match_str.= " -m multiport --sport ";
			   break;
			case 3:
			   $match_str.= " -m multiport --port ";
			   break;
			
		     }

		     $j = 0;

		     for($i = 0; $i <= $cnt_ports; $i++) {
		     
			if($j == 0)
			   $tmp_ports = "";

			if(isset($all_ports[$i]))
			      $tmp_ports.= $all_ports[$i] .",";

                        /* with one multiport match iptables can max. match 14 single ports */
			if($j == 14 || $i == $cnt_ports-1) {

			   $tmp_str = $match_str . substr($tmp_ports, 0, strlen($tmp_ports)-1); 
			   array_push($match_ary, $tmp_str);

			   $j = 0;

			}
			else 
			   $j++;
		     }

		  }

		  break;

	       default:

                  /* is there any l7 filter protocol we have to attach to the filter? */
		  if($l7_cnt > 0) {

		     foreach($l7_protos as $l7_proto) {

			array_push($match_ary, $match_str ." -m layer7 --l7proto ". $l7_proto);

		     }
		  }
		  else {

                        array_push($match_ary, $match_str); 

		  }
		  break;
	    }


            foreach($match_ary as $match_str) {

	       /* Add to the ruleset */

	       $ipt_tmpl = IPT_BIN ." -t mangle -A ms-chain-". $interface ."-". $parent;

	       if($filter->filter_src_target != 0 && $filter->filter_dst_target == 0) {

		  $src_hosts = $this->getTargetHosts($filter->filter_src_target);
		  
		  foreach($src_hosts as $src_host) {

                     foreach($proto_ary as $proto_str) {

			$this->addRule($ms, $ipt_tmpl ." -s ". $src_host ." ". $proto_str ." ". $match_str ." -j CLASSIFY --set-class ". $my_id);
			$this->addRule($ms, $ipt_tmpl ." -s ". $src_host ." ". $proto_str ." ". $match_str ." -j RETURN");

                     }
		  }
	       }
	       elseif($filter->filter_src_target == 0 && $filter->filter_dst_target != 0) {

		  $dst_hosts = $this->getTargetHosts($filter->filter_dst_target);

		  foreach($dst_hosts as $dst_host) {

                     foreach($proto_ary as $proto_str) {

			$this->addRule($ms, $ipt_tmpl ." -d ". $dst_host ." ". $proto_str ." ". $match_str ." -j CLASSIFY --set-class ". $my_id);
			$this->addRule($ms, $ipt_tmpl ." -d ". $dst_host ." ". $proto_str ." ". $match_str ." -j RETURN");

                     }
		  }
	       }
	       elseif($filter->filter_src_target != 0 && $filter->filter_dst_target != 0) {

		  $src_hosts = $this->getTargetHosts($filter->filter_src_target);
		  $dst_hosts = $this->getTargetHosts($filter->filter_dst_target);

		  foreach($src_hosts as $src_host) {

		     foreach($dst_hosts as $dst_host) {
		      
			foreach($proto_ary as $proto_str) {

			   $this->addRule($ms, $ipt_tmpl ." -s ". $src_host ." -d ". $dst_host ." ". $proto_str ." ". $match_str ." -j CLASSIFY --set-class ". $my_id);
			   $this->addRule($ms, $ipt_tmpl ." -s ". $src_host ." -d ". $dst_host ." ". $proto_str ." ". $match_str ." -j RETURN");


			}
		     }
		  }
	       }
	       elseif($filter->filter_src_target == 0 && $filter->filter_dst_target == 0) {

		  foreach($proto_ary as $proto_str) {

		     $this->addRule($ms, $ipt_tmpl ." ". $proto_str ." ". $match_str ." -j CLASSIFY --set-class ". $my_id);
		     $this->addRule($ms, $ipt_tmpl ." ". $proto_str ." ". $match_str ." -j RETURN");

		  }
	       }
	    }

	    break;

      }

   } // addPipeFilter()

   /* new incoming chain */
   function buildInChains($ms, $interface)
   {
      $currentlevel = 0;

      $this->current_in_chain = 1;
      $this->current_in_class = 1;
      $this->current_in_filter = 1;

      $chains = $this->getIncomingChains();

      while($chain = $chains->fetchRow()) {

	 $this->addRuleComment(&$ms, "chain ". $chain->chain_name ."");

	 /* chain doesn't ignore QoS? */
	 if($chain->chain_sl_idx != 0)
	    $this->addInClass(&$ms, $interface, "1:1", "1:". $this->current_in_chain . $this->current_in_class, $this->getInBandwidth($chain->chain_sl_idx));

	 /* remember the assigned chain id */
	 $this->setChainID($interface, $chain->chain_idx, "1:". $this->current_in_chain . $this->current_in_class, "dst", "src");

	 if($this->parent->getOption("filter") == "ipt") {
	    $this->addRule($ms, IPT_BIN ." -t mangle -N ms-chain-". $interface ."-1:". $this->current_in_chain . $this->current_in_filter);
	    $this->addRule($ms, IPT_BIN ." -t mangle -A ms-all-chains -m connmark --mark ". $this->getConnmarkId($interface, "1:". $this->current_in_chain . $this->current_in_filter) ." -j ms-chain-". $interface ."-1:". $this->current_in_chain . $this->current_in_filter);
	 }
		   
	 /* setup the filter definition to match traffic which should go into this chain */
	 if($chain->chain_src_target != 0 || $chain->chain_dst_target != 0) {

	    $this->addHostFilter(&$ms, $interface, "1:1", "host", $chain, "1:". $this->current_in_chain . $this->current_in_filter, "src", "dst", $this->out_interface);

	 } else {

	    $this->addMatchallFilter(&$ms, $interface, "1:1", "matchall", "1:". $this->current_in_chain . $this->current_in_filter, $this->out_interface);

	 }

         /* chain doesn't ignore QoS? */
	 if($chain->chain_sl_idx != 0) {

            /* chain uses fallback service level? */
	    if($chain->chain_fallback_idx != 0) {

	       $this->addRuleComment(&$ms, "generating pipes for ". $chain->chain_name ."");
	       $this->buildInPipes(&$ms, $interface, $chain->chain_idx, "1:". $this->current_in_chain . $this->current_in_class);

	       // Fallback
	       $this->addInClass(&$ms, $interface, "1:". $this->current_in_chain . $this->current_in_class, "1:". $this->current_in_chain ."99", $this->getServiceLevel($chain->chain_fallback_idx));
	       $this->addSubQdisc(&$ms, $interface, $this->current_in_chain ."99:", "1:". $this->current_in_chain ."99", $this->getServiceLevel($chain->chain_fallback_idx));
	       $this->addFallbackFilter(&$ms, $interface, "1:". $this->current_in_chain . $this->current_in_class, "fallback", "1:". $this->current_in_chain ."99", "2");
	       $this->setPipeID($interface, -1, $chain->chain_idx, "1:". $this->current_in_chain ."99");
	    }
	    else {

	       $this->addRuleComment(&$ms, "chain without service level");
	       $this->addSubQdisc(&$ms, $interface, $this->current_in_chain . $this->current_in_class .":", "1:". $this->current_in_chain . $this->current_in_class, $this->getServiceLevel($chain->chain_sl_idx));

	    }
	 }

	 $this->current_in_class  = 1;
	 $this->current_in_filter = 1;
	 $this->current_in_chain  = dechex(hexdec($this->current_in_chain) + 1);

      }
   } // buildInChains()

   /* new outgoing chain */ 
   function buildOutChains($ms, $interface)
   {
      $currentlevel = 0;

      $this->current_out_chain = 1;
      $this->current_out_class = 1;
      $this->current_out_filter = 1;

      $chains = $this->getOutgoingChains();

      while($chain = $chains->fetchRow()) {

	 $this->addRuleComment(&$ms,  "chain ". $chain->chain_name ."");

	 /* chain doesn't ignore QoS? */
	 if($chain->chain_sl_idx != 0)
	    $this->addOutClass(&$ms, $interface, "1:1", "1:". $this->current_out_chain . $this->current_out_class, $this->getOutBandwidth($chain->chain_sl_idx));

	 /* remember the assigned chain id */
	 $this->setChainID($interface, $chain->chain_idx, "1:". $this->current_out_chain . $this->current_out_class);

	 if($this->parent->getOption("filter") == "ipt") {
	    $this->addRule($ms, IPT_BIN ." -t mangle -N ms-chain-". $interface ."-1:". $this->current_out_chain . $this->current_out_filter);
	    $this->addRule($ms, IPT_BIN ." -t mangle -A ms-all-chains -m connmark --mark ". $this->getConnmarkId($interface, "1:". $this->current_out_chain . $this->current_out_filter) ." -j ms-chain-". $interface ."-1:". $this->current_out_chain . $this->current_out_filter);
	 }

	 /* setup the filter definition to match traffic which should go into this chain */
	 if($chain->chain_src_target != 0 || $chain->chain_dst_target != 0) {

	    $this->addHostFilter(&$ms, $interface, "1:1", "host", $chain, "1:". $this->current_out_chain . $this->current_out_filter, "src", "dst", $this->in_interface, "swap_in_out");

	 } else {
	 
	    $this->addMatchallFilter(&$ms, $interface, "1:1", "matchall", "1:". $this->current_out_chain . $this->current_out_filter, $this->in_interface);
	    
	 }

         /* chain doesn't ignore QoS? */
	 if($chain->chain_sl_idx != 0) {

            /* chain uses fallback service level? */
	    if($chain->chain_fallback_idx != 0) {

	       $this->addRuleComment(&$ms, "generating pipes for ". $chain->chain_name ."");
	       $this->buildOutPipes(&$ms, $interface, $chain->chain_idx, "1:". $this->current_out_chain . $this->current_out_class);

	       // Fallback
	       $this->addOutClass(&$ms, $interface, "1:". $this->current_out_chain . $this->current_out_class, "1:". $this->current_out_chain ."99", $this->getServiceLevel($chain->chain_fallback_idx));
	       $this->addSubQdisc(&$ms, $interface, $this->current_out_chain ."99:", "1:". $this->current_out_chain ."99", $this->getServiceLevel($chain->chain_fallback_idx));
	       $this->addFallbackFilter(&$ms, $interface, "1:". $this->current_out_chain . $this->current_out_class, "fallback", "1:". $this->current_out_chain ."99", "2");
	       $this->setPipeID($interface, -1, $chain->chain_idx, "1:". $this->current_out_chain ."99");
	    }
	    else {

	       $this->addRuleComment(&$ms, "chain without service level");
	       $this->addSubQdisc(&$ms, $interface, $this->current_out_chain . $this->current_out_class .":", "1:". $this->current_out_chain . $this->current_out_class, $this->getServiceLevel($chain->chain_sl_idx));

	    }
	 }

	 $this->current_out_class  = 1;
	 $this->current_out_filter = 1;
	 $this->current_out_chain  = dechex(hexdec($this->current_out_chain) + 1);
      }
   } // buildOutChain()

   /* get incoming parameters from service level */
   function getInBandwidth($sl_idx)
   {
      $row = $this->db->db_fetchSingleRow("SELECT sl_htb_bw_in_rate, sl_htb_bw_in_ceil, sl_htb_bw_in_burst, "
	       	                         ."sl_htb_priority, sl_hfsc_in_rate, sl_hfsc_in_ulrate, sl_cbq_in_rate, "
			                 ."sl_cbq_in_priority, sl_cbq_bounded "
					 ."FROM shaper_service_levels WHERE sl_idx='". $sl_idx ."'");
      return $row;
   } // getInBandwidth()

   /* get outgoing parameters from service level */
   function getOutBandwidth($sl_idx)
   {
      $row = $this->db->db_fetchSingleRow("SELECT sl_htb_bw_out_rate, sl_htb_bw_out_ceil, sl_htb_bw_out_burst, "
			                 ."sl_htb_priority, sl_hfsc_out_rate, sl_hfsc_out_ulrate, sl_cbq_out_rate, "
			                 ."sl_cbq_out_priority, sl_cbq_bounded "
					 ."FROM shaper_service_levels WHERE sl_idx='". $sl_idx ."'");
      return $row;
   } // getOutBandwidth()

   /* get list of incoming chains */
   function getIncomingChains()
   {
      $chains = $this->db->db_query("SELECT chain_idx, chain_name, chain_sl_idx, chain_src_target, "
			           ."chain_dst_target, chain_direction, chain_fallback_idx FROM "
			           ."shaper_chains WHERE chain_active='Y' "
			           ."ORDER BY chain_position ASC");
      return $chains;
   } // getIncomingChains()

   /* get list of outgoing chains */
   function getOutgoingChains()
   {
      $chains = $this->db->db_query("SELECT chain_idx, chain_name, chain_sl_idx, chain_src_target, "
			           ."chain_dst_target, chain_direction, chain_fallback_idx FROM "
			           ."shaper_chains WHERE chain_direction='". BIDIRECTIONAL ."' AND chain_active='Y' "
			           ."ORDER BY chain_position ASC");
      return $chains;
   } // getOutgoingChains()

   /* build ruleset for incoming pipes */
   function buildInPipes($ms, $interface, $chain_idx, $my_parent)
   {
      /* get all active pipes for this chain */
      $pipes = $this->db->db_query("SELECT pipe_idx, pipe_name, pipe_sl_idx, pipe_direction FROM "
			          ."shaper_pipes WHERE pipe_active='Y' AND pipe_chain_idx='". $chain_idx ."' "
			          ."ORDER BY pipe_position ASC");

      $this->current_in_pipe = 1;

      while($pipe = $pipes->fetchRow()) {

	 $this->current_in_pipe+=1;
	 $my_id = "1:". $this->current_in_chain . $this->current_in_pipe;
	 $this->addRuleComment(&$ms, "pipe ". $pipe->pipe_name ."");

	 $sl      = $this->getServiceLevel($pipe->pipe_sl_idx);

	 /* add a new class for this pipe */
	 $this->addInClass(&$ms, $interface, $my_parent, $my_id, $sl);
	 $this->addSubQdisc(&$ms, $interface, $this->current_in_chain . $this->current_in_pipe .":", $my_id, $sl);
	 $this->setPipeID($interface, $pipe->pipe_idx, $chain_idx, "1:". $this->current_in_chain . $this->current_in_pipe); 

	 /* get the nescassary parameters */
	 $filters = $this->getFilters($pipe->pipe_idx);

	 while($filter = $filters->fetchRow()) {

	    $detail = $this->getFilterDetails($filter->apf_filter_idx);
	    $this->addPipeFilter(&$ms, $interface, $my_parent, "pipe_filter", $detail, $my_id, $pipe->pipe_direction, $pipe->pipe_idx);

	 }
      }
   } // buildInPipes()

   /* build ruleset for outgoing pipes */
   function buildOutPipes($ms, $interface, $chain_idx, $my_parent)
   {
      /* get all active pipes for this Chain */
      $pipes = $this->db->db_query("SELECT pipe_idx, pipe_name, pipe_sl_idx, pipe_direction FROM "
                                  ."shaper_pipes WHERE pipe_active='Y' and pipe_chain_idx='". $chain_idx ."' "
			          ."ORDER BY pipe_position ASC");

      $this->current_out_pipe = 1;

      while($pipe = $pipes->fetchRow()) {

	 $this->current_out_pipe+=1;
	 $my_id = "1:". $this->current_out_chain . $this->current_out_pipe;
	 $this->addRuleComment(&$ms, "pipe ". $pipe->pipe_name ."");

	 $sl      = $this->getServiceLevel($pipe->pipe_sl_idx);

	 /* add a new class for this pipe */
	 $this->addOutClass(&$ms, $interface, $my_parent, $my_id, $sl);
	 $this->addSubQdisc(&$ms, $interface, $this->current_out_chain . $this->current_out_pipe .":", $my_id, $sl);
	 $this->setPipeID($interface, $pipe->pipe_idx, $chain_idx, "1:". $this->current_out_chain . $this->current_out_pipe); 

	 /* get the nescassary parameters */
	 $filters = $this->getFilters($pipe->pipe_idx);

	 while($filter = $filters->fetchRow()) {

	    $detail = $this->getFilterDetails($filter->apf_filter_idx);

            /* If this filter matches bidirectional, we src & dst target has to be swapped */
            if($detail->filter_direction == BIDIRECTIONAL) {

	       $tmp = $detail->filter_src_target;
	       $detail->filter_src_target = $detail->filter_dst_target;
	       $detail->filter_dst_target = $tmp;
	     
	    }
	    
	    $this->addPipeFilter(&$ms, $interface, $my_parent, "pipe_filter", $detail, $my_id, $pipe->pipe_direction, $pipe->pipe_idx);

	 }
      }
   } // buildOutPipes()

   /* get IANA protocol number from table id */
   function getProtocolNumber($id)
   {
      $row = $this->db->db_fetchSingleRow("SELECT proto_number FROM shaper_protocols WHERE proto_idx='". $id ."'");
      return $row->proto_number;
   } // getProtocolNumber()

   /* get IANA protocol name from table id */
   function getProtocolName($id)
   {
      $row = $this->db->db_fetchSingleRow("SELECT proto_name FROM shaper_protocols WHERE proto_idx='". $id ."'");
      return $row->proto_name;
   } // getProtocolName()

   /* return a list for all ports in a filter definition */
   function getPorts($filter_idx)
   {
      $list = NULL;
      $numbers = "";

      $ports = $this->db->db_query("SELECT afp_port_idx FROM shaper_assign_ports WHERE afp_filter_idx='". $filter_idx ."'");
      while($port = $ports->fetchRow()) {
	 $numbers.= $port->afp_port_idx .",";
      }
      if($numbers != "") {
	 $numbers = substr($numbers, 0, strlen($numbers)-1);
	 $list = $this->db->db_query("SELECT port_name, port_number FROM shaper_ports WHERE port_idx IN (". $numbers .")");
      }
      
      return $list;

   } // getPorts()

   /* return a list for all layer7 protocols in a filter definition */
   function getL7Protocols($filter_idx)
   {
      $list = NULL;
      $numbers = "";

      $protocols = $this->db->db_query("SELECT afl7_l7proto_idx FROM shaper_assign_l7_protocols WHERE afl7_filter_idx='". $filter_idx ."'");
      while($protocol = $protocols->fetchRow()) {
         $numbers.= $protocol->afl7_l7proto_idx .",";
      }
      if($numbers != "") {
         $numbers = substr($numbers, 0, strlen($numbers)-1);
	 $list = $this->db->db_query("SELECT l7proto_name FROM shaper_l7_protocols WHERE l7proto_idx IN (". $numbers .")");
      }

      return $list;

   } // getL7Protocols

   /* set the actually tc handle ID for a pipe */ 
   function setPipeID($interface, $pipe_idx, $chain_tc_id, $pipe_tc_id)
   {
      $this->db->db_query("INSERT INTO shaper_tc_ids (id_pipe_idx, id_chain_idx, id_if, id_tc_id) "
			 ."VALUES ('". $pipe_idx ."', '". $chain_tc_id ."', '". $interface ."', '". $pipe_tc_id ."')");
   } // setPipeID()

   /* set the actually tc handle ID for a chain */
   function setChainID($interface, $chain_idx, $chain_tc_id)
   {
      $this->db->db_query("INSERT INTO shaper_tc_ids (id_pipe_idx, id_chain_idx, id_if, id_tc_id) "
			 ."VALUES ('0', '". $chain_idx ."', '". $interface ."', '". $chain_tc_id ."')");
   } // setChainID()

   /* get all filters which are assigned to a pipe */
   function getFilters($pipe_idx)
   {
      return $this->db->db_query("SELECT a.apf_filter_idx as apf_filter_idx FROM shaper_assign_filters a, shaper_filters b WHERE "
                                ."a.apf_pipe_idx='". $pipe_idx ."' AND a.apf_filter_idx=b.filter_idx AND b.filter_active='Y'");
   } // getFilters()

   /* returns filter details */
   function getFilterDetails($filter_idx)
   {
      return $this->db->db_fetchSingleRow("SELECT * FROM shaper_filters WHERE filter_idx='". $filter_idx ."'");

   } // getFilterDetails()	

   /* returns all service level parameters for specific service level */
   function getServiceLevel($sl_idx)
   {
      return $this->db->db_fetchSingleRow("SELECT * FROM shaper_service_levels WHERE sl_idx='". $sl_idx ."'");
   } // getServiceLevel()

   /* extracts all host informations from a string */
   function extractHosts($string)
   {
      $hosts = Array();

      $string = str_replace(" ", "", $string);
      if(preg_match("/,/", $string))
	 $hosts = split(",", $string);
      else
	 array_push($hosts, $string);

      $targets = Array();

      if($hosts) {
	 foreach($hosts as $host) {
	    if(preg_match("/\//", $host)) {
	       if($this->ipv4->parseAddress($host))
		  array_push($targets, $host);
	    } 
	    elseif($this->ipv4->validateIP($host)) {
	       array_push($targets, $host);
	    }
	 }
	 return $targets;
      }

      return 0;
      
   } // extractHosts()

   /* returns a array of host addresses for a target definition */
   function getTargetHosts($target_idx)
   {
      $targets = array();

      $row = $this->db->db_fetchSingleRow("SELECT target_match, target_ip, target_mac FROM "
	        ."shaper_targets WHERE target_idx='". $target_idx ."'");

      switch($row->target_match) {

	 case 'IP':

	    /* Is target_ip a ip range seperated by "-" */
	    if(strstr($row->target_ip, "-") !== false) {

	       list($host1, $host2) = split("-", $row->target_ip);

	       $host1 = ip2long($host1);
	       $host2 = ip2long($host2);

	       for($i = $host1; $i <= $host2; $i++) {

		  array_push($targets, long2ip($i));

	       }
	    }
	    else {
	       array_push($targets, $row->target_ip);
	    }
	    break;

	 case 'MAC':

	    $row->target_mac = str_replace("-", ":", $row->target_mac);
	    list($one, $two, $three, $four, $five, $six) = split(":", $row->target_mac);
	    $row->target_mac = sprintf("%02s:%02s:%02s:%02s:%02s:%02s", $one, $two, $three, $four, $five, $six);
	    array_push($targets, $row->target_mac);
	    break;

	 case 'GROUP':

	    $result = $this->db->db_query("SELECT atg_target_idx FROM shaper_assign_target_groups "
	                 ."WHERE atg_group_idx='". $target_idx ."'");

	    while($target = $result->fetchRow()) {

	       $members = $this->getTargetHosts($target->atg_target_idx);

	       $i = count($targets);
	       foreach($members as $member) {

		  $targets[$i] = $member;
		  $i++;

	       }
	    }
	    break;

      }

      return $targets;

   } // getTargetHosts()

   /* extract all ports from a string */
   function extractPorts($string)
   {
      if($string != "" && !preg_match("/any/", $string)) {

	 $string = str_replace(" ", "", $string);
	 $ports = split(",", $string);

	 $targets = Array();

	 foreach($ports as $port) {

	    if(preg_match("/.*-.*/", $port)) {

	       list($start, $end) = split("-", $port);

	       for($i = $start*1; $i <= $end*1; $i++) 
		  array_push($targets, $i);
	    }
	    else 
	      array_push($targets, $port);
	 }			
	 return $targets;
      }
      else {

	 return NULL;

      }

   } // extractPorts()

   function makeId($interface, $id)
   {
      if($interface == $this->in_interface)
	 $if_id = 1;

      if($interface == $this->out_interface)
	 $if_id = 2;

      $id = str_replace(":", "", $id);

      return dechex($id*$if_id);

   } // makeId()

   function doIt()
   {

      $error = Array();
      $found_error = 0;

      /* Delete current root qdiscs */
      if($this->in_interface != "")
	 $this->delQdisc($this->in_interface);
      if($this->out_interface != "")
	 $this->delQdisc($this->out_interface);

      $this->delIptablesRules();

      /* Prepare the tc batch file */
      $temp_tc  = tempnam (TEMP_PATH, "FOOTC");
      $output_tc  = fopen($temp_tc, "w");

      /* If necessary prepare iptables batch files */
      if($this->parent->getOption("filter") == "ipt") {

	 $temp_ipt = tempnam (TEMP_PATH, "FOOIPT");
	 $output_ipt = fopen($temp_ipt, "w");
	 
      }

      /* pump inbound tasks into batch files */
      foreach($this->getRules(MS_IN) as $line) {

	 $line = trim($line);

	 if(!preg_match("/^#/", $line)) {

	    /* tc filter task */
	    if(strstr($line, TC_BIN) !== false && $line != "") {
	       $line = str_replace(TC_BIN ." ", "", $line);
	       fputs($output_tc, $line ."\n");
	    }

	    /* iptables task */
	    if(strstr($line, IPT_BIN) !== false && $this->parent->getOption("filter") == "ipt")
	       fputs($output_ipt, $line ."\n");
	 }
      }

      /* pump outbound tasks into batch files */
      foreach($this->getRules(MS_OUT) as $line) {

	 $line = trim($line);

	 if(!preg_match("/^#/", $line)) {

	    /* tc filter task */
	    if(strstr($line, TC_BIN) !== false && $line != "") {
	       $line = str_replace(TC_BIN ." ", "", $line);
	       fputs($output_tc, $line ."\n");
	    }

	    /* iptables task */
	    if(strstr($line, IPT_BIN) !== false && $this->parent->getOption("filter") == "ipt")
	       fputs($output_ipt, $line ."\n");
	 }
      }

      /* flush batch files */
      fclose($output_tc);

      if($this->parent->getOption("filter") == "ipt")
	 fclose($output_ipt);

      if(!$this->parent->fromcmd) {

	 $this->parent->startTable("<img src=\"". ICON_OPTIONS ."\">&nbsp;Loading MasterShaper Ruleset");
?>
    <table style="width: 100%; text-align: center;" class="withborder2">
<?
      }

      /* load tc filter rules */
      if(($error = $this->runProc("tc", TC_BIN . " -b ". $temp_tc)) != TRUE) {
?>
     <tr><td style="text-align: center;"><img src="<? print ICON_INACTIVE; ?>" align="middle">&nbsp;MasterShaper is not active!</td></tr>
     <tr><td style="text-align: center;">Error on mass loading tc rules. Try load ruleset in debug mode to figure incorrect or not supported rule.</td></tr>
     <tr><td style="text-align: center;"><? print $error; ?></td></tr>
<?
	 $found_error = 1;

      }

      /* load iptables rules */
      if($this->parent->getOption("filter") == "ipt" && !$found_error) {

	 if(($error = $this->runProc("iptables", $temp_ipt)) != TRUE) {
?>
     <tr><td style="text-align: center;"><img src="<? print ICON_INACTIVE ?>" align="middle">&nbsp;MasterShaper is not active!</td></tr>
     <tr><td style="text-align: center;">Error on mass loading iptables rule. Try load ruleset in debug mode to figure incorrect or not supported rule.</td></tr>
     <tr><td style="text-align: center;"><? print $error; ?></td></tr>
<?

	    $found_error = 1;

	 }
      }

      if(!$this->parent->fromcmd && !$found_error) {
?>
     <tr><td style="text-align: center;"><img src="<? print ICON_ACTIVE ?>" align="middle">&nbsp;Rules are enabled. No error found.</td></tr>
<?
      }

      if(!$this->parent->fromcmd) {
?>
    </table>
<?

	 $this->parent->closeTable();

      }

      unlink($temp_tc);
      if($this->parent->getOption("filter") == "ipt")
	 unlink($temp_ipt);


      if(!$found_error)
         $this->parent->setShaperStatus(true);
      else
         $this->parent->setShaperStatus(false);

      return $found_error;

   } // doIt()

   function doItLineByLine()
   {
      $this->parent->startTable("<img src=\"". ICON_OPTIONS ."\">&nbsp;Loading MasterShaper Ruleset (debug)");

      /* Delete current root qdiscs */
      $this->delQdisc($this->in_interface);
      $this->delQdisc($this->out_interface);
      $this->delIptablesRules();

      $ipt_lines = array();

      foreach($this->getRules(MS_IN) as $line) {

	 if(!preg_match("/^#/", $line)) {

	    if(strstr($line, TC_BIN) !== false) {

	       print $line."<br />\n";
	       if(($tc = $this->runProc("tc", $line)) != TRUE)
		  print $tc."<br />\n";
	    }
	    if(strstr($line, IPT_BIN) !== false) 
	       array_push($ipt_lines, $line);
	 }
	 else
	    print $line."<br />\n";
      }

      print "<br /><br />";

      foreach($this->getRules(MS_OUT) as $line) {

	 if(!preg_match("/^#/", $line)) {

	    if(strstr($line, TC_BIN) !== false) {

	       print $line."<br />\n";
	       if(($tc = $this->runProc("tc", $line)) != TRUE)
		  print $tc."<br />\n";
	    }
	    if(strstr($line, IPT_BIN) !== false) 
	       array_push($ipt_lines, $line);
	 }
	 else
	    print $line."<br />\n";
      }

      foreach($ipt_lines as $line) {

	 print $line."<br />\n";
	 if(($tc = $this->runProc("tc", $line)) != TRUE)
	    print $tc."<br />\n";
      }

      $this->parent->closeTable();

   } // doItLineByLine()

   function output($text)
   {
      if($_GET['output'] == "noisy")
	 print $text ."\n";

   } // output()

   function showIt()
   {

      $this->parent->startTable("<img src=\"". ICON_HOME ."\" alt=\"home icon\" />&nbsp;MasterShaper Ruleset - Show rules");

      foreach($this->getRules(MS_IN) as $tmp) {

	 foreach(split("\n", $tmp) as $line) {

	    $line = trim($line);

	    if($line != "")
	       print "<font style='color: ". $this->getColor($line) .";'>". $line ."</font><br />\n";
	 }
      }

      print "<br /><br /><br />\n";

      foreach($this->getRules(MS_OUT) as $tmp) {

	 foreach(split("\n", $tmp) as $line) {

	    $line = trim($line);

	    if($line != "")
	       print "<font style='color: ". $this->getColor($line) .";'>". $line ."</font><br />\n";
	 }
      }

      $this->parent->closeTable();

   } // showIt()

   function getColor($text)
   {
      if(strstr($text, "########"))
	 return "#666666";
      if(strstr($text, TC_BIN))
	 return "#AF0000";
      if(strstr($text, IPT_BIN))
	 return "#0000AF";

      return "#000000";

   } // getColor()

   function runProc($option, $cmd = "", $ignore_err = FALSE)
   {
      $desc = array(
	 0 => array('pipe','r'), /* STDIN */
	 1 => array('pipe','w'), /* STDOUT */
	 2 => array('pipe','a'), /* STDERR */ 
      );

      $process = proc_open(SUDO_BIN ." ". SHAPER_PATH ."/shaper_loader.sh ". $option ." \"". $cmd ."\"", $desc, $pipes);

      if(is_resource($process)) {

	 $string = fgets($pipes[1], 255);
	 $string = trim($string);

	 fclose($pipes[1]);
	 proc_close($process);

	 if($string != "" && $string != "OK" && !$ignore_err)
	    return $string;
	 
	 return TRUE;
      }

      return "Error on executing command: ". $cmd;

   } // runProc()

   function getConnmarkId($string1, $string2)
   {

      return "0x". dechex(crc32($string1 . str_replace(":", "", $string2))* -1);

   } // getConnmarkId()

   function getNETEMParams($sl)
   {
      if($sl->sl_netem_delay != "" && is_numeric($sl->sl_netem_delay)) {

	 $params.= "delay ". $sl->sl_netem_delay ."ms ";

	 if($sl->sl_netem_jitter != "" && is_numeric($sl->sl_netem_jitter)) {

	    $params.= $sl->sl_netem_jitter ."ms ";

	    if($sl->sl_netem_random != "" && is_numeric($sl->sl_netem_random)) {

	       $params.= $sl->sl_netem_random ."% ";
	         
	    }

	 }

	 if($sl->sl_netem_distribution != "ignore") {

	    $params.= "distribution ". $sl->sl_netem_distribution ." ";

	 }

      }

      if($sl->sl_netem_loss != "" && is_numeric($sl->sl_netem_loss)) {

	 $params.= "loss ". $sl->sl_netem_loss ."% ";

      }

      if($sl->sl_netem_duplication != "" && is_numeric($sl->sl_netem_duplication)) {

	 $params.= "duplicate ". $sl->sl_netem_duplication ."% ";
         
      }

      if($sl->sl_netem_gap != "" && is_numeric($sl->sl_netem_gap)) {

	 $params.= "gap ". $sl->sl_netem_gap ." ";

      }

      if($sl->sl_netem_reorder_percentage != "" && is_numeric($sl->sl_netem_reorder_percentage)) {

	 $params.= "reorder ". $sl->sl_netem_reorder_percentage ."% ";

	 if($sl->sl_netem_reorder_correlation  != "" && is_numeric($sl->sl_netem_reorder_correlation )) {

	    $params.= $sl->sl_netem_reorder_correlation ."% ";

	 }
      }

      return $params;

   } // getNETEMParams()

   function getESFQParams($sl)
   {

      $params = "";

      if($sl->sl_esfq_perturb != "" && is_numeric($sl->sl_esfq_perturb))
         $params.= "perturb ". $sl->sl_esfq_perturb ." ";

      if($sl->sl_esfq_limit != "" && is_numeric($sl->sl_esfq_limit))
         $params.= "limit ". $sl->sl_esfq_limit ." ";

      if($sl->sl_esfq_depth != "" && is_numeric($sl->sl_esfq_depth))
         $params.= "depth ". $sl->sl_esfq_depth ." ";

      if($sl->sl_esfq_divisor != "" && is_numeric($sl->sl_esfq_divisor))
         $params.= "divisor ". $sl->sl_esfq_divisor ." ";
	 
      if($sl->sl_esfq_hash != "")
         $params.= "hash ". $sl->sl_esfq_hash;

      return $params;

   } // getESFQParams()

}

?>
