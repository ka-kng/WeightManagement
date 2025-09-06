<?php

return [

    'required' => ':attributeは必須です。',
    'email' => ':attributeの形式が正しくありません。',
    'confirmed' => ':attributeが一致しません。',
    'numeric' => ':attributeは数字でなければなりません。',
    'min' => [
        'numeric' => ':attributeは:min以上でなければなりません。',
        'string' => ':attributeは:min文字以上でなければなりません。',
    ],
    'max' => [
        'numeric' => ':attributeは:max以下でなければなりません。',
        'string' => ':attributeは:max文字以下でなければなりません。',
    ],

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード確認',
        'gender' => '性別',
        'birth_date' => '生年月日',
        'height' => '身長',
        'target_weight' => '目標体重',
    ],
];
