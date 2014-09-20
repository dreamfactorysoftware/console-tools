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

use DreamFactory\Library\Console\Bags\NodeBag;
use DreamFactory\Library\Console\Interfaces\NodeLike;
use DreamFactory\Library\Console\Interfaces\RegistryLike;
use Kisma\Core\Exceptions\FileSystemException;

/**
 * Manages a JSON registry
 */
class Registry implements RegistryLike
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The default suffix for our files
     */
    const DEFAULT_CONFIG_SUFFIX = '.config.json';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The id of this registry
     */
    protected $_id = null;
    /**
     * @type string The path where the registry lives
     */
    protected $_path = null;
    /**
     * @type array An array of keys to normalized keys for faster lookups
     */
    protected $_normalized = array();
    /**
     * @type NodeBag
     */
    protected $_bag = null;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Creates a data registry
     *
     * @param string $id   The short name for this registry
     * @param string $path The absolute path of where the registry should be stored
     *
     * @internal param string $filePath The path to where the registry lives
     */
    public function __construct( $id, $path )
    {
        $this->_id = $id;
        $this->_path = $path;

        $this->load( $id, $path );
    }

    /**
     * @throws FileSystemException
     */
    public function __destruct()
    {
        //  Save junk if dirty...
        $this->save();
    }

    /**
     * Initializes the contents of the bag
     *
     * @param array $contents
     *
     * @return NodeLike
     */
    public function initialize( array $contents = array() )
    {
        $this->_contents = $contents;

        return $this;
    }

    /**
     * @param array|object $contents
     *
     * @return string
     */
    public function encode( $contents = null )
    {
        $_contents = $contents ?: $this->_contents;

        if ( empty( $_contents ) )
        {
            $_contents = array();
        }

        return JsonFile::encode( $_contents );
    }

    /**
     * @param string $contents
     * @param bool   $toArray
     *
     * @return string
     */
    public function decode( $contents, $toArray = true )
    {
        return json_decode( $contents, $toArray );
    }

    /**
     * Loads the current configuration
     *
     * @return Registry
     */
    public function load()
    {
        $_filePath = $this->_initializeRegistry( $this->_id, $this->_path );

        $this->_contents = $this->decode( file_get_contents( $_filePath ) );

        if ( false === $this->_contents || JSON_ERROR_NONE != json_last_error() )
        {
            throw new \RuntimeException( 'Registry invalid or corrupt.' );
        }

        return $this;
    }

    /**
     * Saves the configuration file
     *
     * @param string $comment A comment to add
     *
     * @return array The contents stored
     * @throws FileSystemException
     */
    public function save( $comment = null )
    {
        $_filePath = $this->_initializeRegistry( $this->_path, $this->_id );
        $_json = $this->encode();

        if ( false === $_json || JSON_ERROR_NONE != json_last_error() )
        {
            throw new \RuntimeException( 'Error encoding registry: ' . json_last_error_msg() );
        }

        if ( false === file_put_contents( $_filePath, $_json ) )
        {
            throw new FileSystemException( 'Error saving registry: ' . $_filePath );
        }

        $this->_path = dirname( $_filePath );

        return $this;
    }

    /**
     * Tries to locate a place to store the registry
     *
     * @param Registry $registry
     *
     * @return string
     */
    public static function findRegistryPath( Registry $registry )
    {
        $_paths = array(
            $registry->getPath(),
            getenv( 'HOME' ),
            function ()
            {
                if ( function_exists( 'posix_getpwuid' ) && function_exists( 'posix_getuid' ) )
                {
                    $_user = posix_getpwuid( posix_getuid() );

                    return ( isset( $_user, $_user['dir'] ) ? $_user['dir'] : getcwd() );
                }
            },
            sys_get_temp_dir(),
            getcwd(),
        );

        $_found = false;
        $_path = null;

        foreach ( $_paths as $_path )
        {
            if ( empty( $_path ) || ( !is_dir( $_path ) && false === @mkdir( $_path, 0777, true ) ) )
            {
                continue;
            }

            $_found = true;
            break;
        }

        if ( !$_found || empty( $_path ) )
        {
            throw new \RuntimeException( 'Cannot find a place to store registry.' );
        }

        return rtrim( $_path, DIRECTORY_SEPARATOR );
    }

    /**
     * @param string $templateFile The absolute path to a JSON file
     *
     * @return Registry
     */
    public static function createFromFile( $templateFile )
    {
        return new static( null, $templateFile );
    }

    /**
     * @param string $id
     * @param string $path
     * @param bool   $createIfNotFound If no registry is found, an empty file is created
     *
     * @return string the full path and file name of the registry file
     */
    protected function _initializeRegistry( $id, $path, $createIfNotFound = false )
    {
        $_filePath = ( $path ?: $this->_path ) . DIRECTORY_SEPARATOR . ( $id ?: $this->_id ) . static::DEFAULT_CONFIG_SUFFIX;
        $_path = dirname( $_filePath );

        if ( !is_dir( $_path ) && false === mkdir( $_path, 0777, true ) )
        {
            throw new \RuntimeException( 'Cannot create directory "' . $_path . '" for registry.' );
        }

        if ( $createIfNotFound && ( !is_file( $_filePath ) || !file_exists( $_filePath ) ) )
        {
            if ( false === file_put_contents( $_filePath, $this->encode() ) )
            {
                throw new \RuntimeException( 'The registry file "' . $_filePath . '" could not be created.' );
            }
        }

        return $_filePath;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * A default schema for the node
     *
     * @return array
     */
    public function getSchema()
    {
        return array();
    }

    /**
     * Removes all bag items
     *
     * @return NodeLike
     */
    public function clear()
    {
        // TODO: Implement clear() method.
    }

    /**
     * @param string $key
     * @param bool   $returnNormalizedKey If true and the key was found, the normalized key is returned. False otherwise
     *
     * @return bool|string The normalized key if found, or false
     */
    public function has( $key, $returnNormalizedKey = true )
    {
        // TODO: Implement has() method.
    }

    /**
     * Retrieves a value at the given key location, or the default value if key isn't found.
     * Setting $burnAfterReading to true will remove the key-value pair from the bag after it
     * is retrieved. Call with no arguments to get back a KVP array of contents
     *
     * @param string $key
     * @param mixed  $defaultValue
     * @param bool   $burnAfterReading
     *
     * @throws \Kisma\Core\Exceptions\BagException
     * @return mixed
     */
    public function get( $key = null, $defaultValue = null, $burnAfterReading = false )
    {
        // TODO: Implement get() method.
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param bool   $overwrite
     *
     * @throws \Kisma\Core\Exceptions\BagException
     * @return NodeLike
     */
    public function set( $key, $value, $overwrite = true )
    {
        // TODO: Implement set() method.
    }

    /**
     * @param string $key
     *
     * @return bool True if the key existed and was deleted
     */
    public function remove( $key )
    {
        // TODO: Implement remove() method.
    }

    /**
     * Returns an array of all node entries
     *
     * @param string $format A data format in which to provide the results. Valid options are null and "json"
     *
     * @return array|string
     */
    public function all( $format = null )
    {
        // TODO: Implement all() method.
    }
}
