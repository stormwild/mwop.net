<?php
namespace Mwop\Blog\Console;

use Mwop\Blog\DisplayPostMiddleware;
use Zend\Expressive\Router\RouterInterface;

class CachePostsFactory
{
    use RoutesTrait;

    public function __invoke($container)
    {
        // Ensure that routes are seeded for purposes of dispatching blog
        // posts.
        $this->seedRoutes($container->get(RouterInterface::class));

        // Create and return the cache posts middleware.
        return new CachePosts(
            $container->get(DisplayPostMiddleware::class)
        );
    }
}
