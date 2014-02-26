<?php  /* -*- c -*- */
  /*
   * Copyright (c) 2014 Robin Garner (robin@nukefest.org)
   * All rights reserved.
   *
   * License: GPL v.3
   */

  /*
   * v.2.0  2014  Implements RFC4293 (mostly)
   * v.1.0  2004  Implements RFC2011
   */

  /** mib-2.IP-MIB **/

  /* .1.3.6.1.2.1 mib-2
   *   .4 ip
   *   .5 icmp
   *
   * HISTORY
   *
   * 1991: RFC 1213, defining mib-2 and a number of sub-groups,
   * originally published.  The "ip" and "icmp" groups initially defined
   * in this RFC.
   *
   * 1994: The "ip" and "icmp" groups were split out into IP-MIB,
   * published in RFC 2011.
   *
   * 2006: RFC 4293 obsoletes RFCs 2011, 2465, and 2466 to make changes
   * to the definitions of "ip" and "icmp" to support IPv6.
   *
   * http://tools.ietf.org/html/rfc4293
   **/


  /* .1.3.6.1.2.1 mib-2
   *   .4 ip
   *     .1  ipForwarding
   *     .2  ipDefaultTTL
   *     .3  ipInReceives       : deprecated in RFC4293
   *     .4  ipInHdrErrors      : deprecated in RFC4293
   *     .5  ipInAddrErrors     : deprecated in RFC4293
   *     .6  ipForwDatagrams    : deprecated in RFC4293
   *     .7  ipInUnknownProtos  : deprecated in RFC4293
   *     .8  ipInDiscards   : deprecated in RFC4293
   *     .9  ipInDelivers   : deprecated in RFC4293
   *     .10 ipOutRequests  : deprecated in RFC4293
   *     .11 ipOutDiscards  : deprecated in RFC4293
   *     .12 ipOutNoRoutes  : deprecated in RFC4293
   *     .13 ipReasmTimeout
   *     .14 ipReasmReqds   : deprecated in RFC4293
   *     .15 ipReasmOKs     : deprecated in RFC4293
   *     .16 ipReasmFails   : deprecated in RFC4293
   *     .17 ipFragOKs      : deprecated in RFC4293
   *     .18 ipFragFails    : deprecated in RFC4293
   *     .19 ipFragCreates  : deprecated in RFC4293
   *     .20 ipAddrTable    : deprecated in RFC4293, get_ipAddrTable()
   *     .21 ipRouteTable       : obsolete
   *     .22 ipNetToMediaTable  : get_ipNetToMediaTable()
   *     .23 ipRoutingDiscards  : deprecated in RFC4293
   *     .24 ipForward          : RFC4292, implemented as seperate library
   *     .25 ipv6IpForwarding
   *     .26 ipv6IpDefaultHopLimit
   *     .27 ipv4InterfaceTableLastChange
   *     .28 ipv4InterfaceTable   : get_ipv4InterfaceTable()
   *     .29 ipv6InterfaceTableLastChange
   *     .30 ipv6InterfaceTable   : get_ipv6InterfaceTable()
   *     .31 ipTrafficStats       : get_ipTrafficStats()
   *     .32 ipAddressPrefixTable : get_ipAddressPrefixTable()
   *     .33 ipAddressSpinLock
   *     .34 ipAddressTable       : get_ipAddressTable()
   *     .35 ipNetToPhysicalTable : get_ipNetToPhysicalTable()
   *     .36 ipv6ScopeZoneIndexTable : get_ipv6ScopeZoneIndexTable()
   *     .37 ipDefaultRouterTable : get_ipDefaultRouterTable()
   *     .38 ipv6RouterAdvertSpinLock
   *     .39 ipv6RouterAdvertTable   : get_ipv6RouterAdvertTable()
   *
   * FUNCTION
   * get_ip ($device_name, $community, &$device)
   *
   * Retrieves scalar objects, calls specific get_[object] functions
   * to retrieve sequential objects: get_ipNetToMediaTable(), etc.
   *
   * Populates $device["ip"]
   **/

