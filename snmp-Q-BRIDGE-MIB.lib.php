<?php /* -*- c -*- */

    /* Q-BRIDGE-MIB
     * .1.3.6.1.2.1 mib-2
     *   .17 dot1dBridge
     *     .7 qBridgeMIB
     *       .1 qBridgeMIBObjects
     *       .2 qBridgeConformance : n/i
     *
     * Q-BRIDGE mib is a branch of the dot1d mib.  tables are indexed
     * by dot1d port (correlated to ifIndex via dot1dBasePortIfIndex).
     * 
     * Q-BRIDGE provides access to...
     *
     * a) general information about the version of 802.1q implemented by
     *    the device and how it's been implemented (max # vlans, current #
     *    of vlans active, gvrp status, etc) (dot1qBase)
     *
     * b) filtering database info & stats (dot1qTp 1)
     *
     * c) transparent forwarding database info (bridge table), indexed
     *    per-vlan.  alternative to the dot1dTpFdbTable defined in the
     *    original BRIDGE-MIB for 802.1d devices. (dot1qTp 2)
     *
     * d) multicast forwarding config per-port (dot1qTp 4)
     *
     * e) static filtering config per-port (dot1qStatic)
     *
     * f) information about which port are transmitting for each vlan
     *    (by which, in conjunciton with dot1dBasePortTable, you can 
     *    figure out which vlans are being piped out each ifIndex) and
     *    wether they were configured dynamically or statically (dot1qVlan)
     *
     * g) per-port vlan and control configs (dot1qVlan 5)
     *
     * h) per-port vlan stats, regular and high-capacity (dot1qVlan 6 & 7)
     *
     * i) vlan learning constraints (dot1qVlan 8-10)
     *
     * j) descriptions of what objects are available on the device
     *    (qBridgeConformance) 
     *
     * MIB SEZ: This table is >>an alternative<< to the
     * dot1dTpFdbTable, so if a device implements 802.1Q, in theory
     * the dot1dTp table can be skipped.
     **/


    /* need to be able to run the get_dot1dBasePortTable function to 
     * map "port" to ifIndex.
     *
     * THIS FUNC IS CRIBBED DIRECTLY FROM the bridge (dot1d) library
     *
     * .1.3.6.1.2.1.17.1.4 dot1dBasePortTable
     *   .1 dot1dBasePortEntry
     *     .2 dot1dBasePortIfIndex
     *
     * INDEX = dot1dBasePort
     * 
     * maps port# to ifIndex
     *
     * FUNCTION
     * get_dot1dBasePortIfIndex ($device_name, $community, &$device)
     * 
     * populates 
     *   $device["dot1dBridge"]["portTable"][$port]["dot1dBasePortIfIndex"]
     * adds pointer $device["interfaces"][$ifIndex]["dot1dBridge"] => 
     *     &$device["dot1dBridge"]["portTable"][$dot1dPort];
     * populates $device["dot1dIfIndexMap"]
     * populates $device["dot1dPortMap"]
     *
     **/

if (!function_exists('get_dot1dBasePortIfIndex'))
{
        /* .4 dot1dBasePortTable
         *   .1 dot1dBasePortEntry
         *     .2 dot1dBasePortIfIndex
         *
         * INDEX = dot1dBasePort
         * 
         * construct a table to map bridge "ports" to ifIndices.
         **/
    
    function get_dot1dBasePortIfIndex ($device_name, $community, &$device) 
    {
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
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
            
            $device["interfaces"][$val]["dot1dBridge"] = 
                &$device["dot1dBridge"]["portTable"][$matches[0]];
        }
    }
}



    /* .1.3.6.1.2.1 mib-2
     *   .17 dot1dBridge
     *     .7 qBridgeMIB
     *       .1 qBridgeMIBObjects
     *
     * FUNCTION
     * get_qBridgeMIB ($device_name, $community, &$device)
     *
     * calls get_qBridgeMIBObjects()
     **/

function get_qBridgeMIB ($device_name, $community, &$device) 
{
    get_qBridgeMIBObjects($device_name, $community, $device);
}


    /* .1.3.6.1.2.1.17.7.1 qBridgeMIBObjects
     *   .1 dot1qBase       
     *   .2 dot1qTp         
     *   .3 dot1qStatic : n/i    
     *   .4 dot1qVlan       
     *
     * FUNCTION
     * get_qBridgeMIBObjects($device_name, $community, &$device)
     *
     * calls get_dot1qBase(), get_dot1qTp(), get_dot1qVlan()
     **/

