(function ($) {

    const $treeview = $('#treeview');

    // Bail if no treeview is found.
    if (!$treeview.length) {
        return;
    }

    // Search node text.
    $('#treeview-search-submit').on('click', function () {
        const query = $('#treeview-search-input').val().toLowerCase();
        $treeview.treeview('search', [query]);
    });

    // Make the tree.
    $treeview.treeview({
        enableLinks: false,
        bootstrap2: false,
        showTags: true,
        showCount: true,
        levels: 2,
        expandIcon: 'dashicons dashicons-plus',
        collapseIcon: 'dashicons dashicons-minus',
        data: JSON.parse($treeview.attr('data-nodes'))
    });

})(window.jQuery);
