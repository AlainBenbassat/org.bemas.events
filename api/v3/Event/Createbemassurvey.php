<?php

function _civicrm_api3_event_createbemassurvey_spec(&$spec) {
  $spec['event_id']['api.required'] = 0;
}

function civicrm_api3_event_createbemassurvey($params) {
  try {
    $msg = 'Created survey for ';
    $surveyCreator = new CRM_Events_BemasSurvey();
    $eventId = _civicrm_api3_event_createbemassurvey_getEventID($params);

    if ($eventId) {
      $msg .= 'event ' . $eventId;
      $surveyCreator->createForEvent($eventId);
    }
    else {
      $msg .= 'all events';
      $surveyCreator->createForAllEvents();
    }

    return civicrm_api3_create_success($msg, $params, 'Event', 'Createbemassurvey');
  }
  catch (Exception $e) {
    throw new API_Exception($e->getMessage(), $e->getCode());
  }
}

function _civicrm_api3_event_createbemassurvey_getEventID($params) {
  return CRM_Utils_Array::value('event_id', $params);
}
