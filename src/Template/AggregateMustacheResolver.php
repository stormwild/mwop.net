<?php
namespace Mwop\Template;

use Countable;
use IteratorAggregate;
use Phly\Mustache\Resolver\ResolverInterface;
use Zend\Stdlib\PriorityQueue;

class AggregateMustacheResolver implements Countable, IteratorAggregate, ResolverInterface
{
    /**
     * @var PriorityQueue
     */
    private $queue;

    /**
     * Constructor.
     *
     * Creates the internal priority queue.
     */
    public function __construct()
    {
        $this->queue = new PriorityQueue();
    }

    /**
     * Return count of attached resolvers
     *
     * @return int
     */
    public function count()
    {
        return $this->queue->count();
    }

    /**
     * IteratorAggregate: return internal iterator.
     *
     * @return PriorityQueue
     */
    public function getIterator()
    {
        return $this->queue;
    }

    /**
     * Attach a resolver
     *
     * @param  ResolverInterface $resolver
     * @param  int $priority
     * @return self
     */
    public function attach(ResolverInterface $resolver, $priority = 1)
    {
        $this->queue->insert($resolver, $priority);
        return $this;
    }

    /**
     * Resolve a template name to a resource the renderer can consume.
     *
     * @param  string $template
     * @return false|string
     */
    public function resolve($template)
    {
        foreach ($this->queue as $resolver) {
            $resource = $resolver->resolve($name);
            if (false !== $resource) {
                return $resource;
            }
        }

        return false;
    }
}
