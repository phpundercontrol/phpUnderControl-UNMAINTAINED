============================
Documentation - Command line
============================

.. contents:: Table of Contents
   :depth: 3

Command Line Interface
======================

The phpUnderControl package comes with a set of cli commands that aim to
provide an easy way to work with CruiseControl. In the following sections you
will find detailed descriptions for each command. All examples expect that you
have changed your current working dir to the phpUnderControl/bin directory. 

The Install/Update Command
--------------------------

To patch your CruiseControl installation you must execute the *install* command.
Even if the name of this command is *install*, this command can be used to 
perform an *update* on an existing installation. ::

    [mapi@frodo.xplib.de bin]$ ./phpuc install /opt/cruisecontrol
    Performing CruiseControl task.

    Performing modify file task.
       1. Modifying file  "/index.jsp".
       2. Modifying file  "/main.jsp".
       3. Modifying file  "/metrics.jsp".
       4. Modifying file  "/xsl/buildresults.xsl".
       5. Modifying file  "/xsl/errors.xsl".
       6. Modifying file  "/xsl/header.xsl".
       7. Modifying file  "/xsl/modifications.xsl".

    Performing create file task.
       1. Creating file "/footer.jsp".
       2. Creating file "/header.jsp".
       3. Creating file "/phpcs.jsp".
       4. Creating file "/phpunit.jsp".
       5. Creating file "/phpunit-pmd.jsp".
       6. Creating file "/css/php-under-control.css".
       7. Creating file "/css/SyntaxHighlighter.css".
       8. Creating file "/images/php-under-control/error.png".
       9. Creating file "/images/php-under-control/failed.png".
      10. Creating file "/images/php-under-control/header-center.png".
      11. Creating file "/images/php-under-control/header-left-logo.png".
      12. Creating file "/images/php-under-control/info.png".
      13. Creating file "/images/php-under-control/skipped.png".
      14. Creating file "/images/php-under-control/success.png".
      15. Creating file "/images/php-under-control/tab-active.png".
      16. Creating file "/images/php-under-control/tab-inactive.png".
      17. Creating file "/images/php-under-control/warning.png".
      18. Creating file "/js/shBrushPhp.js".
      19. Creating file "/js/shCore.js".
      20. Creating file "/xsl/phpcs.xsl".
      21. Creating file "/xsl/phpcs-details.xsl".
      22. Creating file "/xsl/phpdoc.xsl".
      23. Creating file "/xsl/phphelper.xsl".
      24. Creating file "/xsl/phpunit.xsl".
      25. Creating file "/xsl/phpunit-details.xsl".
      26. Creating file "/xsl/phpunit-pmd.xsl".
      27. Creating file "/xsl/phpunit-pmd-details.xsl".

Arguments
`````````

- This task needs the CruiseControl installation directory as argument.  


The Example Command
-------------------

For a quick start with CruiseControl, phpUnderControl comes with a small example
project. Just enter "phpuc.php example /path/to/cruisecontrol". ::

    [mapi@frodo.xplib.de bin]$ ./phpuc.php example /opt/cruisecontrol
    Performing project task.
      1. Creating project directory: projects/php-under-control
      2. Creating source directory:  projects/php-under-control/source
      3. Creating build directory:   projects/php-under-control/build
      4. Creating log directory:     projects/php-under-control/build/logs
      5. Creating build file:        projects/php-under-control/build.xml
      6. Creating backup of file:    config.xml.orig
      7. Searching ant directory
      8. Modifying project file:     config.xml

    Performing example task.
      1. Creating source directory:  project/php-under-control/source/src
      2. Creating tests directory:   project/php-under-control/source/tests
      3. Creating source class:      project/php-under-control/source/src/Math.php
      4. Creating test class:        project/php-under-control/source/tests/MathTest.php
      5. Modifying config file:      config.xml

    Performing PhpDocumentor task.
      1. Creating api documentation dir: project/php-under-control/build/api
      2. Modifying build file:           project/php-under-control/build.xml
      3. Modifying config file:          config.xml

    Performing PHP_CodeSniffer task.
      1. Modifying build file: project/php-under-control/build.xml

    Performing PHPUnit task.
      1. Creating coverage dir: project/php-under-control/build/coverage
      2. Modifying build file:  project/php-under-control/build.xml
      3. Modifying config file: config.xml

 

Options
```````
- --pear-executables-dir

  With this option you can configure the pear executable directory. This
  parameter is optional if your $PATH environment variable points to the PEAR
  executable directory. If phpUnderControl cannot find the required PEAR
  executable under any of the $PATH locations the command will fail.
  
- --without-code-sniffer
  
  Suppresses the PHP_CodeSniffer task for the example project.
  
- --without-phpunit

  Suppresses the PHPUnit task for the example project.
  
- --without-php-documentor

  Suppresses the PhpDocumentor task for the example project.
  
- --project-name
  
  An optional name for the example project. The default value is
  "php-under-control".
  
- --schedule-interval

  The ant pause interval between two builds.
  
- --coding-guideline
  
  An optional PHP_CodeSniffer coding guideline. The default value is "PEAR".
  
- --build-tool

  An optional build tool that CruiseControl should use for the project. At the
  moment phpUnderControl only supports "ant".    

Arguments
`````````

- This task needs the CruiseControl installation directory as argument.  


The Clean Command
-----------------

This command provides a simple way to remove old project log files and build
artifacts. Just type "phpuc.php clean -j <project> -k 10 /path/to/cruisecontrol"
to keep the last ten builds. ::

    [mapi@frodo.xplib.de bin]$ ./phpuc.php clean -j <project> -k 10 /path/to/cruisecontrol
    
Or you can define the maximum age of the project logs and build artifacts with.
The following command will keep everything that is young than ten days. ::

    [mapi@frodo.xplib.de bin]$ ./phpuc.php clean -j <project> -d 10 /path/to/cruisecontrol

Options
```````

- --keep-builds (-k)

  The number of builds to keep.
  
- --keep-days (-d)

  The number of days for the maximum age of logs and artifacts.
  
- --project-name (-j)

  The context project name. 

Arguments
`````````

- This task needs the CruiseControl installation directory as argument.  
