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

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * A simple registry
 */
class AppRegistry extends ParameterBag
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
    //* Methods
    //******************************************************************************

    /**
     * @inheritdoc
     */
    public function __construct( array $parameters = array() )
    {
        parent::__construct( $parameters );

        //  Initialize if empty
        if ( !count( $this->all() ) )
        {
            $this->add( $this->_initializeContents() );
        }
    }

    /**
     * Static factory method
     *
     * @param string $fileName
     *
     * @return bool|static
     */
    public static function createFromFile( $fileName )
    {
        if ( false === ( $_config = json_decode( file_get_contents( $fileName ), true ) ) || JSON_ERROR_NONE != json_last_error() )
        {
            return false;
        }

        return new static( $_config );
    }

    /**
     * Returns the contents of this registry in JSON
     *
     * @return string
     */
    public function allJson()
    {
        return json_encode( parent::all(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
    }

    /**
     * @return array The default registry values
     */
    protected function _initializeContents()
    {
        $_template = file_get_contents( dirname( dirname( __DIR__ ) . '/registry.schema.json' ) );

        return array_merge(
            $_template,
            array(
                '_comments'  => array(),
                '_timestamp' => date( 'Ymd HiS' ),
            )
        );
    }
}
