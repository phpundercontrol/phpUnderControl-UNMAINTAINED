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
 * @author    Sebastian Marek <proofek@gmail.com>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the apply ant task.
 *
 * @package   Tasks
 * @author    Sebastian Marek <proofek@gmail.com>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucApplyAntTaskTest extends phpucAbstractTest
{
    /**
     * The test project name.
     *
     * @type string
     * @var string $projectName
     */
    protected $projectName = 'php-under-control';

    /**
     * The test build file name.
     *
     * @type string
     * @var string $fileName
     */
    protected $fileName = null;

    /**
     * The constructor sets the build file name.
     *
     * @param string $name An optional test case name.
     * @param array  $data An optional data array.
     */
    public function __construct( $name = null, array $data = array() )
    {
        parent::__construct( $name, $data );

        $this->fileName = PHPUC_TEST_DIR . '/build.xml';
    }

    /**
     * Tests the apply target is generated properly with default executable set.
     *
     * @covers phpucApplyAntTask::__construct
     * @covers phpucApplyAntTask::buildXml
     *
     * @return void
     */
    public function testBuildApplyTargetWithDefaultExecutable()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $target    = $buildFile->createBuildTarget( 'phpuc' );

        $taskArgLine = '-l';
        $applyTask = phpucAbstractAntTask::create( $buildFile, 'apply' );
        $applyTask->dir         = PHPUC_TEST_DIR;
        $applyTask->argLine     = $taskArgLine;
        $applyTask->failonerror = true;
        $applyTask->logerror    = true;

        $target->addTask($applyTask);
        $buildFile->store();

        $sxml  = simplexml_load_file( $this->fileName );

        $build = $sxml->xpath( '/project/target[@name="build"]' );
        $phpuc = $sxml->xpath( '/project/target[@name="phpuc"]' );

        $this->assertEquals( 1, count( $build ) );
        $this->assertEquals( 1, count( $phpuc ) );

        $this->assertEquals( 'phpuc', (string) $build[0]['depends'] );
        $this->assertEquals( 'phpuc', (string) $phpuc[0]['name'] );

        $apply = $phpuc[0]->apply;

        $this->assertType( 'SimpleXMLElement', $apply );
        $this->assertEquals( 'php', (string) $apply['executable'] );
        $this->assertEquals( 'on',  (string) $apply['failonerror'] );
        $this->assertEquals( 'on',  (string) $apply['logerror'] );
        $this->assertEquals( PHPUC_TEST_DIR, (string) $apply['dir'] );

        $this->assertType( 'SimpleXMLElement', $apply->arg );

        $this->assertEquals( $taskArgLine, (string) $apply->arg['line'] );
    }

    /**
     * Tests the apply target is generated properly with custom executable set.
     *
     * @covers phpucApplyAntTask::__construct
     * @covers phpucApplyAntTask::buildXml
     *
     * @return void
     */
    public function testBuildApplyTargetWithCustomExecutable()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $target    = $buildFile->createBuildTarget( 'phpuc' );

        $php5        = 'php5';
        $taskArgLine = '-l';
        $applyTask = phpucAbstractAntTask::create( $buildFile, 'apply' );
        $applyTask->executable = $php5;
        $applyTask->dir         = PHPUC_TEST_DIR;
        $applyTask->argLine     = $taskArgLine;
        $applyTask->failonerror = true;
        $applyTask->logerror    = true;

        $target->addTask($applyTask);
        $buildFile->store();

        $sxml  = simplexml_load_file( $this->fileName );

        $build = $sxml->xpath( '/project/target[@name="build"]' );
        $phpuc = $sxml->xpath( '/project/target[@name="phpuc"]' );

        $this->assertEquals( 1, count( $build ) );
        $this->assertEquals( 1, count( $phpuc ) );

        $this->assertEquals( 'phpuc', (string) $build[0]['depends'] );
        $this->assertEquals( 'phpuc', (string) $phpuc[0]['name'] );

        $apply = $phpuc[0]->apply;

        $this->assertType( 'SimpleXMLElement', $apply );
        $this->assertEquals( $php5, (string) $apply['executable'] );
        $this->assertEquals( 'on', (string) $apply['failonerror'] );
        $this->assertEquals( 'on', (string) $apply['logerror'] );
        $this->assertEquals( PHPUC_TEST_DIR, (string) $apply['dir'] );

        $this->assertType( 'SimpleXMLElement', $apply->arg );

        $this->assertEquals( $taskArgLine, (string) $apply->arg['line'] );
    }

    /**
     * Tests the apply target is generated properly with subtasks attached.
     *
     * @covers phpucApplyAntTask::__construct
     * @covers phpucApplyAntTask::buildXml
     *
     * @return void
     */
    public function testBuildApplyTargetWithSubtasksAttached()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $target    = $buildFile->createBuildTarget( 'phpuc' );

        $filesetTask = $this->getMock(
            'phpucFilesetAntTask',
            array( 'buildXml' ),
            array(),
            '',
            false
        );
        $filesetTask
            ->expects( $this->once() )
            ->method( 'buildXml' );

        $taskArgLine = '-l';
        $applyTask = phpucAbstractAntTask::create( $buildFile, 'apply' );
        $applyTask->dir         = PHPUC_TEST_DIR;
        $applyTask->argLine     = $taskArgLine;
        $applyTask->failonerror = true;
        $applyTask->logerror    = true;
        $applyTask->addTask($filesetTask);

        $target->addTask($applyTask);
        $buildFile->store();

        $sxml  = simplexml_load_file( $this->fileName );

        $build = $sxml->xpath( '/project/target[@name="build"]' );
        $phpuc = $sxml->xpath( '/project/target[@name="phpuc"]' );

        $this->assertEquals( 1, count( $build ) );
        $this->assertEquals( 1, count( $phpuc ) );

        $this->assertEquals( 'phpuc', (string) $build[0]['depends'] );
        $this->assertEquals( 'phpuc', (string) $phpuc[0]['name'] );

        $apply = $phpuc[0]->apply;

        $this->assertType( 'SimpleXMLElement', $apply );
        $this->assertEquals( 'php', (string) $apply['executable'] );
        $this->assertEquals( 'on',  (string) $apply['failonerror'] );
        $this->assertEquals( 'on',  (string) $apply['logerror'] );
        $this->assertEquals( PHPUC_TEST_DIR, (string) $apply['dir'] );

        $this->assertType( 'SimpleXMLElement', $apply->arg );

        $this->assertEquals( $taskArgLine, (string) $apply->arg['line'] );
    }
}