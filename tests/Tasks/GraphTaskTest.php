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
 * Test case for the graph install task.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucGraphTaskTest extends phpucAbstractTaskTest
{
    /**
     * Tests that the graph task generates the contents into the logs/prj/
     * directory if no artifacts folder exists.
     *
     * @return void
     */
    public function testInstallGraphExampleWithoutArtifactsDirectory()
    {
        $command = $this->prepareTestAndQueryCommandAttribute();

        $this->assertEquals( 'logs/${project.name}', substr( $command, -20 ) );
    }

    /**
     * Tests that the graph task generates an execute-tag with the output
     * directory artifacts/${project.name}, if an artifacts directory exists.
     *
     * @return void
     */
    public function testInstallGraphExampleWithArtifactsDirectory()
    {
        $dirs    = array( "artifacts/{$this->projectName}" );
        $command = $this->prepareTestAndQueryCommandAttribute( $dirs );

        $this->assertEquals(
            'logs/${project.name} artifacts/${project.name}',
            substr( $command, -46 )
        );
    }

    /**
     * Creates the required test structure and returns the value of the
     * @command attribute.
     *
     * @param array $dirs Optional list of directories.
     *
     * @return string
     */
    protected function prepareTestAndQueryCommandAttribute( array $dirs = array() )
    {
        // Create a dummy cruise control config
        $this->createCCConfig();

        // Append default log directory
        $dirs[] = "logs/{$this->projectName}";

        // Create a dummy cc structure
        $this->createTestDirectories( $dirs );

        $this->prepareArgv(
            array(
                'example',
                '--project-name',
                $this->projectName,
                PHPUC_TEST_DIR
            )
        );

        $input = new phpucConsoleInput();
        $input->parse();

        $task = new phpucGraphTask();
        $task->setConsoleArgs( $input->args );
        $task->execute();

        $dom = new DOMDocument();
        $dom->load( PHPUC_TEST_DIR . '/config.xml' );

        $xpath  = new DOMXPath( $dom );
        $result = $xpath->query( '//project/publishers/execute/@command' );

        $this->assertEquals( 1, $result->length );

        return $result->item( 0 )->nodeValue;
    }

    /**
     * This test checks whether --without-ezc-graph option has been set properly
     *
     * @covers phpucGraphTask::registerCommandExtension
     *
     * @return void
     */
    public function testGraphTaskIsIgnored()
    {
        $this->prepareArgv(
            array( 'example', PHPUC_TEST_DIR, '--without-ezc-graph' )
        );

        $input = new phpucConsoleInput();
        $input->parse();

        $command = phpucAbstractCommand::createCommand(
                    $input->args->command
        );
        $command->setConsoleArgs( $input->args );

        $cmdTasks = $command->createTasks();

        $this->assertPhpucTaskNotOnTheList( $cmdTasks, 'phpucGraphTask' );
    }
}