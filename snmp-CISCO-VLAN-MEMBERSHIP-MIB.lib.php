<?php /* -*- c -*- */


  /*
   * Copyright (c) 2014 Robin Garner (robin@nukefest.org)
   * All rights reserved.
   *
   * License: GPL v.3
   */

  /* v.1.0  2007  Implements CISCO-VLAN-MEMBERSHIP-MIB
   */

  /* This MIB manages VLAN membership assignments of non-trunk
   * bridge ports and VQP/VMPS operations.  some objects are
   * voice-specific and a lot of the mib is devoted to conformance.
   * it provides a fairly simple and straight-forward way to get and
   * set information about vlan/port relationships on a device
   * PROVIDED that the port is NOT a "trunking port" (trunking ports
   * are ones that run 802.1q/isl/etc encapsulation and cisco
   * assumes those are being used to interconnect "network devices"
   * like switches and routers; cisco-vtp-mib is more useful in that
   * context).  note that ports can be configured as "multiVlan"
   * which is membership in multiple vlans without any special
   * encapsulation.  multi-vlan port config is recommended for use
   * only on ports that uplink to routers or servers, allowing them
   * to be present in multiple broadcast domains.
   * 
   * http://www.cisco.com/univercd/cc/td/doc/product/lan/c2900xl/29_35sa6/olhelp/vlanhelp.htm
   *
   * The "summary" table in this mib returns vlan numbers and the
   * bit-field list of ports that are members of the vlan, sort of
   * the inverse of the vtp-mib's functions which return ports and
   * the list of vlans the ports are members of.  the non-summary
   * "membership" table are used for configuring settings.
   **/

  /* .1.3.6.1.4.1.9.9 ciscoMgmt
   *   .68 ciscoVlanMembershipMIB
   *     .1 ciscoVlanMembershipMIBObjects
   *       .1 vmVmps
   *       .2 vmMembership
   *       .3 vmStatistics
   *       .4 vmStatus
   *       .5 vmVoiceVlan : n/i
   *     .2 vmNotifications : n/i
   *     .3 vmMIBConformance : n/i
   **/


  /* .1.3.6.1.4.1.9.9.68.1.1 vmVmps
   *   .1 vmVmpsVQPVersion
   *   .2 vmVmpsRetries
   *   .3 vmVmpsReconfirmInterval
   *   .4 vmVmpsReconfirm
   *   .5 vmVmpsReconfirmResult
   *   .6 vmVmpsCurrent
   *   .7 vmVmpsTable   : n/i
   *     .1 vmVmpsEntry  
   *       .1 vmVmpsIpAddress
   *       .2 vmVmpsPrimary
   *       .3 vmVmpsRowStatus
   *
   * objects related to vlan membership policy server (VMPS)
   * operation and the vlan query protocol (VQP).
   *
   * FUNCTION
   * get_vmVmps ($device_name, $community, &$device)
   *
   * calls get_vmVmpsVQPVersion(), get_vmVmpsRetries(),
   * get_vmVmpsReconfirmInterval(), get_vmVmpsReconfirm(),
   * get_vmVmpsReconfirmResult(), get_vmVmpsCurrent()
   **/

function get_vmVmps ($device_name, $community, &$device)
{
    get_vmVmpsVQPVersion ($device_name, $community, $device);
    get_vmVmpsRetries ($device_name, $community, $device);
    get_vmVmpsReconfirmInterval ($device_name, $community, $device);
    get_vmVmpsReconfirm ($device_name, $community, $device);
    get_vmVmpsReconfirmResult ($device_name, $community, $device);
    get_vmVmpsCurrent ($device_name, $community, $device);
}


    /* .1.3.6.1.4.1.9.9.68.1.1 vmVmps
     *   .1 vmVmpsVQPVersion
     *
     * FUNCTION
     * get_vmVmpsVQPVersion ($device_name, $community, &$device)
     *
     * sets $device["VQP"]["vmVmpsVQPVersion"]
     */

function get_vmVmpsVQPVersion ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.68.1.1.1.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }
        
        /* CISCO-VLAN-MEMBERSHIP-MIB::vmVmpsVQPVersion.0 = INTEGER: 1
         **/

    $device["VQP"]["vmVmpsVQPVersion"] = $data;
}


    /* .1.3.6.1.4.1.9.9.68.1.1 vmVmps
     *   .2 vmVmpsRetries
     *
     * FUNCTION
     * get_vmVmpsRetries ($device_name, $community, &$device)
     *
     * sets $device["VQP"]["vmVmpsRetries"]
     */

function get_vmVmpsRetries ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.68.1.1.2.0";

    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }
        
        /* CISCO-VLAN-MEMBERSHIP-MIB::vmVmpsRetries.0 = INTEGER: 3
         **/

    $device["VQP"]["vmVmpsRetries"] = $data;
}


    /* .1.3.6.1.4.1.9.9.68.1.1 vmVmps
     *   .3 vmVmpsReconfirmInterval
     *
     * FUNCTION
     * get_vmVmpsReconfirmInterval ($device_name, $community, &$device)
     *
     * sets $device["VQP"]["vmVmpsReconfirmInterval"]
     */

