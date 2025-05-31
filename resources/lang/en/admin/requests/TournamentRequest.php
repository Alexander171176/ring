<?php

// resources/lang/en/admin/requests/TournamentRequest.php

return [
    'sort.integer' => 'The sort field must be a number.',
    'sort.min' => 'The sort field cannot be negative.',

    'activity.required' => 'The activity field is required.',
    'activity.boolean' => 'The activity field must be a Boolean value.',

    'locale.required' => 'The locale is required.',
    'locale.size' => 'The locale must contain 2 characters.',

    'name.required' => 'The name of the tournament is required.',
    'name.max' => 'The name of the tournament must not exceed 255 characters.',
    'short.max' => 'The short description must not exceed 255 characters.',

    'description.string' => 'The description must be a string.',

    'tournament_date_time.required' => 'The date and time of the tournament are required.',
    'tournament_date_time.date' => 'The date and time of the tournament must be a valid date.',

    'status.required' => 'Tournament status is required.',
    'status.in ' => 'Invalid tournament status value.',

    'venue.max' => 'The name of the venue must not exceed 255 characters.',
    'city.max' => 'The name of the city must not exceed 255 characters.',
    'country.max' => 'The country name must not exceed 255 characters.',

    'weight_class_name.max' => 'The name of the weight category must not exceed 255 characters.',
    'rounds_scheduled.integer' => 'The number of rounds must be a number.',
    'rounds_scheduled.min' => 'The minimum number of rounds is 1.',
    'rounds_scheduled.max' => 'The maximum number of rounds is 12.',

    'is_title_fight.boolean' => 'The "Title fight" field must be Boolean.',

    'fighter_red_id.required' => 'The athlete in the red corner is required.',
    'fighter_red_id.exists' => 'The athlete in the red corner was not found.',
    'fighter_red_id.different' => 'Fighters cannot match.',

    'fighter_blue_id.required' => 'The athlete in the blue corner is required.',
    'fighter_blue_id.exists' => 'The athlete in the blue corner was not found.',
    'fighter_blue_id.different' => 'Fighters cannot match.',

    'winner_id.exists' => 'No winner has been found.',

    'method_of_victory.max' => 'The winning method must not exceed 255 characters.',
    'round_of_finish.integer' => 'The completion round must be a number.',
    'round_of_finish.min' => 'The minimum value of the round is 1.',
    'round_of_finish.max' => 'The maximum value of the round is 12.',
    'time_of_finish.max' => 'The completion time should not exceed 255 characters.',

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
