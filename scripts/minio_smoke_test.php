<?php

use App\Modules\Property\Services\PropertyMediaStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$storage = app(PropertyMediaStorage::class);

echo 'disk='.$storage->diskName().PHP_EOL;
echo 'cloud='.($storage->usesCloudDisk() ? 'yes' : 'no').PHP_EOL;

$tmp = tempnam(sys_get_temp_dir(), 'minio');
file_put_contents($tmp, 'vizagland-minio-smoke-test');

$file = new UploadedFile($tmp, 'smoke.txt', 'text/plain', null, true);
$path = $storage->storeDocument($file);

echo 'path='.$path.PHP_EOL;
$url = $storage->url($path);
echo 'url='.$url.PHP_EOL;
echo 'exists='.(Storage::disk('minio')->exists($path) ? 'yes' : 'no').PHP_EOL;

$headers = @get_headers($url ?: '');
echo 'http='.($headers[0] ?? 'unreachable').PHP_EOL;

@unlink($tmp);
