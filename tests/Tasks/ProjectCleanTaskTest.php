<?php
/**
 * This file is part of phpUnderControl.
 *
 * PHP Version 5.2.0
 *
 * Copyright (c) 2007-2010, Manuel Pichler <mapi@manuel-pichler.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/AbstractTaskTest.php';

/**
 * Test case for the {@link phpucProjectCleanTask} class.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucProjectCleanTaskTest extends phpucAbstractTaskTest
{
    /**
     * Creates some sample logs and artifacts.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createTestDirectories(
            array(
                "/cruisecontrol/logs/{$this->projectName}",
                "/cruisecontrol/logs/{$this->projectName}/20071211211853",
                "/cruisecontrol/logs/{$this->projectName}/20071211220903",
                "/cruisecontrol/logs/{$this->projectName}/20071217180035",
                "/cruisecontrol/logs/{$this->projectName}/20080106030401",
                "/cruisecontrol/logs/{$this->projectName}/20080109182028",
                "/cruisecontrol/logs/{$this->projectName}/20080113145726",
                "/cruisecontrol/logs/{$this->projectName}/20080114115320",
                "/cruisecontrol/logs/{$this->projectName}/20080118220842",
                "/cruisecontrol/artifacts",
                "/cruisecontrol/artifacts/{$this->projectName}/20071211211853",
                "/cruisecontrol/artifacts/{$this->projectName}/20071211220903",
                "/cruisecontrol/artifacts/{$this->projectName}/20071217180035",
                "/cruisecontrol/artifacts/{$this->projectName}/20080106030401",
                "/cruisecontrol/artifacts/{$this->projectName}/20080109182028",
                "/cruisecontrol/artifacts/{$this->projectName}/20080113145726",
                "/cruisecontrol/artifacts/{$this->projectName}/20080114115320",
                "/cruisecontrol/artifacts/{$this->projectName}/20080118220842",
            )
        );

        $this->createTestFile(
            "/cruisecontrol/logs/{$this->projectName}/log20071211211853.xml",
            file_get_contents( PHPUC_TEST_LOGS . '/log20071211211853.xml' )
        );
        $this->createTestFile(
            "/cruisecontrol/logs/{$this->projectName}/log20071211220903Lbuild.3.xml",
            file_get_contents( PHPUC_TEST_LOGS . '/log20071211220903Lbuild.3.xml' )
        );
        $this->createTestFile(
            "/cruisecontrol/logs/{$this->projectName}/log20071217180035Lbuild.18.xml",
            file_get_contents( PHPUC_TEST_LOGS . '/log20071217180035Lbuild.18.xml' )
        );
        $this->createTestFile(
            "/cruisecontrol/logs/{$this->projectName}/log20080106030401Lbuild.24.xml",
            file_get_contents( PHPUC_TEST_LOGS . '/log20080106030401Lbuild.24.xml' )
        );
        $this->createTestFile(
            "/cruisecontrol/logs/{$this->projectName}/log20080109182028Lbuild.30.xml",
            file_get_contents( PHPUC_TEST_LOGS . '/log20080109182028Lbuild.30.xml' )
        );
        $this->createTestFile(
            "/cruisecontrol/logs/{$this->projectName}/log20080113145726.xml",
            file_get_contents( PHPUC_TEST_LOGS . '/log20080113145726.xml' )
        );
        $this->createTestFile(
            "/cruisecontrol/logs/{$this->projectName}/log20080114115320.xml",
            file_get_contents( PHPUC_TEST_LOGS . '/log20080114115320.xml' )
        );
        $this->createTestFile(
            "/cruisecontrol/logs/{$this->projectName}/log20080118220842Lbuild.57.xml",
            file_get_contents( PHPUC_TEST_LOGS . '/log20080118220842Lbuild.57.xml' )
        );
    }

    /**
     * Tests that {@phpucProjectCleanTask#execute()} keeps the specified number
     * of logs and artifacts.
     *
     * @return void
     */
    public function testExecuteProjectCleanTaskWithNumberOfBuilds()
    {
        $args = $this->prepareConsoleArgs(
            array(
                'clean',
                '-j',
                $this->projectName,
                '-k',
                4,
                PHPUC_TEST_DIR . '/cruisecontrol'
            )
        );

        $task = new phpucProjectCleanTask();
        $task->setConsoleArgs( $args );
        $task->execute();

        // Check artifacts directory
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20071211211853"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20071211220903"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20071217180035"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20080106030401"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20080109182028"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20080113145726"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20080114115320"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20080118220842"
        );

        // Check logs directory
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20071211211853"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20071211220903"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20071217180035"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20080106030401"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20080109182028"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20080113145726"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20080114115320"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20080118220842"
        );

        // Check log files
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20071211211853.xml"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20071211220903Lbuild.3.xml"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20071217180035Lbuild.18.xml"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20080106030401Lbuild.24.xml"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20080109182028Lbuild.30.xml"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20080113145726.xml"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20080114115320.xml"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20080118220842Lbuild.57.xml"
        );
    }

    /**
     * Tests the {@phpucProjectCleanTask#execute()} implementation with the
     * <b>--keep-days</b> option.
     *
     * @return void
     */
    public function testExecuteProjectCleanTaskWithKeepDays()
    {
        $oldestBuild = mktime( 0, 0, 0, 1, 13, 2008 );
        $currentTime = mktime( 0, 0, 0 );

        $days = ceil( ( $currentTime - $oldestBuild ) / 86400 );

        $args = $this->prepareConsoleArgs(
            array(
                'clean',
                '-j',
                $this->projectName,
                '--keep-days',
                $days,
                PHPUC_TEST_DIR . '/cruisecontrol'
            )
        );

        $task = new phpucProjectCleanTask();
        $task->setConsoleArgs( $args );
        $task->execute();

        // Check artifacts directory
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20071211211853"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20071211220903"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20071217180035"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20080106030401"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20080109182028"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20080113145726"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20080114115320"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/artifacts/{$this->projectName}/20080118220842"
        );

        // Check logs directory
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20071211211853"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20071211220903"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20071217180035"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20080106030401"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20080109182028"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20080113145726"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20080114115320"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/20080118220842"
        );

        // Check log files
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20071211211853.xml"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20071211220903Lbuild.3.xml"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20071217180035Lbuild.18.xml"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20080106030401Lbuild.24.xml"
        );
        $this->assertFileNotExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20080109182028Lbuild.30.xml"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20080113145726.xml"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20080114115320.xml"
        );
        $this->assertFileExists(
            PHPUC_TEST_DIR . "/cruisecontrol/logs/{$this->projectName}/log20080118220842Lbuild.57.xml"
        );
    }

    /**
     * Tests that the validate method throws an exception when the specified
     * project does not exist.
     *
     * @return void
     * @expectedException phpucValidateException
     */
    public function testExecuteProjectCleanTaskThrowsValidationException()
    {
        $args = $this->prepareConsoleArgs(
            array(
                'clean',
                '-j',
                __FUNCTION__,
                PHPUC_TEST_DIR . '/cruisecontrol'
            )
        );

        $task = new phpucProjectCleanTask();
        $task->setConsoleArgs( $args );
        $task->validate();
    }
}