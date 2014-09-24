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
namespace DreamFactory\Tools\Fabric\Components;

use DreamFactory\Library\Console\Bags\GenericBag;
use DreamFactory\Library\Console\Components\JsonFile;
use DreamFactory\Library\Console\Components\MetaDataBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * A settings manager for the fabric cli
 */
class FabricSettings
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type GenericBag
     */
    protected $_settings;
    /**
     * @type MetaDataBag
     */
    public $metadata;
    /**
     * @type ParameterBag
     */
    public $appServers;
    /**
     * @type ParameterBag
     */
    public $webServers;
    /**
     * @type ParameterBag
     */
    public $dbServers;

    /**
     * Initializes the settings object
     *
     * @param array $appServers
     * @param array $dbServers
     * @param array $webServers
     * @param array $metadata
     */
    public function initialize( array $appServers = array(), array $dbServers = array(), array $webServers = array(), array $metadata = array() )
    {
        $this->_settings['servers.app'] = new ParameterBag( 'servers.app', $appServers );
        $this->_settings['servers.app'] = new ParameterBag( 'servers.db', $dbServers );
        $this->_settings['servers.app'] = new ParameterBag( 'servers.web', $webServers );
        $this->_settings['servers.app'] = new MetaDataBag( '_metadata', $metadata );
    }

    /**
     * Creates a new object based on a file
     *
     * @param string $file /path/to/file/with/settings.json
     *
     * @return FabricSettings
     *
     * @api
     */
    public static function createFromFile( $file )
    {
        if ( !file_exists( $file ) || !is_readable( $file ) )
        {
            throw new \InvalidArgumentException( 'The file "' . $file . '" does not exist or cannot be read.' );
        }

        $_settings = new FabricSettings();

        $_json = new JsonFile( $file );
        $_data = $_json->read();

        foreach ( $_data as $_key => $_value )
        {
            switch ( $_key )
            {
                case 'servers.app':
                    $_settings->appServers = $_value;
                    break;

                case 'servers.db':
                    $_settings->dbServers = $_value;
                    break;

                case 'servers.web':
                    $_settings->webServers = $_value;
                    break;

                case '_metadata':
                    $_settings->metadata = $_value;
                    break;

                default:
                    $_settings->{$_key} = $_value;
                    break;
            }
        }

        return $_settings;
    }

}
