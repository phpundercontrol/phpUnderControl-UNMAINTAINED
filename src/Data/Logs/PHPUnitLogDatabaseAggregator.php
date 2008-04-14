<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.0
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
 * @category   QualityAssurance
 * @package    Data
 * @subpackage Logs
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007-2008 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpundercontrol.org/
 */

/**
 * This class aggregates a log revision stored in multiple PHPUnit log databases. 
 *
 * @category   QualityAssurance
 * @package    Data
 * @subpackage Logs
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007-2008 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpundercontrol.org/
 */
class phpucPHPUnitLogDatabaseAggregator
{
    /**
     * The used log connection.
     *
     * @type PDO
     * @var PDO $conn
     */
    protected $conn = null;
    
    /**
     * The context log revision.
     *
     * @type integer
     * @var integer $revision
     */
    protected $revision = 0;
    
    /**
     * The database identifier for the aggregated test run.
     *
     * @type integer 
     * @var integer $runId
     */
    protected $runId = 0;
    
    /**
     * The database identifier for the aggregated test suite.
     *
     * @type integer 
     * @var integer $runId
     */
    protected $testId = 0;
    
    /**
     * Nested set update value. 
     *
     * @type integer
     * @var integer $nestedSetUpdate
     */
    protected $nestedSetUpdate = 1;
    
    /**
     * Mapping of old and new code file identifiers.
     *
     * @type array<integer>
     * @var array(integer=>integer) $codeFileMap
     */
    protected $codeFileMap = array();
    
    /**
     * Mapping between code class names and its new identifier.
     *
     * @type array<integer>
     * @var array(string=>integer) $codeClassMap
     */
    protected $codeClassMap = array();
    
    /**
     * Mapping between a code method name and its new identifier.
     *
     * @type array<integer>
     * @var array(string=>integer) $codeMethodMap
     */
    protected $codeMethodMap = array();
    
    /**
     * Mapping between old and new test identifiers.
     *
     * @type array<integer>
     * @var array(integer=>integer) $testIdMap
     */
    protected $testIdMap = array();
    
    /**
     * Mapping between file names, line numbers and new line database identifiers.
     *
     * @type array<array>
     * @var array(string=>array(integer=>integer)) $fileLineMap
     */
    protected $fileLineMap = array();
    
    /**
     * Creates a new log aggregator instance.
     *
     * @param PDO     $connection A database connection to the aggregate database.
     * @param integer $revision   The context project revision.
     */
    public function __construct( PDO $connection, $revision )
    {
        $this->conn     = $connection;
        $this->revision = $revision;
    }
    
    /**
     * Aggregates the log databases stored in the given iterator.
     *
     * @param Iterator $connections Iterator with input log databases.
     * 
     * @return void
     */
    public function aggregate( Iterator $connections )
    {
        $this->createRunAndTest();
        
        foreach ( $connections as $conn )
        {
            $stmt = $conn->prepare(
                'SELECT code_file_id,
                        code_file_name,
                        code_file_md5
                   FROM code_file
                  WHERE revision = :revision'
            );
            $stmt->bindValue( ':revision', $this->revision );
            $stmt->execute();
            $files = $stmt->fetchAll( PDO::FETCH_ASSOC );
            $stmt->closeCursor();
            
            foreach ( $files as $file )
            {
                $fileName = $file['code_file_name'];
                
                $stmt = $this->conn->prepare(
                    'SELECT code_file_id
                       FROM code_file
                      WHERE code_file_name = :code_file_name
                        AND revision       = :revision'
                );
                $stmt->bindValue( ':code_file_name', $fileName );
                $stmt->bindValue( ':revision', $this->revision );
                $stmt->execute();
                
                if ( count( $stmt->fetchAll() ) === 0 )
                {
                    $this->insertCodeFile( $conn, $file );
                }
                else
                {
                    $this->updateCodeLines( $conn, $file );
                }
                $stmt->closeCursor();
            }
            
            $this->mergeTests( $conn );
            $this->mergeCodeCoverage( $conn );
        }
        
        $this->insertProjectMetrics( $conn );
        
        $this->updateFileMetrics();
        $this->updateFunctionMetrics();
        $this->updateClassMetrics();
        $this->updateMethodMetrics();
    }
    
