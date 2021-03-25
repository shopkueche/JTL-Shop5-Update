<?php declare(strict_types=1);

namespace JTL\dbeS;

use JTL\Extensions\Upload\File;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Request;

/**
 * Class Uploader
 * @package JTL\dbeS
 */
class Uploader extends NetSyncHandler
{
    /**
     * @param int $request
     */
    protected function request($request): void
    {
        if (!Upload::checkLicense()) {
            self::throwResponse(NetSyncResponse::ERRORNOLICENSE);
        }
        switch ($request) {
            case NetSyncRequest::UPLOADFILES:
                $orderID = Request::postInt('kBestellung');
                if ($orderID > 0) {
                    $systemFiles = [];
                    $uploads     = Upload::gibBestellungUploads($orderID);
                    if (\is_array($uploads) && \count($uploads)) {
                        foreach ($uploads as $upload) {
                            $paths = \pathinfo($upload->cName);
                            $ext   = $paths['extension'];
                            if (\strlen($ext) === 0) {
                                $ext = 'unknown';
                            }

                            $systemFiles[] = new SystemFile(
                                $upload->kUpload,
                                $upload->cName,
                                $upload->cName,
                                $paths['filename'],
                                '/',
                                $ext,
                                \date_format(\date_create($upload->dErstellt), 'U'),
                                $upload->nBytes
                            );
                        }
                        self::throwResponse(NetSyncResponse::OK, $systemFiles);
                    }
                }
                self::throwResponse(NetSyncResponse::ERRORINTERNAL);
                break;

            case NetSyncRequest::UPLOADFILEDATA:
                $uploadID = Request::getInt('kFileID');
                if ($uploadID > 0) {
                    $uploadFile = new File();
                    if ($uploadFile->loadFromDB($uploadID)) {
                        $path = \PFAD_UPLOADS . $uploadFile->cPfad;
                        if (\file_exists($path)) {
                            $this->streamFile($path, 'application/octet-stream', $uploadFile->cName);
                            exit;
                        }
                    }
                }
                self::throwResponse(NetSyncResponse::ERRORINTERNAL);
                break;
        }
    }
}