function get_qBridgeMIBObjects($device_name, $community, &$device)
{
        /* call get_dot1qVlanFdbId() first so vlan info can be
         * included in the FdbTable
         **/

    get_dot1qVlanFdbId($device_name, $community, $device);

    get_dot1qBase ($device_name, $community, $device);
    get_dot1qTp ($device_name, $community, $device);
    get_dot1qVlan ($device_name, $community, $device);
}


    /* .1.3.6.1.2.1.17.7.1 qBridgeMIBObjects
     *   .1 dot1qBase       
     *     .1 dot1qVlanVersionNumber
     *     .2 dot1qMaxVlanId
     *     .3 dot1qMaxSupportedVlans
     *     .4 dot1qNumVlans
     *     .5 dot1qGvrpStatus
     *
     * general info about 802.1q implementation on this device
     *
     * FUNCTION
     * get_dot1qBase($device_name, $community, &$device)
     *
     * populates $device["dot1dBridge"]["qBridge"]
     **/

function get_dot1qBase ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid = ".1.3.6.1.2.1.17.7.1.1";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);

            /* Q-BRIDGE-MIB::dot1qVlanVersionNumber.0 = INTEGER: version1(1)
             * Q-BRIDGE-MIB::dot1qMaxVlanId.0 = INTEGER: 4094
             * Q-BRIDGE-MIB::dot1qMaxSupportedVlans.0 = Gauge32: 16
             * Q-BRIDGE-MIB::dot1qNumVlans.0 = Gauge32: 6
             * Q-BRIDGE-MIB::dot1qGvrpStatus.0 = INTEGER: disabled(2)
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => dot1qVlanVersionNumber.0
             *     [1] => dot1qVlanVersionNumber
             *     [2] => 0
             * )
             *
             * $matches[1] is the object
             **/

        $device["dot1dBridge"]["qBridge"][$matches[1]] = $value;
    }
}


    /* .1.3.6.1.2.1.17.7.1 qBridgeMIBObjects
     *   .1 dot1qBase       
     *     .1 dot1qVlanVersionNumber
     * 
     * FUNCTION
     * get_ ($device_name, $community, &$device) 
     *
     * sets $device["dot1dBridge"]["qBridge"]["dot1qVlanVersionNumber"]
     **/

function get_dot1qVlanVersionNumber ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid = ".1.3.6.1.2.1.17.7.1.1.1.0";
    
    $data = @snmpget ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

        /* Q-BRIDGE-MIB::dot1qVlanVersionNumber.0 = INTEGER: version1(1)
        **/

    $device["dot1dBridge"]["qBridge"]["dot1qVlanVersionNumber"] = $data;
}


    /* .1.3.6.1.2.1.17.7.1 qBridgeMIBObjects
     *   .2 dot1qTp
     *     .1 dot1qFdbTable 
     *     .2 dot1qTpFdbTable
     *     .3 dot1qTpGroupTable : n/i
     *     .4 dot1qForwardAllTable : n/i
     *     .5 dot1qForwardUnregisteredTable : n/i
     *
     * FUNCTIONS
     * get_dot1qTp ($device_name, $community, &$device)
     * 
     * calls get_dot1qFdbTable(), get_dot1qTpFdbTable()
     **/

 
function get_dot1qTp ($device_name, $community, &$device) 
{
    get_dot1qFdbTable ($device_name, $community, $device);
    get_dot1qTpFdbTable ($device_name, $community, $device);
}


    /* .1.3.6.1.2.1.17.1.2 dot1qTp
     *   .1 dot1qFdbTable
     *     .1 dot1qFdbEntry
     *       .1 dot1qFdbId : n/a
     *       .2 dot1qFdbDynamicCount
     *
     * INDEX   { dot1qFdbId }
     *
     * existing vlans and the number of MACs known in each
     *
     * FUNCTION
     * get_dot1qFdbTable($device_name, $community, &$device)
     *
     * populates $device["dot1dBridge"]["qBridge"]["FdbTable"]
     **/

