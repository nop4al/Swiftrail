<?php
// Quick verification script for Swiftrail deployment

echo "\n";
echo "╔════════════════════════════════════════════════════╗\n";
echo "║     Swiftrail Deployment Verification              ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

$checks = [];

// PHP Version
$checks['PHP Version'] = [
    'required' => '8.2+',
    'current' => phpversion(),
    'pass' => version_compare(phpversion(), '8.2.0', '>=')
];

// Required Extensions
$extensions = ['pdo', 'curl', 'json', 'openssl', 'mbstring'];
foreach ($extensions as $ext) {
    $checks["Extension: $ext"] = [
        'required' => 'installed',
        'current' => extension_loaded($ext) ? 'installed' : 'missing',
        'pass' => extension_loaded($ext)
    ];
}

// Directories
$dirs = [
    'storage' => '/storage',
    'bootstrap/cache' => '/bootstrap/cache',
    'public' => '/public'
];

foreach ($dirs as $name => $path) {
    $fullPath = __DIR__ . $path;
    $writable = is_writable($fullPath);
    $checks["Directory writable: $name"] = [
        'required' => 'writable',
        'current' => $writable ? 'writable' : 'not writable',
        'pass' => $writable
    ];
}

// .env file
$envExists = file_exists(__DIR__ . '/.env');
$checks['.env file'] = [
    'required' => 'exists',
    'current' => $envExists ? 'exists' : 'missing',
    'pass' => $envExists
];

// Composer autoload
$autoloadExists = file_exists(__DIR__ . '/vendor/autoload.php');
$checks['Composer dependencies'] = [
    'required' => 'installed',
    'current' => $autoloadExists ? 'installed' : 'missing',
    'pass' => $autoloadExists
];

// Database
if ($envExists) {
    $env = parse_ini_file(__DIR__ . '/.env');
    if (isset($env['DB_CONNECTION'])) {
        $dbType = $env['DB_CONNECTION'];
        
        if ($dbType === 'sqlite') {
            $dbFile = $env['DB_DATABASE'] ?? database_path('database.sqlite');
            $checks['SQLite Database'] = [
                'required' => 'accessible',
                'current' => is_file($dbFile) ? 'exists' : 'missing',
                'pass' => is_file($dbFile)
            ];
        } else {
            $checks['Database Configuration'] = [
                'required' => 'configured',
                'current' => "Using $dbType",
                'pass' => true
            ];
        }
    }
}

// Print results
$allPass = true;
foreach ($checks as $check => $result) {
    $status = $result['pass'] ? '✓' : '✗';
    $color = $result['pass'] ? "\033[32m" : "\033[31m";
    $reset = "\033[0m";
    
    printf(
        "%s[%s]%s %-35s [%s → %s]\n",
        $color,
        $status,
        $reset,
        $check,
        $result['required'],
        $result['current']
    );
    
    if (!$result['pass']) {
        $allPass = false;
    }
}

echo "\n";
if ($allPass) {
    echo "\033[32m✓ All checks passed! You're ready to deploy.\033[0m\n\n";
} else {
    echo "\033[31m✗ Some checks failed. Please fix them before deployment.\033[0m\n\n";
}

echo "Next steps:\n";
echo "  1. Run setup-nginx.bat to configure everything\n";
echo "  2. Start PHP-FPM on port 9000\n";
echo "  3. Start Nginx\n";
echo "  4. Access http://swiftrail.local\n\n";
