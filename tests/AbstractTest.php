<?php
/**
 * This file is part of phpUnderControl.
 *
 * PHP Version 5
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
 * @category  QualityAssurance
 * @package   PhpUnderControl
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

if ( defined( 'PHPUC_TEST' ) === false )
{
    define( 'PHPUC_TEST', true );
    define( 'PHPUC_TEST_DIR', dirname( __FILE__ ) . '/run' );
    define( 'PHPUC_TEST_DATA', dirname( __FILE__ ) . '/_data' );
    define( 'PHPUC_TEST_LOGS', dirname( __FILE__ ) . '/logs' );
    define( 'PHPUC_TEST_EXPECTED', dirname( __FILE__ ) . '/_expected' );
    define( 'PHPUC_TEST_LOG_FILE', PHPUC_TEST_LOGS . '/log20080118220842Lbuild.57.xml');

    if ( strpos( '@php_dir@', '@php_dir' ) === false )
    {
        define( 'PHPUC_SOURCE', '@php_dir@/phpUnderControl' );
    }
    else
    {
        define( 'PHPUC_SOURCE', realpath( dirname( __FILE__ ) . '/../src' ) );
    }
}


require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Abstract base class for phpUnderControl test cases.
 *
 * @category  QualityAssurance
 * @package   PhpUnderControl
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
abstract class phpucAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * Is the current operation system Windows?
     *
     * @type boolean
     * @var boolean $windows
     */
    public static $windows = false;

    /**
     * Clears the file stat cache.
     */
    protected function setUp()
    {
        parent::setUp();

        clearstatcache();
    }

    /**
     * Removes all test contents.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->clearTestContents();

        parent::tearDown();
    }

    /**
     * Skips the current test when the specified binary does not exist.
     *
     * @param string $binary The local binary name.
     *
     * @return void
     */
    protected function markTestSkippedWhenBinaryNotExists( $binary )
    {
        if ( $this->getBinary( $binary ) === '' )
        {
            $this->markTestSkipped( sprintf( 'No binary %s found', $binary ) );
        }
    }

    /**
     * Skips the current test when Graph ezComponent not available.
     *
     * @return void
     */
    protected function markTestSkippedWhenEzcGraphChartNotExists()
    {
        if (!class_exists('ezcGraphChart')) {

            $this->markTestSkipped('ezcGraph not installed - skipping the test.');
        }
    }

    /**
     * Returns the absolute path for the given binary.
     *
     * @param string $binary The local binary name.
     *
     * @return string
     */
    protected function getBinary( $binary )
    {
        return trim( shell_exec( 'which ' . escapeshellarg( $binary ) ) );
    }

    /**
     * Returns the name of the currently executed test method.
     *
     * @return string
     */
    protected function getTestFunctionName()
    {
        foreach ( debug_backtrace() as $traceFrame )
        {
            if ( $this->isTestFunction( $traceFrame ) )
            {
                return $traceFrame['function'];
            }
        }
        throw new ErrorException( 'Cannot detect test function.' );
    }

    /**
     * Returns <b>true</b> when the given trace frame represents a test function.
     *
     * @param array $traceFrame A single stacktrace frame.
     *
     * @return boolean
     */
    private function isTestFunction( array $traceFrame )
    {
        return ( strpos( $traceFrame['function'], 'test' ) === 0 );
    }

    /**
     * Creates a log file instance for the currently executed test method.
     *
     * @return DOMDocument
     */
    protected function createCruiseControlLog()
    {
        $xml = new DOMDocument( '1.0', 'UTF-8' );
        $xml->load( $this->getCruiseControlLogPath() );

        return $xml;
    }

    /**
     * Returns the absolute path for the actually executed test method.
     *
     * @return string
     */
    protected function getCruiseControlLogPath()
    {
        $path = $this->createCruiseControlLogPath();
        if ( file_exists( $path ) )
        {
            return $path;
        }
        throw new ErrorException(
            sprintf(
                'Cannot locate CruiseControl log for test case %s::%s().',
                get_class( $this ),
                $this->getTestFunctionName()
            )
        );
    }

    /**
     * Creates the absolute path for the actually executed test method.
     *
     * @return string
     */
    private function createCruiseControlLogPath()
    {
        return sprintf(
            '%s/%s/logs/%s.xml',
            PHPUC_TEST_DATA,
            $this->getTestDirectory(),
            $this->getTestFunctionName()
        );
    }

    private function getTestDirectory()
    {
        $reflection = new ReflectionObject( $this );

        return substr(
            dirname( $reflection->getFileName() ),
            strpos( $reflection->getFileName(), 'tests' ) + 6
        );
    }

    /**
     * Prepares the global <b>$argv</b> array.
     *
     * @param array $argv A new argument array.
     *
     * @return void
     */
    protected function prepareArgv( array $argv = null )
    {
        if ( $argv === null )
        {
            unset( $GLOBALS['argv'] );
        }
        else
        {
            // Add dummy file
            array_unshift( $argv, 'phpuc.php' );
            // Set new $argv array
            $GLOBALS['argv'] = $argv;
        }
    }

    /**
     * Prepares the cli <b>$argv</b> array and create a {@link phpucConsoleArgs}
     * instance.
     *
     * @param array $argv A new argument array.
     *
     * @return phpucConsoleArgs
     */
    protected function prepareConsoleArgs( array $argv = null )
    {
        $this->prepareArgv( $argv );

        $input = new phpucConsoleInput();
        $input->parse();

        return $input->args;
    }

    /**
     * Creates an init scripts directory under the test directory.
     *
     * @return void
     */
    protected function createInitScriptsDirectory()
    {
        $fullPath = PHPUC_TEST_DIR . '/init.d';
        mkdir( $fullPath, 0755, true );
    }

    /**
     * Creates a directory structure under the test directory.
     *
     * @param array(string) $directories Test directories.
     *
     * @return array(string)
     */
    protected function createTestDirectories( array $directories )
    {
        $fullPaths = array();

        foreach ( $directories as $directory )
        {
            // Create full testing path
            $fullPath = PHPUC_TEST_DIR . '/' . $directory;

            if ( file_exists( $fullPath ) === false )
            {
                mkdir( $fullPath, 0755, true );
            }

            $fullPaths[] = $fullPath;
        }
        return $fullPaths;
    }

    /**
     * Creates a single test file.
     *
     * @param string $filePath The test filepath.
     * @param string $content  Optional file contents.
     *
     * @return string
     */
    protected function createTestFile( $filePath, $content = '...' )
    {
        $fullPath = PHPUC_TEST_DIR . '/' . $filePath;

        file_put_contents( $fullPath, $content );

        chmod( $fullPath, 0755 );

        return $fullPath;
    }

    /**
     * Removes temporary test content recursively.
     *
     * @param string $directory The context directory.
     *
     * @return void
     */
    protected function clearTestContents( $directory = null )
    {
        if ( $directory === null )
        {
            $directory = PHPUC_TEST_DIR;
        }
        if ( !is_dir( $directory ) )
        {
            return;
        }

        $it = new DirectoryIterator( $directory );
        foreach ( $it as $entry )
        {
            if ( $entry->isDot() )
            {
                continue;
            }
            else if ( $entry->isDir() )
            {
                if ( $entry->getFilename() !== '.svn' || $directory !== PHPUC_TEST_DIR )
                {
                    $this->clearTestContents( $entry->getPathname() );
                    rmdir( $entry->getPathname() );
                }
            }
            else if ( $entry->isFile() )
            {
                unlink( $entry->getPathname() );
            }
        }
    }

    /**
     * Initializes the test environment.
     *
     * @return void
     */
    public static function init()
    {
        // Load phpUnderControl base class
        include_once PHPUC_SOURCE . '/PhpUnderControl.php';
        include_once PHPUC_SOURCE . '/Util/Autoloader.php';

        // Register autoload
        $autoloader = new phpucAutoloader();

        spl_autoload_register( array( $autoloader, 'autoload' ) );

        // Load ezcBase class
        if ( file_exists( PHPUC_EZC_BASE ) )
        {
            include_once PHPUC_EZC_BASE;

            spl_autoload_register( array( 'ezcBase', 'autoload' ) );
        }

        include_once dirname( __FILE__ ) . '/ConsoleOutputBuffer.php';

        phpucConsoleOutput::set( new phpucConsoleOutputBuffer() );

        if ( !is_dir( PHPUC_TEST_DIR ) )
        {
            mkdir( PHPUC_TEST_DIR );
        }

        self::$windows = phpucFileUtil::getOS() === phpucFileUtil::OS_WINDOWS;
    }

    /**
     * Sets up vfsStream if available
     *
     * Run as part of a test to register vfsStream and create root directory.
     * If vfsStream is not installed the test will be skipped.
     * Use {@link phpucAbstractTest::createVfsFile()} to create a virtual file
     * or {@link phpucAbstractTest::createVfsDirectories()} to create
     * virtual directories.
     *
     * @return bool
     */
    protected function setUpVfsStream()
    {

        @include_once 'vfsStream/vfsStream.php';
        if ( !class_exists( 'vfsStreamWrapper' ) ) {

            $this->markTestSkipped( 'vfsStream is not available - skipping' );
            $bolDone = false;

        } else {

            vfsStreamWrapper::register();
            vfsStreamWrapper::setRoot( vfsStream::newDirectory( '/' ) );
            $bolDone = true;
        }

        return $bolDone;
    }

    /**
     * Creates a vitrual file using vfsStream plugin
     *
     * @see phpucAbstractTest::setUpVfsStream()
     *
     * @param string $filePath Path to the virtual file
     * @param string $content  Contents of the file
     *
     * @return vfsStreamFile
     */
    protected function createVfsFile( $filePath, $content = '...' )
    {
        return vfsStream::newFile( $filePath )
                        ->withContent( $content )
                        ->at( vfsStreamWrapper::getRoot() );
    }

    /**
     * Creates virtual directories using vfsStream plugin
     *
     * @see phpucAbstractTest::setUpVfsStream()
     *
     * @param array $directories List of directories
     *
     * @return void
     */
    protected function createVfsDirectories( array $directories )
    {
        foreach ( $directories as $directory )
        {
            vfsStream::newDirectory( $directory )
                     ->at( vfsStreamWrapper::getRoot() );
        }
    }
}

phpucAbstractTest::init();
