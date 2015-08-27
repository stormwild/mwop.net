<?php
namespace Mwop\Template;

use InvalidArgumentException;
use Phly\Mustache\Resolver\DefaultResolver;
use Phly\Mustache\Exception\InvalidTemplatePathException;
use SplStack;

/**
 * Extension to the phly_mustache default resolver providing namespaced
 * template resolution.
 */
class NamespacedMustacheResolver extends DefaultResolver
{
    const DEFAULT_NAMESPACE = '__DEFAULT__';

    /**
     * @var SplStack[]
     */
    private $paths = [];

    /**
     * Set or add a path to a given namespace.
     *
     * @param  mixed templatePath
     * @return self
     * @throws InvalidTemplatePathException
     */
    public function setTemplatePath($templatePath, $namespace = null)
    {
        if (!is_dir($templatePath)) {
            throw new InvalidTemplatePathException(sprintf(
                '%s expects a valid path to a directory; received "%s"',
                __METHOD__,
                $templatePath
            ));
        }

        if (null !== $namespace && ! is_string($namespace)) {
            throw new InvalidArgumentException('Namespace must be a string');
        }

        $namespace = $namespace ?: self::DEFAULT_NAMESPACE;

        $templatePath = rtrim((string) $templatePath, '/\\');
        $this->getTemplatePath($namespace)->push($templatePath);

        return $this;
    }

    /**
     * Retrieve the paths for the given namespace.
     *
     * @param null|string $namespace Defaults to DEFAULT_NAMESPACE when null
     * @return SplStack
     * @throws InvalidArgumentException for invalid namespace values.
     */
    public function getTemplatePath($namespace = null)
    {
        if (null !== $namespace && ! is_string($namespace)) {
            throw new InvalidArgumentException('Namespace must be a string');
        }

        $namespace = $namespace ?: self::DEFAULT_NAMESPACE;

        if (! array_key_exists($namespace, $this->paths)) {
            $this->paths[$namespace] = new SplStack;
        }

        return $this->paths[$namespace];
    }

    /**
     * Return a list of registered namespaces.
     *
     * Only returns those that have paths registered to them.
     *
     * @return string[]
     */
    public function getNamespaces()
    {
        $namespaces = [];

        foreach ($this->paths as $namespace => $paths) {
            if (count($paths)) {
                $namespaces[] = $namespace;
            }
        }

        return $namespaces;
    }

    /**
     * Resolve a template to its file
     *
     * Can accept either a bare template name, or a name in the format
     * `namespace::template`; if the latter, it looks first in the paths for
     * that given namespace, and then in the default namespace if none is
     * found.
     *
     * @param  string $template
     * @return false|string Returns false if unable to resolve the template to a path
     */
    public function resolve($template)
    {
        $namespace = self::DEFAULT_NAMESPACE;
        if (preg_match('#^(?P<namespace>[^:]+)::(?P<template>.*)$#', $template, $matches)) {
            $namespace = $matches['namespace'];
            $template  = $matches['template'];
        }

        $segments = explode($this->getSeparator(), $template);
        $template = implode('/', $segments) . $this->getSuffix();

        $path = $this->fetchTemplateForNamespace($template, $namespace);

        if ($path !== false || $namespace === self::DEFAULT_NAMESPACE) {
            return $path;
        }

        return $this->fetchTemplateForNamespace($template, self::DEFAULT_NAMESPACE);
    }

    /**
     * Attempt to retrieve a template for a given namespace.
     *
     * @param string $template
     * @param string $namespace
     * @return false|string False on failure to resolve, string path otherwise.
     */
    private function fetchTemplateForNamespace($template, $namespace)
    {
        foreach ($this->getTemplatePath($namespace) as $path) {
            if (! empty($path)) {
                $path .= '/';
            }

            $filename = $path . $template;
            if (file_exists($filename)) {
                return file_get_contents($filename);
            }
        }

        return false;
    }
}
