<?php

namespace Vilpy;

class InstallVilpyIcons
{

    public function installIconLib()
    {
        $css = $path = plugins_url('/assets/icons/icons.css', PLUGINROOT);
        $new_icons = array(
            'streepje',
            'check-dun',
            'check-two',
            'verkleinen',
            'vergroten',
            'vergrootglas-plus',
            'vergrootglas-min',
            'vergrootglas',
            'kruis',
            'plus',
            'file',
            'file-outline',
            'play-circle-outline',
            'play',
            'play-circle',
            'phone-ring',
            'phone-outline',
            'user',
            'user-outline',
            'mail-open',
            'mail',
            'mail-outline',
            'locatie',
            'arrow-bottom',
            'arrow-top',
            'arrow-right',
            'arrow-left',
            'chevron-bottom',
            'chevron-top',
            'chevron-right',
            'chevron-left',
            'bold-chevron-down',
            'bold-chevron-left',
            'bold-chevron-right',
            'bold-chevron-up',
            'check-circle-outline',
            'check-circle',
            'check',
            'facebook-f',
            'facebook-square',
            'instagram-square',
            'instagram',
            'linkedin-in',
            'linkedin',
            'pinterest-p',
            'pinterest-square',
            'tiktok',
            'twitter-square',
            'twitter',
            'whatsapp-square',
            'whatsapp',
            'youtube'
        );
        $settings['vilpy-icons'] = [
            'name'          => 'Vilpy icons',
            'label'         => __('Vilpy icons', themeTextDomain()),
            'labelIcon'     => 'icon-user',
            'prefix'        => 'icon-',
            'displayPrefix' => 'icon',
            'url'           => $css,
            'icons'         => $new_icons,
            'ver'           => '1.0.0',
        ];

        return $settings;
    }
}
