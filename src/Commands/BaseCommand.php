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
namespace DreamFactory\Library\Console\Commands;

use DreamFactory\Library\Console\BaseApplication;
use DreamFactory\Library\Console\Components\Registry;
use DreamFactory\Library\Console\Enums\AnsiCodes;
use DreamFactory\Library\Fabric\Queue\Components\FabricQueue;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Adds some additional functionality to the Command class
 */
class BaseCommand extends ContainerAwareCommand
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type InputInterface
     */
    protected $_input;
    /**
     * @type OutputInterface
     */
    protected $_output;
    /**
     * @type float
     */
    protected $_startTime;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $name   The name of the command
     * @param array  $config The command configuration
     */
    public function __construct( $name = null, array $config = array() )
    {
        parent::__construct( $name );

        //  Spin the config options and set any known values...
        foreach ( $config as $_key => $_value )
        {
            if ( method_exists( $this, 'set' . $_key ) )
            {
                call_user_func( array($this, 'set' . $_key), $_value );
            }
        }
    }

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     * @param int          $type     The type of output (one of the OUTPUT constants)
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    public function write( $messages, $newline = false, $type = OutputInterface::OUTPUT_NORMAL )
    {
        $this->_output->write( $messages, $newline, $type );
    }

    /**
     * Writes an escape sequence to the output.
     *
     * @param string $code   The code to output
     * @param int    $value1 Optional row #
     * @param int    $value2 Optional column #
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    public function writeCode( $code, $value1 = 1, $value2 = 1 )
    {
        $this->_output->write( AnsiCodes::render( $code, $value1, $value2 ) );
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @param int          $type     The type of output (one of the OUTPUT constants)
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    public function writeln( $messages, $type = OutputInterface::OUTPUT_NORMAL )
    {
        $this->write( $messages, true, $type );
    }

    /**
     * Writes a string to the output then shifts the cursor back to the beginning of the line
     *
     * @param string $message
     */
    public function writeInPlace( $message )
    {
        $this->writeCode( AnsiCodes::SCP );
        $this->write( $message );
        $this->writeCode( AnsiCodes::RCP );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize( InputInterface $input, OutputInterface $output )
    {
        parent::initialize( $input, $output );

        $this->_input = $input;
        $this->_output = $output;

        //  Mark the start time of the command
        $this->_startTime = microtime( true );
    }

    /**
     * @return float The elapsed time since the start of execution
     */
    protected function _elapsed()
    {
        return microtime( true ) - $this->_startTime;
    }

    /**
     * @return Registry
     */
    public function getRegistry()
    {
        /** @type BaseApplication $_app */
        $_app = $this->getApplication();

        if ( empty( $_app ) )
        {
            throw new \RuntimeException( 'The $application property has not been set for this command.' );
        }

        return $_app->getRegistry();
    }

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->_input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->_output;
    }

    /**
     * Gets the work queue
     *
     * @param null|string|array $configFile Either /path/to/config/file or array of config parameters or nada
     *
     * @return FabricQueue
     */
    public function getQueue( $configFile = null )
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->getApplication()->getQueue( $configFile );
    }
}