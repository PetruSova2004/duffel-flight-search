<?php

return [
    'accepted' => 'Нужно принять поле «:attribute».',
    'after_or_equal' => 'Поле «:attribute» должно быть не раньше :date.',
    'before_or_equal' => 'Поле «:attribute» должно быть не позже :date.',
    'alpha' => 'Поле «:attribute» может содержать только буквы.',
    'boolean' => 'Поле «:attribute» должно быть да или нет.',
    'confirmed' => 'Пароли не совпадают.',
    'current_password' => 'Неверный текущий пароль.',
    'date' => 'Поле «:attribute» должно быть датой.',
    'date_format' => 'Поле «:attribute» должно быть в формате :format.',
    'different' => 'Поля «:attribute» и «:other» должны различаться.',
    'email' => 'Введите корректный e-mail.',
    'in' => 'Выберите значение из списка в поле «:attribute».',
    'lowercase' => 'Поле «:attribute» должно быть в нижнем регистре.',
    'max' => [
        'string' => 'Поле «:attribute» должно быть не длиннее :max символов.',
    ],
    'min' => [
        'string' => 'Поле «:attribute» должно быть не короче :min символов.',
    ],
    'password' => [
        'letters' => 'Пароль должен содержать хотя бы одну букву.',
        'mixed' => 'Пароль должен содержать строчные и заглавные буквы.',
        'numbers' => 'Пароль должен содержать хотя бы одну цифру.',
        'symbols' => 'Пароль должен содержать хотя бы один спецсимвол.',
        'uncompromised' => 'Этот пароль встречался в утечках, выберите другой.',
    ],
    'regex' => 'Поле «:attribute» заполнено в неверном формате.',
    'required' => 'Заполните поле «:attribute».',
    'size' => [
        'string' => 'Поле «:attribute» должно содержать :size символа.',
    ],
    'string' => 'Поле «:attribute» должно быть строкой.',
    'unique' => 'Такой :attribute уже зарегистрирован.',

    'attributes' => [
        'name' => 'Имя',
        'email' => 'e-mail',
        'password' => 'Пароль',
        'current_password' => 'Текущий пароль',
        'country' => 'Страна',
        'departure_date' => 'Дата отправления',
        'origin_country' => 'Страна отправления',
        'origin' => 'Город отправления',
        'destination_country' => 'Страна прибытия',
        'destination' => 'Город прибытия',
    ],
];