function get_dot1qFdbTable ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid = ".1.3.6.1.2.1.17.7.1.2.1";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /* Q-BRIDGE-MIB::dot1qFdbDynamicCount.1 = Counter32: 7
             * Q-BRIDGE-MIB::dot1qFdbDynamicCount.202 = Counter32: 2
             * Q-BRIDGE-MIB::dot1qFdbDynamicCount.205 = Counter32: 30
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 202
             * )
             *
             * $matches[0] is a dot1qFdbId (usually a 1-1 map to vlanID)
             **/

        $device["dot1dBridge"]["qBridge"]["FdbTable"][$matches[0]]["count"] =
            $value;
    }
}



    /* .1.3.6.1.2.1.17.7.1.2 dot1qTp
     *   .2 dot1qTpFdbTable
     *     .1 dot1qTpFdbEntry
     *       .1 dot1qTpFdbAddress : n/i
     *       .2 dot1qTpFdbPort
     *       .3 dot1qTpFdbStatus
     *
     * dot1qTpFdbStatus values:
     * 1 == other
     * 2 == invalid
     * 3 == learned
     * 4 == self
     * 5 == mgmt
     *
     * INDEX   { dot1qFdbId, dot1qTpFdbAddress }
     *
     * FUNCTION
     * get_dot1qTpFdbTable ($device_name, $community, &$device) 
     *
     * populates
     * $device["dot1dBridge"]["portTable"][$port]["FdbTable"],
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     *
     * if get_dot1qVlanFdbId() was called previously
     * ($device["dot1dBridge"]["qBridge"]["FdbTable"] has been
     * populated), associates the FdbID with a vlan# and adds
     * "vlan"=>[vlan#] to
     * $device["dot1dBridge"]["portTable"][$dot1dPort][$port]["FdbTable"]
     **/

function get_dot1qTpFdbTable ($device_name, $community, &$device) 
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
    snmp_set_quick_print(TRUE);
    
    $oid = ".1.3.6.1.2.1.17.7.1.2.2";
    
    $data = @snmprealwalk ($device_name, $community, $oid);
    
        /* return to default behavior */

    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);


    if (empty($data))  {  return;  }
    
    $i = 0;
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+){1}((\.[0-9]+){6})$/i', 
                   $key, 
                   $matches);

            /* Q-BRIDGE-MIB::dot1qTpFdbPort.'..t..P' = 5
             * Q-BRIDGE-MIB::dot1qTpFdbStatus.202.'....92' = 
             *   INTEGER: learned(3)
             *
             * because of OUTPUT_NUMERIC, it's actually returned
             * thusly:
             * 
             * .1.3.6.1.2.1.17.7.1.2.2.1.2.1.0.14.132.0.57.50 == 5
             *
             * the last 6 dotted segments of the oid are the mac
             * address in DECIMAL format, the 7th-to-last segment of
             * the oid is the vlanID, and the value is vlanID
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 2.1.0.14.132.0.57.50
             *     [1] => 2
             *     [2] => 1
             *     [3] => .0.14.132.0.57.50
             *     [4] => .50
             * )
             *
             * $matches[1] == the object: 
             *    1 - dot1qTpFdbAddress : n/a
             *    2 - dot1qTpFdbPort [^1]
             *    3 - dot1qTpFdbStatus
             *
             * $matches[2] is a dot1qFdbId (usually a 1-1 map to vlanID)
             *
             * substr_replace($matches[3], "", 0, 1) strips the
             * leading '.' off $matches[3] to leave just
             * the decimal-format MAC address used as the table index.
             * generate a hex MAC from this val.
             *
             * $value is the dot1d port#
             *
             * [^1] dot1qTpFdbPort can have a value of '0' (zero),
             * indicating the port number hasn't been learned yet
             **/
        
        $idx = substr_replace($matches[3], "", 0, 1);
        
        $octet_list = explode(".", $idx);
        
        $mac = "";
        foreach ($octet_list as $octet)
        {
            $string = dechex($octet);
            if (strlen($string) < 2) {  $string = "0".$string;  }
            
            $mac .= $string." ";
        }
        
        
        if     ($matches[1] === "2")  {  $name = "dot1qTpFdbPort";  }
        elseif ($matches[1] === "3")  {  $name = "dot1qTpFdbStatus";  }
        else                          {  continue;  }

        $FdbTable[$i][$name] = $value;
        $FdbTable[$i]["qFdbId"] = $matches[2];
        $FdbTable[$i]["dot1qTpFdbAddress"] = "\"".$mac."\"";

        $i++;
    }

        /* integrate the $FdbTable into
         * $device["dot1dBridge"]["portTable"][$port] and the
         * $device["interfaces"][$ifIndex]["TpFdb"] table
         **/

    foreach ($FdbTable as $i=>$entry)
    {
            /* accomodate unlearned port#'s - they have a value of 0
             * (zero)
             **/

        if (!isset($entry["dot1qTpFdbPort"])
            ||
            ($entry["dot1qTpFdbPort"] === "0"))
        {
            continue;
        }
        
            /* if $device["dot1dBridge"]["qBridge"]["FdbTable"] has
             * been set, associate the qFdbId with a vlan and add that
             * to the FdbTable entry in the interest of readability
             **/

        $fdbId = &$entry["qFdbId"];  // readability

        if (isset($device["dot1dBridge"]["qBridge"]["FdbTable"][$fdbId]["vlan"]))
        {
            $entry["vlan"] =
                $device["dot1dBridge"]["qBridge"]["FdbTable"][$fdbId]["vlan"];
        }
        
            /* add the FdbTable entry to
             * $device["dot1dBridge"]["portTable"]
             **/

        $port = $entry["dot1qTpFdbPort"];  // readability

        $device["dot1dBridge"]["portTable"][$port]["FdbTable"][] = $entry;
    }
}


    /* .1.3.6.1.2.1.17.7.1 qBridgeMIBObjects
     *   .4 dot1qVlan
     *     .1  dot1qVlanNumDeletes : n/i
     *     .2  dot1qVlanCurrentTable
     *     .3  dot1qVlanStaticTable : n/i
     *     .4  dot1qNextFreeLocalVlanIndex : n/i
     *     .5  dot1qPortVlanTable
     *     .6  dot1qPortVlanStatisticsTable   : n/i
     *     .7  dot1qPortVlanHCStatisticsTable : n/i
     *     .8  dot1qLearningConstraintsTable  : n/i
     *     .9  dot1qConstraintSetDefault  : n/i
     *     .10 dot1qConstraintTypeDefault : n/i
     *
     * FUNCTION
     * get_dot1qVlan ($device_name, $community, &$device)
     * 
     * calls get_dot1qVlanCurrentTable(), get_dot1qPortVlanTable()
     **/


