<?php  /* -*- c -*- */

  /*
   * Copyright (c) 2014 Robin Garner (robin@nukefest.org)
   * All rights reserved.
   *
   * License: GPL v.3
   */

  /* v.1.1  2014  Minor edits to comments, added empty declarations 
   *              for objects that aren't presently implemented.
   * v.1.0  2004  Implements RFC2863 
   */

  /* 1.3.6.1.2.1 mib-2
   *   .2  interfaces
   *   .31 ifMIB
   *
   * HISTORY
   * 
   * RFC1229/1239 -> 1573 -> 2233 -> 2863
   *
   * RFC 2863 addressed the need to be able to distinguish between
   * different layers of the network stack (physical, link, net, etc.)
   * and also the ability to multiplex interfaces and layers.
   */


  /* 1.3.6.1.2.1 mib-2
   *   .2 interfaces
   *     .1 ifNumber 
   *     .2 ifTable  
   *
   * FUNCTION
   * get_interfaces ($device_name, $community, &$device)
   *
   * Calls get_ifNumber(), get_ifTable()
   **/

function get_interfaces ($device_name, $community, &$device) 
{
    get_ifNumber($device_name, $community, $device);
    get_ifTable($device_name, $community, $device);
}


    /* .1.3.6.1.2.1.2 interfaces
     *   .1 ifNumber 
     *
     * FUNCTION
     * get_ifNumber ($device_name, $community, &$device)
     *
     * Sets $device["interfaces"]["ifNumber"]
     **/

function get_ifNumber ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.1.0";
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    $device["interfaces"]["ifNumber"] = $data;
}


    /* .1.3.6.1.2.1.2 interfaces
     *   .2 ifTable
     *     .1 ifEntry
     *       .1  ifIndex
     *       .2  ifDescr
     *       .3  ifType 
     *       .4  ifMtu  
     *       .5  ifSpeed
     *       .6  ifPhysAddress 
     *       .7  ifAdminStatus 
     *       .8  ifOperStatus  
     *       .9  ifLastChange  
     *       .10 ifInOctets    
     *       .11 ifInUcastPkts 
     *       .12 ifInNUcastPkts (deprecated)
     *       .13 ifInDiscards  
     *       .14 ifInErrors    
     *       .15 ifInUnknownProtos
     *       .16 ifOutOctets      
     *       .17 ifOutUcastPkts   
     *       .18 ifOutNUcastPkts (deprecated)
     *       .19 ifOutDiscards    
     *       .20 ifOutErrors      
     *       .21 ifOutQLen (deprecated)
     *       .22 ifSpecific (deprecated)
     * 
     * INDEX   { ifIndex }
     *  
     * FUNCTION
     * get_ifTable ($device_name, $community, &$device)
     *
     * Populates $device["interfaces"][$ifIndex]["ifTable"]
     **/

function get_ifTable ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.2.2";
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
        
            /* IF-MIB::ifDescr.9 == GigabitEthernet1/9
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => ifDescr.9
             *     [1] => ifDescr
             *     [2] => 9
             * )
             *
             * $matches[2] is the ifIndex, $matches[1] is the oid
             */
        
        $device["interfaces"][$matches[2]]["ifTable"][$matches[1]] = 
            $value;
    }
}


    /* .1.3.6.1.2.1.2.2 ifTable
     *   .1 ifEntry
     *     .2  ifDescr
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifDescr ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifDescr"]
     **/

function get_ifDescr ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.2";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifDescr.329 = STRING: FastEthernet6/9
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 329
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifDescr"] = $value;
    }
}
    

    /* 1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .3 ifType
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifType ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifType"]
     *
     * Values for ifType are assigned by IANA; IANAifType-MIB 
     * http://www.iana.org/assignments/ianaiftype-mib/ianaiftype-mib
     **/

