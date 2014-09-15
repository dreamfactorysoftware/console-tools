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

use DreamFactory\Library\Console\Interfaces\RegistryLike;
use Kisma\Core\Exceptions\FileSystemException;

/**
 * Reads and write a DreamFactory configuration file
 */
class ConfigFile
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The name of the directory containing our configuration
     */
    const DEFAULT_CONFIG_BASE = '.dreamfactory';
    /**
     * @type string The name of the directory containing our configuration
     */
    const DEFAULT_CONFIG_SUFFIX = '.config.json';
    /**
     * @type string The format to use when creating date strings
     */
    const DEFAULT_TIMESTAMP_FORMAT = 'c';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The name/ID of this configuration
     */
    protected $_name = null;
    /**
     * @type string The path in which to load/save this config sans file name
     */
    protected $_configPath = null;
    /**
     * @type RegistryLike
     */
    protected $_registry;
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
     * @param string $name       The configuration name, or ID. File will be stored in [name].config.json
     * @param string $configPath The /path/to/the/store/config sans file name
     * @param array  $parameters Any parameters to add to the config
     *
     * @internal param string $path The path to the configuration file. Defaults to ~/.dreamfactory
     */
    public function __construct( $name, $configPath = null, array $parameters = array() )
    {
        $this->_name = $name;
        $this->_configPath = $configPath;

        //  Load the file...
        $this->load();
    }

    //  Save junk if dirty...
    /**
     * @throws FileSystemException
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * Loads the current configuration
     *
     * @return RegistryLike
     * @throws FileSystemException
     */
    public function load()
    {
        return $this->_registry = Registry::createFromTemplate( $this->_locateRegistry() );
    }

    /**
     * Saves the configuration file
     *
     * @param string $comment A comment to add to the configuration file in the "_comment" property
     *
     * @throws FileSystemException
     */
    public function save( $comment = null )
    {
        if ( null === $this->_registry )
        {
            throw new \LogicException( 'The save() method may not be called before the load() method.' );
        }

        //  Work with local copy
        $_timestamp = date( static::DEFAULT_TIMESTAMP_FORMAT, $_time = time() );

        //  Timestamp this save
        $this->_registry->set( '_timestamp', $_timestamp );

        //  Add a comment to the configuration file
        if ( $comment )
        {
            $_comments = $this->_registry->get( '_comments', array() );
            $_comments[$_timestamp] = $comment;
            $this->_registry->set( '_comments', $_comments );
        }

        //  Convert to JSON and store
        $_jsonFile = new JsonFile( $this->_locateRegistry() );
        $_jsonFile->write( $this->_registry->all() );

        //  Try and lock the file down...
        @chmod( $_jsonFile->getFilePath(), 0600 );
    }

    /**
     * @param string $registryKey The root key withing the registry
     * @param array  $properties  Optional properties to set into this key
     *
     * @return $this
     * @throws \Kisma\Core\Exceptions\FileSystemException
     */
    public function addNode( $id, array $properties = array() )
    {
        $this->_registry->add( array($id => $properties) );

        return $this;
    }

    /**
     * Removes a registry from the config
     *
     * @param string $registryKey
     *
     * @return bool
     */
    public function removeNode( $id )
    {
        if ( $this->_registry->has( $id ) )
        {
            $this->_registry->set( $id, null );

            return true;
        }

        return false;
    }

    /**
     * Returns the value stored under the registry key $registryKey. Returns FALSE on not-found
     *
     * @param string $key
     * @param bool   $autoCreate
     * @param bool   $returnValue
     *
     * @return bool|array
     */
    public function getNode( $id, $autoCreate = true, $returnValue = false )
    {
        if ( !$this->_registry->has( $id ) )
        {
            if ( !$autoCreate )
            {
                return false;
            }

            $this->_registry->set( $id, array() );
        }

        return $this->_registry->get( $id );
    }

    /**
     * Returns the value stored under the registry key $registryKey. Returns FALSE on not-found
     *
     * @param string $key
     * @param bool   $autoCreate
     * @param bool   $returnValue
     *
     * @return $this
     */
    public function setNode( $id, array $properties = array() )
    {
        $this->_registry->set( $id, $properties );

        return $this;
    }

    /**
     * @param array $registry An existing registry
     *
     * @return array
     */
    public function mergeNode( $id, array $properties = array() )
    {
        $_data = array_merge( $this->getNode( $id ), $properties );

        return $this->setNode( $id, $_data );
    }

    /**
     * @param string $nodeId     The node ID
     * @param string $entryId    The entry ID under node
     * @param array  $properties The properties of the entry
     *
     * @return array|bool
     */
    public function addNodeEntry( $nodeId, $entryId, array $properties = array() )
    {
        $_node = $this->getNode( $nodeId, true, true );
        $_node[$entryId] = $properties;

        return $this->setNode( $nodeId, $_node );
    }

    /**
     * @param string $nodeId
     * @param string $entryId
     *
     * @return bool True if deleted, false if not found
     */
    public function removeNodeEntry( $nodeId, $entryId )
    {
        $_node = $this->getNode( $nodeId, true, true );

        if ( array_key_exists( $entryId, $_node ) )
        {
            unset( $_node[$entryId] );
            $this->setNode( $nodeId, $_node );

            return true;
        }

        return false;

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
            Registry::createFromFile( $fileName, true );
        }

        //  Exists
        return $fileName;
    }

    /**
     * Find and returns the path to my file, taking $this->_configPath into account
     */
    protected function _locateRegistry()
    {
        if ( !empty( $this->_configPath ) && is_dir( $this->_configPath ) )
        {
            $_path = $this->_configPath;
        }
        else
        {
            if ( function_exists( 'posix_getpwuid' ) && function_exists( 'posix_getuid' ) )
            {
                $_user = posix_getpwuid( posix_getuid() );
                $_path = ( isset( $_user, $_user['dir'] ) ? $_user['dir'] : getcwd() );
            }
            else
            {
                $_path = sys_get_temp_dir();
            }
        }

        //  This is where the file will live
        $_path .= DIRECTORY_SEPARATOR . static::DEFAULT_CONFIG_BASE;

        //  Doesn't exist? Create it...
        return $this->_ensureFileExists( $_path, $_path . DIRECTORY_SEPARATOR . $this->_name . static::DEFAULT_CONFIG_SUFFIX );
    }
}
