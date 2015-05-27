<?php

/*
 * This file is part of the P13 package.
 * 
 * (c) Wagner Sicca <wagnersicca@ifsul.edu.br>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace p13\core\util;

use \SplFileInfo;

/**
 * Classe que faz o parse de URLs para o P13 Framework, obtendo,
 * a partir da URL, o recurso da aplicação que está sendo requisitado
 *
 * @author Wagner Sicca <wagnersicca@ifsul.edu.br>
 * @namespace p13\core\util
 * @package p13\core\util
 */
class URLParser
{

    /**
     * String de referência ao controller desejado
     * @var string
     */
    private $controller;

    /**
     *
     * @var Nome do host
     */
    private $host;

    /**
     * String que referencia o método de controller desejado
     * @var string
     */
    private $method;

    /**
     * String que referencia o nome do módulo desejado
     * @var string
     */
    private $module;

    /**
     * Valor que vem logo após o # na URL
     * @var string
     */
    private $fragment;

    /**
     * Path do arquivo, a partir da raiz da aplicação,
     * que recebe todas as requisições
     * @var string
     */
    private $frontController;

    /**
     * Senha passada na sessão "authority" da URL
     * @var string
     */
    private $pass;

    /**
     * Caminho completo após o nome do host
     * @var string
     */
    private $path;

    /**
     * Subdiretório no qual está instalada a aplicação
     * @var string
     */
    private $subPath;

    /**
     * Porta de acesso à aplicação
     * @var int 
     */
    private $port;

    /**
     * Array com os dados enviados pela query string
     * @var array
     */
    private $query;

    /**
     * Nome do scheme
     * @var string
     */
    private $scheme;

    /**
     * Nome de usuário informado na sessão "authority"
     * @var string
     */
    private $user;

    function __construct($url)
    {
        $this->frontController = 'public' . DIRECTORY_SEPARATOR . 'index.php';

        $this->setScheme($url);
        $this->setHost($url);
        $this->setPort($url);
        $this->setUser($url);
        $this->setPass($url);
        $this->setQuery($url);
        $this->setFragment($url);
        $this->setPath($url);
        $this->setSubPath($url);
    }

    /**
     * Retorna o "fragment", ou seja, o que ve logo
     * após a # na URL
     * @return string
     */
    function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Retorna o nome do host
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Retorna a senha descrita no "userinfo" da URL
     * @return string
     */
    function getPass()
    {
        return $this->pass;
    }

    /**
     * Retorna o path (caminho após o domínio) completo
     * @return string
     */
    function getPath()
    {
        return $this->path;
    }

    /**
     * Retorna a porta descrita na URL
     * @return int
     */
    function getPort()
    {
        return $this->port;
    }

    /**
     * Retorna um array associativo com os dados da query string
     * @return array
     */
    function getQuery()
    {
        return $this->query;
    }

    /**
     * Retorna o scheme da URL
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Retorna o subdiretório no qual está instalada a aplicação
     * @return string
     */
    function getSubPath()
    {
        return $this->subPath;
    }

    /**
     * Obtém o nome de usuário informado na URL
     * @return string
     */
    function getUser()
    {
        return $this->user;
    }

    /**
     * Define o "fragment" com o valor que vem após a # na URL
     * @param string $url
     */
    function setFragment($url)
    {
        $this->fragment = parse_url($url, PHP_URL_FRAGMENT);
    }

    /**
     * Define o hostname a partir da URL
     * @param string $url
     */
    public function setHost($url)
    {
        $this->host = parse_url($url, PHP_URL_HOST);
    }

    /**
     * Define o "password" a partir do "userinfo" da URL
     * @param string $url
     */
    function setPass($url)
    {
        $this->pass = parse_url($url, PHP_URL_PASS);
    }

    /**
     * Define o path a partir da URL
     * @param string $url
     */
    function setPath($url)
    {
        $this->path = parse_url($url, PHP_URL_PATH);
    }

    /**
     * Define a porta a partir do "authority" da URL
     * @param string $url
     */
    function setPort($url)
    {
        $port = parse_url($url, PHP_URL_PORT);
        $this->port = !empty($port) ? (int) $port : null;
    }

    /**
     * Obtém a query string da URL e a converte para um array associativo
     * @param string $url
     */
    function setQuery($url)
    {
        $queryString = parse_url($url, PHP_URL_QUERY);
        if (!empty($queryString)) {
            parse_str($queryString, $this->query);
        }
    }

    /**
     * Define o "scheme" a partir da URL
     * @param strig $url
     */
    function setScheme($url)
    {
        $this->scheme = parse_url($url, PHP_URL_SCHEME);
    }

    /**
     * Define o subdiretório de document root no qual está instalada a aplicação
     * 
     * Se o path estiver vazio, isso significa se a aplicação está sendo
     * rodada na raiz de seu host ou virtual host e, portanto, não há 
     * subdiretório.
     * 
     * Se o acesso for feito citando o front controller explicitamente,
     * podemos afirmar que o pedaço do path que vem antes da referência
     * ao front controller é o subdiretório no qual está a aplicação
     * 
     * Se não houver referência explícita ao front controller, devemos usar
     * uma "artimanha" pra descobrir qual o subdiretório na qual a aplicação
     * está. A jogada é percorrer o path e verificar se o pedaço da vez
     * é um subdiretório e se possui dentro dele o front controller (assim
     * eliminanos problemas de diretórios e controllers/modules com 
     * o mesmo nome).
     * 
     * @param string $url
     */
    function setSubPath($url)
    {
        if (is_null($this->getPath())) {
            return;
        }

        if (mb_ereg('public/index.php', $this->getPath())) {
            $this->subPath = mb_ereg_replace(
                    '/', DIRECTORY_SEPARATOR, mb_substr(
                            $this->getPath(), 1, mb_strpos(
                                    $this->getPath(), 'public/index.php') - 2
                    )
            );
            return;
        }

        $filter = create_function('$a', 'return !empty($a);');
        $pathExplodido = array_filter(explode('/', $this->getPath()), $filter);
        if (empty($pathExplodido)) {
            return;
        }

        $documentRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
        $this->subPath = DIRECTORY_SEPARATOR;
        foreach ($pathExplodido as $subdiretorio) {
            $dirInfo = new SplFileInfo(
                    $documentRoot . $this->subPath . $subdiretorio);
            if ($dirInfo->isDir() === false) {
                break;
            }

            $frontControllerInfo = new SplFileInfo(
                    $dirInfo->getPathname() . DIRECTORY_SEPARATOR . $this->frontController);
            $this->subPath .= $subdiretorio . DIRECTORY_SEPARATOR;
            if ($frontControllerInfo->isFile()) {
                break;
            }
        }

        $frontControllerInfo = new SplFileInfo(
                $documentRoot . $this->subPath . $this->frontController);
        $this->subPath = $frontControllerInfo->isFile() ?
                mb_substr(mb_ereg_replace('/', DIRECTORY_SEPARATOR, $this->subPath), 1, -1) :
                null;
    }

    /* Troca os separadores da URL por separadores de diretório do SO */

    /**
     * Define o usuário a partir do "userinfo" da URL
     * @param string $url
     */
    function setUser($url)
    {
        $this->user = parse_url($url, PHP_URL_USER);
    }

}
