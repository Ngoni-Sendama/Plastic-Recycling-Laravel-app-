<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Material Intake QZ Print - {{ $intake->grn_number }}</title>
    <script src="{{ asset('qz-tray/qz-tray.js') }}"></script>
</head>
<body>
    <script type="application/json" id="material-intake-payload">@json($payload)</script>
    <script src="{{ asset('qz-tray/material-intake-print.js') }}"></script>
</body>
</html>
