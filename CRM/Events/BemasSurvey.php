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
    $event = $this->getEvent($eventId);
    $surveyType = $this->getSurveyTypeFromEventType($event['event_type_id']);

    if ($surveyType) {
      $eventCode = $this->getEventCode($event['title']);

      $eventSurvey = new CRM_Events_DrupalWebform();
      $eventSurvey->eventId = $eventId;
      $eventSurvey->eventTitle = $event['title'];
      $eventSurvey->templateType = $surveyType;
      $eventSurvey->language = $this->getEventLanguage($eventCode);
      $eventSurvey->speakers = $this->getEventSpeakers($eventId);
      $eventSurvey->create();
    }
  }

  private function getEventCode($title) {
    $n = strpos($title, ' - ');
    if ($n === FALSE) {
      throw new Exception("Cannot extract event code from event: $title");
    }

    return substr($title, 0, $n);
  }

  private function getEventLanguage($eventCode) {
    $lastLetter = substr($eventCode, -1);

    if ($lastLetter == 'V') {
      return 'NL';
    }

    if ($lastLetter == 'W') {
      return 'FR';
    }

    if ($lastLetter == 'N') {
      return 'EN';
    }

    throw new Exception("Cannot extract the language from event code: $eventCode");
  }

  private function getEvent($eventId) {
    $params = [
      'id' => $eventId,
      'sequential' => 1,
    ];
    $event = civicrm_api3('Event', 'getsingle', $params);

    return $event;
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

  private function getEventSpeakers($eventId) {
    $speakers = [];

    $sql = "
      select
        c.id
        , concat(c.first_name, ' ', c.last_name) speaker
      from
        civicrm_event e
      inner join
        civicrm_participant p on e.id = p.event_id
      inner join
        civicrm_contact c on c.id = p.contact_id
      where
        e.id = $eventId
      and
        p.role_id in (4, 6)
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $speakers[$dao->id] = $dao->speaker;
    }

    return $speakers;
  }

  private function getUpcomingEvents() {
    throw new Exception("TODO getUpcomingEvents()");
  }

}
