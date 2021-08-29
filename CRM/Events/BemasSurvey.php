<?php

class CRM_Events_BemasSurvey {
  private $offsetInDays = 7;

  public function createForAllEvents() {
    /*
    $eventIds = $this->getUpcomingEvents();
    foreach ($eventIds as $eventId) {
      $this->createForEvent($eventId);
    }
    */
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
      $eventSurvey->modules = $this->getEventModules($eventId);
      $surveyNids = $eventSurvey->create();
    }
    else {
      $surveyNids = FALSE;
    }

    return $surveyNids;
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
        (p.role_id like '%4%' or p.role_id like '%6%')
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $speakers[$dao->id] = $dao->speaker;
    }

    return $speakers;
  }

  private function getEventModules($eventId) {
    $modules = [];

    $priceSetId = $this->getEventPriceSetId($eventId);
    if ($priceSetId) {
      $priceFieldId = $this->getPriceFieldIdOfModules($priceSetId);
      if ($priceFieldId) {
        $modules = $this->getPriceFieldValues($priceFieldId);
      }
    }

    return $modules;
  }

  private function getEventPriceSetId($eventId) {
    $sql = "select price_set_id from civicrm_price_set_entity where entity_id = $eventId and entity_table = 'civicrm_event'";
    return CRM_Core_DAO::singleValueQuery($sql);
  }

  private function getPriceFieldIdOfModules($priceSetId) {
    $sql = "select min(id) from civicrm_price_field where price_set_id  = $priceSetId and name like 'modules%' and is_active = 1";
    return CRM_Core_DAO::singleValueQuery($sql);
  }

  private function getPriceFieldValues($priceFieldId) {
    $modules = [];
    $sql = "select id, label module_name from civicrm_price_field_value where price_field_id = $priceFieldId order by 2";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $modules[$dao->id] = $dao->module_name;
    }

    return $modules;
  }

  private function getUpcomingEvents() {
    throw new Exception("TODO getUpcomingEvents()");
  }

}
