<?php
/**
 * This file is part of the DreamFactory Console Tools Library
 *
 * Copyright 2014 DreamFactory Software, Inc. <support@dreamfactory.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace DreamFactory\Library\Console\Components;

use DreamFactory\Library\Console\Interfaces\NodeLike;
use Kisma\Core\Exceptions\FileSystemException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Manages a bag full of ConfigNode objects
 */
class NodeBag extends ParameterBag implements NodeLike
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The default suffix for our file
     */
    const DEFAULT_CONFIG_SUFFIX = '.config.json';
    /**
     * @type string The default relative directory for our file
     */
    const DEFAULT_CONFIG_PATH = '.dreamfactory';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The name of this app/config
     */
    protected $_name = null;
    /**
     * @type string The path oof where the config file lives
     */
    protected $_path = null;
    /**
     * @type bool If true, the config needs saving
     */
    protected $_dirty = false;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Creates a configuration file component
     *
     * @param string $name A short name that describes this config file. Typically the name of your application is used.
     * @param string $path The path to where this config file lives
     * @param array  $values
     */
    public function __construct( $name, $path, array $values = array() )
    {
        parent::__construct( $values );

        $this->_name = str_replace( ' ', '-', strtolower( $name ) );
        $this->_path = $path;

        $this->load();
    }

    //  Save junk if dirty...
    /**
     * @throws FileSystemException
     */
    public function __destruct()
    {
        if ( $this->_dirty )
        {
            $this->save();
        }
    }

    /**
     * Loads the current configuration
     *
     * @return array
     * @throws FileSystemException
     */
    public function load()
    {
        try
        {
            $_path = $this->_locateConfig();
            $_file = new JsonFile( $_path );
            $_config = $_file->read();
        }
        catch ( FileSystemException $_ex )
        {
            throw new \RuntimeException( $_ex->getMessage() );
        }

        $this->_path = dirname( $_path );

        $this->add( $_config );
        $this->_dirty = false;
    }

    /**
     * Saves the configuration file
     *
     * @param string $comment A comment to add to the configuration file in the "_comment" property
     *
     * @return array The contents stored
     * @throws FileSystemException
     */
    public function save( $comment = null )
    {
        $_path = $this->_locateConfig();

        //  Add a comment to the configuration file
        if ( $comment )
        {
            $this->
            $this->addComment( $comment );
        }

        //  Convert to JSON and store
        $_json = json_encode( $_config = $this->all(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

        if ( false === $_json || JSON_ERROR_NONE == json_last_error() )
        {
            throw new \RuntimeException( 'Error encoding data to JSON: ' . json_last_error_msg() );
        }

        if ( false === file_put_contents( $_path, $_json ) )
        {
            $this->_path = null;
            throw new FileSystemException( 'Error saving configuration file: ' . $_path );
        }

        $this->_path = dirname( $_path );

        return $_config;
    }

    /**
     * @param string $nodeId
     * @param array  $defaultValue
     *
     * @return mixed
     */
    public function getNode( $nodeId, $defaultValue = null )
    {
        if ( !$this->has( $nodeId ) )
        {
            $this->set( $nodeId, $defaultValue ?: $this->getDefaultNodeSchema() );
        }

        return $this->get( $nodeId );
    }

    /**
     * @param string $nodeId
     * @param array  $parameters
     */
    public function addNode( $nodeId, array $parameters = array() )
    {
        if ( $this->has( $nodeId ) )
        {
            $this->set( $nodeId, $parameters );
        }
        else
        {
            $this->add( array($nodeId => $parameters) );
        }

        $this->addComment( 'Created node "' . $nodeId . '"' );
    }

    /**
     * Removes a registry from the config
     *
     * @param string $nodeId The node to remove
     *
     * @return array The prior value of the node
     */
    public function removeNode( $nodeId )
    {
        if ( !$this->has( $nodeId ) )
        {
            throw new ParameterNotFoundException( $nodeId );
        }

        $this->remove( $nodeId );
        $this->addComment( 'Removed node "' . $nodeId . '"' );
    }

    /**
     * Makes sure the file passed it exists. Create default config and saves otherwise.
     *
     * @param string $path     The path in which the file resides
     * @param string $fileName The absolute path of the file, including the name.
     *
     * @throws FileSystemException
     * @return string The absolute path to the file
     */
    protected function _ensureFileExists( $path, $fileName )
    {
        if ( !is_dir( $path ) )
        {
            if ( false === mkdir( $path, 0777, true ) )
            {
                throw new FileSystemException( 'Unable to create directory: ' . $path );
            }
        }

        if ( !file_exists( $fileName ) )
        {
            $_node = array();

            if ( false === file_put_contents( $fileName, json_encode( $_node, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) ) )
            {
                throw new FileSystemException( 'Unable to create file: ' . $fileName );
            }
        }

        //  Exists
        return $fileName;
    }

    /**
     * Find and returns the path to my file
     */
    protected function _locateConfig()
    {
        if ( function_exists( 'posix_getpwuid' ) && function_exists( 'posix_getuid' ) )
        {
            $_user = posix_getpwuid( posix_getuid() );
            $_basePath = ( isset( $_user, $_user['dir'] ) ? $_user['dir'] : getcwd() );
        }
        else
        {
            //  Default to CWD
            $_basePath = getcwd();
        }

        //  This is where the file will live
        $_basePath .= DIRECTORY_SEPARATOR . static::DEFAULT_CONFIG_PATH;
        $_fileName = $_basePath . DIRECTORY_SEPARATOR . $this->_name . static::DEFAULT_CONFIG_SUFFIX;

        //  Doesn't exist? Create it...
        return $this->_ensureFileExists( $_basePath, $_fileName );
    }

    /**
     * Returns an array that will become the contents of a new configuration node
     *
     * @return array|NodeLike
     */
    public function getDefaultSchema()
    {
        return array(static::META_DATA_KEY => array());
    }

    /**
     * @return bool Always false as this is the top level node
     */
    public function getParentId()
    {
        return false;
    }

    /**
     * @param string $key
     *
     * @return bool True if the key exists in the node
     */
    public function contains( $key )
    {
        return $this->has( $key );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @return string The id of this node
     */
    public function getId()
    {
        return $this->_name;
    }

    /**
     * @param string $key
     *
     * @return bool True if the key existed and was deleted
     */
    public function delete( $key )
    {
        return $this->delete( $key );
    }
}
