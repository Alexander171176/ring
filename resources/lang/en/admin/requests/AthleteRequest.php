<?php

// resources/lang/en/admin/requests/AthleteRequest.php

return [
    'sort.integer' => 'The sort field must be a number.',
    'sort.min' => 'The sort field cannot be less than zero.',
    'activity.required' => 'The activity field is required.',
    'activity.boolean' => 'The activity field must be a boolean value.',

    'first_name.required' => 'The name is required.',
    'first_name.max' => 'The name must not exceed 255 characters.',
    'last_name.required' => 'Last name is required.',
    'last_name.max' => 'Last name must not exceed 255 characters.',
    'nickname.max' => 'The alias must not exceed 255 characters.',
    'date_of_birth.date_format' => 'Date of birth must be in the format YYYY-MM-DD.',
    'date_of_birth.before_or_equal' => 'The date of birth cannot be in the future.',
    'nationality.max' => 'Nationality must not exceed 255 characters.',
    'height_cm.integer' => 'Height must be a number.',
    'height_cm.min' => 'Height must be at least 50 cm.',
    'height_cm.max' => 'Height must be no more than 300 cm.',
    'reach_cm.integer' => 'Arm span must be a number.',
    'reach_cm.min' => 'The arm span must be at least 50 cm.',
    'reach_cm.max' => 'The arm span should not exceed 350 cm.',
    'stance.in ' => 'Invalid rack value.',
    'bio.max' => 'The bio is too long.',
    'short.max' => 'The short description must not exceed 1000 characters.',
    'description.max' => 'The description is too long.',

    'avatar.image' => 'The avatar must be an image.',
    'avatar.mimes' => 'The avatar must be in PNG format.',
    'avatar.max' => 'The avatar must not exceed 2 MB.',

    'wins.integer' => 'Wins should be a number.',
    'wins.min' => 'Victories cannot be negative.',
    'losses.integer' => 'Defeats should be a number.',
    'losses.min' => 'The lesions cannot be negative.',
    'draws.integer' => 'Draws must be a number.',
    'draws.min' => 'Draws cannot be negative.',
    'no_contests.integer' => 'Failed fights must be a number.',
    'no_contests.min' => 'Failed fights cannot be negative.',
    'wins_by_ko.integer' => 'Knockout wins should be a number.',
    'wins_by_ko.min' => 'Knockout wins cannot be negative.',
    'wins_by_submission.integer' => 'Winning by surrender must be a number.',
    'wins_by_submission.min' => 'Winning by surrender cannot be negative.',
    'wins_by_decision.integer' => 'Winning a decision should be a number.',
    'wins_by_decision.min' => 'Winning a decision cannot be negative.',

    'images.*.id.exists' => 'Image not found.',
    'images.*.file.image' => 'The file must be an image.',
    'images.*.file.mimes' => 'The image file must be jpeg, jpg, png, webp or gif.',
    'images.*.file.max' => 'The image file must not exceed 5 MB.',
];
