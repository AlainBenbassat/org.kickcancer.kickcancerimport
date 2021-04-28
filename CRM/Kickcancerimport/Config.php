<?php

class CRM_Kickcancerimport_Config {
  public function create() {
    $this->createCampaignTypes();
    $this->createCampaigns();
  }

  public function getCampaign($campaignName, $campaignType) {
    $campaign = $this->findCampaign($campaignName);
    if (!$campaign) {
      $campaign = $this->createCampaign($campaignName, $campaignType);
    }

    return $campaign;
  }

  public function getCampaignType($campaignTypeLabel) {
    $campaign = $this->findCampaignType($campaignTypeLabel);
    if (!$campaign) {
      $campaign = $this->createCampaignType($campaignTypeLabel);
    }

    return $campaign;
  }

  private function createCampaigns() {
    $this->getCampaign('Run to Kick 2018','Run to Kick');
    $this->getCampaign('Run to Kick 2019','Run to Kick');
    $this->getCampaign('Run to Kick 2020','Run to Kick');
    $this->getCampaign('Run to Kick 2021','Run to Kick');
    $this->getCampaign('Research Projects 2018','Research Projects');
    $this->getCampaign('Research Projects 2019','Research Projects');
    $this->getCampaign('Research Projects 2020','Research Projects');
    $this->getCampaign('Research Projects 2021','Research Projects');
  }
  
  private function createCampaignTypes() {
    $campaignTypes = [
      'Run to Kick',
      'Sport Challenge',
      'In Memoriam',
      'Celebrations',
      'Research Projects',
    ];

    foreach ($campaignTypes as $campaignType) {
      $this->getCampaignType($campaignType);
    }
  }

  private function findCampaign($campaignTitle) {
    $campaigns = \Civi\Api4\Campaign::get()
      ->addWhere('title', '=', $campaignTitle)
      ->execute();

    if ($campaigns->count() > 0) {
      return $campaigns->first();
    }
    else {
      return FALSE;
    }
  }

  private function createCampaign($campaignTitle, $campaignType) {
    $campaignTypeId = $this->getCampaignType($campaignType)['value'];
    $campaignName = CRM_Utils_String::munge($campaignTitle, '_', 64);

    $campaigns = \Civi\Api4\Campaign::create()
      ->addValue('title', $campaignTitle)
      ->addValue('name', $campaignName)
      ->addValue('campaign_type_id', $campaignTypeId)
      ->execute();

    return $campaigns->first();
  }

  private function findCampaignType($campaignTypeLabel) {
    $campaignTypes = \Civi\Api4\OptionValue::get()
      ->addWhere('label', '=', $campaignTypeLabel)
      ->addWhere('option_group_id', '=','campaign_type')
      ->execute();

    if ($campaignTypes->count() > 0) {
      return $campaignTypes->first();
    }
    else {
      return FALSE;
    }
  }

  private function createCampaignType($campaignTypeLabel) {
    $campaignTypeName = CRM_Utils_String::munge($campaignTypeLabel, '_', 64);
    $campaignTypes = \Civi\Api4\OptionValue::create()
      ->addValue('label', $campaignTypeLabel)
      ->addValue('name', $campaignTypeName)
      ->addValue('option_group', 'campaign_type')
      ->execute();

    return $campaignTypes->first();
  }

}
