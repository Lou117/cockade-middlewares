<?php
/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 14/06/2018
 * Time: 20:54
 */
namespace Lou117\Core\Middleware;

use \Throwable;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Basic implementation for a error handling middleware formatting any Throwable to a text response with HTTP code 500.
 *
 * PSR-15 specification: "It is RECOMMENDED that any application using middleware include a component that catches
 * exceptions and converts them into responses. This middleware SHOULD be the first component executed and wrap all
 * further processing to ensure that a response is always generated."
 *
 * Please note that this implementation will ALWAYS return a ready-to-use text response, which can be problematic if
 * incoming HTTP request Accept header doesn't allow for "text/plain" Content-Type (a.k.a. content negotiation).
 * @package Lou117\Core\Middleware
 */
class ErrorHandlingTextMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {

            return $handler->handle($request);

        } catch (Throwable $e) {

            $response = new Response();
            return $response
                ->withStatus(500)
                ->withHeader("Content-Type", "text/plain")
                ->withBody(stream_for((string) $e));

        }
    }
}
