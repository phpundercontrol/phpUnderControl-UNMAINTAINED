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
 * <...>
 *
 * @package    phpUnderControl
 * @subpackage Data
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/wiki/phpUnderControl
 */
class phpucBuildTarget
{
    /**
     * Magic properties for the build target.
     *
     * @type array<array>
     * @var array(string=>array) $properties
     */
    protected $properties = array(
        'failonerror'  =>  false,
        'executable'   =>  null,
        'targetName'   =>  null,
        'buildFile'    =>  null,
        'logerror'     =>  false,
        'argLine'      =>  null,
        'output'       =>  null,
        'dir'          =>  '${basedir}/source',
    );
    
    /**
     * The constructor takes the parent build file and the target name as 
     * arguments. 
     *
     * @param phpucBuildFile $buildFile  The parent build file object.
     * @param strinv         $targetName The build target name.
     */
    public function __construct( phpucBuildFile $buildFile, $targetName )
    {
        $this->properties['targetName'] = $targetName;
        $this->properties['buildFile']  = $buildFile;
    }
    
    /**
     * Generates the target xml content.
     *
     * @return void
     */
    public function buildXml()
    {
        $target = $this->buildFile->createElement( 'target' );
        $target->setAttribute( 'name', $this->targetName );
        
        $exec = $target->appendChild( $this->buildFile->createElement( 'exec' ) );
        $exec->setAttribute( 'executable', $this->executable );
        $exec->setAttribute( 'dir', $this->dir );
        
        if ( $this->failonerror === true )
        {
            $exec->setAttribute( 'failonerror', 'on' );
        }
        if ( $this->logerror === true )
        {
            $exec->setAttribute( 'logerror', 'on' );
        }
        if ( $this->output !== null )
        {
            $exec->setAttribute( 'output', $this->output );
        }
        
        if ( $this->argLine !== null )
        {
            $arg = $this->buildFile->createElement( 'arg' );
            $arg->setAttribute( 'line', $this->argLine );
            
            $exec->appendChild( $arg );
        }
        
        $this->buildFile->documentElement->appendChild( $target );
        
        $xpath = new DOMXPath( $this->buildFile );
        $build = $xpath->query( '/project/target[@name="build"]' )->item( 0 );
        
        if ( trim( $depends = $build->getAttribute( 'depends' ) ) !== '' )
        {
            $depends .= ',';
        }
        $build->setAttribute( 'depends', $depends . $this->targetName );
    }
    
    public function __isset( $name )
    {
        return array_key_exists( $name, $this->properties );
    }
    
    public function __get( $name )
    {
        if ( array_key_exists( $name, $this->properties ) )
        {
            return $this->properties[$name];
        }
        throw new OutOfRangeException(
            sprintf( 'Unknown or writonly property $%s.', $name )
        );
    }
    
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            case 'executable':
            case 'argLine':
            case 'output':
            case 'dir':
                $this->properties[$name] = $value;
                break;
                
            case 'failonerror':
            case 'logerror':
                if ( !is_bool( $value ) )
                {
                    throw new InvalidArgumentException(
                        sprintf( 'The property $%s must be an boolean.', $name )
                    );
                }
                $this->properties[$name] = $value;
                break;
                
            default:
                throw new OutOfRangeException(
                    sprintf( 'Unknown or readonly property $%s.', $name )
                );
                break;
        }
    }
}