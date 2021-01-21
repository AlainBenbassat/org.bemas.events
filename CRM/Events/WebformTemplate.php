<?php

class CRM_Events_WebformTemplate {
  public $eventId = 0;
  public $templateType = '';
  public $language = '';
  public $speakers = [];

  public function create() {
    $result = db_select('node', 'n')
      ->fields('n', ['nid'])
      ->condition('title', 'TEMPLATE ' . $this->templateType . ' - Evaluatie opleidingen NL', '=')
      ->execute()
      ->fetchAssoc();
    var_dump($result);
  }
}