function get_dot1qVlan ($device_name, $community, &$device) 
{
    get_dot1qVlanCurrentTable ($device_name, $community, $device);
    get_dot1qPortVlanTable ($device_name, $community, $device);
}


    /* .1.3.6.1.2.1.17.7.1.4 dot1qVlan
     *   .2 dot1qVlanCurrentTable
     *     .1 dot1qVlanCurrentEntry
     *       .1 dot1qVlanTimeMark : n/a
     *       .2 dot1qVlanIndex    : n/a
     *       .3 dot1qVlanFdbId
     *       .4 dot1qVlanCurrentEgressPorts
     *       .5 dot1qVlanCurrentUntaggedPorts
     *       .6 dot1qVlanStatus
     *       .7 dot1qVlanCreationTime
     *
     * INDEX   { dot1qVlanTimeMark, dot1qVlanIndex }
     *
     * config information for each VLAN
     *
     * FUNCTION
     * get_dot1qVlanCurrentTable ($device_name, $community, &$device)
     *
     * calls get_dot1qVlanFdbId(), get_dot1qVlanCurrentEgressPorts(),
     * get_dot1qVlanCurrentUntaggedPorts(), get_dot1qVlanStatus(),
     * get_dot1qVlanCreationTime()
     **/

function get_dot1qVlanCurrentTable ($device_name, $community, &$device)
{
    get_dot1qVlanFdbId($device_name, $community, $device);
    get_dot1qVlanCurrentEgressPorts($device_name, $community, $device);
    get_dot1qVlanCurrentUntaggedPorts($device_name, $community, $device);
    get_dot1qVlanStatus($device_name, $community, $device);
    get_dot1qVlanCreationTime($device_name, $community, $device);
}


    /* .1.3.6.1.2.1.17.7.1.4.2 dot1qVlanCurrentTable
     *   .1 dot1qVlanCurrentEntry
     *     .3 dot1qVlanFdbId
     *
     * INDEX   { dot1qVlanTimeMark, dot1qVlanIndex }
     *
     * maps FdbId ("forwarding database id") to vlanID.  functionally,
     * they are usually the same.  but since they *can* differ, it's
     * prudent to go through the overhead of mapping.
     *
     * FUNCTION
     * get_dot1qVlanFdbId($device_name, $community, &$device)
     *
     * populates $device["dot1dBridge"]["qBridge"]["FdbTable"][$fdbId]["vlan"]
     * populates $device["dot1dBridge"]["qBridge"]["vlanTable"][$vlan]["fdbId"]
     **/

