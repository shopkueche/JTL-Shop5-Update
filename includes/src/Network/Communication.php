<?php

namespace JTL\Network;

use Exception;
use JTL\Helpers\Request;

/**
 * Class Communication
 * @package JTL\Network
 */
final class Communication
{
    /**
     * @param string $cURL
     * @param array  $postData
     * @param bool   $bPost
     * @return mixed
     * @throws Exception
     */
    private static function doCall(string $cURL, array $postData, bool $bPost = true)
    {
        if (\function_exists('curl_init')) {
            $ch = \curl_init();
            \curl_setopt($ch, \CURLOPT_POST, $bPost);
            \curl_setopt($ch, \CURLOPT_POSTFIELDS, $postData);
            \curl_setopt(
                $ch,
                \CURLOPT_USERAGENT,
                'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1'
            );
            \curl_setopt($ch, \CURLOPT_URL, $cURL);
            \curl_setopt($ch, \CURLOPT_ENCODING, 'UTF-8');
            \curl_setopt($ch, \CURLOPT_AUTOREFERER, true);
            \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
            \curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, 60);
            \curl_setopt($ch, \CURLOPT_TIMEOUT, 60);

            $content = Request::curl_exec_follow($ch);

            \curl_close($ch);
        } else {
            throw new Exception('Die PHP Funktion curl_init existiert nicht!');
        }

        return $content;
    }

    /**
     * @param string $url
     * @param array  $data
     * @param bool   $bPost
     * @return string
     * @throws Exception
     */
    public static function postData(string $url, $data = [], bool $bPost = true): ?string
    {
        return \is_array($data)
            ? self::doCall($url, $data, $bPost)
            : '';
    }

    /**
     * @param string $url
     * @param string $file
     * @param bool   $deleteFile
     * @return mixed|string
     * @throws Exception
     */
    public static function sendFile(string $url, string $file, bool $deleteFile = false)
    {
        if (!\file_exists($file)) {
            return '';
        }
        $content = self::doCall($url, ['opt_file' => '@' . $file]);
        if ($deleteFile) {
            @\unlink($file);
        }

        return $content;
    }
}
