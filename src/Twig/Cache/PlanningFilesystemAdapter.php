<?php

declare(strict_types=1);

namespace App\Twig\Cache;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

final class PlanningFilesystemAdapter extends FilesystemAdapter
{
    private string $cacheDirectory;

    public function __construct(string $namespace = '', int $defaultLifetime = 0, string $directory = null, MarshallerInterface $marshaller = null)
    {
        parent::__construct($namespace, $defaultLifetime, $directory, $marshaller);

        $this->cacheDirectory = $directory.\DIRECTORY_SEPARATOR.$namespace.\DIRECTORY_SEPARATOR;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch(array $ids)
    {
        /** @var array $values */
        $values = parent::doFetch($ids);
        foreach ($values as $id => $value) {
            $file = $this->getFile($id);
            if (!$handle = @fopen($file, 'rb')) {
                continue;
            }
            $values[$id] = [
                "\x9D".pack('VN', (int) (0.1 + (int) fgets($handle) - 1527506807), ceil(filectime($file) / 100))."\x5F" => $value,
            ];
            fclose($handle);
        }

        return $values;
    }

    private function getFile(string $id): string
    {
        // Use MD5 to favor speed over security, which is not an issue here
        $hash = str_replace('/', '-', base64_encode(hash('md5', static::class.$id, true)));
        $dir = $this->cacheDirectory.strtoupper($hash[0].\DIRECTORY_SEPARATOR.$hash[1].\DIRECTORY_SEPARATOR);

        return $dir.substr($hash, 2, 20);
    }
}
