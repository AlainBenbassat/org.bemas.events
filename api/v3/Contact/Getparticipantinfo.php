<?php

function _civicrm_api3_contact_getparticipantinfo_spec(&$spec) {
  $spec['email']['api.required'] = 1;
}

function civicrm_api3_contact_getparticipantinfo($params) {
  if (!array_key_exists('email', $params)) {
    throw new API_Exception('Missing required parameter: contact_id', -1);
  }

  // select the contact
  $sql = "
    SELECT
      c.id,
      c.employer_id,
      c.organization_name,
      (select count(*) from civicrm_membership m where m.contact_id = c.id and m.status_id in (1, 2, 3)) membership_count
    FROM
      civicrm_contact c
    INNER JOIN
      civicrm_email e on c.id = e.contact_id
    WHERE
      c.contact_type = 'Individual'
    AND
      e.email = %1
    AND
      c.is_deleted = 0
  ";
  $sqlParams = [
    1 => [trim($params['email']), 'String'],
  ];
  $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);
  if ($dao->N == 1) {
    $dao->fetch();
    $contact['id'] = $dao->id;
    $contact['employer_id'] = $dao->employer_id;
    $contact['organization_name'] = $dao->organization_name;
    $contact['membership_count'] = $dao->membership_count;

    $contact['main_address'] = CRM_Events_BemasParticipant::getAddress($dao->employer_id, 'main');
    $contact['billing_address'] = CRM_Events_BemasParticipant::getAddress($dao->employer_id, 'billing');
    $contact['vat'] = CRM_Events_BemasParticipant::getVat($dao->employer_id);

    $return[0] = $contact;

    return civicrm_api3_create_success($return, $params, NULL, NULL);
  }
  elseif ($dao->N == 0) {
    throw new API_Exception('Contact not found', -1);
  }
  else {
    throw new API_Exception('More than one contact found (n = ' . $dao->N . ')', -1);
  }
}

