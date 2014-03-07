<?php  /* -*- c -*- */

  /*
   * Copyright (c) 2014 Robin Garner (robin@nukefest.org)
   * All rights reserved.
   *
   * License: GPL v.3
   */

  /* v.1.1  2014  Improved handling of sysObjectID and improved comments.
   * v.1.0  2004  Implements RFC3418
   */

  /** system group **/

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
   * retrieves objects 1-2 and 4-8 (scalars), and calls
   * get_sysORTable(), get_sysUpTime()
   *
   * populates $device["system"][$object]
   **/

function get_system ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $base_oid  = ".1.3.6.1.2.1.1";

        /* retrieve the scalar objects, 4 through 8 */

    for($i=4; $i <= 8; $i++)
    {
        $oid = $base_oid.".$i.0";
        
        $data = @snmprealwalk ($device_name, $community, $oid);
        
        if (empty($data))  {  return;  }
    
        foreach ($data as $key=>$value) 
        {
            preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
            
                /* SNMPv2-MIB::sysContact.0 = 
                 *   STRING: NOC, noc@foo.edu, 867-5309
                 *
                 * Structure of $matches:
                 * Array
                 * (
                 *     [0] => sysObjectID.0
                 *     [1] => sysObjectID
                 *     [2] => 0
                 * )
                 *
                 * $matches[1] is the oid, $matches[2] is the instance_id.
                 *
                 * sysServices require special handling.
                 **/
            

            if ($matches[1] !== "sysServices")
            {
                $device["system"][$matches[1]] = $value;
                continue;
            }
            
                /* sysServices
                 *
                 * Translate the sysServices values into the
                 * human-readable text strings specified in the RFC:
                 *
                 * "This sum initially takes the value zero. Then, for
                 * each layer, L, in the range 1 through 7, that this
                 * node performs transactions for, 2 raised to (L - 1)
                 * is added to the sum."
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

        /* retrieve 1,2,3,9: sysDescr, sysORTable, sysUpTime,
         * sysObjectID 
         */

    get_sysUpTime ($device_name, $community, $device);
    get_sysDescr ($device_name, $community, $device);
    get_sysObjectID ($device_name, $community, $device);
    get_sysORTable ($device_name, $community, $device);
}



    /* 1.3.6.1.2.1.1 system
     *   .1 sysDescr
     *
     * FUNCTION
     * get_sysDescr ($device_name, $community, &$device)
     * 
     * Sets $device["system"]["sysDescr"]
     **/

function get_sysDescr ($device_name,  
                       $community, 
                       &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.1.1.0";
    
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
     * Sets $device["system"]["sysObjectID"]
     **/

function get_sysObjectID ($device_name,  
                          $community, 
                          &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.1.2.0";
    
    $data = @snmpget ($device_name, $community, $oid);

        /* SNMPv2-MIB::sysObjectID.0 = OID: CISCO-PRODUCTS-MIB::catalyst35504
         *
         * Forcing the OID output format to SNMP_OID_OUTPUT_FULL
         * means that sysObjectID may be returned as a very,
         * very long string:
         *
         * [sysObjectID] => .iso.org.dod.internet.private.enterprises.netSnmp.netSnmpEnumerations.netSnmpAgentOIDs.linux
         *
         * Using SNMP_OID_OUTPUT_SUFFIX would be more
         * appropriate with this object, but that constant was
         * only added in v.5.4 and this library is intended to
         * be backwards compatible with v.4
         *
         * -ALSO- The NET-SNMP-TC MIB is not always parsed by
         * the agent or may not contain a definnition for a
         * particular ID, leading to a value that isn't fully
         * translated, like this:
         *
         * [sysObjectID] => .iso.org.dod.internet.private.enterprises.netSnmp.netSnmpEnumerations.netSnmpAgentOIDs.10
         *
         * If the value has not been translated, the termial
         * substring will be numeric, otherwise it will be
         * alpha-numeric.  If it's numeric, preserve the entire
         * value, else trim the value and return the terminal
         * alpha-numeric substring without the leading '.'.
         */
    
    if (empty($data))  {  return;  }
    
    $sysObjectID = substr(strrchr($data, '.'), 1);
        
    $device["system"]["sysObjectID"] = 
            is_numeric($sysObjectID) ? $data : $sysObjectID;
}


    /* 1.3.6.1.2.1.1 system
     *   .3 sysUpTime // sysUpTimeInstance
     *
     * 
     *
     * FUNCTION
     * get_sysUpTime ($device_name, $community, &$device)
     * 
     * Sets $device["system"]["sysUpTime"]
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

    foreach ($data as $key=>$value) 
    {
        $key="SNMPv2-MIB::sysUpTime.0";
        
        preg_match('/([A-Z0-9]+)(\.([0-9]+))?$/i', $key, $matches);
            
            /* SNMPv2-MIB::sysUpTime.0 = 
             *   Timeticks: (931312920) 107 days, 18:58:49.20
             * 
             * Structure of $matches:
             * Array
             * (
             *    [0] => sysUpTime.0
             *    [1] => sysUpTime
             *    [2] => .0
             *    [3] => 0
             * )
             *
             * - OR -
             *
             * DISMAN-EVENT-MIB::sysUpTimeInstance = 
             *   Timeticks: (563962901) 65 days, 6:33:49.01
             *
             * Structure of $matches:
             * Array
             * (
             *    [0] => sysUpTimeInstance
             *    [1] => sysUpTimeInstance
             * )
             *
             * Which result you get depends on your environment: if you
             * have DISMAN-EVENT-MIB in your mib tree, you may get the
             * later.  If not, you should get the former.  In either
             * case the value is a scalar, not a sequence, so $data will
             * be an array containing one member whose key is
             * unpredictable but whose value is the UpTime.
             **/

        $device["system"][$matches[1]] = $value;
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

    /** SNMP Group **/

    /* 1.3.6.1.2.1 mib-2
     *   .11 snmp
     *     .1  snmpInPkts
     *     .2  snmpOutPkts (obsolete)
     *     .3  snmpInBadVersions
     *     .4  snmpInBadCommunityNames
     *     .5  snmpInBadCommunityUses
     *     .6  snmpInASNParseErrs
     *     .7  -- not used --
     *     .8  snmpInTooBigs (obsolete)
     *     .9  snmpInNoSuchNames (obsolete)
     *     .10 snmpInBadValues (obsolete)
     *     .11 snmpInReadOnlys (obsolete)
     *     .12 snmpInGenErrs (obsolete)
     *     .13 snmpInTotalReqVars (obsolete)
     *     .14 snmpInTotalSetVars (obsolete)
     *     .15 snmpInGetRequests (obsolete)
     *     .16 snmpInGetNexts (obsolete)
     *     .17 snmpInSetRequests (obsolete)
     *     .18 snmpInGetResponses (obsolete)
     *     .19 snmpInTraps (obsolete)
     *     .20 snmpOutTooBigs (obsolete)
     *     .21 snmpOutNoSuchNames (obsolete)
     *     .22 snmpOutBadValues (obsolete)
     *     .23 -- not used -- (obsolete)
     *     .24 snmpOutGenErrs (obsolete)
     *     .25 snmpOutGetRequests (obsolete)
     *     .26 snmpOutGetNexts (obsolete)
     *     .27 snmpOutSetRequests (obsolete)
     *     .28 snmpOutGetResponses (obsolete)
     *     .29 snmpOutTraps (obsolete)
     *     .30 snmpEnableAuthenTraps
     *     .31 snmpSilentDrops
     *     .32 snmpProxyDrops
     *
     * FUNCTION
     * get_snmp ($device_name, $community, &$device)
     * 
     * Retrieves the SNMP group.  The MIB file defines OIDs 7 through 29
     * as "obsolete", but a lot of agents manage them anyway.  All
     * objects are scalars.
     *
     * populates $device["snmp"][$object]
     **/

function get_snmp ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
        
                /* SNMPv2-MIB::snmpInPkts.0 = Counter32: 1560
                 *
                 * Structure of $matches:
                 * Array
                 * (
                 *    [0] => snmpInPkts.0
                 *    [1] => snmpInPkts
                 *    [2] => 0
                 * )
                 *
                 * $matches[1] is the oid, $matches[2] is the instance_id.
                 **/
            
        $device["snmp"][$matches[1]] = $value;
    }
}


    /* 1.3.6.1.2.1.11 snmp
     *   .1  snmpInPkts
     *
     * FUNCTION
     * get_snmpInPkts ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInPkts"]
     **/

