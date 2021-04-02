<?php

class CRM_Kickcancerimport_Contact {
  public function findOrCreateIndividualByNameAndPostalCode($firstName, $lastName, $postalCode) {
    $contactId = $this->findIndividualByNameAndPostalCode($firstName, $lastName, $postalCode);
    if ($contactId == 0) {
      $params = [
        'first_name' => $firstName,
        'last_name' => $lastName,
      ];
      $contactId = $this->createIndividual($params);
    }

    return $contactId;
  }

  private function findIndividualByNameAndPostalCode($firstName, $lastName, $postalCode) {
    $sql = "
      select 
        c.id 
      from 
        civicrm_contact c
      left outer join
        civicrm_address a on a.contact_id = c.id
      where 
        c.first_name = %1
      and 
        c.last_name = %2
      and
        c.postal_code = %3
    ";
    $sqlParams = [
      1 => [$firstName, 'String'],
      2 => [$lastName, 'String'],
      3 => [$postalCode, 'String'],
    ];
    $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);
    if ($dao->fetch()) {
      return $dao->id;
    }
    else {
      return 0;
    }
  }

  private function createIndividual($params) {
    $params['sequential'] = 1;
    $params['contact_type'] = 'Individual';
    $result = civicrm_api3('Contact', 'create', $params);
    return $result['values'][0]['id'];
  }

}