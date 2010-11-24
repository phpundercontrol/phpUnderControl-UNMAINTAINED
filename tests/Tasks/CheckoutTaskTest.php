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
 * Test case for the version control checkout task
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucCheckoutTaskTest extends phpucAbstractTaskTest
{
    private $cwd;

    /**
     * Creates a dummy CruiseControl structure.
     *
     * @return void
     */
    protected function setUp()
    {
        $socket = @fsockopen( 'xplib.de', 80, $errno, $errstr, 1);
        if ( is_resource( $socket ) )
        {
            fclose( $socket );
        }
        else
        {
            $this->markTestSkipped( 'Cannot connect to pserver.' );
        }

        $this->cwd = getcwd();

        parent::setUp();

        $this->createCCSkeleton();
    }

    /**
     * Start every test from an existing directory
     *
     * @return void
     */
    protected function tearDown()
    {
        chdir($this->cwd);

        parent::tearDown();
    }

    /**
     * Tests a subversion checkout.
     *
     * @return void
     */
    public function testSubversionCheckout()
    {
        $this->prepareArgv(
            array(
                'project',
                '-j',
                $this->projectName,
                '-v',
                'svn',
                '-x',
                'svn://svn.xplib.de/PHP_Depend/trunk',
                PHPUC_TEST_DIR
            )
        );

        $this->doTestCheckout( 'svn' , 'tests' );
    }

    /**
     * Tests a CVS checkout.
     *
     * @return void
     */
    public function testCvsCheckout()
    {
        $socket = @fsockopen( 'xplib.de', 2401, $errno, $errstr, 1);
        if ( is_resource( $socket ) )
        {
            fclose( $socket );
        }
        else
        {
            $this->markTestSkipped( 'Cannot connect to pserver.' );
        }

        $this->prepareArgv(
            array(
                'project',
                '-j',
                $this->projectName,
                '-v',
                'cvs',
                '-x',
                'xplib.de:/cvs',
                '-u',
                'anonymous',
                '-m',
                'PHP_Depend',
                PHPUC_TEST_DIR
            )
        );

        $this->doTestCheckout( 'cvs' , 'PHP' );
    }

    /**
     * Tests a git checkout.
     *
     * @return void
     */
    public function testGitCheckout()
    {
        $this->prepareArgv(
            array(
                'project',
                '-j',
                $this->projectName,
                '-v',
                'git',
                '-x',
                'git://github.com/manuelpichler/phpUnderControl.git',
                PHPUC_TEST_DIR
            )
        );

        $this->doTestCheckout( 'git' , 'src' );
    }

    /**
     * Executes the {@link phpucCheckoutTask} and tests the generated contents.
     *
     * @param string $type The version control system.
     *
     * @return void
     */
    protected function doTestCheckout( $type , $file)
    {
        $directory = PHPUC_TEST_DIR . "/projects/{$this->projectName}/source";

        $input = new phpucConsoleInput();
        $input->parse();

        $this->assertFileNotExists( "{$directory}/{$file}" );

        $checkout = new phpucCheckoutTask();
        $checkout->setConsoleArgs( $input->args );
        $checkout->execute();

        $this->assertFileExists( "{$directory}/{$file}" );

        $config = new DOMDocument();
        $config->load( PHPUC_TEST_DIR . '/config.xml' );
        $xpath  = new DOMXPath( $config );

        $result = $xpath->query( "//{$type}bootstrapper[@localWorkingCopy='{$directory}']" );
        $this->assertEquals( 1, $result->length );

        $result = $xpath->query( "//modificationset/{$type}[@localWorkingCopy='{$directory}']" );
        $this->assertEquals( 1, $result->length );

        $build = new DOMDocument();
        $build->load( PHPUC_TEST_DIR . "/projects/{$this->projectName}/build.xml" );
        $xpath = new DOMXPath( $build );

        $result = $xpath->query( '//target[@name="checkout"]/exec[@dir="${basedir}/source"]' );
        $this->assertEquals( 1, $result->length );
    }
}
