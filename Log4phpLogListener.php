<?php

require_once 'phing/BuildListener.php';
include_once 'phing/BuildEvent.php';
require_once 'Logger.php';

class LogListener implements BuildListener {
    
    /**
     * 
     * @var LoggerRoot
     */
    private $rootLogger;
    
    private $logFile;
    
    public function __construct($logFile) {echo $logFile;
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
 						'file' => $logFile
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
    function buildFinished(BuildEvent $event) {
        //print_r('buildFinished');
    }

    /**
     * Fired when a target is started.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent::getTarget()
     */
    function targetStarted(BuildEvent $event) {
        //print_r('targetStarted');
    }

    /**
     * Fired when a target has finished.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent#getException()
     */
    function targetFinished(BuildEvent $event) {
        //print_r('targetFinished');
    }

    /**
     * Fired when a task is started.
     *
     * @param BuildEvent The BuildEvent
     * @see BuildEvent::getTask()
     */
    function taskStarted(BuildEvent $event) {
        //print_r('taskStarted');
    }

    /**
     *  Fired when a task has finished.
     *
     *  @param BuildEvent The BuildEvent
     *  @see BuildEvent::getException()
     */
    function taskFinished(BuildEvent $event) {
        //print_r('taskFinished');
    }

    /**
     *  Fired whenever a message is logged.
     *
     *  @param BuildEvent The BuildEvent
     *  @see BuildEvent::getMessage()
     */
    function messageLogged(BuildEvent $event) {
        //print_r('messageLogged');
        $this->rootLogger->debug($event->getMessage());
    }  
}