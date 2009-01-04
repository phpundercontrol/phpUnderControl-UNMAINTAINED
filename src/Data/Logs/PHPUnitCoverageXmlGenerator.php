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

/**
 * Generates the code coverage xml log file from a PHPUnit log database.
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
class phpucPHPUnitCoverageXmlGenerator
{
    /**
     * The log database connection.
     *
     * @type PDO
     * @var PDO $pdo
     */
    protected $pdo = null;
    
    /**
     * The log revision number.
     * 
     * @type integer
     * @var integer $revision
     */
    protected $revision = 0;
    
    /**
     * The output log document.
     *
     * @type DOMDocument
     * @var DOMDocument $document
     */
    protected $document = null;
    
    /**
     * The main project section.
     *
     * @type DOMElement
     * @var DOMElement $project
     */
    protected $project = null;
    
    /**
     * Constructs a new coverage xml generator.
     *
     * @param PDO     $pdo      The log database connection.
     * @param integer $revision The log revision number.
     */
    public function __construct( PDO $pdo, $revision )
    {
        $this->pdo      = $pdo;
        $this->revision = $revision;
    }
    
    /**
     * Saves the generated coverage log file.
     *
     * @param string $fileName The path to the saved XML document.
     * 
     * @return integer|boolean Written bytes or <b>false</b>.
     */
    public function store( $fileName )
    {
        $this->generate();
        
        return $this->document->save( $fileName );
    }
    
    /**
     * Generates the log file contents if this wasn't done before.
     *
     * @return void
     */
    protected function generate()
    {
        if ( $this->document !== null )
        {
            return;
        }
        
        $this->createDocument();
        $this->generateFiles();
    }
    
    /**
     * Creates the dom document and the base log file elements.
     *
     * @return void
     */
    protected function createDocument()
    {
        $this->document               = new DOMDocument( '1.0', 'UTF-8' );
        $this->document->formatOutput = true;
        
        $timestamp = time();
        
        $coverage = $this->document->createElement( 'coverage' );
        $coverage->setAttribute( 'generated', $timestamp );
        $coverage->setAttribute( 'php-under-control', '@package_version@' );
        $this->document->appendChild( $coverage );
        
        $select = $this->pdo->prepare(
            'SELECT t.test_name AS name
               FROM run r
         INNER JOIN test t
                 ON t.run_id    = r.run_id
                AND t.node_left = 1
              WHERE r.revision  = :revision'
        );
        $select->bindValue( ':revision', $this->revision );
        $select->execute();
        
        $name = $select->fetchColumn();
        
        $select->closeCursor();
        
        $this->project = $this->document->createElement( 'project' );
        $this->project->setAttribute( 'name', $name );
        $this->project->setAttribute( 'timestamp', $timestamp );
        $coverage->appendChild( $this->project );
    }
    
    /**
     * Generates all log file file sections.
     *
     * @return void
     */
    protected function generateFiles()
    {
        $select = $this->pdo->prepare(
            'SELECT cf.code_file_id                 AS id,
                    cf.code_file_name               AS name,
                    mf.metrics_file_loc             AS loc,
                    mf.metrics_file_ncloc           AS ncloc,
                    mf.metrics_file_loc_executed    AS covered,
                    mf.metrics_file_loc_executable  AS statements
               FROM code_file cf
         INNER JOIN metrics_file mf
                 ON mf.code_file_id = cf.code_file_id
              WHERE cf.revision = :revision
           ORDER BY cf.code_file_name ASC'
        );
        $select->bindValue( ':revision', $this->revision );
        $select->execute();
        
        foreach ( $select->fetchAll( PDO::FETCH_ASSOC ) as $info )
        {
            $file = $this->document->createElement( 'file' );
            $file->setAttribute( 'name', $info['name'] );
            $this->project->appendChild( $file );
            
            $details = $this->generateClasses( $file, $info['id'] );
            $this->generateLines( $file, $info['id'] );
            
            $elements = $info['statements'] + $details['methods'];
            $covered  = $info['covered'] + $details['covered'];
            
            $metrics = $this->document->createElement( 'metrics' );
            $metrics->setAttribute( 'loc', $info['loc'] );
            $metrics->setAttribute( 'ncloc', $info['ncloc'] );
            $metrics->setAttribute( 'classes', $details['classes'] );
            $metrics->setAttribute( 'methods', $details['methods'] );
            $metrics->setAttribute( 'coveredmethods', $details['covered'] );
            $metrics->setAttribute( 'statements', $info['statements'] );
            $metrics->setAttribute( 'coveredstatements', $info['covered'] );
            $metrics->setAttribute( 'elements', $elements );
            $metrics->setAttribute( 'coveredelements', $covered );
            $file->appendChild( $metrics );
        }
    }
    
    /**
     * Generates the class sections for the given <b>$fileId</b> and appends them
     * to the given <b>$file</b> element.
     *
     * @param DOMElement $file   The parent file element
     * @param integer    $fileId Database identifier for the context file entry.
     * 
     * @return array(string=>integer) Statistics about elements and coverage.
     */
    protected function generateClasses( DOMElement $file, $fileId )
    {
        $select = $this->pdo->prepare(
            'SELECT cc.code_class_id                AS id,
                    cc.code_class_name              AS name,
                    COUNT(cm.code_method_id)        AS methods,
                    COUNT(mm.code_method_id)        AS covered,
                    mc.metrics_class_loc_executed   AS executed,
                    mc.metrics_class_loc_executable AS executable
               FROM code_class cc
         INNER JOIN metrics_class mc
                 ON mc.code_class_id = cc.code_class_id
          LEFT JOIN code_method cm
                 ON cm.code_class_id = cc.code_class_id
          LEFT JOIN metrics_method mm
                 ON mm.code_method_id = cm.code_method_id
                AND mm.metrics_method_coverage > 0
              WHERE cc.code_file_id = :file_id
           GROUP BY cc.code_class_id
           ORDER BY cc.code_class_name ASC'
        );
        $select->bindValue( ':file_id', $fileId );
        $select->execute();
        
        $infos = $select->fetchAll( PDO::FETCH_ASSOC );
        
        $select->closeCursor();
        
        $details = array(
            'classes'  =>  0,
            'methods'  =>  0,
            'covered'  =>  0,
        );
        
        foreach ( $infos as $info )
        {
            $class = $this->document->createElement( 'class' );
            $class->setAttribute( 'name', $info['name'] );
            $file->appendChild( $class );
            
            $elements = $info['methods'] + $info['executable'];
            $covered  = $info['covered'] + $info['executed'];
            
            $metrics = $this->document->createElement( 'metrics' );
            $metrics->setAttribute( 'methods', $info['methods'] );
            $metrics->setAttribute( 'coveredmethods', $info['covered'] );
            $metrics->setAttribute( 'statements', $info['executable'] );
            $metrics->setAttribute( 'coveredstatements', $info['executed'] );
            $metrics->setAttribute( 'elements', $elements );
            $metrics->setAttribute( 'coveredelements', $covered );
            $class->appendChild( $metrics );
            
            ++$details['classes'];
            $details['methods'] += $info['methods'];
            $details['covered'] += $info['covered'];
        }
        
        return $details;
    }
    
    /**
     * Generates the line sections for the given <b>$fileId</b> and appends them
     * to the given <b>$file</b> element.
     *
     * @param DOMElement $file   The parent file element
     * @param integer    $fileId Database identifier for the context file entry.
     * 
     * @return void
     */
    protected function generateLines( DOMElement $file, $fileId )
    {
        $select = $this->pdo->prepare(
            'SELECT cl.code_line_number     AS num,
                    cm.code_method_end_line AS end,
                    COUNT(co.code_line_id)  AS calls
               FROM code_class cc
         INNER JOIN code_line cl
                 ON cl.code_file_id = cc.code_file_id
          LEFT JOIN code_method cm
                 ON cm.code_class_id = cc.code_class_id
                AND cm.code_method_start_line = cl.code_line_number
          LEFT JOIN code_coverage co
                 ON co.code_line_id = cl.code_line_id
              WHERE cc.code_file_id = :file_id
                AND (cl.code_line_covered IN (-1, 1) 
                 OR cm.code_method_id IS NOT NULL)
           GROUP BY cl.code_line_id
           ORDER BY cl.code_line_number ASC'
        );
        $select->bindValue( ':file_id', $fileId );
        $select->execute();
        
        $lines = $select->fetchAll( PDO::FETCH_ASSOC );
        
        $end = 0;
        $idx = 0;
        
        foreach ( $lines as $i => $line )
        {
            if ( 0 < (int) $line['end'] )
            {
                $end = (int) $line['end'];
                $idx = $i;
            }
            else if ( $end > 0 && $line['num'] >= $end )
            {
                $end = 0;
            }
            
            if ( $end > 0 && $lines[$idx]['calls'] < (int) $line['calls'] )
            {
                $lines[$idx]['calls'] = (int) $line['calls'];
            }
        }
        
        foreach ( $lines as $info )
        {
            $type = 'stmt';
            if ( 0 < (int) $info['end'] )
            {
                $type = 'method';
            }
            
            $line = $this->document->createElement( 'line' );
            $line->setAttribute( 'num', $info['num'] );
            $line->setAttribute( 'type', $type );
            $line->setAttribute( 'count', $info['calls'] );
            
            $file->appendChild( $line );
        }
    }
}