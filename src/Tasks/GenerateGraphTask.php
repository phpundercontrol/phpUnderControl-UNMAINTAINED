<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.4
 *
 * Copyright (c) 2007-2008, Manuel Pichler <mapi@phpundercontrol.org>.
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
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

/**
 * Generates a set of graphs for each project build.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucGenerateGraphTask extends phpucAbstractTask
{
    /**
     * The log directory.
     *
     * @type string
     * @var string $logDir
     */
    protected $logDir = null;
    
    /**
     * Internal used debug property. 
     * 
     * If this is set to <b>true</b> all graphs are regenerate on every call.
     *
     * @type boolean
     * @var boolean $debug
     */
    private $debug = true;
    
    /**
     * Validates that the project log directory exists.
     *
     * @return void
     * 
     * @throws phpucValidateException If the validation fails.
     */
    public function validate()
    {
        $logdir = $this->args->getArgument( 'project-log-dir' );
        
        if ( trim( $this->logDir = realpath( $logdir ) ) === '' )
        {
            throw new phpucValidateException(
                "The specified log directory '{$logdir}' doesn't exist."
            );
        }
    }
    
    /**
     * Generates a set of charts for each project build(if required).
     *
     * @return void
     */
    public function execute()
    {
        $inputLoader  = new phpucInputLoader();
        $chartFactory = new phpucChartFactory();
        
        $logFiles = new phpucLogFileIterator( $this->logDir );
        
        foreach ( $logFiles as $logFile )
        {
            $xpath = new DOMXPath( $logFile );
    
            $outputDir = "{$this->logDir}/{$logFile->timestamp}/graph";

            foreach ( $inputLoader as $input )
            {
                $input->processLog( $xpath );
                
                if ( !is_dir( $outputDir ) )
                {
                    mkdir( $outputDir, 0755, true );
                }
                
                $fileName = "{$outputDir}/{$input->fileName}.svg";
                if ( !file_exists( $fileName ) || $this->debug )
                {
                    $chart = $chartFactory->createChart( $input );
                    $chart->render( 390, 250, $fileName );
                }
            }
        }
    }
}