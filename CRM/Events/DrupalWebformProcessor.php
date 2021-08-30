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
      4 => 'module',
      5 => 'template',
      6 => 'algemene_tevredenheid',
      7 => 'invulling',
      8 => 'cursusmateriaal',
      9 => 'interactie',
      10 => 'kwaliteit',
      11 => 'bijgeleerd',
      12 => 'verwachting',
      13 => 'relevantie',
      14 => 'administratief_proces',
      15 => 'ontvangst',
      16 => 'catering',
      17 => 'locatie',
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

  public function saveParticipantTrainerEvaluation($nodeId, $submissionId, $data, $expertise, $didactischeVaardigheden) {
    $speakerFormKeyList = $this->getFormKeysStartingWith($data, 'evalform_speaker_id_');
    foreach ($speakerFormKeyList as $speakerFormKey) {
      if (!empty($data[$speakerFormKey][$expertise])) {
        $sqlParams = [];
        $sql = "
        insert into
          civicrm_bemas_eval_participant_trainer
        (
          nid, sid, contact_id, event_id, module, template, expertise, didactische_vaardigheden
        )
        values
        (
          %1, %2, %3, %4, %5, %6, %7, %8
        )
      ";
        $sqlParams[1] = $this->getAnswerValueAndTypeFromSubmission('nid', $nodeId, $submissionId, $data);
        $sqlParams[2] = $this->getAnswerValueAndTypeFromSubmission('sid', $nodeId, $submissionId, $data);
        $sqlParams[3] = [
          $this->extractIdFromFormKey($speakerFormKey, 'evalform_speaker_id_'),
          'Integer'
        ];
        $sqlParams[4] = $this->getAnswerValueAndTypeFromSubmission('event_id', $nodeId, $submissionId, $data);
        $sqlParams[5] = $this->getAnswerValueAndTypeFromSubmission('module', $nodeId, $submissionId, $data);
        $sqlParams[6] = $this->getAnswerValueAndTypeFromSubmission('template', $nodeId, $submissionId, $data);
        $sqlParams[7] = $this->handleNullAndReturnArray($data[$speakerFormKey][$expertise], 'Integer');
        $sqlParams[8] = $this->handleNullAndReturnArray($data[$speakerFormKey][$didactischeVaardigheden], 'Integer');

        CRM_Core_DAO::executeQuery($sql, $sqlParams);
      }
    }
  }

  public function handleNullAndReturnArray($value, $type) {
    // process answer "niet van toepassing"
    if ($type == 'Integer' && ($value === 'x' || $value === '')) {
      $value = '';
      $type = 'Timestamp'; // unorthodox way to insert a NULL value
    }
    elseif ($type == 'null') {
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
