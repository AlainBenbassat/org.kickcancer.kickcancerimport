<?php

class CRM_Kickcancerimport_Event {
  const EVENT_TYPE_FUNDRAISER = 3;

  public function create($eventName, $eventStartDate, $eventEndDate) {
    $event = $this->getAndCreateIfNotExists($eventName, $eventStartDate, $eventEndDate);
    return $event;
  }

  public function createParticipant($contactId, $eventId, $registrationDate) {

  }

  private function getAndCreateIfNotExists($eventName, $eventStartDate, $eventEndDate) {
    $params = [
      'sequential' => 1,
      'title' => $eventName,
      'start_date' => $eventStartDate,
      'end_date' => $eventEndDate,
      'event_type_id' => self::EVENT_TYPE_FUNDRAISER,
    ];

    try {
      $e = civicrm_api3('Event', 'getsingle', $params);
      return $e;
    }
    catch (Exception $e) {
      $e = civicrm_api3('Event', 'create', $params);
      return $e['values'][0];
    }
  }
}
