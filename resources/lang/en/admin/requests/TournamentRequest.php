<?php

// resources/lang/en/admin/requests/TournamentRequest.php

return [
    'sort.integer' => 'The sort field must be a number.',
    'sort.min' => 'The sort field cannot be negative.',

    'activity.required' => 'The activity field is required.',
    'activity.boolean' => 'The activity field must be a Boolean value.',

    'parent_tournament_id.integer' => 'The ID of the parent tournament must be a number.',
    'parent_tournament_id.exists' => 'The parent tournament was not found.',

    'type.in ' => 'is an invalid value for the tournament type.',

    'name.required' => 'The name of the tournament is required.',
    'name.max' => 'The name of the tournament must not exceed 255 characters.',

    'tournament_date_time.required' => 'The date and time of the tournament are required.',
    'tournament_date_time.date' => 'The date and time of the tournament must be a valid date.',

    'status.required' => 'Tournament status is required.',
    'status.in ' => 'Invalid tournament status value.',

    'venue.max' => 'The name of the venue must not exceed 255 characters.',
    'city.max' => 'The name of the city must not exceed 255 characters.',
    'country.max' => 'The country name must not exceed 255 characters.',

    'short.max' => 'The short description must not exceed 255 characters.',

    'rounds_scheduled.integer' => 'The number of rounds must be a number.',
    'rounds_scheduled.min' => 'The minimum number of rounds is 1.',
    'rounds_scheduled.max' => 'The maximum number of rounds is 12.',

    'is_title_fight.boolean' => 'The "Title fight" field must be Boolean.',

    'winner_id.exists' => 'No winner has been found.',

    'round_of_finish.integer' => 'The completion round must be a number.',
    'round_of_finish.min' => 'The minimum value of the round is 1.',
    'round_of_finish.max' => 'The maximum value of the round is 12.',

    // Images
    'images.array' => 'The image field must be an array.',
    'images.*.id.integer' => 'The image ID must be a number.',
    'images.*.id.exists' => 'Image not found.',
    'images.*.id.prohibited' => 'The image ID should not be transmitted during creation.',
    'images.*.order.integer' => 'The order of the image should be a number.',
    'images.*.order.min' => 'The image order cannot be negative.',
    'images.*.alt.max' => 'Alternative text must not exceed 255 characters.',
    'images.*.caption.max' => 'The signature must not exceed 255 characters.',
    'images.*.file.image' => 'The file must be an image.',
    'images.*.file.mimes' => 'The file must be in jpeg, jpg, png, gif, svg or webp format.',
    'images.*.file.max' => 'The image file must not exceed 10 MB.',
    'images.*.file.required_without' => 'The image file is required if the ID is missing.',

    'deletedImages.array' => 'The deleted image field must be an array.',
    'deletedImages.*.integer' => 'The ID of the deleted image must be a number.',
    'deletedImages.*.exists' => 'The deleted image was not found.',
];
