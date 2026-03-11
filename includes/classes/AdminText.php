<?php

namespace Vilpy;

class AdminText
{

    public function removeWPText()
    {
        return '';
    }

    public function removeVersionNum()
    {
        remove_filter('update_footer', 'core_update_footer');
    }
}
