<?php

function _civicrm_api3_event_generatesurvey_spec(&$spec) {
  $spec['event_id']['api.required'] = 0;
  $spec['starts_within_num_days']['api.required'] = 0;
}

function civicrm_api3_event_generatesurvey($params) {
  try {
    $surveyGenerator = new CRM_Events_Survey();

    $eventId = _civicrm_api3_contact_generatesurvey_getEventID($params);
    if ($eventId) {
      $surveyGenerator->generateForEvent($eventId);
    }
    else {
      $surveyGenerator->generateForAllEvents();
    }

    $return = 'TODO'; // TODO
    return civicrm_api3_create_success($return, $params, 'Event', 'Generatesurvey');
  } catch (Exception $e) {
    throw new API_Exception($e->getMessage(), $e->getCode());
  }
}

function _civicrm_api3_contact_generatesurvey_getEventID($params) {
  return CRM_Utils_Array::value('event_id', $params);
}


