<?php

/*
 * This file is part of the P13 package.
 * 
 * (c) Wagner Sicca <wssicca@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace p13\core\session;

use \Exception;

/**
 * Exceção para problemas de $_SESSION
 *
 * @author Wagner Sicca <wssicca@gmail.com>
 * @namespace p13\core\session
 * @package p13\core\session
 */
class SessionException extends Exception
{

    /**
     * 
     * @param string $message Mensagem da exceção
     * @param int $code Código da exceção
     * @param Exception $previous
     */
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        if (is_null($message) ||
                is_int($message) && $message == PHP_SESSION_DISABLED
        ) {
            $message = 'As sessões estão desabilitadas em seu servidor';
        }
        parent::__construct($message, $code, $previous);
    }

}
