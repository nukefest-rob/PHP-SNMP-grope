<?php /* -*- c -*- */

  /*
   * Copyright (c) 2014 Robin Garner (robin@nukefest.org)
   * All rights reserved.
   *
   * License: GPL v.3
   */

  /* v.1.0  2007  Implements RFC 1493
   */

  /* BRIDGE-MIB
   *
   * The bridge MIB is extracted from RFC 1493 (one of the better
   * written ones out there -ed).  the RFC seems to imply that a
   * single network device will implement a single bridge (sec.3.2.1),
   * probably a safe assumption in 1993.  in practice, some modern
   * systems implement multiple bridges: certain cisco devices
   * configured for "trunking" implement one bridge per VLAN and each
   * vlan must be interogated independently.  before using these
   * functions, understand how your particular devices implement
   * dot1d.
   *
   * NOTE: This library is ethernet specific.  At the time of its
   * writing, that's all I have access to for the purposes of testing.
   * *sigh*.
   **/

  /* .1.3.6.1.2.1 mib-2
   *   .17 dot1dBridge
   *     .1 dot1dBase     
   *     .2 dot1dStp      
   *     .3 dot1dSr : defined in SOURCE-ROUTING-MIB
   *     .4 dot1dTp       
   *     .5 dot1dStatic : n/i
   *     .7 qBridgeMIB : defined in Q-BRIDGE-MIB
   *
   * dot1d base and Stp bridge sequences are indexed by "port", not
   * ifIndex.  on an ethernet (NOT x.25, etc), dot1dPorts have a
   * one-to-one correlation to ifIndices and the mapping can be
   * retrieved by getting the dot1dBasePortIfIndex object.  this
   * applies for both for 802.1d and 802.1q bridge tables.
   * transparent bridge forwarding tables are indexed by "forwarding
   * database address", a MAC address.  FdbId can be corelated to port
   * via the dot1dTpFdbPort object.
   **/


  /* .1.3.6.1.2.1.17 dot1dBridge
   *   .1 dot1dBase
   *     .1 dot1dBaseBridgeAddress
   *     .2 dot1dBaseNumPorts
   *     .3 dot1dBaseType
   *     .4 dot1dBasePortTable
   **/


  /* .1.3.6.1.2.1.17.1 dot1dBase
   *   .1 dot1dBaseBridgeAddress
   *
   * MAC addr used by the bridge
   *
   * FUNCTION
   * get_dot1dBaseBridgeAddress ($device_name, $community, &$device) 
   *
   * sets $device["dot1dBridge"]["dot1dBaseBridgeAddress"]
   **/

function get_dot1dBaseBridgeAddress ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.17.1.1.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* BRIDGE-MIB::dot1dBaseBridgeAddress.0 = 
         *   Hex-STRING: 00 04 80 1E 31 00
         **/

    $device["dot1dBridge"]["dot1dBaseBridgeAddress"] = $data;
}


    /* .1.3.6.1.2.1.17.1 dot1dBase
     *   .2 dot1dBaseNumPorts
     *
     * number of ports controlled by this bridge
     *
     * FUNCTION
     * get_dot1dBaseNumPorts ($device_name, $community, &$device) 
     *
     * sets $device["dot1dBridge"]["dot1dBaseNumPorts"]
     **/

function get_dot1dBaseNumPorts ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.17.1.2.0";

    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* BRIDGE-MIB::dot1dBaseNumPorts.0 = INTEGER: 112
        **/

    $device["dot1dBridge"]["dot1dBaseNumPorts"] = $data;
}


    /* .1.3.6.1.2.1.17.1 dot1dBase
     *   .3 dot1dBaseType
     *
     * 1 == unknown
     * 2 == transparent-only
     * 3 == sourceroute-only
     * 4 == srt
     * 
     * what type of bridging this bridge can perform.
     *
     * FUNCTION
     * get_dot1dBaseType ($device_name, $community, &$device) 
     *
     * sets $device["dot1dBridge"]["dot1dBaseType"]
     **/

