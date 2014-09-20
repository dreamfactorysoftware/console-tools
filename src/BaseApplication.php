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

use DreamFactory\Library\Console\Components\DataNode;
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
     * @type string The name of this application
     */
    const APP_NAME = null;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type RegistryLike
     */
    protected $_registry;
    /**
     * @type string The absolute path of the registry
     */
    protected $_registryPath;
    /**
     * @type string Absolute path to a JSON file to seed new registries
     */
    protected $_registryTemplate;

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
     * @param array $config
     */
    protected function _loadConfig( array $config = array() )
    {
        //  Set any of our variables first...
        foreach ( $config as $_key => $_value )
        {
            if ( method_exists( $this, 'set' . $_key ) )
            {
                $this->{'set' . $_key}( $_value );
                unset( $config[$_key] );
            }
        }
    }

    /**
     * Configure the command
     *
     * @param array $config Configuration settings
     *
     * @return void
     */
    protected function _configure( array $config )
    {
        $this->_loadConfig( $config );

        $_name = static::APP_NAME ?: ( isset( $argv, $argv[0] ) ? $argv[0] : basename( __CLASS__ ) );
        $_path = $this->_registryPath;

        $_registry = new Registry( $_name, $_path );

        //  Does this registry have a node for me?
        if ( false === ( $_key = $_registry->has( $_name ) ) )
        {
            //  Create one....
            $_registry->set( $_name, new DataNode( $_name, $config ) );
        }

        $this->_registry = $_registry->save();
    }

    /**
     * @return string
     */
    protected function _discoverRegistryPath()
    {
        return sys_get_temp_dir();
    }

    /**
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->_registry;
    }

    /**
     * @return string
     */
    public function getRegistryPath()
    {
        return $this->_registryPath;
    }

    /**
     * @param string $registryPath
     *
     * @return BaseApplication
     */
    public function setRegistryPath( $registryPath )
    {
        $this->_registryPath = $registryPath;

        return $this;
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

}