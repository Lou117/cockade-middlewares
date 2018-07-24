<?php
/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 08/07/2018
 * Time: 19:09
 */
namespace Lou117\Core\Middleware;

use \PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Basic implementation for a middleware providing a MySQL connection.
 *
 * When used with an implementation of RequestHandlerInterface that exposes a 'container' property (as Core
 * RequestHandler class do), MySQL connection will be stored in container. Otherwise, MySQL connection will be stored as
 * a request attribute.
 * @package Lou117\Core\Middleware
 */
class MysqlConnectionMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    protected $settings = [
        "server" => "localhost",
        "database" => "tmp",
        "username" => "root",
        "password" => "root",
        "silent" => true
    ];


    /**
     *
     */
    public function __construct(array $middleware_settings)
    {
        $this->settings = array_replace_recursive($this->settings, $middleware_settings);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $mysql = new PDO(
            "mysql:host={$this->settings["server"]};dbname={$this->settings["database"]}",
            $this->settings["username"],
            $this->settings["password"],
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => $this->settings["silent"] ? PDO::ERRMODE_SILENT : PDO::ERRMODE_EXCEPTION
            ]
        );

        if (
            property_exists($handler, "container")
            && $handler->container instanceof ContainerInterface
        ) {

            $handler->container->set("mysql", $mysql);

        } else {

            $request->withAttribute("mysql", $mysql);

        }

        return $handler->handle($request);
    }
}
