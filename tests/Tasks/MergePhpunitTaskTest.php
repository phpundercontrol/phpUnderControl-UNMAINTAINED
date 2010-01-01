<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.0
 *
 * Copyright (c) 2007-2010, Manuel Pichler <mapi@manuel-pichler.de>.
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
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

require_once dirname( __FILE__ ) . '/AbstractTaskTest.php';

/**
 * Test case for the phpunit log merge task.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucMergePhpunitTaskTest extends phpucAbstractTaskTest
{
    public function testInputOptionIsMandatoryFail()
    {
        $this->setExpectedException(
            'phpucConsoleException',
            "The option '--input' is marked as mandatory and not set."
        );
        
        $args = $this->prepareConsoleArgs(
            array(
                'merge-phpunit', 
                '--output', 'out.xml',
            )
        );        
    }
    
    public function testInputOptionRequiresValueFail()
    {
        $this->setExpectedException(
            'phpucConsoleException',
            "The option '--input' requires an additional value."
        );
        
        $args = $this->prepareConsoleArgs(
            array(
                'merge-phpunit', 
                '--input', 
                '--output', 'out.xml',
            )
        );    
    }
    public function testOutputOptionIsMandatoryFail()
    {
        $this->setExpectedException(
            'phpucConsoleException',
            "The option '--output' is marked as mandatory and not set."
        );
        
        $args = $this->prepareConsoleArgs(
            array(
                'merge-phpunit', 
                '--input', 'in.xml',
            )
        );        
    }
    
    public function testOutputOptionRequiresValueFail()
    {
        $this->setExpectedException(
            'phpucConsoleException',
            "The option '--output' requires an additional value."
        );
        
        $args = $this->prepareConsoleArgs(
            array(
                'merge-phpunit', 
                '--input', 'in.xml', 
                '--output'
            )
        );        
    }
    
    public function testBuildOptionRequiresValueFail()
    {
        $this->setExpectedException(
            'phpucConsoleException',
            "The option '--builds' requires an additional value."
        );
        
        $args = $this->prepareConsoleArgs(
            array(
                'merge-phpunit', 
                '--input', 'in.xml', 
                '--output', 'out.xml',
                '--builds'
            )
        );        
    }

    public function testInputOptionWithInvalidLogFileFail()
    {
        $args = $this->prepareConsoleArgs(
            array(
                'merge-phpunit', 
                '--input', 'log.xml',
                '--output', 'out.xml',
            )
        );
        
        $task = new phpucMergePhpunitTask();
        $task->setConsoleArgs($args);

        $this->setExpectedException(
            'phpucValidateException',
            'The specified --input "log.xml" doesn\'t exist.'
        );
        
        $task->validate();
    }

    public function testInputAndBuildsOptionNumberMustMatchFail()
    {
        $args = $this->prepareConsoleArgs(
            array(
                'merge-phpunit', 
                '--input', PHPUC_TEST_DATA . '/phpunit/php525/log.xml',
                '--builds', 'a,b', 
                '--output', 'out.xml',
            )
        );
        
        $task = new phpucMergePhpunitTask();
        $task->setConsoleArgs($args);

        $this->setExpectedException(
            'phpucValidateException',
            'Number of build identifiers "2" and files "1" doesn\'t match.'
        );
        
        $task->validate();
    }
    
    public function testCreateOutputDirectoryFail()
    {
        $directory = PHPUC_TEST_DIR . '/output';
        touch( $directory );
        
        $this->assertFileExists( $directory );
        
        $args = $this->prepareConsoleArgs(
            array(
                'merge-phpunit', 
                '--input', PHPUC_TEST_DATA . '/phpunit/php525/log.xml',
                '--output', PHPUC_TEST_DIR . '/output/out.xml',
            )
        );
        
        $task = new phpucMergePhpunitTask();
        $task->setConsoleArgs($args);
        
        $this->setExpectedException(
            'phpucValidateException', 
            sprintf( 'Cannot create output directory "%s".', $directory )
        );
        
        $task->validate();
    }
    
    public function testMergeLogFilesFromDifferentDirectoriesWithoutCustomBuildIds()
    {
        $input  = sprintf(
            '%s/phpunit/php520/log.xml,' .
            '%s/phpunit/php525/log.xml,' .
            '%s/phpunit/php526RC2/log.xml',
            PHPUC_TEST_DATA,
            PHPUC_TEST_DATA,
            PHPUC_TEST_DATA
        );
        
        $expected = sprintf( 
            '%s/phpunit/expected/log-files-without-build-ids.xml', 
            PHPUC_TEST_DATA 
        );
        
        $this->doTestMergeLogFiles( $input, $expected );
    }
    
    public function testMergeLogFilesFromDifferentDirectoriesWithBuildIds()
    {
        $input  = sprintf(
            '%s/phpunit/php520/log.xml,' .
            '%s/phpunit/php525/log.xml,' .
            '%s/phpunit/php526RC2/log.xml',
            PHPUC_TEST_DATA,
            PHPUC_TEST_DATA,
            PHPUC_TEST_DATA
        );
        
        $expected = sprintf( 
            '%s/phpunit/expected/log-files-with-build-ids.xml', 
            PHPUC_TEST_DATA 
        );
        
        $builds = 'php-5.2.0,php-5.2.5,php-5.2.6';
        
        $this->doTestMergeLogFiles( $input, $expected, $builds );
    }
    
    public function testMergeLogFilesFromDifferentDirectoriesWithBuildIdsAndOneMissingFileFail()
    {
        $input  = sprintf(
            '%s/phpunit/php520/log.xml,' .
            '%s/phpunit/php525/log.xml,' .
            '%s/phpunit/php700/log.xml',
            PHPUC_TEST_DATA,
            PHPUC_TEST_DATA,
            PHPUC_TEST_DATA
        );

        $builds = 'php-5.2.0,php-5.2.5,php-7.0.0';
        
        $args = $this->prepareConsoleArgs(
            array(
                'merge-phpunit', 
                '--input', $input,
                '--builds', $builds,
                '--output', PHPUC_TEST_DIR . '/output/out.xml',
            )
        );
        
        $task = new phpucMergePhpunitTask();
        $task->setConsoleArgs( $args );
        
        $this->setExpectedException(
            'phpucTaskException', 
             sprintf(
                'The specified --input "%s/phpunit/php700/log.xml" doesn\'t exist.', 
                PHPUC_TEST_DATA
             )
        );
        
        $task->validate();
        $task->execute();
    }
    
    
    public function testMergeLogFilesWithMissingLogFileCreatesFileThrowRuntimeException()
    {
        $input  = sprintf(
            '%s/phpunit/log-dir-passed-tests/php-5.2.0.xml,' .
            '%s/phpunit/log-dir-passed-tests/php-5.2.5.xml,' .
            '%s/phpunit/log-dir-passed-tests/php-5.2.6.xml',
            PHPUC_TEST_DATA,
            PHPUC_TEST_DATA,
            PHPUC_TEST_DATA
        );
        
        $output = sprintf( '%s/output/out.xml', PHPUC_TEST_DIR );
        $builds = 'php-5.2.0,php-5.2.5,php-5.2.6';
        
        $args = $this->prepareConsoleArgs(
            array(
                'merge-phpunit', 
                '--input', $input,
                '--builds', $builds,
                '--output', $output,
            )
        );
        
        $task = new phpucMergePhpunitTask();
        $task->setConsoleArgs( $args );
        $task->validate();
        
        try
        {
            $task->execute();
            $this->fail( 'phpucTaskException expected.' );
        } 
        catch ( phpucTaskException $e )
        {
            $message = sprintf(
                'The specified --input "%s/phpunit/log-dir-passed-tests/php-5.2.5.xml" doesn\'t exist.', 
                PHPUC_TEST_DATA
            );
            $this->assertEquals( $message, $e->getMessage() );
        }
        
        $this->assertFileExists( $output );
        
        $file = sprintf(
            '%s/phpunit/expected/log-files-with-build-ids-missing-log-file.xml', 
            PHPUC_TEST_DATA
        );
        $this->assertXmlFileEqualsXmlFile( $file, $output );
    }
    
    public function testMergeLogFilesFromSingleDirectoryWithoutCustomBuildIds()
    {
        $input    = sprintf( '%s/phpunit/log-dir-failed-tests', PHPUC_TEST_DATA );
        $expected = sprintf( 
            '%s/phpunit/expected/log-dir-without-build-ids.xml', 
            PHPUC_TEST_DATA 
        );
        
        $this->doTestMergeLogFiles( $input, $expected );
    }
    
    public function testMergeLogFilesFromSingleDirectoryWithCustomBuildIds()
    {
        $input    = sprintf( '%s/phpunit/log-dir-failed-tests', PHPUC_TEST_DATA );
        $builds   = 'php-5.2.0,php-5.2.5,php-5.2.6';
        $expected = sprintf( 
            '%s/phpunit/expected/log-dir-with-build-ids.xml', 
            PHPUC_TEST_DATA 
        );
        
        $this->doTestMergeLogFiles( $input, $expected, $builds );
    }
    
    protected function doTestMergeLogFiles( $input, $expected, $buildIds = null )
    {
        $output = sprintf( '%s/out.xml', PHPUC_TEST_DIR );
        
        $argv = array(
            'merge-phpunit', 
            '--input', $input, 
            '--output', $output
        );
        if ( $buildIds !== null )
        {
            $argv[] = '--builds';
            $argv[] = $buildIds;
        }
        
        $args = $this->prepareConsoleArgs( $argv );
        
        $this->assertFileNotExists( $output );
        
        $task = new phpucMergePhpunitTask();
        $task->setConsoleArgs( $args );
        $task->validate();
        
        try
        {
            $task->execute();
        }
        catch (phpucTaskException $e) 
        {
            $this->assertEquals(
                'There are errors or failures in the generated test suite.',
                $e->getMessage()
            );
        }
        
        $this->assertFileExists( $output );
        $this->assertXmlFileEqualsXmlFile( $expected, $output );
    }
}