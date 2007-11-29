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
 * @subpackage Tasks
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/wiki/phpUnderControl
 */

/**
 * Task for the cruise control directory.
 *
 * @package    phpUnderControl
 * @subpackage Tasks
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/wiki/phpUnderControl
 * 
 * @property string $ccInstallDir The cruise control installation directory.
 * @property string $projectName  The name of the example project.
 */
class phpucCruiseControlTask extends phpucAbstractTask
{
    /**
     * List of additional directories for phpUnderControl.
     *
     * @type array<string>
     * @var array(string)
     */
    private $directories = array(
        'images/php-under-control',
        'js'
    );
    
    /**
     * Validates the required constrains.
     *
     * @return void
     */
    public function validate()
    {
        $installDir = $this->args->getArgument( 'cc-install-dir' );
        
        // Check for a valid directory.
        if ( is_dir( $installDir ) === false )
        {
            throw new phpucValidateException(
                sprintf(
                    'The specified CruiseControl directory "%s" doesn\'t exist.',
                    $installDir
                )
            );
        }
        // List of required sub directories.
        $subdirs = array(
            '/webapps/cruisecontrol',
            '/webapps/cruisecontrol/css',
            '/webapps/cruisecontrol/xsl',
        );

        foreach ( $subdirs as $subdir )
        {
            // Check for a valid directory.
            if ( is_dir( $installDir . $subdir ) === false )
            {
                throw new phpucValidateException(
                    sprintf(
                        'Missing required CruiseControl sub directory "%s".',
                        $subdir
                    )
                );
            }            
        }
    }
    
    /**
     * Creates the some directories in the CruiseControl folder. 
     *
     * @return void
     */
    public function execute()
    {
        echo 'Performing CruiseControl task.' . PHP_EOL;
        
        // Get root directory.
        $installDir = sprintf(
            '%s/webapps/cruisecontrol/',
            $this->args->getArgument( 'cc-install-dir' )
        );
        
        foreach ( $this->directories as $index => $directory )
        {
            // Skip for existing directories.
            if ( is_dir( $installDir . $directory ) )
            {
                continue;
            }
            
            printf( 
                ' % 2d. Creating directory "webapps/cruisecontrol/%s".%s',
                ( $index + 1 ),
                $directory,
                PHP_EOL
            );
            mkdir( $installDir . $directory );
        }

        echo PHP_EOL;
    }
}