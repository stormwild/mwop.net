<?php
namespace Mwop\Factory;

use Interop\Container\ContainerInterface;
use Mwop\Blog\EntryView;
use Phly\Expressive\Mustache\MustacheTemplate;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

class UriTemplateDelegatorFactory implements DelegatorFactoryInterface
{
    public function __invoke(ContainerInterface $contaier, $requestedName, callable $callback, array $options = null)
    {
        $renderer = $callback();

        if (! $renderer instanceof MustacheTemplate) {
            return $renderer;
        }

        $renderer->attachParamListener(function ($vars, array $defaults) {
            if (! $vars instanceof EntryView) {
                return;
            }

            foreach ($defaults as $key => $value) {
                if ($key === 'uri') {
                    $vars->uriHelper = $value;
                    continue;
                }
                $vars->{$key} = $value;
            }

            return $vars;
        });

        return $renderer;
    }
}
