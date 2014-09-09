<?php
/**
 * This file is part of the DreamFactory Freezer(tm)
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
use Kisma\Core\Utility\Option;

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

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The name/ID of this configuration
     */
    protected $_name = null;
    /**
     * @type array The current configuration
     */
    protected $_config = null;
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
        $_user = posix_getpwuid( posix_getuid() );
        $_path =
            $this->_configPath ?: ( isset( $_user, $_user['dir'] ) ? $_user['dir'] : getcwd() ) . DIRECTORY_SEPARATOR . static::DEFAULT_CONFIG_BASE;

        if ( !is_dir( $_path ) )
        {
            if ( false === mkdir( $_path, 0777, true ) )
            {
                throw new FileSystemException( 'Unable to create directory: ' . $_path );
            }
        }

        $this->_configFilePath = $_path . DIRECTORY_SEPARATOR . $this->_name . static::DEFAULT_CONFIG_SUFFIX;

        if ( !file_exists( $this->_configFilePath ) )
        {
            $this->setOption( 'db-servers', array() );

            return $this->save( 'I\'m Mr. Meeseeks! Look at me!!' );
        }

        if ( false === ( $_config = json_decode( file_get_contents( $this->_configFilePath ), true ) ) || JSON_ERROR_NONE != json_last_error() )
        {
            $this->_configFilePath = null;
            throw new \RuntimeException( 'Invalid or missing JSON in file "' . $this->_configFilePath . '".' );
        }

        return $this->_config = array_merge( $this->_config, $_config );
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
        if ( !$this->_configFilePath )
        {
            throw new \LogicException( 'No configuration file has been loaded. Cannot save.' );
        }

        if ( empty( $this->_config ) )
        {
            $this->_config = array();
        }

        //  Work with local copy
        $_config = $this->_config;
        $_timestamp = date( 'c', $_time = time() );

        //  Timestamp this save
        $this->setOption( '_timestamp', $_timestamp );

        //  Add a comment to the configuration file
        if ( $comment )
        {
            $_comments = $this->getOption( '_comments', array() );
            $_comments[$_timestamp] = $comment;
            $this->setOption( '_comments', $_comments );
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
     * @param string                     $name         The option to get
     * @param string|number|array|object $defaultValue The default value of the option
     *
     * @return mixed
     */
    public function getOption( $name, $defaultValue = null )
    {
        return Option::get( $this->_config, $name, $defaultValue );
    }

    /**
     * @param string                     $name  The option to set
     * @param string|number|array|object $value The new option value
     *
     * @return array|string
     */
    public function setOption( $name, $value = null )
    {
        if ( false === json_encode( $value, JSON_UNESCAPED_SLASHES ) || JSON_ERROR_NONE != json_last_error() )
        {
            throw new \InvalidArgumentException( 'The value supplied cannot be converted to JSON: ' . json_last_error_msg() );
        }

        $this->_dirty = true;

        return Option::set( $this->_config, $name, $value );
    }

    /**
     * @return array
     */
    protected function _createDefaultConfig()
    {
        return array();
    }

}
