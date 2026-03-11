<?php

namespace Vilpy;

class MenuItems
{
    //Some plugins have some weird capabilities, that allows them to be in the menu but not on the settings page. These can be placed here to fix that.
    public static $exceptions = [
        // 'jet-engine',
        // 'wpcf7',
        // 'wpseo_dashboard',
        // 'themes.php'
        'pixelyoursite'
    ];

    public function createTopMenuItem($adminBar)
    {
        //If user role is not administrator return
        if (!current_user_can('administrator')) {
            return;
        }

        $turnOn = get_option('client-cache-control');
        if (!$turnOn) {
            return;
        }

        //Get full page url including domain
        $actualLink = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $adminBar->add_node(array(
            'id'    => 'hh-adminbar-item',
            'parent' => null,
            'group'  => null,
            'title' => 'Site acties', //you can use img tag with image link. it will show the image icon Instead of the title.
            'href'  => '#',
            // 'meta' => [
            //     'title' => 'Test', //This title will show on hover
            // ]
        ));

        $litespeedInstalled = pluginInstalled("litespeed-cache/litespeed-cache.php");
        $cloudFlareZoneFilled = get_option('client-cloudflare-zone');
        if ($litespeedInstalled) {
            $adminBar->add_node(array(
                'id'    => 'hh-adminbar-item-child-litespeed',
                'parent' => 'hh-adminbar-item',
                'group'  => null,
                'title' => 'Huidige pagina litespeed cache legen', //you can use img tag with image link. it will show the image icon Instead of the title.
                'href'  => '/wp-admin/admin-post.php?action=clearHhLsCache&url=' . $actualLink,
            ));
        }
        
        if ($cloudFlareZoneFilled) {
            $adminBar->add_node(array(
                'id'    => 'hh-adminbar-item-child-cloudflare',
                'parent' => 'hh-adminbar-item',
                'group'  => null,
                'title' => 'Huidige pagina Cloudflare cache legen', //you can use img tag with image link. it will show the image icon Instead of the title.
                'href'  => '/wp-admin/admin-post.php?action=clearHhCfCache&url=' . $actualLink,
            ));
        }

        if ($litespeedInstalled && $cloudFlareZoneFilled) {
            $adminBar->add_node(array(
                'id'    => 'hh-adminbar-item-child-both',
                'parent' => 'hh-adminbar-item',
                'group'  => null,
                'title' => 'Huidige pagina cache volledig legen', //you can use img tag with image link. it will show the image icon Instead of the title.
                'href'  => '/wp-admin/admin-post.php?action=clearHhCache&url=' . $actualLink,
            ));
        }

        if (!$litespeedInstalled && !$cloudFlareZoneFilled) {
            $adminBar->add_node(array(
                'id'    => 'hh-adminbar-item-child-both-not',
                'parent' => 'hh-adminbar-item',
                'group'  => null,
                'title' => '<i>Geen acties beschikbaar</i>', //you can use img tag with image link. it will show the image icon Instead of the title.
                'href'  => '#',
            ));
        }

    }

    public static function getSubMenu()
    {
        global $submenu;
        return $submenu;
    }

    public static function getMenu()
    {
        global $menu;
        return $menu;
    }

    public static function getSelectedMenuItems()
    {
        function implode_all($glue, $arr)
        {
            if (is_array($arr)) {
                foreach ($arr as $key => &$value) {
                    if (@is_array($value)) {
                        $arr[ $key ] = implode_all($glue, $value);
                    }
                }

                return implode($glue, $arr);
            }

            // Not array
            return $arr;
        }

        $selectedOptions = get_option('vilpy_sidebar_items_tohide');

        //Selected menu items to string and back to array so we can check if it is in the array easily
        $selectedOptionsString = implode_all(",", $selectedOptions);
        $simpleOptionsArray = explode(",", $selectedOptionsString);

        $sOArrayWithoutSpaces = [];

        foreach ($simpleOptionsArray as $item) {
            $fixed = preg_replace('/\s+/', '', $item);
            $sOArrayWithoutSpaces[] = $fixed;
        }

        return $sOArrayWithoutSpaces;
    }