function get_ip ($device_name, $community, &$device, $get_dep=FALSE)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

        /* Retrieve .ip scalars */

    $base_oid = "1.3.6.1.2.1.4";

    $objs = array(
        1=>"ipForwarding",
        2=>"ipDefaultTTL",
        13=>"ipReasmTimeout",
        25=>"ipv6IpForwarding",
        26=>"ipv6IpDefaultHopLimit",
        27=>"ipv4InterfaceTableLastChange",
        29=>"ipv6InterfaceTableLastChange",
        33=>"ipAddressSpinLock",
        38=>"ipv6RouterAdvertSpinLock"
                        );

    $objs_dep = array(
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
        14=>"ipReasmReqds",
        15=>"ipReasmOKs",
        16=>"ipReasmFails",
        17=>"ipFragOKs",
        18=>"ipFragFails",
        19=>"ipFragCreates",
        23=>"ipRoutingDiscards"
                      );

    foreach ($objs as $i=>$object)
    {
        $oid  = $base_oid.".$i.0";
        $data = @snmpget ($device_name, $community, $oid);

        $device["ip"][$object] = $data;
    }

        /* Retrieve .ip tables */

    get_ipNetToMediaTable($device_name, $community, $device);
    get_ipv4InterfaceTable($device_name, $community, $device);
    get_ipv6InterfaceTable($device_name, $community, $device);
    get_ipTrafficStats($device_name, $community, $device);
    get_ipAddressTable($device_name, $community, $device);
    get_ipNetToPhysicalTable($device_name, $community, $device);
    get_ipv6ScopeZoneIndexTable($device_name, $community, $device);
    get_ipDefaultRouterTable($device_name, $community, $device);
    get_ipv6RouterAdvertTable($device_name, $community, $device);


        /* If $get_dep is FALSE (default), return now, else retrieve
         * deprecated objects.
         */

    if (!$get_dep)  {  return;  }

    foreach ($objs_dep as $i=>$object)
    {
        $oid  = $base_oid.".$i.0";
        $data = @snmpget ($device_name, $community, $oid);

        $device["ip"][$object] = $data;
    }

    get_ipAddrTable($device_name, $community, $device);
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
     * Deprecated in RFC4293, loosely replaced by ipAddressTable,
     * ipAdEntReasmMaxSize.
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

    $oid  = "1.3.6.1.2.1.4.20.1";
    $oid .= (!empty($ip)) ? ".$ip" : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$value)
    {
        preg_match('/([A-Z0-9]+)((\.[0-9]+){4})$/i', $key, $matches);

            /* > snmpwalk -v 2c -c public localhost ipAddrTable
             *
             * IP-MIB::ipAdEntAddr.127.0.0.1 = IpAddress: 127.0.0.1
             * IP-MIB::ipAdEntAddr.192.168.1.20 = IpAddress: 192.168.1.20
             * IP-MIB::ipAdEntIfIndex.127.0.0.1 = INTEGER: 1
             * IP-MIB::ipAdEntIfIndex.192.168.1.20 = INTEGER: 2
             * IP-MIB::ipAdEntNetMask.127.0.0.1 = IpAddress: 255.0.0.0
             * IP-MIB::ipAdEntNetMask.192.168.1.20 = IpAddress: 255.255.255.0
             * IP-MIB::ipAdEntBcastAddr.127.0.0.1 = INTEGER: 0
             * IP-MIB::ipAdEntBcastAddr.192.168.1.20 = INTEGER: 1
             * IP-MIB::ipAdEntReasmMaxSize.172.20.128.139 = INTEGER: 18024
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

    $oid  = ".1.3.6.1.2.1.4.22.1";
    $oid .= (!empty($if)) ? ".".(int) $if : "";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

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

    /* .1.3.6.1.2.1.4 ip
     *   .28 ipv4InterfaceTable
     *     .1 ipv4InterfaceEntry
     *       .1 ipv4InterfaceIfIndex
     *       .2 ipv4InterfaceReasmMaxSize
     *       .3 ipv4InterfaceEnableStatus
     *       .4 ipv4InterfaceRetransmitTime
     *
     * INDEX  { ipv4InterfaceIfIndex }
     *
     * FUNCTION
     * get_ipv4InterfaceTable ($device_name, $community, &$device, $if="")
     *
     * STATUS: unable to implement; no access to participating agent
     **/

function get_ipv4InterfaceTable ($device_name,
                                 $community,
                                 &$device,
                                 $if="")
{
    return;

    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.4.28.1";
    $oid .= (!empty($if)) ? ".".(int) $if : "";

    $data = @snmprealwalk ($device_name, $community, $oid);
}


    /* .1.3.6.1.2.1.4 ip
     *   .30 ipv6InterfaceTable
     *     .1 ipv6InterfaceEntry
     *       .1 ipv6InterfaceIfIndex
     *       .2 ipv6InterfaceReasmMaxSize
     *       .3 ipv6InterfaceIdentifier
     *       .4 ipv6InterfacePhysicalAddress : obsolete
     *       .5 ipv6InterfaceEnableStatus
     *       .6 ipv6InterfaceReachableTime
     *       .7 ipv6InterfaceRetransmitTime
     *       .8 ipv6InterfaceForwarding
     *
     * INDEX  { ipv6InterfaceIfIndex }
     *
     * FUNCTION
     * get_ipv6InterfaceTable ($device_name, $community, &$device, $if="")
     *
     * STATUS: unable to implement; no access to participating agent
     **/

function get_ipv6InterfaceTable ($device_name,
                                 $community,
                                 &$device)
{
    return;

    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.4.30.1";

    $data = @snmprealwalk ($device_name, $community, $oid);
}


    /* .1.3.6.1.2.1.4 ip
     *   .31 ipTrafficStats
     *     .1 ipSystemStatsTable
     *       .1 ipSystemStatsEntry
     *         .1  ipSystemStatsIPVersion
     *         .2  <reserved>
     *         .3  ipSystemStatsInReceives
     *         .4  ipSystemStatsHCInReceives
     *         .5  ipSystemStatsInOctets
     *         .6  ipSystemStatsHCInOctets
     *         .7  ipSystemStatsInHdrErrors
     *         .8  ipSystemStatsInNoRoutes
     *         .9  ipSystemStatsInAddrErrors
     *         .10 ipSystemStatsInUnknownProtos
     *         .11 ipSystemStatsInTruncatedPkts
     *         .12 ipSystemStatsInForwDatagrams
     *         .13 ipSystemStatsHCInForwDatagrams
     *         .14 ipSystemStatsReasmReqds
     *         .15 ipSystemStatsReasmOKs
     *         .16 ipSystemStatsReasmFails
     *         .17 ipSystemStatsInDiscards
     *         .18 ipSystemStatsInDelivers
     *         .19 ipSystemStatsHCInDelivers
     *         .20 ipSystemStatsOutRequests
     *         .21 ipSystemStatsHCOutRequests
     *         .22 ipSystemStatsOutNoRoutes
     *         .23 ipSystemStatsOutForwDatagrams
     *         .24 ipSystemStatsHCOutForwDatagrams
     *         .25 ipSystemStatsOutDiscards
     *         .26 ipSystemStatsOutFragReqds
     *         .27 ipSystemStatsOutFragOKs
     *         .28 ipSystemStatsOutFragFails
     *         .29 ipSystemStatsOutFragCreates
     *         .30 ipSystemStatsOutTransmits
     *         .31 ipSystemStatsHCOutTransmits
     *         .32 ipSystemStatsOutOctets
     *         .33 ipSystemStatsHCOutOctets
     *         .34 ipSystemStatsInMcastPkts
     *         .35 ipSystemStatsHCInMcastPkts
     *         .36 ipSystemStatsInMcastOctets
     *         .37 ipSystemStatsHCInMcastOctets
     *         .38 ipSystemStatsOutMcastPkts
     *         .39 ipSystemStatsHCOutMcastPkts
     *         .40 ipSystemStatsOutMcastOctets
     *         .41 ipSystemStatsHCOutMcastOctets
     *         .42 ipSystemStatsInBcastPkts
     *         .43 ipSystemStatsHCInBcastPkts
     *         .44 ipSystemStatsOutBcastPkts
     *         .45 ipSystemStatsHCOutBcastPkts
     *         .46 ipSystemStatsDiscontinuityTime
     *         .47 ipSystemStatsRefreshRate
     *
     * INDEX  { ipSystemStatsIPVersion }
     *
     * FUNCTION
     * get_ipTrafficStats ($device_name, $community, &$device)
     *
     * Populates $device["ip"][$ip_ver]
     **/

function get_ipTrafficStats ($device_name,
                             $community,
                             &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.4.31.1";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$value)
    {
        preg_match('/([A-Z0-9]+)\.(ipv[46])$/i', $key, $matches);

            /* IP-MIB::ipSystemStatsInReceives.ipv4 = Counter32: 537743
             * IP-MIB::ipSystemStatsInReceives.ipv6 = Counter32: 239
             * IP-MIB::ipSystemStatsHCInReceives.ipv4 = Counter64: 537743
             * IP-MIB::ipSystemStatsHCInReceives.ipv6 = Counter64: 239
             * IP-MIB::ipSystemStatsHCInOctets.ipv4 = Counter64: 0
             * IP-MIB::ipSystemStatsHCInOctets.ipv6 = Counter64: 0
             * IP-MIB::ipSystemStatsInHdrErrors.ipv4 = Counter32: 0
             * IP-MIB::ipSystemStatsInHdrErrors.ipv6 = Counter32: 0
             * IP-MIB::ipSystemStatsInAddrErrors.ipv4 = Counter32: 0
             * IP-MIB::ipSystemStatsInAddrErrors.ipv6 = Counter32: 0
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => ipSystemStatsInUnknownProtos.ipv6
             *     [1] => ipSystemStatsInUnknownProtos
             *     [2] => ipv6
             * )
             *
             * $matches[1] is the object, $matches[2] is the IP version
             */

        $device["ip"][$matches[2]][$matches[1]] = $value;
    }
}

    /* .1.3.6.1.2.1.4 ip
     *   .32 ipAddressPrefixTable
     *     .1 ipAddressPrefixEntry
     *       .1 ipAddressPrefixIfIndex
     *       .2 ipAddressPrefixType
     *       .3 ipAddressPrefixPrefix
     *       .4 ipAddressPrefixLength
     *       .5 ipAddressPrefixOrigin
     *       .6 ipAddressPrefixOnLinkFlag
     *       .7 ipAddressPrefixAutonomousFlag
     *       .8 ipAddressPrefixAdvPreferredLifetime
     *       .9 ipAddressPrefixAdvValidLifetime
     *
     * INDEX  { ipAddressPrefixIfIndex, ipAddressPrefixType,
     *          ipAddressPrefixPrefix, ipAddressPrefixLength }
     *
     * FUNCTION
     * get_ipAddressPrefixTable ($device_name, $community, &$device)
     *
     * STATUS: unable to implement; no access to participating agent
     **/

function get_ipAddressPrefixTable ($device_name,
                                   $community,
                                   &$device)
{
    return;

    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.4.32.1";

    $data = @snmprealwalk ($device_name, $community, $oid);
}

    /* .1.3.6.1.2.1.4 ip
     *   .34 ipAddressTable
     *     .1 ipAddressEntry
     *       .1  ipAddressAddrType
     *       .2  ipAddressAddr
     *       .3  ipAddressIfIndex
     *       .4  ipAddressType
     *       .5  ipAddressPrefix
     *       .6  ipAddressOrigin
     *       .7  ipAddressStatus
     *       .8  ipAddressCreated
     *       .9  ipAddressLastChanged
     *       .10 ipAddressRowStatus
     *       .11 ipAddressStorageType
     *
     * INDEX  { ipAddressAddrType, ipAddressAddr }
     *
     * FUNCTION
     * get_ipAddressTable ($device_name, $community, &$device)
     *
     * Populates $device["ip"][$ip_ver][$ip_addr]["ipAddressTable"],
     *           $device["interfaces"][$if][$ip_ver][$ip_addr]
     **/

function get_ipAddressTable ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.4.34.1";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$value)
    {
        preg_match('/([A-Z0-9]+)\.(ipv[46])\.\"([0-9a-f:\.]+)\"$/i',
                   $key,
                   $matches);

            /* IP-MIB::ipAddressIfIndex.ipv4."127.0.0.1" = INTEGER: 1
             * IP-MIB::ipAddressIfIndex.ipv4."192.168.1.20" = INTEGER: 2
             * IP-MIB::ipAddressIfIndex.ipv6."00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:01" = INTEGER: 1
             * IP-MIB::ipAddressIfIndex.ipv6."fe:80:00:00:00:00:00:00:d2:67:e5:ff:fe:09:79:a6" = INTEGER: 2
             * IP-MIB::ipAddressType.ipv4."127.0.0.1" = INTEGER: unicast(1)
             * IP-MIB::ipAddressType.ipv4."192.168.1.20" = INTEGER: unicast(1)
             * IP-MIB::ipAddressType.ipv6."00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:01" = INTEGER: unicast(1)
             * IP-MIB::ipAddressType.ipv6."fe:80:00:00:00:00:00:00:d2:67:e5:ff:fe:09:79:a6" = INTEGER: unicast(1)
             * IP-MIB::ipAddressPrefix.ipv4."127.0.0.1" = OID: IP-MIB::ipAddressPrefixOrigin.1.ipv4."67.77.94.183".0
             * IP-MIB::ipAddressPrefix.ipv4."192.168.1.20" = OID: IP-MIB::ipAddressPrefixOrigin.2.ipv4."67.77.94.183".0
             * IP-MIB::ipAddressPrefix.ipv6."00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:01" = OID: IP-MIB::ipAddressPrefixOrigin.1.ipv6."00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:01".128
             *
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => ipAddressIfIndex.ipv4."192.168.1.20"
             *     [1] => ipAddressIfIndex
             *     [2] => ipv4
             *     [3] => 192.168.1.20
             * )
             *
             * $matches[1] is the object, $matches[2] is the IP version.
             */

        $device["ip"][$matches[2]][$matches[3]][$matches[1]] = $value;

        $ipAddress_tbl[$matches[2]][$matches[3]][$matches[1]] = $value;
    }

        /* Add data to the "interfaces" branch of the device table */

    foreach ($ipAddress_tbl as $ip_ver=>$addr_tbl)
    {
        foreach($addr_tbl as $addr=>$entry)
        {
            $device["interfaces"][$entry["ipAddressIfIndex"]][$ip_ver][$addr] =
                $entry;
        }
    }
}


    /* .1.3.6.1.2.1.4 ip
     *   .35 ipNetToPhysicalTable
     *     .1 ipNetToPhysicalEntry
     *       .1 ipNetToPhysicalIfIndex
     *       .2 ipNetToPhysicalNetAddressType
     *       .3 ipNetToPhysicalNetAddress
     *       .4 ipNetToPhysicalPhysAddress
     *       .5 ipNetToPhysicalLastUpdated
     *       .6 ipNetToPhysicalType
     *       .7 ipNetToPhysicalState
     *       .8 ipNetToPhysicalRowStatus
     *
     * INDEX  { ipNetToPhysicalIfIndex,
     *          ipNetToPhysicalNetAddressType,
     *          ipNetToPhysicalNetAddress }
     *
     * FUNCTION
     * get_ipNetToPhysicalTable ($device_name, $community, &$device)
     *
     * Populates $device["ip"][$ip_ver][$ip_addr]["ipNetToPhysicalTable"],
     *           $device["interfaces"][$if][$ip_ver][$ip_addr]
     **/

