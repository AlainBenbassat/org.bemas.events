<?php

class CRM_Events_BemasSurvey {
  private $offsetInDays = 7;

  public function createForAllEvents() {
    $eventIds = $this->getUpcomingEvents();
    foreach ($eventIds as $eventId) {
      $this->createForEvent($eventId);
    }
  }

  public function createForEvent($eventId) {
    $event = civicrm_api3('Event', 'getsingle', ['event_id' => $eventId]);

    $surveyType = $this->getSurveyTypeFromEventType($event['event_type_id']);
    if ($surveyType) {
      $this->createSurveyWebformForEvent($surveyType, $event);
    }
  }

  private function createSurveyWebformForEvent($surveyType, $event) {
    $eventCode = $this->getEventCode($event['title']);
    $eventLanguage = $this->getEventLanguage($eventCode);
    $node = $this->cloneSurvey($surveyType);

    $node->title = $event['title' . $this->getEventLanguage()];
  }

  private function getEventCode($title) {
    $n = strstr($title, ' - ');
    if ($n === FALSE) {
      throw new Exception("Cannot extract event code from event: $title");
    }

    return substr($title, 0, $n);
  }

  private function getEventLanguage($eventCode) {
    $lastLetter = substr($eventCode, -1);
    return $lastLetter;
  }

  private function cloneSurvey($surveyType) {
    // get the template
    $webformNode = $this->loadDrupalWebformByTitle('TEMPLATE ' . $surveyType);

    // blank out some fields
    $webformNode->nid = NULL;
    $webformNode->vid = NULL;
    $webformNode->tnid = NULL;
    $webformNode->log = NULL;
    $webformNode->uuid = NULL;
    $webformNode->vuuid = NULL;
    $webformNode->created = NULL;

    node_save($webformNode);
  }

  private function loadDrupalWebformByTitle($title) {
    $query = new EntityFieldQuery();

    $entities = $query->entityCondition('entity_type', 'node')
      ->propertyCondition('type', 'webform')
      ->propertyCondition('title', $title)
      ->propertyCondition('status', 1)
      ->range(0,1)
      ->execute();

    if (empty($entities['node'])) {
      throw new Exception("Cannot find webform with title: $title");
    }

    $arr = array_keys($entities['node']);
    $node = node_load(array_shift($arr));

    return $node;
  }

  private function getSurveyTypeFromEventType($eventTypeId) {
    $eventTypeSurveyMapping = [
      9	=> 'A', // Online opleiding
      4	=> 'A', // Opleiding
      5 => 'B', // Opleidingsreeks
      1 => 'C', // Studiesessie
      18 => 'A', //	Toepassingstraject
      15 => 'C', // Webinar
    ];

    if (array_key_exists($eventTypeId, $eventTypeSurveyMapping)) {
      return $eventTypeSurveyMapping[$eventTypeId];
    }
    else {
      // return blank
      // we don't throw an exception because we want to ignore event types for which no survey is needed
      return '';
    }
  }

  private function getUpcomingEvents() {

  }

}
