<?php

function _civicrm_api3_event_createbemassurvey_spec(&$spec) {
  $spec['event_id']['api.required'] = 0;
}

function civicrm_api3_event_createbemassurvey($params) {
  try {
    $msg = 'Created survey for ';
    $surveyCreator = new CRM_Events_BemasSurvey();

    if (array_key_exists('event_id', $params)) {
      $msg .= 'event ' . $params['event_id'];
      $surveyCreator->createForEvent($params['event_id']);
    }
    else {
      $msg .= 'all events';
      $surveyCreator->createForAllEvents();
    }

    return civicrm_api3_create_success($msg, $params, NULL, NULL);
  }
  catch (Exception $e) {
    throw new API_Exception($e->getMessage(), -1);
  }
}