function get_ipNetToPhysicalTable ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.4.35.1";

    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$value)
    {
        preg_match('/([A-Z0-9]+)\.([0-9])+\.(ipv[46])\.\"([0-9a-f:\.]+)\"$/i',
                   $key,
                   $matches);

            /* IP-MIB::ipNetToPhysicalPhysAddress.2.ipv4."192.168.1.1" = STRING: 0:16:b6:19:af:e0
             * IP-MIB::ipNetToPhysicalPhysAddress.2.ipv4."192.168.1.11" = STRING: 90:e6:ba:ba:f9:4d
             * IP-MIB::ipNetToPhysicalPhysAddress.2.ipv4."192.168.1.110" = STRING: 0:4:20:28:28:ce
             * IP-MIB::ipNetToPhysicalType.2.ipv4."192.168.1.1" = INTEGER: dynamic(3)
             * IP-MIB::ipNetToPhysicalType.2.ipv4."192.168.1.11" = INTEGER: dynamic(3)
             * IP-MIB::ipNetToPhysicalType.2.ipv4."192.168.1.110" = INTEGER: dynamic(3)
             * IP-MIB::ipNetToPhysicalState.2.ipv4."192.168.1.1" = INTEGER: reachable(1)
             * IP-MIB::ipNetToPhysicalState.2.ipv4."192.168.1.11" = INTEGER: reachable(1)
             * IP-MIB::ipNetToPhysicalState.2.ipv4."192.168.1.110" = INTEGER: reachable(1)
             * IP-MIB::ipNetToPhysicalRowStatus.2.ipv4."192.168.1.1" = INTEGER: active(1)
             * IP-MIB::ipNetToPhysicalRowStatus.2.ipv4."192.168.1.11" = INTEGER: active(1)
             * IP-MIB::ipNetToPhysicalRowStatus.2.ipv4."192.168.1.110" = INTEGER: active(1)
             *
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => ipNetToPhysicalPhysAddress.2.ipv4."192.168.1.1"
             *     [1] => ipAddressIfIndex
             *     [2] => $if_number
             *     [3] => ipv4
             *     [4] => 192.168.1.1
             * )
             *
             * $matches[1] is the object, $matches[2] is the interface,
             * $matches[3] is the IP version.
             */

        $device["ip"][$matches[3]][$matches[4]]["if"][$matches[2]][$matches[1]]
            = $value;

        $ipNtoP_tbl[$matches[3]][$matches[2]][$matches[4]][$matches[1]]
            = $value;
    }

        /* Add data to the "interfaces" branch of the device table
         * foreach() statements from hell.
         */

    foreach ($ipNtoP_tbl as $ip_ver=>$addr_tbl)
    {
        foreach($addr_tbl as $if=>$addr)
        {
            foreach($addr as $ip=>$net_to_phys)
            {
                foreach ($net_to_phys as $oid=>$val)
                {
                    $device["interfaces"][$if][$ip_ver][$ip][$oid] = $val;
                }
            }
        }
    }
}


    /* .1.3.6.1.2.1.4 ip
     *   .36 ipv6ScopeZoneIndexTable
     *     .1 ipv6ScopeZoneIndexEntry
     *       .1  ipv6ScopeZoneIndexIfIndex
     *       .2  ipv6ScopeZoneIndexLinkLocal
     *       .3  ipv6ScopeZoneIndex3
     *       .4  ipv6ScopeZoneIndexAdminLocal
     *       .5  ipv6ScopeZoneIndexSiteLocal
     *       .6  ipv6ScopeZoneIndex6
     *       .7  ipv6ScopeZoneIndex7
     *       .8  ipv6ScopeZoneIndexOrganizationLocal
     *       .9  ipv6ScopeZoneIndex9
     *       .10 ipv6ScopeZoneIndexA
     *       .11 ipv6ScopeZoneIndexB
     *       .12 ipv6ScopeZoneIndexC
     *       .13 ipv6ScopeZoneIndexD
     *
     * INDEX  { ipv6ScopeZoneIndexIfIndex }
     *
     * FUNCTION
     * get_ipv6ScopeZoneIndexTable ($device_name, $community, &$device)
     *
     * STATUS: unable to implement; no access to participating agent
     **/