function get_dot1dBaseType ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.17.1.3.0";

    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }
    
        /* BRIDGE-MIB::dot1dBaseType.0 = INTEGER: transparent-only(2)
         **/

    $device["dot1dBridge"]["dot1dBaseType"] = $data;
}


    /* .1.3.6.1.2.1.17.1 dot1dBase
     *   .4 dot1dBasePortTable
     *     .1 dot1dBasePortEntry
     *       .1 dot1dBasePort
     *       .2 dot1dBasePortIfIndex
     *       .3 dot1dBasePortCircuit
     *       .4 dot1dBasePortDelayExceededDiscards
     *       .5 dot1dBasePortMtuExceededDiscards
     * 
     * INDEX  { dot1dBasePort }
     * 
     * generic information about each port in the bridge
     *
     * FUNCTION
     * get_dot1dBasePortTable ($device_name, $community, &$device)
     * 
     * populates $device["dot1dBridge"]["portTable"][$port]
     *
     * adds pointer $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     *     $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1dBasePortTable ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);
 
    $oid  = ".1.3.6.1.2.1.17.1.4";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }

    $port_map = array();

    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
        
            /* BRIDGE-MIB::dot1dBasePort.49 = INTEGER: 49
             * BRIDGE-MIB::dot1dBasePort.50 = INTEGER: 50
             * BRIDGE-MIB::dot1dBasePortIfIndex.49 = INTEGER: 49
             * BRIDGE-MIB::dot1dBasePortIfIndex.50 = INTEGER: 50
             * BRIDGE-MIB::dot1dBasePortCircuit.49 = 
             *   OID: SNMPv2-SMI::zeroDotZero
             * BRIDGE-MIB::dot1dBasePortCircuit.50 = 
             *   OID: SNMPv2-SMI::zeroDotZero
             * BRIDGE-MIB::dot1dBasePortDelayExceededDiscards.49 = Counter32: 0
             * BRIDGE-MIB::dot1dBasePortDelayExceededDiscards.50 = Counter32: 0
             * BRIDGE-MIB::dot1dBasePortMtuExceededDiscards.49 = Counter32: 0
             * BRIDGE-MIB::dot1dBasePortMtuExceededDiscards.50 = Counter32: 0
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => dot1dBasePort.49
             *     [1] => dot1dBasePort
             *     [2] => 49
             * )
             *
             * $matches[1] is the object, $matches[2] is the dot1d port
             **/

        $device["dot1dBridge"]["portTable"][$matches[2]][$matches[1]] =
            $value;
        
            /* add pointer from $device["interfaces"][$ifIndex] */

        if ($matches[1] === "dot1dBasePortIfIndex")
        {
            $device["interfaces"][$val]["dot1dBridge"] = 
                &$device["dot1dBridge"]["portTable"][$matches[2]];
        }
    }
}


    /* .1.3.6.1.2.1.17.1.4 dot1dBasePortTable
     *   .1 dot1dBasePortEntry
     *     .2 dot1dBasePortIfIndex
     *
     * INDEX  { dot1dBasePort }
     * 
     * maps dot1dBasePort to ifIndex
     *
     * FUNCTION
     * get_dot1dBasePortIfIndex ($device_name, $community, &$device)
     * 
     * populates 
     *   $device["dot1dBridge"]["portTable"][$port]["dot1dBasePortIfIndex"]
     * adds pointer $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     *     $device["dot1dBridge"]["portTable"][$dot1dPort];
     * populates $device["dot1dIfIndexMap"]
     * populates $device["dot1dPortMap"]
     *
     *
     * this function is also declared in q-bridge library, so it must
     * be wrapped in function_exists() in both places to avoid
     * redeclaration errors.
     **/

