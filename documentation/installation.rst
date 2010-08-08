============================
Documentation - Installation
============================


This section describes how to install `CruiseControl`__ and phpUnderControl. If
you have read the following lines you should be able to set up your own continuous
integration environment for PHP development.

__ http://cruisecontrol.sourceforge.net/

Setting up CruiseControl and phpUnderControl
============================================

The first things you need is a `Java`__ environment and a working installation 
of `CruiseControl`__. I assume that Java is already installed and so I will not 
cover this topic here, but I suggest to use an original Java version from `SUN`__
instead of some versions bundled with linux distributions, because in combination 
with `compiz`__ or `beryl`__ the metrics view may be broken otherwise.

__ http://java.sun.com/javase/downloads/?intcmp=1281
__ http://sourceforge.net/project/showfiles.php?group_id=23523
__ http://java.sun.com/
__ http://compiz.org/
__ http://www.beryl-project.org/

Setting up a working CruiseControl installation is a really simple task. Just 
download one of the provided zip archives from the project site, unpack the 
contents into an arbitrary folder and start the application. For a detailed 
installation description how to install CruiseControl and how to configure it 
with PHPUnit I recommend an excellent `article by Sebastian Nohn`__ and the 
`Continuous Integration chapter`__ from the `PHPUnit Pocket Guide`__.

__ http://nohn.org/blog/view/id/cruisecontrol_ant_and_phpunit
__ http://www.phpunit.de/pocket_guide/3.2/en/continuous-integration.html
__ http://www.phpunit.de/pocket_guide/3.2/en/index.html

*Please note* the CruiseControl `tutorial`__ uses version 2.4.*, but the current
version is 2.7.*. So please install an up to date version, because it is required
by phpUnderControl!

__ http://nohn.org/blog/view/id/cruisecontrol_ant_and_phpunit

Now that you have a working CruiseControl, check out phpUnderControl from its
repository hosted on github. ::

    git clone git://github.com/manuelpichler/phpUnderControl.git

Now you can patch your CruiseControl installation by entering ::

    phpUnderControl/bin/phpuc(.php|.bat) install /path/to/cruisecontrol

This command will exchange some template and the original metric charts and it 
installs additional resources.

That's it.  
