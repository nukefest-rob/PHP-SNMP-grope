<?php /* -*- c -*- */

  /*
   * Copyright (c) 2014 Robin Garner (robin@nukefest.org)
   * All rights reserved.
   *
   * License: GPL v.3
   */

  /* v.1.0  2007  Implements CISCO-PORT-SECURITY-MIB
   */

  /* http://www.cisco.com/c/en/us/tech/ip/simple-network-management-protocol-snmp/index.html
   */

    /* .1.3.6.1.4.1.9.9
     *   .315 ciscoPortSecurityMIB
     *     .0 ciscoPortSecurityMIBNotifs
     *     .1 ciscoPortSecurityMIBObjects
     *       .1 cpsGlobalObjects
     *       .2 cpsInterfaceObjects
     *     .2 ciscoPortSecurityMIBConform
     */

    /* .1.3.6.1.4.1.9.9.315.1 ciscoPortSecurityMIBObjects
     *   .1 cpsGlobalObjects
     *     .1 cpsGlobalMaxSecureAddress
     *     .2 cpsGlobalTotalSecureAddress
     *     .3 cpsGlobalPortSecurityEnable
     *     .4 cpsGlobalSNMPNotifRate
     *     .5 cpsGlobalSNMPNotifControl
     *     .6 cpsGlobalClearSecureMacAddresses
     *
     * FUNCTION
     * get_cpsGlobalObjects ($device_name, $community, &$device)
     *
     * populates $device["portSecurity"]
     */


function get_cpsGlobalObjects ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.315.1.1";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
        
            /* CISCO-PORT-SECURITY-MIB::cpsGlobalMaxSecureAddress.0 = 
             *   INTEGER: 1056
             * CISCO-PORT-SECURITY-MIB::cpsGlobalTotalSecureAddress.0 = 
             *   INTEGER: 0
             * CISCO-PORT-SECURITY-MIB::cpsGlobalPortSecurityEnable.0 = 
             *   INTEGER: true(1)
             * CISCO-PORT-SECURITY-MIB::cpsGlobalSNMPNotifRate.0 = 
             *   INTEGER: 0 notifs per second
             * CISCO-PORT-SECURITY-MIB::cpsGlobalSNMPNotifControl.0 = 
             *   INTEGER: true(1)
             * CISCO-PORT-SECURITY-MIB::cpsGlobalClearSecureMacAddresses.0 = 
             *   INTEGER: done(0)
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => cpsGlobalMaxSecureAddress.0
             *     [1] => cpsGlobalMaxSecureAddress
             *     [2] => 0
             * )
             */

        $device["portSecurity"][$matches[1]] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.315.1 ciscoPortSecurityMIBObjects
     *   .1 cpsGlobalObjects
     *     .3 cpsGlobalPortSecurityEnable
     *
     * "1" = true
     * "0" = false
     *
     * FUNCTION
     * get_cpsGlobalPortSecurityEnable ($device_name, $community, &$device)
     *
     * sets $device["portSecurity"]["cpsGlobalPortSecurityEnable"] 
     */

