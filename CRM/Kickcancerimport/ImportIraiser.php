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
    [$contactId, $employerId] = $this->processContact($iRaiserRecord);

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

    $employerId = 0;
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
    $params = [];
    $params['id'] = $contactId;
    $this->addToParamIfValueNotEmpty($params, 'prefix_id', $iRaiserRecord->prefix_id);
    $this->addToParamIfValueNotEmpty($params, 'gender_id', $iRaiserRecord->gender_id);
    $this->addToParamIfValueNotEmpty($params, 'preferred_language', $iRaiserRecord->preferred_language);
    $this->addToParamIfValueNotEmpty($params, 'employer_id', $employerId);
    $this->addToParamIfValueNotEmpty($params, 'birth_date', $iRaiserRecord->birth_date);
    $contact->createIndividual($params);

    $params = [
      'contact_id' => $contactId,
      'email' => $iRaiserRecord->email,
    ];
    $contact->createEmail($params);

    return [$contactId, $employerId];
  }

  private function processContribution($contactId, $iRaiserRecord) {
    $contribution = new CRM_Kickcancerimport_Contribution();
    $contribution->create($contactId, $iRaiserRecord->amount, $iRaiserRecord->receive_date, $this->importSource . ' ' . $iRaiserRecord->payment_reference);
  }

  private function addToParamIfValueNotEmpty(&$param, $key, $value) {
    if ($value) {
      $param[$key] = $value;
    }
  }

}
