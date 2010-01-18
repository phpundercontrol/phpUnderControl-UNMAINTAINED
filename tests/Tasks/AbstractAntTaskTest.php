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
 * Test case for the abstract ant task.
 *
 * @package   Tasks
 * @author    Sebastian Marek <proofek@gmail.com>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucAbstractAntTaskTest extends phpucAbstractTest
{
    /**
     * Tests that {@link phpucAbstractAntTask} is added to tasks list.
     *
     * @covers phpucAbstractAntTask::addTask
     *
     * @return void
     */
    public function testAbstractAntTaskAddsTasksToTheList()
    {
        $subTask1 = $this->getMock(
            'phpucAbstractAntTask',
            array(),
            array(),
            '',
            false
        );
        $subTask2 = $this->getMock(
            'phpucAbstractAntTask',
            array(),
            array(),
            '',
            false
        );

        $task = $this->getMock(
            'phpucAbstractAntTask',
            array( 'buildXml' ),
            array(),
            '',
            false
        );
        $task->addTask( $subTask1 );
        $task->addTask( $subTask2 );

        $this->assertEquals(
            array( $subTask1, $subTask2 ),
            PHPUnit_Util_Class::getObjectAttribute( $task, 'tasks' )
        );
    }

    /**
     * Tests that the create method returns exec ant task object
     *
     * @covers phpucAbstractAntTask::create
     *
     * @return void
     */
    public function testCreateReturnsExecTask()
    {
        $buildFile = $this->getMock(
            'phpucBuildFile',
            array(),
            array(),
            '',
            false
        );
        $task = phpucAbstractAntTask::create( $buildFile, 'exec' );

        $this->assertType( 'phpucExecAntTask', $task );
    }

    /**
     * Tests that the create method returns apply ant task object
     *
     * @covers phpucAbstractAntTask::create
     *
     * @return void
     */
    public function testCreateReturnsApplyTask()
    {
        $buildFile = $this->getMock(
            'phpucBuildFile',
            array(),
            array(),
            '',
            false
        );
        $task = phpucAbstractAntTask::create( $buildFile, 'apply' );

        $this->assertType( 'phpucApplyAntTask', $task );
    }

    /**
     * Tests that the create method returns fileset ant task object
     *
     * @covers phpucAbstractAntTask::create
     *
     * @return void
     */
    public function testCreateReturnsFilesetTask()
    {
        $buildFile = $this->getMock(
            'phpucBuildFile',
            array(),
            array(),
            '',
            false
        );
        $task = phpucAbstractAntTask::create( $buildFile, 'fileset' );

        $this->assertType( 'phpucFilesetAntTask', $task );
    }

    /**
     * Tests that the create method throws an exception
     * when incorrect task name used
     *
     * @covers phpucAbstractAntTask::create
     * @covers phpucTaskException
     *
     * @return void
     */
    public function testCreateThrowsPhpucTaskExceptionWhenAntTaskClassNotFound()
    {
        $buildFile = $this->getMock(
            'phpucBuildFile',
            array(),
            array(),
            '',
            false
        );

        $taskName = 'fake';
        $expectedClassName = 'phpucFakeAntTask';

        $this->setExpectedException(
            'phpucTaskException',
            "Cannot create '$taskName' ant task. Source class '$expectedClassName' not available."
        );

        phpucAbstractAntTask::create( $buildFile, $taskName );
    }

    /**
     * Tests access to data stored in {@link phpucAbstractAntTask::$properties}
     *
     * @covers phpucAbstractAntTask::__set
     * @covers phpucAbstractAntTask::__get
     * @covers phpucAbstractAntTask::__isset
     *
     * @return void
     */
    public function testDefinedProperties()
    {
        $buildFile = $this->getMock(
            'phpucBuildFile',
            array(),
            array(),
            '',
            false
        );
        $exec = phpucAbstractAntTask::create( $buildFile, 'exec' );

        $php = 'php5';
        $exec->executable = $php;

        $properties = PHPUnit_Util_Class::getObjectAttribute( $exec, 'properties' );
        $this->assertEquals($php, $properties['executable']);
        $this->assertEquals($php, $exec->executable);
        $this->assertTrue( isset( $exec->executable ) );
        $this->assertFalse( isset( $exec->foobar ) );
    }

    /**
     * Tests access to boolean variable stored
     * in {@link phpucAbstractAntTask::$properties}
     *
     * @covers phpucAbstractAntTask::__set
     * @covers InvalidArgumentException
     *
     * @return void
     */
    public function testSetNonBooleanVarToBooleanPropertyThrowsInvalidArgumentException()
    {
        $buildFile = $this->getMock(
            'phpucBuildFile',
            array(),
            array(),
            '',
            false
        );
        $exec = phpucAbstractAntTask::create( $buildFile, 'exec' );

        $this->setExpectedException(
            'InvalidArgumentException',
            'The property $logerror must be a boolean.'
        );
        $exec->logerror = 'string';
    }

    /**
     * Tests access to undefined property in {@link phpucAbstractAntTask::$properties}
     *
     * @covers phpucAbstractAntTask::__get
     * @covers OutOfRangeException
     *
     * @return void
     */
    public function testGetUndefinedPropertyThrowsOutOfRangeException()
    {
        $buildFile = $this->getMock(
            'phpucBuildFile',
            array(),
            array(),
            '',
            false
        );
        $exec = phpucAbstractAntTask::create( $buildFile, 'exec' );

        $this->setExpectedException(
            'OutOfRangeException',
            'Unknown or writeonly property $fake.'
        );
        $fake = $exec->fake;
    }

    /**
     * Tests setting a value of undefined property
     * in {@link phpucAbstractAntTask::$properties}
     *
     * @covers phpucAbstractAntTask::__set
     * @covers OutOfRangeException
     *
     * @return void
     */
    public function testSetUndefinedPropertyThrowsOutOfRangeException()
    {
        $buildFile = $this->getMock(
            'phpucBuildFile',
            array(),
            array(),
            '',
            false
        );
        $exec = phpucAbstractAntTask::create( $buildFile, 'exec' );

        $this->setExpectedException(
            'OutOfRangeException',
            'Unknown or readonly property $fake.'
        );
        $exec->fake = 'foobar';
    }
}