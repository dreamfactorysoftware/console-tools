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
use DreamFactory\Library\Console\Interfaces\ConfigFileLike;
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
     * @type ConfigFileLike
     */
    protected $_config;

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
     *
     * @return void
     */
    protected function _configure( array $config )
    {
        $_name = $this->getName() ?: ( static::APP_NAME ?: ( isset( $argv, $argv[0] ) ? $argv[0] : basename( __CLASS__ ) ) );

        $this->_config = new ConfigFile( $_name, null );

        $_config = $this->_config->load();
        $_apps = array();

        if ( is_array( $_config ) && array_key_exists( 'app', $_config ) )
        {
            $_apps = $_config['app'];
        }

        $_apps[$this->getName()] = $config;

        $this->_config->set( 'app', $_apps )->save();
    }

    /**
     * @return ConfigFileLike
     */
    public function getConfig()
    {
        return $this->_config;
    }

}