<?php
/**
 * This file is part of phpUnderControl.
 *
 * Copyright (c) 2007, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @package    phpUnderControl
 * @subpackage Data
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/wiki/phpUnderControl
 */

/**
 * This class represents a single build.xml file.
 *
 * @package    phpUnderControl
 * @subpackage Data
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/wiki/phpUnderControl
 */
class phpucBuildFile extends DOMDocument
{
    /**
     * The build.xml file name.
     *
     * @type string
     * @var string $fileName
     */
    protected $fileName = '';
    
    /**
     * List of build file targets.
     *
     * @type array<phpucBuildTarget>
     * @var array(phpucBuildTarget) $targets
     */
    protected $targets = array();
    
    /**
     * The ctor takes the build file name and the project name as arguments.
     *
     * @param string $fileName    The build file name.
     * @param string $projectName An optional project name. 
     */
    public function __construct( $fileName, $projectName = null )
    {
        parent::__construct( '1.0', 'UTF-8' );
        
        $this->fileName           = $fileName;
        $this->projectName        = $projectName;
        $this->formatOutput       = true;
        $this->preserveWhiteSpace = false;
        
        if ( file_exists( $fileName ) )
        {
            $this->load( $fileName );
            $this->projectName = $this->documentElement->getAttribute( 'name' );
        }
        else
        {
            $this->initBuildFile();
        }
    }
    
    /**
     * Factory method for the build targets.
     *
     * @param string $targetName The target identifier.
     * 
     * @return phpucBuildTarget
     */
    public function createBuildTarget( $targetName )
    {
        $target = new phpucBuildTarget( $this, $targetName );
        
        $this->targets[] = $target;
        
        return $target;
    }
    
    /**
     * Writes changes to the build file.
     *
     * @return void
     */
    public function save()
    {
        foreach ( $this->targets as $target )
        {
            $target->buildXml();
        }
        
        parent::save( $this->fileName );
    }
    
    /**
     * Creates the base object structure for a new build file.
     *
     * @return void
     */
    protected function initBuildFile()
    {
        $project = $this->appendChild( $this->createElement( 'project' ) );
        $project->setAttribute( 'name', $this->projectName );
        $project->setAttribute( 'default', 'build' );
        $project->setAttribute( 'basedir', '.' );
        
        $build = $project->appendChild( $this->createElement( 'target' ) );
        $build->setAttribute( 'name', 'build' );
        $build->setAttribute( 'depends', '' );
    }
}