<?php
require __DIR__ . '/vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$base64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

$manager = new ImageManager(new Driver());
$image = $manager->decode(base64_decode($base64));
try {
    $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(80));
    var_dump($encoded->toString() !== '');
} catch (\Throwable $e) {
    echo "Encoder class failed: " . $e->getMessage() . "\n";
}
try {
    $encoded = $image->encodeUsingFileExtension('webp', 80);
    var_dump($encoded->toString() !== '');
} catch (\Throwable $e) {
    echo "encodeUsingFileExtension failed: " . $e->getMessage() . "\n";
}