function get_cpsGlobalPortSecurityEnable ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.315.1.1.3.0";

    $data = @snmpget ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
        
        /* CISCO-PORT-SECURITY-MIB::cpsGlobalPortSecurityEnable.0 = 
         *   INTEGER: true(1)
         **/

    $device["portSecurity"]["cpsGlobalPortSecurityEnable"] = $data;
}


    /* .1.3.6.1.4.1.9.9.315.1 ciscoPortSecurityMIBObjects
     *   .2 cpsInterfaceObjects
     *     .1 cpsIfConfigTable
     *     .2 cpsSecureMacAddressTable  : deprecated
     *     .3 cpsIfVlanSecureMacAddrTable
     *     .4 cpsIfVlanTable : n/i, no test avail
     */

    /* .1.3.6.1.4.1.9.9.315.1.2.1 cpsIfConfigTable
     *   .1 cpsIfConfigEntry
     *     .1 cpsIfPortSecurityEnable
     *     .2 cpsIfPortSecurityStatus
     *     .3 cpsIfMaxSecureMacAddr
     *     .4 cpsIfCurrentSecureMacAddrCount
     *     .5 cpsIfSecureMacAddrAgingTime
     *     .6 cpsIfSecureMacAddrAgingType
     *     .7 cpsIfStaticMacAddrAgingEnable
     *     .8 cpsIfViolationAction
     *     .9 cpsIfViolationCount
     *     .10 cpsIfSecureLastMacAddress
     *     .11 cpsIfClearSecureAddresses  : n/i
     *     .12 cpsIfUnicastFloodingEnable : n/i
     *     .13 cpsIfShutdownTimeout : n/i
     *     .14 cpsIfClearSecureMacAddresses : n/i
     *     .15 cpsIfStickyEnable
     *     .16 cpsIfInvalidSrcRateLimitEnable : n/i
     *     .17 cpsIfInvalidSrcRateLimitValue  : n/i
     *
     * INDEX  { ifIndex }
     *
     * FUNCITON
     * get_cpsIfConfigTable ($device_name, $community, &$device)
     *
     * calls get_cpsIfPortSecurityEnable(),
     * get_cpsIfPortSecurityStatus(), get_cpsIfMaxSecureMacAddr(),
     * get_cpsIfCurrentSecureMacAddrCount(),
     * get_cpsIfSecureMacAddrAgingTime(),
     * get_cpsIfSecureMacAddrAgingType(),
     * get_cpsIfStaticMacAddrAgingEnable(),
     * get_cpsIfViolationAction(), get_cpsIfViolationCount(),
     * get_cpsIfSecureLastMacAddress(), get_cpsIfStickyEnable()
     *
     * populates $device["interfaces"][$ifIndex]["portSecurity"]
     **/

function get_cpsIfConfigTable ($device_name, $community, &$device)
{
    get_cpsIfPortSecurityEnable($device_name, $community, $device);
    get_cpsIfPortSecurityStatus($device_name, $community, $device);
    get_cpsIfMaxSecureMacAddr($device_name, $community, $device);
    get_cpsIfCurrentSecureMacAddrCount($device_name, $community, $device);
    get_cpsIfSecureMacAddrAgingTime($device_name, $community, $device);
    get_cpsIfSecureMacAddrAgingType($device_name, $community, $device);
    get_cpsIfStaticMacAddrAgingEnable($device_name, $community, $device);
    get_cpsIfViolationAction($device_name, $community, $device);
    get_cpsIfViolationCount($device_name, $community, $device);
    get_cpsIfSecureLastMacAddress($device_name, $community, $device);
    get_cpsIfStickyEnable($device_name, $community, $device);
}


    /* .1.3.6.1.4.1.9.9.315.1.2.1 cpsIfConfigTable
     *   .1 cpsIfConfigEntry
     *     .1 cpsIfPortSecurityEnable
     *
     * 1 = true (port security enabled on the interface)
     * 2 = false
     *
     * INDEX  { ifIndex }
     *
     * FUNCTION
     * get_cpsIfPortSecurityEnable ($device_name, $community, &$device, 
     *                              $if="")
     *
     * sets $device["interfaces"][$ifIndex]["portSecurity"]["cpsIfPortSecurityEnable"]
     **/

