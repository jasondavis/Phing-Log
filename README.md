Description
===========

The main goal is to implement an extension that provides the functionality of the ant-task <record>

Configuration
=============

Path to log4php-lib should be in the include path.

go to the phing-dir classes/phing/tasks and run 

        git clone https://github.com/floriansemm/Phing-Log.git log4php


Following configuration logs all events.


		<?xml version="1.0" encoding="UTF-8"?>
		<project name="FooBar" default="build" basedir=".">
			<taskdef name="record" classname="phing.tasks.log4php.Log4phpRecordTask" />
		
		
		    <!-- ============================================  -->
		    <!-- Target: prepare                               -->
		    <!-- ============================================  -->
		    <target name="prepare">
		    	<record file="./build.log" />
		        <echo msg="doing other Stuff" />
		        <echo msg="Making directory ./build" />
		        <mkdir dir="./build" />
		    </target>
		
		    <!-- ============================================  -->
		    <!-- Target: build                                 -->
		    <!-- ============================================  -->
		    <target name="build" depends="prepare">
		        <echo msg="Copying files to build directory..." />
		
		        <echo msg="Copying ./testfile.php to ./build directory..." />
		        <copy file="./testfile.php" tofile="./build/testfile.php" />
		    </target>
		
		   
		</project>