function get_ifType ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.2.2.1.3";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifType.15 = INTEGER: ethernetCsmacd(6)
             * IF-MIB::ifType.51 = INTEGER: other(1)
             * IF-MIB::ifType.52 = INTEGER: propVirtual(53)
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 52
             * )
             *
             * $matches[0] is the ifIndex
             **/

        $device["interfaces"][$matches[0]]["ifTable"]["ifType"] = $value;
    }
}
    

    /* 1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .4 ifMtu
     *
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifMtu ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifMtu"]
     **/

function get_ifMtu ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.4";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifMtu.14 = INTEGER: 1500
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 14
             * )
             *
             * $matches[0] is the ifIndex
             **/
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifMtu"] = $value;
    }
}
    

    /* 1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .5 ifSpeed
     *
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifSpeed ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifSpeed"]
     **/

function get_ifSpeed ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.5";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifSpeed.38 = Gauge32: 100000000
             * IF-MIB::ifSpeed.39 = Gauge32: 10000000
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 39
             * )
             *
             * $matches[0] is the ifIndex
             **/
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifSpeed"] = $value;
    }
}
     

    /* 1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .6 ifPhysAddress
     *
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifPhysAddress ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifPhysAddress"]
     **/

function get_ifPhysAddress ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.2.2.1.6";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifPhysAddress.13 = STRING: 0:9:b7:d2:52:11
             * IF-MIB::ifPhysAddress.14 = STRING: 0:9:b7:d2:52:12
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 14
             * )
             *
             * $matches[0] is the ifIndex
             **/
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifPhysAddress"] = 
            $value;
    }
}
      

    /* 1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .7 ifAdminStatus
     *
     * 1 == up
     * 2 == down
     * 3 == testing
     *
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifAdminStatus ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifAdminStatus"]
     **/

function get_ifAdminStatus ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.2.2.1.7";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifAdminStatus.14 = INTEGER: up(1)
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 14
             * )
             *
             * $matches[0] is the ifIndex
             **/
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifAdminStatus"] = 
            $value;
    }
}
      

    /* 1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .8 ifOperStatus
     *
     * 1 == up
     * 2 == down
     * 3 == testing
     * 4 == unknown
     * 5 == dormant
     * 6 == notPresent
     * 7 == lowerLayerDown
     *
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifOperStatus ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifOperStatus"]
     **/

function get_ifOperStatus ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.2.2.1.8";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifOperStatus.16 = INTEGER: up(1)
             * IF-MIB::ifOperStatus.17 = INTEGER: down(2)
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 17
             * )
             *
             * $matches[0] is the ifIndex
             **/
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifOperStatus"] = 
            $value;
    }
}


    /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .10 ifInOctets
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifInOctets ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifInOctets"]
     **/

function get_ifInOctets ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.10";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifInOctets.183 = Counter32: 3319179067
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 183
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifInOctets"] = $value;
    }
}


    /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .11 ifInUcastPkts
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifInUcastPkts ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifInUcastPkts"]
     **/

function get_ifInUcastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.11";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifInUcastPkts.171 = Counter32: 5782237
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 171
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifInUcastPkts"] = $value;
    }
}


    /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .12 ifInNUcastPkts (deprecated)
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifInNUcastPkts ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifInNUcastPkts"]
     **/

function get_ifInNUcastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.12";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifInNUcastPkts.2 = Counter32: 0
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 2
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifInNUcastPkts"] = 
            $value;
    }
}


   /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .13 ifInDiscards
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifInDiscards ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifInDiscards"]
     **/

function get_ifInDiscards ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.13";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifInDiscards.123 = Counter32: 141786
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 123
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifInDiscards"] = $value;
    }
}


    /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .14 ifInErrors
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifInErrors ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifInErrors"]
     **/

function get_ifInErrors ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.14";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifInErrors.98 = Counter32: 80
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 98
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifInErrors"] = $value;
    }
}


    /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .15 ifInUnknownProtos
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifInUnknownProtos ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifInUnknownProtos"]
     **/

