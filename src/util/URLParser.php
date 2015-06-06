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
     * Array com os argumentos passados pela URL logo após o nome do método
     * @var array
     */
    private $args;

    /**
     * String de referência ao controller desejado
     * @var string
     */
    private $controllerName;

    /**
     *
     * @var Nome do host
     */
    private $host;

    /**
     * String que referencia o método de controller desejado
     * @var string
     */
    private $methodName;

    /**
     * String que referencia o nome do módulo desejado
     * @var string
     */
    private $moduleName;

    /**
     * Valor que vem logo após o # na URL
     * @var string
     */
    private $fragment;

    /**
     * Path do arquivo, a partir da raiz da aplicação,
     * que recebe todas as requisições
     * @var string
     * @link http://en.wikipedia.org/wiki/Front_Controller_pattern
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
     * Pedaço do path relativo ao recurso da aplicação que está sendo 
     * requisitado. Este pedaço vem depois de authority/host/port, do 
     * subdiretório no qual a aplicação está instalada e do front controller.
     * 
     * Ex.: teremos o $resourcePath = "teste/usuario" quando a url for igual a:
     * - http://localhost/intranet/public/index.php/teste/usuario
     * - http://localhost/intranet/teste/usuario
     * - http://localhost.intranet/public/index.php/teste/usuario
     * - http://localhost.intranet/teste/usuario
     * 
     * @var string
     */
    private $resourcePath;

    /**
     * Subdiretório no qual está instalada a aplicação
     * @var string
     */
    private $subDirectory;

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
     * URL usada para acessar a aplicação
     * @var string
     */
    private $url;

    /**
     * Nome de usuário informado na sessão "authority"
     * @var string
     */
    private $user;

    function __construct($url = null)
    {
        $this->setUrl($url);
        $this->setFrontController('public/index.php');
        $this->setScheme();
        $this->setHost();
        $this->setPort();
        $this->setUser();
        $this->setPass();
        $this->setQuery();
        $this->setFragment();
        $this->setPath();
        $this->setSubDirectory();
    }

    /**
     * Obtém um array com os argumentos passados via URL logo após o nome do 
     * método. 
     * ATENCIÓN: isso NÃO é um array da query string! Para obtê-lo,
     * utilize o método getQuery()
     * @return array
     */
    public function getArgs()
    {
        if (empty($this->args) && !is_null($this->getResourcePath())) {
            $resourcePath = !is_null($this->getMethodName()) ?
                    mb_substr($this->getResourcePath(), mb_strpos($this->getResourcePath(), $this->getMethodName()) + mb_strlen($this->getMethodName()) + 1) :
                    null;

            if (!empty($resourcePath)) {
                $filter = create_function('$a', 'return !empty($a);');
                $this->args = array_filter(explode('/', $resourcePath), $filter);
            }
        }
        return $this->args;
    }

    /**
     * 
     * @return string
     */
    public function getControllerName()
    {
        if (empty($this->controllerName) && !is_null($this->getResourcePath())) {
            $resourcePath = is_null($this->getModuleName()) ?
                    $this->getResourcePath() :
                    mb_substr($this->getResourcePath(), mb_strlen($this->getModuleName()) + 1);

            if (mb_strlen($resourcePath) > 0) {
                $this->controllerName = mb_ereg_match('.*/', $resourcePath) ?
                        mb_substr($resourcePath, 0, mb_strpos($resourcePath, '/')) :
                        $resourcePath;
            }
        }
        return $this->controllerName;
    }

    /**
     * Retorna o "fragment", ou seja, o que ve logo
     * após a # na URL
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Obtém o caminho para o front controller a partir da raiz da aplicação
     * @return string
     */
    public function getFrontController()
    {
        return $this->frontController;
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
     * Retorna o nome do método
     * @return string
     */
    public function getMethodName()
    {
        if (empty($this->methodName) && !is_null($this->getResourcePath())) {
            $resourcePath = is_null($this->getControllerName()) ?
                    null :
                    mb_substr($this->getResourcePath(), mb_strpos($this->getResourcePath(), $this->getControllerName()) + mb_strlen($this->getControllerName()) + 1);

            $methodName = mb_ereg_match('.*/', $resourcePath) ?
                    mb_substr($resourcePath, 0, mb_strpos($resourcePath, '/')) :
                    $resourcePath;

            $this->methodName = empty($methodName) ? null : $methodName;
        }
        return $this->methodName;
    }

    /**
     * Retorna o nome do module requisitado via URL
     * 
     * Como o primeiro parâmetro passado via URL pode ser tanto um module
     * quando um controller, eu verifico se este module existe na aplicação.
     * Trazudindo: só retorno um módulo caso o mesmo exista!
     * 
     * @return string
     */
    public function getModuleName()
    {
        if (empty($this->moduleName) && !is_null($this->getResourcePath())) {
            $moduleName = $this->getResourcePath();

            /* Extrai a primeira parte do que sobrou */
            if (mb_strlen($moduleName) > 0 && mb_ereg_match('.*/', $moduleName)) {
                $moduleName = mb_substr($moduleName, 0, mb_strpos($moduleName, '/'));
            }

            /* Verifico se o módulo existe */
            if (mb_strlen($moduleName) > 0) {
                $dirInfo = new SplFileInfo(MODULE_DIR . DIRECTORY_SEPARATOR . $moduleName);
                $this->moduleName = $dirInfo->isDir() ? $moduleName : null;
            }
        }

        return $this->moduleName;
    }

    /**
     * Retorna a senha descrita no "userinfo" da URL
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * Retorna o path (caminho após o domínio) completo
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Retorna a porta descrita na URL
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Retorna um array associativo com os dados da query string
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Obtém o pedaço do path relativo ao recurso da aplicação que está sendo 
     * solicitado, ou seja, o que vem depois do authority/host/porta, do
     * subdiretório no qual a aplicação está instalada e do front controller.
     * @return string
     */
    public function getResourcePath()
    {
        if (empty($this->resourcePath)) {
            $this->setResourcePath();
        }
        return $this->resourcePath;
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
     * Obs.: o subdiretório não é um path completo, e sim o caminho a partir do 
     * document root. Desta forma, ele não começa e nem termina com um 
     * DIRECTORY_SEPARATOR.
     * @return string
     */
    public function getSubDirectory()
    {
        return $this->subDirectory;
    }

    /**
     * Retorna a URL utilizada para acessar a aplicação
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Retorna o nome de usuário informado na URL
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Define o "fragment" com o valor que vem após a # na URL
     * @param string $fragment
     */
    public function setFragment($fragment = null)
    {
        $this->fragment = empty($fragment) ?
                parse_url($this->getUrl(), PHP_URL_FRAGMENT) :
                $fragment;
    }

    /**
     * Define o caminho para o front controller  a partir da raiz da aplicação
     * @param string $frontController
     */
    public function setFrontController($frontController)
    {
        $this->frontController = mb_ereg_replace(
                preg_quote(DIRECTORY_SEPARATOR), '/', $frontController);
    }

    /**
     * Define o hostname a partir da URL
     * @param string $host
     */
    public function setHost($host = null)
    {
        $this->host = empty($host) ?
                parse_url($this->getUrl(), PHP_URL_HOST) :
                $host;
    }

    /**
     * Define o "password" a partir do "userinfo" da URL
     * @param string $pass
     */
    public function setPass($pass = null)
    {
        $this->pass = empty($pass) ?
                parse_url($this->getUrl(), PHP_URL_PASS) :
                $pass;
    }

    /**
     * Define o path a partir da URL
     * @param string $path
     */
    public function setPath($path = null)
    {
        $this->path = empty($path) ?
                parse_url($this->getUrl(), PHP_URL_PATH) :
                $path;
    }

    /**
     * Define a porta a partir do "authority" da URL
     * @param int $port
     */
    public function setPort($port = null)
    {
        $this->port = empty($port) ?
                parse_url($this->getUrl(), PHP_URL_PORT) :
                $port;
    }

    /**
     * Obtém a query string da URL e a converte para um array associativo
     * @param array $query
     */
    public function setQuery($query = null)
    {
        if (empty($query)) {
            $queryString = parse_url($this->getUrl(), PHP_URL_QUERY);
            if (!empty($queryString)) {
                parse_str($queryString, $this->query);
            }
        } else if (is_array($query)) {
            $this->query = $query;
        }
    }

    /**
     * Define o resource path
     * @param string $resourcePath
     */
    public function setResourcePath($resourcePath = null)
    {
        if (empty($resourcePath)) {
            $resourcePath = mb_ereg_match('^/.*/$', $this->getPath()) ?
                    mb_substr($this->getPath(), 1, -1) :
                    mb_substr($this->getPath(), 1);

            /* Remove o subpath se o mesmo existir */
            if (mb_strlen($this->getSubDirectory()) > 0) {
                $resourcePath = mb_substr(
                        $resourcePath, mb_strlen(mb_ereg_replace(preg_quote(DIRECTORY_SEPARATOR), '/', $this->getSubDirectory())) + 1
                );
            }
            /* Remove o front controller se o mesmo existir */
            if (mb_ereg_match('.*' . preg_quote($this->getFrontController()), $resourcePath) === true) {
                $resourcePath = mb_substr(
                        $resourcePath, mb_strlen($this->getFrontController()) + 1);
            }
        }

        $this->resourcePath = empty($resourcePath) ? null : $resourcePath;
    }

    /**
     * Define o "scheme" a partir da URL
     * @param strig $scheme
     */
    public function setScheme($scheme = null)
    {
        $this->scheme = empty($scheme) ?
                parse_url($this->getUrl(), PHP_URL_SCHEME) :
                $scheme;
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
     * @param string $subPath
     */
    public function setSubDirectory($subPath = null)
    {
        if (!empty($subPath)) {
            $this->subDirectory = mb_ereg_replace(
                    '/', DIRECTORY_SEPARATOR, $subPath);
            return;
        }

        if (is_null($this->getPath())) {
            return;
        }

        if (mb_ereg($this->frontController, $this->getPath())) {
            $fcPos = mb_strpos($this->getPath(), $this->frontController);
            $this->subDirectory = $fcPos > 1 ?
                    mb_ereg_replace('/', DIRECTORY_SEPARATOR, mb_substr($this->getPath(), 1, $fcPos - 2)) :
                    null;
            return;
        }

        $filter = create_function('$a', 'return !empty($a);');
        $pathExplodido = array_filter(explode('/', $this->getPath()), $filter);
        if (empty($pathExplodido)) {
            return;
        }

        $documentRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
        $this->subDirectory = DIRECTORY_SEPARATOR;
        foreach ($pathExplodido as $subdiretorio) {
            $dirInfo = new SplFileInfo(
                    $documentRoot . $this->subDirectory . $subdiretorio);
            if ($dirInfo->isDir() === false) {
                break;
            }

            $frontControllerInfo = new SplFileInfo(
                    $dirInfo->getPathname() . DIRECTORY_SEPARATOR . $this->frontController);
            $this->subDirectory .= $subdiretorio . DIRECTORY_SEPARATOR;
            if ($frontControllerInfo->isFile()) {
                break;
            }
        }

        $frontControllerInfo = new SplFileInfo(
                $documentRoot . $this->subDirectory . $this->frontController);
        $this->subDirectory = $frontControllerInfo->isFile() ?
                mb_substr(mb_ereg_replace('/', DIRECTORY_SEPARATOR, $this->subDirectory), 1, -1) :
                null;
    }

    /**
     * Define a URL que será parseada. Se nenhum valor for informado,
     * o método tenta montar a URL a partir de $_SERVER com a sintaxe 
     * <scheme name> : <hierarchical part> [ ? <query> ] [ # <fragment> ]
     * 
     * @param string $url
     * @link http://tools.ietf.org/html/std66 Descrição da sintaxe genérica de um URI
     */
    public function setUrl($url = null)
    {
        if (empty($url)) {
            $scheme = filter_input(INPUT_SERVER, 'REQUEST_SCHEME');
            $host = filter_input(INPUT_SERVER, 'HTTP_HOST');
            $uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
            $url = "{$scheme}://{$host}{$uri}";
        }

        $this->url = $url;
    }

    /**
     * Define o usuário a partir do "userinfo" da URL
     * @param string $user
     */
    public function setUser($user = null)
    {
        $this->user = empty($user) ?
                parse_url($this->getUrl(), PHP_URL_USER) :
                $user;
    }

}