function get_dot1qVlanFdbId ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid = ".1.3.6.1.2.1.17.7.1.4.2.1.3";

    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /* Q-BRIDGE-MIB::dot1qVlanFdbId.0.1 = INTEGER: 1
             * Q-BRIDGE-MIB::dot1qVlanFdbId.0.202 = INTEGER: 202
             * Q-BRIDGE-MIB::dot1qVlanFdbId.0.205 = INTEGER: 205
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 202
             * )
             *
             * $matches[0] is vlanID
             * $value is the FdbID
             *
             * the additional '.0' in front of the vlanID in the oid is the
             * dot1qVlanTimeMark.  read the mib for details.
             **/

        $device["dot1dBridge"]["qBridge"]["FdbTable"][$value]["vlan"] =
            $matches[0];

        $device["dot1dBridge"]["qBridge"]["vlanTable"][$matches[0]]["fdbId"] =
            $value;
    }
}


    /* .1.3.6.1.2.1.17.7.1.4.2 dot1qVlanCurrentTable
     *   .1 dot1qVlanCurrentEntry
     *     .4 dot1qVlanCurrentEgressPorts
     *
     * INDEX   { dot1qVlanTimeMark, dot1qVlanIndex }
     *
     * set of ports transmitting traffic for this vlan as EITHER
     * tagged or untagged frames.
     *
     * FUNCTION
     * get_dot1qVlanCurrentEgressPorts($device_name, $community, &$device)
     *
     * populates 
     * $device["dot1dBridge"]["qBridge"]["vlanTable"][$vlan]["ports"]
     * $device["dot1dBridge"]["portTable"][$idx]["vlans"][] 
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1qVlanCurrentEgressPorts($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);
    
    $oid = ".1.3.6.1.2.1.17.7.1.4.2.1.4";
    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }


    foreach ($data as $key=>$val)
    {
        if (empty($val))   {  continue;  }

        preg_match('/[0-9]+$/i', $key, $matches);

            /* Q-BRIDGE-MIB::dot1qVlanCurrentEgressPorts.0.251 = 
             *   Hex-STRING: 0F 00 00 00 00 00 00 00 F0 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * FF FF FF 00 00 00 00 00 FF FF FF 00 00 00 00 00
             * FF FF FF 00 00 00 00 00 FF FF FE 00 00 00 00 00
             * 00 
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 251
             * )
             *
             * $matches[0] is vlanID
             *
             * $value is a hex string.  it can be broken into bytes by
             * unpacking the string as unsigned chars.  each bit in
             * each byte represents one *port*, with the most
             * signifigant bit representing the lowest numbered port:
             * the first octet represents ports 1 through 8, the
             * second represents ports 9 through 16, etc.  if a bit
             * has a value of '1' then that port is included in the
             * set of ports; a value of '0' indicates that the port is
             * not included.  
             *
             * the additional '.0' in front of the vlanID in the oid is the
             * dot1qVlanTimeMark.  read the mib for details.  
             **/

        $vlan = $matches[0];
        
        $port_list = array();
        
        $port  = 0;
        $bytes = unpack("C*", $val);

        foreach ($bytes as $i=>$byte)
        {
            for ($a = 8; $a >= 1; $a--)
            {
                $mask = pow(2, $a);  
                
                if (($byte & $mask) === $mask)
                {
                    $idx = $port + (8 - $a);
                    
                        /* add the port to the vlanTable */

                    $device["dot1dBridge"]["qBridge"]["vlanTable"][$vlan]["ports"][] = $idx;
                    
                        /* add the vlan to the portTable */

                    $device["dot1dBridge"]["portTable"][$idx]["vlans"][] = 
                        $matches[0];
                }
            }

            $port = $port + 8;  // move on to the next group of 8 ports
        }
    }
}


    /* .1.3.6.1.2.1.17.7.1.4.2 dot1qVlanCurrentTable
     *   .1 dot1qVlanCurrentEntry
     *     .5 dot1qVlanCurrentUntaggedPorts
     *
     * INDEX   { dot1qVlanTimeMark, dot1qVlanIndex }
     *
     * set of ports transmitting traffic for this vlan as untagged frames.
     *
     * FUNCTION
     * get_dot1qVlanCurrentUntaggedPorts($device_name, $community, &$device)
     *
     * populates 
     * $device["dot1dBridge"]["qBridge"]["vlanTable"][$vlan]["portsUT"]
     * $device["dot1dBridge"]["portTable"][$idx]["vlansUT"][] 
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1qVlanCurrentUntaggedPorts($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);
    
    $oid  = ".1.3.6.1.2.1.17.7.1.4.2.1.5";
    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }


    foreach ($data as $key=>$val)
    {
        if (empty($val))   {  continue;  }

        preg_match('/[0-9]+$/i', $key, $matches);

            /* Q-BRIDGE-MIB::dot1qVlanCurrentUntaggedPorts.0.251 = 
             *   Hex-STRING: 0F 00 00 00 00 00 00 00 F0 00 00 00 00 00 00 00
             * 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
             * FF FF FF 00 00 00 00 00 FF FF FF 00 00 00 00 00
             * FF FF FF 00 00 00 00 00 FF FF FE 00 00 00 00 00
             * 00 
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 251
             * )
             *
             * $matches[0] is vlanID
             *
             * $value is a hex string.  it can be broken into bytes by
             * unpacking the string as unsigned chars.  each bit in
             * each byte represents one *port*, with the most
             * signifigant bit representing the lowest numbered port:
             * the first octet represents ports 1 through 8, the
             * second represents ports 9 through 16, etc.  if a bit
             * has a value of '1' then that port is included in the
             * set of ports; a value of '0' indicates that the port is
             * not included.  
             *
             * the additional '.0' in front of the vlanID in the oid is the
             * dot1qVlanTimeMark.  read the mib for details.  
             **/

        $vlan = $matches[0];
        
        $port_list = array();
        
        $port  = 0;
        $bytes = unpack("C*", $val);

        foreach ($bytes as $i=>$byte)
        {
            for ($a = 8; $a >= 1; $a--)
            {
                $mask = pow(2, $a);  
                
                if (($byte & $mask) === $mask)
                {
                    $idx = $port + (8 - $a);
                    
                        /* add the port to the vlanTable */

                    $device["dot1dBridge"]["qBridge"]["vlanTable"][$vlan]["portsUT"][] = $idx;
                    
                        /* add the vlan to the portTable */

                    $device["dot1dBridge"]["portTable"][$idx]["vlansUT"][] = 
                        $matches[0];
                }
            }

            $port = $port + 8;  // move on to the next group of 8 ports
        }
    }
}


    /* .1.3.6.1.2.1.17.7.1.4.2 dot1qVlanCurrentTable
     *   .1 dot1qVlanCurrentEntry
     *     .6 dot1qVlanStatus
     *
     * 1 == other
     * 2 == permanent
     * 3 == dynamicGvrp
     *
     * INDEX   { dot1qVlanTimeMark, dot1qVlanIndex }
     *
     * reports status of the vlan entry
     *
     * FUNCTION
     * get_dot1qVlanStatus($device_name, $community, &$device)
     *
     * populates 
     * $device["dot1dBridge"]["qBridge"]["vlanTable"][$vlan]["status"]
     **/


