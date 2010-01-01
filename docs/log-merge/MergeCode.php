<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.0
 *
 * Copyright (c) 2007-2010, Manuel Pichler <mapi@phpundercontrol.org>.
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
 * @package    PhpUnderControl
 * @subpackage Documentation
 * @author     Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright  2007-2010 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id: PhpUnderControl.php 2631 2008-03-18 15:23:55Z mapi $
 * @link       http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/MergeCodeHelper.php';

/**
 * Documentation/Example/Test class for environment specific code.
 *
 * @category   QualityAssurance
 * @package    PhpUnderControl
 * @subpackage Documentation
 * @author     Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright  2007-2010 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpundercontrol.org/
 */
class phpucMergeCode
{
    /**
     * Return used sapi.
     */
    const SAPI = 1;
    
    /**
     * Return used php version
     */
    const VERSION = 2;
    
    /**
     * Return tested class name.
     */
    const NAME = 3;
    
    /**
     * Return normal token.
     */
    const NORMAL = 1;
    
    /**
     * Return reverse token.
     */
    const REVERSE = 2;
    
    /**
     * Return base64 token.
     */
    const BASE64 = 3;
    
    /**
     * Returns a php version specific token.
     *
     * @return string
     */
    public function versionSpecific()
    {
        $helper = new phpucMergeCodeHelper();
        
        $value = null;
        if (version_compare(phpversion(), '5.2.0') === 0)
        {
            $value = $helper->version520();
        }
        else if (version_compare(phpversion(), '5.2.5') === 0)
        {
            $value = $helper->version525();
        }
        else
        {
            $value = $helper->versionAny();
        }
        return $value;
    }
    
    /**
     * Returns a not version specific token.
     * 
     * @param integer $what What token?
     * @param integer $mode Reverse, normal or base64 token string?
     *
     * @return string
     */
    public function notVersionSpecific($what, $mode)
    {
        $value = null;
        switch ( $what )
        {
            case self::SAPI:
                if ( $mode === self::REVERSE )
                {
                    $value = strrev( php_sapi_name() );
                }
                else if ( $mode === self::BASE64 )
                {
                    $value = base64_encode( php_sapi_name() );
                }
                else
                {
                    $value = php_sapi_name();
                }
                break;
                
            case self::VERSION:
                if ($mode === self::REVERSE)
                {
                    $value = strrev( phpversion() );
                }
                else if ( $mode === self::BASE64 )
                {
                    $value = base64_encode( phpversion() );
                }
                else if ( $mode === self::NORMAL )
                {
                    $value = phpversion();
                }
                break;
                
            case self::NAME:
                switch ($mode)
                {
                    case self::REVERSE:
                        $value = strrev( __CLASS__ );
                        break;
                        
                    case self::NORMAL:
                        $value = __CLASS__;
                        break;
                        
                    default:
                        $value = base64_encode( __CLASS__ );
                        break;
                }
        }
        return $value;
    }
    
    /**
     * Adds two values.
     *
     * @param integer $x Test value one.
     * @param integer $y Test value two.
     * 
     * @return integer
     */
    public function calculate( $x, $y )
    {
        return ( $x + $y );
    }
}