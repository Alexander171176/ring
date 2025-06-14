<?php

// resources/lang/kk/admin/requests/PluginRequest.php

return [
    'sort.integer' => 'Сұрыптау өрісі сан болуы керек.',
    'sort.min' => 'Сұрыптау өрісі теріс болуы мүмкін емес.',

    'name.required' => 'Плагин атауы қажет.',
    'name.max' => 'Плагин атауы :max таңбадан аспауы керек.',
    'name.unique' => 'Осындай аты бар плагин бұрыннан бар.',

    'version.max' => 'Плагин нұсқасы :max таңбадан аспауы керек.',

    'icon.string' => 'Белгіше жол болуы керек.',
    'icon.max' => 'Белгішенің мазмұны тым ұзын.',

    'description.string' => 'Сипаттама жол болуы керек.',
    'description.max' => 'Сипаттама тым ұзын.',

    'readme.string' => 'README жол болуы керек.',

    'options.json' => 'Опциялар жарамды JSON жолы болуы керек.', // Қосылды

    'code.string' => 'Код жол болуы керек.',
    'code.max' => 'Код :max таңбадан аспауы керек.',
    'code.regex' => 'Код тек латын әріптерін, сандарды және астын сызуларды қамтуы мүмкін.', // Қосылды
// 'code.unique' => 'Бұл коды бар плагин бұрыннан бар.', // Код бірегей болса

    'templates.max' => 'Үлгілер өрісі :max таңбадан аспауы керек.',

    'activity.required' => 'Әрекет өрісі қажет.',
    'activity.boolean' => 'Әрекет өрісі логикалық болуы керек.',
];
