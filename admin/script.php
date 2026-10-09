<?php
\ = 'd:/Projets/Yakhoub/app/admin/app/Http/Controllers/RestAPI/v1/auth/CustomerAPIAuthController.php';
\ = file_get_contents(\);

\ = str_replace(
    \"'phone' => 'required|min:6|max:20|unique:users',\",
    \"'phone' => 'required|min:6|max:20|unique:users',\n            'nni' => 'required|string|max:20|unique:nni_records,nni_number',\",
    \
);

\ = str_replace(
    \"'password' => bcrypt(\\\['password'])\",
    \"'password' => bcrypt(\\\['password']),\n            'nni_number' => \\\['nni']\",
    \
);

\ = str_replace(
    \"'referred_by' => \\\->id ?? null,\n        ]);\",
    \"'referred_by' => \\\->id ?? null,\n        ]);\n\n        \App\Models\NniRecord::create([\n            'user_id' => \\\->id,\n            'nni_number' => \\\['nni'],\n        ]);\",
    \
);

file_put_contents(\, \);
