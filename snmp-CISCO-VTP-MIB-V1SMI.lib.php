<?php /* -*- c -*- */

  /*
   * Copyright (c) 2014 Robin Garner (robin@nukefest.org)
   * All rights reserved.
   *
   * License: GPL v.3
   */

  /* v.1.0  2007  Implements CISCO-VTP-MIB-V1SMI
   */

  /* Objects in this MIB apply only to "trunking" ports.  Cisco
   * describes "trunk" ports as those used to connect networking
   * devices (switch<->switch, switch<->router, etc) and whose
   * encapsulation is either 802.1q or ISL.  
   *
   * Get vlanTrunkPortDynamicStatus before doing any other retrievals.
   * If a trunk's status is "notTrunking", the objects' values are
   * meaningless and processing can be skipped.  Use the
   * cisco-vlan-membership-mib to get vlan info about non- trunking
   * ports.
   **/

  /* NOTE: my environment is relatively simple, I can't test most of
   * this MIB.  I've only implemented those parts that I can test.
   *  - sep, 2007 rob
   *
   * http://www.cisco.com/c/en/us/tech/ip/simple-network-management-protocol-snmp/index.html
   **/


  /* .1.3.6.1.4.1.9.9
   *   .46 ciscoVtpMIB
   *     .1 vtpMIBObjects
   *       .3 vlanInfo : n/i - no test avail.
   *       .6 vlanTrunkPorts 
   **/

  /* .1.3.6.1.4.1.9.9.46.1.3 vlanInfo : n/i
   *   .1 vtpVlanTable
   *     .1 vtpVlanEntry
   *      .1  vtpVlanIndex : n/a
   *      .2  vtpVlanState
   *      .3  vtpVlanType
   *      .4  vtpVlanName
   *      .5  vtpVlanMtu
   *      .6  vtpVlanDot10Said
   *      .7  vtpVlanRingNumber
   *      .8  vtpVlanBridgeNumber
   *      .9  vtpVlanStpType
   *      .10 vtpVlanParentVlan
   *      .11 vtpVlanTranslationalVlan1
   *      .12 vtpVlanTranslationalVlan2
   *      .13 vtpVlanBridgeType
   *      .14 vtpVlanAreHopCount
   *      .15 vtpVlanSteHopCount
   *      .16 vtpVlanIsCRFBackup
   *      .17 vtpVlanTypeExt
   *      .18 vtpVlanIfIndex
   *
   * "This table contains information on the VLANs which currently
   *  exist.  The creation, deletion or modification of entries occurs
   *  through: a) the receipt of VTP messages in VTP Clients and in VTP
   *  Servers, or, b) in VTP Servers (or in VTP transparent mode),
   *  through management operations acting upon entries in the
   *  vtpVlanEditTable and then issuing an 'apply' command via the
   *  vtpVlanEditOperation object."
   * 
   * Note: vtpVlanIfIndex.1.124 = 234
   *          .1   == VTP management domain
   *          .124 == VLAN
   *          .234 == IfIndex
   *
   * No aspect of this branch is implemented at this time.
   **/   



  /* .1.3.6.1.4.1.9.9.46.1.6 vlanTrunkPorts
   *  .1 vlanTrunkPortTable
   *    .1 vlanTrunkPortEntry
   *      .1  vlanTrunkPortIfIndex  : n/a
   *      .2  vlanTrunkPortManagementDomain  : n/i
   *      .3  vlanTrunkPortEncapsulationType : n/i
   *      .4  vlanTrunkPortVlansEnabled  
   *      .5  vlanTrunkPortNativeVlan 
   *      .6  vlanTrunkPortRowStatus  : n/i
   *      .7  vlanTrunkPortInJoins    : n/i
   *      .8  vlanTrunkPortOutJoins   : n/i
   *      .9  vlanTrunkPortOldAdverts : n/i
   *      .10 vlanTrunkPortVlansPruningEligible  : n/i
   *      .11 vlanTrunkPortVlansXmitJoined       : n/i
   *      .12 vlanTrunkPortVlansRcvJoined        : n/i
   *      .13 vlanTrunkPortDynamicState 
   *      .14 vlanTrunkPortDynamicStatus 
   *      .15 vlanTrunkPortVtpEnabled  : n/i
   *      .16 vlanTrunkPortEncapsulationOperType 
   *      .17 vlanTrunkPortVlansEnabled2k  
   *      .18 vlanTrunkPortVlansEnabled3k  
   *      .19 vlanTrunkPortVlansEnabled4k  
   *      .20 vtpVlansPruningEligible2k  : n/i 
   *      .21 vtpVlansPruningEligible3k  : n/i 
   *      .22 vtpVlansPruningEligible4k  : n/i
   *      .23 vlanTrunkPortVlansXmitJoined2k  : n/i
   *      .24 vlanTrunkPortVlansXmitJoined3k  : n/i
   *      .25 vlanTrunkPortVlansXmitJoined4k  : n/i
   *      .26 vlanTrunkPortVlansRcvJoined2k   : n/i
   *      .27 vlanTrunkPortVlansRcvJoined3k   : n/i
   *      .28 vlanTrunkPortVlansRcvJoined4k   : n/i
   *      .29 vlanTrunkPortDot1qTunnel : n/i
   *
   * INDEX: "trunk port" - An ifIndex that is "trunking", an
   *        interface used to connect a network device to another
   *        network device (eg, router or switch) over which
   *        communications are encapsulated in 802.1q or ISL.  If a
   *        port (ifIndex) is not "trunking", none of these objects
   *        will be relevant, though they might contain garbage
   *        data.  Test vlanTrunkPortDynamicStatus to find out if a
   *        port is trunking.
   *
   **/


  /* .1.3.6.1.4.1.9.9.46.1.6.1 vlanTrunkPortTable
   *   .1 vlanTrunkPortEntry
   *     .4  vlanTrunkPortVlansEnabled  
   *
   * INDEX: trunk port (ifIndex)
   *
   * A list of the vlans between 1 and 1023 that are enabled on a
   * trunk port
   *
   * FUNCTION
   * get_vlanTrunkPortVlansEnabled ($device_name, $community, &$device, 
   *                                $if="")
   *
   * Populates $device["interfaces"][$ifIndex]["vlansEnabled"]
   *
   * WARNING: Call get_vlanTrunkPortEncapsulationOperType() first !
   * The value of this object cannot be properly evaluated without
   * knowing the vlanTrunkPortEncapsulationOperType for an
   * interface.
   **/   

