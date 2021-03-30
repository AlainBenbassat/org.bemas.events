<?php

class CRM_Events_BemasEvent {
  public static function processHookCustom($groupID, $entityID, $params) {
    if (self::isEventEvaluationGroup($groupID) && self::shouldCreateSurvey($entityID)) {
      $survey = new CRM_Events_BemasSurvey();
      $surveyNids = $survey->createForEvent($entityID);
      if ($surveyNids === FALSE) {
        // surveys already created, do nothing
      }
      else {
        self::addSurveyLinks($entityID, $surveyNids);
      }
    }
  }

  private static function isEventEvaluationGroup($groupID) {
    if ($groupID == 27) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private static function shouldCreateSurvey($eventId) {
    if (self::hasCreateSurveyFlag($eventId) && self::hasEmptySurveyLinks($eventId)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private static function hasCreateSurveyFlag($eventId) {
    $retval = FALSE;

    $result = civicrm_api3('CustomValue', 'get', [
      'entity_id' => $eventId,
      'return' => 'custom_154',
      'sequential' => 1,
    ]);

    if ($result['count'] > 0) {
      if ($result['values'][0]['latest'] == 1) {
        $retval = TRUE;
      }
    }

    return $retval;
  }

  private static function hasEmptySurveyLinks($eventId) {
    $retval = TRUE;

    $result = civicrm_api3('CustomValue', 'get', [
      'entity_id' => $eventId,
      'return' => 'custom_155',
      'sequential' => 1,
    ]);

    if ($result['count'] > 0) {
      if (strlen($result['values'][0]['latest']) > 0) {
        $retval = FALSE;
      }
    }

    return $retval;
  }

  public static function addSurveyLinks($eventId, $surveyNids) {
    // participant
    if (!empty($surveyNids['participant_survey_nid'])) {
      civicrm_api3('CustomValue', 'create', [
        'entity_id' => $eventId,
        'custom_155' => self::getUrlFromNid($surveyNids['participant_survey_nid']),
      ]);
    }

    // trainer
    if (!empty($surveyNids['trainer_survey_nid'])) {
      civicrm_api3('CustomValue', 'create', [
        'entity_id' => $eventId,
        'custom_156' => self::getUrlFromNid($surveyNids['trainer_survey_nid']),
      ]);
    }
  }

  private static function getUrlFromNid($nid) {
    $url = url('node/' . $nid, ['absolute' => FALSE]);
    return '<a href="' . $url . '">' . $url . '</a>';
  }
}