function get_ipv6ScopeZoneIndexTable ($device_name, $community, &$device)
{
    return;

    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.4.36.1";

    $data = @snmprealwalk ($device_name, $community, $oid);
}

    /* .1.3.6.1.2.1.4 ip
     *   .37 ipDefaultRouterTable
     *     .1 ipDefaultRouterEntry
     *     .1 ipDefaultRouterAddressType
     *     .2 ipDefaultRouterAddress
     *     .3 ipDefaultRouterIfIndex
     *     .4 ipDefaultRouterLifetime
     *     .5 ipDefaultRouterPreference
     *
     * INDEX  { ipDefaultRouterAddressType, ipDefaultRouterAddress,
     *          ipDefaultRouterIfIndex }
     *
     * FUNCTION
     * get_ipDefaultRouterTable ($device_name, $community, &$device)
     *
     * STATUS: unable to implement; no access to participating agent
     **/

function get_ipDefaultRouterTable ($device_name, $community, &$device)
{
    return;

    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.4.37.1";

    $data = @snmprealwalk ($device_name, $community, $oid);
}


    /* .1.3.6.1.2.1.4 ip
     *   .39 ipv6RouterAdvertTable
     *     .1 ipv6RouterAdvertEntry
     *       .1  ipv6RouterAdvertIfIndex
     *       .2  ipv6RouterAdvertSendAdverts
     *       .3  ipv6RouterAdvertMaxInterval
     *       .4  ipv6RouterAdvertMinInterval
     *       .5  ipv6RouterAdvertManagedFlag
     *       .6  ipv6RouterAdvertOtherConfigFlag
     *       .7  ipv6RouterAdvertLinkMTU
     *       .8  ipv6RouterAdvertReachableTime
     *       .9  ipv6RouterAdvertRetransmitTime
     *       .10 ipv6RouterAdvertCurHopLimit
     *       .11 ipv6RouterAdvertDefaultLifetime
     *       .12 ipv6RouterAdvertRowStatus
     *
     * INDEX  { ipv6RouterAdvertIfIndex }
     *
     * FUNCTION
     * get_ipv6RouterAdvertTable ($device_name, $community, &$device)
     *
     * STATUS: unable to implement; no access to participating agent
     **/

function get_ipv6RouterAdvertTable ($device_name, $community, &$device)
{
    return;

    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.4.39.1";

    $data = @snmprealwalk ($device_name, $community, $oid);
}

?>
