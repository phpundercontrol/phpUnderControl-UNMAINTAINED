<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.0
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
 * @category   QualityAssurance
 * @package    PhpUnderControl
 * @subpackage Documentation
 * @author     Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright  2007-2010 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id: PhpUnderControl.php 2631 2008-03-18 15:23:55Z mapi $
 * @link       http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/MergeCode.php';

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Documentation/Example/Test testcase for environment specific code.
 *
 * @category   QualityAssurance
 * @package    PhpUnderControl
 * @subpackage Documentation
 * @author     Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright  2007-2010 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpundercontrol.org/
 */
class phpucMergeCodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Version specific test case.
     *
     * @return void
     */
    public function testVersionSpecific()
    {
        $obj = new phpucMergeCode();
        $this->assertEquals( phpversion(), $obj->versionSpecific() );
    }
    
    public function testNotVersionSpecific()
    {
        $obj = new phpucMergeCode();
        
        $mode = phpucMergeCode::REVERSE;
        if ( version_compare( phpversion(), '5.2.5' ) === 1 )
        {
            $mode = phpucMergeCode::NORMAL;
        }
        
        $this->assertEquals( 
            strrev( php_sapi_name() ), 
            $obj->notVersionSpecific( phpucMergeCode::SAPI, $mode )
        );
    }
    
    /**
     * phpunit dataProvider test.
     *
     * @param integer $x Test value one.
     * @param integer $y Test value two.
     * 
     * @return void
     * @dataProvider dataProvider
     */
    public function testCalculate($x, $y)
    {
        $obj = new phpucMergeCode();
        
        $this->assertEquals( 3, $obj->calculate( $x, $y ) );
    }
    
    /**
     * Test data provider.
     *
     * @return array(array)
     */
    public static function dataProvider()
    {
        return array(
            array( 1, 2 ),
            array( -2, 5 ),
            array( 2, 2 ),
            array( 9, -6 )
        );
    }
}