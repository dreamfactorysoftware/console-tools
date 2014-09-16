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
     * @type string The name/id of this config file
     */
    protected $_name = null;
    /**
     * @type string The absolute path to the actual configuration file
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
     */
    public function __construct( $name, $path = null )
    {
        $this->_name = $name;
        $this->_configPath = $path;
        $this->_config = array();
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
        $this->_configPath = $this->_locateConfig();

        if ( false === ( $_config = json_decode( file_get_contents( $this->_configPath ), true ) ) || JSON_ERROR_NONE != json_last_error() )
        {
            $this->_configPath = null;
            throw new \RuntimeException( 'Invalid or missing JSON in file "' . $this->_configPath . '".' );
        }

        $this->merge( $_config );
    }

    /**
     * Merges data into an existing node
     *
     * @param array $dataToMerge
     */
    public function merge( array $dataToMerge = array() )
    {
        $this->set( $this->_nodeId, array_merge( $this->all(), $dataToMerge ) );
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
        if ( !$this->_configPath )
        {
            $this->_configPath = $this->_locateConfig();
        }

        //  Work with local copy
        $_timestamp = date( static::DEFAULT_TIMESTAMP_FORMAT, $_time = time() );

        //  Add a comment to the configuration file
        if ( $comment )
        {
            $this->addComment( $comment );
        }

        //  Convert to JSON and store
        $_json = json_encode( $this->all(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

        if ( false === file_put_contents( $this->_configPath, $_json ) )
        {
            $this->_configPath = null;
            throw new FileSystemException( 'Error saving configuration file: ' . $this->_configPath );
        }

        return $this->all();
    }

    /**
     * @param ConfigNode $node The node
     *
     * @return $this
     */
    public function addNode( $node )
    {
        $node->set( 'parentId', $this->_parentId );
        $this->add( array($node->getNodeId() => $node->all()) );

        return $this;
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
        $_node = $this->get( $nodeId );
        $this->remove( $nodeId );

        return $_node->all();
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
                file_put_contents(
                    $fileName,
                    json_encode( $this->getDefaultSchema( true, $this->_nodeId ), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT )
                )
            )
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
    protected function _locateConfig()
    {
        if ( function_exists( 'posix_getpwuid' ) && function_exists( 'posix_getuid' ) )
        {
            $_user = posix_getpwuid( posix_getuid() );
            $_path =
                ( isset( $_user, $_user['dir'] ) ? $_user['dir'] : getcwd() );
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
}
