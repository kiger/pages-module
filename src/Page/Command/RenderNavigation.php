<?php namespace Anomaly\PagesModule\Page\Command;

use Anomaly\PagesModule\Page\Contract\PageInterface;
use Anomaly\PagesModule\Page\Contract\PageRepositoryInterface;
use Anomaly\PagesModule\Page\PageCollection;
use Anomaly\Streams\Platform\Support\Collection;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\View\Factory;

/**
 * Class RenderNavigation
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 * @package       Anomaly\PagesModule\Page\Command
 */
class RenderNavigation implements SelfHandling
{

    use DispatchesJobs;

    /**
     * The rendering options.
     *
     * @var Collection
     */
    protected $options;

    /**
     * Create a new RenderNavigation instance.
     *
     * @param Collection $options
     */
    function __construct(Collection $options)
    {
        $this->options = $options;
    }

    /**
     * Handle the command.
     *
     * @param PageRepositoryInterface $pages
     * @return null|PageInterface
     */
    public function handle(PageRepositoryInterface $pages, Factory $view)
    {
        $options = $this->options;

        /* @var PageCollection $pages */
        $pages = $pages->sorted();
        $pages = $pages->visible();

        $this->dispatch(new SetCurrentPage($pages));
        $this->dispatch(new SetActivePages($pages));
        $this->dispatch(new SetParentRelations($pages));
        $this->dispatch(new SetChildrenRelations($pages));
        $this->dispatch(new RemoveRestrictedPages($pages));

        if ($root = $options->get('root')) {
            if ($page = $this->dispatch(new GetPage($root))) {
                $options->put('parent', $page);
            }
        }

        return $view->make(
            $options->get('view', 'anomaly.module.pages::structure'),
            compact('pages', 'options')
        )->render();
    }
}