function get_dot1qVlanStatus ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.17.7.1.4.2.1.6";
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /* Q-BRIDGE-MIB::dot1qVlanStatus.0.1 = INTEGER: permanent(2)
             * Q-BRIDGE-MIB::dot1qVlanStatus.0.202 = INTEGER: permanent(2)
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 202
             * )
             *
             * $matches[0] is vlanID
             **/

        $device["dot1dBridge"]["qBridge"]["vlanTable"][$matches[0]]["status"] =
            $value;
    }
}


    /* .1.3.6.1.2.1.17.7.1.4.2 dot1qVlanCurrentTable
     *   .1 dot1qVlanCurrentEntry
     *     .7 dot1qVlanCreationTime
     *
     * INDEX   { dot1qVlanTimeMark, dot1qVlanIndex }
     *
     * reports value of sysUpTime when this vlan was created
     *
     * FUNCTION
     * get_dot1qVlanCreationTime($device_name, $community, &$device)
     *
     * populates 
     * $device["dot1dBridge"]["qBridge"]["vlanTable"][$vlan]["created"]
     **/


function get_dot1qVlanCreationTime ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.17.7.1.4.2.1.7";
    $data = @snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);

            /* Q-BRIDGE-MIB::dot1qVlanCreationTime.0.1 = 
             *   Timeticks: (0) 0:00:00.00
             * Q-BRIDGE-MIB::dot1qVlanCreationTime.0.202 = 
             *   Timeticks: (0) 0:00:00.00
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 202
             * )
             *
             * $matches[0] is vlanID
             **/

        $device["dot1dBridge"]["qBridge"]["vlanTable"][$matches[0]]["created"]
            = $value;
    }
}


    /* .1.3.6.1.2.1.17.7.1.4 dot1qVlan
     *   .5 dot1qPortVlanTable
     *     .1 dot1qPortVlanEntry
     *       .1 dot1qPvid
     *       .2 dot1qPortAcceptableFrameTypes
     *       .3 dot1qPortIngressFiltering
     *       .4 dot1qPortGvrpStatus
     *       .5 dot1qPortGvrpFailedRegistrations
     *       .6 dot1qPortGvrpLastPduOrigin
     *
     * INDEX    { dot1dBasePortEntry }
     * AUGMENTS { dot1dBasePortEntry }
     *
     * information controlling VLNA configuration for a port
     *
     * FUNCTION
     * get_dot1qPortVlanTable ($device_name, $community, &$device)
     *
     * calls get_dot1qPvid(), get_dot1qPortAcceptableFrameTypes(),
     * get_dot1qPortIngressFiltering(), get_dot1qPortGvrpStatus(),
     * get_dot1qPortGvrpFailedRegistrations(),
     * get_dot1qPortGvrpLastPduOrigin()
     **/

