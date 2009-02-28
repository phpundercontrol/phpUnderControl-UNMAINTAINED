<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.0
 *
 * Copyright (c) 2007-2009, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @package    Data
 * @subpackage Logs
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007-2009 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/../../AbstractTest.php';

/**
 * Test case for the coverage log generator.
 *
 * @category   QualityAssurance
 * @package    Data
 * @subpackage Logs
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007-2009 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpundercontrol.org/
 */
class phpucPHPUnitCoverageXmlGeneratorTest extends phpucAbstractTest
{
    /**
     * Tests the generated coverage xml report.
     *
     * @return void
     */
    public function testGenerateCoverageXml()
    {
        $rev = 3;

        $dsn = sprintf( 'sqlite:%s/phpunit/php525/log.db', PHPUC_TEST_DATA );
        $pdo = new PDO( $dsn );
        $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        
        $result   = PHPUC_TEST_DIR . '/result.xml';
        $expected = PHPUC_TEST_DIR . '/expected.xml';
        
        copy( PHPUC_TEST_DATA . '/phpunit/expected/cov.xml', $expected );
        
        $generator = new phpucPHPUnitCoverageXmlGenerator( $pdo, $rev );
        $generator->store( $result );
        
        $this->assertFileExists( $result );
        
        // Set timestamp
        $time = time();
        
        $dom1 = new DOMDocument();
        $dom1->load( $result );
        $dom1->documentElement->setAttribute( 'generated', $time );
        $dom1->getElementsByTagName( 'project' )
             ->item( 0 )
             ->setAttribute( 'timestamp', $time );
        $dom1->save( $result );
        
        $dom2 = new DOMDocument();
        $dom2->load( $expected );
        $dom2->documentElement->setAttribute( 'generated', $time );
        $dom2->getElementsByTagName( 'project' )
             ->item( 0 )
             ->setAttribute( 'timestamp', $time );
        $dom2->save( $expected );
        
        $this->assertXmlFileEqualsXmlFile( $expected, $result );
    }
}
