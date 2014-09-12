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

use DreamFactory\Library\Console\Components\ConfigFile;
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
    const COMMAND_NAME = null;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type ConfigFile The config file manager
     */
    protected $_config;
    /**
     * @type string The name/id of the config
     */
    protected $_configName;
    /**
     * @type string Path for the config file
     */
    protected $_configPath;
    /**
     * @type string The short application name
     */
    protected $_shortName;

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

        $_name = isset( $argv, $argv[0] ) ? $argv[0] : $this->getShortName();

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

        $this->_config = new ConfigFile( $this->_configName ?: $this->getName(), $this->_configPath );

    }

    /**
     * @return ConfigFile
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param ConfigFile $configFile
     *
     * @return BaseApplication
     */
    public function setConfig( ConfigFile $configFile )
    {
        $this->_config = $configFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getConfigName()
    {
        return $this->_configName;
    }

    /**
     * @return string
     */
    public function getConfigPath()
    {
        return $this->_configPath;
    }

    /**
     * @param string $configName
     *
     * @return BaseApplication
     */
    public function setConfigName( $configName )
    {
        $this->_configName = $configName;

        return $this;
    }

    /**
     * @param string $configPath
     *
     * @return BaseApplication
     */
    public function setConfigPath( $configPath )
    {
        $this->_configPath = $configPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return $this->_shortName;
    }

    /**
     * @param string $shortName
     *
     * @return BaseApplication
     */
    public function setShortName( $shortName )
    {
        $this->_shortName = $shortName;

        return $this;
    }

}