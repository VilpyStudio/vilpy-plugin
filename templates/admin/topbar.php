<?php 
    namespace Vilpy;
?>
<section class='menu-items'>
    <form action="/wp-admin/admin-post.php" method="post">
        <input type="hidden" name="action" value="removeTopBarItems">
        <div class="menu-items-card">
            <label class="parent-label" for="test">
                <input name="test" type="checkbox" id="test" value="test">
            </label>
        </div>
        <?php
        wp_nonce_field('removeTopBarItems');
        submit_button();
        ?>
    </form>
</section>