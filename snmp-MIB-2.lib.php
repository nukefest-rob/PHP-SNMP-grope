<?php  /* -*- c -*- */

    /* 1.3.6.1.2.1 mib-2
     *   .1 system
     *   .2 interfaces
     *   .3 at :deprecated
     *   .4 ip
     *   .5 icmp
     *   .6 tcp
     *   .7 udp
     *   .8 egp
     *   .9 cmot
     *   .10 transmission
     *   .11 snmp
     *   .13 appletalk
     *   .14 ospf
     *   .15 bgp
     *   .16 rmon
     *   .17 dot1dBridge
     *   .18 phiv (DECNET PhaseV)
     *   .19 char (CHARACTER-MIB)
     *   .20 snmpParties (RFC1353-MIB, SNMP Party)
     *   .21 snmpSecrets (RFC1353-MIB, SNMP Party
     *   .22 snmpDot3RptrMgt (SNMP-REPEATER-MIB)
     *   .23 rip2 (RIPv2-MIB)
     *   .24 ident (RFC1414-MIB, TCP Client Identity Protocol)
     *   .25 host
     *   .26 snmpDot3MauMgt (MAU-MIB, rfc4836.txt)
     *   .27 application (NETWORK-SERVICES-MIB)
     *   .28 mta (MTA-MIB, rfc2789)
     *   .29 dsaMIB (DSA-MIB, rfc1567)
     *   .30 ianaifType
     *   .31 ifMIB
     *   .32 dns (DNS-SERVER-MIB, RFC-1213)
     *   .33 upsMIB (UPS-MIB, rfc1628)
     *   .34 snanauMIB (SNA-NAU-MIB, rfc1666)
     *   .35 etherMIB (SIP-MIB, RFC 1694)
     *   .37 atmMIB
     *   .38 mdmMib (Modem-MIB, RFC 1696)
     *   .39 rdbmsMIB (RDBMS-MIB, rfc1697)
     *   .40 flowMIB (FLOW-METER-MIB, RFC 2720)
     *   .41 snaDLC (NA-SDLC-MIB, RFC1747)
     *   .42 dot5SrMIB (TOKENRING-STATION-SR-MIB, rfc1749)
     *   .43 finisherMIB (Finisher-MIB)
     *   .44 mipMIB
     *   .45 ???
     *   .46 dlsw (DLSW-MIB)
     *   .47 entityMIB
     *   .48 ipMIB
     *   .49 tcpMIB
     *   .50 udpMIB
     *   .51 rsvp (RSVP-MIB, RFC 2206)
     *   .52 intSrv (INTEGRATED-SERVICES-MIB, rfc2213)
     *   .53 vgRptrMIB (DOT12-RPTR-MIB, RFC 2266)
     *   .54 sysApplMIB (SYSAPPL-MIB, RFC 2287)
     *   .55 ipv6MIB
     *   .56 ipv6IcmpMIB
     *   .57 marsMIB (IPATM-IPMC-MIB, RFC2022)
     *   .58 perfHistTCMIB (PerfHist-TC-MIB, rfc3593)
     *   .59 atmAccountingInformationMIB (ATM-ACCOUNTING-INFORMATION-MIB, 
     *                                    rfc512)
     *   .60 accountingControlMIB
     *   .61 ianaTn3270eTcMib
     *   .62 applicationMib (APPLICATION-MIB)
     *   .63 schedMIB
     *   .67 radiusMIB 
     *   .68 vrrpMIB 
     *   .69 docsDev 
     *   .72 ianaAddressFamilyNumbers 
     *   .75 fcFeMIB 
     *   .76 inetAddressMIB 
     *   .78 hcnumTC 
     *   .79 ptopoMIB 
     *   .83 ipMRouteStdMIB 
     *   .84 ianaRtProtoMIB 
     *   .85 igmpStdMIB 
     *   .88 dismanEventMIB 
     *   .91 mldMIB 
     *   .92 notificationLogMIB 
     *   .96 diffServDSCPTC 
     *   .97 diffServMib 
     *   .99 entitySensorMIB 
     *   .105 powerEthernetMIB 
     *   .129 vpnTcMIB 
     **/


    /* 1.3.6.1.2.1 mib-2
     *   .1 system
     *     .1 sysDescr
     *     .2 sysObjectID
     *     .3 sysUpTime
     *     .4 sysContact
     *     .5 sysName
     *     .6 sysLocation
     *     .7 sysServices
     *     .8 sysORLastChange
     *     .9 sysORTable
     *
     * FUNCTION
     * get_system ($device_name, $community, &$device)
     * 
     * retrieves objects 1 through 8 (scalars), and calls
     * get_sysORTable()
     *
     * populates $device["system"][$object]
     **/

