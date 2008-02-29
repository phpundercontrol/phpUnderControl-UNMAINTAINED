<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.4
 *
 * Copyright (c) 2007-2008, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
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
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucCheckoutTaskTest extends phpucAbstractTaskTest
{
    /**
     * Tests a subversion checkout.
     *
     * @return void
     */
    public function testSubversionCheckout()
    {
        $this->createCCSkeleton();
        
        $directory = PHPUC_TEST_DIR . "/projects/{$this->projectName}/source";
        
        $this->prepareArgv(
            array(
                'project',
                '-j',
                $this->projectName,
                '-y',
                'svn',
                '-x',
                'svn://svn.xplib.de/PHP_Depend/trunk',
                '-d',
                'source',
                PHPUC_TEST_DIR
            )
        );
        
        $input = new phpucConsoleInput();
        $input->parse();
        
        $this->assertFileNotExists( "{$directory}/PHP" );
        
        $checkout = new phpucCheckoutTask();
        $checkout->setConsoleArgs( $input->args );
        $checkout->execute();
        
        $this->assertFileExists( "{$directory}/PHP" );
        
        $config = new DOMDocument();
        $config->load( PHPUC_TEST_DIR . '/config.xml' );
        
        $xpath  = new DOMXPath( $config );
        $result = $xpath->query( "//svnbootstrapper[@localWorkingCopy='{$directory}']" );
        
        $this->assertEquals( 1, $result->length );
    } 
}