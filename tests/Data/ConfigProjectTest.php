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

require_once dirname( __FILE__ ) . '/AbstractConfigTest.php';

/**
 * Test cases for CruiseControl project configuration.
 *
 * @package   Data
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucConfigProjectTest extends phpucAbstractConfigTest
{
    /**
     * The context config file instance.
     *
     * @type phpucConfigFile
     * @var phpucConfigFile $config
     */
    protected $config = null;

    /**
     * Creates a new/clean {@link phpucConfigFile} instance.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createTestFile( '/config.xml', $this->testXml );

        $this->config = new phpucConfigFile( $this->testFile );
    }

    /**
     * Tests the {@link phpucConfigProject} ctor and tests the magic properties.
     *
     * @return void
     */
    public function testNewConfigProjectInstance()
    {
        $project = new phpucConfigProject( $this->config, 'phpUnderControl' );

        $this->assertTrue( $project->isNew() );
        $this->assertSame( $this->config, $project->configFile );
        $this->assertEquals( 'phpUnderControl', $project->projectName );
    }

    /**
     * Test that {@link phpucConfigProject#delete()} removes the context project
     * from the config.xml file.
     *
     * @return void
     */
    public function testDeleteConfigProjectFromConfigFile()
    {
        // Create a dummy project
        new phpucConfigProject( $this->config, 'phpUnderControl0' );
        new phpucConfigProject( $this->config, 'phpUnderControl1' );
        $this->config->store();

        $dom = new DOMDocument();
        $dom->load( $this->testFile );
        $this->assertEquals( 2, $dom->getElementsByTagName( 'project' )->length );

        // Load project again
        $config  = new phpucConfigFile( $this->testFile );
        $project = $config->getProject( 'phpUnderControl0' );

        // Remove project and save again
        $project->delete();
        $config->store();

        $dom = new DOMDocument();
        $dom->load( $this->testFile );
        $this->assertEquals( 1, $dom->getElementsByTagName( 'project' )->length );
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
            'Unknown or writonly property $phpuc.'
        );

        $project = new phpucConfigProject( $this->config, 'phpUnderControl' );
        echo $project->phpuc;
    }

    /**
     * Tests that the magic setter method for the $interval property fails with
     * an exception for a non integer.
     *
     * @return void
     */
    public function testIntervalSetterInvalidTypeFail()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Property $interval must be a positive integer.'
        );

        $project = new phpucConfigProject( $this->config, 'phpUnderControl' );
        $project->interval = false;
    }

    /**
     * Tests that the magic setter method for the $interval property fails with
     * an exception for a negative integer.
     *
     * @return void
     */
    public function testIntervalSetterNegativeValueFail()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Property $interval must be a positive integer.'
        );

        $project = new phpucConfigProject( $this->config, 'phpUnderControl' );
        $project->interval = -1;
    }

    /**
     * Tests that the magic __set() method fails with an exception for an unknown
     * property.
     *
     * @return void
     */
    public function testSetterUnknownPropertyFail()
    {
        $this->setExpectedException(
            'OutOfRangeException',
            'Unknown or readonly property $phpuc.'
        );

        $project = new phpucConfigProject( $this->config, 'phpUnderControl' );
        $project->phpuc = true;
    }

    /**
     * Tests that the config project fails with an exception if there is more
     * than one project with the same name.
     *
     * @return void
     */
    public function testProjectCtorWithTwoEqualProjectsFail()
    {
        $this->createTestFile(
            '/config.xml',
            '<?xml version="1.0"?>
             <cruisecontrol>
               <project name="phpUnderControl" />
               <project name="phpUnderControl" />
             </cruisecontrol>'
        );

        $this->setExpectedException(
            'phpucErrorException',
            "There is more than one project named 'phpUnderControl'."
        );

        $config = new phpucConfigFile( $this->testFile );
        new phpucConfigProject( $config, 'phpUnderControl' );

        $this->config = new phpucConfigFile( $this->testFile );
    }

    /**
     * Test that the antscript attribute is set for custom ant launcer.
     */
    public function testSetAntscriptAttribute()
    {
        $project = new phpucConfigProject( $this->config, 'phpUnderControl' );
        $project->anthome = '/foo';
        $project->antscript = '/foo/bar/bazscript.sh';

        $project->buildXml();
        $element = $project->element;
        $schedule = $element->getElementsByTagName( 'schedule' );
        $builders = $schedule->item( 0 )->getElementsByTagName( 'ant' );
        $ant = $builders->item( 0 );

        $this->assertTrue($ant->hasAttribute('antscript'));
        $this->assertEquals('/foo/bar/bazscript.sh', $ant->getAttribute('antscript'));
        $this->assertFalse($ant->hasAttribute('anthome'));
    }

    public function testNonBundledAntReplacesAntWorkerWithExecTask()
    {
        $project = new phpucConfigProject( $this->config, 'phpUnderControl' );
        $project->anthome = '/usr';

        $project->buildXml();
        $element = $project->element;
        $schedule = $element->getElementsByTagName( 'schedule' );
        $builders = $schedule->item( 0 )->getElementsByTagName( 'exec' );
        $exec = $builders->item( 0 );

        $this->assertTrue($exec->hasAttribute('workingdir'));
        $this->assertEquals(PHPUC_TEST_DIR, $exec->getAttribute('workingdir'));

        $this->assertTrue($exec->hasAttribute('command'));
        $this->assertEquals('/usr/bin/ant', $exec->getAttribute('command'));

        $this->assertTrue($exec->hasAttribute('args'));
        $dir = PHPUC_TEST_DIR . '/';
        $argStr = '-logger org.apache.tools.ant.XmlLogger ' .
                  "-logfile {$dir}log.xml " .
                  '-buildfile projects/${project.name}/build.xml';
        $this->assertEquals($argStr, $exec->getAttribute('args'));
    }

    /**
     * Everytime {@link phpucConfigFile} created make sure it fetches
     * custom path to antscript and sets it up
     *
     * @covers phpucConfigProject::init
     *
     * @return void
     */
    public function testInitAlwaysPopulatesCustomAntScriptWhenSet()
    {
        $antScript = '/foo/bar/bazscript.sh';

        $this->createTestFile(
            '/config.xml',
            "<?xml version=\"1.0\"?>
             <cruisecontrol>
               <project name=\"phpUnderControl\">
                 <schedule interval=\"300\">
                   <ant buildfile=\"projects/TestProject/build.xml\" antscript=\"$antScript\"/>
                 </schedule>
               </project>
             </cruisecontrol>"
        );

        $config = new phpucConfigFile( $this->testFile );
        $project = new phpucConfigProject( $config, 'phpUnderControl' );

        $schedule = $project->element->getElementsByTagName( 'schedule' );
        $builders = $schedule->item( 0 )->getElementsByTagName( 'ant' );
        $ant = $builders->item( 0 );

        $this->assertEquals($antScript, $ant->getAttribute('antscript'));

        $project = new phpucConfigProject( $config, 'phpUnderControl' );

        $this->assertEquals($antScript, $project->antscript);
    }
}