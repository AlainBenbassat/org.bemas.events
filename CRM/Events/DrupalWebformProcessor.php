<?php

abstract class CRM_Events_DrupalWebformProcessor {
  abstract public function process($nodeId, $submissionId, $data);
  abstract protected function getAnswerValueAndTypeFromSubmission($columnName, $nodeId, $submissionId, $data);

  public function saveParticipantEventEvaluation($nodeId, $submissionId, $data) {
    $sqlParams = [];
    $columns = [
      1 => 'nid',
      2 => 'sid',
      3 => 'event_id',
      4 => 'template',
      5 => 'algemene_tevredenheid',
      6 => 'invulling',
      7 => 'cursusmateriaal',
      8 => 'interactie',
      9 => 'kwaliteit',
      10 => 'bijgeleerd',
      11 => 'verwachting',
      12 => 'relevantie',
      13 => 'administratief_proces',
      14 => 'ontvangst',
      15 => 'catering',
      16 => 'locatie',
    ];

    foreach ($columns as $columnIndex => $columnName) {
      $columnList[] = $columnName;
      $columnIndexList[] = "%$columnIndex";

      [$columnValue, $columnType] = $this->getAnswerValueAndTypeFromSubmission($columnName, $nodeId, $submissionId, $data);
      $sqlParams[$columnIndex] = [$columnValue, $columnType];
    }

    $sql = sprintf("insert into civicrm_bemas_eval_participant_event (%s) values (%s)", implode(', ', $columnList), implode(', ', $columnIndexList));

    CRM_Core_DAO::executeQuery($sql, $sqlParams);
  }

  public function handleNullAndReturnArray($value, $type) {
    // process answer "niet van toepassing"
    if ($type == 'Integer' && ($value == 'x' || $value == '')) {
      $value = '';
      $type = 'Timestamp'; // unorthodox way to insert a NULL value
    }

    return [$value, $type];
  }

  public function extractIdFromFormKey($formKey, $prefix) {
    return substr($formKey, strlen($prefix));
  }

  public function getFormKeysStartingWith($data, $keyStartsWith) {
    $keys = [];

    foreach ($data as $k => $v) {
      if (strpos($k, $keyStartsWith) === 0) {
        $keys[] = $k;
      }
    }

    return $keys;
  }
}
