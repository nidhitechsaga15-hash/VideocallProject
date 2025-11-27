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
        Schema::table('call_requests', function (Blueprint $table) {
            // Drop the unique constraint on room_id
            $table->dropUnique(['room_id']);
            
            // Add composite unique constraint to prevent duplicate invitations
            // This allows multiple users to be invited to the same room (group calls)
            // but prevents duplicate invitations for the same caller-receiver-room combination
            $table->unique(['caller_id', 'receiver_id', 'room_id'], 'call_requests_unique_invitation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_requests', function (Blueprint $table) {
            // Drop composite unique
            $table->dropUnique('call_requests_unique_invitation');
            
            // Restore unique constraint on room_id
            $table->unique('room_id');
        });
    }
};
