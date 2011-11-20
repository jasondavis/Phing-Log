<?php

require_once "phing/Task.php";
require_once "phing/tasks/log4php/Log4phpRecordListener.php";
require_once 'Logger.php';


class Log4phpRecordTask extends Task {
    private $file = 'build.log';
    
    private $action = self::START;
    
    private $messages = array();
    
    private $append = true;
    
    const STOP = 'stop';
    const START = 'start';
    
    public function init() {
        $recordListener = Log4phpRecordListener::getInstance();
       
        if ($recordListener->isListening() == false) {
            $this->project->addBuildListener($recordListener);
            $recordListener->setListening(true);    
        }
    }
    
    public function addMessage(BuildEvent $message) {
        $this->messages[] = $message;
    }
    
    public function main() {
    
    }
    
    public function setFile($file) {
        $this->file = $file;
    }
    
    public function getFile() {
        return $this->file;
    }
    
    public function setAction($action) {
        $this->action = $action;
    }
    
    public function getAction() {
        return $this->action;
    }
        
    public function setAppend($append) {
        $this->append = $append;
    }
    
    public function isStart() {
        return $this->action == self::START;
    }
    
    public function isStop() {
        return $this->action == self::STOP;
    }
    
    public function log() {
        $targetName = $this->target->getName();
        
        $appenderName = $targetName.'_appender';
        Logger::configure(array(
 			'appenders' => array(
 				$appenderName => array(
 					'class' => 'LoggerAppenderFile',
 					'layout' => array(
 						'class' => 'LoggerLayoutTTCC'
 					),
 					'params' => array(
 						'file' => $this->file,
 					    'append' => $this->append
                    )
 				)
 			), 
 			'loggers' => array(
 			    $targetName => array(
                    'appenders' => array($appenderName),
 			        'additivity' => false,
 			        'level'		=> 'info'			    
 			    )
 			) 
 		));
 		
        $logger = Logger::getLogger($targetName);
        foreach ($this->messages as $message) {
            $logger->info($message->getMessage());
        }
    }
}