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
class phpucConfigProject
{
    protected $properties = array(
        'element'      =>  null,
        'anthome'      =>  null,
        'interval'     =>  null,
        'configFile'   =>  null,
        'projectName'  =>  null,
    );
    
    public function __construct( phpucConfigFile $configFile, $projectName )
    {
        $this->properties['configFile']  = $configFile;
        $this->properties['projectName'] = $projectName;
        
        $xpath  = new DOMXPath( $configFile );
        $result = $xpath->query( "/cruisecontrol/project[@name='{$projectName}']" );
        
        if ( $result->length === 0 )
        {
            $project = new DOMDocument();
            $project->load( PHPUC_DATA_DIR . '/template/project.xml' );
            
            $element = $configFile->importNode( $project->documentElement, true );
            $element->setAttribute( 'name', $projectName );
            $configFile->documentElement->appendChild( $element );
            
            $this->properties['element'] = $element;
        }
        else
        {
            $this->properties['element'] = $result->item( 0 );
        }
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
            case 'anthome':
                $this->properties[$name] = $value;
                break;
                
            case 'interval':
                if ( !is_integer( $value ) || $value < 0 )
                {
                    throw new InvalidArgumentException(
                        sprintf( 'Property $%s must be a positive integer.', $name )
                    );
                }
                $this->properties[$name] = $value;
                return;
            
            default:
                throw new OutOfRangeException(
                    sprintf( 'Unknown or readonly property $%s.', $name )
                );
                break;
        }
    }
    
    public function buildXml()
    {
        $xpath = new DOMXPath( $this->configFile );
        $query = "/cruisecontrol/project[@name='{$this->projectName}']/schedule";
        
        $schedule = $xpath->query( $query )->item( 0 );
        $schedule->setAttribute( 'interval', $this->interval );
        
        $ant = $xpath->query( "{$query}/ant" )->item( 0 );
        $ant->setAttribute( 'anthome', $this->anthome );
    }
}