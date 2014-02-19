<?php  /* -*- c -*- */

    /** mib-2.ENTITYMIB **/


      /* .1.3.6.1.2.1 mib-2
       *   .47 entityMIB
       *     .1 entityMIBObjects
       *        .1 entityPhysical
       *        .2 entityLogical
       *        .3 entityMapping : n/i
       *        .4 entityGeneral : n/i
       *
       * FUNCTION
       * get_entityMIB ($device_name, $community, &$device)
       *
       * calls get_entityPhysical(), get_entityLogical()
       **/


function get_entityMIB ($device_name, $community, &$device)
{
    get_entityPhysical ($device_name, $community, $device);
    get_entityLogical ($device_name, $community, $device);
}


      /* .1.3.6.1.2.1.47.1 entityMIBObjects
       *   .1  entityPhysical
       *     .1  entPhysicalTable
       *       .1  entPhysicalEntry
       *         .1 entPhysicalIndex          
       *           .2 entPhysicalDescr          
       *           .3 entPhysicalVendorType     
       *           .4 entPhysicalContainedIn    
       *           .5 entPhysicalClass          
       *           .6 entPhysicalParentRelPos   
       *           .7 entPhysicalName           
       *           .8 entPhysicalHardwareRev    
       *           .9 entPhysicalFirmwareRev    
       *           .10 entPhysicalSoftwareRev    
       *           .11 entPhysicalSerialNum      
       *           .12 entPhysicalMfgName        
       *           .13 entPhysicalModelName      
       *           .14 entPhysicalAlias          
       *           .15 entPhysicalAssetID        
       *           .16 entPhysicalIsFRU          
       *
       * FUNCTION 
       * get_entityPhysical ($device_name, $community, &$device)
       *
       * populates $device["entity"][$entity]["physical"][$oid]
       **/

function get_entityPhysical ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.47.1.1";
    $data = snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
        
            /* ENTITY-MIB::entPhysicalContainedIn.4054 = INTEGER: 4000
             * ENTITY-MIB::entPhysicalContainedIn.4055 = INTEGER: 4000
             * ENTITY-MIB::entPhysicalClass.1 = INTEGER: chassis(3)
             * ENTITY-MIB::entPhysicalClass.2 = INTEGER: container(5)
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => entPhysicalDescr.10
             *     [1] => entPhysicalDescr
             *     [2] => 10
             * )
             * 
             * $matches[1] is the oid, $matches[2] is the entity
             **/
        
        $device["entity"][$matches[2]]["physical"][$matches[1]] = $value;
    }
}


      /* .1.3.6.1.2.1.47.1 entityMIBObjects
       *   .2  entityLogical
       *     .1 entLogicalTable
       *       .1 entLogicalEntry
       *         .1 entLogicalIndex      
       *         .2 entLogicalDescr      
       *         .3 entLogicalType       
       *         .4 entLogicalCommunity  
       *         .5 entLogicalTAddress   
       *         .6 entLogicalTDomain    
       *         .7 entLogicalContextEngineID
       *         .8 entLogicalContextName    
       *
       * FUNCTION 
       * get_entityLogical ($device_name, $community, &$device)
       *
       * populates $device["entity"][$entity]["logical"][$oid]
       **/

function get_entityLogical ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid  = "1.3.6.1.2.1.47.1.2";
    $data = snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/([A-Z0-9]+)\.([0-9]+)$/i', $key, $matches);
        
            /* ENTITY-MIB::entLogicalType.54 = OID: BRIDGE-MIB::dot1dBridge
             * ENTITY-MIB::entLogicalType.55 = OID: BRIDGE-MIB::dot1dBridge
             * ENTITY-MIB::entLogicalCommunity.1 = STRING: "sekret@1"
             * ENTITY-MIB::entLogicalCommunity.2 = STRING: "sekret@207"
             * ENTITY-MIB::entLogicalCommunity.3 = STRING: "sekret@414"
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => entLogicalType.10
             *     [1] => entLogicalType
             *     [2] => 10
             * )
             * 
             * $matches[1] is the oid, $matches[2] is the entity
             */
        
        $device["entity"][$matches[2]]["logical"][$matches[1]] = $value;
    }
}
 

      /* .1.3.6.1.2.1.47.1 entityMIBObjects
       *   .2  entityLogical
       *     .1 entLogicalTable
       *       .1 entLogicalEntry
       *         .3 entLogicalType       
       *
       * FUNCTION 
       * get_entityLogicalType ($device_name, $community, &$device)
       *
       * populates $device["entity"][$entity]["logical"]["entLogicalType"]
       **/

