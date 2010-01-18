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
 * Test case for the build file class.
 *
 * @package   Data
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucBuildFileTest extends phpucAbstractTest
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
     * The default build file skeleton.
     *
     * @type string
     * @var string $buildXML
     */
    protected $buildXML = null;

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

        $this->buildXML = sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL .
            '<project name="%s" default="build" basedir=".">' . PHP_EOL .
            '  <target name="build" depends=""/>' . PHP_EOL .
            '</project>',
            $this->projectName
        );
    }

    /**
     * Tests that the build file creates a new build file if new file exists.
     *
     * @return void
     */
    public function testInitNewBuildFile()
    {
        $this->assertFileNotExists( $this->fileName );

        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $buildFile->store();

        $this->assertFileExists( $this->fileName );

        $this->assertXmlStringEqualsXmlString(
            $this->buildXML,
            file_get_contents( $this->fileName )
        );
    }

    /**
     * Tests that the class reads the project name from the build.xml file.
     *
     * @return void
     */
    public function testOpenExistingBuildFile()
    {
        file_put_contents( $this->fileName, $this->buildXML );

        // Check to be sure that the file exists.
        $this->assertFileExists( $this->fileName );

        $buildFile = new phpucBuildFile( $this->fileName );

        $this->assertEquals( $this->projectName, $buildFile->projectName );
    }

    /**
     * Just tests that both properties are readonly and future non readonly
     * properties will not drop this behaviour.
     *
     * @return void
     */
    public function testPropertiesProjectNameAndFileNameAreReadonly()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );

        $this->assertTrue( isset( $buildFile->projectName ) );
        $this->assertTrue( isset( $buildFile->fileName ) );

        try
        {
            $buildFile->projectName = 'Foobar';
            $this->fail( 'OutOfRangeException expected' );
        }
        catch ( OutOfRangeException $e ) {}

        try
        {
            $buildFile->fileName = 'Foobar';
            $this->fail( 'OutOfRangeException expected' );
        }
        catch ( OutOfRangeException $e ) {}
    }

    /**
     * Tests that the magic __get() method fails with an exception for an unknown
     * property.
     *
     * @return void
     */
    public function testGetterUnknownPropertyFail()
    {
        $this->setExpectedException(
            'OutOfRangeException',
            'Unknown or writeonly property $phpuc.'
        );

        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );

        echo $buildFile->phpuc;
    }

    /**
     * Tests that dependencies on given target are defined properly
     *
     * @covers phpucBuildFile::processTargetDependencies
     *
     * @return void
     */
    public function testProcessTargetDependencies()
    {
        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $lint  = $buildFile->createBuildTarget( 'lint' );
        $buildFile->store();

        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $phpcs  = $buildFile->createBuildTarget( 'phpcs' );
        $buildFile->store();

        $buildFile = new phpucBuildFile( $this->fileName, $this->projectName );
        $phpuc = $buildFile->createBuildTarget( 'phpuc' );
        $phpuc->dependOn( 'lint' );
        $phpuc->dependOn( 'phpcs' );
        $buildFile->store();

        $sxml  = simplexml_load_file( $this->fileName );
        $build = $sxml->xpath( '/project/target[@name="phpuc"]' );
        $this->assertEquals( 'lint,phpcs', (string) $build[0]['depends'] );
    }
}