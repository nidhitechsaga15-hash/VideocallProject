<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify ENUM to include 'contact' type
        // MySQL requires raw SQL to alter ENUM columns
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'image', 'file', 'audio', 'video', 'location', 'contact') DEFAULT 'text'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'contact' from ENUM (revert to original)
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'image', 'file', 'audio', 'video', 'location') DEFAULT 'text'");
    }
};
