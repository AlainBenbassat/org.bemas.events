<?php

class CRM_Events_DrupalWebform {
  public $eventId = 0;
  public $eventTitle = '';
  public $templateType = '';
  public $language = '';
  public $speakers = [];

  public function create() {
    if ($this->hasEventSurvey()) {
      return FALSE;
    }

    $surveyNids = [];
    $surveyNids['participant_survey_nid'] = $this->createParticipantEventSurvey();
    if ($this->templateType == 'A') {
      $surveyNids['trainer_survey_nid'] = $this->createTrainerEventSurvey();
    }

    return $surveyNids;
  }

  private function createParticipantEventSurvey() {
    $nodeTemplate = $this->getTemplateNodeByTitle($this->getParticipantTemplateTitle());
    $eventSurvey = clone $nodeTemplate;
    $this->wipeNodeFields($eventSurvey);
    $this->setNodeTitle($eventSurvey, '');
    $this->setNodeEventId($eventSurvey);
    $this->setNodeSpeakers($eventSurvey);

    $nid = $this->saveNode($eventSurvey, $nodeTemplate);
    return $nid;
  }

  private function createTrainerEventSurvey() {
    $nodeTemplate = $this->getTemplateNodeByTitle($this->getTrainerTemplateTitle());
    $eventSurvey = clone $nodeTemplate;
    $this->wipeNodeFields($eventSurvey);
    $this->setNodeTitle($eventSurvey, 'Trainer ');
    $this->setNodeEventId($eventSurvey);

    $nid = $this->saveNode($eventSurvey, $nodeTemplate);
    return $nid;
  }

  private function hasEventSurvey() {
    // if a survey already exists, there must be a form component with a form key containing the event id
    $eventFormKey = $this->getEventFormKey();

    $result = db_select('webform_component', 'wc')
      ->fields('wc', ['nid'])
      ->condition('form_key', $eventFormKey, '=')
      ->execute()
      ->fetchAssoc();

    if ($result === FALSE) {
      return FALSE;
    }
    else {
      return TRUE;
    }
  }

  private function getEventFormKey() {
    $formKey = 'evalform_event_id_' . $this->eventId;

    return $formKey;
  }

  private function getSpeakerFormKey($contactId) {
    $formKey = 'evalform_speaker_id_' . $contactId;

    return $formKey;
  }

  private function getParticipantTemplateTitle() {
    if ($this->templateType == 'A') {
      $title = 'TEMPLATE ' . $this->templateType . ' - Evaluatie opleidingen ' . $this->language;
    }
    elseif ($this->templateType == 'B') {
      throw new Exception(('Templage B is not implemented yet'));
    }
    elseif ($this->templateType == 'C') {
      $title = 'TEMPLATE ' . $this->templateType . ' - Evaluatie webinars en studiesessies ' . $this->language;
    }

    return $title;
  }

  private function getTrainerTemplateTitle() {
    $title = 'TEMPLATE L1 - Evaluatie lesgever ' . $this->language;

    return $title;
  }

  private function getTemplateNodeByTitle($title) {
    $nodes = node_load_multiple(NULL, ['title' => $title]);
    $node = current($nodes);

    return $node;
  }

  private function wipeNodeFields(&$node) {
    $node->nid = NULL;
    $node->vid = NULL;
    $node->tnid = NULL;
    $node->log = NULL;
    $node->uuid = NULL;
    $node->vuuid = NULL;
    $node->created = NULL;
    $node->path = NULL;
    $node->files = [];
  }

  private function setNodeTitle(&$node, $prefix) {
    $node->title = $prefix . $this->eventTitle;
  }

  private function setNodeEventId(&$node) {
    for ($i = 1; $i <= count($node->webform['components']); $i++) {
      if ($node->webform['components'][$i]['form_key'] == 'evalform_event_id') {
        $node->webform['components'][$i]['form_key'] = $this->getEventFormKey();
        $node->webform['components'][$i]['value'] = $this->eventId;
      }
    }
  }

  private function setNodeSpeakers($node) {
    $numSpeakers = count($this->speakers);
    if ($numSpeakers == 0) {
      $this->removeSpeakerFields($node);
    }
    else {
      $this->addSpeakerFields($node);
    }
  }

  private function removeSpeakerFields(&$node) {
    $i = $this->getSpeakerComponentIndex($node->webform['components']);
    unset($node->webform['components'][$i]);
  }

  private function getSpeakerComponentIndex($arr) {
    for ($i = 1; $i <= count($arr); $i++) {
      if (substr($arr[$i]['form_key'], -10) == '_speaker_a') {
        return $i;
      }
    }
  }

  private function addSpeakerFields(&$node) {
    $speakerIndex = $this->getSpeakerComponentIndex($node->webform['components']);

    $speakerComponents = [];
    $n = 0;
    foreach ($this->speakers as $speakerId => $speakerName) {
      // clone the speaker component
      $speakerComponent = array_merge([], $node->webform['components'][$speakerIndex]);

      $speakerComponent['form_key'] = $this->getSpeakerFormKey($speakerId);
      $speakerComponent['name'] = $this->fillInSpeakerName($speakerComponent['name'], $speakerName, $n);
      $speakerComponents[] = $speakerComponent;
      $n++;
    }

    $this->replaceSpeakerComponent($node, $speakerIndex, $speakerComponents);
  }

  private function replaceSpeakerComponent(&$node, $speakerIndex, $speakerComponents) {
    $newComponentsArray = [];

    $n = 1;
    foreach ($node->webform['components'] as $component) {
      if ($n == $speakerIndex) {
        foreach ($speakerComponents as $newSpeaker) {
          $newComponentsArray[$n] = $newSpeaker;
          $newComponentsArray[$n]['cid'] = $n;
          $n++;
        }
      }
      else {
        $newComponentsArray[$n] = $component;
        $newComponentsArray[$n]['cid'] = $n;
        $n++;
      }
    }

    $node->webform['components'] = $newComponentsArray;
  }

  private function fillInSpeakerName($origText, $speakerName, $n) {
    $newName = str_replace('XXX YYY', $speakerName, $origText);

    // replace a. with a. or b. or c. or d. ...
    $letterIndex = strpos($newName, 'a. ');
    $newName = substr($newName, 0, $letterIndex) . chr(97 + $n) . '. ' . substr($newName, $letterIndex + 3);

    return $newName;
  }

  private function saveNode($node, $nodeTemplate) {
    $context = array('method' => 'save-edit', 'original_node' => $nodeTemplate);
    drupal_alter('clone_node', $node, $context);

    node_save($node);
    return $node->nid;
  }
}
