<?php

class CRM_Events_DrupalWebformProcessorA extends CRM_Events_DrupalWebformProcessor {
  public function process($nodeId, $submissionId, $data) {
    $this->saveParticipantEventEvaluation($nodeId, $submissionId, $data);
    $this->saveParticipantTrainerEvaluation($nodeId, $submissionId, $data);
  }

  private function saveParticipantTrainerEvaluation($nodeId, $submissionId, $data) {
    $speakerFormKeyList = $this->getFormKeysStartingWith($data, 'evalform_speaker_id_');
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
      $sqlParams[1] = $this->getAnswerValueAndTypeFromSubmission('nid', $nodeId, $submissionId, $data);
      $sqlParams[2] = $this->getAnswerValueAndTypeFromSubmission('sid', $nodeId, $submissionId, $data);
      $sqlParams[3] = [$this->extractIdFromFormKey($speakerFormKey, 'evalform_speaker_id_'), 'Integer'];
      $sqlParams[4] = $this->getAnswerValueAndTypeFromSubmission('event_id', $nodeId, $submissionId, $data);
      $sqlParams[5] = $this->getAnswerValueAndTypeFromSubmission('template', $nodeId, $submissionId, $data);
      $sqlParams[6] = $this->handleNullAndReturnArray($data[$speakerFormKey]['a'], 'Integer');
      $sqlParams[7] = $this->handleNullAndReturnArray($data[$speakerFormKey]['b'], 'Integer');

      CRM_Core_DAO::executeQuery($sql, $sqlParams);
    }
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
    elseif ($columnName == 'algemene_tevredenheid') {
      $value = $data['evalform_q1'][0];
      $type = 'Integer';
    }
    elseif ($columnName == 'invulling') {
      $value = $data['evalform_q2a']['a'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'cursusmateriaal') {
      $value = $data['evalform_q2a']['b'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'interactie') {
      $value = $data['evalform_q2a']['c'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'kwaliteit') {
      $value = $data['evalform_q2a']['d'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'bijgeleerd') {
      $value = $data['evalform_q2a']['e'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'verwachting') {
      $value = $data['evalform_q2a']['f'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'relevantie') {
      $value = $data['evalform_q2a']['g'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'administratief_proces') {
      $value = $data['evalform_q6a']['a'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'ontvangst') {
      $value = $data['evalform_q6a']['b'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'catering') {
      $value = $data['evalform_q6a']['c'] ?? 'x';
      $type = 'Integer';
    }
    elseif ($columnName == 'locatie') {
      $value = $data['evalform_q6a']['d'] ?? 'x';
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