if (!function_exists('get_dot1dBasePortIfIndex'))
{
    function get_dot1dBasePortIfIndex ($device_name, $community, &$device) 
    {
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
        snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
        snmp_set_quick_print(TRUE);
        
        $oid  = ".1.3.6.1.2.1.17.1.4.1.2";
        
        $data = @snmprealwalk ($device_name, $community, $oid);
        
        if (empty($data))  {  return;  }
        
        $port_map = array();
        
        foreach ($data as $key=>$val) 
        {
            preg_match('/[0-9]+$/i', $key, $matches);
            
                /* BRIDGE-MIB::dot1dBasePortIfIndex.49 = INTEGER: 49
                 * BRIDGE-MIB::dot1dBasePortIfIndex.50 = INTEGER: 50
                 *
                 * structure of $matches:
                 * Array
                 * (
                 *     [0] => 49
                 * )
                 *
                 * $matches[0] is the dot1d port, $val is the ifIndex
                 **/
            
            $device["dot1dBridge"]["portTable"][$matches[0]]["dot1dBasePortIfIndex"] = $val;
            
                /* add pointer to $device["interfaces"][$ifIndex] */

            $device["interfaces"][$val]["dot1dBridge"] = 
                &$device["dot1dBridge"]["portTable"][$matches[0]];
        }
    }
}



    /* .1.3.6.1.2.1.17 dot1dBridge
     *   .2 dot1dStp
     *     .1 dot1dStpProtocolSpecification : n/i
     *     .2 dot1dStpPriority : n/i
     *     .3 dot1dStpTimeSinceTopologyChange : n/i
     *     .4 dot1dStpTopChanges : n/i
     *     .5 dot1dStpDesignatedRoot : n/i
     *     .6 dot1dStpRootCost : n/i
     *     .7 dot1dStpRootPort : n/i
     *     .8 dot1dStpMaxAge   : n/i
     *     .9 dot1dStpHelloTime : n/i
     *     .10 dot1dStpHoldTime : n/i
     *     .11 dot1dStpForwardDelay : n/i
     *     .12 dot1dStpBridgeMaxAge : n/i
     *     .13 dot1dStpBridgeHelloTime    : n/i
     *     .14 dot1dStpBridgeForwardDelay : n/i
     *     .15 dot1dStpPortTable
     *       .1 dot1dStpPortEntry
     *         .1  dot1dStpPort
     *         .2  dot1dStpPortPriority
     *         .3  dot1dStpPortState
     *         .4  dot1dStpPortEnable
     *         .5  dot1dStpPortPathCost
     *         .6  dot1dStpPortDesignatedRoot
     *         .7  dot1dStpPortDesignatedCost
     *         .8  dot1dStpPortDesignatedBridge
     *         .9  dot1dStpPortDesignatedPort
     *         .10 dot1dStpPortForwardTransitions
     *  
     * state of the spanning tree protocol on this bridge
     *
     * FUNCTION
     * get_dot1dStp ($device_name, $community, &$device) 
     *
     * retrieves objects 1 through 14, calls get_dot1dStpPortTable()
     *
     * sets $device["dot1dBridge"]["Stp"]
     **/


