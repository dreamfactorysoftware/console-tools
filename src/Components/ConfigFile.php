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
    const DEFAULT_CONFIG_FILE = 'options.json';

    //******************************************************************************
    //* Members
    //******************************************************************************

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

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Creates a configuration file component
     *
     * @param string $file The configuration file name. Defaults to options.json
     * @param string $path The path to the configuration file. Defaults to ~/.dreamfactory
     */
    public function __construct( $file = null, $path = null )
    {
        $this->_configFile = $file;
        $this->_configPath = $path;
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

        $this->_configFilePath = $_path . DIRECTORY_SEPARATOR . ( $this->_configFile ?: static::DEFAULT_CONFIG_FILE );

        if ( !file_exists( $this->_configFilePath ) )
        {
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

        //  Timestamp this save
        $_config['_timestamp'] = date( 'c', $_time = time() );

        //  Add a comment to the configuration file
        if ( $comment )
        {
            if ( !isset( $_config['_comments'] ) )
            {
                $_config['_comments'] = array();
            }

            $_config['_comments'][] = array($_config['_timestamp'] => $comment);
        }

        //  Convert to JSON and store
        $_json = json_encode( $this->_config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

        if ( false === file_put_contents( $this->_configFilePath, $_json ) )
        {
            $this->_configFilePath = null;
            throw new FileSystemException( 'Error saving configuration file: ' . $this->_configFilePath );
        }

        return $this->_config = $_config;
    }
}