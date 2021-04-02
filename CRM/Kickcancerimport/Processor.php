<?php

class CRM_Kickcancerimport_Processor {
  public function run($entityTable) {
    $queue = new CRM_Kickcancerimport_Queue('kickcancerimport', 'Import KickCancer Data', 'civicrm/import-kick-data');
    $this->addEntityIDsToQueue($entityTable, $queue);
    $queue->run();
  }

  private function addEntityIDsToQueue($entityTable, $queue): void {
    $class = 'CRM_Kickcancerimport_Processor';
    $method = 'processQueueTask';

    $sql = "select id from $entityTable";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $queue->addTask($class, $method, [$entityTable, $dao->id]);
    }
  }

  public static function getClassFromEntityTable($entityTable) {
    if ($entityTable == 'tmp_import_fdr') {
      $class = 'CRM_Kickcancerimport_FRB';
    }
    else {
      throw new Exception("$entityTable is not implemented");
    }

    return $class;
  }

  public static function processQueueTask(CRM_Queue_TaskContext $ctx, $entityTable, $id) {
    $class = self::getClassFromEntityTable($entityTable);
    $class->import($id);
  }
}
