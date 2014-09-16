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
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

/**
 * Manages a bag full of ConfigNode objects
 */
class ConfigFile extends ConfigNode
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The name of the directory containing our configuration
     */
    const DEFAULT_CONFIG_BASE = '.dreamfactory';
    /**
     * @type string The default suffix for our files
     */
    const DEFAULT_CONFIG_SUFFIX = '.config.json';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The prefix name of this config file (i.e. "fabric", or "sandman").
     * Will be used to construct the actual file name: {$name}.config.json
     */
    protected $_name = null;
    /**
     * @type string The absolute path where the config file lives
     */
    protected $_configPath = null;
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
     * @param string $name The name/id of this configuration
     * @param string $path The path to the configuration file. Defaults to ~/.dreamfactory
     * @param array  $parameters
     */
    public function __construct( $name, $path = null, array $parameters = array() )
    {
        $this->_name = $name;
        $this->_configPath = $path;

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
        $_path = $this->_locateConfig();

        if ( false === ( $_config = json_decode( file_get_contents( $_path ), true ) ) || JSON_ERROR_NONE != json_last_error() )
        {
            $this->_configPath = null;
            throw new \RuntimeException( 'Invalid or missing JSON in file "' . $this->_configPath . '".' );
        }

        $this->_configPath = dirname( $_path );
        $this->add( $_config );
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
            $this->_configPath = null;
            throw new FileSystemException( 'Error saving configuration file: ' . $_path );
        }

        $this->_configPath = dirname( $_path );

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
            if ( false ===
                file_put_contents( $fileName, json_encode( $this->getDefaultSchema( false ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) )
            )
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
        if ( is_dir( $this->_configPath ) )
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
                //  Default to CWD
                $_path = getcwd();
            }
        }

        //  This is where the file will live
        $_path .= DIRECTORY_SEPARATOR . static::DEFAULT_CONFIG_BASE;

        //  Doesn't exist? Create it...
        return $this->_ensureFileExists( $_path, $_path . DIRECTORY_SEPARATOR . $this->_name . static::DEFAULT_CONFIG_SUFFIX );
    }
}
