<?php

class CRM_Kickcancerimport_Queue {
  private $queueTitle;
  private $queueEndUrl;
  private $queue;

  public function __construct($name, $title, $endUrl) {
    $this->queue = CRM_Queue_Service::singleton()->create([
      'type' => 'Sql',
      'name' => $name,
      'reset' => TRUE, // flush queue upon creation
    ]);

    $this->queueTitle = $title;
    $this->queueEndUrl = $endUrl;
  }

  public function addTask($class, $method, $data) {
    $task = new CRM_Queue_Task([$class, $method], $data);
    $this->queue->createItem($task);
  }

  public function run() {
    $runner = new CRM_Queue_Runner([
      'title' => $this->queueTitle,
      'queue' => $this->queue,
      'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE,
      'onEndUrl' => CRM_Utils_System::url($this->queueEndUrl, 'reset=1'),
    ]);
    $runner->runAllViaWeb();
  }

}