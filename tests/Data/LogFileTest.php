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
 * @package   Data
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../AbstractTest.php';

/**
 * Test case for the CruiseControl log file.
 *
 * @category  QualityAssurance
 * @package   Data
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucLogFileTest extends phpucAbstractTest
{
    /**
     * Test the timestamp extraction for a good build.
     *
     * @return void
     */
    public function testLogTimestampGoodBuild()
    {
        $log = new phpucLogFile( PHPUC_TEST_LOGS . '/log20071211220903Lbuild.3.xml' );
        $this->assertEquals( '20071211220903', $log->timestamp );
    }

    /**
     * Test the timestamp extraction for a broken build.
     *
     * @return void
     */
    public function testLogTimestampBrokenBuild()
    {
        $log = new phpucLogFile( PHPUC_TEST_LOGS . '/log20080113145726.xml' );
        $this->assertEquals( '20080113145726', $log->timestamp );
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
        
        $log = new phpucLogFile( PHPUC_TEST_LOGS . '/log20080113145726.xml' );
        echo $log->phpuc;
    }
    
    /**
     * Tests that every call to the magic __set() method fails with an exception.
     *
     * @return void
     */
    public function testPropertySetterFail()
    {
        $this->setExpectedException(
            'OutOfRangeException',
            'Unknown or readonly property $timestamp.'
        );
        
        $log = new phpucLogFile( PHPUC_TEST_LOGS . '/log20080113145726.xml' );
        
        $log->timestamp = '1234567890';
    }
}