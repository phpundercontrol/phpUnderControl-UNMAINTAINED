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

require_once 'PHPUnit/Extensions/Database/DB/DefaultDatabaseConnection.php';
require_once 'PHPUnit/Extensions/Database/DataSet/XmlDataSet.php';
require_once 'PHPUnit/Extensions/Database/Operation/Factory.php';
require_once 'PHPUnit/Extensions/Database/DefaultTester.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';

require_once dirname( __FILE__ ) . '/../../AbstractTest.php';

/**
 * Test the log database aggregator.
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
class phpucPHPUnitLogDatabaseAggregatorTest extends phpucAbstractTest
{
    /**
     * The used pdo database connection.
     *
     * @type PDO 
     * @var PDO $pdo
     */
    protected $pdo = null;
    
    /**
     * Creates the required database and a new pdo connection.
     *
     * @return void
     */
    protected function setUp()
    {
        $emptydb = sprintf( '%s/phpunit/log.db', PHPUC_TEST_DATA );
        $testdb  = sprintf( '%s/log.db', PHPUC_TEST_DIR );
        
        copy( $emptydb, $testdb );
        
        $this->pdo = new PDO( "sqlite://{$testdb}" );
        $this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    }
    
    /**
     * Creates a database tester instance.
     * 
     * @return PHPUnit_Extensions_Database_DefaultTester
     */
    protected function getDatabaseTester()
    {
        $connection = new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection(
            $this->pdo, 'sqlite'
        );
        
        $tester = new PHPUnit_Extensions_Database_DefaultTester($connection);
        
        $tester->setSetUpOperation(
            PHPUnit_Extensions_Database_Operation_Factory::TRUNCATE()
        );
        $tester->setTearDownOperation(
            PHPUnit_Extensions_Database_Operation_Factory::NONE()
        );
        $tester->setDataSet(
            new PHPUnit_Extensions_Database_DataSet_XmlDataSet(
                PHPUC_TEST_DATA . '/phpunit/log-db-seed.xml'
            )
        );
        
        return $tester;
    }
    
    /**
     * Tests the aggregate logs method.
     *
     * @return void
     */
    public function testAggregateLogs()
    {
        $revision = 3;
        
        $tester = $this->getDatabaseTester();
        $tester->onSetUp();
        
        $conns = array();
        foreach ( array( 'php520', 'php525', 'php526RC2' ) as $build )
        {
            $file = sprintf( '%s/phpunit/%s/log.db', PHPUC_TEST_DATA, $build );
            
            $conns[] = new PDO( "sqlite:{$file}" );
        }
        
        $aggregator = new phpucPHPUnitLogDatabaseAggregator( $this->pdo, $revision );
        $aggregator->aggregate( new ArrayIterator( $conns ) );

        $dataSet = $this->createLogAfterAggregation( $revision );
        PHPUnit_Extensions_Database_TestCase::assertDataSetsEqual(
            $dataSet, 
            $tester->getConnection()->createDataSet()
        );
        
        $tester->onTearDown();
    }
    
    /**
     * Creates a prepared xml data set.
     *
     * @param integer $revision The test/merge revision
     * 
     * @return PHPUnit_Extensions_Database_DataSet_XmlDataSet
     */
    protected function createLogAfterAggregation( $revision )
    {
        $stmt = $this->pdo->prepare(
            'SELECT timestamp
               FROM run
              WHERE revision = :revision'
        );
        $stmt->bindValue( ':revision', $revision );
        $stmt->execute();
        
        $timestamp = $stmt->fetchColumn();
        
        $stmt->closeCursor();
        
        $dom = new DOMDocument();
        $dom->load( PHPUC_TEST_DATA . '/phpunit/log-db-after-aggregation.xml' );
        
        $xpath  = new DOMXPath( $dom );
        $result = $xpath->query( 
            "//table[@name='run']/row[value[3]/text() = '{$revision}']/value[2]"
        );
        
        foreach ( $result as $node )
        {
            $node->firstChild->nodeValue = $timestamp;
        }
        
        $dom->save( PHPUC_TEST_DIR . '/log-db-after-aggregation.xml' );
        
        return new PHPUnit_Extensions_Database_DataSet_XmlDataSet(
            PHPUC_TEST_DIR . '/log-db-after-aggregation.xml'
        );
    }
}