<?php
/**
 * This file is part of the DreamFactory Console Tools Library
 * Copyright 2014 DreamFactory Software, Inc. <support@dreamfactory.com>
 *
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

use DreamFactory\Library\Utility\Exceptions\FileSystemException;

/**
 * Reads/writes a json file
 */
class JsonFile
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type int
     */
    const JSON_UNESCAPED_SLASHES = 64;
    /**
     * @type int
     */
    const JSON_PRETTY_PRINT = 128;
    /**
     * @type int
     */
    const JSON_UNESCAPED_UNICODE = 256;
    /**
     * @type int The default options for json_encode. This value is (JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE)
     */
    const DEFAULT_JSON_ENCODE_OPTIONS = 448;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The absolute path of our JSON file.
     */
    protected $_filePath;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Construct
     *
     * @param string       $filePath        The absolute path of the file, including the name.
     * @param array|object $defaultContents The contents to write to the file if being created
     */
    public function __construct( $filePath = null, $defaultContents = null )
    {
        $this->_filePath = static::ensureFileExists( dirname( $filePath ), basename( $filePath ), $defaultContents );
    }

    /**
     * Checks whether json file exists.
     *
     * @return bool
     */
    public function exists()
    {
        $_path = dirname( $this->_filePath );
        $_filePath = $this->_filePath;

        if ( !is_dir( $_path ) && false === @mkdir( $_path, 0777, true ) )
        {
            if ( file_exists( $_filePath ) )
            {
                throw new FileSystemException( $_path . ' exists but it is not a directory.' );
            }

            throw new FileSystemException( 'Cannot create directory "' . $_path . '" . ' );
        }

        return is_file( $this->_filePath );
    }

    /**
     * Reads the file
     *
     * @param bool $decoded If true (the default), the read data is decoded
     *
     * @throws FileSystemException
     * @return array|object
     */
    public function read( $decoded = true )
    {
        if ( !$this->exists() )
        {
            throw new FileSystemException( 'The file "' . $this->_filePath . '" does not exist.' );
        }

        if ( false === ( $_json = file_get_contents( $this->_filePath ) ) )
        {
            throw new FileSystemException( 'Unable to read JSON file "' . $this->_filePath . '".' );
        }

        return $decoded ? static::decode( $_json ) : $_json;
    }

    /**
     * Writes the file
     *
     * @param array|object $data        The unencoded data to store
     * @param int          $jsonOptions Options for json_encode. Default is static::DEFAULT_JSON_ENCODE_OPTIONS
     * @param int          $retries     The number of times to retry the write.
     * @param float|int    $retryDelay  The number of microseconds (100000 = 1s) to wait between retries
     *
     *
     * @throws FileSystemException
     * @throws \Exception
     */
    public function write( $data = array(), $jsonOptions = self::DEFAULT_JSON_ENCODE_OPTIONS, $retries = 3, $retryDelay = 500000 )
    {
        //  Try once at least!
        if ( empty( $retries ) )
        {
            $retries = 1;
        }

        $_encoded = static::encode( $data, $jsonOptions ) . ( $jsonOptions & static::JSON_PRETTY_PRINT ? PHP_EOL : null );

        while ( $retries-- )
        {
            try
            {
                if ( false === file_put_contents( $this->_filePath, $_encoded ) )
                {
                    throw new FileSystemException( 'Unable to write data to file "' . $this->_filePath . '".' );
                }

                break;
            }
            catch ( \Exception $_ex )
            {
                if ( $retries )
                {
                    usleep( $retryDelay );
                    continue;
                }

                throw $_ex;
            }
        }
    }

    /**
     * JSON encodes data
     *
     * @param  mixed $data        Data to encode
     * @param  int   $jsonOptions Options for json_encode. Default is static::DEFAULT_JSON_ENCODE_OPTIONS
     *
     * @return string Encoded json
     */
    public static function encode( $data, $jsonOptions = self::DEFAULT_JSON_ENCODE_OPTIONS )
    {
        if ( false === ( $_json = json_encode( $data, $jsonOptions ) ) || JSON_ERROR_NONE != json_last_error() )
        {
            throw new \InvalidArgumentException( 'The data could not be encoded: ' . json_last_error_msg() );
        }

        return $_json;
    }

    /**
     * Decodes a JSON string
     *
     * @param string $json The data to decode
     * @param bool   $asArray
     * @param int    $depth
     * @param int    $options
     *
     * @return mixed
     */
    public static function decode( $json, $asArray = true, $depth = 512, $options = 0 )
    {
        if ( false === ( $_data = json_decode( $json, $asArray, $depth, $options ) ) || JSON_ERROR_NONE != json_last_error() )
        {
            throw new \InvalidArgumentException( 'The data could not be decoded: ' . json_last_error_msg() );
        }

        return $_data;
    }

    /**
     * Decodes a JSON string
     *
     * @param string $file The absolute path to the file to decode
     * @param bool   $asArray
     * @param int    $depth
     * @param int    $options
     *
     * @return mixed
     */
    public static function decodeFile( $file, $asArray = true, $depth = 512, $options = 0 )
    {
        if ( !file_exists( $file ) || !is_readable( $file ) || false === ( $_json = file_get_contents( $file ) ) )
        {
            throw new \InvalidArgumentException( 'The file "' . $file . '" does not exist or cannot be read.' );
        }

        if ( false === ( $_data = json_decode( $_json, $asArray, $depth, $options ) ) || JSON_ERROR_NONE != json_last_error() )
        {
            throw new \InvalidArgumentException( 'The data could not be decoded: ' . json_last_error_msg() );
        }

        return $_data;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->_filePath;
    }

    /**
     * Makes sure the file passed it exists. Create default config and saves otherwise.
     *
     * @param string       $filePath        The absolute path of the file, including the name.
     * @param array|object $defaultContents The contents to write to the file if being created
     *
     * @throws FileSystemException
     * @return string The absolute path to the file
     */
    public function ensureFileExists( $filePath, $defaultContents = null )
    {
        $_path = dirname( $filePath );

        if ( !is_dir( $_path ) || false === @mkdir( $_path, 0777, true ) )
        {
            throw new FileSystemException( 'Unable to create directory: ' . $_path );
        }

        if ( !file_exists( $filePath ) )
        {
            if ( false === file_put_contents( $filePath, empty( $defaultContents ) ? '{}' : static::encode( $defaultContents ) ) )
            {
                throw new FileSystemException( 'Unable to create file: ' . $filePath );
            }
        }

        //  Exists
        return $filePath;
    }

}