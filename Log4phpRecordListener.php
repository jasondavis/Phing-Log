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
            
        foreach ($events as $event) {
            if ($event instanceof BuildEvent) {
                $this->rootLogger->debug($event->getMessage());
            }
        }
        
        $this->events = array();
    }
    
    private function findEventsForLog(Target $target) {       
        $targetName = $target->getName();
        if (array_key_exists($targetName, $this->events)) {
            var_dump(count($this->events[$targetName]));
            
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

            $targetName = $targetOfTask->getName();
            if ($this->startRecording($task)) {
                if (!array_key_exists($targetName, $this->targets)) {
                    $this->targets[$targetName][] = Log4phpRecordTask::START;        
                }
            }
            
            if ($this->startRecording($task) === false) {
                $this->targets[$targetName][] = Log4phpRecordTask::STOP;
            }
            
            if ($targetOfTask instanceof Target && !array_key_exists($targetName, $this->events)) {
                $this->events[$targetName] = array();
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

        return array_key_exists($targetName, $this->targets) && 
               in_array(Log4phpRecordTask::START, $this->targets[$targetName]) && 
               !in_array(Log4phpRecordTask::STOP, $this->targets[$targetName]);
    }
    
    public function getEvents() {
        return $this->events;
    }
    
    public function getTargets() {
        return $this->targets;
    }
    
    private function startRecording(Task $task) {
        if ($task instanceof Log4phpRecordTask && $task->getAction() == Log4phpRecordTask::STOP) {
            return false;
        }
        
        if ($task instanceof Log4phpRecordTask && $task->getAction() == Log4phpRecordTask::START) {
            return true;
        }
    }

}