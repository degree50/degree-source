<?php


namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function getFilters(): array
    {
        return [
            // the logic of this filter is now implemented in a different class
            new TwigFilter('virtualized_file_url', [AppRuntime::class, 'virtualizedFileUrl']),
        ];
    }

    public function getGlobals(): array
    {
        return [
            'sidebar_is_open' => key_exists("sidebarIsOpen", $_COOKIE) && $_COOKIE["sidebarIsOpen"] === "false" ? 'sidebar--is-closed' : '',
        ];
    }
}