function get_snmpInPkts ($device_name,  
                         $community, 
                         &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.1.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInPkts.0 = Counter32: 1560 */
    
    $device["snmp"]["snmpInPkts"] = $data;
}

    /* 1.3.6.1.2.1.11 snmp
     *   .2  snmpOutPkts (obsolete)
     *
     * FUNCTION
     * get_snmpOutPkts ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpOutPkts"]
     **/

function get_snmpOutPkts ($device_name,  
                          $community, 
                          &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.2.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpOutPkts.0 = Counter32: 1561 */
    
    $device["snmp"]["snmpOutPkts"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .3  snmpInBadVersions
     *
     * FUNCTION
     * get_snmpInBadVersions ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInBadVersions"]
     **/

function get_snmpInBadVersions ($device_name,  
                                $community, 
                                &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.3.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInBadVersions.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInBadVersions"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .4  snmpInBadCommunityNames
     *
     * FUNCTION
     * get_snmpInBadCommunityNames ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInBadCommunityNames"]
     **/

function get_snmpInBadCommunityNames ($device_name,  
                                      $community, 
                                      &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.4.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInBadCommunityNames.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInBadCommunityNames"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .5  snmpInBadCommunityUses
     *
     * FUNCTION
     * get_snmpInBadCommunityUses ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInBadCommunityUses"]
     **/

function get_snmpInBadCommunityUses ($device_name,  
                                     $community, 
                                     &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.5.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInBadCommunityUses.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInBadCommunityUses"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .6  snmpInASNParseErrs
     *
     * FUNCTION
     * get_snmpInASNParseErrs ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInASNParseErrs"]
     **/

function get_snmpInASNParseErrs ($device_name,  
                                 $community, 
                                 &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.6.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInASNParseErrs.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInASNParseErrs"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .8  snmpInTooBigs (obsolete)
     *
     * FUNCTION
     * get_snmpInTooBigs ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInTooBigs"]
     **/

function get_snmpInTooBigs ($device_name,  
                            $community, 
                            &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.8.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInTooBigs.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInTooBigs"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .9  snmpInNoSuchNames (obsolete)
     *
     * FUNCTION
     * get_snmpInNoSuchNames ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInNoSuchNames"]
     **/

function get_snmpInNoSuchNames ($device_name,  
                                $community, 
                                &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.9.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInNoSuchNames.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInNoSuchNames"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .10 snmpInBadValues (obsolete)
     *
     * FUNCTION
     * get_snmpInBadValues ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInBadValues"]
     **/

function get_snmpInBadValues ($device_name,  
                              $community, 
                              &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.10.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInBadValues.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInBadValues"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .11 snmpInReadOnlys (obsolete)
     *
     * FUNCTION
     * get_snmpInReadOnlys ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInReadOnlys"]
     **/

function get_snmpInReadOnlys ($device_name,  
                              $community, 
                              &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.11.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInReadOnlys.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInReadOnlys"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .12 snmpInGenErrs (obsolete)
     *
     * FUNCTION
     * get_snmpInGenErrs ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInGenErrs"]
     **/

function get_snmpInGenErrs ($device_name,  
                            $community, 
                            &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.12.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInGenErrs.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInGenErrs"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .13 snmpInTotalReqVars (obsolete)
     *
     * FUNCTION
     * get_snmpInTotalReqVars ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInTotalReqVars"]
     **/

function get_snmpInTotalReqVars ($device_name,  
                                 $community, 
                                 &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.13.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInTotalReqVars.0 = Counter32: 1553 */
    
    $device["snmp"]["snmpInTotalReqVars"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .14 snmpInTotalSetVars (obsolete)
     *
     * FUNCTION
     * get_snmpInTotalSetVars ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInTotalSetVars"]
     **/

function get_snmpInTotalSetVars ($device_name,  
                                 $community, 
                                 &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.14.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInTotalSetVars.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInTotalSetVars"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .15 snmpInGetRequests (obsolete)
     *
     * FUNCTION
     * get_snmpInGetRequests ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInGetRequests"]
     **/

function get_snmpInGetRequests ($device_name,  
                                $community, 
                                &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.15.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInGetRequests.0 = Counter32: 20 */
    
    $device["snmp"]["snmpInGetRequests"] = $data;
}

    /* 1.3.6.1.2.1.11 snmp
     *   .16 snmpInGetNexts (obsolete)
     *
     * FUNCTION
     * get_snmpInGetNexts ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInGetNexts"]
     **/

function get_snmpInGetNexts ($device_name,  
                             $community, 
                             &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.16.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInGetNexts.0 = Counter32: 1554 */
    
    $device["snmp"]["snmpInGetNexts"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .17 snmpInSetRequests (obsolete)
     *
     * FUNCTION
     * get_snmpInSetRequests ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInSetRequests"]
     **/

function get_snmpInSetRequests ($device_name,  
                                $community, 
                                &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.17.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInSetRequests.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInSetRequests"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .18 snmpInGetResponses (obsolete)
     *
     * FUNCTION
     * get_snmpInGetResponses ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInGetResponses"]
     **/

function get_snmpInGetResponses ($device_name,  
                                 $community, 
                                 &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.18.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInGetResponses.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInGetResponses"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .19 snmpInTraps (obsolete)
     *
     * FUNCTION
     * get_snmpInTraps ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpInTraps"]
     **/

function get_snmpInTraps ($device_name,  
                          $community, 
                          &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.19.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpInTraps.0 = Counter32: 0 */
    
    $device["snmp"]["snmpInTraps"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .20 snmpOutTooBigs (obsolete)
     *
     * FUNCTION
     * get_snmpOutTooBigs ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpOutTooBigs"]
     **/

function get_snmpOutTooBigs ($device_name,  
                             $community, 
                             &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.20.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpOutTooBigs.0 = Counter32: 0 */
    
    $device["snmp"]["snmpOutTooBigs"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .21 snmpOutNoSuchNames (obsolete)
     *
     * FUNCTION
     * get_snmpOutNoSuchNames ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpOutNoSuchNames"]
     **/

function get_snmpOutNoSuchNames ($device_name,  
                                 $community, 
                                 &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.21.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpOutNoSuchNames.0 = Counter32: 17 */
    
    $device["snmp"]["snmpOutNoSuchNames"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .22 snmpOutBadValues (obsolete)
     *
     * FUNCTION
     * get_snmpOutBadValues ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpOutBadValues"]
     **/

function get_snmpOutBadValues ($device_name,  
                               $community, 
                               &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.22.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpOutBadValues.0 = Counter32: 0 */
    
    $device["snmp"]["snmpOutBadValues"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .24 snmpOutGenErrs (obsolete)
     *
     * FUNCTION
     * get_snmpOutGenErrs ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpOutGenErrs"]
     **/

function get_snmpOutGenErrs ($device_name,  
                             $community, 
                             &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.24.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpOutGenErrs.0 = Counter32: 0 */
    
    $device["snmp"]["snmpOutGenErrs"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .25 snmpOutGetRequests (obsolete)
     *
     * FUNCTION
     * get_snmpOutGetRequests ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpOutGetRequests"]
     **/

function get_snmpOutGetRequests ($device_name,  
                                 $community, 
                                 &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.25.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpOutGetRequests.0 = Counter32: 0 */
    
    $device["snmp"]["snmpOutGetRequests"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .26 snmpOutGetNexts (obsolete)
     *
     * FUNCTION
     * get_snmpOutGetNexts ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpOutGetNexts"]
     **/

function get_snmpOutGetNexts ($device_name,  
                              $community, 
                              &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.26.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpOutGetNexts.0 = Counter32: 0 */
    
    $device["snmp"]["snmpOutGetNexts"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .27 snmpOutSetRequests (obsolete)
     *
     * FUNCTION
     * get_snmpOutSetRequests ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpOutSetRequests"]
     **/

function get_snmpOutSetRequests ($device_name,  
                                 $community, 
                                 &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.27.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpOutSetRequests.0 = Counter32: 0 */
    
    $device["snmp"]["snmpOutSetRequests"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .28 snmpOutGetResponses (obsolete)
     *
     * FUNCTION
     * get_snmpOutGetResponses ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpOutGetResponses"]
     **/

function get_snmpOutGetResponses ($device_name,  
                                  $community, 
                                  &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.28.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpOutGetResponses.0 = Counter32: 1584 */
    
    $device["snmp"]["snmpOutGetResponses"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .29 snmpOutTraps (obsolete)
     *
     * FUNCTION
     * get_snmpOutTraps ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpOutTraps"]
     **/

function get_snmpOutTraps ($device_name,  
                           $community, 
                           &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.29.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpOutTraps.0 = Counter32: 1 */
    
    $device["snmp"]["snmpOutTraps"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .30 snmpEnableAuthenTraps
     *
     * FUNCTION
     * get_snmpEnableAuthenTraps ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpEnableAuthenTraps"]
     **/

function get_snmpEnableAuthenTraps ($device_name,  
                                    $community, 
                                    &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.30.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpEnableAuthenTraps.0 = INTEGER: disabled(2) */
    
    $device["snmp"]["snmpEnableAuthenTraps"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .31 snmpSilentDrops
     *
     * FUNCTION
     * get_snmpSilentDrops ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpSilentDrops"]
     **/

function get_snmpSilentDrops ($device_name,  
                              $community, 
                              &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.31.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpSilentDrops.0 = Counter32: 0 */
    
    $device["snmp"]["snmpSilentDrops"] = $data;
}


    /* 1.3.6.1.2.1.11 snmp
     *   .32 snmpProxyDrops
     *
     * FUNCTION
     * get_snmpProxyDrops ($device_name, $community, &$device)
     * 
     * Sets $device["snmp"]["snmpProxyDrops"]
     **/

function get_snmpProxyDrops ($device_name,  
                             $community, 
                             &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.11.32.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* SNMPv2-MIB::snmpProxyDrops.0 = Counter32: 0 */
    
    $device["snmp"]["snmpProxyDrops"] = $data;
}


?>
