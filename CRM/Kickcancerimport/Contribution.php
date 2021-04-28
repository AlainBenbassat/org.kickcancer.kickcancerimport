<?php

class CRM_Kickcancerimport_Contribution {
  private $FINANCIAL_TYPE_DONATION = 1;
  private $PAYMENT_INSTRUMENT_EFT = 5;

  public function create($contactId, $amount, $receiveDate, $source) {
    $params = [
      'contact_id' => $contactId,
      'total_amount' => $amount,
      'receive_date' => $receiveDate,
      'financial_type_id' => $this->FINANCIAL_TYPE_DONATION,
      'payment_instrument_id' => $this->PAYMENT_INSTRUMENT_EFT,
      'source' => $source,
    ];
    civicrm_api3('Contribution', 'create', $params);
  }
}
