<?php /* -*- c -*- */

 /*
   * Copyright (c) 2014 Robin Garner (robin@nukefest.org)
   * All rights reserved.
   *
   * License: GPL v.3
   */

  /* v.1.0  2007  Implements CISCO-STACK-MIB
   */

  /* Quoth the mib: "This MIB provides configuration and runtime
   * status for -- chassis, modules, ports, etc. on the Catalyst
   * systems.
   **/

  /* .1.3.6.1.4.1.9.5 workgroup
   *   .0 ciscoStackNotificationsPrefix
   *   .1 ciscoStackMIB
   *     .1 systemGrp : n/i
   *     .2 chassisGrp : n/i
   *     .3 moduleGrp : n/i
   *     .4 portGrp 
   *     .5 tftpGrp : n/i
   *     .6 brouterGrp : n/i
   *     .7 filterGrp : n/i
   *     .8 monitorGrp : n/i
   *     .9 vlanGrp : n/i
   *     .10 securityGrp : n/i
   *     .11 tokenRingGrp : n/i
   *     .12 multicastGrp : n/i
   *     .13 dnsGrp : n/i
   *     .14 syslogGrp : n/i
   *     .15 ntpGrp : n/i
   *     .16 tacacsGrp : n/i
   *     .17 ipPermitListGrp : n/i
   *     .18 portChannelGrp : n/i
   *     .19 portCpbGrp : n/i
   *     .20 portTopNGrp : n/i
   *     .21 mdgGrp : n/i
   *     .22 radiusGrp : n/i
   *     .24 traceRouteGrp : n/i
   *     .25 fileCopyGrp : n/i
   *     .26 voiceGrp : n/i
   *     .27 portJumboFrameGrp : n/i
   *     .28 switchAccelerationGrp : n/i
   *     .29 configGrp : n/i
   *     .31 ciscoStackMIBConformance : n/i
   **/

  /* .1.3.6.1.4.1.9.5.1 ciscoStackMIB
   *   .4 portGrp
   *     .1 portTable
   *       .1 portEntry
   *         .1 portModuleIndex : n/i
   *         .2 portIndex : n/i
   *         .3 portCrossIndex : n/i
   *         .4 portName : n/i
   *         .5 portType : n/i
   *         .6 portOperStatus 
   *         .7 portCrossGroupIndex : n/i
   *         .8 portAdditionalStatus : n/i
   *         .9 portAdminSpeed : n/i
   *         .10 portDuplex
   *         .11 portIfIndex
   *         .12 portSpantreeFastStart : n/i
   *         .13 portAdminRxFlowControl : n/i
   *         .14 portOperRxFlowControl : n/i
   *         .15 portAdminTxFlowControl : n/i
   *         .16 portOperTxFlowControl : n/i
   *         .17 portMacControlTransmitFrames : n/i
   *         .18 portMacControlReceiveFrames : n/i
   *         .19 portMacControlPauseTransmitFrames : n/i
   *         .20 portMacControlPauseReceiveFrames : n/i
   *         .21 portMacControlUnknownProtocolFrames : n/i
   *         .22 portLinkFaultStatus : n/i
   *         .23 portAdditionalOperStatus : n/i
   *         .24 portInlinePowerDetect : n/i
   *         .25 portEntPhysicalIndex : n/i
   *         .26 portErrDisableTimeOutEnable : n/i
   *
   * INDEX  { portModuleIndex, portIndex }
   *
   * the portModule is an index identifying a module that has ports:
   * effectively, a card in a chassis.  a portIndex is an index
   * identifying a particular port on a module.  use portIfIndex to 
   * correlate portModule.portIndex to ifIndex.
   **/


  /* .1.3.6.1.4.1.9.5.1.4 portGrp
   *   .1 portTable
   *
   * INDEX  { portModuleIndex, portIndex }
   *
   * FUNCTION
   * get_portTable ($device_name, $community, &$device, $mod="", $port="")
   *
   * populates $device["portGrp"][$mod][$port]
   *
   * adds pointer $device["interfaces"][$if]["portTable"] =>
   * &$device["portGrp"][$mod][$port];
   **/

function get_portTable ($device_name, 
                        $community, 
                        &$device, 
                        $mod="",
                        $port="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.5.1.4.1";
    $oid .= (!empty($mod) && is_numeric($mod)) ? ".1.$mod" : "";
    $oid .= (!empty($mod) && is_numeric($mod) 
             && !empty($port) && is_numeric($port)) ? ".$port" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)\.([0-9]+)$/i', $key, $matches);
        
            /* CISCO-STACK-MIB::portOperStatus.7.23 = INTEGER: other(1)
             * CISCO-STACK-MIB::portOperStatus.7.24 = INTEGER: ok(2)
             * CISCO-STACK-MIB::portOperStatus.7.35 = INTEGER: minorFault(3)
             * 
             * structure of $matches:
             *
             * Array
             * (
             *     [0] => portModuleIndex.7.23
             *     [1] => portModuleIndex
             *     [2] => 7
             *     [3] => 23
             * )
             *
             * $matches[1] is the object, $matches[2] is the module
             * id#, $matches[3] is the port id#
             **/

        $device["portGrp"][$matches[2]][$matches[3]][$matches[1]] = $val;

            /* add pointer from $device["interfaces"] */

        if ($matches[1] === "portIfIndex")
        {
            $device["interfaces"][$val]["portTable"] =
                &$device["portGrp"][$matches[2]][$matches[3]];
        }
    }
}


  /* .1.3.6.1.4.1.9.5.1.4 portGrp
   *   .1 portTable
   *     .1 portEntry
   *       .6 portOperStatus 
   *
   * 1 == other
   * 2 == ok
   * 3 == minorFault
   * 4 == majorFault
   *
   * INDEX  { portModuleIndex, portIndex }
   *
   * FUNCTION
   * get_portOperStatus ($device_name, $community, &$device, $mod="", $port="")
   *
   * populates $device["portGrp"][$mod][$port]["portOperStatus"]
   *
   * if get_portIfIndex() is called, a pointer will exist:
   * $device["interfaces"][$if]["portTable"] =>
   * &$device["portGrp"][$mod][$port];
   **/

