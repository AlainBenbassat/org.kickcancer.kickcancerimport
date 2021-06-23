<?php

class CRM_Kickcancerimport_ImporterKoalect extends CRM_Kickcancerimport_ImporterBase {
  public function import($entityTable, $id) {
    $this->importSource = 'Koalect';

    $koalectRecord = $this->getRecordToImport($entityTable, $id);
    $contactId = $this->processContact($koalectRecord);
    $this->processContribution($contactId, $koalectRecord);
  }

  private function processContact($koalectRecord) {
    $contact = new CRM_Kickcancerimport_Contact($this->importSource);
    $contactId = $contact->findOrCreateIndividualByNameAndEmail($koalectRecord->first_name, $koalectRecord->last_name, $koalectRecord->email);

    $contact->updateOrCreateAddress($contactId, $contact->LOCATION_TYPE_HOME, $koalectRecord->street_address, $koalectRecord->postal_code, $koalectRecord->city, $koalectRecord->country_iso_code);

    $contact->createEmail([
      'contact_id' => $contactId,
      'email' => $koalectRecord->email,
    ]);

    $params = [
      'id' => $contactId,
      'birth_date' => $koalectRecord->birthdate,
      'gender_id' => $koalectRecord->gender_id,
    ];
    $contact->createIndividual($params);

    return $contactId;
  }

  private function processContribution($contactId, $koalectRecord) {
    $contribution = new CRM_Kickcancerimport_Contribution();
    $contribution->create($contactId, $koalectRecord->amount, $koalectRecord->receive_date, $this->importSource . ' ' . $koalectRecord->id, CRM_Kickcancerimport_Contribution::FINANCIAL_TYPE_DONATION);
  }
}