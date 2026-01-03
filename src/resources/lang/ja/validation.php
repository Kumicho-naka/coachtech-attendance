<?php

return [
    'required' => ':attributeを入力してください',
    'email' => '有効なメールアドレスを入力してください',
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください',
    ],
    'confirmed' => ':attributeと一致しません',
    'unique' => 'この:attributeは既に登録されています',
    'max' => [
        'string' => ':attributeは:max文字以下で入力してください',
    ],

    'attributes' => [
        'name' => 'お名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => '確認用パスワード',
    ],
];
