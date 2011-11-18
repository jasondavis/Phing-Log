<?php

require_once "phing/Task.php";
require_once "phing/tasks/log4php/Log4phpLogListener.php";


class Log4phpRecordTask extends Task {
    private $file;
    
    public function main() {
        echo $this->file;
        
        $logListener = new LogListener($this->file);
        
        $this->project->addBuildListener($logListener);        
        
    }
    
    public function setFile($file) {
        $this->file = $file;
    }
}