function get_vmVmpsReconfirmInterval ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.68.1.1.3.0";

    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }
        
        /* CISCO-VLAN-MEMBERSHIP-MIB::vmVmpsReconfirmInterval.0 = INTEGER: 60
         **/

    $device["VQP"]["vmVmpsReconfirmInterval"] = $data;
}


    /* .1.3.6.1.4.1.9.9.68.1.1 vmVmps
     *   .4 vmVmpsReconfirm
     *
     * FUNCTION
     * get_vmVmpsReconfirm ($device_name, $community, &$device)
     *
     * sets $device["VQP"]["vmVmpsReconfirm"]
     */

function get_vmVmpsReconfirm ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.68.1.1.4.0";

    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }
        
        /* CISCO-VLAN-MEMBERSHIP-MIB::vmVmpsReconfirm.0 = INTEGER: ready(1)
        **/

    $device["VQP"]["vmVmpsReconfirm"] = $data;
}


    /* .1.3.6.1.4.1.9.9.68.1.1 vmVmps
     *   .5 vmVmpsReconfirmResult
     *
     * FUNCTION
     * get_vmVmpsReconfirmResult ($device_name, $community, &$device)
     *
     * sets $device["VQP"]["vmVmpsReconfirmResult"]
     */

function get_vmVmpsReconfirmResult ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.68.1.1.5.0";

    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }
        
        /* CISCO-VLAN-MEMBERSHIP-MIB::vmVmpsReconfirmResult.0 = 
         *   INTEGER: noDynamicPort(6)
         **/

    $device["VQP"]["vmVmpsReconfirmResult"] = $data;
}


    /* .1.3.6.1.4.1.9.9.68.1.1 vmVmps
     *   .6 vmVmpsCurrent
     *
     * FUNCTION
     * get_vmVmpsCurrent ($device_name, $community, &$device)
     *
     * sets $device["VQP"]["vmVmpsCurrent"]
     */

function get_vmVmpsCurrent ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.68.1.1.6.0";

    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }
        
        /* CISCO-VLAN-MEMBERSHIP-MIB::vmVmpsCurrent.0 = IpAddress: 0.0.0.0
        **/

    $device["VQP"]["vmVmpsCurrent"] = $data;
}



    /* .1.3.6.1.4.1.9.9.68.1 ciscoVlanMembershipMIBObjects
     *   .2 vmMembership
     *     .1 vmMembershipSummaryTable
     *       .1 vmMembershipSummaryEntry
     *         .1 vmMembershipSummaryVlanIndex : n/a
     *         .2 vmMembershipSummaryMemberPorts : deprecated
     *         .3 vmMembershipSummaryMember2kPorts
     *
     * to quote the mib: "A summary of VLAN membership of non-trunk
     * bridge ports. This is a convenience table for retrieving VLAN
     * membership information."  this table returns each vlan and its
     * corresponding list of ports that are members (as a bit-field,
     * one entry per vlan)
     * 
     * FUNCTION
     * get_vmMembershipSummaryTable ($device_name, 
     *                               $community, 
     *                               &$device, 
     *                               $if="")
     *
     * calls get_vmMembershipSummaryMember2kPorts()
     **/

function get_vmMembershipSummaryTable ($device_name, $community, &$device, 
                                          $if="")
{
    get_vmMembershipSummaryMember2kPorts ($device_name, $community, $device, 
                                          $if="");
}


    /* .1.3.6.1.4.1.9.9.68.1.2.1 vmMembershipSummaryTable
     *   .1 vmMembershipSummaryEntry
     *     .3 vmMembershipSummaryMember2kPorts
     *
     * INDEX: vlan#
     *
     * a list of the port#'s that are members of the vlan refered to
     * by the index.  port number is the value of dot1dBasePort for
     * the port, use get_dot1dBasePortIfIndex() to map port to ifIndex.
     *
     * FUNCTION
     * get_vmMembershipSummaryMember2kPorts ($device_name, 
     *                                       $community, 
     *                                       &$device, 
     *                                       $if="")
     *
     * sets $device[$ifIndex]["vmMembership"][] and
     * device["vmMembership"][$vlan]["ports"][]
     **/   

