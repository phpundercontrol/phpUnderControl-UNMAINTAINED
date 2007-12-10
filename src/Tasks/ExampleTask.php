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
 * <...>
 *
 * @package    phpUnderControl
 * @subpackage Tasks
 * @author     Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright  2007 Manuel Pichler. All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/wiki/phpUnderControl
 * 
 * @property-read boolean $metrics  Enable metrics support?
 * @property-read boolean $coverage Enable coverage support?
 */
class phpucExampleTask extends phpucAbstractTask
{
    /**
     * List of example files.
     *
     * @type array<string>
     * @var array(string) $fileNames
     */
    protected $fileNames = array(
        'src/Math.php',
        'tests/MathTest.php',
    );
    
    /**
     * Creates a new example project with test files.
     *
     * @return void
     * @throws phpucExecuteException If the execution fails.
     */
    public function execute()
    {
        $installDir  = $this->args->getArgument( 'cc-install-dir' );
        $projectName = $this->args->getOption( 'project-name' );
        $projectPath = sprintf( '%s/projects/%s', $installDir, $projectName );
        
        echo 'Performing example task.' . PHP_EOL;
        
        printf( '  1. Creating source directory:  project/%s/source/src%s', $projectName, PHP_EOL );
        mkdir( $projectPath . '/source/src' );
        
        printf( '  2. Creating tests directory:   project/%s/source/tests%s', $projectName, PHP_EOL );
        mkdir( $projectPath . '/source/tests' );
        
        printf( '  3. Creating source class:      project/%s/source/src/Math.php%s', $projectName, PHP_EOL );
        file_put_contents(
            $projectPath . '/source/src/Math.php',
            file_get_contents( PHPUC_DATA_DIR . '/example/src/Math.php' )
        );
        
        printf( '  4. Creating test class:        project/%s/source/tests/MathTest.php%s', $projectName, PHP_EOL );
        file_put_contents(
            $projectPath . '/source/tests/MathTest.php',
            file_get_contents( PHPUC_DATA_DIR . '/example/tests/MathTest.php' )
        );
        
        echo '  5. Modifying config file:      config.xml' . PHP_EOL;
        
        $configXml                     = new DOMDocument();
        $configXml->preserveWhiteSpace = false;
        $configXml->load( $installDir . '/config.xml' );
        
        $alwaysbuild = $configXml->createElement( 'alwaysbuild' );
        
        $xpath         = new DOMXPath( $configXml );
        $modifications = $xpath->query( 
            sprintf( 
                '/cruisecontrol/project[@name="%s"]/modificationset', $projectName
            )
        )->item( 0 );
        $modifications->appendChild( $alwaysbuild );
        
        $configXml->formatOutput = true;
        $configXml->save( $installDir . '/config.xml' );
        
        echo PHP_EOL;
    }
}