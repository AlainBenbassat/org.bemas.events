<?php

class CRM_Events_Survey {
  public function generateForAllEvents() {

  }

  public function generateForEvent($eventId) {
    $event = $this->getEvent($eventId);
    $templateType = $this->getTemplateTypeFromEventType($event['event_type_id']);

    if ($templateType) {
      $template = new CRM_Events_WebformTemplate();
      $template->eventId = $eventId;
      $template->templateType = $templateType;
      $template->language = $this->getEventLanguage($event['title']);
      $template->speakers = $this->getEventSpeakers($eventId);
      $template->create();
    }
  }

  private function getEvent($eventId) {
    $params = [
      'id' => $eventId,
    ];
    $event = civicrm_api3('Event', 'getsingle', $params);

    return $event;
  }

  private function getTemplateTypeFromEventType($eventTypeId) {
    $templates = [
      9	=> 'A', //Online opleiding
      4	=> 'A', //Opleiding
      5	=> 'B', //Opleidingsreeks
      1 => 'C', // Studiesessie
      18 => 'A', // Toepassingstraject
      15 => 'C', // Webinar
    ];

    if (array_key_exists($eventTypeId, $templates)) {
      return $templates[$eventTypeId];
    }
    else {
      return '';
    }
  }

  private function getEventLanguage($title) {
    $titleParts = explode(' - ', $title);

    if (count($titleParts) > 1) {
      // the language is encoded in the last letter of the event code
      $lastLetter = substr($titleParts[0], -1);

      if ($lastLetter == 'V') {
        return 'NL';
      }

      if ($lastLetter == 'W') {
        return 'FR';
      }

      if ($lastLetter == 'N') {
        return 'EN';
      }
    }

    throw new Exception("Cannot extract the language from event title: $title");
  }

  private function getEventSpeakers($eventId) {
    return [1 => 'TODO', 2 => 'Jos Peeters', 3 => 'Wendy Willems'];
  }
}
