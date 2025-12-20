<?php
echo "getenv(DB_CONNECTION): ".(getenv('DB_CONNECTION') === false ? 'false' : getenv('DB_CONNECTION'))."\n";
echo "_ENV[DB_CONNECTION]: ".(isset($_ENV['DB_CONNECTION']) ? $_ENV['DB_CONNECTION'] : 'not set')."\n";

echo "PDO drivers: \n";
print_r(PDO::getAvailableDrivers());