function get_dot1qPortVlanTable ($device_name, $community, &$device)
{
    get_dot1qPvid($device_name, $community, $device);
    get_dot1qPortAcceptableFrameTypes($device_name, $community, $device);
    get_dot1qPortIngressFiltering($device_name, $community, $device);
    get_dot1qPortGvrpStatus($device_name, $community, $device);
    get_dot1qPortGvrpFailedRegistrations($device_name, $community, $device);
    get_dot1qPortGvrpLastPduOrigin($device_name, $community, $device);
}


    /* .1.3.6.1.2.1.17.7.1.4.5 dot1qPortVlanTable
     *   .1 dot1qPortVlanEntry
     *     .1 dot1qPvid
     *
     * INDEX  { dot1qPort }
     *
     * vlan id assigned to untagged or priority-tagged frames received
     * on a port
     *
     * FUNCTION
     * get_dot1qPvid ($device_name, $community, &$device)
     * 
     * populates 
     * $device["dot1dBridge"]["portTable"][$port]["Pvid"]
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1qPvid ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);
    
    $oid  = ".1.3.6.1.2.1.17.7.1.4.5.1.1";
    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* Q-BRIDGE-MIB::dot1qPvid.1 = INTEGER: 900
             * structure of $matches:
             *
             * Array
             * (
             *     [0] => 1
             * ) 
             *
             * $matches[0] is a port#
             * $value is a vlanID
             **/
        
        $device["dot1dBridge"]["portTable"][$matches[0]]["Pvid"] = $value;
    }
}


    /* .1.3.6.1.2.1.17.7.1.4.5 dot1qPortVlanTable
     *   .1 dot1qPortVlanEntry
     *     .2 dot1qPortAcceptableFrameTypes
     *
     * 1 == admitAll
     * 2 == admitOnlyVlanTagged
     *
     * INDEX  { dot1qPort }
     *
     * how received untagged or priority-tagged frames are treated.
     *
     * FUNCTION
     * get_dot1qPortAcceptableFrameTypes ($device_name, $community, &$device)
     * 
     * populates 
     * $device["dot1dBridge"]["portTable"][$port]["AcceptableFrameTypes"]
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1qPortAcceptableFrameTypes ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.17.7.1.4.5.1.2";
    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* Q-BRIDGE-MIB::dot1qPortAcceptableFrameTypes.3 = 
             *   INTEGER: admitAll(1)
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 3
             * ) 
             *
             * $matches[0] is a port
             **/
        
        $device["dot1dBridge"]["portTable"][$matches[0]]["AcceptableFrameTypes"] = $value;
    }
}


    /* .1.3.6.1.2.1.17.7.1.4.5 dot1qPortVlanTable
     *   .1 dot1qPortVlanEntry
     *     .3 dot1qPortIngressFiltering
     *
     * 1 == true
     * 2 == false
     *
     * INDEX    { dot1dBasePortEntry }
     * AUGMENTS { dot1dBasePortEntry }
     *
     * indicates whether or not incoming frames for VLANs which do not
     * include this port are discarded
     *
     * FUNCTION
     * get_dot1qPortIngressFiltering ($device_name, $community, &$device)
     * 
     * populates 
     * $device["dot1dBridge"]["portTable"][$port]["IngressFiltering"]
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1qPortIngressFiltering ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.17.7.1.4.5.1.3";
    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* Q-BRIDGE-MIB::dot1qPortIngressFiltering.1 = INTEGER: true(1)
             * Q-BRIDGE-MIB::dot1qPortIngressFiltering.2 = INTEGER: true(1)
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 3
             * ) 
             *
             * $matches[0] is a port
             **/
        
        $device["dot1dBridge"]["portTable"][$matches[0]]["IngressFiltering"] =
            $value;
    }
}


    /* .1.3.6.1.2.1.17.7.1.4.5 dot1qPortVlanTable
     *   .1 dot1qPortVlanEntry
     *     .4 dot1qPortGvrpStatus
     *
     * 1 == enabled
     * 2 == disabled
     *
     * INDEX    { dot1dBasePortEntry }
     * AUGMENTS { dot1dBasePortEntry }
     *
     * state of GVRP operation on a port
     *
     * FUNCTION
     * get_dot1qPortGvrpStatus ($device_name, $community, &$device)
     * 
     * populates 
     * $device["dot1dBridge"]["portTable"][$port]["GvrpStatus"]
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1qPortGvrpStatus ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.17.7.1.4.5.1.4";
    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* Q-BRIDGE-MIB::dot1qPortGvrpStatus.258 = INTEGER: disabled(2)
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 258
             * )
             *
             * $matches[0] is a port 
             **/

        $device["dot1dBridge"]["portTable"][$matches[0]]["GvrpStatus"] =
            $value;
    }
}


    /* .1.3.6.1.2.1.17.7.1.4.5 dot1qPortVlanTable
     *   .1 dot1qPortVlanEntry
     *     .5 dot1qPortGvrpFailedRegistrations
     *
     * 1 == enabled
     * 2 == disabled
     *
     * INDEX    { dot1dBasePortEntry }
     * AUGMENTS { dot1dBasePortEntry }
     *
     * state of GVRP operation on a port
     *
     * FUNCTION
     * get_dot1qPortGvrpFailedRegistrations ($device_name, $community, 
     *                                       &$device)
     * 
     * populates 
     * $device["dot1dBridge"]["portTable"][$port]["FailedRegistrations"]
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1qPortGvrpFailedRegistrations ($device_name, 
                                               $community, 
                                               &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.17.7.1.4.5.1.5";
    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* Q-BRIDGE-MIB::dot1qPortGvrpFailedRegistrations.397 = 
             *   Counter32: 0
             * Q-BRIDGE-MIB::dot1qPortGvrpFailedRegistrations.398 = 
             *   Counter32: 0
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 397
             * )
             *
             * $matches[0] is a port 
             **/

        $device["dot1dBridge"]["portTable"][$matches[0]]["FailedRegistrations"] = $value;
    }
}


    /* .1.3.6.1.2.1.17.7.1.4.5 dot1qPortVlanTable
     *   .1 dot1qPortVlanEntry
     *     .6 dot1qPortGvrpLastPduOrigin
     *
     * INDEX    { dot1dBasePortEntry }
     * AUGMENTS { dot1dBasePortEntry }
     *
     * source MAC of the last GVRP message
     *
     * FUNCTION
     * get_dot1qPortGvrpLastPduOrigin ($device_name, $community, 
     *                                       &$device)
     * 
     * populates 
     * $device["dot1dBridge"]["portTable"][$port]["LastPduOrigin"]
     *
     * if get_dot1dBasePortIfIndex() was called previously, a pointer
     * exists: $device["interfaces"][$ifIndex]["dot1dBridge"] =>
     * $device["dot1dBridge"]["portTable"][$dot1dPort];
     **/

function get_dot1qPortGvrpLastPduOrigin ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = ".1.3.6.1.2.1.17.7.1.4.5.1.6";
    $data = @snmprealwalk ($device_name, $community, $oid);

    if (empty($data))  {  return;  }

    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* Q-BRIDGE-MIB::dot1qPortGvrpLastPduOrigin.1 = STRING: 0:0:0:0:0:0
             * Q-BRIDGE-MIB::dot1qPortGvrpLastPduOrigin.2 = STRING: 0:0:0:0:0:0
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 397
             * )
             *
             * $matches[0] is a port 
             **/

        $octet_list = explode(":", $value);
        
        $mac = "";
        foreach ($octet_list as $octet)
        {
            if (strlen($octet) < 2) {  $octet = "0".$octet;  }
            $mac .= $octet." ";
        }
        
        $device["dot1dBridge"]["portTable"][$matches[0]]["LastPduOrigin"] = 
            "\"".$mac."\"";
    }
}

?>
