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
        $oid = $base_oid.".$i";
        
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
                 * sysObjectID and sysServices require special handling.
                 **/
            

                /* The majority of the system tree is simple scalars */

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


?>
