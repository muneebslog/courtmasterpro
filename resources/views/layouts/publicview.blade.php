<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tournaments</title>
    <style>
        /* Default anchor reset */
        a {
            text-decoration: none;
            color: inherit;
            display: block;
        }
    </style>
    @livewireStyles
</head>

<body
    style="background-color: #0B0F14; height: 100vh; color: #F9FAFB; font-family: Inter, system-ui, -apple-system, sans-serif; margin: 0; padding: 0; line-height: 1.5;">

    {{ $slot }}
</body>
@livewireScripts

</html>