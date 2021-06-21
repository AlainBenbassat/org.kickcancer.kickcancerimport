<?php

class CRM_Kickcancerimport_Event {
  private const EVENT_TYPE_FUNDRAISER = 3;
  private const PARTICIPANT_STATUS_ATTENDED = 2;
  private const PARTICIPANT_ROLE_ATTENDEE = 1;

  public function create($eventName, $eventStartDate, $eventEndDate) {
    $event = $this->getAndCreateIfNotExists($eventName, $eventStartDate, $eventEndDate);
    return $event;
  }

  public function createParticipant($contactId, $eventId, $registrationDate) {
    $params = [
      'sequential' => 1,
      'event_id' => $eventId,
      'contact_id' => $contactId,
      'role_id' => self::PARTICIPANT_ROLE_ATTENDEE,
      'status_id' => self::PARTICIPANT_STATUS_ATTENDED,
      'register_date' => $registrationDate,
    ];

    $p = civicrm_api3('Participant', 'create', $params);
    return $p['values'][0];
  }

  public function createEventPayment($participantId, $contributionId) {
    $params = [
      'sequential' => 1,
      'participant_id' => $participantId,
      'contribution_id' => $contributionId,
    ];

    $ep = civicrm_api3('ParticipantPayment', 'create', $params);
    return $ep['values'][0];
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