function get_portOperStatus ($device_name, 
                             $community, 
                             &$device, 
                             $mod="",
                             $port="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.5.1.4.1.1.6";
    $oid .= (!empty($mod) && is_numeric($mod)) ? ".$mod" : "";
    $oid .= (!empty($mod) && is_numeric($mod) 
             && !empty($port) && is_numeric($port)) ? ".$port" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/([0-9]+)\.([0-9]+)$/i', $key, $matches);

            /* CISCO-STACK-MIB::portOperStatus.7.23 = INTEGER: other(1)
             * CISCO-STACK-MIB::portOperStatus.7.24 = INTEGER: ok(2)
             * CISCO-STACK-MIB::portOperStatus.7.35 = INTEGER: minorFault(3)
             * 
             * structure of $matches:
             * Array
             * (
             *     [0] => 7.23
             *     [1] => 7
             *     [2] => 23
             * )
             *
             * $matches[1] is the module id#, $matches[2] is the port id#
             */

        $device["portGrp"][$matches[1]][$matches[2]]["portOperStatus"] = $val;
    }
}


  /* .1.3.6.1.4.1.9.5.1.4 portGrp
   *   .1 portTable
   *     .1 portEntry
   *       .10 portDuplex
   *
   * 1 == half
   * 2 == full
   * 3 == disagree
   * 4 == auto
   *
   * INDEX  { portModuleIndex, portIndex }
   *
   * FUNCTION
   * get_portDuplex ($device_name, $community, &$device, $mod="", $port="")
   *
   * populates $device["portGrp"][$mod][$port]["portDuplex"]
   *
   * if get_portIfIndex() is called, a pointer will exist:
   * $device["interfaces"][$if]["portTable"] =>
   * &$device["portGrp"][$mod][$port];
   **/

function get_portDuplex ($device_name, $community, &$device, $mod="", $port="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.5.1.4.1.1.10";
    $oid .= (!empty($mod) && is_numeric($mod)) ? ".$mod" : "";
    $oid .= (!empty($mod) && is_numeric($mod) 
             && !empty($port) && is_numeric($port)) ? ".$port" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/([0-9]+)\.([0-9]+)$/i', $key, $matches);

            /* CISCO-STACK-MIB::portDuplex.3.9 = INTEGER: auto(4)
             * CISCO-STACK-MIB::portDuplex.3.10 = INTEGER: full(2)
             * 
             * structure of $matches:
             * Array
             * (
             *     [0] => 3.9
             *     [1] => 3
             *     [2] => 9
             * )
             *
             * $matches[1] is the module id#, $matches[2] is the port id#
             */

        $mod  = $matches[1];
        $port = $matches[2];
        
        $device["portGrp"][$matches[1]][$matches[2]]["portDuplex"] = $val;
    }
}


  /* .1.3.6.1.4.1.9.5.1.4 portGrp
   *   .1 portTable
   *     .1 portEntry
   *       .11 portIfIndex
   *
   * INDEX  { portModuleIndex, portIndex }
   *
   * FUNCTION
   * get_portIfIndex ($device_name, $community, &$device, $mod="", $port="")
   *
   * populates $device["portGrp"][$mod][$port]["portIfIndex"]
   *
   * adds pointer $device["interfaces"][$if]["portTable"] =>
   * &$device["portGrp"][$mod][$port];
   **/

function get_portIfIndex ($device_name, 
                          $community, 
                          &$device, 
                          $mod="",
                          $port="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.5.1.4.1.1.11";
    $oid .= (!empty($mod) && is_numeric($mod)) ? ".$mod" : "";
    $oid .= (!empty($mod) && is_numeric($mod) 
             && !empty($port) && is_numeric($port)) ? ".$port" : "";

    $data = snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/([0-9]+)\.([0-9]+)$/i', $key, $matches);
        
            /* CISCO-STACK-MIB::portIfIndex.8.19 = INTEGER: 261
              * 
             * structure of $matches:
             * Array
             * (
             *     [0] => 8.19
             *     [1] => 8
             *     [2] => 19
             * )
             *
             * $matches[1] is the module id#, $matches[2] is the port id#
             */

        $device["portGrp"][$matches[1]][$matches[2]]["portIfIndex"] = $val;

            /* add pointer from $device["interfaces"] */

        $device["interfaces"][$val]["portTable"] =
            &$device["portGrp"][$matches[1]][$matches[2]];
    }
}


?>
