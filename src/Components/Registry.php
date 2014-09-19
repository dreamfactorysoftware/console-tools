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
 * Manages a JSON registry
 */
class Registry extends DataNode
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
     * @type string The name of this registry
     */
    protected $_name = null;
    /**
     * @type string The path where the registry lives
     */
    protected $_path = null;
    /**
     * @type array An array of keys to normalized keys for faster lookups
     */
    protected $_normalized = array();

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Creates a data registry
     *
     * @param string $name A short name that describes this config file. Typically the name of your application is used.
     * @param string $path The path to where this config file lives
     */
    public function __construct( $name, $path )
    {
        parent::__construct( $name );

        $this->_name = $name;
        $this->_path = $path;

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
     * @param array $contents
     *
     * @return string
     */
    public function encode( array $contents = null )
    {
        $_contents = $contents ?: $this->_contents;

        if ( empty( $_contents ) )
        {
            $_contents = array();
        }

        return json_encode( $_contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
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
     * @param string $path
     * @param string $name
     *
     * @return Registry
     */
    public function load( $path = null, $name = null )
    {
        $_filePath = $this->_initializeRegistry( $path, $name );

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
        $_filePath = $this->_initializeRegistry( $this->_path, $this->_name );
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
     * @param string $path
     * @param string $name
     * @param bool   $createIfNotFound If no registry is found, an empty file is created
     *
     * @return string the full path and file name of the registry file
     */
    protected function _initializeRegistry( $path = null, $name = null, $createIfNotFound = false )
    {
        $_filePath = ( $path ?: $this->_path ) . DIRECTORY_SEPARATOR . ( $name ?: $this->_name ) . static::DEFAULT_CONFIG_SUFFIX;
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

}
