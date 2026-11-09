<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Webkul\B2BSuite\Menu as BaseMenu;

class Menu extends BaseMenu
{
    /**
     * Get all menu items.
     *
     * In native Bagisto, removeChildrenUnauthorizedMenuItem() automatically
     * overwrites a parent menu item's route with its first child's route.
     * For 'configuration', we restore the native route 'admin.configuration.index'
     * so that Configure continues to open /admin/configuration, while preserving
     * its submenus (Magic AI and AI Assistant).
     */
    public function getItems(?string $area = null): Collection
    {
        $items = parent::getItems($area);

        foreach ($items as $item) {
            if ($item->getKey() === 'configuration') {
                $item->route = 'admin.configuration.index';
                break;
            }
        }

        return $items;
    }
}
