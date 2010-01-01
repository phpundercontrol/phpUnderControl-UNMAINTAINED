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
 * Test case for the build breakdown timeline input.
 * 
 * @category   QualityAssurance
 * @package    Graph
 * @subpackage Input
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007-2010 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucBuildBreakdownTimelineInputTest extends phpucAbstractGraphInputTest
{
    /**
     * Test case for ticket 838, where a CruiseControl log file with several
     * builddate properties results in invalid dates.
     *
     * http://www.phpunit.de/ticket/838
     *
     * @return void
     * @group graph
     */
    public function testInputOnlyEvaluatesTheFirstBuildDatePropertyTicket838()
    {
        $xpath = new DOMXPath( $this->createCruiseControlLog() );

        $input = new phpucBuildBreakdownTimelineInput();
        $input->processLog( $xpath );

        $this->assertSame(
            array( 
                'Good Builds' => array(
                    1239383574 => '19:12'
                )
            ),
            $input->data
        );
    }

    /**
     * Create the context input object.
     *
     * @return phpucInputI
     */
    protected function createInput()
    {
        return new phpucBuildBreakdownTimelineInput();
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
        $error = $xpath->query(
            '/cruisecontrol[build/@error]/info/property[@name = "builddate"]/@value'
        );
            
        $input = new phpucBuildBreakdownTimelineInput();
        $input->processLog( $xpath );
            
        $data = $input->data;
        
        $this->assertEquals( 1, count( $data ) );

        if ( $error->length === 1 )
        {
            $this->assertArrayHasKey( 'Broken Builds', $data );
            $this->assertArrayNotHasKey( 'Good Builds', $data );
            $this->assertEquals( 1, count( $data['Broken Builds'] ) );
            $this->assertRegExp( '/[0-2][0-9]:[0-5][0-9]/', reset( $data['Broken Builds'] ) );
        }
        else
        {
            $this->assertArrayHasKey( 'Good Builds', $data );  
            $this->assertArrayNotHasKey( 'Broken Builds', $data );
            $this->assertEquals( 1, count( $data['Good Builds'] ) ); 
            $this->assertRegExp( '/[0-2][0-9]:[0-5][0-9]/', reset( $data['Good Builds'] ) );
        }
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
        $total = $xpath->query(
            '/cruisecontrol/info/property[@name = "builddate"]/@value'
        );
        $error = $xpath->query(
            '/cruisecontrol[build/@error]/info/property[@name = "builddate"]/@value'
        );

        $label = ( $error->length === 1 ? 'Broken' : 'Good' ) . ' Builds';
        $time  = strtotime( $total->item( 0 )->nodeValue );

        $record = $previous;
            
        if ( !isset( $record[$label] ) )
        {
            $record[$label] = array();
        }
        $record[$label][$time] = date( 'H:i', $time );

        return $record;
    }
}
