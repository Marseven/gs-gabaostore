<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('categories', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('mouvements', fn (Blueprint $t) => $t->softDeletes());
    }

    public function down(): void
    {
        Schema::table('articles', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('categories', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('mouvements', fn (Blueprint $t) => $t->dropSoftDeletes());
    }
};