function get_vlanTrunkPortVlansEnabled ($device_name, 
                                        $community,
                                        &$device,
                                        $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.46.1.6.1.1.4";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        if(empty($val))   {  continue;  }

        preg_match('/[0-9]+$/i', $key, $matches);

            /* CISCO-VTP-MIB::vlanTrunkPortVlansEnabled.48 = 
             *   Hex-STRING: 7F FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * CISCO-VTP-MIB::vlanTrunkPortVlansEnabled.49 = 
             *   Hex-STRING: 40 00 00 00 00 00 00 00 00 00 00 00 00 00 00 40
             * 04 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * 02 01 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 48
             * )
             *
             * $matches[0] is ifIndex, $val is a bit map of vlans.
             *
             * In this example, the first value is representative of a
             * non-trunking port (meaningless) and the second is
             * representative of a trunking port (real data).
             *
             * The value returned is type "octet string", a hex string.
             * It can be broken into bytes by unpacking the string as
             * unsigned chars, resulting in an array of 128 1-byte values
             * with the initial index being 1 (rather than 0).
             * 
             * Subtract 1 from the array index and multiply the result by 8
             * to get the vlan# represented by the left-most bit in the byte.
             * then mask:
             *
             * ( (array index - 1) * 8 ) + the position of each 1 bit
             * in the byte gives the value of a vlan.
             **/

        $ifIndex = $matches[0];
        
            /* Pointer makes reading the code easier */

        $if = &$device["interfaces"][$ifIndex];
        
            /* Check interface encapsulation.  If not set, $val is
             * both irrelevant and probably full of random data.
             **/

        if (!isset($if["vlanTrunkPortEncapsulationOperType"])
            ||
            ($if["vlanTrunkPortEncapsulationOperType"] === "notApplicable")
            ||
            ($if["vlanTrunkPortEncapsulationOperType"] === "negotiating"))
        {
            continue;
        }
        
        $bytes = unpack("C*", $val);
        
        foreach ($bytes as $i=>$byte)
        {
            for ($a = 7; $a >= 0; $a--)
            {
                $mask = pow(2, $a);  
                
                if (($byte & $mask) === $mask)
                {
                    $vlan = ((($i-1) * 8) + (7 - $a));

                    $device["interfaces"][$ifIndex]["vlansEnabled"][] = 
                        $vlan;
                }
            }
        }
    }
}


    /* .1.3.6.1.4.1.9.9.46.1.6.1 vlanTrunkPortTable
     *   .1 vlanTrunkPortEntry
     *     .5  vlanTrunkPortVlansEnabled  
     *
     * INDEX: trunk port (ifIndex)
     *
     * The vlanIndex of the vlan of native frames (raw,
     * unencapsulated) sent/recv on a port, if native frames are
     * allowed.  If they aren't, the value will be zero.
     *
     * FUNCTION
     * get_vlanTrunkPortNativeVlan ($device_name, $community, &$device, 
     *                              $if="")
     *
     * Populates $device["interfaces"][$ifIndex]["nativeVlan"]
     **/   

