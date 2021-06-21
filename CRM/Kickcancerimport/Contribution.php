<?php

class CRM_Kickcancerimport_Contribution {
  public const FINANCIAL_TYPE_DONATION = 1;
  public const FINANCIAL_TYPE_EVENT = 4;
  private const PAYMENT_INSTRUMENT_EFT = 5;
  private const SOFT_CONTRIBUTION_TYPE_GIFT = 11;

  public function create($contactId, $amount, $receiveDate, $source, $financialTypeId) {
    $params = [
      'sequential' => 1,
      'contact_id' => $contactId,
      'total_amount' => $amount,
      'receive_date' => $receiveDate,
      'financial_type_id' => $financialTypeId,
      'payment_instrument_id' => self::PAYMENT_INSTRUMENT_EFT,
      'source' => $source,
    ];
    $contribution = civicrm_api3('Contribution', 'create', $params);

    return $contribution['values'][0];
  }

  public function createSoft($contributionId, $contactId, $amount) {
    $params = [
      'sequential' => 1,
      'contact_id' => $contactId,
      'amount' => $amount,
      'contribution_id' => $contributionId,
      'soft_credit_type_id' => self::SOFT_CONTRIBUTION_TYPE_GIFT,

    ];
    $contributionSoft = civicrm_api3('ContributionSoft', 'create', $params);

    return $contributionSoft['values'][0];
  }
}
