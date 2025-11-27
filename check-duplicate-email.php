<?php
// Quick script to check duplicate emails in database

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "Checking for Duplicate Emails\n";
echo "========================================\n\n";

// Get all users
$users = User::all();

echo "Total Users: " . $users->count() . "\n\n";

// Group by email
$emailGroups = $users->groupBy('email');

$duplicates = $emailGroups->filter(function($group) {
    return $group->count() > 1;
});

if ($duplicates->count() > 0) {
    echo "⚠️  DUPLICATE EMAILS FOUND:\n";
    echo "========================================\n";
    foreach ($duplicates as $email => $group) {
        echo "\nEmail: $email (Count: " . $group->count() . ")\n";
        foreach ($group as $user) {
            echo "  - ID: {$user->id}, Name: {$user->name}, Created: {$user->created_at}\n";
        }
    }
    echo "\n";
} else {
    echo "✅ No duplicate emails found!\n\n";
}

// Show all emails
echo "All Registered Emails:\n";
echo "========================================\n";
foreach ($emailGroups as $email => $group) {
    $user = $group->first();
    echo "- {$email} (ID: {$user->id}, Name: {$user->name})\n";
}

echo "\n";

