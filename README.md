Description
===========

The main goal is to implement an extension that provides the functionality of the ant-task <record>

Configuration
=============

Path to log4php-lib should be in the include path.

go to the phing-dir classes/phing/tasks and run 

        git clone https://github.com/floriansemm/Phing-Log.git log4php

Options
=======

The tag `<record />` has the options

* action - could be start/stop (default: start)
* file - the log file (default: build.log)
* append - append the messages or create a new log file (default: true)


Usage
=====

To log tasks just declare a "start-record" task and all tasks under this declaration will be logged:

		<record />

In this case the default-options are:

        - append = true
        - log-file = ./build.log

	
To log only a section use the action `start` and `stop`:

		<record file="myLogfile.log" />
		<echo msg="doing other Stuff" />
		<echo msg="log me too" />
		<record action="stop" />

An action `start` is not needed. The important thing is the `stop` action. If there is another `file` in the `stop` task declare, it will be ignored.


Example
=======
		<?xml version="1.0" encoding="UTF-8"?>
		<project name="FooBar" default="build" basedir=".">
			<taskdef name="record" classname="phing.tasks.log4php.Log4phpRecordTask" />


			<!-- ============================================  -->
			<!-- Target: prepare                               -->
			<!-- ============================================  -->
			<target name="prepare">
				<record file="./prepare.log" append="false" />
				<echo msg="doing other Stuff" />
				<echo msg="log me too" />
				<record action="stop" />
				
				<echo msg="not logged" />
				
				
			</target>

			<!-- ============================================  -->
			<!-- Target: build                                 -->
			<!-- ============================================  -->
			<target name="build" depends="prepare">
				<record file="./build.log" />
				
				<echo msg="doing other Stuff" />
				<echo msg="log me too" />
				
				<record action="stop" />
				
				<echo msg="not logged" />
			</target>
		</project>

   