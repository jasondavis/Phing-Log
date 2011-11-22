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
    
    /**
     * Fired before any targets are started.
     *
     * @param BuildEvent The BuildEvent
     */
    public function buildStarted(BuildEvent $event) { }

    /**
     * Fired after the last target has finished.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent::getException()
     */
    public function buildFinished(BuildEvent $event) { }

    /**
     * Fired when a target is started.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent::getTarget()
     */
    public function targetStarted(BuildEvent $event) { }

    /**
     * Fired when a target has finished.
     *
     * @param BuildEvent The BuildEvent
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
        }
        
        $startTask->log();
        
        $this->events = array();
    }
    
    private function getStartRecordTask(Target $target) {
        $tasks = $this->targets[$target->getName()];
        
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

    /**
     * Fired when a task is started.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent::getTask()
     */
    public function taskStarted(BuildEvent $event) { }
    

    /**
     *  Fired when a task has finished.
     *
     *  @param BuildEvent The BuildEvent
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
     *  Fired whenever a message is logged.
     *
     *  @param BuildEvent The BuildEvent
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
    
    public function getEvents() {
        return $this->events;
    }
    
    public function getTargets() {
        return $this->targets;
    }
    
    private function startRecording(Task $task) {
        if ($task instanceof Log4phpRecordTask && $task->isStop()) {
            return false;
        }
        
        if ($task instanceof Log4phpRecordTask && $task->isStart()) {
            return true;
        }
    }

}