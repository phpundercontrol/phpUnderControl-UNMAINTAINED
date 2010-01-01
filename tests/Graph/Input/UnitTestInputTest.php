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
 * Test case for the unit test result input.
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
class phpucUnitTestInputTest extends phpucAbstractGraphInputTest
{
    /**
     * Create the context input object.
     *
     * @return phpucInputI
     */
    protected function createInput()
    {
        return new phpucUnitTestInput();
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
        
        $input = new phpucUnitTestInput();
        $input->processLog( $xpath );
        
        $data = $input->data;
        
        $this->assertArrayHasKey( 'Total', $data );
        $this->assertArrayHasKey( 'Failures', $data );
        
        $this->assertEquals( $log['Total'], reset( $data['Total'] ) );
        $this->assertEquals( $log['Failures'], reset( $data['Failures'] ) );
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
                'Total'      =>  array(),
                'Failures'  =>  array(),
            );
        }

        $records['Total'][]    = $log['Total'];
        $records['Failures'][] = $log['Failures'];
        
        return $records;
    }
    
    /**
     * Queries the expected log data from the given xpath object.
     * 
     * @param DOMXPath $xpath The context xpath instance.
     * 
     * @return array(string=>DOMNodeList)
     */
    protected function queryLogData( DOMXPath $xpath )
    {
        return array(
            'Total'     =>  $xpath->query( '/cruisecontrol/testsuites//testcase' )->length,
            'Failures'  =>  $xpath->query( '/cruisecontrol/testsuites//testcase[failure]' )->length
        );
    }
}