function get_entLogicalType ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid = ".1.3.6.1.2.1.47.1.2.1.1.3";
    $data = snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* ENTITY-MIB::entLogicalType.54 = OID: BRIDGE-MIB::dot1dBridge
             * ENTITY-MIB::entLogicalType.55 = OID: BRIDGE-MIB::dot1dBridge
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 12
             * )
             *
             * $matches[0] is the logical entity id
             */
        
        $device["entity"][$matches[0]]["logical"]["entLogicalType"] = $value;
    }
}


      /* .1.3.6.1.2.1.47.1 entityMIBObjects
       *   .2  entityLogical
       *     .1 entLogicalTable
       *       .1 entLogicalEntry
       *         .4 entLogicalCommunity
       *
       * FUNCTION 
       * get_entLogicalCommunity ($device_name, $community, &$device)
       *
       * populates $device["entity"][$entity]["logical"]["entLogicalCommunity"]
       **/

function get_entLogicalCommunity ($device_name, $community, &$device)
{
    snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_FULL);
    snmp_set_quick_print(TRUE);

    $oid = ".1.3.6.1.2.1.47.1.2.1.1.4";
    $data = snmprealwalk ($device_name, $community, $oid);
    
    if (empty($data))  {  return;  }
    
    foreach ($data as $key=>$value) 
    {
        preg_match('/[0-9]+$/i', $key, $matches);
        
            /* ENTITY-MIB::entLogicalCommunity.1 = STRING: "sekret@1"
             * ENTITY-MIB::entLogicalCommunity.2 = STRING: "sekret@207"
             * ENTITY-MIB::entLogicalCommunity.3 = STRING: "sekret@414"
             *
             * structure of $matches:
             * Array
             * (
             *     [0] => 12
             * )
             *
             * $matches[0] is the logical entity id
             */
        
        $device["entity"][$matches[0]]["logical"]["entLogicalCommunity"] = 
            $value;
    }
}


      /* .1.3.6.1.2.1.47.1 entityMIBObjects
       *   .3 entityMapping
       *     .1 entLPMappingTable 
       *     .2 entAliasMappingTable
       *     .3 entPhysicalContainsTable
       *       .1 entPhysicalContainsEntry
       *         .1 entPhysicalChildIndex
       **/

      /* .1.3.6.1.2.1.47.1.3 entityMapping
       *   .1 entLPMappingTable 
       **/

      /* .1.3.6.1.2.1.47.1.3 entityMapping
       *   .2 entAliasMappingTable
       *     .1 entAliasMappingEntry
       *       .1 entAliasLogicalIndexOrZero
       *       .2 entAliasMappingIdentifier
       *
       * INDEX { entPhysicalIndex, entAliasLogicalIndexOrZero }
       * 
       * quoth the mib: "This table contains zero or more rows,
       * representing mappings of logical entity and physical
       * component to external MIB identifiers.  Each physical port in
       * the system may be associated with a mapping to an external
       * identifier, which itself is associated with a particular
       * logical entity's naming scope.  A 'wildcard' mechanism is
       * provided to indicate that an identifier is associated with
       * more than one logical entity."
       *
       **/

      /* .1.3.6.1.2.1.47.1.3 entityMapping
       *   .3 entPhysicalContainsTable
       *     .1 entPhysicalContainsEntry
       *       .1 entPhysicalChildIndex
       *
       * "A table which exposes the container/'containee'
       * relationships between physical entities. This table provides
       * all the information found by constructing the virtual
       * containment tree for a given entPhysicalTable, but in a more
       * direct format.
       *
       * In the event a physical entity is contained by more than one
       * other physical entity (e.g., double-wide modules), this table
       * should include these additional mappings, which cannot be
       * represented in the entPhysicalTable virtual containment
       * tree."
       *
       **/


      /* .1.3.6.1.2.1.47.1 entityMIBObjects
       *   .4  entityGeneral
       *     .1 entLastChangeTime
       **/


?>
