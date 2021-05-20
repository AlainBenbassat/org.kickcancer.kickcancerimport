<?php

class CRM_Kickcancerimport_ImportIraiser extends CRM_Kickcancerimport_ImporterBase {
  public function import($entityTable, $id) {
    $this->importSource = 'iRaiser';

    if ($entityTable == 'tmp_import_iraiser_donations') {
      $this->importDonations($entityTable, $id);
    }
    elseif ($entityTable == 'tmp_import_iraiser_events') {
      $this->importEvents($entityTable, $id);
    }
  }

  private function importDonations($entityTable, $id) {
    $iRaiserRecord = $this->getRecordToImport($entityTable, $id);
    list($contactId, $employerId) = $this->processContact($iRaiserRecord);

    if ($employerId) {
      // add donation to organization
      $this->processContribution($employerId, $iRaiserRecord);
    }
    else {
      // add donation to individual
      $this->processContribution($contactId, $iRaiserRecord);
    }
  }

  private function importEvents($entityTable, $id) {

  }

  private function processContact($iRaiserRecord) {
    $contact = new CRM_Kickcancerimport_Contact($this->importSource);

    $contactId = $contact->findOrCreateIndividualByNameAndEmail($iRaiserRecord->first_name, $iRaiserRecord->last_name, $iRaiserRecord->email);

    $employerId = '';
    if ($iRaiserRecord->current_employer) {
      $employerId = $contact->findOrCreateOrganizationByName($iRaiserRecord->organization_name);
      $locType = $contact->LOCATION_TYPE_WORK;
      $addressContactId = $employerId;
    }
    else {
      $locType = $contact->LOCATION_TYPE_HOME;
      $addressContactId = $contactId;
    }

    // add address (to individual or org)
    $contact->updateOrCreateAddress($addressContactId, $locType, $iRaiserRecord->street_address, $iRaiserRecord->postal_code, $iRaiserRecord->city, $iRaiserRecord->country);

    // add extra info to contact
    $params = [
      'id' => $contactId,
      'employer_id' => $employerId,
      'birth_date' => $iRaiserRecord->birth_date,
      'prefix_id' => $iRaiserRecord->prefix_id,
      'preferred_language' => $iRaiserRecord->preferred_language,
    ];
    $contact->createIndividual($params);

    $params = [
      'id' => $contactId,
      'email' => $iRaiserRecord->email,
    ];
    $contact->createEmail($params);

    return [$contactId, $employerId];
  }

  private function processContribution($contactId, $iRaiserRecord) {
    $contribution = new CRM_Kickcancerimport_Contribution();
    $contribution->create($contactId, $iRaiserRecord->amount, $iRaiserRecord->receive_date, $this->importSource . ' ' . $iRaiserRecord->payment_reference);
  }

}
