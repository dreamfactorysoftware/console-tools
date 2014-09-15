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

use DreamFactory\Library\Console\Components\Registry;
use DreamFactory\Library\Console\Interfaces\RegistryLike;
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
     * @type string The format to use when creating date strings
     */
    const DEFAULT_TIMESTAMP_FORMAT = 'c';
    /**
     * @type string The name of this application
     */
    const APP_NAME = null;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string A json file containing a template for creating a new registry
     */
    protected $_registryTemplate;
    /**
     * @type RegistryLike
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

        $this->_configure( $config );
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
     * Configure the command
     *
     * @param array $config Configuration settings
     */
    protected function _configure( array $config )
    {
        foreach ( $config as $_key => $_value )
        {
            if ( method_exists( $this, 'set' . $_key ) )
            {
                call_user_func( array($this, 'set' . $_key), $_value );
            }
        }

        if ( !empty( $this->_registryTemplate ) )
        {
            $this->_registry = new Registry( array(), $this->_registryTemplate );
        }
    }

    /**
     * @return string
     */
    public function getRegistryTemplate()
    {
        return $this->_registryTemplate;
    }

    /**
     * @param string $registryTemplate
     *
     * @return BaseApplication
     */
    public function setRegistryTemplate( $registryTemplate )
    {
        $this->_registryTemplate = $registryTemplate;

        return $this;
    }

    /**
     * @return RegistryLike
     */
    public function getRegistry()
    {
        return $this->_registry;
    }

    /**
     * @param RegistryLike $registry
     *
     * @return BaseApplication
     */
    public function setRegistry( RegistryLike $registry )
    {
        $this->_registry = $registry;

        return $this;
    }

}