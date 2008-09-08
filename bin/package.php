#!/usr/bin/env php
<?php
class phpucPackage
{
    /**
     * The project directory.
     *
     * @var string $projectPath
     */
    private $projectPath = null;
    
    /**
     * Constructs a new phpuc package instance.
     *
     * @param string $projectPath The project root directory.
     */
    public function __construct( $projectPath )
    {
        $path = realpath( $projectPath );
        if ( is_dir( $path ) === false )
        {
            throw new Exception( "Invalid project directory '{$path}'." );
        }
        $this->projectPath = $path;
    }
    
    /**
     * This method updates the package.xml manifest file.
     *
     * @return void
     */
    public function update()
    {
        $package = $this->loadPackageXml();
        
        $structures = $this->collectStructures();
        
        $xpath = new DOMXPath( $package );
        $xpath->registerNamespace( 'p', 'http://pear.php.net/dtd/package-2.0' );
        
        foreach ( $structures as $name => $structure )
        {
            $expression = "//p:contents/p:dir[@name='/']/p:dir[@name='{$name}']";
            
            $result = $xpath->query( $expression );
            if ( $result->length !== 1 )
            {
                throw new Exception( "Invalid xml data, missing contents//dir@{$name}." );
            }
            
            $element = $package->createElement( 'dir' );
            $element->setAttribute( 'name', $name );
            
            $this->createXmlStructure( $element, $structure, $name === 'src' ? 'php' : 'test' );
            
            $result->item( 0 )->parentNode->replaceChild( $element, $result->item( 0 ) );
        }
        
        $expression = "//p:phprelease/p:filelist/p:install";
        
        $result = $xpath->query( $expression );
        for ( $i = $result->length - 1; $i >= 0; --$i )
        {
            if ( strpos( $result->item( $i )->getAttribute( 'name' ), 'src/' ) === 0 )
            {
                $result->item( $i )->parentNode->removeChild( $result->item( $i ) );
            }
        }
        
        $installList = $this->createInstallList( $structures['src'] );
        
        $expression = "//p:phprelease/p:filelist";
        foreach ( $xpath->query( $expression ) as $node )
        {
            foreach ( $installList as $as => $name )
            {
                $install = $package->createElement( 'install' );
                $install->setAttribute( 'as', $as );
                $install->setAttribute( 'name', $name );
                
                $node->appendChild( $install );
            }
        }
        
        $package->save( $this->projectPath . '/package.xml' );
    }
    
    /**
     * Collects the content structure.
     *
     * @return array
     */
    private function collectStructures()
    {
        $structure = array();
        foreach ( array( 'src', 'tests' ) as $dir )
        {
            $path = sprintf( '%s/%s', $this->projectPath, $dir );
            if ( file_exists( $path ) === false )
            {
                throw new Exception( "Path '{$path}' doesn't exist." );
            }
            if ( is_dir( $path ) === false )
            {
                throw new Exception( "Path '{$path}' isn't a directory." );
            }
            $structure[$dir] = $this->collectStructure( $dir . '/' );
        }
        return $structure;
    }
    
    /**
     * Collects the directory tree for the given dir
     *
     * @param string $dir The context directory.
     * 
     * @return array
     */
    private function collectStructure( $dir )
    {
        $structure = array();
        
        $it = new DirectoryIterator( $this->projectPath . '/' . $dir );
        foreach ( $it as $fileInfo )
        {
            $fileName = $fileInfo->getFilename();
            if ( strpos( $fileName, '.' ) === 0 )
            {
                continue;
            }
            else if ( $fileInfo->isDir() === true )
            {
                $structure[$fileName] = $this->collectStructure( $dir . $fileName . '/' );
            }
            else
            {
                $structure[$fileName] = $fileInfo->getPathname();
            }
        }
        return $structure;
    }
    
    /**
     * Loads the package xml file.
     *
     * @return DOMDocument
     */
    private function loadPackageXml()
    {
        $xml = new DOMDocument( '1.0', 'UTF-8' );
        
        $xml->formatOutput       = true;
        $xml->preserveWhiteSpace = false;
        
        $xml->load( $this->projectPath . '/package.xml' );
        
        return $xml;
    }
    
    private function createXmlStructure( DOMElement $parent, array $structure, $role )
    {
        foreach ( $structure as $name => $data )
        {
            if ( is_array( $data ) )
            {
                $element = $parent->ownerDocument->createElement( 'dir' );
                $this->createXmlStructure( $element, $data, $role );
            }
            else
            {
                $element = $parent->ownerDocument->createElement( 'file' );
                $element->setAttribute( 'role', $role );
                
                $task1 = $parent->ownerDocument->createElement( 'tasks:replace' );
                $task1->setAttribute( 'from', '@package_version@' );
                $task1->setAttribute( 'to', 'version' );
                $task1->setAttribute( 'type', 'package-info' );
                $element->appendChild( $task1 );
                
                $task2 = $parent->ownerDocument->createElement( 'tasks:replace' );
                $task2->setAttribute( 'from', '@php_dir@' );
                $task2->setAttribute( 'to', 'php_dir' );
                $task2->setAttribute( 'type', 'pear-config' );
                $element->appendChild( $task2 );
                
                $task3 = $parent->ownerDocument->createElement( 'tasks:replace' );
                $task3->setAttribute( 'from', '@bin_dir@' );
                $task3->setAttribute( 'to', 'bin_dir' );
                $task3->setAttribute( 'type', 'pear-config' );
                $element->appendChild( $task3 );
                
                $task4 = $parent->ownerDocument->createElement( 'tasks:replace' );
                $task4->setAttribute( 'from', '@data_dir@' );
                $task4->setAttribute( 'to', 'data_dir' );
                $task4->setAttribute( 'type', 'pear-config' );
                $element->appendChild( $task4 );
            }
            
            $element->setAttribute( 'name', $name );
            
            $parent->appendChild( $element );
        }
    }
    
    private function createInstallList( array $structure, $dir = '' )
    {
        $installList = array();
        foreach ( $structure as $name => $data )
        {
            if ( is_array( $data ) )
            {
                $installList += $this->createInstallList( $data, $dir . $name . '/' );
            }
            else
            {
                $installList['phpUnderControl/' . $dir . $name] = 'src/' . $dir . $name;
            }
        }
        return $installList;
    }
    
    public static function main( array $args )
    {
        if ( count( $args ) < 2 )
        {
            throw new Exception( 'Missing argument one.' );
        }
        $package = new phpucPackage( $args[1] );
        $package->update();
    }
}

phpucPackage::main( $argv );