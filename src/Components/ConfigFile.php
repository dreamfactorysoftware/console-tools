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
    const DEFAULT_CONFIG_SUFFIX = '.options.json';
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
     * @type string The configuration file path, no file.
     */
    protected $_configPath = null;
    /**
     * @type string The configuration file name, no path.
     */
    protected $_configFile = null;
    /**
     * @type string The absolute path to the actual configuration file
     */
    protected $_configFilePath = null;
    /**
     * @type bool If true, the config needs saving
     */
    protected $_dirty = false;
    /**
     * @type AppRegistry
     */
    protected $_registry = null;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Creates a configuration file component
     *
     * @param string $name The configuration name, or ID. File will be stored in [name].config.json
     * @param string $path The path to the configuration file. Defaults to ~/.dreamfactory
     */
    public function __construct( $name, $path = null )
    {
        $this->_name = $name;
        $this->_configPath = $path;
        $this->_config = array();

        //  Load the file...
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
        $this->_configFilePath = $this->_locateRegistry();

        return $this->_registry = new AppRegistry( $this->_configFilePath );
    }

    /**
     * Saves the configuration file
     *
     * @param string $comment A comment to add to the configuration file in the "_comment" property
     *
     * @return array
     * @throws FileSystemException
     */
    public function save( $comment = null )
    {
        if ( null === $this->_registry )
        {
            throw new \LogicException( 'The save() method may not be called before the load() method.' );
        }

        if ( !$this->_configFilePath )
        {
            $this->_configFilePath = $this->_locateRegistry();
        }

        if ( !count( $this->_registry->all() ) )
        {
            $this->_registry->add( $this->_initializeRegistry() );
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
        $_json = json_encode( $this->_config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

        if ( false === file_put_contents( $this->_configFilePath, $_json ) )
        {
            $this->_configFilePath = null;
            throw new FileSystemException( 'Error saving configuration file: ' . $this->_configFilePath );
        }

        //  Try and lock the file down...
        @chmod( $this->_configFilePath, 0600 );

        return $this->_config;
    }

    /**
     * @param string $registryKey The root key withing the registry
     * @param array  $properties  Optional properties to set into this key
     *
     * @return $this
     * @throws \Kisma\Core\Exceptions\FileSystemException
     */
    public function addRegistry( $registryKey, array $properties = array() )
    {
        $this->_registry->set( $registryKey, $this->_mergeRegistry( $this->_registry->all(), $properties ) );

        return $this;
    }

    /**
     * Removes a registry from the config
     *
     * @param string $registryKey
     *
     * @return bool
     */
    public function removeRegistry( $registryKey )
    {
        if ( !$this->hasRegistry( $registryKey ) )
        {
            return false;
        }

        $this->_registry->remove( $registryKey );

        return true;
    }

    /**
     * @param string $registryKey The master key within the registry
     * @param bool   $autoCreate  Inits the hive if the key isn't found
     * @param bool   $returnValue If found, and this is true, the value stored at key $registryKey is returned, otherwise TRUE
     *
     * @return bool|array
     */
    public function hasRegistry( $registryKey, $autoCreate = true, $returnValue = false )
    {
        if ( false === ( $_registry = $this->_registry->has( $registryKey ) ) )
        {
            if ( !$autoCreate )
            {
                return false;
            }

            //  Default to an array
            $this->_registry->set( $registryKey, array() );
        }

        return $returnValue ? $this->_registry[$registryKey] : true;
    }

    /**
     * Returns the value stored under the registry key $registryKey. Returns FALSE on not-found
     *
     * @param string $registryKey
     * @param bool   $autoCreate
     * @param bool   $returnValue
     *
     * @return bool|array
     */
    public function getRegistry( $registryKey, $autoCreate = true, $returnValue = false )
    {
        if ( !$this->_registry->has( $registryKey ) )
        {
            return $this->_registry->get( $registryKey );
        }

        return $this->_registry->get( $registryKey );
    }

    /**
     * @param array $registry An existing registry
     *
     * @return array
     */
    protected function _mergeRegistry( array $registry = array() )
    {
        $_original = $this->_registry->all();

        return array_merge( $_original, $registry );
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
            if ( false === file_put_contents( $fileName, json_encode( $this->_initializeRegistry(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) ) )
            {
                throw new FileSystemException( 'Unable to create registry file: ' . $fileName );
            }
        }

        //  Exists
        return $fileName;
    }

    /**
     * Find and returns the path to my file
     */
    protected function _locateRegistry()
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

        //  This is where the file will live
        $_path .= DIRECTORY_SEPARATOR . static::DEFAULT_CONFIG_BASE;

        //  Doesn't exist? Create it...
        return $this->_ensureFileExists( $_path, $_path . DIRECTORY_SEPARATOR . $this->_name . static::DEFAULT_CONFIG_SUFFIX );
    }

    /**
     * @return array The default registry values
     */
    protected function _initializeRegistry()
    {
        return new AppRegistry();
    }
}
