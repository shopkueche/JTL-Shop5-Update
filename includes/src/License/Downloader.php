<?php declare(strict_types=1);

namespace JTL\License;

use GuzzleHttp\Client;
use JTL\License\Exception\ApiResultCodeException;
use JTL\License\Exception\ChecksumValidationException;
use JTL\License\Exception\DownloadValidationException;
use JTL\License\Exception\FilePermissionException;
use JTL\License\Struct\Release;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Downloader
 * @package JTL\License
 */
class Downloader
{
    /**
     * @param Release $available
     * @return ResponseInterface|string
     * @throws DownloadValidationException
     * @throws FilePermissionException
     * @throws ApiResultCodeException
     * @throws ChecksumValidationException
     */
    public function downloadRelease(Release $available): string
    {
        if (!$this->validateDownloadArchive($available)) {
            throw new DownloadValidationException('Could not validate archive');
        }
        $url  = $available->getDownloadURL();
        $file = $this->downloadItemArchive($url, \basename($url));
        if (!$this->validateChecksum($file, $available->getChecksum())) {
            if (\file_exists($file)) {
                \unlink($file);
            }
            throw new ChecksumValidationException('Archive checksum validation failed');
        }

        return $file;
    }

    /**
     * @param string $url
     * @param string $targetName
     * @return ResponseInterface
     * @throws FilePermissionException
     * @throws ApiResultCodeException
     */
    private function downloadItemArchive(string $url, string $targetName): string
    {
        $fileName = \PFAD_ROOT . \PFAD_DBES_TMP . \basename($targetName);
        $resource = \fopen($fileName, 'w+');
        if ($resource === false) {
            throw new FilePermissionException('Cannot open file ' . $fileName);
        }
        $client = new Client();
        $res    = $client->request('GET', $url, ['sink' => $resource]);
        if ($res->getStatusCode() !== 200) {
            throw new ApiResultCodeException('Did not get 200 OK result code form api but ' . $res->getStatusCode());
        }

        return $fileName;
    }

    /**
     * @param Release $available
     * @return bool
     */
    private function validateDownloadArchive(Release $available): bool
    {
        if ($available->getDownloadURL() === null) {
            return false;
        }
        $parsed = \parse_url($available->getDownloadURL());
        if (!\is_array($parsed) || $parsed['scheme'] !== 'https') {
            return false;
        }
        // @todo: signature validation
        return true;
    }

    /**
     * @param string $file
     * @param string $checksum
     * @return bool
     */
    private function validateChecksum(string $file, string $checksum): bool
    {
        return \sha1_file($file) === $checksum;
    }
}
