<?php

class CRM_Events_DrupalWebformProcessorC extends CRM_Events_DrupalWebformProcessor  {
  private $trainerFields = [];

  public function process($nodeId, $submissionId, $data) {
    $this->preProcessTrainerFields($data);
    $this->saveParticipantEventEvaluation($nodeId, $submissionId, $data);
    $this->saveParticipantTrainerEvaluation($nodeId, $submissionId, $data);
  }

  private function preProcessTrainerFields($data) {
    $this->trainerFields['invulling'] = new CRM_Events_ScoreCounter();
    $this->trainerFields['cursusmateriaal'] = new CRM_Events_ScoreCounter();
    $this->trainerFields['interactie'] = new CRM_Events_ScoreCounter();
    $this->trainerFields['kwaliteit'] = new CRM_Events_ScoreCounter();
    $this->trainerFields['bijgeleerd'] = new CRM_Events_ScoreCounter();
    $this->trainerFields['verwachting'] = new CRM_Events_ScoreCounter();
    $this->trainerFields['relevantie'] = new CRM_Events_ScoreCounter();

    // loop over each trainer (we store the avg score per topic)
    $speakerFormKeyList = $this->getFormKeysStartingWith($data, 'evalform_speaker_id_');
    foreach ($speakerFormKeyList as $speakerFormKey) {
      $this->trainerFields['invulling']->add($data[$speakerFormKey]['a']);
      $this->trainerFields['cursusmateriaal']->add($data[$speakerFormKey]['b']);
      $this->trainerFields['interactie']->add($data[$speakerFormKey]['c']);
      $this->trainerFields['kwaliteit']->add($data[$speakerFormKey]['d']);
      $this->trainerFields['bijgeleerd']->add($data[$speakerFormKey]['e']);
      $this->trainerFields['verwachting']->add($data[$speakerFormKey]['f']);
      $this->trainerFields['relevantie']->add($data[$speakerFormKey]['g']);
    }
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
      $sqlParams[6] = $this->handleNullAndReturnArray($data[$speakerFormKey]['h'], 'Integer');
      $sqlParams[7] = $this->handleNullAndReturnArray($data[$speakerFormKey]['i'], 'Integer');

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
      $value = $this->trainerFields['invulling']->getAvgScore();
      $type = 'Integer';
    }
    elseif ($columnName == 'cursusmateriaal') {
      $value = $this->trainerFields['cursusmateriaal']->getAvgScore();
      $type = 'Integer';
    }
    elseif ($columnName == 'interactie') {
      $value = $this->trainerFields['interactie']->getAvgScore();
      $type = 'Integer';
    }
    elseif ($columnName == 'kwaliteit') {
      $value = $this->trainerFields['kwaliteit']->getAvgScore();
      $type = 'Integer';
    }
    elseif ($columnName == 'bijgeleerd') {
      $value = $this->trainerFields['bijgeleerd']->getAvgScore();
      $type = 'Integer';
    }
    elseif ($columnName == 'verwachting') {
      $value = $this->trainerFields['verwachting']->getAvgScore();
      $type = 'Integer';
    }
    elseif ($columnName == 'relevantie') {
      $value = $this->trainerFields['relevantie']->getAvgScore();
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
      // TODO throw error? or just log?
      $value = '';
      $type = 'null';
    }

    return $this->handleNullAndReturnArray($value, $type);
  }

}