function get_dot1dStp ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);
 
    $base_oid  = ".1.3.6.1.2.1.17.2";

        /* get the first 14 objects; they're all scalars:
         *
         * BRIDGE-MIB::dot1dStpProtocolSpecification.0 = 
         *   INTEGER: ieee8021d(3)
         * BRIDGE-MIB::dot1dStpPriority.0 = INTEGER: 49153
         * BRIDGE-MIB::dot1dStpTimeSinceTopologyChanges.0 = 
         *   Timeticks: (114529100) 13 days, 6:08:11.00
         * BRIDGE-MIB::dot1dStpTopChanges.0 = Counter32: 8
         * BRIDGE-MIB::dot1dStpDesignatedRoot.0 = 
         *   Hex-STRING: 80 00 00 12 F2 EB E6 2E 
         * BRIDGE-MIB::dot1dStpRootCost.0 = INTEGER: 6008
         * BRIDGE-MIB::dot1dStpRootPort.0 = INTEGER: 49
         * BRIDGE-MIB::dot1dStpMaxAge.0 = INTEGER: 2000
         * BRIDGE-MIB::dot1dStpHelloTime.0 = INTEGER: 200
         * BRIDGE-MIB::dot1dStpHoldTime.0 = INTEGER: 100
         * BRIDGE-MIB::dot1dStpForwardDelay.0 = INTEGER: 1500
         * BRIDGE-MIB::dot1dStpBridgeMaxAge.0 = INTEGER: 2000
         * BRIDGE-MIB::dot1dStpBridgeHelloTime.0 = INTEGER: 200
         * BRIDGE-MIB::dot1dStpBridgeForwardDelay.0 = INTEGER: 1500
         **/

    for ($i=1; $i <= 14; $i++)
    {
        $oid = $base_oid.".$i";
        
        $data = @snmprealwalk ($device_name, $community, $oid);
    
        if (empty($data))  {  continue;  }

        foreach ($data as $key=>$val)
        {
            preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
        
                /* BRIDGE-MIB::dot1dStpPriority.0 = INTEGER: 49153
                 *
                 * structure of $matches:
                 * Array
                 * (
                 *     [0] => dot1dStpPriority.0
                 *     [1] => dot1dStpPriority
                 *     [2] => 0
                 * )
                 **/

            $device["dot1dBridge"]["Stp"][$matches[1]] = $val;
        }
    }

        /* get object 15, dot1dStpPortState */

    get_dot1dStpPortTable ($device_name, $community, $device);
}


    /* .1.3.6.1.2.1.17.2 dot1dStp
     *   .15 dot1dStpPortTable
     *     .1 dot1dStpPortEntry
     *       .1  dot1dStpPort
     *       .2  dot1dStpPortPriority
     *       .3  dot1dStpPortState
     *       .4  dot1dStpPortEnable
     *       .5  dot1dStpPortPathCost
     *       .6  dot1dStpPortDesignatedRoot
     *       .7  dot1dStpPortDesignatedCost
     *       .8  dot1dStpPortDesignatedBridge
     *       .9  dot1dStpPortDesignatedPort
     *       .10 dot1dStpPortForwardTransitions
     *
     * INDEX   { dot1dStpPort }
     *
     * snmp_set_valueretrieval(SNMP_VALUE_PLAIN) is NOT USED because
     * some of the values returned are type Hex-STRING.
     *
     * FUNCTION
     * get_dot1dStpPortTable ($device_name, $community, &$device)
     *
     * populates $device["dot1dBridge"]["portTable"][$stp_oid]
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1dStpPortTable ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);
    
    $oid = ".1.3.6.1.2.1.17.2.15";
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
        
            /* BRIDGE-MIB::dot1dStpPortState.4 = INTEGER: disabled(1)
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => dot1dStpPort.49
             *     [1] => dot1dStpPort
             *     [2] => 49
             * )
             *
             * $matches[1] is the object, $matches[2] is the port
             **/
        
        $device["dot1dBridge"]["portTable"][$matches[2]][$matches[1]] =
            $value;
    }
}


    /* .1.3.6.1.2.1.17.2.15 dot1dStpPortTable
     *   .1 dot1dStpPortEntry
     *     .3  dot1dStpPortState
     *
     * INDEX   { dot1dStpPort }
     *
     * FUNCTION
     * get_dot1dStpPortTable ($device_name, $community, &$device)
     *
     * populates 
     * $device["dot1dBridge"]["portTable"][$port]["dot1dStpPortState"]
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1dStpPortState ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);
    
    $oid = ".1.3.6.1.2.1.17.2.15.1.3";
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* BRIDGE-MIB::dot1dStpPortState.4 = INTEGER: disabled(1)
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 4
             * ) 
             *
             * $matches[0] is the port, $value is stpPortState
             **/
        
        $device["dot1dBridge"]["portTable"][$matches[0]]["dot1dStpPortState"] =
            $value;
    }
}


    /* .1.3.6.1.2.1.17.2.15 dot1dStpPortTable
     *   .1 dot1dStpPortEntry
     *     .4 dot1dStpPortEnable
     *
     * 1 == enabled
     * 2 == disabled
     *
     * INDEX   { dot1dStpPort }
     * 
     * FUNCTION
     * get_dot1dStpPortEnable ($device_name, $community, &$device)
     *
     * populates 
     * $device["dot1dBridge"]["portTable"][$port]["dot1dStpPortEnable"]
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1dStpPortEnable ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);
    
    $oid  = ".1.3.6.1.2.1.17.2.15.1.4";
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* BRIDGE-MIB::dot1dStpPortEnable.921 = INTEGER: enabled(1)
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 921
             * ) 
             *
             * $matches[0] is the port, $value is stpPortState
             **/
        
        $device["dot1dBridge"]["portTable"][$matches[0]]["dot1dStpPortState"] =
            $value;
    }
}


    /* .1.3.6.1.2.1.17 dot1dBridge
     *   .4 dot1dTp
     *     .1 dot1dTpLearnedEntryDiscards
     *     .2 dot1dTpAgingTime
     *     .3 dot1dTpFdbTable
     *
     *  transparent bridging
     *
     * FUNCTION
     * get_dot1dTp ($device_name, $community, &$device)
     *
     * retrieves objects 1 and 2, calls get_dot1dTpFdbTable()
     * 
     * populates $device["dot1dBridge"]["Tp"]
     **/

