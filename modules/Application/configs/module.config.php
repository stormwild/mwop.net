<?php
$config = array();
$config['production'] = array(
    'bootstrap_class'    => 'Application\Bootstrap',

    'authentication' => array(
        'Application\Controller\PageController' => array(
            'comics',
        ),
    ),

    'display_exceptions' => false,
    'layout'             => 'layout.phtml',
    'disqus' => array(
        'key'         => 'phlyboyphly',
        'development' => 0,
    ),

    'di' => array( 'instance' => array(
        'alias' => array(
            'view'          => 'Zend\View\PhpRenderer',
            'view-resolver' => 'Zend\View\TemplatePathStack',
            'view-broker'   => 'Zend\View\HelperBroker',
            'view-loader'   => 'Zend\View\HelperLoader',
        ),

        'view-loader' => array('parameters' => array(
            'map' => array(
                'url'    => 'Application\View\Helper\Url',
                'disqus' => 'Application\View\Helper\Disqus',
            ),
        )),

        'view-broker' => array('parameters' => array(
            'loader' => 'view-loader',
        )),

        'view' => array( 'parameters' => array(
            'resolver' => 'view-resolver',
            'broker'   => 'view-broker',
        )),

        'view-resolver' => array('parameters' => array(
            'paths' => array(
                'application' => __DIR__ . '/../views',
            ),
        )),
    )),

    'routes' => array(
        'default' => array(
            'type' => 'Zend\Mvc\Router\Http\Regex',
            'options' => array(
                'regex' => '/.*',
                'defaults' => array(
                    'controller' => 'Application\Controller\PageController',
                    'action'     => '404',
                ),
            ),
        ),
        'home' => array(
            'type'    => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route' => '/',
                'defaults' => array(
                    'controller' => 'Application\Controller\PageController',
                    'action'     => 'home',
                ),
            ),
        ),
        'comics' => array(
            'type'    => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route' => '/comics',
                'defaults' => array(
                    'controller' => 'Application\Controller\PageController',
                    'action'     => 'comics',
                ),
            ),
        ),
        'resume' => array(
            'type'    => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route' => '/resume',
                'defaults' => array(
                    'controller' => 'Application\Controller\PageController',
                    'action'     => 'resume',
                ),
            ),
        ),
    ),
);

$config['staging']     = $config['production'];

$config['testing']     = $config['production'];
$config['testing']['display_exceptions']    = true;

$config['development'] = $config['production'];
$config['development']['disqus']['key']         = "testphlyboyphly";
$config['development']['disqus']['development'] = 1;
$config['development']['display_exceptions']    = true;
return $config;
