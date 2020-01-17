<?php

class CRM_Events_BemasParticipant {
  public static function getAddress($orgID, $addressType) {
    $address = '';
    $locationTypeID = 0;

    if ($addressType == 'main') {
      $locationTypeID = 3;
    }
    elseif ($addressType == 'billing') {
      $locationTypeID = 5;
    }

    if ($orgID > 0 && $locationTypeID > 0) {
      $adr = civicrm_api3('Address', 'get', [
        'sequential' => 1,
        'contact_id' => $orgID,
        'location_type_id' => $locationTypeID,
      ]);
      if ($adr['count'] > 0) {
        if ($adr['values'][0]['street_address']) {
          $address .= $adr['values'][0]['street_address']  . "\n";
        }
        if ($adr['values'][0]['supplemental_address_1']) {
          $address .= $adr['values'][0]['supplemental_address_1']  . "\n";
        }
        if ($adr['values'][0]['supplemental_address_2']) {
          $address .= $adr['values'][0]['supplemental_address_2']  . "\n";
        }
        $address .= trim($adr['values'][0]['postal_code'] . ' ' . $adr['values'][0]['city'])  . "\n";
      }
    }

    return $address;
  }

  public static function getVat($orgID) {
    $customValues = civicrm_api3('CustomValue', 'get', [
      'sequential' => 1,
      'entity_id' => $orgID,
    ]);

    foreach ($customValues['values'] as $v) {
      if ($v['id'] == 11) {
        if ($v['latest']) {
          return $v['latest'];
        }
        else {
          return '';
        }
      }
    }

    return '';
  }
}