    public static function getParsed($vilpyRole)
    {

        $menuItems = self::getMenu();
        $submenuItems = self::getSubMenu();
		

        //Merge all the specific user caps from vilpy_client because role caps are not the only ones that matter
        $userArgs = [
            'role' => 'vilpy_client'
        ];

        $userCaps = [];

        $users = get_users($userArgs);
        foreach ($users as $user) {			
            $userCaps[] = $user->allcaps;
        }
		
        $parsedMenuBar = [];

        foreach ($menuItems as $item) {
			
            //Skip all separator items
            if (str_contains($item[2], 'separator')) {
                continue;
            }

            //Skip if it hasnt capablitiy but allow if is in exceptions array
            if (!$vilpyRole->has_cap($item[1]) && !in_array($item[2], self::$exceptions)) {
                continue;
            }

            //If menu item has no child items just print it and skip further iteration.
            if (!array_key_exists($item[2], $submenuItems)) {
                $parsedMenuBar[$item[2]] = array(
                    'capability' => $item[1],
                    'menu_title' => $item[0],
                    'icon' => $item[6],
                    'children' => []
                );
                continue;
            }

            //If menu has children items, print it and iterate through the children.
            $parsedMenuBar[$item[2]] = array(
                'capability' => $item[1],
                'menu_title' => $item[0],
                'icon' => $item[6],
                'children' => []
            );

            foreach ($submenuItems[$item[2]] as $subitem) {
				
				
                if ($vilpyRole->has_cap($subitem[1]) || in_array($item[2], self::$exceptions)) {
                    $parsedMenuBar[$item[2]]['children'][$subitem[2]] = $subitem[0];
                }
            }
        }
		

        return $parsedMenuBar;
    }


    public function removeSidebarItems()
    {
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $itemsToHide = $_POST['menu'];


//         echo '<pre>';
//         print_r($itemsToHide);
//         echo '</pre>';
//         die;

        update_option('vilpy_sidebar_items_tohide', $itemsToHide);

        wp_redirect($_POST['_wp_http_referer']);
        exit;
    }

    public function removeNotices()
    {
        ?>
<style>
.notice {
    display: none !important;
}

.woocommerce-message {
    display: none !important;
}

.mwp-notice-container {
    display: none !important;
}
</style>
<?php
    }

    public function removeTopbarItems()
    {
        global $wp_admin_bar;
        $nodes = $wp_admin_bar->get_nodes();
        $cachecontrol = get_option('client-cache-control');

        $itemsToSkip = [
            'logout',
            'my-account',
            'top-secondary',
            'user-actions',
            'site-name',
            'view-site',
            'edit',
            'view-store',
            'dashboard',
        ];

        if ($cachecontrol) {
            $itemsToSkip[] = 'hh-adminbar-item';
            $itemsToSkip[] = 'hh-adminbar-item-child-litespeed';
            $itemsToSkip[] = 'hh-adminbar-item-child-cloudflare';
            $itemsToSkip[] = 'hh-adminbar-item-child-both';
            $itemsToSkip[] = 'hh-adminbar-item-child-both-not';
        }

        $ids = [];

        foreach ($nodes as $menunode) {
            if (in_array($menunode->id, $itemsToSkip)) {
                continue;
            }

            $ids[] = $menunode->id;
        }
        foreach ($ids as $id) {
            $wp_admin_bar->remove_node($id);
        }

        //Set defaults for duplicate post
        $defaultsForDp = [
            'row' => 1,
            'submitbox' => 1,
            'bulkactions' => 1,
        ];

        if (get_option('duplicate_post_show_link_in')) {
            update_option('duplicate_post_show_link_in', $defaultsForDp);
        }
    }

    public function removeFrontendItems()
    {
        ?>
<style>
#wp-admin-bar-elementor_edit_page {
    display: none;
}
</style>
<?php
    }

    public function removeScreenOptions()
    {
        global $pagenow;
        if ($pagenow === 'index.php') {
            ?>
<style>
#screen-meta {
    display: none !important;
}

#screen-meta-links {
    display: none !important;
}

.meta-box-sortables {
    display: none !important;
}

.welcome-panel-close {
    display: none !important;
}

#collapse-menu {
    display: none !important;
}
</style>
<?php
        }
    }

    public function litespeedDiamond()
    {
            ?>
    <style>
    .litespeed-top-toolbar {
        display: none
    }

    ;
    </style>
    <?php
    }

    public function removeMenuItems()
    {
        //Actually to show
        $itemsToHide = get_option('vilpy_sidebar_items_tohide');

		
        //If is not set hide everything
        $menu = self::getMenu();
        if ($itemsToHide === false) {
            foreach ($menu as $menuitem) {
                remove_menu_page($menuitem[2]);
            }
            return;
        }

        if (is_array($itemsToHide)) {
            foreach ($itemsToHide as $item => $val) {
                $parent = array_key_exists('parent', $val) ? $val['parent'][0] : str_replace('||', '.', $item);
                array_key_exists('parent', $val) && remove_menu_page($parent);

                //Children are multiple
                if (array_key_exists('children', $val)) {
                    foreach ($val['children'] as $child) {
                        remove_submenu_page($parent, htmlspecialchars($child));
                    }
                }
            }
        }
    }
}