function get_vlanTrunkPortNativeVlan ($device_name, 
                                      $community, 
                                      &$device, 
                                      $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.46.1.6.1.1.5";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /* CISCO-VTP-MIB::vlanTrunkPortNativeVlan.49 = INTEGER: 900
             * CISCO-VTP-MIB::vlanTrunkPortNativeVlan.50 = INTEGER: 1
             *
             * ifIndex = $matches[0];
             *
             * Note: no need to check trunking status of the port 
             **/
        
        if (is_numeric($val) && ($val !== "0"))
        {
            $device["interfaces"][$matches[0]]["nativeVlan"] = $val;
        }
        
    }
}


    /* .1.3.6.1.4.1.9.9.46.1.6.1 vlanTrunkPortTable
     *   .1 vlanTrunkPortEntry
     *     .13 vlanTrunkPortDynamicState
     *
     * 1 = "on"
     * 2 = "off"
     * 3 = "desireable"
     * 4 = "auto"
     * 5 = "onNoNegotiate"
     *
     * INDEX: trunk port (ifIndex)
     *
     * On devices that permit dynamic determination of trunking between
     * two devices, this object reports the operator-mandated behavior:
     *
     * FUNCTION
     * get_vlanTrunkPortDynamicState ($device_name, $community, &$device, 
     *                                $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["vlanTrunkPortDynamicState"]
     **/   

