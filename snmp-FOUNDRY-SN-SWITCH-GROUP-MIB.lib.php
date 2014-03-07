<?php /* -*- c -*- */


  /*
   * Copyright (c) 2014 Robin Garner (robin@nukefest.org)
   * All rights reserved.
   *
   * License: GPL v.3
   */

  /* v.1.0  2008  Foundry products
   */

  /* .1.3.6.1.4.1 enterprises
   *   .1991 foundry
   *     .1 products
   *       .1 switch
   *         .1  snChassis
   *         .2  snAgentSys
   *         .3  snSwitch
   **/

  /* .1.3.6.1.4.1.1991.1.1 switch
   *   .3  snSwitch
   *     .1 snSwInfo
   *     .2 snVLanInfo
   *     .3 snSwPortInfo
   *     .4 snFdbInfo
   *     .5 snPortStpInfo
   *     .6 snTrunkInfo
   *     .7 snSwSummary
   *     .8 snDhcpGatewayListInfo
   *     .9 snDnsInfo
   *     .10 snMacFilter
   *     .11 snNTP
   *     .12 snRadius
   *     .13 snTacacs
   *     .14 snQos
   *     .15 snAAA
   *     .16 snCAR
   *     .17 snVLanCAR
   *     .18 snNetFlow
   *     .19 snSFlow
   *     .20 snFDP
   *     .21 snVsrp
   *     .22 snArpInfo
   *     .23 snWireless
   *     .24 snMac
   *     .25 snPortMonitor
   *     .26 snSSH
   *     .27 snSSL
   **/

  /* .1.3.6.1.4.1.1991.1.1 switch
   *   .3  snSwitch
   *     .1 snSwInfo
   *     .2 snVLanInfo
   *     .3 snSwPortInfo
   *       .1 snSwPortInfoTable
   *         .1 snSwPortInfoEntry 
   *           .1  snSwPortInfoPortNum
   *           .2  snSwPortInfoMonitorMode
   *           .3  snSwPortInfoTagMode
   *           .4  snSwPortInfoChnMode
   *           .5  snSwPortInfoSpeed
   *           .6  snSwPortInfoMediaType
   *           .7  snSwPortInfoConnectorType
   *           .8  snSwPortInfoAdminStatus
   *           .9  snSwPortInfoLinkStatus
   *           .10 snSwPortInfoPortQos
   *           .11 snSwPortInfoPhysAddress
   *           .12 snSwPortStatsInFrames 
   *           .13 snSwPortStatsOutFrames 
   *           .14 snSwPortStatsAlignErrors            
   *           .15 snSwPortStatsFCSErrors                  
   *           .16 snSwPortStatsMultiColliFrames    
   *           .17 snSwPortStatsFrameTooLongs              
   *           .18 snSwPortStatsTxColliFrames
   *           .19 snSwPortStatsRxColliFrames
   *           .20 snSwPortStatsFrameTooShorts
   *           .21 snSwPortLockAddressCount
   *           .22 snSwPortStpPortEnable
   *           .23 snSwPortDhcpGateListId
   *           .24 snSwPortName
   *           .25 snSwPortStatsInBcastFrames
   *           .26 snSwPortStatsOutBcastFrames
   *           .27 snSwPortStatsInMcastFrames
   *           .28 snSwPortStatsOutMcastFrames
   *           .29 snSwPortStatsInDiscard
   *           .30 snSwPortStatsOutDiscard
   *           .31 snSwPortStatsMacStations
   *           .32 snSwPortCacheGroupId
   *           .33 snSwPortTransGroupId
   *           .34 snSwPortInfoAutoNegotiate
   *           .35 snSwPortInfoFlowControl
   *           .36 snSwPortInfoGigType
   *           .37 snSwPortStatsLinkChange
   *           .38 snSwPortIfIndex
   *           .39 snSwPortDescr
   *           .40 snSwPortInOctets
   *           .41 snSwPortOutOctets
   *           .42 snSwPortStatsInBitsPerSec
   *           .43 snSwPortStatsOutBitsPerSec
   *           .44 snSwPortStatsInPktsPerSec
   *           .45 snSwPortStatsOutPktsPerSec
   *           .46 snSwPortStatsInUtilization
   *           .47 snSwPortStatsOutUtilization
   *           .48 snSwPortFastSpanPortEnable
   *           .49 snSwPortFastSpanUplinkEnable
   *           .50 snSwPortVlanId
   *           .51 snSwPortRouteOnly
   *           .52 snSwPortPresent
   *           .53 snSwPortGBICStatus
   *           .54 snSwPortStatsInKiloBitsPerSec
   *           .55 snSwPortStatsOutKiloBitsPerSec    
   *           .56 snSwPortLoadInterval
   *           .57 snSwPortTagType
   *           .58 snSwPortInLinePowerControl
   *           .59 snSwPortInLinePowerWattage
   *           .60 snSwPortInLinePowerClass
   *           .61 <undefined>
   *           .62 snSwPortInfoMirrorMode
   *           .63 snSwPortStatsInJumboFrames
   *           .64 snSwPortStatsOutJumboFrames
   *
   *  INDEX   { snSwPortInfoPortNum }
   *
   * FUNCTION
   * get_snSwPortInfoTable ($device_name, $community, &$device, $port="")
   *
   * populates $device["snSwPortInfoTable"]
   *
   * adds pointer $device["interfaces"][$if]["snSwPortInfoTable"] =>
   * &$device["snSwPortInfoTable"][$port];
   **/

