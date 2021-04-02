<?php

use CRM_Kickcancerimport_ExtensionUtil as E;

class CRM_Kickcancerimport_Form_Import extends CRM_Core_Form {
  public function buildQuickForm() {
    $tasks = $this->getTasks();
    $this->addRadio('import_tasks', 'Task', $tasks, [], '<br>', TRUE);

    $buttons = $this->getButtons();
    $this->addButtons($buttons);

    $this->assign('elementNames', $this->getRenderableElementNames());

    parent::buildQuickForm();
  }

  public function postProcess() {
    $task = $this->getSubmittedTask();
    if ($task == 'config') {
      $config = new CRM_Importmeps_Config();
      $config->create();
    }
    elseif ($task == 'import_ep_orgs') {
      $ep = new CRM_Importmeps_EuroParliament();
      $ep->importOrgs();
    }
    elseif ($task == 'import_ep_persons') {
      $ep = new CRM_Importmeps_EuroParliament();
      $ep->importPersons();
    }
    elseif ($task == 'import_ec_orgs') {
      $ep = new CRM_Importmeps_EuroCommission();
      $ep->importOrgs();
    }
    elseif ($task == 'import_ec_cabinet_persons') {
      $ep = new CRM_Importmeps_EuroCommission();
      $ep->importEcPersons('tmp_ec_cabinet_persons');
    }
    elseif ($task == 'import_ec_dg_persons') {
      $ep = new CRM_Importmeps_EuroCommission();
      $ep->importEcPersons('tmp_ec_dg_persons');
    }
    elseif ($task == 'import_ec_eeas_persons') {
      $ep = new CRM_Importmeps_EuroCommission();
      $ep->importEcPersons('tmp_ec_eeas_persons');
    }
    elseif ($task == 'import_permreps_orgs') {
      $ep = new CRM_Importmeps_PermReps();
      $ep->importOrgs();
    }
    elseif ($task == 'import_permreps_persons') {
      $ep = new CRM_Importmeps_PermReps();
      $ep->importPersons();
    }
    else {
      $task .= ' is not implemented!';
    }

    CRM_Core_Session::setStatus('Done', $task, 'status');
  }

  private function getSubmittedTask() {
    $values = $this->exportValues();
    return $values['import_tasks'];
  }

  private function getTasks() {
    $sep = '<br>--------------------------------------------------------------';

    $tasks = [
      'config' => 'Create config items' . $sep,
      'import_fdr' => 'Import FDR contacts',
      'import_iraiser' => 'Import iRaiser contacts',
      'import_koalect' => 'Import Koalect contacts',
    ];

    return $tasks;
  }

  private function getButtons() {
    $buttons = [
      [
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ],
    ];

    return $buttons;
  }

  public function getRenderableElementNames() {
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
