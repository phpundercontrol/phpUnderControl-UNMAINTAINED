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
 * @category   QualityAssurance
 * @package    Graph
 * @subpackage Input
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007-2010 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/AbstractGraphInputTest.php';

/**
 * Test case for the code to tests ratio input.
 * 
 * @category   QualityAssurance
 * @package    Graph
 * @subpackage Input
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007-2010 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpundercontrol.org/
 */
class phpucTestCodeRatioInputTest extends phpucAbstractGraphInputTest
{
    /**
     * Create the context input object.
     *
     * @return phpucInputI
     */
    protected function createInput()
    {
        return new phpucTestCodeRatioInput();
    }
    
    /**
     * Extracts the test data for a single log file and tests the result.
     *
     * @param DOMXPath $xpath The xpath instance for the log file.
     * 
     * @return void
     */
    protected function doTestSingleLog( DOMXPath $xpath )
    {
        $log = $this->queryLogData( $xpath );
        
        $input = new phpucTestCodeRatioInput();
        $input->processLog( $xpath );
        
        $data = $input->data;
        
        $this->assertArrayHasKey( 'Classes', $data );
        $this->assertArrayHasKey( 'Methods', $data );
        $this->assertArrayHasKey( 'Test Classes', $data );
        $this->assertArrayHasKey( 'Test Methods', $data );
        
        $this->assertEquals( $log['Classes'], reset( $data['Classes'] ) );
        $this->assertEquals( $log['Methods'], reset( $data['Methods'] ) );
        $this->assertEquals( $log['Test Classes'], reset( $data['Test Classes'] ) );
        $this->assertEquals( $log['Test Methods'], reset( $data['Test Methods'] ) );
    }
    
    /**
     * Tests the summary log result.
     *
     * @param phpucInputI $input    The context log input object.
     * @param DOMXPath    $xpath    The xpath instance for the log file.
     * @param array       $previous Results from previous calls.
     * 
     * @return array Merged $previous with actual results.
     */
    protected function doTestSumLog( phpucInputI $input, DOMXPath $xpath, array $previous )
    {
        $log = $this->queryLogData( $xpath );
        
        $records = $previous;
        if ( count( $records ) === 0 )
        {
            $records = array(
                'Classes'       =>  array(),
                'Methods'       =>  array(),
                'Test Classes'  =>  array(),
                'Test Methods'  =>  array()
            );
        }

        $records['Classes'][]      = $log['Classes'];
        $records['Methods'][]      = $log['Methods'];
        $records['Test Classes'][] = $log['Test Classes'];
        $records['Test Methods'][] = $log['Test Methods'];
        
        return $records;
    }
    
    protected function queryLogData( DOMXPath $xpath )
    {
        $classes   = $xpath->query( '/cruisecontrol/coverage/project/file/class' );
        $methods   = $xpath->query( '/cruisecontrol/coverage/project/file/line[@type="method"]' );
        $testcases = $xpath->query( '/cruisecontrol/testsuites//testsuite[testcase]' );
        $tests     = $xpath->query( '/cruisecontrol/testsuites//testsuite/testcase' );
        
        return array(
            'Classes'       =>  $classes->length,
            'Methods'       =>  $methods->length,
            'Test Classes'  =>  $testcases->length,
            'Test Methods'  =>  $tests->length
        );
    }
}