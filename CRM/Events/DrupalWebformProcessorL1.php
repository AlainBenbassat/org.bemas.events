<?php

class CRM_Events_DrupalWebformProcessorL1 extends CRM_Events_DrupalWebformProcessor {
  public function process($nodeId, $submissionId, $data) {
    $this->saveTrainerEventEvaluation($nodeId, $submissionId, $data);
  }

  private function saveTrainerEventEvaluation($nodeId, $submissionId, $data) {
    $sqlParams = [];
    $columns = [
      1 => 'nid',
      2 => 'sid',
      3 => 'event_id',
      4 => 'module',
      5 => 'template',
      6 => 'algemene_tevredenheid',
      7 => 'ontvangst',
      8 => 'catering',
      9 => 'locatie',
      10 => 'cursusmateriaal',
      11 => 'interactie',
      12 => 'verwachting',
    ];

    foreach ($columns as $columnIndex => $columnName) {
      $columnList[] = $columnName;
      $columnIndexList[] = "%$columnIndex";

      [$columnValue, $columnType] = $this->getAnswerValueAndTypeFromSubmission($columnName, $nodeId, $submissionId, $data);
      $sqlParams[$columnIndex] = [$columnValue, $columnType];
    }

    $sql = sprintf("insert into civicrm_bemas_eval_trainer_event (%s) values (%s)", implode(', ', $columnList), implode(', ', $columnIndexList));

    CRM_Core_DAO::executeQuery($sql, $sqlParams);
  }

  protected function getAnswerValueAndTypeFromSubmission($columnName, $nodeId, $submissionId, $data) {
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
      $formKeyList = $this->getFormKeysStartingWith($data, 'evalform_event_id_');
      $value = $this->extractIdFromFormKey($formKeyList[0], 'evalform_event_id_');
      $type = 'Integer';
    }
    elseif ($columnName == 'template') {
      $value = $templateType;
      $type = 'String';
    }
    elseif ($columnName == 'module') {
      if (array_key_exists('evalform_modules', $data)) {
        $value = $data['evalform_modules'][0];
        $type = 'String';
      }
      else {
        $value = '';
        $type = 'null';
      }
    }
    elseif ($columnName == 'algemene_tevredenheid') {
      $value = $data['evalform_q1'][0];
      $type = 'Integer';
    }
    elseif ($columnName == 'ontvangst') {
      $value = $data['evalform_q2a']['a'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'catering') {
      $value = $data['evalform_q2a']['b'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'locatie') {
      $value = $data['evalform_q2a']['c'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'cursusmateriaal') {
      $value = $data['evalform_q2a']['d'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'interactie') {
      $value = $data['evalform_q2a']['e'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'verwachting') {
      $value = $data['evalform_q2a']['f'] ?? 'x';
      $type = 'Integer';
    }
    else {
      // TODO throw error? or just log?
      $value = '';
      $type = 'null';
    }

    return $this->handleNullAndReturnArray($value, $type);
  }

}