function get_ifInUnknownProtos ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.15";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifInUnknownProtos.118 = Counter32: 9984693
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 118
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifInUnknownProtos"] = 
            $value;
    }
}


    /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .16 ifOutOctets
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifOutOctets ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifOutOctets"]
     **/

function get_ifOutOctets ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.16";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifOutOctets.175 = Counter32: 2927205176
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 175
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifOutOctets"] = $value;
    }
}


    /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .17 ifOutUcastPkts
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifOutUcastPkts ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifOutUcastPkts"]
     **/

function get_ifOutUcastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.17";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifOutUcastPkts.180 = Counter32: 2876521
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 180
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifOutUcastPkts"] =
            $value;
    }
}


    /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .18 ifOutNUcastPkts (deprecated)
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifOutNUcastPkts ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifOutUcastPkts"]
     **/

function get_ifOutNUcastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.18";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifOutNUcastPkts.1 = Counter32: 0
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 1
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifOutNUcastPkts"] =
            $value;
    }
}


    /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .19 ifOutDiscards
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifOutDiscards ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifOutDiscards"]
     **/

function get_ifOutDiscards ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.19";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifOutDiscards.111 = Counter32: 11789
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 111
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifOutDiscards"] = $value;
    }
}


    /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .20 ifOutErrors
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifOutErrors ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifOutErrors"]
     **/

function get_ifOutErrors ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.20";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifOutErrors.78 = Counter32: 729
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 78
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifOutErrors"] = $value;
    }
}
 

    /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .21 ifOutQLen (deprecated)
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifOutQLen ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifOutQLen"]
     **/

function get_ifOutQLen ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.21";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifOutQLen.1 = Gauge32: 0
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 1
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifOutQLen"] = $value;
    }
}
 


    /* .1.3.6.1.2.1.2.2 ifTable:
     *   .1 ifEntry
     *     .22 ifSpecific (deprecated)
     * 
     * INDEX   { ifIndex }
     *
     * FUNCTION
     * get_ifSpecific ($device_name, $community, &$device, $if="")
     *
     * Sets $device["interfaces"][$ifIndex]["ifTable"]["ifSpecific"]
     **/

function get_ifSpecific ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.2.2.1.22";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifSpecific.1 = OID: SNMPv2-SMI::zeroDotZero
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 1
             * )
             *
             * $matches[0] is the ifIndex
             */
        
        $device["interfaces"][$matches[0]]["ifTable"]["ifSpecific"] = $value;
    }
}

    /* .1.3.6.1.2.1 mib-2
     *   .31 : ifMIB
     *     .1 ifMIBObjects
     *     .2 ifConformance : n/i
     *
     * FUNCTION
     * array get_ifMIB ($device_name, $community, &$device)
     * 
     * calls get_ifMIBObjects()
     */

function get_ifMIB ($device_name, $community, &$device)
{
    get_ifMIBObjects($device_name, $community, $device);
}


    /* .1.3.6.1.2.1 mib-2
     *   .31 : ifMIB
     *     .1 ifMIBObjects
     *       .1 ifXTable 
     *       .2 ifStackTable 
     *       .3 ifTestTable (deprecated)
     *       .4 ifRcvAddressTable : n/i
     *       .5 ifTableLastChange : n/i
     *       .6 ifStackLastChange : n/i
     *
     * FUNCTION
     * array get_ifMIBObjects ($device_name, $community, &$device)
     * 
     * calls get_ifXTable(), get_ifStackTable().
     */

