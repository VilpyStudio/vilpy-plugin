<?php

namespace Vilpy;

?>

<div id="default" class="vilpy-tab-content active-tab">
    <form method="post" action="options.php">
        <?php
        settings_fields('vilpy-settings');
        do_settings_sections('vilpy');
        submit_button();
        ?>
    </form>
</div>
