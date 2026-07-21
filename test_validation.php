<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    $ignoreId = 'NULL';
    $empresaId = 'ea17bd12-1d31-46f3-8320-495f56d600bc';
    
    $rules = [
        'nombre'   => [
            'required', 'string', 'max:150',
            \Illuminate\Validation\Rule::unique('sucursales')
                ->where('empresa_id', $empresaId)
                ->ignore($ignoreId),
        ],
        'slug'     => [
            'required', 'string', 'max:100',
            \Illuminate\Validation\Rule::unique('sucursales', 'slug')
                ->ignore($ignoreId),
        ],
        'direccion' => 'nullable|string',
        'ciudad'    => 'required|string|max:100',
        'telefono'  => 'nullable|string|max:20',
        'latitud'   => 'nullable|numeric',
        'longitud'  => 'nullable|numeric',
    ];

    $data = [
        'nombre' => 'Test Sede 3',
        'slug' => 'test-sede-3',
        'ciudad' => 'Bogota',
        'telefono' => '300000',
    ];

    $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);

    if ($validator->fails()) {
        echo "VALIDATION FAILED:\n";
        print_r($validator->errors()->toArray());
    } else {
        echo "VALIDATION PASSED!\n";
    }
} catch (\Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
