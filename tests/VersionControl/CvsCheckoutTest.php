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
 * @package   VersionControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/AbstractCheckoutTest.php';

/**
 * Test case for the cvs checkout.
 *
 * @category  QualityAssurance
 * @package   VersionControl
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucCvsCheckoutTest extends phpucAbstractCheckoutTest
{
    /**
     * The current working directory.
     *
     * @type string
     * @var string $cwd
     */
    protected $cwd = null;
    
    /**
     * Resets the path and os settings in {@link phpucFileUtil}.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        
        phpucFileUtil::setOS();
        phpucFileUtil::setPaths();
        
        $this->cwd = getcwd();
        
        chdir( PHPUC_TEST_DIR );
    }
    
    /**
     * Changes back to the current working dir.
     *
     * @return void
     */
    protected function tearDown()
    {
        chdir( $this->cwd );
        
        parent::tearDown();
    }
    
    /**
     * Tests the checkout against a valid repository.
     *
     * @return void
     */
    public function testPServerCheckout()
    {
        $this->markTestSkippedWhenRemoteHostNotAvailable( 'xplib.de:2401' );

        $destination = PHPUC_TEST_DIR . '/source';
        $checkFile1  = $destination . '/pdepend.php';
        $checkFile2  = $destination . '/PHP/Depend.php';
        $checkFile3  = $destination . '/PHP/Depend/Code/Class.php';
        
        $this->assertFileNotExists( $checkFile1 );
        $this->assertFileNotExists( $checkFile2 );
        $this->assertFileNotExists( $checkFile3 );
        
        $checkout           = new phpucCvsCheckout();
        $checkout->url      = 'xplib.de:/cvs';
        $checkout->module   = 'PHP_Depend';
        $checkout->username = 'anonymous';
        $checkout->password = '';

        $checkout->checkout();
        
        $this->assertFileExists( $checkFile1 );
        $this->assertFileExists( $checkFile2 );
        $this->assertFileExists( $checkFile3 );
    }
    
    /**
     * Tests that the pserver checkout fails with an exception for an invalid
     * cvs module.
     *
     * @return void
     */
    public function testPServerCheckoutWithInvalidModuleFail()
    {
        $this->setExpectedException( 'phpucErrorException' );
        
        $checkout           = new phpucCvsCheckout();
        $checkout->url      = 'xplib.de:/cvs';
        $checkout->module   = 'PHP_Depend2';
        $checkout->username = 'anonymous';
        $checkout->password = '';

        $checkout->checkout();
    }
    
    /**
     * Tests that the checkout fails with an exception if an invalid username
     * is given.
     *
     * @return void
     */
    public function testPServerCheckoutWithInvalidUsernameFail()
    {
        $this->setExpectedException( 'phpucErrorException' );
        
        $checkout           = new phpucCvsCheckout();
        $checkout->url      = 'xplib.de:/cvs';
        $checkout->module   = 'PHP_Depend';
        $checkout->password = '';
        $checkout->username = 'mapi';

        $checkout->checkout();
    }
    
    /**
     * Tests that the checkout fails with an exception if an invalid password
     * is given.
     *
     * @return void
     */
    public function testPServerCheckoutWithInvalidPasswordFail()
    {
        $this->setExpectedException( 'phpucErrorException' );
        
        $checkout           = new phpucCvsCheckout();
        $checkout->url      = 'xplib.de:/cvs';
        $checkout->module   = 'PHP_Depend';
        $checkout->password = 'foobar';
        $checkout->username = 'anonymous';
        $checkout->checkout();
    }
    
    /**
     * Test factory method.
     *
     * @return phpucCheckoutI
     */
    protected function createCheckout()
    {
        return new phpucCvsCheckout();
    }
}
