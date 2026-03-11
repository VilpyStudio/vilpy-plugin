<?php

namespace Vilpy;

class SystemPage
{
    //Wordpress settings API
    public function systemOptions()
    {
        //Add section
        add_settings_section('vilpysystem', __('Systeem', themeTextDomain()), [$this, 'systemSection'], 'vilpy-system-section', 'vilpysystem');

        //Add cleanup functions
        add_settings_field('do-cleanup', __('Database cleanup', themeTextDomain()), [$this, 'doCleanup'], 'vilpy-system-section', 'vilpysystem');
    }

    public function deleteTableIfExists($tableName) {
        global $wpdb;

        $table_name = $wpdb->prefix . $tableName;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");

        // die($table_name);
        if ($table_exists) {
            $wpdb->query("DROP TABLE IF EXISTS $table_name");
            return true;
        } else {
            return false;
        }
    }

    public function deleteOption($optionName) {
        return delete_option($optionName);
    }

    public function systemSection()
    {
        _e('Diverse systeem processen.', themeTextDomain());
    }

    public function doCleanup()
    {
        //  remove options
        $deleteOptionDisableGoogleFonts = $this->deleteOption('remove-elementor-google-fonts');
        // remove database tables
        $deleteClientreportingTable = $this->deleteTableIfExists('vilpy_clientreporting');

        ob_start();
        ?>

        <!-- ----- element ----- -->
        <div class="systemPage-item">
            <strong>27-01-2025:</strong>
            <div class="item">
                <div class="item_label">Actie:</div><div class="item_description">Verwijderen optie `remove-elementor-google-fonts`. Deze functie is binnen elementor beschikbaar Elementor - instellingen - geavanceerd - sectie: Google Fonts</div>
            </div>
            <div class="item">
                <div class="item_label">Status:</div><div class="item_description"><?php echo ($deleteOptionDisableGoogleFonts ? '<span style="color:red;">Optie met de naam `remove-elementor-google-fonts` is zojuist verwijderd!</span>' : '<span style="color:green;">De optie `remove-elementor-google-fonts` bestaat niet (meer), is mogelijk al eerder verwijderd!</span>');  ?></div>
            </div>
        </div>

        <!-- ----- element ----- -->
        <div class="systemPage-item">
            <strong>27-01-2025:</strong>
            <div class="item">
                <div class="item_label">Actie:</div><div class="item_description">Verwijderen database tabel `vilpy_clientreporting`. Dit was onderdeel van een mogelijke client reporting functionaliteit. Deze wordt niet meer gebruikt.</div>
            </div>
            <div class="item">
                <div class="item_label">Status:</div><div class="item_description"><?php echo ($deleteClientreportingTable ? '<span style="color:red;">Database tabel `wp_vilpy_clientreporting` is zojuist verwijderd!</span>' : '<span style="color:green;">Database tabel `wp_vilpy_clientreporting` bestaat niet (meer), is mogelijk al eerder verwijderd!</span>');  ?></div>
            </div>
        </div>

        <?php
        echo ob_get_clean();
    }
}
