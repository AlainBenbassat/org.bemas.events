<?php

class CRM_Events_DrupalWebformSubmission {
  public static function process($nodeId, $submissionId, $data) {
    $templateType = strtoupper($data['evalform_type'][0]);

    $processor = self::drupalWebformProcessorFactory($templateType);
    $processor->process($nodeId, $submissionId, $data);
  }

  /**
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

  private static function drupalWebformProcessorFactory($templateType) {
    switch ($templateType) {
      case 'A':
        $obj = new CRM_Events_DrupalWebformProcessorA();
        break;
      case 'B':
        $obj = new CRM_Events_DrupalWebformProcessorB();
        break;
      case 'C':
        $obj = new CRM_Events_DrupalWebformProcessorC();
        break;
      case 'L1':
        $obj = new CRM_Events_DrupalWebformProcessorL1();
        break;
      default:
        throw new Exception("Processing of template $templateType is not implemented");
    }

    return $obj;
  }

}
