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

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Base class for most task tests.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
abstract class phpucAbstractTaskTest extends phpucAbstractTest
{
    /**
     * Name of the default test project
     *
     * @type string
     * @var string $projectName
     */
    protected $projectName = 'test-project';

    /**
     * Creates some directories that fake the CruiseControl directory structure.
     *
     * @return void
     */
    protected function createCCSkeleton()
    {
        $this->createTestDirectories(
            array(
                'projects',
                "projects/{$this->projectName}",
                'logs',
                "logs/{$this->projectName}",
            )
        );

        // Create CruiseControl config
        $this->createCCConfig();
        // Create empty and build file
        $this->createCCBuild();
    }

    /**
     * Create an empty ant build xml file.
     *
     * @return void
     */
    protected function createCCBuild()
    {
        file_put_contents(
            PHPUC_TEST_DIR . "/projects/{$this->projectName}/build.xml",
            sprintf(
                '<?xml version="1.0" encoding="UTF-8"?>
                 <project name="%s" basedir="." default="build">
                   <target name="build" />
                 </project>',
                 $this->projectName
            )
        );
    }

    /**
     * Creates a default cruisecontrol config with the test project.
     *
     * @return string The config.xml path.
     */
    protected function createCCConfig()
    {
        $file = PHPUC_TEST_DIR . '/config.xml';

        file_put_contents(
            $file,
            sprintf(
                '<?xml version="1.0"?>
                 <cruisecontrol>
                   <project name="%s" buildafterfailed="false">
                     <modificationset />
                     <bootstrappers />
                     <schedule>
                       <ant anthome="/" interval="0" />
                     </schedule>
                     <publishers />
                   </project>
                 </cruisecontrol>',
                 $this->projectName
            )
        );

        $dom = new DOMDocument();
        $dom->load( $file );

        $xpath = new DOMXPath( $dom );
        $nodes = $xpath->query( "/cruisecontrol/project[@name='{$this->projectName}']" );

        $this->assertEquals( 1, $nodes->length );

        return $file;
    }

    /**
     * Asserts that given task is on the task list
     *
     * @see phpucAbstractCommand::createTasks()
     *
     * @return void
     */
    protected function assertPhpucTaskOnTheList(array $taskList, $taskName)
    {
        $taskTypes = array();

        foreach ( $taskList as $task ) {

            $taskTypes[] = get_class( $task );
        }

        $this->assertContains(
            $taskName,
            $taskTypes
        );
    }

    /**
     * Asserts that given task is not on the task list
     *
     * @see phpucAbstractCommand::createTasks()
     *
     * @return void
     */
    protected function assertPhpucTaskNotOnTheList(array $taskList, $taskName)
    {
        $taskTypes = array();

        foreach ( $taskList as $task ) {

            $taskTypes[] = get_class( $task );
        }

        $this->assertNotContains(
            $taskName,
            $taskTypes
        );
    }
}