<?php
/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007-2010, Manuel Pichler <mapi@phpundercontrol.org>.
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
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/AbstractPearTaskTest.php';

/**
 * Test case for the PHP_CodeBrowser task.
 *
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucCodeBrowserTaskTest extends phpucAbstractPearTaskTest
{
    /**
     * @return void
     * @covers phpucCodeBrowserTask
     * @group tasks
     * @group unittest
     */
    public function testExecuteWithAbsoluteSourceDirectory()
    {
        $command = $this->executeCommandAndReturnExecutePublisher(
            array(
                'example',
                '--project-name',
                $this->projectName,
                '--source-dir',
                $this->projectDir,
                PHPUC_TEST_DIR
            )
        );

        $this->assertContains(
            '--log projects/${project.name}/build/logs ' .
            '--source ' . $this->projectDir . ' ' .
            '--output projects/${project.name}/build/php-code-browser',
            $command
        );
    }

    /**
     * @return void
     * @covers phpucCodeBrowserTask
     * @group tasks
     * @group unittest
     */
    public function testExecuteWithRelativeSourceDirectory()
    {
        $command = $this->executeCommandAndReturnExecutePublisher(
            array(
                'example',
                '--project-name',
                $this->projectName,
                '--source-dir',
                './src',
                PHPUC_TEST_DIR
            )
        );

        $this->assertContains(
            '--log projects/${project.name}/build/logs ' .
            '--source projects/${project.name}/source/./src ' .
            '--output projects/${project.name}/build/php-code-browser',
            $command
        );
    }

    /**
     * Executes the code browser command and returns the attribute value from
     * the execute publisher.
     *
     * @param array $argv Cli argument vector
     *
     * @return string
     */
    private function executeCommandAndReturnExecutePublisher( array $argv )
    {
        $xpath  = $this->executeCommand( $argv );
        $result = $xpath->query( '//project/publishers/execute/@command' );

        return $result->item( 0 )->nodeValue;
    }

    /**
     * @return void
     * @covers phpucCodeBrowserTask
     * @group tasks
     * @group unittest
     */
    public function testExecuteWithLogDirectoryCopyTarget()
    {
        $destination = $this->executeCommandAndReturnArtifactsPublisher(
            array(
                'example',
                '--project-name',
                $this->projectName,
                '--source-dir',
                './src',
                PHPUC_TEST_DIR
            )
        );

        $this->assertEquals( 'logs/${project.name}', $destination );
    }

    /**
     * @return void
     * @covers phpucCodeBrowserTask
     * @group tasks
     * @group unittest
     */
    public function testExecuteWithArtifactsDirectoryCopyTarget()
    {
        $this->createTestDirectories( array( 'artifacts' ) );

        $destination = $this->executeCommandAndReturnArtifactsPublisher(
            array(
                'example',
                '--project-name',
                $this->projectName,
                '--source-dir',
                './src',
                PHPUC_TEST_DIR
            )
        );

        $this->assertEquals( 'artifacts/${project.name}', $destination );
    }

    /**
     * Executes the code browser command and returns the attribute value from
     * the artifacts publisher.
     *
     * @param array $argv Cli argument vector
     *
     * @return string
     */
    private function executeCommandAndReturnArtifactsPublisher( array $argv )
    {
        $xpath  = $this->executeCommand( $argv );
        $result = $xpath->query( '//project/publishers/artifactspublisher/@dest' );

        return $result->item( 0 )->nodeValue;
    }

    /**
     * Executes the PHP_CodeBrowser command and returns an xpath instance for
     * the CruiseControl config file.
     *
     * @param array $argv Cli argument vector
     *
     * @return DOMXPath
     */
    private function executeCommand( array $argv )
    {
        $this->createCCSkeleton();

        $task = new phpucCodeBrowserTask();
        $task->setConsoleArgs( $this->prepareConsoleArgs( $argv ) );
        $task->execute();

        $dom = new DOMDocument();
        $dom->load( PHPUC_TEST_DIR . '/config.xml' );

        return new DOMXPath( $dom );
    }

    /**
     * This test checks whether --without-code-browser option has been set properly
     *
     * @covers phpucCodeBrowserTask::registerCommandExtension
     *
     * @return void
     */
    public function testCodeBrowserTaskIsIgnored()
    {
        $this->prepareArgv(
            array( 'example', PHPUC_TEST_DIR, '--without-code-browser' )
        );

        $input = new phpucConsoleInput();
        $input->parse();

        $command = phpucAbstractCommand::createCommand(
                    $input->args->command
        );
        $command->setConsoleArgs( $input->args );

        $cmdTasks = $command->createTasks();

        $this->assertPhpucTaskNotOnTheList( $cmdTasks, 'phpucCodeBrowserTask' );
    }
}
