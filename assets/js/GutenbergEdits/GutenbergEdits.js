window.onload = () => {
    wp.domReady( () => {
        wp.data.dispatch('core/edit-post').removeEditorPanel('template');
        wp.data.dispatch('core/edit-post').removeEditorPanel('discussion-panel');
        wp.data.dispatch('core/edit-post').removeEditorPanel('post-link');
        wp.data.dispatch('core/edit-post').removeEditorPanel('page-attributes');
    } );
}