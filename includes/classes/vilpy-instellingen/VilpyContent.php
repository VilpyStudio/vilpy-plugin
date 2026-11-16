<?php

namespace Vilpy;

class VilpyContent
{
    private $content = '';

    public function setTitle($title)
    {
        $this->content .= '<div style="margin-top: 25px"><h1 style="margin-bottom: 25px; display: inline">' . esc_html($title) . '</h1>';
        return $this;
    }

    public function setSubtitle($title)
    {
        $this->content .= '<h2 style="opacity: .5; display: inline; margin-left: 10px"><i>' . esc_html($title) . '</i></h2></div>';
        return $this;
    }

    public function addOptions()
    {
        ob_start();
        require PLUGINDIR . '/templates/admin/vilpy-options.php';
        $this->content .= ob_get_clean();
        return $this;
    }

    public function getContent()
    {
        echo $this->content;
    }
}
