<?php

namespace Vilpy;

class VilpyRole
{

    public static function add()
    {
        $caps = array_merge(get_role('administrator')->capabilities);
        add_role(
            'vilpy_client',
            __('Client', themeTextDomain()),
            $caps
        );
    }

    public static function remove()
    {
        remove_role('vilpy_client');
    }

    public function disableProfileAccess()
    {
        remove_menu_page('profile.php');
        remove_submenu_page('users.php', 'profile.php');
        $screen = get_current_screen();
        if ($screen->base == 'profile') {
            // wp_die('You are not permitted to change your own profile information. Please contact a member of HR to have your profile information changed.');
            wp_redirect('/wp-admin/');
        }
    }

    public static function isVilpyUser()
    {
        $user = wp_get_current_user();
        $roles = (array) $user->roles;

        if (in_array('vilpy_client', $roles)) {
            return true;
        } else {
            return false;
        }
    }

    public static function name()
    {
        $user = wp_get_current_user();
        return $user->user_login;
    }

    public static function getUser()
    {
        return wp_get_current_user();
    }
}
