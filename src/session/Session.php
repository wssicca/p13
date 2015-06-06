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

use p13\core\exception\SessionException;

/**
 * Classe com alguns métodos que intermediam a chamada direta 
 * a funções nativas de acesso à sessão
 *
 * @author Wagner Sicca <wssicca@gmail.com>
 * @namespace p13\core\session
 * @package p13\core\session
 */
final class Session
{

    function __construct()
    {
        if ($this->status() == PHP_SESSION_DISABLED) {
            throw new SessionException();
        }
    }

    /**
     * Exclui o cookie da sessão, limpa os dados do array
     * e destrói a sessão no servidor
     * @return boolean
     */
    public function destroy()
    {
        // Inicia a sessão caso ela não esteja inicializada
        if ($this->status() == PHP_SESSION_NONE && $this->start() == false) {
            return false;
        }

        // Exclui o cookie da sessão
        $cookie = filter_input(INPUT_COOKIE, $this->name());
        if (!empty($cookie) &&
                setcookie($this->name(), '', time() - 3600, '/') === false
        ) {
            return false;
        }

        // Esvazia o array da sessão
        session_unset();

        // Destrói a sessão
        return session_destroy();
    }

    /**
     * Define ou obtém o nome da sessão. Se $name for informado,
     * o nome da sessão é definido com o seu valor. Se não, é
     * retornado o nome gerado automaticamente para a sessão
     * @param string $name
     * @return string
     */
    public function name($name = null)
    {
        return session_name($name);
    }

    /**
     * Inicia a sessão com o nome informado
     * @param string $session_name
     * @return boolean
     */
    public function start($session_name = null)
    {
        if (!empty($session_name)) {
            session_name($session_name);
        }

        return session_start();
    }

    /**
     * Retorna o código de status da função. Pode ser 0 se as sessões não
     * estão habilitadas no servidor, 1 se o recurso existe mas a sessão
     * não foi iniciada ou 2 se o recurso existe e a sessão foi iniciada.
     * @return int
     */
    public function status()
    {
        return session_status();
    }

}
