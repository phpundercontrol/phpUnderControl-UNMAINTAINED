<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.4
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
 * @package    Graph
 * @subpackage Input
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007-2008 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpundercontrol.org/
 */

/**
 * ...
 *
 * @category   QualityAssurance
 * @package    Graph
 * @subpackage Input
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007-2008 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpundercontrol.org/
 * 
 * @property-read string               $title      The human readable data title.
 * @property-read integer              $type       The output chart type.
 * @property-read array(string=>mixed) $data       The extracted log file data.
 * @property-read string               $yAxisLabel An optional label for the y-axis.
 * @property-read string               $xAxisLabel An optional label for the x-axis.
 */
abstract class phpucAbstractInput
{
    /**
     * Identifies an input implementation for pie charts.
     */
    const TYPE_PIE = 0;
    
    /**
     * Identifies an input implementation for line charts.
     */
    const TYPE_LINE = 1;
    
    /**
     * This identifies the sum mode where all found records are summed up.
     */
    const MODE_SUM = 0;
    
    /**
     * This identifier the count mode which counts the number of matching records.
     */
    const MODE_COUNT = 1;
    
    /**
     * The human readable input type title.
     *
     * @type string
     * @var string $title
     */
    protected $title = null;
    
    /**
     * An optional label for the y-axis.
     *
     * @type string
     * @var string $yAxisLabel
     */
    protected $yAxisLabel = '';
    
    /**
     * An optional label for the x-axis.
     *
     * @type string
     * @var string $xAxisLabel
     */
    protected $xAxisLabel = '';
    
    /**
     * The output image file name.
     *
     * @type string
     * @var string $fileName
     */
    private $fileName = null;
    
    /**
     * The output chart type.
     * 
     * @type integer
     * @var integer $type
     */
    private $type = null;
    
    /**
     * The extracted log file data.
     *
     * @type array<mixed>
     * @var array(string=>mixed) $data
     */
    private $data = array();
    
    /**
     * List of input rules.
     *
     * @type array<phpucInputRule>
     * @var array(phpucInputRule) $rules
     */
    private $rules = array();
    
    /**
     * Constructs a new input type implementation.
     *
     * @param string  $title    The human readable input type title.
     * @param string  $fileName The output image file name.
     * @param integer $type     The output chart type.
     * 
     * @throws InvalidArgumentException If the given type is unknown.
     */
    public function __construct( $title, $fileName, $type )
    {
        $this->title    = $title;
        $this->fileName = $fileName;
        
        if ( !in_array( $type, array( self::TYPE_PIE, self::TYPE_LINE ) ) )
        {
            throw new InvalidArgumentException( 'Invalid input type given.' );
        }
        $this->type = $type;
    }
    
    /**
     * Magic property getter method.
     *
     * @param string $name The property name.
     * 
     * @return mixed
     * @throws OutOfRangeException If the requested property doesn't exist or
     *         is writonly.
     * @ignore 
     */
    public function __get( $name )
    {
        switch ( $name )
        {
            case 'data':
            case 'type':
            case 'title':
            case 'fileName':
            case 'yAxisLabel':
            case 'xAxisLabel':
                return $this->$name;
                
            default:
                throw new OutOfRangeException(
                    sprintf( 'Unknown or writonly property $%s.', $name )
                );
        }
    }
    
    /**
     * Magic property setter method.
     *
     * @param string $name  The property name.
     * @param mixed  $value The property value.
     * 
     * @return void
     * @throws OutOfRangeException If the requested property doesn't exist or
     *         is readonly.
     * @throws InvalidArgumentException If the given value has an unexpected 
     *         format or an invalid data type.
     * @ignore 
     */
    public function __set( $name, $value )
    {
        throw new OutOfRangeException(
            sprintf( 'Unknown or readonly property $%s.', $name )
        );
    }
    
    public function processLog( DOMXPath $xpath )
    {
        foreach ( $this->rules as $rule )
        {
            $nodeList = $xpath->query( $rule->xpath );
            
            switch ( $rule->mode )
            {
                case self::MODE_COUNT:
                    $data = $this->processLogCount( $nodeList );
                    break;
                    
                case self::MODE_SUM:
                    $data = $this->processLogSum( $nodeList );
                    break;
            }
            
            $this->data[$rule->label][] = $data;
        }
    }
    
    protected function processLogSum( DOMNodeList $nodeList )
    {
        $sum = 0;
        foreach ( $nodeList as $node )
        {
            $sum += (int) $node->nodeValue;
        }
        return $sum;
    }
    
    protected function processLogCount( DOMNodeList $nodeList )
    {
        return $nodeList->length;
    }
    
    /**
     * Adds a xpath rule to this input object. 
     *
     * @param phpucInputRule $rule The rule instance
     */
    protected function addRule( phpucInputRule $rule )
    {
        $this->rules[] = $rule;
        
        $this->data[$rule->label] = array();
    }
}