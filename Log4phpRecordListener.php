<?php

require_once 'phing/BuildListener.php';
include_once 'phing/BuildEvent.php';
require_once 'Logger.php';

class Log4phpRecordListener implements BuildListener {
    
    /**
     * 
     * @var array
     */
    private $events = array();
    
    /**
     * 
     * @var Log4phpRecordListener
     */
    private static $instance = null;
    
    private $targets = array();
    
    private $listening = false;
    
    /**
     * @return Log4phpRecordListener 
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    
    public function isListening() {
        return $this->listening;
    }
    
    public function setListening($listen) {
        $this->listening = $listen;
    }
    
    private function __construct() { }
    
    public function setLogFile($file) {
        $this->logFile = $file;
    }
    
    public function buildStarted(BuildEvent $event) { }

    public function buildFinished(BuildEvent $event) { }

    public function targetStarted(BuildEvent $event) { }

    /**
     * logs all messages of this target
     *
     * @param BuildEvent $event
     * @see BuildEvent#getException()
     */
    public function targetFinished(BuildEvent $event) {
        if (!$target = $event->getTarget()) {
            return;
        }

        $events = $this->findEventsForLog($target);
            
        if ($startTask = $this->getStartRecordTask($target)) {
            foreach ($events as $event) {
                if ($event instanceof BuildEvent) {
                    $startTask->addMessage($event); 
                }
            }
        
            $startTask->log();
        }
        
        $this->events = array();
    }
    
    private function getStartRecordTask(Target $target) {
        $tasks = $this->targets[$target->getName()];
        
        if (!is_array($tasks)) {
            return null;
        }
        
        foreach ($tasks as $task) {
            if ($task->isStart()) {
                return $task;
            }
        }
        
        return null;
    }
    
    private function findEventsForLog(Target $target) {       
        $targetName = $target->getName();
        if (array_key_exists($targetName, $this->events)) {
            
            return $this->events[$targetName];
        }

        return array();
    }

    public function taskStarted(BuildEvent $event) { }

    /**
     *  adds the start/stop record tasks 
     *
     *  @param BuildEvent $event
     *  @see BuildEvent::getException()
     */
    public function taskFinished(BuildEvent $event) { 
       if ($task = $event->getTask()) {
            if (!$targetOfTask = $event->getTarget()) {
                return;
            }

            if (!$task instanceof Log4phpRecordTask) {
                return;
            }
            
            if (!$this->stillRecording($targetOfTask) || $task->isStop()) {
                $targetName = $targetOfTask->getName();
                $this->targets[$targetName][] = $task;        
                
                if ($targetOfTask instanceof Target && !array_key_exists($targetName, $this->events)) {
                    $this->events[$targetName] = array();
                }
            }
        }    
    }
    
    /**
     *  adds for the current target a log-message if the recording is started/event has the correct priority
     *
     *  @param BuildEvent $event
     *  @see BuildEvent::getMessage()
     */
    public function messageLogged(BuildEvent $event) {
        if (!$target = $event->getTarget()) {
            return;
        }
        
        if ($this->stillRecording($target)) {
            $targetName = $target->getName();
            if ($this->needsRecording($event) && array_key_exists($targetName, $this->events)) {
                $this->events[$targetName][] = $event;            
            }
        }    
    }  
    
    private function needsRecording(BuildEvent $event) {
        return $event->getPriority() == 2;
    }
    
    private function stillRecording(Target $target) {
        $targetName = $target->getName();
        if (!array_key_exists($targetName, $this->targets)) {
            return false;
        } 
        
        $started = false;
        $stoped = false;
        foreach ($this->targets[$targetName] as $recordTask) {
            if ($recordTask->isStart()) {
                 $started = true;       
            } 
            
            if ($recordTask->isStop()) {
                $stoped = true;
            }
        }
        
        return $started && !$stoped;
    }
    
    /**
     * @return array 
     */
    public function getEvents() {
        return $this->events;
    }
    
    /**
     * @return array 
     */
    public function getTargets() {
        return $this->targets;
    }
}