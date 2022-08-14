<?php
/**
 * User: jtodd
 * Date: 2020-05-12
 * Time: 17:02
 */

namespace App\Services;

use App\Traits\Singleton;
use Aws\S3\S3Client;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class S3StorageService
{
    use Singleton;

    private $s3Client;
    private $bucket;

    public function init()
    {

        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'endpoint' => env('AWS_S3_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);
        $this->bucket = env('AWS_BUCKET');
    }

    public function putObject($urlId, $filePath)
    {

        $response = $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $urlId, //add path here
            'Body' => file_get_contents($filePath),
            'ACL' => 'public-read'
        ]);
        if (
            $response['ObjectURL'] &&
            strpos($response['ObjectURL'], $urlId) !== false
        ) {
            $response['key'] = $urlId;
        } else {
            throw new \Exception("S3 not saving right");
        }
        return $response;

    }

    public function getObject($objectUrl)
    {
        if (strpos(strtolower($objectUrl), 'http') === 0) {
            return $this->getUrl($objectUrl);
        }
        $retrive = $this->s3Client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $objectUrl
        ]);
        return $retrive['Body'];
    }

    public function getUrl($url)
    {
        /** @var Response $res */
        $client = app()->make(Client::class);
        $res = $client->request('GET', $url);
        return $res->getBody();
    }

}
