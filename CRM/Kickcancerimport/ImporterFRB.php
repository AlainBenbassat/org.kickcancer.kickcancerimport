<?php

class CRM_Kickcancerimport_ImporterFRB extends CRM_Kickcancerimport_ImporterBase {
  public function import($entityTable, $id) {
    $this->importSource = 'FRB';

    $frbRecord = $this->getRecordToImport($entityTable, $id);
    $contactId = $this->processContact($frbRecord);
    $this->processContribution($contactId, $frbRecord);
  }

  private function processContact($frbRecord) {
    $contact = new CRM_Kickcancerimport_Contact($this->importSource);
    $locType = 0;
    if ($this->isOrganization($frbRecord)) {
      $contactId = $contact->findOrCreateOrganizationByName($frbRecord->organization_name);
      $locType = $contact->LOCATION_TYPE_WORK;
    }
    else {
      $contactId = $contact->findOrCreateIndividualByNameAndPostalCode($frbRecord->first_name, $frbRecord->last_name, $frbRecord->postal_code);
      $locType = $contact->LOCATION_TYPE_HOME;
    }

    $contact->updateOrCreateAddress($contactId, $locType, $frbRecord->street_address, $frbRecord->postal_code, $frbRecord->city, '');

    return $contactId;
  }

  private function processContribution($contactId, $frbRecord) {
    $contribution = new CRM_Kickcancerimport_Contribution();
    $contribution->create($contactId, $frbRecord->amount, $frbRecord->receive_date, $this->importSource . ' ' . $frbRecord->payment_reference);
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
