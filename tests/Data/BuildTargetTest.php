<?php
/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

/**
 * Test case for the build target object.
 *
 * @package   Data
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
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
        
        $target->executable  = 'phpuc';
        $target->dir         = PHPUC_TEST_DIR;
        $target->argLine     = 'example /opt/cruisecontrol';
        $target->output      = PHPUC_TEST_DIR;
        $target->failonerror = true;
        $target->logerror    = true;
        
        $buildFile->save();
        
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
    
    public function testBuildTargetDependsList()
    {
        // Use build file factory method here so that it is also tested :)
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $target1   = $buildFile->createBuildTarget( 'phpuc1' );
        $target2   = $buildFile->createBuildTarget( 'phpuc2' );
        $target3   = $buildFile->createBuildTarget( 'phpuc3' );
        
        $buildFile->save();
        
        $sxml  = simplexml_load_file( $this->fileName );
        $build = $sxml->xpath( '/project/target[@name="build"]' );
        
        $this->assertEquals( 'phpuc1,phpuc2,phpuc3', (string) $build[0]['depends'] );
    }
    
    /**
     * Tests the read only properties.
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
}