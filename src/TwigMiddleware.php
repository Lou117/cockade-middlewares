<?php
/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 08/07/2018
 * Time: 18:49
 */
namespace Lou117\Core\Middleware;

use \Twig_Environment;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Basic implementation for a middleware providing a Twig environment.
 *
 * When used with an implementation of RequestHandlerInterface that exposes a 'container' property (as Core
 * RequestHandler class do), Twig environment will be stored in container. Otherwise, Twig environment will be stored as
 * a request attribute.
 * @package Lou117\Core\Middleware
 */
class TwigMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    protected $settings = [
        "loader" => ["Twig_Loader_Filesystem", ["view"]],
        "environment_options" => []
    ];


    /**
     * @param array $middleware_settings
     */
    public function __construct(array $middleware_settings = [])
    {
        $this->settings = array_replace_recursive($this->settings, $middleware_settings);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $loaderClass = $this->settings["loader"][0];
        $loader = new $loaderClass(...$this->settings["loader"][1]);

        $twig = new Twig_Environment($loader, $this->settings["environment_options"]);

        if (
            property_exists($handler, "container")
            && $handler->container instanceof ContainerInterface
        ) {

            $handler->container->set("twig", $twig);

        } else {

            $request->withAttribute("twig", $twig);

        }

        return $handler->handle($request);
    }
}
