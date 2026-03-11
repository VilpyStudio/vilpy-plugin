<?php 
    namespace Vilpy;

    $vilpyRole = get_role('vilpy_client');
    // echo '<pre>';

    // print_r($vilpyRole->capabilities);
    // echo '</pre>';

    $menuNew = MenuItems::getParsed($vilpyRole);

    // global $menu;
    // echo '<pre>';
    // print_r($menu);
    // echo '</pre>';
    // echo '<hr>';
    // echo '<pre>';
    // print_r($menuNew);
    // echo '</pre>';
    // die;
?>
<section class='menu-items'>
    <?php
    $simpleOptionsArray = MenuItems::getSelectedMenuItems();

    $initalShow = true;
    if ($simpleOptionsArray[0] === '' && get_option('vilpy_sidebar_items_tohide') === false) {
        if (get_option('vilpy_sidebar_items_tohide') !== '') {
            $initalShow = false;
        }
    }

    $initialCheck = empty($simpleOptionsArray[0]);
    ?>
    <form action="/wp-admin/admin-post.php" method="post" id='menuitemsform'>
        <input type="hidden" name="action" value="removeSidebarItems">
        <?php

        foreach ($menuNew as $item => $val) {
            $menuItemSlug = $item;
            $extraData = $val;
            $serializedItemSlug = str_replace('.', '||', $menuItemSlug);

            ?>
        <div class='menu-items-card'>
            <label class='parent-label' for="<?php echo $menuItemSlug ?>">
                <input 
                    class="parent-checkbox"
                    name="menu[<?php echo $serializedItemSlug ?>][parent][]" 
                    type="checkbox" id="<?php echo $menuItemSlug ?>"
                    value="<?php echo $menuItemSlug ?>" 
                    onclick="toggle(this)" 
                    <?php echo !in_array($menuItemSlug, $simpleOptionsArray) && $initalShow ?: 'checked' ?>>
                <?php echo $extraData['menu_title'] ?>
            </label>
            <hr>
            <div class="sub-wrapper">
            <?php foreach ($extraData['children'] as $subMenuItemSlug => $name) { ?>
            <label class='submenu-label' for="<?php echo $subMenuItemSlug . '-sub' ?>">
                <input name="menu[<?php echo $serializedItemSlug ?>][children][]" type="checkbox"
                    id="<?php echo $subMenuItemSlug . '-sub' ?>" value="<?php echo $subMenuItemSlug ?>" <?php echo !in_array(html_entity_decode($subMenuItemSlug), $simpleOptionsArray) && $initalShow ?: 'checked' ?>>
                <?php echo $name ?>
            </label>
            <br>
            <?php } ?>
            </div>
            <br>
        </div>

        <?php
        }

        wp_nonce_field('removeSidebarItems'); ?>
    </form>
    <div>
    <?php submit_button(null, 'primary', 'submit', true, ['form' => 'menuitemsform']); ?>
    </div>
</section>