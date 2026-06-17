<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('slug', 150)->nullable()->after('nombre');
        });

        // Poblar slugs existentes
        $empresas = DB::table('empresas')->get();
        foreach ($empresas as $empresa) {
            $slugBase = Str::slug($empresa->nombre);
            $slug = $slugBase;
            $counter = 1;
            while (DB::table('empresas')->where('slug', $slug)->exists()) {
                $slug = $slugBase . '-' . $counter;
                $counter++;
            }
            DB::table('empresas')->where('id', $empresa->id)->update(['slug' => $slug]);
        }

        // Hacer la columna not null y unique
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('slug', 150)->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
