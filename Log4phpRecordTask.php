<?php

require_once "phing/Task.php";
require_once "phing/tasks/log4php/Log4phpRecordListener.php";


class Log4phpRecordTask extends Task {
    private $file;
    
    private $action = 'start';
    
    private $logLevel = 'info';
    
    public function init() {
        $recordListener = Log4phpRecordListener::getInstance();
       
        if ($recordListener->isListening() == false) {
            $this->project->addBuildListener($recordListener);
            $recordListener->setListening(true);    
        }
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
    
    public function setLogLevel($logLevel) {
        $this->logLevel = $logLevel;
    }

    public function getLogLevel() {
        return $this->logLevel;
    }
}