function get_ifMIBObjects ($device_name, $community, &$device) 
{
    get_ifXTable($device_name, $community, $device);
    get_ifStackTable ($device_name, $community, $device);
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable
     *   .1 ifXEntry
     *     .1 ifName
     *     .2 ifInMulticastPkts
     *     .3 ifInBroadcastPkts
     *     .4 ifOutMulticastPkts
     *     .5 ifOutBroadcastPkts
     *     .6 ifHCInOctets
     *     .7 ifHCInUcastPkts
     *     .8 ifHCInMulticastPkts
     *     .9 ifHCInBroadcastPkts
     *     .10 ifHCOutOctets
     *     .11 ifHCOutUcastPkts
     *     .12 ifHCOutMulticastPkts
     *     .13 ifHCOutBroadcastPkts
     *     .14 ifLinkUpDownTrapEnable
     *     .15 ifHighSpeed
     *     .16 ifPromiscuousMode
     *     .17 ifConnectorPresent
     *     .18 ifAlias
     *     .19 ifCounterDiscontinuityTime
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     *
     * FUNCTION
     * array get_ifXTable ($device_name, $community, &$device) 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"][$oid]
     **/

function get_ifXTable ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1";
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
        
            /* Structure of $matches:
             * Array
             * (
             *     [0] => ifName.2
             *     [1] => ifName
             *     [2] => 2
             * )
             *
             * $matches[2] is the ifIndex, $matches[1] is the oid
             */

        $device["interfaces"][$matches[2]]["ifXTable"][$matches[1]] = 
            $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .1 ifName
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifName ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifName"]
     **/

function get_ifName ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.1";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifName.463 = STRING: ethernet8/15
             * Structure of $matches:
             * Array
             * (
             *     [0] => 463
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifName"] = $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .2 ifInMulticastPkts
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifInMulticastPkts ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifInMulticastPkts"]
     **/

function get_ifInMulticastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.2";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifInMulticastPkts.104 = Counter32: 158808451 
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 104
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifInMulticastPkts"] = 
            $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .3 ifInBroadcastPkts
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifInBroadcastPkts ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifInBroadcastPkts"]
     **/

function get_ifInBroadcastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.3";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifInBroadcastPkts.104 = Counter32: 96634
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 104
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifInBroadcastPkts"] = 
            $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .4 ifOutMulticastPkts
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifOutMulticastPkts ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifOutMulticastPkts"]
     **/

function get_ifOutMulticastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.4";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifOutMulticastPkts.78 = Counter32: 4032217
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 78
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifOutMulticastPkts"] = 
            $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .5 ifOutBroadcastPkts
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifOutBroadcastPkts ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifOutBroadcastPkts"]
     **/

function get_ifOutBroadcastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.5";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifOutBroadcastPkts.105 = Counter32: 359895316
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 105
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifOutBroadcastPkts"] =
            $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .6 ifHCInOctets
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifHCInOctets ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifHCInOctets"]
     **/

function get_ifHCInOctets ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.6";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifHCInOctets.176 = Counter64: 5297790485740
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 176
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifHCInOctets"] = 
            $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .7 ifHCInUcastPkts
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifHCInUcastPkts ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifHCInUcastPkts"]
     **/

function get_ifHCInUcastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.7";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifHCInUcastPkts.183 = Counter64: 283444062
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 183
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifHCInUcastPkts"] = 
            $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .8 ifHCInMulticastPkts
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifHCInMulticastPkts ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifHCInMulticastPkts"]
     **/

function get_ifHCInMulticastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.8";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifHCInMulticastPkts.70 = Counter64: 372438
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 70
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifHCInMulticastPkts"] =
            $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .9 ifHCInBroadcastPkts
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifHCInBroadcastPkts ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifHCInBroadcastPkts"]
     **/

function get_ifHCInBroadcastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.9";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifHCInBroadcastPkts.97 = Counter64: 7687
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] =>97 
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifHCInBroadcastPkts"] =
            $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .10 ifHCOutOctets
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifHCOutOctets ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifHCOutOctets"]
     **/

function get_ifHCOutOctets ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.10";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifHCOutOctets.170 = Counter64: 4939040
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 170
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifHCOutOctets"] = 
            $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .11 ifHCOutUcastPkts
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifHCOutUcastPkts ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifHCOutUcastPkts"]
     **/

function get_ifHCOutUcastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.11";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifHCOutUcastPkts.181 = Counter64: 1355997
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 181
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifHCOutUcastPkts"] = 
            $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .12 ifHCOutMulticastPkts
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifHCOutMulticastPkts ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifHCOutMulticastPkts"]
     **/

function get_ifHCOutMulticastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.12";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifHCOutMulticastPkts.96 = Counter64: 3619676
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 96
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifHCOutMulticastPkts"]
            = $value;
    }
}


    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .13 ifHCOutBroadcastPkts
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * get_ifHCOutBroadcastPkts ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifHCOutBroadcastPkts"]
     **/