function get_cpsIfPortSecurityEnable ($device_name, 
                                      $community, 
                                      &$device, 
                                      $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.315.1.2.1.1.1";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /*CISCO-PORT-SECURITY-MIB::cpsIfPortSecurityEnable.1 = 
             *   INTEGER: true(1)
             *
             * $matches[0] is ifIndex
             **/

        $device["interfaces"][$matches[0]]["portSecurity"]["cpsIfPortSecurityEnable"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.315.1.2.1 cpsIfConfigTable
     *   .1 cpsIfConfigEntry
     *     .2 cpsIfPortSecurityStatus
     *
     * 1 = secureup (port security is operational)
     * 2 = securedown (port security is not operational)
     * 3 = shutdown (port is shutdown because of port sec. violation)
     *
     * INDEX  { ifIndex }
     *
     * FUNCTION
     * get_cpsIfPortSecurityStatus ($device_name, $community, &$device, 
     *                              $if="")
     *
     * sets $device["interfaces"][$ifIndex]["portSecurity"]["cpsIfPortSecurityStatus"]
     **/

function get_cpsIfPortSecurityStatus ($device_name, 
                                      $community, 
                                      &$device, 
                                      $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.315.1.2.1.1.2";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /*CISCO-PORT-SECURITY-MIB::cpsIfPortSecurityStatus.1 = 
             *   INTEGER: secureup(1)
             *
             * $matches[0] is ifIndex
             **/
        
        $device["interfaces"][$matches[0]]["portSecurity"]["cpsIfPortSecurityStatus"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.315.1.2.1 cpsIfConfigTable
     *   .1 cpsIfConfigEntry
     *     .3 cpsIfMaxSecureMacAddr
     *
     * INDEX  { ifIndex }
     *
     * max number of MAC addresses allowed on interface
     *
     * FUNCTION
     * get_cpsIfMaxSecureMacAddr ($device_name, $community, &$device, 
     *                            $if="")
     *
     * sets $device["interfaces"][$ifIndex]["cpsIfMaxSecureMacAddr"]     
     **/

function get_cpsIfMaxSecureMacAddr ($device_name, 
                                    $community, 
                                    &$device, 
                                    $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid    = ".1.3.6.1.4.1.9.9.315.1.2.1.1.3";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /*CISCO-PORT-SECURITY-MIB::cpsIfMaxSecureMacAddr.1 = 
             *   INTEGER: 1
             *
             * $matches[0] is ifIndex
             */

        $device["interfaces"][$matches[0]]["portSecurity"]["cpsIfMaxSecureMacAddr"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.315.1.2.1 cpsIfConfigTable
     *   .1 cpsIfConfigEntry
     *     .4 cpsIfCurrentSecureMacAddrCount
     *
     * INDEX  { ifIndex }
     *
     * max number of MAC addresses currently on interface
     *
     * FUNCTION
     * get_cpsIfCurrentSecureMacAddrCount ($device_name, $community, &$device, 
     *                                     $if="")
     *
     * sets $device["interfaces"][$ifIndex]["cpsIfCurrentSecureMacAddrCount"]  
     **/

function get_cpsIfCurrentSecureMacAddrCount ($device_name,
                                             $community, 
                                             &$device, 
                                             $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid    = ".1.3.6.1.4.1.9.9.315.1.2.1.1.4";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /*CISCO-PORT-SECURITY-MIB::cpsIfCurrentSecureMacAddrCount.1 = 
             *   INTEGER: 1
             *
             * $matches[0] is ifIndex
             **/

        $device["interfaces"][$matches[0]]["portSecurity"]["cpsIfCurrentSecureMacAddrCount"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.315.1.2.1 cpsIfConfigTable
     *   .1 cpsIfConfigEntry
     *     .5 cpsIfSecureMacAddrAgingTime
     *
     * INDEX  { ifIndex }
     *
     * interval (minutes) in which the MAC is secure.  value is 0
     * (zero) if agining is disabled.
     *
     * FUNCTION
     * get_cpsIfSecureMacAddrAgingTime ($device_name, $community, &$device, 
     *                                  $if="")
     *
     * sets $device["interfaces"][$ifIndex]["cpsIfSecureMacAddrAgingTime"]  
     */

function get_cpsIfSecureMacAddrAgingTime ($device_name, 
                                          $community, 
                                          &$device, 
                                          $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.315.1.2.1.1.5";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /* CISCO-PORT-SECURITY-MIB::cpsIfSecureMacAddrAgingTime.1 = 
             *   INTEGER: 1
             *
             * $matches[0] is ifIndex
             **/

        $val .= ($val === "0")         ? " (aging disabled)" : "" ;
        $val .= ($val === "0 minutes") ? " (aging disabled)" : "" ;

        $device["interfaces"][$matches[0]]["portSecurity"]["cpsIfSecureMacAddrAgingTime"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.315.1.2.1 cpsIfConfigTable
     *   .1 cpsIfConfigEntry
     *     .6 cpsIfSecureMacAddrAgingType
     *
     * 1 == absolute
     * 2 == inactivity
     *
     * INDEX  { ifIndex }
     *
     * indicates the way secure MAC addrs are aged out
     *
     * FUNCTION
     * get_cpsIfSecureMacAddrAgingType ($device_name, $community, &$device, 
     *                                  $if="")
     *
     * sets $device["interfaces"][$ifIndex]["cpsIfSecureMacAddrAgingType"]  
     **/

function get_cpsIfSecureMacAddrAgingType ($device_name, 
                                          $community, 
                                          &$device, 
                                          $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.315.1.2.1.1.6";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /*CISCO-PORT-SECURITY-MIB::cpsIfSecureMacAddrAgingType.1 = 
             *   INTEGER: absolute(1)
             *
             * $matches[0] is ifIndex
             **/
        
        $device["interfaces"][$matches[0]]["portSecurity"]["cpsIfSecureMacAddrAgingType"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.315.1.2.1 cpsIfConfigTable
     *   .1 cpsIfConfigEntry
     *     .7 cpsIfStaticMacAddrAgingEnable
     *
     * 1 == true
     * 2 == false
     *
     * INDEX  { ifIndex }
     *
     * indicates whether secure MAC aging is enabled on static MACs
     *
     * FUNCTION
     * get_cpsIfStaticMacAddrAgingEnable ($device_name, $community, &$device, 
     *                                    $if="")
     *
     * sets $device["interfaces"][$ifIndex]["cpsIfStaticMacAddrAgingEnable"]  
     **/

function get_cpsIfStaticMacAddrAgingEnable ($device_name, 
                                            $community, 
                                            &$device, 
                                            $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.315.1.2.1.1.7";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /*CISCO-PORT-SECURITY-MIB::cpsIfStaticMacAddrAgingEnable
             *   INTEGER: false(2)
             *
             * $matches[0] is ifIndex
             **/
        
        $device["interfaces"][$matches[0]]["portSecurity"]["cpsIfStaticMacAddrAgingEnable"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.315.1.2.1 cpsIfConfigTable
     *   .1 cpsIfConfigEntry
     *     .8 cpsIfViolationAction
     *
     * 1 == shutdown
     * 2 == dropNotify
     * 3 == drop
     *
     * INDEX  { ifIndex }
     *
     * action taken on a violation event
     *
     * FUNCTION
     * get_cpsIfViolationAction ($device_name, $community, &$device, 
     *                           $if="")
     *
     * sets $device["interfaces"][$ifIndex]["cpsIfViolationAction"]  
     **/

function get_cpsIfViolationAction ($device_name, 
                                   $community, 
                                   &$device, 
                                   $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid    = ".1.3.6.1.4.1.9.9.315.1.2.1.1.8";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /*CISCO-PORT-SECURITY-MIB::cpsIfViolationAction
             *   INTEGER: drop(3)
             *
             * $matches[0] is ifIndex
             **/
        
        $device["interfaces"][$matches[0]]["portSecurity"]["cpsIfViolationAction"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.315.1.2.1 cpsIfConfigTable
     *   .1 cpsIfConfigEntry
     *     .9 cpsIfViolationCount
     *
     * INDEX  { ifIndex }
     *
     * value is the number of violation events on the interface
     *
     * FUNCTION
     * get_cpsIfViolationCount ($device_name, $community, &$device, 
     *                           $if="")
     *
     * sets $device["interfaces"][$ifIndex]["cpsIfViolationCount"]  
     **/

function get_cpsIfViolationCount ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid    = ".1.3.6.1.4.1.9.9.315.1.2.1.1.9";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /*CISCO-PORT-SECURITY-MIB::cpsIfViolationCount
             *   Counter32: 0
             *
             * $matches[0] is ifIndex
             **/
        
        $device["interfaces"][$matches[0]]["portSecurity"]["cpsIfViolationCount"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.315.1.2.1 cpsIfConfigTable
     *   .1 cpsIfConfigEntry
     *     .10 cpsIfSecureLastMacAddress
     *
     * INDEX  { ifIndex }
     *
     * value is the last MAC addr learned on the interface
     *
     * FUNCTION
     * get_cpsIfSecureLastMacAddress ($device_name, $community, &$device, 
     *                                $if="")
     *
     * sets $device["interfaces"][$ifIndex]["cpsIfSecureLastMacAddress"]  
     **/

function get_cpsIfSecureLastMacAddress ($device_name, 
                                        $community,
                                        &$device, 
                                        $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid    = ".1.3.6.1.4.1.9.9.315.1.2.1.1.10";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /*CISCO-PORT-SECURITY-MIB::cpsIfSecureLastMacAddress
             *   STRING: 0:c:f1:e2:f8:b3
             *
             * $matches[0] is ifIndex
             *
             * $val is returned formatted as non-zero-padded octets
             * separated by colons.  MACs in all other MIBs are
             * returned zero-padded, regardless of the octet sepators.
             * normalize the MAC so it can be displayed sanely.
             **/

        $octet_list = explode(":", $val);
        
        $mac = "";

        foreach ($octet_list as $octet)
        {
            if (!empty($mac)) {  $mac .= ":";  }
            
            if (strlen($octet) < 2) {  $octet = "0".$octet;  }
            
            $mac .= $octet;
        }
        
        $device["interfaces"][$matches[0]]["portSecurity"]["cpsIfSecureLastMacAddress"] = $mac;
    }
}


    /* .1.3.6.1.4.1.9.9.315.1.2.1 cpsIfConfigTable
     *   .1 cpsIfConfigEntry
     *     .15 cpsIfStickyEnable
     *
     * 1 == true
     * 2 == false
     *
     * INDEX  { ifIndex }
     *
     * indicates whether or not MACs are learned permanently
     *
     * FUNCTION
     * get_cpsIfStickyEnable ($device_name, $community, &$device, 
     *                        $if="")
     *
     * sets $device["interfaces"][$ifIndex]["cpsIfStickyEnable"]  
     */

function get_cpsIfStickyEnable ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid    = ".1.3.6.1.4.1.9.9.315.1.2.1.1.15";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /*CISCO-PORT-SECURITY-MIB::cpsIfStickyEnable
             *   INTEGER: false(2)
             *
             * $matches[0] is ifIndex
             **/
        
        $device["interfaces"][$matches[0]]["portSecurity"]["cpsIfStickyEnable"] = $val;
    }
}



    /* .1.3.6.1.4.1.9.9.315.1.2.3 cpsIfVlanSecureMacAddrTable
     *   .1 cpsIfVlanSecureMacAddrEntry
     *     .1 cpsIfVlanSecureMacAddress : not-accessible
     *     .2 cpsIfVlanSecureVlanIndex : not-accessible
     *     .3 cpsIfVlanSecureMacAddrType
     *     .4 cpsIfVlanSecureMacAddrRemainAge
     *     .5 cpsIfVlanSecureMacAddrRowStatus : n/a
     *
     * INDEX  { ifIndex, MAC, vlanIndex }
     *
     * this table is used to configure/report secure MAC data on
     * access or trunking ports.  includes vlan info.
     *
     * FUNCTION
     * get_cpsIfVlanSecureMacAddrTable ($device_name, $community, &$device, 
     *                                  $if="")
     *
     * calls get_cpsIfVlanSecureMacAddrType(),
     * get_cpsIfVlanSecureMacAddrRemainAge()
     **/

function get_cpsIfVlanSecureMacAddrTable ($device_name, 
                                          $community, 
                                          &$device, 
                                          $if="")
{
    get_cpsIfVlanSecureMacAddrType($device_name, 
                                   $community, 
                                   $device, 
                                   $if="");

    get_cpsIfVlanSecureMacAddrRemainAge($device_name, 
                                        $community, 
                                        $device, 
                                        $if="");
}


    /* .1.3.6.1.4.1.9.9.315.1.2.3 cpsIfVlanSecureMacAddrTable
     *   .1 cpsIfVlanSecureMacAddrEntry
     *     .3 cpsIfVlanSecureMacAddrType
     *
     * 1 == static
     * 2 == dynamic
     * 3 == sticky
     *
     * INDEX  { ifIndex, MAC, vlanIndex }
     * 
     * indicates how the secure MAC is configured
     * 
     * FUNCTION
     * get_cpsIfVlanSecureMacAddrType ($device_name, $community, &$device, 
     *                                 $if="")
     *
     * sets $device["interfaces"][$ifIndex]["portSecurity"][$mac][$vlan]["cpsIfVlanSecureMacAddrType"]
     **/

function get_cpsIfVlanSecureMacAddrType ($device_name, 
                                         $community, 
                                         &$device, 
                                         $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.315.1.2.3.1.3";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);

    if (empty($data))  {  return;  }


    foreach ($data as $key=>$val)
    {
        preg_match('/([0-9]+)((?:\.[0-9]+){6})\.([0-9]+)$/i', $key, $matches);

            /* CISCO-PORT-SECURITY-MIB::
             *   cpsIfVlanSecureMacAddrType.1.0.12.241.226.248.179.133 = 
             *     INTEGER: dynamic(2)
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 1.0.12.241.226.248.179.133
             *     [1] => 1
             *     [2] => .0.12.241.226.248.179
             *     [3] => 133
             * )
             *
             * $matches[1] is ifIndex.  
             * $matches[2] is a mac address (octets in decimal, not hex, 
             *    with a leading '.').
             * $matches[3] is the vlan.  
             *
             * substr_replace($matches[2], "", 0, 1) strips the
             * leading '.' off $matches[2] to leave just the
             * decimal-format MAC address used as the table index.
             * convert to a hex-format MAC for use as an index in
             * $device["interfaces"].
             *
             * normalize MAC to zero-padded, colon-separated octets.
             **/
        
        $idx = substr_replace($matches[2], "", 0, 1);

        $octet_list = explode(".", $idx);
        
        $mac = "";

        foreach ($octet_list as $octet)
        {
            $string = dechex($octet);
            if (strlen($string) < 2) {  $string = "0".$string;  }
            
            if (!empty($mac)) {  $mac .= ":";  }

            $mac .= $string;
        }

        $device["interfaces"][$matches[1]]["portSecurity"][$mac][$matches[3]]["cpsIfVlanSecureMacAddrType"] = $val;
    }
}


    /* .1.3.6.1.4.1.9.9.315.1.2.3 cpsIfVlanSecureMacAddrTable
     *   .1 cpsIfVlanSecureMacAddrEntry
     *     .4 cpsIfVlanSecureMacAddrRemainAge
     *
     * INDEX  { ifIndex, MAC, vlanIndex }
     *
     * value indicates remaining age, if aging is enabled. 0 (zero)
     * indicates aging is disabled.
     * 
     * FUNCTION
     * get_cpsIfVlanSecureMacAddrType ($device_name, $community, &$device, 
     *                                 $if="")
     *
     * sets $device["interfaces"][$ifIndex]["portSecurity"][$mac][$vlan]["cpsIfVlanSecureMacAddrRemainAge"]
     **/

function get_cpsIfVlanSecureMacAddrRemainAge ($device_name, 
                                              $community, 
                                              &$device, 
                                              $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.4.1.9.9.315.1.2.3.1.4";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$val)
    {
        preg_match('/([0-9]+)((?:\.[0-9]+){6})\.([0-9]+)$/i', $key, $matches);
        
            /* CISCO-PORT-SECURITY-MIB::
             *   cpsIfVlanSecureMacAddrRemainAge.1.0.12.241.226.248.179.133 = 
             *     Gauge32: 0 minutes
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 1.0.12.241.226.248.179.133
             *     [1] => 1
             *     [2] => .0.12.241.226.248.179
             *     [3] => 133
             * )
             *
             * $matches[1] is ifIndex.  
             * $matches[2] is a mac address (octets in decimal, not hex, 
             *    with a leading '.').
             * $matches[3] is the vlan.  
             *
             * substr_replace($matches[2], "", 0, 1) strips the
             * leading '.' off $matches[2] to leave just the
             * decimal-format MAC address used as the table index.
             * convert to a hex-format MAC for use as an index in
             * $device["interfaces"].
             *
             * normalize MAC to zero-padded, colon-separated octets.
             **/
        
        $idx = substr_replace($matches[2], "", 0, 1);

        $octet_list = explode(".", $idx);
        
        $mac = "";

        foreach ($octet_list as $octet)
        {
            $string = dechex($octet);
            if (strlen($string) < 2) {  $string = "0".$string;  }
            
            if (!empty($mac)) {  $mac .= ":";  }

            $mac .= $string;
        }
        
        $val .= ($val === "0")         ? " (aging disabled)" : "" ;
        $val .= ($val === "0 minutes") ? " (aging disabled)" : "" ;
        
        $device["interfaces"][$matches[1]]["portSecurity"][$mac][$vlan]["cpsIfVlanSecureMacAddrRemainAge"] = $val;

    }
}


?>
