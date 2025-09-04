<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->constrained();
            $table->enum("unit", ["count", "min"])->default("count");
            $table->smallInteger("target_min");
            $table->tinyInteger("is_active")->default(1);
            $table->smallInteger("cycle_length")->default(21);
            $table->date("cycle_started_at");
            $table->smallInteger("cycle_success_threshold")->default(18);
            $table->smallInteger("grace_total")->default(2);
            $table->smallInteger("grace_used")->default(0);
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habits');
    }
};