function get_ifHCOutBroadcastPkts ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.13";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifHCOutBroadcastPkts.104 = Counter64: 5
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 104
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifHCOutBroadcastPkts"] 
            = $value;
    }
}

    
    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .14 ifLinkUpDownTrapEnable
     * 
     * 1 == enabled
     * 2 == disabled
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     *
     * "By default, this object should have the value enabled(1) for
     * interfaces which do not operate on 'top' of any other interface
     * (as defined in the ifStackTable), and disabled(2) otherwise."
     *
     * FUNCTION
     * get_ifLinkUpDownTrapEnable ($device_name, $community, &$device, $if="") 
     *
     * Sets 
     * $device["interfaces"][$ifIndex]["ifXTable"]["ifLinkUpDownTrapEnable"]
     **/

function get_ifLinkUpDownTrapEnable ($device_name, 
                                     $community, 
                                     &$device, 
                                     $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.14";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifLinkUpDownTrapEnable.23 = INTEGER: enabled(1)
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 24
             * )
             *
             * $matches[0] is the IfIndex
             */

            /* for some reason, the value of this object isn't be
             * translated.  translate it here.
             */

        $value = ($value === "1") ? "enabled"  : $value;
        $value = ($value === "2") ? "disabled" : $value;

        $device["interfaces"][$matches[0]]["ifXTable"]["ifLinkUpDownTrapEnable"] = $value;
    }
}

    
    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .15 ifHighSpeed
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * array get_ifHighSpeed ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifHighSpeed"]
     **/

function get_ifHighSpeed ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.15";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifHighSpeed.23 = Gauge32: 100
             * IF-MIB::ifHighSpeed.24 = Gauge32: 10
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 24
             * )
             *
             * $matches[0] is the IfIndex
             */

        $device["interfaces"][$matches[0]]["ifXTable"]["ifHighSpeed"] =
            $value;
    }
}

    
    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .16 ifPromiscuousMode
     * 
     * 1 == true
     * 2 == false
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     *
     * FUNCTION
     * array get_ifPromiscuousMode ($device_name, $community, &$device, 
     *                              $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifPromiscuousMode"]
     **/

function get_ifPromiscuousMode ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.16";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifPromiscuousMode.24 = INTEGER: false(2)
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 24
             * )
             *
             * $matches[0] is the IfIndex
             */
        
        $device["interfaces"][$matches[0]]["ifXTable"]["ifPromiscuousMode"] =
            $value;
    }
}

    
    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .17 ifConnectorPresent
     * 
     * 1 == true
     * 2 == false
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * array get_ifConnectorPresent ($device_name, $community, &$device, 
     *                               $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifConnectorPresent"]
     **/

function get_ifConnectorPresent ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.17";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifConnectorPresent.50 = INTEGER: true(1)
             * IF-MIB::ifConnectorPresent.51 = INTEGER: false(2)
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 51
             * )
             *
             * $matches[0] is the IfIndex
             */
        
        $device["interfaces"][$matches[0]]["ifXTable"]["ifConnectorPresent"] =
            $value;
    }
}

    
    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .18 ifAlias
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * array get_ifAlias ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifAlias"]
     **/

