<?php

namespace Vilpy;

class VilpyContent
{

    private $content = '';

    public function setTitle($title)
    {
        $this->content .= '<div style="margin-top: 25px"><h1 style="margin-bottom: 25px; display: inline">' . $title . '</h1>';
        return $this;
    }

    public function setSubtitle($title)
    {
        $this->content .= '<h2 style="opacity: .5; display: inline">' . $title . '</h2>';
        return $this;
    }

    public function addHTML($html)
    {
        if ($html != strip_tags($html)) {
            $this->content .= $html;
        } else {
            $this->content .= '<i style="color: red">De addHTML functie kan alleen worden gebruikt met HTML!</i>';
        }

        return $this;
    }

    public function enableNotice()
    {
        ob_start(); ?>
        <div class="notice notice-info" style="margin-top: 20px; margin-left: 0px;"> 
            <!-- <p>
                <strong style="margin-bottom: 10px">Wat we al gedaan hebben:</strong>
                <br>
                </span>&bull; Login pagina customizen.</span>
                <br>
                </span>&bull; Elementor fix voor menu arrows en het gebruiken van SVG's als icoon.</span>
                <br>
                </span>&bull; Stijl fix voor bijvoorbeeld het zoomen bij een contact form en de lijntjes weg halen bij het klikken.</span>
                <br>
                </span>&bull; De rol: <i>Vilpy klant</i> toegevoegd.</span>
                <br>
                </span>&bull; Diverse knoppen en mogelijkheden worden verwijderd bij een <i>Vilpy klant</i>.</span>
                <br>
                </span>&bull; Een mooi dashboard bij de <i>Vilpy klant</i> rol met handige links.</span>
                <br>
                </span>&bull; Dashboard content opsplisten in tabs, activate en deactivate scripts toegevoegd.</span>
                <br>
            </p> -->
            <p>
                Welkom op de Vilpy instellingenpagina.
            </p>
        </div>
        </div> <!---Wrapping div --->
        <?php $this->content .= ob_get_clean();
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
