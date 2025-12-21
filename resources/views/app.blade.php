<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script>
    // Set HMR host dynamically so it works from any IP/device
    window.__VITE_HMR_HOST__ = window.location.hostname;
    window.__VITE_HMR_PORT__ = 5173;
  </script>
  @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>
<body>
  <div id="app"></div>
</body>
</html>
