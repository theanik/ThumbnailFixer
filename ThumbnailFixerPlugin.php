<?php
class ThumbnailFixerPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = ['define_routes'];

    protected $_filters = ['admin_navigation_main'];


    public function hookDefineRoutes($args)
    {
        $router = $args['router'];
        $router->addRoute(
            'thumbnail_fixer_run',
            new Zend_Controller_Router_Route(
                'thumbnail-fixer/run',
                [
                    'module'     => 'thumbnail-fixer',
                    'controller' => 'index',
                    'action'     => 'run'
                ]
            )
        );

    }

    public function filterAdminNavigationMain($nav)
    {
        $nav[] = [
            'label' => __('Thumbnail Fixer'),
            'uri' => url('thumbnail-fixer/run')
        ];
        return $nav;
    }
}