function get_dot1dTp ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);
    
    $base_oid  = ".1.3.6.1.2.1.17.4";

        /* get the first 2 objects; they're both scalars:
         *
         * BRIDGE-MIB::dot1dTpLearnedEntryDiscards.0 = Counter32: 608
         * BRIDGE-MIB::dot1dTpAgingTime.0 = INTEGER: 300
         **/

    for ($i=1; $i <= 2; $i++)
    {
        $oid = $base_oid.".$i";
        
        $data = @snmprealwalk ($device_name, $community, $oid);
    
        if (empty($data))  {  continue;  }

        foreach ($data as $key=>$val)
        {
            preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
        
                /* BRIDGE-MIB::dot1dTpAgingTime.0 = INTEGER: 300
                 *
                 * structure of $matches:
                 * Array
                 * (
                 *     [0] => dot1dTpAgingTime.0
                 *     [1] => dot1dTpAgingTime
                 *     [2] => 0
                 * )
                 **/

            $device["dot1dBridge"]["Tp"][$matches[1]] = $val;
        }
    }

        /* get object 3, dot1dTpFdbTable */

    get_dot1dTpFdbTable ($device_name, $community, $device);
}


    /* .1.3.6.1.2.1.17.4 dot1dTp
     *   .3 dot1dTpFdbTable
     *     .1 dot1dTpFdbEntry
     *       .1 dot1dTpFdbAddress
     *       .2 dot1dTpFdbPort
     *       .3 dot1dTpFdbStatus
     *
     * INDEX   { dot1dTpFdbAddress }
     * 
     * snmp_set_valueretrieval(SNMP_VALUE_PLAIN) is NOT USED because
     * some of the values returned are type Hex-STRING.
     * snmp_set_valueretrieval(SNMP_VALUE_LIBRARY) is used in
     * conjunction with snmp_set_quick_print(TRUE) so the value's type
     * isn't reported with the value:
     *   "enabled" -v- "INTEGER: enabled(1)"
     *
     * snmp_set_oid_numeric_print(TRUE) will cause the oid to be
     * returned numerically insted of being interpretted as strings:
     *
     *   dot1dTpFdbAddress.0.4.128.115.5.0 = "00 04 80 22 F6 00 "
     *    -v-
     *   dot1dTpFdbAddress.'..C,..' = Hex-STRING: 00 09 43 2C 96 00
     *
     * (reset snmp_set_oid_numeric_print(FALSE) after the retrieval!)
     * 
     * the last 6 dotted segments of the oid are the mac address in
     * DECIMAL format (MAC values are reported in HEX format).
     *
     * FUNCTION
     * get_dot1dTpFdbTable ($device_name, $community, &$device)
     *
     * populates $device["dot1dBridge"]["portTable"][$port]["TpFdbTable"]
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1dTpFdbTable ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
    snmp_set_quick_print(TRUE);
    
    $oid  = ".1.3.6.1.2.1.17.4.3.1";
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);

    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)(\.[0-9]+){6}$/i', $key, $matches);

            /* BRIDGE-MIB::dot1dTpFdbAddress.'..t..P' = 
             *    Hex-STRING: 00 00 74 92 1F 50
             *
             * since set_numeric_print(TRUE) was invoked, it's
             * actually returned thusly:
             * 
             * .1.3.6.1.2.1.17.4.3.1.1.0.6.91.119.104.210 == 
             *    "00 06 5B 77 68 D2 "
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 1.0.0.192.18.219.248
             *     [1] => 1
             *     [2] => .248
             * )
             *
             * $matches[0] is the decimal-format MAC address used as
             * the table index plus the object id (prepended).
             * substr_replace($matches[0], "", 0, 2) strips the
             * leading object id and '.' off to leave just the MAC.
             *
             * $matches[1] == the object: 
             *    1 - dot1dTpFdbAddress
             *    2 - dot1dTpFdbPort
             *    3 - dot1dTpFdbStatus
             *
             * $matches[2] is garbage.
             **/

        $idx = substr_replace($matches[0], "", 0, 2);
        
        if     ($matches[1] === "1")  {  $name = "dot1dTpFdbAddress";  }
        elseif ($matches[1] === "2")  {  $name = "dot1dTpFdbPort";  }
        elseif ($matches[1] === "3")  {  $name = "dot1dTpFdbStatus";  }
        else                          {  continue;  }

        $FdbTable[$idx][$name] = $value;
    }

    if (empty($FdbTable))
    {
        return;
    }
    
        /* transpose $FdbTable into $device["dot1dBridge"] and
         * add pointers into $device["interfaces"]
         **/

    $i=0;
    
    reset($FdbTable);

    foreach ($FdbTable as $entry)
    {
            /* accomodate incomplete tables.  it happens. */

        if (!isset($entry["dot1dTpFdbPort"]))
        {
            continue;
        }
        
            
        $port = &$entry["dot1dTpFdbPort"];  // readability


        $device["dot1dBridge"]["portTable"][$port]["TpFdbTable"][$i] =
            $entry;


        $i++;
    }
}

?>