    /**
     * Creates a new run entry for the current revision and an empty dummy root
     * test suite.
     * 
     * @return void
     */
    protected function createRunAndTest()
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO run (
                         timestamp, 
                         revision, 
                         completed
              ) VALUES (
                         :timestamp, 
                         :revision, 
                         1
                       )'
        );
        $stmt->bindValue( ':timestamp', time() );
        $stmt->bindValue( ':revision', $this->revision ); 
        $stmt->execute();
        
        $this->runId = $this->conn->lastInsertId();
        
        // Create a dummy root test suite first
        $stmt = $this->conn->prepare(
            "INSERT INTO test (
                         run_id,
                         test_name,
                         test_message,
                         node_root,
                         node_left,
                         node_right
              ) VALUES (
                         :run_id,
                         'Merged Test Suite',
                         '',
                         0,
                         1,
                         2
                       )"
        );
        $stmt->bindValue( ':run_id', $this->runId );
        $stmt->execute();
        
        $this->testId = $this->conn->lastInsertId();
        
        $stmt = $this->conn->prepare(
            'UPDATE test 
                SET node_root = :node_root
              WHERE test_id   = :test_id'
        );
        $stmt->bindValue( ':node_root', $this->testId );
        $stmt->bindValue( ':test_id', $this->testId );
        $stmt->execute();
    }
    
    /**
     * Merges the test suite stored in the log database with the new aggregated
     * test suite.
     *
     * @param PDO $conn The input connection.
     * 
     * @return void
     */
    protected function mergeTests( PDO $conn )
    {
        $this->testIdMap = array();
        
        $select = $conn->prepare(
            'SELECT test.test_id,
                    test.test_name,
                    test.test_result,
                    test.test_message,
                    test.test_execution_time,
                    test.node_root,
                    test.node_left,
                    test.node_right,
                    test.node_is_leaf,
                    code_class.code_class_name,
                    code_method.code_method_name
               FROM test
         LEFT JOIN code_method
                 ON code_method.code_method_id = test.code_method_id
         LEFT JOIN code_class
                 ON code_class.code_class_id = code_method.code_class_id
              WHERE test.run_id = (
                    SELECT run_id 
                      FROM run 
                     WHERE revision = :revision
                    )'
        );
        $select->bindValue( ':revision', $this->revision );
        $select->execute();
        
        $tests =  $select->fetchAll( PDO::FETCH_ASSOC );
        
        $select->closeCursor();
        
        $insert = $this->conn->prepare(
            'INSERT INTO test (
                         run_id,
                         test_name,
                         test_result,
                         test_message,
                         test_execution_time,
                         code_method_id,
                         node_root,
                         node_left,
                         node_right,
                         node_is_leaf
              ) VALUES (
                         :run_id,
                         :test_name,
                         :test_result,
                         :test_message,
                         :execution_time,
                         :code_method_id,
                         :node_root,
                         :node_left,
                         :node_right,
                         :node_is_leaf
                       )'
        );
        $insert->bindValue( ':run_id', $this->runId );
        $insert->bindValue( ':node_root', $this->testId );
        
        $executionTime = 0.0;

        foreach ( $tests as $test )
        {
            $methodName = sprintf(
                '%s::%s',
                $test['code_class_name'],
                $test['code_method_name']
            );
            
            $methodId = null;
            if ( isset( $this->codeMethodMap[$methodName] ) )
            {
                $methodId = $this->codeMethodMap[$methodName];
            }
                        
            $left  = $test['node_left'] + $this->nestedSetUpdate;
            $right = $test['node_right'] + $this->nestedSetUpdate;

            $insert->bindValue( ':test_name', $test['test_name'] );
            $insert->bindValue( ':test_result', $test['test_result'] );
            $insert->bindValue( ':test_message', $test['test_message'] );
            $insert->bindValue( ':execution_time', $test['test_execution_time'] );
            $insert->bindValue( ':code_method_id', $methodId );
            $insert->bindValue( ':node_left', $left );
            $insert->bindValue( ':node_right', $right );
            $insert->bindValue( ':node_is_leaf', $test['node_is_leaf'] );
            $insert->execute();
            
            $this->testIdMap[$test['test_id']] = $this->conn->lastInsertId();
            
            if ( 1 === (int) $test['node_left'] )
            {
                $executionTime = (float) $test['test_execution_time']; 
            }
        }

        $this->nestedSetUpdate += count( $tests ) * 2;

        $stmt = $this->conn->prepare(
            'UPDATE test
                SET node_right          = :node_right + 1,
                    test_execution_time = test_execution_time + :test_execution_time
              WHERE test_id             = :test_id'
        );
        $stmt->bindValue( 'test_execution_time', $executionTime );
        $stmt->bindValue( ':node_right', $this->nestedSetUpdate );
        $stmt->bindValue( ':test_id', $this->testId );
        $stmt->execute();
    }
    
    /**
     * Inserts all covered lines into the aggregated database. This means that
     * this method maps the old and new test and line identifiers.
     *
     * @param PDO $conn The context input connection.
     * 
     * @return void
     */
    protected function mergeCodeCoverage( PDO $conn )
    {
        $tests = join( ',', array_keys( $this->testIdMap ) );

        $stmt = $conn->prepare(
            "SELECT code_coverage.test_id,
                    code_line.code_line_number,
                    code_file.code_file_name
               FROM code_coverage
         INNER JOIN code_line
                 ON code_line.code_line_id = code_coverage.code_line_id
         INNER JOIN code_file
                 ON code_file.code_file_id = code_line.code_file_id
              WHERE code_coverage.test_id IN ({$tests})"
        );
        $stmt->execute();
        $lines = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $stmt->closeCursor();
        
        $stmt = $this->conn->prepare(
            'INSERT INTO code_coverage
                         (test_id, code_line_id)
                  VALUES (:test_id, :code_line_id)'
        );

        foreach ( $lines as $line )
        {
            $name = $line['code_file_name'];
            $no   = $line['code_line_number'];
            
            $testId = $this->testIdMap[$line['test_id']];
            $lineId = $this->fileLineMap[$name][$no];
            
            $stmt->bindValue( ':test_id', $testId );
            $stmt->bindValue( ':code_line_id', $lineId );
            $stmt->execute();
        }
    }

    /**
     * Inserts a code file into the aggregated log database.
     *
     * @param PDO   $conn Connection to the log database.
     * @param array $file The context file entry.
     * 
     * @return void
     */
    protected function insertCodeFile( PDO $conn, array $file )
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO code_file (
                         code_file_name, 
                         code_file_md5, 
                         revision
              ) VALUES (
                         :code_file_name, 
                         :code_file_md5, 
                         :revision
                       )'
        );
        $stmt->bindValue( ':code_file_name', $file['code_file_name'] );
        $stmt->bindValue( ':code_file_md5', $file['code_file_md5'] );
        $stmt->bindValue( ':revision', $this->revision );
        $stmt->execute();
        
        $fileId = $this->conn->lastInsertId();
        
        $this->codeFileMap[$file['code_file_id']] = $fileId;
        
        $this->insertFileMetrics( $conn, $file['code_file_id'], $fileId );
        $this->insertCodeLines( $conn, $file, $fileId );
        $this->insertCodeFunctions( $conn, $file['code_file_id'], $fileId );
        $this->insertCodeClasses( $conn, $file['code_file_id'], $fileId );
    }
    
    /**
     * Inserts the metrics for a code file.
     *
     * @param PDO     $conn  Connection to the log database.
     * @param integer $oldId The old file identifier.
     * @param integer $newId The new file identifier.
     * 
     * @return void
     */
    protected function insertFileMetrics( PDO $conn, $oldId, $newId )
    {
        $select = $conn->prepare(
            'SELECT metrics_file_coverage,
                    metrics_file_loc,
                    metrics_file_cloc,
                    metrics_file_ncloc,
                    metrics_file_loc_executable,
                    metrics_file_loc_executed
               FROM metrics_file
         INNER JOIN run
                 ON run.run_id   = metrics_file.run_id
                AND run.revision = :revision
              WHERE code_file_id = :file_id'
        );
        $select->bindValue( ':revision', $this->revision );
        $select->bindValue( ':file_id', $oldId );
        $select->execute();
        
        $metrics = $select->fetch( PDO::FETCH_ASSOC );
        
        $select->closeCursor();
        
        $insert = $this->conn->prepare(
            'INSERT INTO metrics_file (
                         run_id,
                         code_file_id,
                         metrics_file_coverage,
                         metrics_file_loc,
                         metrics_file_cloc,
                         metrics_file_ncloc,
                         metrics_file_loc_executable,
                         metrics_file_loc_executed
              ) VALUES (
                         :run_id,
                         :code_file_id,
                         :metrics_file_coverage,
                         :metrics_file_loc,
                         :metrics_file_cloc,
                         :metrics_file_ncloc,
                         :metrics_file_loc_executable,
                         :metrics_file_loc_executed
                       )'
        );
        $insert->bindValue( ':run_id', $this->runId );
        $insert->bindValue( ':code_file_id', $newId );
        
        foreach ( $metrics as $column => $value )
        {
            $insert->bindValue( ":{$column}", $value );
        }
        
        $insert->execute();
    }
    
    /**
     * Inserts all code lines for <b>$file</b> with the new parent <b>$fileId</b>.
     *
     * @param PDO     $conn   Connection to the log database.
     * @param array   $file   The context code file entry.
     * @param integer $fileId The new aggregated file identifier.
     * 
     * @return void
     */
    protected function insertCodeLines( PDO $conn, array $file, $fileId )
    {
        $fileName = $file['code_file_name'];
        
        $this->fileLineMap[$fileName] = array();
        
        $stmt1 = $this->conn->prepare(
            'INSERT INTO code_line (
                         code_file_id, 
                         code_line_number, 
                         code_line, 
                         code_line_covered
              ) VALUES (
                         :code_file_id, 
                         :code_line_number, 
                         :code_line, 
                         :code_line_covered
                       )'
        );
        $stmt1->bindValue( ':code_file_id', $fileId );
        
        $select = $conn->prepare(
            'SELECT code_line_id,
                    code_line_number,
                    code_line,
                    code_line_covered
               FROM code_line
              WHERE code_file_id = :code_file_id'
        );
        
        $select->bindValue( ':code_file_id', $file['code_file_id'] );
        $select->execute();
        
        $lines = $select->fetchAll( PDO::FETCH_ASSOC );
        
        $select->closeCursor();

        foreach ( $lines as $line )
        {
            $covered = (int) $line['code_line_covered'];

            $stmt1->bindValue( ':code_line', $line['code_line'] );
            $stmt1->bindValue( ':code_line_number', $line['code_line_number'] );
            $stmt1->bindValue( ':code_line_covered', $covered );
            $stmt1->execute();
            
            $no = $line['code_line_number'];

            $this->fileLineMap[$fileName][$no] = $this->conn->lastInsertId();
        }
    }
    
    /**
     * Updates the stored coverage information for the given <b>$file</b>.
     *
     * @param PDO   $conn Connection to the log database.
     * @param array $file The context file entry.
     * 
     * @return void
     */
    protected function updateCodeLines( PDO $conn, array $file )
    {
        $select = $conn->prepare(
            'SELECT code_line_number
               FROM code_line
              WHERE code_line_covered = 1
                AND code_file_id      = :code_file_id'
        );
        $select->bindValue( ':code_file_id', $file['code_file_id'] );
        $select->execute();
        
        $lines = $select->fetchAll( PDO::FETCH_ASSOC );
        
        $select->closeCursor();
        
        $update = $this->conn->prepare(
            'UPDATE code_line
                SET code_line_covered = 1
              WHERE code_line_id      = :code_line_id'
        );
        foreach ( $lines as $line )
        {
            $name = $file['code_file_name'];
            $no   = $line['code_line_number'];

            $update->bindValue( ':code_line_id', $this->fileLineMap[$name][$no] );
            $update->execute();
        }
    }
    
    /**
     * Inserts the code records for functions.
     *
     * @param PDO     $conn      Connection to a log database.
     * @param integer $oldFileId The old parent file identifier.
     * @param integer $newFileId The new parent file identifier.
     * 
     * @return void
     */
    protected function insertCodeFunctions( PDO $conn, $oldFileId, $newFileId )
    {
        $stmt1 = $this->conn->prepare(
            'INSERT INTO code_function (
                         code_file_id, 
                         code_function_name, 
                         code_function_start_line, 
                         code_function_end_line
              ) VALUES (
                         :code_file_id, 
                         :function_name, 
                         :start_line, 
                         :end_line
                       )'
        );
        $stmt1->bindValue( ':code_file_id', $newFileId );
        
        $stmt2 = $conn->prepare(
            'SELECT code_function_id,
                    code_function_name,
                    code_function_start_line,
                    code_function_end_line
               FROM code_function
              WHERE code_file_id = :code_file_id'
        );
        $stmt2->bindValue( ':code_file_id', $oldFileId );
        $stmt2->execute();
        
        while ( ( $func = $stmt2->fetch( PDO::FETCH_ASSOC ) ) !== false )
        {
            $stmt1->bindValue( ':function_name', $func['code_function_name'] );
            $stmt1->bindValue( ':end_line', $func['code_function_end_line'] );
            $stmt1->bindValue( ':start_line', $func['code_function_start_line'] );
            $stmt1->execute();
            
            $oldId = $func['code_function_id'];
            $newId = $this->conn->lastInsertId();
            
            $this->insertFunctionMetrics( $conn, $oldId, $newId );
        }
        $stmt2->closeCursor();
    }
    
    /**
     * Inserts the original function metrics for <b>$oldId</b>.
     *
     * @param PDO     $conn  Connection to a log database.
     * @param integer $oldId The old function identifier.
     * @param integer $newId The new function identifier.
     * 
     * @return void
     */
    protected function insertFunctionMetrics(PDO $conn, $oldId, $newId)
    {
        $select = $conn->prepare(
            'SELECT metrics_function_coverage,
                    metrics_function_loc,
                    metrics_function_loc_executable,
                    metrics_function_loc_executed,
                    metrics_function_ccn,
                    metrics_function_crap,
                    metrics_function_npath
               FROM metrics_function
         INNER JOIN run
                 ON run.run_id       = metrics_function.run_id
                AND run.revision     = :revision
              WHERE code_function_id = :function_id'
        );
        $select->bindValue( ':revision', $this->revision );
        $select->bindValue( ':function_id', $oldId );
        $select->execute();
        
        $metrics = $select->fetch( PDO::FETCH_ASSOC );
        
        $select->closeCursor();
        
        $insert = $this->conn->prepare(
            'INSERT INTO metrics_function (
                         run_id,
                         code_function_id,
                         metrics_function_coverage,
                         metrics_function_loc,
                         metrics_function_loc_executable,
                         metrics_function_loc_executed,
                         metrics_function_ccn,
                         metrics_function_crap,
                         metrics_function_npath
              ) VALUES (
                         :run_id,
                         :code_function_id,
                         :metrics_function_coverage,
                         :metrics_function_loc,
                         :metrics_function_loc_executable,
                         :metrics_function_loc_executed,
                         :metrics_function_ccn,
                         :metrics_function_crap,
                         :metrics_function_npath
                       )'
        );
        $insert->bindValue( ':run_id', $this->runId );
        $insert->bindValue( ':code_function_id', $newId );
        
        foreach ( $metrics as $column => $value )
        {
            $insert->bindValue( ":{$column}", $value );
        }
        
        $insert->execute();
    }
    
    /**
     * Inserts the code record for a class.
     *
     * @param PDO     $conn      Connection to a log database.
     * @param integer $oldFileId The old parent file identifier.
     * @param integer $newFileId The new parent file identifier.
     * 
     * @return void
     */
    protected function insertCodeClasses( PDO $conn, $oldFileId, $newFileId )
    {
        $stmt1 = $this->conn->prepare(
            'INSERT INTO code_class (
                         code_file_id, 
                         code_class_parent_id, 
                         code_class_name, 
                         code_class_start_line, 
                         code_class_end_line
              ) VALUES (
                         :code_file_id, 
                         :parent_id,
                         :class_name, 
                         :start_line, 
                         :end_line
                       )'
        );
        $stmt1->bindValue( ':code_file_id', $newFileId );
        
        $stmt2 = $conn->prepare(
            'SELECT c0.code_class_id,
                    c0.code_class_parent_id,
                    c0.code_class_name,
                    c0.code_class_start_line,
                    c0.code_class_end_line,
                    c1.code_class_name AS parent_code_class_name
               FROM code_class c0
          LEFT JOIN code_class c1
                 ON c1.code_class_id = c0.code_class_parent_id
              WHERE c0.code_file_id = :code_file_id
           ORDER BY c0.code_class_parent_id ASC'
        );
        $stmt2->bindValue( ':code_file_id', $oldFileId );
        $stmt2->execute();
        
        foreach ( $stmt2->fetchAll( PDO::FETCH_ASSOC ) as $class )
        {
            $parentId = null;
            if ( $class['parent_code_class_name'] != null )
            {
                $parentId = $this->codeClassMap[$class['parent_code_class_name']];
            }
            
            $stmt1->bindValue( ':class_name', $class['code_class_name'] );
            $stmt1->bindValue( ':parent_id', $parentId );
            $stmt1->bindValue( ':end_line', $class['code_class_end_line'] );
            $stmt1->bindValue( ':start_line', $class['code_class_start_line'] );
            $stmt1->execute();
            
            $newId = $this->conn->lastInsertId();
            $oldId = $class['code_class_id'];
            
            $this->codeClassMap[$class['code_class_name']] = $newId;
            $this->insertClassMetrics( $conn, $oldId, $newId );
            $this->insertCodeMethods( $conn, $oldId, $newId );
        }
        $stmt2->closeCursor();
    }
    
    /**
     * Inserts the metric record for a code class. 
     *
     * @param PDO     $conn  Connection to a log database.
     * @param integer $oldId The old class identifier.
     * @param integer $newId The new class identifier.
     * 
     * @return void
     */
    protected function insertClassMetrics( PDO $conn, $oldId, $newId )
    {
        $select = $conn->prepare(
            'SELECT metrics_class_coverage,
                    metrics_class_loc,
                    metrics_class_loc_executable,
                    metrics_class_loc_executed,
                    metrics_class_aif,
                    metrics_class_ahf,
                    metrics_class_cis,
                    metrics_class_csz,
                    metrics_class_dit,
                    metrics_class_impl,
                    metrics_class_mif,
                    metrics_class_mhf,
                    metrics_class_noc,
                    metrics_class_pf,
                    metrics_class_vars,
                    metrics_class_varsnp,
                    metrics_class_varsi,
                    metrics_class_wmc,
                    metrics_class_wmcnp,
                    metrics_class_wmci
               FROM metrics_class
         INNER JOIN run
                 ON run.run_id    = metrics_class.run_id
                AND run.revision  = :revision
              WHERE code_class_id = :class_id'
        );
        $select->bindValue( ':revision', $this->revision );
        $select->bindValue( ':class_id', $oldId );
        $select->execute();
        
        $metrics = $select->fetch( PDO::FETCH_ASSOC );
        
        $select->closeCursor();
        
        $insert = $this->conn->prepare(
            'INSERT INTO metrics_class (
                         run_id,
                         code_class_id,
                         metrics_class_coverage,
                         metrics_class_loc,
                         metrics_class_loc_executable,
                         metrics_class_loc_executed,
                         metrics_class_aif,
                         metrics_class_ahf,
                         metrics_class_cis,
                         metrics_class_csz,
                         metrics_class_dit,
                         metrics_class_impl,
                         metrics_class_mif,
                         metrics_class_mhf,
                         metrics_class_noc,
                         metrics_class_pf,
                         metrics_class_vars,
                         metrics_class_varsnp,
                         metrics_class_varsi,
                         metrics_class_wmc,
                         metrics_class_wmcnp,
                         metrics_class_wmci
              ) VALUES (
                         :run_id,
                         :code_class_id,
                         :metrics_class_coverage,
                         :metrics_class_loc,
                         :metrics_class_loc_executable,
                         :metrics_class_loc_executed,
                         :metrics_class_aif,
                         :metrics_class_ahf,
                         :metrics_class_cis,
                         :metrics_class_csz,
                         :metrics_class_dit,
                         :metrics_class_impl,
                         :metrics_class_mif,
                         :metrics_class_mhf,
                         :metrics_class_noc,
                         :metrics_class_pf,
                         :metrics_class_vars,
                         :metrics_class_varsnp,
                         :metrics_class_varsi,
                         :metrics_class_wmc,
                         :metrics_class_wmcnp,
                         :metrics_class_wmci
                       )'
        );
        $insert->bindValue( ':run_id', $this->runId );
        $insert->bindValue( ':code_class_id', $newId );
        
        foreach ( $metrics as $column => $value )
        {
            $insert->bindValue( ":{$column}", $value );
        }
        
        $insert->execute();
    }
    
    /**
     * Inserts the code records for class methods.
     *
     * @param PDO     $conn       Connection to a log database.
     * @param integer $oldClassId The old parent class identifier.
     * @param integer $newClassId The new parent class identifier.
     * 
     * @return void
     */
    protected function insertCodeMethods( PDO $conn, $oldClassId, $newClassId )
    {
        $stmt1 = $this->conn->prepare(
            'INSERT INTO code_method (
                         code_class_id,
                         code_method_name,
                         code_method_start_line,
                         code_method_end_line
              ) VALUES (
                         :class_id,
                         :method_name,
                         :start_line,
                         :end_line
                       )'
        );
        $stmt1->bindValue( ':class_id', $newClassId );
        
        $stmt2 = $conn->prepare(
            'SELECT m0.code_method_id,
                    m0.code_method_name,
                    m0.code_method_start_line,
                    m0.code_method_end_line,
                    c0.code_class_name
               FROM code_method m0
         INNER JOIN code_class c0
                 ON c0.code_class_id = m0.code_class_id
              WHERE m0.code_class_id = :code_class_id'
        );
        $stmt2->bindValue( ':code_class_id', $oldClassId );
        $stmt2->execute();
        
        foreach ( $stmt2->fetchAll( PDO::FETCH_ASSOC ) as $method )
        {
            $stmt1->bindValue( ':method_name', $method['code_method_name'] );
            $stmt1->bindValue( ':end_line', $method['code_method_end_line'] );
            $stmt1->bindValue( ':start_line', $method['code_method_start_line'] );
            $stmt1->execute();
            
            $oldId = $method['code_method_id'];
            $newId = $this->conn->lastInsertId();
            
            $methodName = sprintf( 
                '%s::%s', 
                $method['code_class_name'],
                $method['code_method_name']
            ); 
            
            $this->codeMethodMap[$methodName] = $newId;
            
            $this->insertMethodMetrics( $conn, $oldId, $newId );
        }
        $stmt2->closeCursor();
    }
    
    /**
     * Inserts the original method metrics for <b>$oldId</b>.
     *
     * @param PDO     $conn  Connection to a log database.
     * @param integer $oldId The old method identifier.
     * @param integer $newId The new method identifier.
     * 
     * @return void
     */
    protected function insertMethodMetrics( PDO $conn, $oldId, $newId )
    {
        $select = $conn->prepare(
            'SELECT metrics_method_coverage,
                    metrics_method_loc,
                    metrics_method_loc_executable,
                    metrics_method_loc_executed,
                    metrics_method_ccn,
                    metrics_method_crap,
                    metrics_method_npath
               FROM metrics_method
         INNER JOIN run
                 ON run.run_id     = metrics_method.run_id
                AND run.revision   = :revision
              WHERE code_method_id = :method_id'
        );
        $select->bindValue( ':revision', $this->revision );
        $select->bindValue( ':method_id', $oldId );
        $select->execute();
        
        $metrics = $select->fetch( PDO::FETCH_ASSOC );
        
        $select->closeCursor();
        
        $insert = $this->conn->prepare(
            'INSERT INTO metrics_method (
                         run_id,
                         code_method_id,
                         metrics_method_coverage,
                         metrics_method_loc,
                         metrics_method_loc_executable,
                         metrics_method_loc_executed,
                         metrics_method_ccn,
                         metrics_method_crap,
                         metrics_method_npath
              ) VALUES (
                         :run_id,
                         :code_method_id,
                         :metrics_method_coverage,
                         :metrics_method_loc,
                         :metrics_method_loc_executable,
                         :metrics_method_loc_executed,
                         :metrics_method_ccn,
                         :metrics_method_crap,
                         :metrics_method_npath
                       )'
        );
        $insert->bindValue( ':run_id', $this->runId );
        $insert->bindValue( ':code_method_id', $newId );
        
        foreach ( $metrics as $column => $value )
        {
            $insert->bindValue( ":{$column}", $value );
        }
        
        $insert->execute();
    }
    
    /**
     * Inserts the global project metrics, stored in the given database. This
     * method is only called for one database, which means that the project
     * metrics do not reflect all aggregated values.
     *
     * @param PDO $conn Connection to a log database.
     * 
     * @return void
     */
    protected function insertProjectMetrics( PDO $conn )
    {
        $select = $conn->prepare(
            'SELECT metrics_project_cls,
                    metrics_project_clsa,
                    metrics_project_clsc,
                    metrics_project_roots,
                    metrics_project_leafs,
                    metrics_project_interfs,
                    metrics_project_maxdit
               FROM metrics_project
              WHERE run_id = (SELECT run_id FROM run WHERE revision = :revision)'
        );
        $select->bindValue( ':revision', $this->revision );
        $select->execute();
        
        $project = $select->fetch( PDO::FETCH_ASSOC );
        
        $select->closeCursor();
        
        $insert = $this->conn->prepare(
            'INSERT INTO metrics_project (
                         run_id,
                         metrics_project_cls,
                         metrics_project_clsa,
                         metrics_project_clsc,
                         metrics_project_roots,
                         metrics_project_leafs,
                         metrics_project_interfs,
                         metrics_project_maxdit
              ) VALUES (
                         :run_id,
                         :cls,
                         :clsa,
                         :clsc,
                         :roots,
                         :leafs,
                         :interfs,
                         :maxdit
                       )'
        );
        
        $insert->bindValue( ':run_id', $this->runId );
        $insert->bindValue( ':cls', $project['metrics_project_cls'] );
        $insert->bindValue( ':clsa', $project['metrics_project_clsa'] );
        $insert->bindValue( ':clsc', $project['metrics_project_clsc'] );
        $insert->bindValue( ':roots', $project['metrics_project_roots'] );
        $insert->bindValue( ':leafs', $project['metrics_project_leafs'] );
        $insert->bindValue( ':interfs', $project['metrics_project_interfs'] );
        $insert->bindValue( ':maxdit', $project['metrics_project_maxdit'] );
        $insert->execute();
    }
    
    /**
     * Updates the executed lines and the code coverage value for all aggregated
     * code files.
     *
     * @return void
     */
    protected function updateFileMetrics()
    {
        $select = $this->conn->prepare(
            'SELECT cl.code_file_id AS code_file_id,
                    cc.code_line_id AS code_line_id
               FROM code_file cf
         INNER JOIN code_line cl
                 ON cl.code_file_id = cf.code_file_id
                AND cl.code_line_covered IN (-1, 1)
          LEFT JOIN code_coverage cc
                 ON cc.code_line_id = cl.code_line_id
              WHERE cf.revision = :revision
           GROUP BY cl.code_line_id'
        );
        $select->bindValue( ':revision', $this->revision );
        $select->execute();
        
        $lines = $select->fetchAll( PDO::FETCH_ASSOC );
        
        $select->closeCursor();
        
        $metrics = array();
        foreach ( $lines as $line )
        {
            $fileId = $line['code_file_id'];
            
            if ( !isset( $metrics[$fileId] ) )
            {
                $metrics[$fileId] = array(
                    'loc'   =>  0,
                    'eloc'  =>  0,
                );
            }

            ++$metrics[$fileId]['loc'];
            
            if ( 0 !== (int) $line['code_line_id'] )
            {
                ++$metrics[$fileId]['eloc'];
            }
        }
        
        $update = $this->conn->prepare(
            'UPDATE metrics_file
                SET metrics_file_coverage       = :coverage,
                    metrics_file_loc_executed   = :executed,
                    metrics_file_loc_executable = :executable
              WHERE code_file_id                = :file_id'
        );
        
        foreach ( $metrics as $fileId => $metric )
        {
            $coverage = $this->calculateCoverage( $metric['loc'], $metric['eloc'] );
            
            $update->bindValue( ':file_id', $fileId );
            $update->bindValue( ':coverage', $coverage );
            $update->bindValue( ':executed', $metric['eloc'] );
            $update->bindValue( ':executable', $metric['loc'] );
            $update->execute();
        }
    }
    
    /**
     * Updates the executed lines, the code coverage and the crap index for all
     * aggregated functions.
     *
     * @return void
     */
    protected function updateFunctionMetrics()
    {
        $select = $this->conn->prepare(
            'SELECT cfu.code_function_id    AS code_function_id,
                    mf.metrics_function_ccn AS metrics_function_ccn,
                    cov.code_line_id        AS code_line_id
               FROM code_file cf
         INNER JOIN code_function cfu
                 ON cfu.code_file_id = cf.code_file_id
         INNER JOIN metrics_function mf
                 ON mf.code_function_id = cfu.code_function_id
         INNER JOIN code_line cl
                 ON cl.code_file_id = cf.code_file_id
                AND cl.code_line_number 
            BETWEEN cfu.code_function_start_line 
                AND cfu.code_function_end_line 
                AND cl.code_line_covered IN (-1, 1)
          LEFT JOIN code_coverage AS cov
                 ON cov.code_line_id = cl.code_line_id
              WHERE cf.revision = :revision
           GROUP BY cl.code_line_id
           ORDER BY cfu.code_function_id,
                    cl.code_line_number'
        );
        $select->bindValue( ':revision', $this->revision );
        $select->execute();
        
        $lines = $select->fetchAll( PDO::FETCH_ASSOC );
        
        $select->closeCursor();
        
        $metrics = array();
        foreach ( $lines as $line )
        {
            $functionId = $line['code_function_id'];
            
            if ( !isset( $metrics[$functionId] ) )
            {
                $metrics[$functionId] = array(
                    'ccn'   =>  $line['metrics_function_ccn'],
                    'loc'   =>  0,
                    'eloc'  =>  0,
                );
            }
            
            ++$metrics[$functionId]['loc'];
            
            if ( 0 !== (int) $line['code_line_id'] )
            {
                ++$metrics[$functionId]['eloc'];
            }
        }
        
        $update = $this->conn->prepare(
            'UPDATE metrics_function
                SET metrics_function_crap           = :crap,
                    metrics_function_coverage       = :coverage,
                    metrics_function_loc_executed   = :executed,
                    metrics_function_loc_executable = :executable
              WHERE code_function_id                = :function_id'
        );
        
        foreach ( $metrics as $functionId => $metric )
        {
            $coverage = $this->calculateCoverage( $metric['loc'], $metric['eloc'] );
            
            $ccn  = $metric['ccn'];
            $crap = pow( $ccn, 2 ) * pow( ( 1 - $coverage / 100 ), 3 ) + $ccn; 
            
            $update->bindValue( ':crap', $crap );
            $update->bindValue( ':coverage', $coverage );
            $update->bindValue( ':executed', $metric['eloc'] );
            $update->bindValue( ':executable', $metric['loc'] );
            $update->bindValue( ':function_id', $functionId );
            $update->execute();
        }
    }
    
    /**
     * Updates the executed lines, the code coverage and the crap index for all
     * aggregated methods.
     *
     * @return void
     */
    protected function updateMethodMetrics()
    {
        $select = $this->conn->prepare(
            'SELECT cm.code_method_id     AS code_method_id,
                    mm.metrics_method_ccn AS metrics_method_ccn,
                    cov.code_line_id      AS code_line_id
               FROM code_file cf
         INNER JOIN code_class cc
                 ON cc.code_file_id = cf.code_file_id
         INNER JOIN code_method cm
                 ON cm.code_class_id = cc.code_class_id
         INNER JOIN metrics_method mm
                 ON mm.code_method_id = cm.code_method_id
         INNER JOIN code_line cl
                 ON cl.code_file_id = cf.code_file_id
                AND cl.code_line_number 
            BETWEEN cm.code_method_start_line 
                AND cm.code_method_end_line 
                AND cl.code_line_covered IN (-1, 1)
          LEFT JOIN code_coverage AS cov
                 ON cov.code_line_id = cl.code_line_id
              WHERE cf.revision = :revision
           GROUP BY cl.code_line_id
           ORDER BY cc.code_class_id,
                    cm.code_method_id,
                    cl.code_line_number'
        );
        $select->bindValue( ':revision', $this->revision );
        $select->execute();
        
        $lines = $select->fetchAll( PDO::FETCH_ASSOC );
        
        $select->closeCursor();
        
        $metrics = array();
        foreach ( $lines as $line )
        {
            $methodId = $line['code_method_id'];
            
            if ( !isset( $metrics[$methodId] ) )
            {
                $metrics[$methodId] = array(
                    'ccn'   =>  $line['metrics_method_ccn'],
                    'loc'   =>  0,
                    'eloc'  =>  0,
                );
            }
            
            ++$metrics[$methodId]['loc'];
            
            if ( 0 !== (int) $line['code_line_id'] )
            {
                ++$metrics[$methodId]['eloc'];
            }
        }
        
        $update = $this->conn->prepare(
            'UPDATE metrics_method
                SET metrics_method_crap           = :crap,
                    metrics_method_coverage       = :coverage,
                    metrics_method_loc_executed   = :executed,
                    metrics_method_loc_executable = :executable
              WHERE code_method_id                = :method_id'
        );
        
        foreach ( $metrics as $methodId => $metric )
        {
            $coverage = $this->calculateCoverage( $metric['loc'], $metric['eloc'] );
            
            $ccn  = $metric['ccn'];
            $crap = pow( $ccn, 2 ) * pow( ( 1 - $coverage / 100 ), 3 ) + $ccn; 
            
            $update->bindValue( ':crap', $crap );
            $update->bindValue( ':coverage', $coverage );
            $update->bindValue( ':executed', $metric['eloc'] );
            $update->bindValue( ':executable', $metric['loc'] );
            $update->bindValue( ':method_id', $methodId );
            $update->execute();
        }
    }
    
    /**
     * Updates the executed lines and the code coverage for all aggregated classes.
     *
     * @return void
     */
    protected function updateClassMetrics()
    {
            $select = $this->conn->prepare(
            'SELECT cc.code_class_id AS code_class_id,
                    cov.code_line_id AS code_line_id
               FROM code_file cf
         INNER JOIN code_class cc
                 ON cc.code_file_id = cf.code_file_id
         INNER JOIN code_line cl
                 ON cl.code_file_id = cf.code_file_id
                AND cl.code_line_number 
            BETWEEN cc.code_class_start_line 
                AND cc.code_class_end_line 
                AND cl.code_line_covered IN (-1, 1)
          LEFT JOIN code_coverage AS cov
                 ON cov.code_line_id = cl.code_line_id
              WHERE cf.revision = :revision
           GROUP BY cl.code_line_id
           ORDER BY cc.code_class_id,
                    cl.code_line_number'
        );
        $select->bindValue( ':revision', $this->revision );
        $select->execute();
        
        $lines = $select->fetchAll( PDO::FETCH_ASSOC );
        
        $select->closeCursor();
        
        $metrics = array();
        foreach ( $lines as $line )
        {
            $classId = $line['code_class_id'];
            
            if ( !isset( $metrics[$classId] ) )
            {
                $metrics[$classId] = array(
                    'loc'   =>  0,
                    'eloc'  =>  0,
                );
            }
            
            ++$metrics[$classId]['loc'];
            
            if ( 0 !== (int) $line['code_line_id'] )
            {
                ++$metrics[$classId]['eloc'];
            }
        }

        $update = $this->conn->prepare(
            'UPDATE metrics_class
                SET metrics_class_coverage       = :coverage,
                    metrics_class_loc_executed   = :executed,
                    metrics_class_loc_executable = :executable
              WHERE code_class_id                = :class_id'
        );
        
        foreach ( $metrics as $classId => $metric )
        {
            $coverage = $this->calculateCoverage( $metric['loc'], $metric['eloc'] ); 
            
            $update->bindValue( ':class_id', $classId );
            $update->bindValue( ':coverage', $coverage );
            $update->bindValue( ':executed', $metric['eloc'] );
            $update->bindValue( ':executable', $metric['loc'] );
            $update->execute();
        }
    }
    
    /**
     * Calculates the code coverage value based on exectuable and executed lines
     * of code.
     *
     * @param integer $executable Number of executable lines.
     * @param integer $executed   Number of executed lines.
     * 
     * @return float
     */
    protected function calculateCoverage( $executable, $executed )
    {
        $coverage = 100;
        if ( $executable > 0 )
        {
            $coverage = ( $executed / $executable ) * 100;
        }
        return round( $coverage, 5 );
    }
}