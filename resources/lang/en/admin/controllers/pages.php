<?php

// resources/lang/en/admin/controllers/pages.php

return [

    // Loading errors
    'index_error' => 'The list of pages could not be loaded.',
    'index_locale_error' => 'The list of pages for the selected locale could not be loaded.',
    'parent_load_error' => 'The list of parent pages could not be loaded.',

    // General actions
    'created' => 'The page was created successfully.',
    'create_error' => 'An error occurred when creating the page.',
    'updated' => 'The page has been updated successfully.',
    'update_error' => 'An error occurred when updating the page.',
    'deleted' => 'The page was successfully deleted.',
    'delete_error' => 'An error occurred when deleting the page.',

    // Activity
    'activity' => 'Page activity status ":title" updated: :action.',
    'activated' => 'activated',
    'deactivated' => 'deactivated',
    'update_activity_error' => 'An error occurred while updating the pages activity status.',

    // Massive activity update
    'bulk_update_activity_no_selection' => 'No pages have been selected to update the activity.',
    'bulk_update_activity_success' => 'The activity status has been updated for :count pages(s): :action.',
    'bulk_update_activity_error' => 'An error occurred during a massive page activity update.',

    // Sorting
    'update_sort_error' => 'An error occurred when updating the page sorting.',
    'order_updated' => 'The page order has been successfully updated.',
    'bulk_update_sort_error' => 'An error occurred when updating the page order. Check the data and try again.',

    // Validation errors
    'invalid_input' => 'Incorrect input data for updating the order.',
    'invalid_page_ids' => 'Incorrect page IDs were detected or pages do not belong to the selected locale.',
    'invalid_parent_ids' => 'Incorrect parent page IDs were found, or the parents do not belong to the selected locale.',
    'parent_loop_error' => 'A page cannot be a parent to itself.',
];
