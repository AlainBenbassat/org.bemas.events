<?php

class CRM_Events_DrupalWebformSubmission {
  public static function process($nodeId, $submissionId, $data) {
    $templateType = $data['evalform_type'][0];

    if ($templateType == 'l1') {
      self::saveTrainerEventEvaluation($nodeId, $submissionId, $data);
    }
    else {
      self::saveParticipantEventEvaluation($nodeId, $submissionId, $data);
      self::saveParticipantTrainerEvaluation($nodeId, $submissionId, $data);
    }
  }

  /**
   * @param $submissionId
   *
   * Delete a survey webform submission from the dashboard tables
   */
  public static function delete($submissionId) {
    $tables = ['civicrm_bemas_eval_participant_event', 'civicrm_bemas_eval_participant_trainer', 'civicrm_bemas_eval_trainer_event'];

    $sqlParams = [
      1 => [$submissionId, 'Integer'],
    ];

    foreach ($tables as $table) {
      CRM_Core_DAO::executeQuery("delete from $table where sid = %1", $sqlParams);
    }
  }

  private static function saveParticipantEventEvaluation($nodeId, $submissionId, $data) {
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

      [$columnValue, $columnType] = self::getAnswerValueAndTypeFromSubmission($columnName, $nodeId, $submissionId, $data);
      $sqlParams[$columnIndex] = [$columnValue, $columnType];
    }

    $sql = sprintf("insert into civicrm_bemas_eval_participant_event (%s) values (%s)", implode(', ', $columnList), implode(', ', $columnIndexList));

    CRM_Core_DAO::executeQuery($sql, $sqlParams);
  }

  private static function saveParticipantTrainerEvaluation($nodeId, $submissionId, $data) {
    $speakerFormKeyList = self::getFormKeysStartingWith($data, 'evalform_speaker_id_');
    foreach ($speakerFormKeyList as $speakerFormKey) {
      $sqlParams = [];
      $sql = "
        insert into
          civicrm_bemas_eval_participant_trainer
        (
          nid, sid, contact_id, event_id, template, expertise, didactische_vaardigheden
        )
        values
        (
          %1, %2, %3, %4, %5, %6, %7
        )
      ";
      $sqlParams[1] = self::getAnswerValueAndTypeFromSubmission('nid', $nodeId, $submissionId, $data);
      $sqlParams[2] = self::getAnswerValueAndTypeFromSubmission('sid', $nodeId, $submissionId, $data);
      $sqlParams[3] = [self::extractIdFromFormKey($speakerFormKey, 'evalform_speaker_id_'), 'Integer'];
      $sqlParams[4] = self::getAnswerValueAndTypeFromSubmission('event_id', $nodeId, $submissionId, $data);
      $sqlParams[5] = self::getAnswerValueAndTypeFromSubmission('template', $nodeId, $submissionId, $data);
      $sqlParams[6] = self::handleNullAndReturnArray($data[$speakerFormKey]['a'], 'Integer');
      $sqlParams[7] = self::handleNullAndReturnArray($data[$speakerFormKey]['b'], 'Integer');

      CRM_Core_DAO::executeQuery($sql, $sqlParams);
    }
  }

  private static function saveTrainerEventEvaluation($nodeId, $submissionId, $data) {
    $sqlParams = [];
    $columns = [
      1 => 'nid',
      2 => 'sid',
      3 => 'event_id',
      4 => 'template',
      5 => 'algemene_tevredenheid',
      6 => 'ontvangst',
      7 => 'catering',
      8 => 'locatie',
      9 => 'cursusmateriaal',
      10 => 'interactie',
      11 => 'verwachting',
    ];

    foreach ($columns as $columnIndex => $columnName) {
      $columnList[] = $columnName;
      $columnIndexList[] = "%$columnIndex";

      [$columnValue, $columnType] = self::getAnswerValueAndTypeFromSubmission($columnName, $nodeId, $submissionId, $data);
      $sqlParams[$columnIndex] = [$columnValue, $columnType];
    }

    $sql = sprintf("insert into civicrm_bemas_eval_trainer_event (%s) values (%s)", implode(', ', $columnList), implode(', ', $columnIndexList));

    CRM_Core_DAO::executeQuery($sql, $sqlParams);
  }

  private static function getAnswerValueAndTypeFromSubmission($columnName, $nodeId, $submissionId, $data) {
    $templateType = $data['evalform_type'][0];

    if ($columnName == 'nid') {
      $value = $nodeId;
      $type = 'Integer';
    }
    elseif ($columnName == 'sid') {
      $value = $submissionId;
      $type = 'Integer';
    }
    elseif ($columnName == 'event_id') {
      $formKeyList = self::getFormKeysStartingWith($data, 'evalform_event_id_');
      $value = self::extractIdFromFormKey($formKeyList[0], 'evalform_event_id_');
      $type = 'Integer';
    }
    elseif ($columnName == 'template') {
      $value = $templateType;
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
      $value = $templateType == 'l1' ? $data['evalform_q2a']['d'] : $data['evalform_q2a']['b'];
      $type = 'Integer';
    }
    elseif ($columnName == 'interactie') {
      $value = $templateType == 'l1' ? $data['evalform_q2a']['e'] : $data['evalform_q2a']['c'];
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
      $value = $templateType == 'l1' ? $data['evalform_q2a']['a'] : $data['evalform_q6a']['b'];
      $type = 'Integer';
    }
    elseif ($columnName == 'catering') {
      $value = $templateType == 'l1' ? $data['evalform_q2a']['b'] : $data['evalform_q6a']['c'];
      $type = 'Integer';
    }
    elseif ($columnName == 'locatie') {
      $value = $templateType == 'l1' ? $data['evalform_q2a']['c'] : $data['evalform_q6a']['d'];
      $type = 'Integer';
    }
    else {
      // TODO throw error? or just log?
      $value = '';
      $type = 'null';
    }

    return self::handleNullAndReturnArray($value, $type);
  }

  private static function handleNullAndReturnArray($value, $type) {
    // process answer "niet van toepassing"
    if ($type == 'Integer' && ($value == 'x' || $value == '')) {
      $value = '';
      $type = 'Timestamp'; // unorthodox way to insert a NULL value
    }

    return [$value, $type];
  }

  private static function extractIdFromFormKey($formKey, $prefix) {
    return substr($formKey, strlen($prefix));
  }

  private static function getFormKeysStartingWith($data, $keyStartsWith) {
    $keys = [];

    foreach ($data as $k => $v) {
      if (strpos($k, $keyStartsWith) === 0) {
        $keys[] = $k;
      }
    }

    return $keys;
  }
}
