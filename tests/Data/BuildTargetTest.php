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
 * @package   Data
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the build target object.
 *
 * @package   Data
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucBuildTargetTest extends phpucAbstractTest
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
     * The ctor sets the build file name.
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
     * Tests the target generation with all target options on.
     *
     * @return void
     */
    public function testBuildTargetAllFeatures()
    {
        // Use build file factory method here so that it is also tested :)
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $target    = $buildFile->createBuildTarget( 'phpuc' );

        $execTask = phpucAbstractAntTask::create( $buildFile, 'exec' );
        $execTask->executable = 'phpuc';
        $execTask->dir         = PHPUC_TEST_DIR;
        $execTask->argLine     = 'example /opt/cruisecontrol';
        $execTask->output      = PHPUC_TEST_DIR;
        $execTask->failonerror = true;
        $execTask->logerror    = true;

        $target->addTask($execTask);
        $buildFile->store();

        $sxml  = simplexml_load_file( $this->fileName );

        $build = $sxml->xpath( '/project/target[@name="build"]' );
        $phpuc = $sxml->xpath( '/project/target[@name="phpuc"]' );

        $this->assertEquals( 1, count( $build ) );
        $this->assertEquals( 1, count( $phpuc ) );

        $this->assertEquals( 'phpuc', (string) $build[0]['depends'] );
        $this->assertEquals( 'phpuc', (string) $phpuc[0]['name'] );

        $this->assertType( 'SimpleXMLElement', $phpuc[0]->exec );

        $exec = $phpuc[0]->exec;

        $this->assertEquals( 'phpuc', (string) $exec['executable'] );
        $this->assertEquals( 'on', (string) $exec['failonerror'] );
        $this->assertEquals( 'on', (string) $exec['logerror'] );
        $this->assertEquals( PHPUC_TEST_DIR, (string) $exec['dir'] );
        $this->assertEquals( PHPUC_TEST_DIR, (string) $exec['output'] );

        $this->assertType( 'SimpleXMLElement', $exec->arg );

        $this->assertEquals( 'example /opt/cruisecontrol', (string) $exec->arg['line'] );
    }

    /**
     * Tests that the build depend list gets extended for each new target.
     *
     * @return void
     */
    public function testBuildTargetDependsList()
    {
        // Use build file factory method here so that it is also tested :)
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $buildFile->createBuildTarget( 'phpuc1' );
        $buildFile->createBuildTarget( 'phpuc2' );
        $buildFile->createBuildTarget( 'phpuc3' );

        $buildFile->store();

        $sxml  = simplexml_load_file( $this->fileName );
        $build = $sxml->xpath( '/project/target[@name="build"]' );

        $this->assertEquals( 'phpuc1,phpuc2,phpuc3', (string) $build[0]['depends'] );
    }

    /**
     * Tests the read only properties.
     *
     * @covers phpucBuildTarget::__isset
     * @covers phpucBuildTarget::__set
     *
     * @return void
     */
    public function testReadonlyPropertiesTargetNameAndBuildFile()
    {
        // Use build file factory method here so that it is also tested :)
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $target    = $buildFile->createBuildTarget( 'phpuc' );

        $this->assertTrue( isset( $target->targetName ) );
        $this->assertTrue( isset( $target->buildFile ) );

        try
        {
            $target->targetName = 'Foobar';
            $this->fail( 'OutOfRangeException expected' );
        }
        catch ( OutOfRangeException $e ) {}

        try
        {
            $target->buildFile = 'Foobar';
            $this->fail( 'OutOfRangeException expected' );
        }
        catch ( OutOfRangeException $e ) {}
    }

    /**
     * Tests that the magic __get() method fails with an exception for an unknown
     * property.
     *
     * @covers phpucBuildTarget::__get
     *
     * @return void
     */
    public function testGetterUnknownPropertyFail()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );

        $this->setExpectedException(
            'OutOfRangeException',
            'Unknown or writeonly property $phpuc.'
        );

        $target = $buildFile->createBuildTarget( 'phpuc' );
        echo $target->phpuc;
    }

    /**
     * Tests that the magic setter method for the $failonerror property fails
     * with an exception for a non boolean value.
     *
     * @covers phpucBuildTarget::__set
     *
     * @return void
     */
    public function testFailOnErrorSetterInvalidTypeFail()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );

        $this->setExpectedException(
            'InvalidArgumentException',
            'The property $failonerror must be a boolean.'
        );

        $target = $buildFile->createBuildTarget( 'phpuc' );
        $target->failonerror = 'Test';
    }

    /**
     * Tests that the magic setter method sets string value
     *
     * @covers phpucBuildTarget::__set
     *
     * @return void
     */
    public function testSetterWithStringVariable()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $target = $buildFile->createBuildTarget( 'phpuc' );

        $argLine = 'argument';
        $target->argLine = $argLine;

        $properties = PHPUnit_Util_Class::getObjectAttribute( $target, 'properties' );

        $this->assertEquals( $argLine, $properties['argLine'] );
    }

    /**
     * Tests that the magic setter method sets boolean value for property
     * like failonerror
     *
     * @covers phpucBuildTarget::__set
     *
     * @return void
     */
    public function testSetterWithBooleanVariable()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $target = $buildFile->createBuildTarget( 'phpuc' );

        $target->failonerror = true;

        $properties = PHPUnit_Util_Class::getObjectAttribute( $target, 'properties' );

        $this->assertTrue( $properties['failonerror'] );
    }

    /**
     * Tests that the magic setter method sets array value for property
     * like depends
     *
     * @covers phpucBuildTarget::__set
     *
     * @return void
     */
    public function testSetterWithArrayVariable()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $target = $buildFile->createBuildTarget( 'phpuc' );

        $target->depends = 'lint';

        $properties = PHPUnit_Util_Class::getObjectAttribute( $target, 'properties' );

        $this->assertEquals( array( 'lint' ), $properties['depends'] );
    }

     /**
     * Tests that {@link phpucAbstractAntTask} are properly added to
     * target tasks list
     *
     * @covers phpucBuildTarget::addTask
     * @covers phpucBuildTarget::getTasks
     *
     * @return void
     */
    public function testTargetTasks()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $target = $buildFile->createBuildTarget( 'phpuc' );

        $execTask  = phpucAbstractAntTask::create( $buildFile, 'exec' );
        $applyTask = phpucAbstractAntTask::create( $buildFile, 'apply' );
        $target->addTask( $execTask );
        $target->addTask( $applyTask );

        $this->assertEquals( array( $execTask, $applyTask ), $target->getTasks() );

        $tasks = PHPUnit_Util_Class::getObjectAttribute( $target, 'tasks' );
        $this->assertEquals( array( $execTask, $applyTask ), $tasks );
    }

    /**
     * Tests that dependency is properly set on build target
     *
     * @covers phpucBuildTarget::dependOn
     *
     * @return void
     */
    public function testSetDependencyOnNonExistingTarget()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $phpuc = $buildFile->createBuildTarget( 'phpuc' );

        $this->assertEquals( array(), $phpuc->depends );

        $phpuc->dependOn( 'lint' );
        $buildFile->store();

        $this->assertEquals( array(), $phpuc->depends );
    }

    /**
     * Tests that dependency is properly set on build target
     *
     * @covers phpucBuildTarget::dependOn
     *
     * @return void
     */
    public function testSetDependencyOnExistingTarget()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $lint  = $buildFile->createBuildTarget( 'lint' );
        $buildFile->store();

        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $phpuc = $buildFile->createBuildTarget( 'phpuc' );

        $this->assertEquals( array(), $phpuc->depends );
        $phpuc->dependOn( 'lint' );
        $buildFile->store();

        $this->assertEquals( array( 'lint' ), $phpuc->depends );
    }
}