function get_system ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $base_oid  = ".1.3.6.1.2.1.1";

        /* retrieve the scalar objects, 1 through 8 */

    for($i=1; $i <= 8; $i++)
    {
        $oid = $base_oid.".$i";
        
        $data = @snmprealwalk ($device_name, $community, $oid);
    
        if (empty($data))  {  return;  }
    
        foreach ($data as $key=>$value) 
        {
            preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
            
                /* SNMPv2-MIB::sysContact.0 = 
                 *   STRING: NOC, noc@foo.edu, 867-5309
                 *
                 * structure of $matches:
                 * Array
                 * (
                 *     [0] => sysObjectID.0
                 *     [1] => sysObjectID
                 *     [2] => 0
                 * )
                 *
                 * $matches[1] is the oid, $matches[2] is the instance_id.
                 *
                 * oddball object, 7:
                 *    SNMPv2-MIB::sysServices.0 = INTEGER: 6
                 *
                 * sysServices is an integer representing a bit field
                 * indicating what services this entity "performs
                 * transactions for".  translate the int to strings.
                 **/
            
            if (!isset($matches[1]))
            {
                    /* sometimes oids don't appear the way you want
                     * them to:
                     * SNMPv2-MIB::sysDescr.0 = STRING: [stuff]
                     * SNMPv2-MIB::sysObjectID.0 = OID: [ more stuff]
                     * DISMAN-EVENT-MIB::sysUpTimeInstance = 
                     *   Timeticks: (583522664) 67 days, 12:53:46.64
                     * SNMPv2-MIB::sysContact.0 = STRING: [other stuff]
                     * SNMPv2-MIB::sysName.0 = STRING: [stuff]
                     * SNMPv2-MIB::sysLocation.0 = STRING: [stuff]
                     * SNMPv2-MIB::sysServices.0 = INTEGER: 6
                     *
                     * note that "DISMAN-EVENT-MIB" line.  there's no
                     * instance id.  that's because the
                     * DISMAN-EVENT-MIB defines sysUpTime.0 as
                     * sysUpTimeInstance.  if the DISMAN-EVENT-MIB is
                     * -not- in your mib tree, 1.3.6.1.2.1.1.3.0 will
                     * be parsed as "sysUpTime" and look like all the
                     * other objects.  if it -is- present, sysUpTime.0
                     * will be translated to "sysUpTimeInstance" and
                     * the above regexp will not parse the line.
                     */

                if (preg_match('/sysUpTimeInstance/i', $key))
                {
                    $device["system"]["sysUpTime"] = $value;
                }
                
                continue;
            }
            

            if ($matches[1] !== "sysServices")
            {
                $device["system"][$matches[1]] = $value;
                continue;
            }
            
                /* parse the sysServices values into a human-readable
                 * text string.
                 */
            
            $sysServices_list = array(1=>"physical (e.g., repeaters)",
                                      2=>"datalink/subnetwork (e.g., bridges)",
                                      3=>"internet (e.g., supports the IP)",
                                      4=>"end-to-end (e.g., supports the TCP)",
                                      5=>"session",
                                      6=>"presentation",
                                      7=>"applications (e.g., supports the ".
                                      "SMTP)");
        
        
            $sysServices_string = "";
            
            for ($i=1; $i < 8; $i++)
            {
                $mask = pow(2, ($i-1));
                if (($value & $mask) === $mask)
                {
                    $sysServices_string .= 
                        (!empty($sysServices_string)) ? "," : "";
                    
                    $sysServices_string .= " ".$sysServices_list[$i];
                }
            }
            
            $device["system"][$matches[1]] = $value.":".$sysServices_string;
        }
    }

        /* retrieve sysORTable */

    get_sysORTable ($device_name, $community, $device);
}



    /* 1.3.6.1.2.1.1 system
     *   .1 sysDescr
     *
     * FUNCTION
     * get_sysDescr ($device_name, $community, &$device)
     * 
     * sets $device["system"]["sysDescr"]
     **/