function get_ifAlias ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.18";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifAlias.329 = STRING: Police DSX
             * Structure of $matches:
             * Array
             * (
             *     [0] => 329
             * )
             *
             * $matches[0] is the IfIndex
             */
        
        $device["interfaces"][$matches[0]]["ifXTable"]["ifAlias"] = $value;
    }
}
    

    /* .1.3.6.1.2.1.31.1.1 ifXTable 
     *   .1 ifXEntry
     *     .19 ifCounterDiscontinuityTime
     *
     * INDEX    { ifIndex }
     * AUGMENTS { ifEntry }
     * 
     * FUNCTION
     * array get_ifCounterDiscontinuityTime ($device_name, $community, &$device, $if="") 
     *
     * Sets $device["interfaces"][$ifIndex]["ifXTable"]["ifCounterDiscontinuityTime"]
     **/

function get_ifCounterDiscontinuityTime ($device_name, $community, &$device, $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.31.1.1.1.19";
    $oid .= (!empty($if) && is_numeric($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* IF-MIB::ifCounterDiscontinuityTime.1 = Timeticks: (0) 0:00:00.00
             * Structure of $matches:
             * Array
             * (
             *     [0] => 1
             * )
             *
             * $matches[0] is the IfIndex
             */
        
        $device["interfaces"][$matches[0]]["ifXTable"]["ifCounterDiscontinuityTime"] = $value;
    }
}
    


    /* .1.3.6.1.2.1.31.1.2 ifStackTable
     *   .1 ifStackEntry
     *     .1 ifStackHigherLayer : n/a
     *     .2 ifStackLowerLayer  : n/a
     *     .3 ifStackStatus
     *
     * INDEX { ifStackHigherLayer, ifStackLowerLayer }
     *
     *  ifStackStatus:
     *
     * 1 == active
     * 2 == notInService (? mib is vague)
     *
     * FUNCTION
     * array get_ifStackTable ($device_name, $community, &$device) 
     *
     * Sets $device["interfaces"][$ifIndex]["ifStackStatus"]["above"][]
     * and $device["interfaces"][$ifIndex]["ifStackStatus"]["below"][]
     **/

function get_ifStackTable ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.31.1.2";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/([0-9]+)\.([0-9]+)$/i', $key, $matches);

            /* IF-MIB::ifStackStatus.0.8 == active(1)
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => 0.8
             *     [1] => 0
             *     [2] => 8
             * )
             *
             * $matches[1] is the ifIndex that runs "on top of",
             * $matches[2] is the ifIndex "below" it.  A value of "0"
             * means "none": in the example above, there are no other
             * 'if' "layers" running on top of ifIndex 8.  There is an
             * inverse entry as well - 8.0 - that indicates that 'if' 8
             * isn't running "on top of" any other 'if'.
             *
             * By contrast, in the example below, 152 is a logical
             * interface that runs "on top of" interfaces 49 and 50.
             * 49 and 50 have no entry of the form "0.49" because an
             * entry like that would be asserting that "there are no
             * interfaces running on top of 49".  Likewise, there is
             * no "152.0" entry - there is a "152.49" and "152.50"
             * indicating 152 runs "on top of" those two.  49 and 50
             * run "on top of" nothing.
             *
             * IF-MIB::ifStackStatus.0.47 = INTEGER: active(1)
             * IF-MIB::ifStackStatus.0.48 = INTEGER: active(1)
             * IF-MIB::ifStackStatus.0.51 = INTEGER: active(1)
             * IF-MIB::ifStackStatus.0.52 = INTEGER: active(1)
             * IF-MIB::ifStackStatus.48.0 = INTEGER: active(1)
             * IF-MIB::ifStackStatus.49.0 = INTEGER: active(1)
             * IF-MIB::ifStackStatus.50.0 = INTEGER: active(1)
             * IF-MIB::ifStackStatus.51.0 = INTEGER: active(1)
             * IF-MIB::ifStackStatus.52.0 = INTEGER: active(1)
             * IF-MIB::ifStackStatus.151.0 = INTEGER: active(1)
             * IF-MIB::ifStackStatus.152.49 = INTEGER: active(1)
             * IF-MIB::ifStackStatus.152.50 = INTEGER: active(1)
             * IF-MIB::ifStackStatus.153.0 = INTEGER: active(1)
             *
             * Instead of testing for numeric status, test for TRUE.
             * 0's are uninteresting and will eval to FALSE.
             */
        
        if (!$matches[1] || !$matches[2])  {  continue;  }

        $above_ifDescr = 
            (isset($device["interfaces"][$matches[1]]["ifTable"]["ifDescr"])) ? 
            " (".$device["interfaces"][$matches[1]]["ifTable"]["ifDescr"].")"
            :
            "" ;
 
        $below_ifDescr = 
            (isset($device["interfaces"][$matches[2]]["ifTable"]["ifDescr"])) ? 
            " (".$device["interfaces"][$matches[2]]["ifTable"]["ifDescr"].")"
             : "" ;
       
        $device["interfaces"][$matches[1]]["ifStackStatus"]["above"][] = 
            $matches[2].$below_ifDescr;

        $device["interfaces"][$matches[2]]["ifStackStatus"]["below"][] = 
            $matches[1].$above_ifDescr;
    }
}


    /* .1.3.6.1.2.1 mib-2
     *   .31 : ifMIB
     *     .1 ifMIBObjects
     *       .4 ifRcvAddressTable
     *
     * STATUS: unable to implement; no access to participating agent
     **/

