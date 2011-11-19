<?php

require_once 'phing/BuildListener.php';
include_once 'phing/BuildEvent.php';
require_once 'Logger.php';

class Log4phpRecordListener implements BuildListener {
    
    /**
     * 
     * @var LoggerRoot
     */
    private $rootLogger;
    
    private $logFile;
    
    /**
     * 
     * @var Log4phpRecordTask
     */
    private $start = null;
    
    /**
     * 
     * @var Log4phpRecordTask
     */
    private $stop = null;
    
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
    
    private $target = null;
    
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
    
    private function __construct() {
        Logger::configure(array(
 			'rootLogger' => array(
 				'appenders' => array('build')
 			),
 			'appenders' => array(
 				'build' => array(
 					'class' => 'LoggerAppenderFile',
 					'layout' => array(
 						'class' => 'LoggerLayoutTTCC'
 					),
 					'params' => array(
 						'file' => 'debug.log'
                    )
 				)
 			) 
 		));
 		
        $this->rootLogger = Logger::getRootLogger();
    }
    
    public function setLogFile($file) {
        $this->logFile = $file;
    }
    
    /**
     * Fired before any targets are started.
     *
     * @param BuildEvent The BuildEvent
     */
    function buildStarted(BuildEvent $event) {
        $this->rootLogger->info($event->getMessage());
    }

    /**
     * Fired after the last target has finished.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent::getException()
     */
    function buildFinished(BuildEvent $event) { }

    /**
     * Fired when a target is started.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent::getTarget()
     */
    function targetStarted(BuildEvent $event) { }

    /**
     * Fired when a target has finished.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent#getException()
     */
    public function targetFinished(BuildEvent $event) {print('Target zuende');
        if (!$target = $event->getTarget()) {
            return;
        }

        $events = $this->findEventsForLog($target);
            
        foreach ($events as $event) {
            if ($event instanceof BuildEvent) {
                $this->rootLogger->debug($event->getMessage());
            }
        }
        
        $this->events = array();
    }
    
    private function findEventsForLog(Target $target) {
        if (null === $this->start && null === $this->stop) {
            return array();
        }
        
        if (null !== $this->stop && null !== $this->start) {
            return $this->events;
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
    function taskFinished(BuildEvent $event) { 
       if ($task = $event->getTask()) {
            if ($this->startRecording($task)) {
                $this->start = $task;
            }
            
            if ($this->startRecording($task)) {
                $this->stop = $task;
            }
            
            if ($this->target === null && $targetOfTarget = $event->getTarget()) {
                $this->target = $targetOfTarget;
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
        if ($this->stillRecording() && !in_array($event, $this->events)) {
            if ($this->needsRecording($event)) {
                $this->events[] = $event;            
            }
        }    
    }  
    
    private function needsRecording(BuildEvent $event) {
        return $event->getTarget() == $this->target && $event->getPriority() == 2;
    }
    
    private function stillRecording() {
        return $this->start !== null && $this->stop === null;
    }
    
    public function getEvents() {
        return $this->events;
    }
    
    private function startRecording($task) {
        if ($task instanceof Log4phpRecordTask && $task->getAction() == 'stop') {
            return false;
        }
        
        if ($task instanceof Log4phpRecordTask && $task->getAction() == 'start') {
            return true;
        }
    }

}