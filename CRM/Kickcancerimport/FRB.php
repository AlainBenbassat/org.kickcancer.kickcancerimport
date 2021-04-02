<?php

class CRM_Kickcancerimport_FRB {
  public function import($id) {
    $frbRecord = $this->getRecord($id);

    if ($this->isOrganization($frbRecord)) {

    }
    else {
      $contactId = $this->findOrCreateIndividual($frbRecord->first_name, $frbRecord->last_name, $frbRecord->postal_code);
    }

  }

  private function getRecord($id) {
    $sql = "select * from tmp_import_frb where id = $id";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $dao->fetch();

    return $dao;
  }

  private function isOrganization($frbRecord) {
    if ($frbRecord->organization_name) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}
