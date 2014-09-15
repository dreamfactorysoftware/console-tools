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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * A simple object to load and store application options
 */
class Registry extends ParameterBag implements RegistryLike
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The name of the directory containing our configuration
     */
    const DEFAULT_REGISTRY_BASE = '.dreamfactory';
    /**
     * @type string The name of the directory containing our configuration
     */
    const DEFAULT_REGISTRY_SUFFIX = '.registry.json';
    /**
     * @type string The format to use when creating date strings
     */
    const DEFAULT_TIMESTAMP_FORMAT = 'c';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Creates a new registry and loads from a JSON file
     *
     * @param string $filePath The absolute path to a JSON file
     * @param bool   $save     If true, the file will be saved before returning
     *
     * @throws \Exception
     * @throws \Kisma\Core\Exceptions\FileSystemException
     * @return RegistryLike
     */
    public static function createFromFile( $filePath, $save = false )
    {
        if ( empty( $templateFile ) || !file_exists( $templateFile ) || !is_readable( $templateFile ) )
        {
            throw new \InvalidArgumentException( 'The template file "' . $templateFile . '" is invalid.' );
        }

        $_jsonFile = new JsonFile( $templateFile );

        if ( $save )
        {
            $_jsonFile->write( $_data = $_jsonFile->read() );
        }

        return new static( $_data );
    }

    /**
     * Reads and loads a registry template
     *
     * @param string|array $templateFile
     */
    protected function _initializeContents( $templateFile )
    {
        if ( is_array( $templateFile ) )
        {
            $_template = $templateFile;
        }
        else
        {
            if ( false === ( $_template = json_decode( file_get_contents( $templateFile, 'r' ), true ) ) || JSON_ERROR_NONE != json_last_error() )
            {
                throw new \RuntimeException( 'The template file "' . $templateFile . '" is corrupt or does not contain valid JSON.' );
            }
        }

        $this->_registryTemplate = $templateFile;

        $this->add( $_template );
    }
}
