@php
    $user = App\Models\User::where('name', 'like', '%Riya%')->first();
    $hasProfilePicture = !empty($user->profile_picture) && file_exists(public_path('storage/profiles/' . $user->profile_picture));
@endphp
User: {{ $user->name }}<br>
Profile Picture: {{ var_export($user->profile_picture, true) }}<br>
Has Profile Picture: {{ $hasProfilePicture ? 'YES' : 'NO' }}<br>
First Letter: {{ strtoupper(substr($user->name, 0, 1)) }}<br>
