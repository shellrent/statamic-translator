<?php

return [

    'translator' => [

        'config_fields' => [
            'button_label' => [
                'title' => 'Label Bottone',
                'instructions' => 'Personalizza la label per il bottone di traduzione',
                'default' => 'Traduci il contenuto',
            ],
        ],

        'vue_component' => [
            'error_default_locale' => 'Non puoi tradurre il default locale',
            'error_source_locale' => 'IL default locale non è supportato per la traduzione',
            'error_target_locale' => 'Il locale corrente non è supportato per la traduzione',
            'error_unavailable' => 'Translator non disponibile',
            'translating_title' => 'Traduzione in corso',
            'translating_message' => 'Attendi che la traduzione finisca',
            'reload' => 'La pagina verrà aggiornata in 3 secondi',
            'success' => 'Successo nella traduzione',
            'error_general' => 'Si è verificato un errore',
            'error_console' => 'Controlla la console per maggiori informazioni',
        ],

    ],

];