function get_ifRcvAddressTable ($device_name,
                                 $community,
                                 &$device)
{
    return;

    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.31.1.4";

    $data = @snmprealwalk ($device_name, $community, $oid);
}


    /* .1.3.6.1.2.1 mib-2
     *   .31 : ifMIB
     *     .1 ifMIBObjects
     *       .5 ifTableLastChange
     *
     * STATUS: unable to implement; no access to participating agent
     **/

function get_ifTableLastChange ($device_name,
                                 $community,
                                 &$device)
{
    return;

    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.31.1.5";

    $data = @snmprealwalk ($device_name, $community, $oid);
}


    /* .1.3.6.1.2.1 mib-2
     *   .31 : ifMIB
     *     .1 ifMIBObjects
     *       .6 ifStackLastChange 
     *
     * STATUS: unable to implement; no access to participating agent
     **/

function get_ifStackLastChange ($device_name,
                                 $community,
                                 &$device)
{
    return;

    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.31.1.6";

    $data = @snmprealwalk ($device_name, $community, $oid);
}


    /* .1.3.6.1.2.1 mib-2
     *   .31 : ifMIB
     *     .2 ifConformance
     *       .1 ifGroups
     *         .1  ifGeneralGroup (deprecated)
     *         .2  ifFixedLengthGroup
     *         .3  ifHCFixedLengthGroup
     *         .4  ifPacketGroup
     *         .5  ifHCPacketGroup
     *         .6  ifVHCPacketGroup
     *         .7  ifRcvAddressGroup
     *         .8  ifTestGroup (deprecated)
     *         .9  ifStackGroup (deprecated)
     *         .10 ifGeneralInformationGroup
     *         .11 ifStackGroup2
     *         .12 ifOldObjectsGroup (deprecated)
     *         .13 ifCounterDiscontinuityGroup
     *         .14 linkUpDownNotificationsGroup
     *       .2 ifCompliances
     *         .1 ifCompliance (deprecated)
     *         .2 ifCompliance2 (deprecated)
     *         .3 ifCompliance3
     *
     * STATUS: unable to implement; no access to participating agent
     **/

function get_ifConformance ($device_name,
                                 $community,
                                 &$device)
{
    return;

    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.31.2";

    $data = @snmprealwalk ($device_name, $community, $oid);
}


    /* .1.3.6.1.2.1 mib-2
     *   .31 : ifMIB
     *     .3 ifTestTable (deprecated)
     *       .1 ifTestEntry
     *         .1 ifTestId
     *         .2 ifTestStatus
     *         .3 ifTestType
     *         .4 ifTestResult
     *         .5 ifTestCode
     *         .6 ifTestOwner
     *
     * STATUS: unable to implement; no access to participating agent
     */

function get_ifTestTable ($device_name,
                                 $community,
                                 &$device)
{
    return;

    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.31.3";

    $data = @snmprealwalk ($device_name, $community, $oid);
}

?>