function get_snSwPortInfoTable ($device_name, $community, &$device, $port="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.1991.1.1.3.3.1";
    $oid .= (!empty($port) && is_numeric($port)) ? ".1.$port" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
        
            /* FOUNDRY-SN-SWITCH-GROUP-MIB::snSwPortIfIndex.1806 = INTEGER: 398
             * 
             * structure of $matches:
             *
             * Array
             * (
             *     [0] => snSwPortIfIndex.1806
             *     [1] => snSwPortIfIndex
             *     [2] => 1806
             * )
             *
             * $matches[1] is the object, $matches[2] is the port id#
             **/

        $device["snSwPortInfoTable"][$matches[2]][$matches[1]] = $val;

            /* add pointer from $device["interfaces"] */

        if ($matches[1] === "snSwPortIfIndex")
        {
            $device["interfaces"][$val]["snSwPortInfoTable"] =
                &$device["snSwPortInfoTable"][$matches[2]];
        }
    }
}


  /* .1.3.6.1.4.1.1991.1.1.3.3.1 snSwPortInfoTable
   *   .1 snSwPortInfoEntry 
   *     .4  snSwPortInfoChnMode
   *
   * 0 == none
   * 1 == halfDuplex
   * 2 == fullDuplex
   *
   * INDEX   { snSwPortInfoPortNum }
   *
   * FUNCTION
   * get_snSwPortInfoChnMode ($device_name, $community, &$device, $port="")
   *
   * populates 
   * $device["snSwPortInfoTable"][$port]["snSwPortInfoChnMode"]
   *
   * if get_snSwPortIfIndex() has previously been called, a pointer will
   * exist: $device["interfaces"][$if]["snSwPortInfoTable"] =>
   * &$device["snSwPortInfoTable"][$port];
   **/

function get_snSwPortInfoChnMode ($device_name, $community, &$device, $port="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.1991.1.1.3.3.1.1.4";
    $oid .= (!empty($port) && is_numeric($port)) ? ".$port" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* FOUNDRY-SN-SWITCH-GROUP-MIB::snSwPortInfoChnMode.2054 = 
             *   INTEGER: fullDuplex(2)
             * FOUNDRY-SN-SWITCH-GROUP-MIB::snSwPortInfoChnMode.2055 = 
             *   INTEGER: halfDuplex(1)
             *
             * $matches[0] is the port#
             */

        $device["snSwPortInfoTable"][$matches[0]]["snSwPortInfoChnMode"] = 
            $val;
    }
}


  /* .1.3.6.1.4.1.1991.1.1.3.3.1 snSwPortInfoTable
   *   .1 snSwPortInfoEntry 
   *     .38 snSwPortIfIndex
   *
   * INDEX   { snSwPortInfoPortNum }
   *
   * FUNCTION
   * get_snSwPortIfIndex ($device_name, $community, &$device, $port="")
   *
   * populates $device["snSwPortInfoTable"][$port]["snSwPortIfIndex"]
   * 
   * adds pointer $device["interfaces"][$if]["snSwPortInfoTable"] =>
   * &$device["snSwPortInfoTable"][$port];
   **/

function get_snSwPortIfIndex ($device_name, $community, &$device, $port="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.1991.1.1.3.3.1.1.38";
    $oid .= (!empty($port) && is_numeric($port)) ? ".$port" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* FOUNDRY-SN-SWITCH-GROUP-MIB::snSwPortIfIndex.1806 = INTEGER: 398
             *
             * $matches[0] is the port#
             */

        $device["snSwPortInfoTable"][$matches[0]]["snSwPortIfIndex"] =
            $val;

        $device["interfaces"][$val]["snSwPortInfoTable"] =
            &$device["snSwPortInfoTable"][$matches[0]];

    }
}

