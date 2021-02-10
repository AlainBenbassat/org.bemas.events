<?php

class CRM_Events_DrupalWebformSubmission {
  public static function process($nodeId, $submissionId, $data) {
    self::saveEventEvaluation($nodeId, $submissionId, $data);
    self::saveTrainerEvaluation($nodeId, $submissionId, $data);
  }

  private static function saveEventEvaluation($nodeId, $submissionId, $data) {
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

      list($columnValue, $columnType) = self::getAnswerValueAndTypeFromSubmission($columnName, $nodeId, $submissionId, $data);
      $sqlParams[$columnIndex] = [$columnValue, $columnType];
    }

    $sql = sprintf("insert into civicrm_bemas_eval_participant_event (%s) values (%s)", implode(', ', $columnList), implode(', ', $columnIndexList));
    watchdog('alain', $sql);
    watchdog('alain', print_r($sqlParams, TRUE));
    CRM_Core_DAO::executeQuery($sql, $sqlParams);
  }

  private static function saveTrainerEvaluation($nodeId, $submissionId, $data) {

  }

  private static function getAnswerValueAndTypeFromSubmission($columnName, $nodeId, $submissionId, $data) {
    // TODO here we can differentiate according to the templete type
    if ($columnName == 'nid') {
      $value = $nodeId;
      $type = 'Integer';
    }
    elseif ($columnName == 'sid') {
      $value = $submissionId;
      $type = 'Integer';
    }
    elseif ($columnName == 'event_id') {
      $value = self::extractIdFromFormKey($data, 'evalform_event_id_');
      $type = 'Integer';
    }
    elseif ($columnName == 'template') {
      $value = $data['evalform_type'][0];
      $type = 'String';
    }
    elseif ($columnName == 'algemene_tevredenheid') {
      $value = $data['evalform_q1'][0];
      $type = 'Integer';
    }
    elseif ($columnName == 'invulling') {
      $value = $data['evalform_q2a']['a'];
      $type = 'Integer';
    }
    elseif ($columnName == 'cursusmateriaal') {
      $value = $data['evalform_q2a']['b'];
      $type = 'Integer';
    }
    elseif ($columnName == 'interactie') {
      $value = $data['evalform_q2a']['c'];
      $type = 'Integer';
    }
    elseif ($columnName == 'kwaliteit') {
      $value = $data['evalform_q2a']['d'];
      $type = 'Integer';
    }
    elseif ($columnName == 'bijgeleerd') {
      $value = $data['evalform_q2a']['e'];
      $type = 'Integer';
    }
    elseif ($columnName == 'verwachting') {
      $value = $data['evalform_q2a']['f'];
      $type = 'Integer';
    }
    elseif ($columnName == 'relevantie') {
      $value = $data['evalform_q2a']['g'];
      $type = 'Integer';
    }
    elseif ($columnName == 'administratief_proces') {
      $value = $data['evalform_q6a']['a'];
      $type = 'Integer';
    }
    elseif ($columnName == 'ontvangst') {
      $value = $data['evalform_q6a']['b'];
      $type = 'Integer';
    }
    elseif ($columnName == 'catering') {
      $value = $data['evalform_q6a']['c'];
      $type = 'Integer';
    }
    elseif ($columnName == 'locatie') {
      $value = $data['evalform_q6a']['d'];
      $type = 'Integer';
    }
    else {
      $value = '';
      $type = 'null';
    }

    // process n.v.t. answer
    if ($value == 'x' || !$value) {
      $value = '';
      $type = 'Timestamp';
    }

    return [$value, $type];
  }

  private static function extractIdFromFormKey($data, $keyStartsWith) {
    foreach ($data as $k => $v) {
      if (strpos($k, $keyStartsWith) === 0) {
        return substr($k, strlen($keyStartsWith));
      }
    }

    // not found
    return '';
  }
}
