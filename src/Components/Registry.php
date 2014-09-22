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
use DreamFactory\Library\Console\Interfaces\RegistryLike;
use Kisma\Core\Exceptions\FileSystemException;

/**
 * Manages a JSON registry
 */
class Registry extends Collection implements RegistryLike
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

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Creates a data registry
     *
     * @param string $id       The short name for this registry
     * @param string $path     The absolute path of where the registry should be stored
     * @param array  $contents Initial contents
     *
     * @internal param string $filePath The path to where the registry lives
     */
    public function __construct( $id, $path, $contents = array() )
    {
        $this->_id = $id;
        $this->_path = $path;

        parent::__construct( $contents );

        $this->initialize();
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
    public function initialize( $contents = array() )
    {
        $this->load( $this->_id, $this->_path );

        return $this;
    }

    /**
     * Loads the current configuration
     *
     * @return Registry
     */
    public function load()
    {
        $_filePath = $this->validateRegistryPath();
        $_data = JsonFile::decode( file_get_contents( $_filePath ) );

        if ( false === $_data || JSON_ERROR_NONE != json_last_error() )
        {
            throw new \RuntimeException( 'Registry invalid or corrupt.' );
        }

        //  Add data to our bag
        $this->merge( $_data );

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
        $_filePath = $this->validateRegistryPath();
        $_json = $this->all( 'json' );

        if ( false === file_put_contents( $_filePath, $_json ) )
        {
            throw new FileSystemException( 'Error saving registry: ' . $_filePath );
        }

        $this->_path = dirname( $_filePath );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function createFromFile( $id, $path, $file, array $replacements = array() )
    {
        if ( false === ( $_json = file_get_contents( $file ) ) )
        {
            throw new \InvalidArgumentException( 'The template file is invalid or does not exist.' );
        }

        $_json = str_ireplace( array_keys( $replacements ), array_values( $replacements ), $_json );
        $_data = JsonFile::decode( $_json );

        return new static( $id, $path, $_data );
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
     * @param string $id
     * @param string $path
     * @param bool   $createIfNotFound
     *
     * @return string
     */
    public function validateRegistryPath( $id = null, $path = null, $createIfNotFound = true )
    {
        $_filePath = ( $path ?: $this->_path ) . DIRECTORY_SEPARATOR . ( $id ?: $this->_id ) . static::DEFAULT_CONFIG_SUFFIX;
        $_path = dirname( $_filePath );

        if ( !is_dir( $_path ) && false === mkdir( $_path, 0777, true ) )
        {
            throw new \RuntimeException( 'Cannot create directory "' . $_path . '" for registry.' );
        }

        if ( $createIfNotFound && ( !is_file( $_filePath ) || !file_exists( $_filePath ) ) )
        {
            if ( false === file_put_contents( $_filePath, '{}' ) )
            {
                throw new \RuntimeException( 'The registry file "' . $_filePath . '" could not be created.' );
            }
        }

        return $_filePath;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

}
