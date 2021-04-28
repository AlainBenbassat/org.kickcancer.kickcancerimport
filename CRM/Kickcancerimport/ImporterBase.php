<?php

class CRM_Kickcancerimport_ImporterBase {
  protected $importSource = '';

  protected function getRecordToImport($entityTable, $id) {
    $sql = "select * from $entityTable where id = $id";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $dao->fetch();

    return $dao;
  }
}