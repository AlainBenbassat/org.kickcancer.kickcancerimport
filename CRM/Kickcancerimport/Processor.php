<?php

class CRM_Kickcancerimport_Processor {
  public function run($entityTable) {
    $queue = new CRM_Kickcancerimport_Queue('kickcancerimport', 'Import KickCancer Data', 'civicrm/import-kick-data');
    $this->addEntityIDsToQueue($entityTable, $queue);
    $queue->run();
  }

  private function addEntityIDsToQueue($entityTable, $queue) {
    $class = __CLASS__;
    $method = 'processQueueTask';

    $sql = "select id from $entityTable";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $queue->addTask($class, $method, [$entityTable, $dao->id]);
    }
  }

  public static function processQueueTask(CRM_Queue_TaskContext $ctx, $entityTable, $id) {
    $importer = self::getImportObjectFromTableName($entityTable);
    $importer->import($entityTable, $id);
    return TRUE;
  }

  public static function getImportObjectFromTableName($entityTable) {
    if ($entityTable == 'tmp_import_frb') {
      $className = 'CRM_Kickcancerimport_ImporterFRB';
    }
    elseif ($entityTable == 'tmp_import_iraiser_donations') {
      $className = 'CRM_Kickcancerimport_ImporterIraiser';
    }
    elseif ($entityTable == 'tmp_import_iraiser_events') {
      $className = 'CRM_Kickcancerimport_ImporterIraiser';
    }
    elseif ($entityTable == 'tmp_import_koalect') {
      $className = 'CRM_Kickcancerimport_ImporterKoalect';
    }
    else {
      throw new Exception("$entityTable is not implemented");
    }

    $obj = new $className;
    return $obj;
  }
}
