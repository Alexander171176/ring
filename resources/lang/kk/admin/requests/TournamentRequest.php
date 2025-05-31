<?php

// resources/lang/kk/admin/requests/TournamentRequest.php

return [
    'sort.integer' => 'Сұрыптау өрісі сан болуы керек.',
    'sort.min' => 'Сұрыптау өрісі теріс болуы мүмкін емес.',

    'activity.required' => 'Әрекет өрісі қажет.',
    'activity.boolean' => 'Әрекет өрісі логикалық мән болуы керек.',

    'locale.required' => 'Жергілікті тіл қажет.',
    'locale.size' => 'Жергілікті тілде 2 таңба болуы керек.',

    'name.required' => 'Турнир атауы қажет.',
    'name.max' => 'Турнир атауы 255 таңбадан аспауы керек.',
    'short.max' => 'Қысқа сипаттама 255 таңбадан аспауы керек.',

    'description.string' => 'Сипаттама жол болуы керек.',

    'tournament_date_time.required' => 'Турнир күні мен уақыты қажет.',
    'tournament_date_time.date' => 'Турнир күні мен уақыты жарамды күн болуы керек.',

    'status.required' => 'Турнир мәртебесі қажет.',
    'status.in' => 'Турнир күйінің мәні жарамсыз.',

    'venue.max' => 'Орын атауы 255 таңбадан аспауы керек.',
    'city.max' => 'Қала атауы 255 таңбадан аспауы керек.',
    'country.max' => 'Ел атауы 255 таңбадан аспауы керек.',

    'weight_class_name.max' => 'Салмақ сыныбының атауы 255 таңбадан аспауы керек.',
    'rounds_scheduled.integer' => 'Раундтардың саны сан болуы керек.',
    'rounds_scheduled.min' => 'Раундтардың ең аз саны - 1.',
    'rounds_scheduled.max' => 'Раундтардың ең көп саны - 12.',

    'is_title_fight.boolean' => 'Тақырыппен күрес өрісі логикалық болуы керек.',

    'fighter_red_id.required' => 'Қызыл бұрышта спортшы болуы керек.',
    'fighter_red_id.exists' => 'Қызыл бұрыштағы спортшы табылмады.',
    'fighter_red_id.different' => 'Ұстаздар бірдей болуы мүмкін емес.',

    'fighter_blue_id.required' => 'Көк бұрыштағы жауынгер қажет.',
    'fighter_blue_id.exists' => 'Көк бұрыштағы жауынгер табылмады.',
    'fighter_blue_id.different' => 'Ұстаздар бірдей болуы мүмкін емес.',

    'winner_id.exists' => 'Жеңімпаз табылмады.',

    'method_of_victory.max' => 'Жеңіс әдісі 255 таңбадан аспауы керек.',
    'round_of_finish.integer' => 'Аяқтау раунды сан болуы керек.',
    'round_of_finish.min' => 'Раундтың ең аз мәні - 1.',
    'round_of_finish.max' => 'Раундтың ең үлкен мәні - 12.',
    'time_of_finish.max' => 'Аяқтау уақыты 255 таңбадан аспауы керек.',

    'images.array' => 'Сурет өрісі массив болуы керек.',
    'images.*.id.integer' => 'Сурет идентификаторы сан болуы керек.',
    'images.*.id.exists' => 'Сурет табылмады.',
    'images.*.id.prohibited' => 'Жасаған кезде кескін идентификаторы берілмеуі керек.',
    'images.*.order.integer' => 'Сурет реті сан болуы керек.',
    'images.*.order.min' => 'Сурет реті теріс болуы мүмкін емес.',
    'images.*.alt.max' => 'Балама мәтін 255 таңбадан аспауы керек.',
    'images.*.caption.max' => 'Тақырып 255 таңбадан аспауы керек.',
    'images.*.file.image' => 'Файл сурет болуы керек.',
    'images.*.file.mimes' => 'Файл келесі пішімде болуы керек: jpeg, jpg, png, gif, svg немесе webp.',
    'images.*.file.max' => 'Сурет файлы 10 МБ аспауы керек.',
    'images.*.file.required_without' => 'Идентификатор жоқ болса, сурет файлы қажет.',

    'deletedImages.array' => 'Жойылған кескіндер өрісі массив болуы керек.',
    'deletedImages.*.integer' => 'Жойылған суреттің идентификаторы сан болуы керек.',
    'deletedImages.*.exists' => 'Жойылатын сурет табылмады.',
];
