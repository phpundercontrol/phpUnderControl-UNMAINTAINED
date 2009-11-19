<?php
/**
 * This file is part of phpUnderControl.
 * 
 * PHP Version 5.2.0
 *
 * Copyright (c) 2007-2009, Manuel Pichler <mapi@phpundercontrol.org>.
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
 * @copyright 2007-2009 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

/**
 * This task generates the code browser.
 *
 * @category  QualityAssurance
 * @package   Tasks
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2009 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://www.phpundercontrol.org/
 */
class phpucCodeBrowserTask extends phpucAbstractPearTask implements phpucConsoleExtensionI
{
    /**
     * Performs the primary task and adds an execute publisher to the config.xml.
     *
     * @return void
     */
    public function execute()
    {
        $out = phpucConsoleOutput::get();
        $out->writeLine( 'Performing PHP_CodeBrowser task.' );

        $installDir  = $this->args->getArgument( 'cc-install-dir' );
        $projectName = $this->args->getOption( 'project-name' );
        $projectPath = sprintf(
            '%s/projects/%s',
            $this->args->getArgument( 'cc-install-dir' ),
            $projectName
        );

        $out->startList();
        $out->writeListItem(
            'Creating browser dir: project/{1}/build/php-code-browser', $projectName
        );

        mkdir( $projectPath . '/build/php-code-browser', 0755, true );

        $out->writeListItem(
            'Modifying build file: project/{1}/build.xml', $projectName
        );

        $buildFile = new phpucBuildFile( $projectPath . '/build.xml', $projectName );

        $buildTarget = $buildFile->createBuildTarget( 'php-code-browser' );

        $buildTarget->executable = $this->executable;
        $buildTarget->argLine    = sprintf(
            '--log ${basedir}/build/logs ' .
            '--source %s ' .
            '--output ${basedir}/build/php-code-browser',
            $this->args->getOption( 'source-dir' )
        );

        $buildFile->store();

        $out->writeListItem( 'Modifying config file: config.xml' );

        $configFile    = new phpucConfigFile( $installDir . '/config.xml' );
        $configProject = $configFile->getProject( $projectName );
        $publisher     = $configProject->createArtifactsPublisher();

        $publisher->dir          = 'projects/${project.name}/build/php-code-browser';
        $publisher->subdirectory = 'php-code-browser';

        if ( $this->artifacts )
        {
            $publisher->dest = 'artifacts/${project.name}';
        }
        else
        {
            $publisher->dest = 'logs/${project.name}';
        }

        $configFile->store();

        $out->writeLine();
    }

    /**
     * Callback method that registers a command extension.
     *
     * @param phpucConsoleInputDefinition $def
     *        The input definition container.
     * @param phpucConsoleCommandI  $command
     *        The context cli command instance.
     *
     * @return void
     */
    public function registerCommandExtension( phpucConsoleInputDefinition $def,
                                              phpucConsoleCommandI $command )
    {
        parent::registerCommandExtension( $def, $command );

        $def->addOption(
            $command->getCommandId(),
            'b',
            'without-code-browser',
            'Disable PHP CodeBrowser support.',
            false
        );
        
        if ( !$def->hasOption( $command->getCommandId(), 'source-dir' ) )
        {
            $def->addOption(
                $command->getCommandId(),
                's',
                'source-dir',
                'The source directory in the project.',
                true,
                'src',
                true
            );
        }
    }

    /**
     * Must return the name of the used cli tool.
     *
     * @return string
     */
    protected function getCliToolName()
    {
        return 'phpcb';
    }
}