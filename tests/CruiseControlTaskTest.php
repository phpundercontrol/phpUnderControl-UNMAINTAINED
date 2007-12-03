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
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpunit.de/wiki/phpUnderControl
 */

require_once dirname( __FILE__ ) . '/AbstractTest.php';

/**
 * <???>
 *
 * @package   phpUnderControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpunit.de/wiki/phpUnderControl
 */
class phpucCruiseControlTaskTest extends phpucAbstractTest
{
    /**
     * The console arguments.
     *
     * @type phpucConsoleArgs
     * @var phpucConsoleArgs $args
     */
    protected $args = null;
    
    /**
     * Prepares the required <b>$argv</b> array.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->prepareArgv( array( 'install', dirname( __FILE__ ) . '/run' ) );
        $this->args = new phpucConsoleArgs();
        $this->args->parse();
    }
    
    /**
     * Removes all test contents.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->clearTestContents();
        
        parent::tearDown();
    }
    
    /**
     * Tests that the validate method fails for an invalid directory structure.
     *
     * @return void
     */
    public function testValidateWithInValidDirectoryStructureFail()
    {
        $this->createTestDirectories(
            array( 
                'webapps', 
                'webapps/cruisecontrol',
                'webapps/cruisecontrol/xsl',
                'webapps/cruisecontrol/images',
            )
        );
        
        $ccTask = new phpucCruiseControlTask( $this->args );
        try
        {
            $ccTask->validate();
            $this->fail( 'phpucValidateException expected.' );
        }
        catch ( phpucValidateException $e ) {}
    }
    
    /**
     * Tests the validate method with a valid directory structure and expects
     * that the cc task works as expected. 
     *
     * @return void
     */
    public function testValidateWithValidDirectoryStructure()
    {
        $this->createTestDirectories(
            array( 
                'webapps', 
                'webapps/cruisecontrol',
                'webapps/cruisecontrol/css',
                'webapps/cruisecontrol/xsl',
                'webapps/cruisecontrol/images',
            )
        );
        
        $ccTask = new phpucCruiseControlTask( $this->args );
        $ccTask->validate();
    }
    
    /**
     * Tests that the cc task creates the expected directories.
     *
     * @return void
     */
    public function testExecuteWithValidDirectoryStructure()
    {
        $this->createTestDirectories(
            array( 
                'webapps', 
                'webapps/cruisecontrol',
                'webapps/cruisecontrol/css',
                'webapps/cruisecontrol/xsl',
                'webapps/cruisecontrol/images',
            )
        );
        
        $ccTask = new phpucCruiseControlTask( $this->args );
        ob_start();
        $ccTask->execute();
        ob_end_clean();
        
        $basedir =  dirname( __FILE__ ) . '/run/webapps/cruisecontrol';
        
        $this->assertFileExists( $basedir . '/js' );
        $this->assertFileExists( $basedir . '/images/php-under-control' );
    }
}