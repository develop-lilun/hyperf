<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

class RecordLogMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    private $logger;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    public function __construct(ContainerInterface $container, LoggerFactory $loggerFactory, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->request = $request;
        $this->response = $response;
        $this->logger = $loggerFactory->get('log', 'api-record');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger->info('request', array_merge(['path' => $this->request->getPathInfo(), 'method' => $this->request->getMethod()], $this->request->all()));
        $response =  $handler->handle($request);
        $this->logger->info('response', ['code' => $response->getStatusCode()]);
        return $response;
    }
}