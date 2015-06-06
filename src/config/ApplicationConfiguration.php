<?php

/*
 * This file is part of the P13 package.
 *
 * (c) Wagner Sicca <wssicca@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace p13\core\config;

use \ReflectionClass;
use p13\core\util\StringHandler;

/**
 * Classe abstrata para definição de configuração de aplicação.
 * Uma ou mais abstrações desta classe devem estar presentes 
 * no diretório "app/config/application" da aplicação, senão dá zica
 *
 * @author Wagner Sicca <wssicca@gmail.com>
 * @namespace p13\core\config
 * @package p13\core\config
 */
class ApplicationConfiguration extends AbstractConfiguration
{

    /**
     * Indica o modo de autenticação. O valor definido deve ser o "short name"
     * de uma implementação de p13\core\authentication\AuthenticationInterface
     * localizada em app\util\authentication.
     * @var string
     */
    public $authenticationClass = null;

    /**
     * Controller que deve ser instanciado caso nenhum controller tenha
     * sido informado na URL e o usuário não esteja autenticado
     * @var string
     */
    public $defaultController = 'default';

    /**
     * Controller que deve ser instanciado caso nenhum controller tenha
     * sido informado na URL e o usuário esteja autenticado
     * @var string
     */
    public $defaultControllerLogado = 'default';

    /**
     * "Short name" da classe com a configuração de banco de dados. Essa classe
     * deve ser uma implementação de p13\core\config\AbstractDatabaseConfiguration
     * e deve estar em app/config/database
     * @var string
     */
    public $defaultDatabaseConfig = null;

    /**
     * Nome do layout que será utilizado pela camada View na aplicação.
     * Esse layout deve estar definido em app/view/layouts/<nome_do_layout>
     * @var string
     */
    public $defaultLayout = 'default';

    /**
     * Nome do layout que será utilizado pela camada View na aplicação.
     * Esse layout deve estar definido em app/view/layouts/<nome_do_layout>
     * @var string
     */
    public $defaultLayoutLogado = 'default';

    /**
     * Indica qual action (método de um controller) deve ser chamado caso 
     * nenhum tenha sido informado na URL e o usuário não esteja autenticado
     * @var string
     */
    public $defaultMethod = 'index';

    /**
     * Indica qual action (método de um controller) deve ser chamado caso 
     * nenhum tenha sido informado na URL e o usuário esteja autenticado
     * @var string
     */
    public $defaultMethodLogado = 'index';

    /**
     * Nome do módulo que será carregado por default caso nenhum seja informado
     * @var string
     */
    public $defaultModule = null;

    /**
     * Nome do ódulo que será carregado por padrão quando nenhum for
     * informado e o usuário estiver autenticado
     * @var string
     */
    public $defaultModuleLogado = null;

    /**
     *
     * @var ApplicationConfiguration
     */
    static protected $instance;

    /**
     * Indica se a aplicação utilizará o recurso de controle
     * de acesso a recursos do sistema
     * @var boolean 
     */
    public $useAcl = false;

    /**
     * Retorna uma instância da classe de configuração ativa. Se houver uma
     * classe de configuração com o nome do APPLICATION_ENV
     * 
     * @return ApplicationConfiguration
     */
    final public static function getInstance()
    {
        if (defined('APPLICATION_ENV') && !empty(APPLICATION_ENV)) {
            $class_name = 'app\\config\\application\\' .
                    StringHandler::underscoreParaPascalCase(APPLICATION_ENV);
        }
        if (isset($class_name) && class_exists($class_name)) {
            $reflection = new ReflectionClass($class_name);
            if ($reflection->isSubclassOf(__CLASS__)) {
                return new $class_name;
            }
        }
        return new self;
    }

}
