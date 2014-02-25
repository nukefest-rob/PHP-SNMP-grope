<?php  /* -*- c -*- */

  /* v.1.0  2004  Implements RFC 2011
   */

    /** mib-2.IP **/

    /* .1.3.6.1.2.1 mib-2
     *   .4 ip
     **/


    /* .1.3.6.1.2.1 mib-2
     *   .4 ip
     *     .1  ipForwarding : is this device an IP router ?
     *     .2  ipDefaultTTL
     *     .3  ipInReceives
     *     .4  ipInHdrErrors
     *     .5  ipInAddrErrors
     *     .6  ipForwDatagrams
     *     .7  ipInUnknownProtos
     *     .8  ipInDiscards
     *     .9  ipInDelivers
     *     .10 ipOutRequests
     *     .11 ipOutDiscards
     *     .12 ipOutNoRoutes
     *     .13 ipReasmTimeout
     *     .14 ipReasmReqds
     *     .15 ipReasmOKs
     *     .16 ipReasmFails
     *     .17 ipFragOKs
     *     .18 ipFragFails
     *     .19 ipFragCreates
     *     .20 ipAddrTable : implemented as get_ipAddrTable()
     *     .21 ipRouteTable : obsolete
     *     .22 ipNetToMediaTable : implemented as get_ipNetToMediaTable()
     *     .23 ipRoutingDiscards
     *
     * FUNCTION
     * get_ip ($device_name, $community, &$device)
     *
     * Retrieves objects 1 through 19 and 23 (scalars), calls
     * get_ipAddrTable() and get_ipNetToMediaTable() (tables).
     *
     * Populates $device["ip"]
     **/

function get_ip ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

        /* Retrieve .ip scalars */

    $base_oid = "1.3.6.1.2.1.4";

    $ip_objects = array(
        1=>"ipForwarding",
        2=>"ipDefaultTTL",
        3=>"ipInReceives",
        4=>"ipInHdrErrors",
        5=>"ipInAddrErrors",
        6=>"ipForwDatagrams",
        7=>"ipInUnknownProtos",
        8=>"ipInDiscards",
        9=>"ipInDelivers",
        10=>"ipOutRequests",
        11=>"ipOutDiscards",
        12=>"ipOutNoRoutes",
        13=>"ipReasmTimeout",
        14=>"ipReasmReqds",
        15=>"ipReasmOKs",
        16=>"ipReasmFails",
        17=>"ipFragOKs",
        18=>"ipFragFails",
        19=>"ipFragCreates",
        23=>"ipRoutingDiscards");

    foreach ($ip_objects as $i=>$object)
    {
        $oid  = $base_oid.".$i.0";
        $data = @snmpget ($device_name, $community, $oid);

        if (empty($data)) {  continue;  }
        
        $device["ip"][$object] = $data;
    }

        /* Retrieve .ip tables */

    get_ipAddrTable($device_name, $community, $device);
    get_ipNetToMediaTable($device_name, $community, $device);
}


    /* .1.3.6.1.2.1.4 ip
     *   .20 ipAddrTable
     *     .1 ipAddrEntry
     *       .1 ipAdEntAddr
     *       .2 ipAdEntIfIndex
     *       .3 ipAdEntNetMask
     *       .4 ipAdEntBcastAddr
     *       .5 ipAdEntReasmMaxSize
     *
     * INDEX      { ipAdEntAddr }
     *
     * FUNCTION
     * get_ipAddrTable ($device_name, $community, &$device, $ip="")
     *
     * Populates $device["ip"]["ipAddrTable"],
     * 
     * Adds pointer from $device["interfaces"][$value]["ipAddrTable"][] =>
     *     &$device["ip"]["ipAddrTable"][$IP];
     **/

function get_ipAddrTable ($device_name, $community, &$device, $ip="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.4.20";
    $oid .= (!empty($ip)) ? ".$ip" : "";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }

    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)((\.[0-9]+){4})$/i', $key, $matches);
        
            /* RFC1213-MIB::ipAdEntAddr.172.20.128.139 = 
             *   IpAddress: 172.20.128.139
             * RFC1213-MIB::ipAdEntIfIndex.172.20.128.139 = INTEGER: 52
             * RFC1213-MIB::ipAdEntNetMask.172.20.128.139 = 
             *   IpAddress: 255.255.252.0
             * RFC1213-MIB::ipAdEntBcastAddr.172.20.128.139 = INTEGER: 1
             * RFC1213-MIB::ipAdEntReasmMaxSize.172.20.128.139 = INTEGER: 18024
             *
             * Structure of $matches:
             * Array
             * (
             *     [0] => ipAdEntAddr.10.64.110.1
             *     [1] => ipAdEntAddr
             *     [2] => .10.64.110.1
             *     [3] => .1
             * )
             *
             * $matches[1] is object, $matches[2] is an IP address
             * (with a leading '.')
             * 
             * Strip the leading '.' to use IP as index
             */
        
        $IP = substr_replace($matches[2], "", 0, 1);  // remove leading '.'
        
        $device["ip"]["ipAddrTable"][$IP][$matches[1]] = $value;

        if ($matches[1] === "ipAdEntIfIndex")
        {
            $device["interfaces"][$value]["ipAddrTable"][] =
                &$device["ip"]["ipAddrTable"][$IP];
        }
    }
}


    /* .1.3.6.1.2.1.4 ip
     *   .22 ipNetToMediaTable
     *     .1 ipNetToMediaTableEntry
     *       .1 ipNetToMediaIfIndex
     *       .2 ipNetToMediaPhysAddress
     *       .3 ipNetToMediaNetAddress
     *       .4 ipNetToMediaType
     *
     * INDEX  { ipNetToMediaIfIndex,  ipNetToMediaNetAddress }
     *
     * FUNCTION
     * get_ipNetToMediaTable ($device_name, $community, &$device, $if="")
     *
     * Populates $device["ip"]["ipNetToMediaTable"]
     * Populates $device["interfaces"][$ifIndex]["ipNetToMediaTable"]
     **/

function get_ipNetToMediaTable ($device_name, 
                                $community, 
                                &$device,
                                $if="")
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.4.22";
    $oid .= (!empty($if)) ? ".$if" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  continue;  }

    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)((\.[0-9]+){4})$/i', $key, $matches);

            /* RFC1213-MIB::ipNetToMediaIfIndex.147.172.20.128.31 = 
             *   INTEGER: 147
             * RFC1213-MIB::ipNetToMediaPhysAddress.181.130.64.198.219 = 
             *   Hex-STRING: 00 11 43 08 30 33 
             * RFC1213-MIB::ipNetToMediaNetAddress.147.172.20.144.15 = 
             *   IpAddress: 172.20.144.15
             * RFC1213-MIB::ipNetToMediaType.157.130.64.110.103 = 
             *   INTEGER: dynamic(3)
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => ipNetToMediaIfIndex.99.172.20.148.8
             *     [1] => ipNetToMediaIfIndex
             *     [2] => 99
             *     [3] => .172.20.148.8
             *     [4] => .8
             * )
             *
             * $matches[1] is the object, $matches[2] is the ifIndex,
             * $matches[3] is the IP (with a prepended '.')
             */
        
        $IP = substr_replace($matches[3], "", 0, 1);  // remove leading '.'
        
        $device["ip"]["ipNetToMediaTable"][$IP][$matches[1]] = $value;
        
        $device["interfaces"][$matches[2]]["ipNetToMediaTable"][$IP][$matches[1]] = $value;
    }
}

 
?>
