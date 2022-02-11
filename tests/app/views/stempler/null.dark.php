{{ $var }}

@foreach($users as $user)
    {{ $users[1]['foo'] ?? '' }}
@endforeach