function get_vlanTrunkPortDynamicState ($device_name, 
                                        $community, 
                                        &$device, 
                                        $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.46.1.6.1.1.13";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /* CISCO-VTP-MIB::vlanTrunkPortDynamicState.48 = INTEGER: off(2)
             * CISCO-VTP-MIB::vlanTrunkPortDynamicState.49 = INTEGER: on(1)
             * 
             * $matches[0] is ifIndex
             */

        $device["interfaces"][$matches[0]]["vlanTrunkPortDynamicState"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.46.1.6.1 vlanTrunkPortTable
     *   .1 vlanTrunkPortEntry
     *     .14 vlanTrunkPortDynamicStatus
     *
     * "1" = "trunking"
     * "2" = "notTrunking"
     *
     * INDEX: trunk port (ifIndex)
     *
     * Indicates whether a port is acting as a trunk or not, based on
     * vlanTrunkPortDynamicState and ifOperStatus
     *
     * FUNCTION
     * get_vlanTrunkPortDynamicStatus ($device_name, $community, &$device, 
     *                                 $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["vlanTrunkPortDynamicStatus"]
     **/   

function get_vlanTrunkPortDynamicStatus ($device_name, 
                                         $community, 
                                         &$device, 
                                         $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.46.1.6.1.1.14";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /* CISCO-VTP-MIB::vlanTrunkPortDynamicStatus.48 = 
             *   INTEGER: notTrunking(2)
             * CISCO-VTP-MIB::vlanTrunkPortDynamicStatus.49 = 
             *   INTEGER: trunking(1)
             *
             * $matches[0] is ifIndex
             */
        
        $device["interfaces"][$matches[0]]["vlanTrunkPortDynamicStatus"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.46.1.6.1 vlanTrunkPortTable
     *   .1 vlanTrunkPortEntry
     *     .16 vlanTrunkPortEncapsulationOperType
     *
     * 1 = "isl"
     * 2 = "dot10"
     * 3 = "lane"
     * 4 = "dot1Q"
     * 5 = "negotiating"
     * 6 = "notApplicable"
     *
     * INDEX: trunk port (ifIndex)
     *
     * Indicates the type of encapsulation in use on the port
     *
     * FUNCTION
     * get_vlanTrunkPortEncapsulationOperType ($device_name, 
     *                                         $community, 
     *                                         &$device, 
     *                                         $if="")
     *
     * Sets 
     * $device["interfaces"][$ifIndex]["vlanTrunkPortEncapsulationOperType"]
     **/   

function get_vlanTrunkPortEncapsulationOperType ($device_name, 
                                                 $community, 
                                                 &$device, 
                                                 $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.46.1.6.1.1.16";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /* CISCO-VTP-MIB::vlanTrunkPortEncapsulationOperType.48 = 
             *   INTEGER: notApplicable(6)
             * CISCO-VTP-MIB::vlanTrunkPortEncapsulationOperType.49 = 
             *   INTEGER: dot1Q(4)
             *
             * $matches[0] is ifIndex
             */
        
        $device["interfaces"][$matches[0]]["vlanTrunkPortEncapsulationOperType"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.46.1.6.1 vlanTrunkPortTable
     *   .1 vlanTrunkPortEntry
     *     .17 vlanTrunkPortVlansEnabled2k
     *
     * INDEX: trunk port (ifIndex)
     *
     * A list of the vlans between 1024 and 2047 that are enabled on a
     * trunk port
     *
     * FUNCTION
     * get_vlanTrunkPortVlansEnabled2k ($device_name, 
     *                                  $community, 
     *                                  &$device, 
     *                                  $if="")
     *
     * Populates $device["interfaces"][$ifIndex]["vlansEnabled"]
     *
     * WARNING: Call get_vlanTrunkPortEncapsulationOperType() first !
     * The value of this object cannot be properly evaluated without
     * knowing the vlanTrunkPortEncapsulationOperType for an
     * interface.
     **/   

function get_vlanTrunkPortVlansEnabled2k ($device_name, 
                                          $community, 
                                          &$device,
                                          $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.46.1.6.1.1.17";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        if(empty($val))   {  continue;  }

        preg_match('/[0-9]+$/i', $key, $matches);

            /* CISCO-VTP-MIB::vlanTrunkPortVlansEnabled2k.48 = 
             *   Hex-STRING: FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * CISCO-VTP-MIB::vlanTrunkPortVlansEnabled2k.49 = ""
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 48
             * )
             *
             * ifIndex = $matches[0], $val is a bitmap of vlans.
             *
             * In the example shown, port 48 is NOT trunking while
             * port 49 IS trunking.  The object for 48 contains junk
             * data, the object for 49 reports accurately that there
             * are no vlans between 1024 and 2047 being trunked.
             *
             * The value returned is type "octet string", a hex string.
             * It can be broken into bytes by unpacking the string as
             * unsigned chars, resulting in an array of 128 1-byte values
             * with the initial index being 1 rather than the usual 0.
             * 
             * Subtract 1 from the array index and multiply the result by 8
             * To get the vlan# represented by the left-most bit in the byte.
             * then mask
             *
             * ( (array index - 1) * 8 ) + the position of each 1 bit
             * in the byte + 1024 (the starting offset for Enabled2k)
             * gives the value of a vlan.  
             **/

        $ifIndex = $matches[0];
        
            /* Pointer makes reading the code easier */

        $if = &$device["interfaces"][$ifIndex];
        
            /* Check interface encapsulation.  if not set, $val is
             * both irrelevant and probably full of random data.
             **/

        if (!isset($if["vlanTrunkPortEncapsulationOperType"])
            ||
            ($if["vlanTrunkPortEncapsulationOperType"] === "notApplicable")
            ||
            ($if["vlanTrunkPortEncapsulationOperType"] === "negotiating"))
        {
            continue;
        }

        
        $bytes = unpack("C*", $val);
        
        foreach ($bytes as $i=>$byte)
        {
            for ($a = 7; $a >= 0; $a--)
            {
                $mask = pow(2, $a);  
                
                if (($byte & $mask) === $mask)
                {
                    $vlan = ((($i-1) * 8) + (7 - $a)) + 1024;
                    $device["interfaces"][$ifIndex]["vlansEnabled"][] = 
                        $vlan;
                }
            }
        }
    }
}


    /* .1.3.6.1.4.1.9.9.46.1.6.1 vlanTrunkPortTable
     *   .1 vlanTrunkPortEntry
     *     .18 vlanTrunkPortVlansEnabled3k
     *
     * INDEX: trunk port (ifIndex)
     *
     * A list of the vlans between 2048 and 3071 that are enabled on a
     * trunk port
     *
     * FUNCTION
     * get_vlanTrunkPortVlansEnabled3k ($device_name, 
     *                                  $community, 
     *                                  &$device, 
     *                                  $if="")
     *
     * Populates $device["interfaces"][$ifIndex]["vlansEnabled"]
     *
     * WARNING: Call get_vlanTrunkPortEncapsulationOperType() first !
     * The value of this object cannot be properly evaluated without
     * knowing the vlanTrunkPortEncapsulationOperType for an
     * interface.
     **/   

function get_vlanTrunkPortVlansEnabled3k ($device_name, 
                                          $community, 
                                          &$device, 
                                          $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.46.1.6.1.1.18";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        if(empty($val))   {  continue;  }

        preg_match('/[0-9]+$/i', $key, $matches);

            /* CISCO-VTP-MIB::vlanTrunkPortVlansEnabled3k.48 = 
             *   Hex-STRING: FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * CISCO-VTP-MIB::vlanTrunkPortVlansEnabled3k.49 = ""
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 48
             * )
             *
             * ifIndex = $matches[0], $val is a bitmap of vlans.
             *
             * In the example shown, port 48 is NOT trunking while
             * port 49 IS trunking.  The object for 48 contains junk
             * data, the object for 49 reports accurately that there
             * are no vlans between 2048 and 3071 being trunked.
             *
             * The value returned is type "octet string", a hex string.
             * It can be broken into bytes by unpacking the string as
             * unsigned chars, resulting in an array of 128 1-byte values
             * with the initial index being 1 rather than the usual 0.
             * 
             * Subtract 1 from the array index and multiply the result by 8
             * to get the vlan# represented by the left-most bit in the byte.
             * Then mask
             *
             * ( (array index - 1) * 8 ) + the position of each 1 bit
             * in the byte + 2048 (the starting offset for Enabled3k)
             * gives the value of a vlan.
             **/

        $ifIndex = $matches[0];
        
            /* Pointer makes reading the code easier */

        $if = &$device["interfaces"][$ifIndex];
        
            /* Check interface encapsulation.  If not set, $val is
             * both irrelevant and probably full of random data.
             **/

        if (!isset($if["vlanTrunkPortEncapsulationOperType"])
            ||
            ($if["vlanTrunkPortEncapsulationOperType"] === "notApplicable")
            ||
            ($if["vlanTrunkPortEncapsulationOperType"] === "negotiating"))
        {
            continue;
        }

        
        $bytes = unpack("C*", $val);
        
        foreach ($bytes as $i=>$byte)
        {
            for ($a = 7; $a >= 0; $a--)
            {
                $mask = pow(2, $a);  
                
                if (($byte & $mask) === $mask)
                {
                    $vlan = ((($i-1) * 8) + (7 - $a)) + 2048;
                    $device["interfaces"][$ifIndex]["vlansEnabled"][] = 
                        $vlan;
                }
            }
        }
    }
}


    /* .1.3.6.1.4.1.9.9.46.1.6.1 vlanTrunkPortTable
     *   .1 vlanTrunkPortEntry
     *     .19 vlanTrunkPortVlansEnabled4k
     *
     * INDEX: trunk port (ifIndex)
     *
     * A list of the vlans between 3072 and 4095 that are enabled on a
     * trunk port
     *
     * FUNCTION
     * get_vlanTrunkPortVlansEnabled4k ($device_name, 
     *                                  $community, 
     *                                  &$device, 
     *                                  $if="")
     *
     * Populates $device["interfaces"][$ifIndex]["vlansEnabled"]
     *
     * WARNING: Call get_vlanTrunkPortEncapsulationOperType() first !
     * The value of this object cannot be properly evaluated without
     * knowing the vlanTrunkPortEncapsulationOperType for an
     * interface.
     **/   

function get_vlanTrunkPortVlansEnabled4k ($device_name, 
                                          $community, 
                                          &$device, 
                                          $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.46.1.6.1.1.19";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        if(empty($val))   {  continue;  }
        
         preg_match('/[0-9]+$/i', $key, $matches);

           /* CISCO-VTP-MIB::vlanTrunkPortVlansEnabled4k.48 = 
             *   Hex-STRING: FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF FF
             * CISCO-VTP-MIB::vlanTrunkPortVlansEnabled4k.49 = ""
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 48
             * )
             *
             * ifIndex = $matches[0], $val is a bitmap of vlans.
             *
             *
             * In the example shown, port 48 is NOT trunking while
             * port 49 IS trunking.  The object for 48 contains junk
             * data, the object for 49 reports accurately that there
             * are no vlans between 3072 and 4095 being trunked.
             *
             * The value returned is type "octet string", a hex string.
             * It can be broken into bytes by unpacking the string as
             * unsigned chars, resulting in an array of 128 1-byte values
             * with the initial index being 1 rather than the usual 0.
             * 
             * Subtract 1 from the array index and multiply the result by 8
             * to get the vlan# represented by the left-most bit in the byte.
             * Then mask
             *
             * ( (array index - 1) * 8 ) + the position of each 1 bit
             * in the byte + 3072 (the starting offset for Enabled4k)
             * gives the value of a vlan.
             **/

        $ifIndex = $matches[0];
        
            /* Pointer makes reading the code easier */

        $if = &$device["interfaces"][$ifIndex];
        
            /* Check interface encapsulation.  If not set, $val is
             * both irrelevant and probably full of random data.
             **/

        if (!isset($if["vlanTrunkPortEncapsulationOperType"])
            ||
            ($if["vlanTrunkPortEncapsulationOperType"] === "notApplicable")
            ||
            ($if["vlanTrunkPortEncapsulationOperType"] === "negotiating"))
        {
            continue;
        }

 
        $bytes = unpack("C*", $val);
        
        foreach ($bytes as $i=>$byte)
        {
            for ($a = 7; $a >= 0; $a--)
            {
                $mask = pow(2, $a);  
                
                if (($byte & $mask) === $mask)
                {
                    $vlan = ((($i-1) * 8) + (7 - $a)) + 3072;
                    $device["interfaces"][$ifIndex]["vlansEnabled"][] = 
                        $vlan;
                }
            }
        }
    }
}

?>
