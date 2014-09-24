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
namespace DreamFactory\Library\Console;

use DreamFactory\Library\Console\Components\Collection;
use DreamFactory\Library\Console\Components\Registry;
use Symfony\Component\Console\Application;

/**
 * A command that reads/writes a JSON configuration file
 *
 * Additional Settings of $configName and $configPath
 * available to customize storage location
 */
class BaseApplication extends Application
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string The name of this application
     */
    const APP_NAME = null;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type Registry settings
     */
    protected $_registry;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $name    The name of the application
     * @param string $version The version of the application
     * @param array  $config  Extra configuration settings
     */
    public function __construct( $name = null, $version = null, array $config = array() )
    {
        parent::__construct( $name, $version );

        $_config = new Collection( $config );

        $_path = $_config->get( 'registry-path', getcwd(), true );
        $_values = $_config->get( 'registry-values', array(), true );

        if ( null !== ( $_template = $_config->get( 'registry-template', null, true ) ) )
        {
            $this->_registry = Registry::createFromFile( static::APP_NAME, $_path, $_template, $_values );
            $this->_registry->load();
        }
        else
        {
            $this->_registry = new Registry( static::APP_NAME, $_path, $config );
        }
    }

    /** @inheritdoc */
    public function getLongVersion()
    {
        if ( 'UNKNOWN' === $this->getName() && 'UNKNOWN' === $this->getVersion() )
        {
            return parent::getLongVersion();
        }

        $_name = static::APP_NAME ?: ( isset( $argv, $argv[0] ) ? $argv[0] : basename( __CLASS__ ) );

        return sprintf( '<info>%s v%s:</info> %s</comment>', $_name, $this->getVersion(), $this->getName() );
    }

    /**
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->_registry;
    }

}