function get_vmMembershipSummaryMember2kPorts ($device_name, 
                                               $community, 
                                               &$device, 
                                               $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.68.1.2.1.1.3";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* if the dot1dBasePort table has been populated, it will be
         * used to corelate port-based data with ifIndices.  create a
         * pointer to make it easier to read later on.
         **/

    if (isset($device["dot1dBridge"]["portTable"]))
    {
        $portTable = &$device["dot1dBridge"]["portTable"];
    }
    

    foreach ($data as $key=>$val)
    {
        if(empty($val))   {  continue;  }

            /* CISCO-VLAN-MEMBERSHIP-MIB::vmMembershipSummaryMember2kPorts.133
             *  = Hex-STRING: FF FF FF FF FF FF 00 00 00 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 00 
             *
             * the value returned is type "octet string", a hex string.
             * it can be broken into bytes by unpacking the string as
             * unsigned chars, resulting in an array of 128 1-byte values
             * with the initial index being 1 rather than the usual 0.
             * 
             * subtract 1 from the array index and multiply the result by 8
             * to get the ifIndex represented by the left-most bit in the byte.
             * then mask
             *
             * ( (array index - 1) * 8 ) + the position of each 1 bit
             * in the byte gives the value of an ifIndex.
             *
             * $vlan = $matches[0]
             **/

        preg_match('/[0-9]+$/i', $key, $matches);

        $vlan = $matches[0];
        
        $bytes = unpack("C*", $val);
        
        foreach ($bytes as $i=>$byte)
        {
            for ($a = 7; $a >= 0; $a--)
            {
                $mask = pow(2, $a);  
                
                if (($byte & $mask) === $mask)
                {
                    $port = ((($i-1) * 8) + (7 - $a) + 1);

                    $device["vmMembership"][$vlan]["ports"][] = $port;
            
                        /* add the vlan to the ifIndex, add the ifIndex
                         * to the vlan
                         **/
                        
                    if (isset($portTable[$port]["dot1dBasePortIfIndex"]))
                    {
                        $if = &$portTable[$port]["dot1dBasePortIfIndex"];

                        $device["vmMembership"][$vlan]["ifIndices"][] = $if;

                        $device["interfaces"][$if]["vmMembership"][] = 
                            $vlan;
                    }
                    
                }
            }
        }
    }
}


    /* .1.3.6.1.4.1.9.9.68.1 ciscoVlanMembershipMIBObjects
     *   .2 vmMembership
     *     .2 vmMembershipTable
     *       .1 vmMembershipEntry
     *         .1 vmVlanType
     *         .2 vmVlan
     *         .3 vmPortStatus
     *         .4 vmVlans   : n/i - no test avail
     *         .5 vmVlans2k : n/i - no test avail
     *         .6 vmVlans3k : n/i - no test avail
     *         .7 vmVlans4k : n/i - no test avail
     *
     * INDEX { ifIndex }
     *
     * this table is used for CONFIGURING vlan membership of non-trunk
     * ports.
     *
     * FUNCTION
     * get_vmMembershipTable ($device_name, $community, &$device)
     *
     * calls get_vmVlanType(), get_vmVlan(), get_vmPortStatus()
     *
     *
     *
     * An example interface config and its corresponding output.
     *
     * interface GigabitEthernet1/2
     *  description asbestos (0003.ba27.5ffa)
     *  switchport
     *  switchport access vlan 2
     *  switchport trunk native vlan 900
     *  switchport mode access
     *  no ip address
     * 
     * [device]
     *   [interfaces]
     *     [2]
     *       [vmVlanType] => static
     *       [vmMembership]
     *         [0] => 2
     *       [vmPortStatus] => active
     * 
     **/

function get_vmMembershipTable ($device_name, $community, &$device)
{
    get_vmVlanType ($device_name, $community, $device);
    get_vmVlan ($device_name, $community, $device);
    get_vmPortStatus ($device_name, $community, $device);
}


    /* .1.3.6.1.4.1.9.9.68.1.2.2 vmMembershipTable
     *   .1 vmMembershipEntry
     *     .1 vmVlanType
     *
     * 1 == "static"; 
     * 2 == "dynamic";
     * 3 == "multiVlan";
     *
     * INDEX { ifIndex }
     *
     * FUNCTION
     * get_vmVlanType ($device_name, $community, &$device, $if="")
     *
     */

function get_vmVlanType ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.68.1.2.2.1.1";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /* CISCO-VLAN-MEMBERSHIP-MIB::vmVlanType.38 = INTEGER: static(1)
             *
             * ifIndex = $matches[0];
             */

        $device["interfaces"][$matches[0]]["vmVlanType"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.68.1.2.2 vmMembershipTable
     *   .1 vmMembershipEntry
     *     .2 vmVlan
     *
     * indexed by ifIndex
     *
     * FUNCTION
     * get_vmVlan ($device_name, $community, &$device, $if="")
     *
     */

function get_vmVlan ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.68.1.2.2.1.2";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /* CISCO-VLAN-MEMBERSHIP-MIB::vmVlan.38 = INTEGER: 133
             *
             * $matches[0] is ifIndex
             **/

        $device["interfaces"][$matches[0]]["vmVlan"][] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.68.1.2.2 vmMembershipTable
     *   .1 vmMembershipEntry
     *     .3 vmPortStatus
     *
     * "1" = "inactive"
     * "2" = "active"
     * "3" = "shutdown"
     *
     * indexed by ifIndex
     *
     * FUNCTION
     * get_vmPortStatus ($device_name, $community, &$device, $if="")
     *
     */

function get_vmPortStatus ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.68.1.2.2.1.3";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /* CISCO-VLAN-MEMBERSHIP-MIB::vmPortStatus.38 = INTEGER: active(2)
             *
             * ifIndex = $matches[0];
             */

        $device["interfaces"][$matches[0]]["vmPortStatus"] = $val;
    }
}

?>
