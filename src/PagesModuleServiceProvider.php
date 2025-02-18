<?php namespace Anomaly\PagesModule;

use Anomaly\PagesModule\Page\Contract\PageInterface;
use Anomaly\PagesModule\Page\Contract\PageRepositoryInterface;
use Anomaly\PagesModule\Page\PageCollection;
use Anomaly\Streams\Platform\Addon\AddonServiceProvider;
use Illuminate\Routing\Router;

/**
 * Class PagesModuleServiceProvider
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 * @package       Anomaly\PagesModule
 */
class PagesModuleServiceProvider extends AddonServiceProvider
{

    /**
     * The addon plugins.
     *
     * @var array
     */
    protected $plugins = [
        'Anomaly\PagesModule\PagesModulePlugin'
    ];

    /**
     * The class bindings.
     *
     * @var array
     */
    protected $bindings = [
        'Anomaly\Streams\Platform\Model\Pages\PagesPagesEntryModel' => 'Anomaly\PagesModule\Page\PageModel',
        'Anomaly\Streams\Platform\Model\Pages\PagesTypesEntryModel' => 'Anomaly\PagesModule\Type\TypeModel'
    ];

    /**
     * The singleton bindings.
     *
     * @var array
     */
    protected $singletons = [
        'Anomaly\PagesModule\Page\Contract\PageRepositoryInterface' => 'Anomaly\PagesModule\Page\PageRepository',
        'Anomaly\PagesModule\Type\Contract\TypeRepositoryInterface' => 'Anomaly\PagesModule\Type\TypeRepository'
    ];

    /**
     * The addon routes.
     *
     * @var array
     */
    protected $routes = [
        'admin/pages'                                                => 'Anomaly\PagesModule\Http\Controller\Admin\PagesController@index',
        'admin/pages/create'                                         => 'Anomaly\PagesModule\Http\Controller\Admin\PagesController@create',
        'admin/pages/edit/{id}'                                      => 'Anomaly\PagesModule\Http\Controller\Admin\PagesController@edit',
        'admin/pages/view/{id}'                                      => 'Anomaly\PagesModule\Http\Controller\Admin\PagesController@view',
        'admin/pages/delete/{id}'                                    => 'Anomaly\PagesModule\Http\Controller\Admin\PagesController@delete',
        'admin/pages/types'                                          => 'Anomaly\PagesModule\Http\Controller\Admin\TypesController@index',
        'admin/pages/types/create'                                   => 'Anomaly\PagesModule\Http\Controller\Admin\TypesController@create',
        'admin/pages/types/edit/{id}'                                => 'Anomaly\PagesModule\Http\Controller\Admin\TypesController@edit',
        'admin/pages/types/assignments/{id}'                         => 'Anomaly\PagesModule\Http\Controller\Admin\TypesController@fields',
        'admin/pages/types/assignments/{id}/assign/{field}'          => 'Anomaly\PagesModule\Http\Controller\Admin\TypesController@assign',
        'admin/pages/types/assignments/{id}/assignment/{assignment}' => 'Anomaly\PagesModule\Http\Controller\Admin\TypesController@assignment',
        'admin/pages/fields'                                         => 'Anomaly\PagesModule\Http\Controller\Admin\FieldsController@index',
        'admin/pages/fields/choose'                                  => 'Anomaly\PagesModule\Http\Controller\Admin\FieldsController@choose',
        'admin/pages/fields/create'                                  => 'Anomaly\PagesModule\Http\Controller\Admin\FieldsController@create',
        'admin/pages/fields/edit/{id}'                               => 'Anomaly\PagesModule\Http\Controller\Admin\FieldsController@edit',
        'admin/pages/ajax/choose_type'                               => 'Anomaly\PagesModule\Http\Controller\Admin\AjaxController@chooseType',
        'admin/pages/ajax/choose_field/{id}'                         => 'Anomaly\PagesModule\Http\Controller\Admin\AjaxController@chooseField',
        'admin/pages/settings'                                       => 'Anomaly\PagesModule\Http\Controller\Admin\SettingsController@index',
        'pages/preview/{id}'                                         => 'Anomaly\PagesModule\Http\Controller\PagesController@preview'
    ];

    /**
     * Map additional routes.
     *
     * @param PageRepositoryInterface $pages
     */
    public function map(PageRepositoryInterface $pages, Router $router)
    {
        /* @var PageCollection $pages */
        $pages = $pages->sorted();

        /* @var PageInterface $page */
        foreach ($pages->exact(true) as $page) {
            $router->any(
                $page->getPath(),
                [
                    'uses'                       => 'Anomaly\PagesModule\Http\Controller\PagesController@view',
                    'streams::addon'             => 'anomaly.module.pages',
                    'anomaly.module.pages::page' => $page->getId()
                ]
            );
        }

        /* @var PageInterface $page */
        foreach ($pages->exact(false) as $page) {
            $router->any(
                $page->getPath() . '/{any?}',
                [
                    'uses'                       => 'Anomaly\PagesModule\Http\Controller\PagesController@view',
                    'streams::addon'             => 'anomaly.module.pages',
                    'anomaly.module.pages::page' => $page->getId(),
                    'where'                      => [
                        'any' => '(.*)'
                    ]
                ]
            );
        }
    }
}
