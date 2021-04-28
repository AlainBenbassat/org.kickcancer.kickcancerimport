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
    try {
      $task = $this->getSubmittedTask();
      if ($task == 'config') {
        $config = new CRM_Kickcancerimport_Config();
        $config->create();
      }
      else {
        $processor = new CRM_Kickcancerimport_Processor();
        $processor->run($task);
      }
      CRM_Core_Session::setStatus('Done', $task, 'success');
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(), $task, 'error');
    }
  }

  private function getSubmittedTask() {
    $values = $this->exportValues();
    return $values['import_tasks'];
  }

  private function getTasks() {
    $sep = '<br>--------------------------------------------------------------';

    // the key of the task is the name of the table that contain these items (except "config")
    $tasks = [
      'config' => 'Create config items' . $sep,
      'tmp_import_frb' => 'Import FRB contacts',
      'tmp_import_iraiser' => 'Import iRaiser contacts',
      'tmp_import_koalect' => 'Import Koalect contacts',
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