function get_sysDescr ($device_name,  
                          $community, 
                          &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.1.1";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::sysDescr.0 = STRING: Cisco IOS Software, C3550
         *  Software (C3550-IPBASEK9-M), Version 12.2(25)SEE1, RELEASE
         *  SOFTWARE (fc1) Copyright (c) 1986-2006 by Cisco Systems,
         *  Inc.  Compiled Mon 22-May-06 08:08 by yenanh
         **/

    $device["system"]["sysDescr"] = $data;
}


    /* 1.3.6.1.2.1.1 system
     *   .2 sysObjectID
     *
     * FUNCTION
     * get_sysObjectID ($device_name, $community, &$device, $valueretrieval="")
     * 
     * sets $device["system"]["sysObjectID"]
     **/

function get_sysObjectID ($device_name,  
                          $community, 
                          &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.1.2";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::sysObjectID.0 = OID: CISCO-PRODUCTS-MIB::catalyst35504
        **/

    $device["system"]["sysObjectOID"] = $data;
}


    /* 1.3.6.1.2.1.1 system
     *   .3 sysUpTime
     *
     * FUNCTION
     * get_sysUpTime ($device_name, $community, &$device)
     * 
     * sets $device["system"]["sysUpTime"]
     **/

function get_sysUpTime ($device_name,  
                          $community, 
                          &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.1.3";
    
    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::sysUpTime.0 = 
         *   Timeticks: (931312920) 107 days, 18:58:49.20
         * 
         * - OR -
         *
         * DISMAN-EVENT-MIB::sysUpTimeInstance = 
         *   Timeticks: (563962901) 65 days, 6:33:49.01
         *
         * which result you get depends on your environment: if you
         * have DISMAN-EVENT-MIB in your mib tree, you may get the
         * later.  if you don't, you should get the former.  in either
         * case the value is a scalar, not a sequence, so $data will
         * be an array containing one member whose key is
         * unpredictable but whose value is the UpTime.
         **/

    foreach ($data as $key=>$val)
    {
        $device["system"]["sysUpTime"] = $val;
    }
}



    /* .1.3.6.1.2.1.1 system
     *   .9 sysORTable
     *     .1 sysOREntry
     *       .1 sysORIndex
     *       .2 sysORID
     *       .3 sysORDescr
     *       .4 sysORUpTime
     *
     * FUNCTION
     * get_sysORTable ($device_name, $community, &$device)
     * 
     * populates $device["system"]["sysORTable"][$i][$object]
     **/

function get_sysORTable ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.1.9";
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
            
            /* SNMPv2-MIB::sysORID.1 = OID: IF-MIB::ifMIB
             * SNMPv2-MIB::sysORID.2 = OID: SNMPv2-MIB::snmpMIB
             * SNMPv2-MIB::sysORID.3 = OID: TCP-MIB::tcpMIB
             * SNMPv2-MIB::sysORDescr.1 = 
             *   STRING: The MIB module to describe generic objects for 
             *           network interface sub-layers
             * SNMPv2-MIB::sysORDescr.2 = 
             *   STRING: The MIB module for SNMPv2 entities
             * SNMPv2-MIB::sysORDescr.3 = 
             *   STRING: The MIB module for managing TCP implementations
             * SNMPv2-MIB::sysORUpTime.1 = Timeticks: (0) 0:00:00.00
             * SNMPv2-MIB::sysORUpTime.2 = Timeticks: (0) 0:00:00.00
             * SNMPv2-MIB::sysORUpTime.3 = Timeticks: (0) 0:00:00.00
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => sysORID.1
             *     [1] => sysORID
             *     [2] => 1
             * )
             *
             * $matches[1] is the oid, $matches[2] is the row number.
             *
             **/

        $device["system"]["sysORTable"][$matches[2]][$matches[1]] = $value;
    }
}


?>
