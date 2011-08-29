<?php

/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 * 
 *      http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @category   tests
 * @package    log4php
 * @subpackage renderers
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version    SVN: $Id$
 * @link       http://logging.apache.org/log4php
 */

class Fruit {
    public $test1 = 'test1';
    public $test2 = 'test2';
    public $test3 = 'test3';
}

class FruitRenderer implements LoggerRendererObject {
    public function render($o) {
        return $o->test1 . ',' . $o->test2 . ',' . $o->test3;
    }
}

/**
 * @group configurators
 */
class LoggerConfiguratorIniTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {
		Logger :: resetConfiguration();
    }

    protected function tearDown() {
        Logger :: resetConfiguration();
    }

    public function testConfigure() {
        Logger :: configure('configurators/test1.properties');
        $root = Logger :: getRootLogger();
        self :: assertEquals(LoggerLevel :: getLevelWarn(), $root->getLevel());
        $appender = $root->getAppender("default");
        self :: assertTrue($appender instanceof LoggerAppenderEcho);
        $layout = $appender->getLayout();
        self :: assertTrue($layout instanceof LoggerLayoutSimple);

        $logger = Logger :: getLogger('mylogger');
        self :: assertEquals(LoggerLevel :: getLevelInfo(), $logger->getLevel());
        self :: assertFalse($logger->getAdditivity());

        $logger3 = Logger :: getLogger('tracer');
        self :: assertEquals(LoggerLevel :: getLevelTrace(), $logger3->getLevel());
        self :: assertFalse($logger3->getAdditivity());

        $logger2 = Logger :: getLogger('mylogger');
        $logger2->setAdditivity(true);
        self :: assertTrue($logger2->getAdditivity());
        self :: assertTrue($logger->getAdditivity());
    }

    public function testConfigureWithRootCategory() {
        Logger :: configure('configurators/test3.properties');
        $root = Logger :: getRootLogger();
        self :: assertEquals(LoggerLevel :: getLevelWarn(), $root->getLevel());
        $appender = $root->getAppender("default");
        self :: assertTrue($appender instanceof LoggerAppenderEcho);
        $layout = $appender->getLayout();
        self :: assertTrue($layout instanceof LoggerLayoutSimple);
    }

    public function testConfigureWithoutIniFile() {
        $catchedException = null;
        try {
            Logger :: configure(null, 'LoggerConfiguratorIni');
            Logger :: initialize();
            self :: assertTrue(false);
        } catch (LoggerException $e) {
            $catchedException = $e;
        }
        self :: assertNotNull($catchedException);
    }

    /**
     * @expectedException PHPUnit_Framework_Error 
     * (needed because configuring with an empty properties file raises an error)
     */
    public function testConfigureWithEmptyIniFile() {
        $catchedException = null;
        try {
            Logger :: configure('configurators/test2.properties');
            Logger :: initialize();
            self :: assertTrue(false);
        } catch (LoggerException $e) {
            $catchedException = $e;
        }
        self :: assertNotNull($catchedException);
    }

    public function testThreshold() {
        Logger :: configure('configurators/test4.properties');
        $root = Logger :: getRootLogger();
        self :: assertEquals(LoggerLevel :: getLevelWarn(), $root->getLevel());
        $appender = $root->getAppender("default");
        self :: assertTrue($appender instanceof LoggerAppenderEcho);
        $layout = $appender->getLayout();
        self :: assertTrue($layout instanceof LoggerLayoutSimple);
        $threshold = $appender->getThreshold();
        self :: assertTrue($threshold instanceof LoggerLevel);
        $e = LoggerLevel :: getLevelWarn();
        self :: assertEquals($e, $threshold);

        $appender = $root->getAppender("blub");
        self :: assertTrue($appender instanceof LoggerAppenderEcho);
        $layout = $appender->getLayout();
        self :: assertTrue($layout instanceof LoggerLayoutSimple);
        $threshold = $appender->getThreshold();
        self :: assertTrue($threshold instanceof LoggerLevel);
        $e = LoggerLevel :: getLevelInfo();
        self :: assertEquals($e, $threshold);

        $threshold = Logger :: getHierarchy()->getThreshold();
        self :: assertTrue($threshold instanceof LoggerLevel);
        $e = LoggerLevel :: getLevelWarn();
        self :: assertEquals($e, $threshold);
    }

    public function testRenderer() {
        Logger :: configure('configurators/test4.properties');
        Logger :: initialize();
        $hierarchy = Logger :: getHierarchy();
        $map = $hierarchy->getRendererMap();
        $clazz = $map->getByClassName('Fruit');
        self :: assertTrue($clazz instanceof FruitRenderer);
    }

    public function testSeveralLoggers() {
        Logger :: clear();
        Logger :: configure('configurators/test5.properties');
        $l = Logger :: getLogger('test50');
        $l->warn('muh');
        $this->assertTrue($l->getAppender('test50') instanceof LoggerAppenderFile);
        $this->assertTrue($l->getAppender('test50')->getLayout() instanceof LoggerLayoutSimple);

        $l = Logger :: getLogger('test51');
        $l->warn('m�h');
        $this->assertTrue($l->getAppender('test51') instanceof LoggerAppenderFile);
        $this->assertTrue($l->getAppender('test51')->getLayout() instanceof LoggerLayoutTTCC);
    }
    
    public function testConfigureHtmlBreaks() {
        Logger :: configure('configurators/test6.properties');
        $root = Logger :: getRootLogger();
        $appender = $root->getAppender("default");
        self :: assertTrue($appender instanceof LoggerAppenderEcho);
        self :: assertTrue($appender->getHtmlLineBreaks());
    }
}