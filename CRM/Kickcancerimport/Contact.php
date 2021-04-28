<?php

class CRM_Kickcancerimport_Contact {
  public $LOCATION_TYPE_HOME = 1;
  public $LOCATION_TYPE_WORK = 2;
  private $source = '';

  public function __construct($source) {
    $this->source = $source;
  }

  public function findOrCreateIndividualByNameAndPostalCode($firstName, $lastName, $postalCode) {
    $contactId = $this->findIndividualByNameAndPostalCode($firstName, $lastName, $postalCode);
    if ($contactId == 0) {
      $contactId = $this->createIndividual([
        'first_name' => $firstName . '',
        'last_name' => $lastName,
      ]);

      $this->createAddress([
        'contact_id' => $contactId,
        'location_type_id' => $this->LOCATION_TYPE_HOME,
        'postal_code' => $postalCode,
      ]);
    }

    return $contactId;
  }

  public function findOrCreateOrganizationByName($organizationName) {
    $contactId = $this->findOrganizationByName($organizationName);
    if ($contactId == 0) {
      $contactId = $this->CreateOrganization([
        'organization_name' => $organizationName,
      ]);
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
        c.is_deleted = 0
      and
        c.first_name = %1
      and 
        c.last_name = %2
      and
        a.postal_code = %3
    ";
    $sqlParams = [
      1 => [$firstName . '', 'String'],
      2 => [$lastName . '', 'String'],
      3 => [$postalCode . '', 'String'],
    ];
    $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);
    if ($dao->fetch()) {
      return $dao->id;
    }
    else {
      return 0;
    }
  }

  private function findOrganizationByName($organizationName) {
    $params = [
      'sequential' => 1,
      'organization_name' => $organizationName,
      'contact_type' => 'Organization',
    ];
    $result = civicrm_api3('Contact', 'get', $params);
    if ($result['count'] > 0) {
      return $result['values'][0]['id'];
    }
    else {
      return 0;
    }
  }

  private function createIndividual($params) {
    $params['sequential'] = 1;
    $params['contact_type'] = 'Individual';
    $params['source'] = $this->source;
    $result = civicrm_api3('Contact', 'create', $params);
    return $result['values'][0]['id'];
  }

  private function createOrganization($params) {
    $params['sequential'] = 1;
    $params['contact_type'] = 'Organization';
    $params['source'] = $this->source;
    $result = civicrm_api3('Contact', 'create', $params);
    return $result['values'][0]['id'];
  }

  private function createAddress($params) {
    $result = civicrm_api3('Address', 'create', $params);
    return $result['values'][0]['id'];
  }

  public function updateOrCreateAddress($contactId, $locationTypeId, $streetAddress, $postalCode, $city, $country) {
    $params = [
      'contact_id' => $contactId,
      'location_type_id' => $locationTypeId,
      'street_address' => $streetAddress,
      'city' => $city,
      'country_id' => $this->getCountryId($country, $postalCode),
    ];

    $addressId = $this->getPrimaryAddressId($contactId);
    if ($addressId > 0) {
      $params['id'] = $addressId;
    }

    return $this->createAddress($params);
  }

  private function getPrimaryAddressId($contactId) {
    $sql = "select id from civicrm_address where contact_id = %1 and is_primary = 1";
    $sqlParams = [
      1 => [$contactId, 'Integer'],
    ];
    $addressId = CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
    if ($addressId) {
      return $addressId;
    }
    else {
      return 0;
    }
  }

  private function getCountryIdFromCountryName($country) {
    $sql = "select id from civicrm_country where name = %1";
    $sqlParams = [
      1 => [$country, 'String'],
    ];
    $countryId = CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
    if ($countryId) {
      return $countryId;
    }
    else {
      return 0;
    }
  }

  private function getCountryIdFromPostalCode($postalCode) {
    if (strlen($postalCode) > 4) {
      // assume first 2 chars of postal code are the country code
      $countryId = $this->getCountryIdFromIsoCode(substr($postalCode, 0, 2));
    }
    else {
      $countryId = 0;
    }

    return $countryId;
  }

  private function getCountryIdFromIsoCode($isoCode) {
    $sql = "select id from civicrm_country where iso_code = %1";
    $sqlParams = [
      1 => [$isoCode, 'String'],
    ];
    $countryId = CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
    if ($countryId) {
      return $countryId;
    }
    else {
      return 0;
    }
  }

  private function getCountryId($country, $postalCode) {
    $countryId = 0;

    if ($country) {
      $countryId = $this->getCountryIdFromCountryName($country);
    }
    elseif ($postalCode) {
      $countryId = $this->getCountryIdFromPostalCode($postalCode);
    }

    if ($countryId == 0) {
      // assume Belgium
      $countryId = $this->getCountryIdFromIsoCode('BE');
    }

    return $countryId;
  }

}