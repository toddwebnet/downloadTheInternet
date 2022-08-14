<?php

namespace App\Services;

use App\Helpers\CompressionHelper;
use App\Helpers\Utils;
use App\Jobs\AddUrlJob;
use App\Jobs\FinalizeUrlJob;
use App\Jobs\ProcessDownloadJob;
use App\Models\Url;
use App\Models\UrlDownload;
use App\Traits\Singleton;
use Carbon\Carbon;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\HtmlNode;
use Soundasleep\Html2Text;

class HtmlProcessService
{
    use Singleton;

    public function scanForFiles()
    {
        $storagePath = storage_path() . '/urls';
        if (!is_dir($storagePath)) {
            mkdir($storagePath);
        }
        foreach (scandir($storagePath) as $path) {
            if (
                !str_starts_with($path, '.') &&
                !RedisListService::instance()->isInList(RedisListService::PROCESSING, $path)
            ) {
                dump($path);
                QueueService::instance()->sendToQueue(
                    ProcessDownloadJob::class,
                    ['urlId' => $path],
                    'procs',
                );
            }
        }
    }

    public function processFile($urlId)
    {
        $storagePath = storage_path() . '/urls/' . $urlId;
        if (file_exists($storagePath)) {
            $url = Url::find($urlId);
            if ($url !== null) {

                $body = utf8_encode(file_get_contents($storagePath));
                $this->processHtml($url, $body);
                return;
            }
        }
        RedisListService::instance()->clearFromAllLists($urlId);

    }

    private function processHtml(Url $url, $html)
    {
        $dom = new Dom();
        $dom->load($html);
        $links = $dom->find('a');
        foreach ($links as $link) {

            if ($link->href &&
                $this->isValidLink($link->href)
            ) {
                $link->href = UrlParserService::instance()->buildFullLinkOnPage(
                    str_replace(' ', '+',
                        trim($link->href)
                    ), $url->url
                );
                $data = [
                    'url' => $link->href,
                    'parentUrlId' => $url->id,
                    'label' => strip_tags(trim($link->innerHtml()))
                ];
                QueueService::instance()->sendToQueue(
                    AddUrlJob::class,
                    $data,
                    'urls'
                );

            }
        }
        QueueService::instance()->sendToQueue(
            FinalizeUrlJob::class,
            ['urlId' => $url->id],
            'finalize'
        );
    }

    public function isValidLink($link)
    {
        $isValid = (
            strpos(strtolower($link), 'javascript:') !== 0 &&
            strpos($link, '#') !== 0
        );
        if ($isValid) {
            $invalidExts = [
                'jpg', 'jpeg', 'png', 'mp4', 'mpg', 'mp3', '7z', 'zip',
                'msi', 'exe', 'arj', 'ace', 'tar', 'gz', 'iso', 'img', 'dmg',
                'gif', 'xml', 'tif', 'bmp', 'mdb', 'sql', 'dat', 'sqlite',
                'pub', 'doc', 'docx', 'xls', 'xlsx', 'mdbx', 'log', 'txt', 'md',
                'pdf', 'asc', 'ascii', 'gpx', 'gml', 'rom', 'ico', 'raw', 'ai', 'psd',
                'eps', 'vod', 'lnk', 'webloc', 'odf', 'obj', 'class', 'dll', 'jar', 'war',
                'ps', 'pnp', 'ppt', 'pptx', 'js', 'javascript', 'au3', 'bat', 'vox', 'voc',
                'ram', 'm3u', 'asx', 'avi', 'fla', 'm4v', 'ogg'
            ];
            $isValid = (!in_array(strtolower(Utils::getLinkExt($link)), $invalidExts));

        }
        return $isValid;

    }

    public function finalizeUrl($urlId)
    {
        $storagePath = storage_path() . '/urls';
        if (!is_dir($storagePath)) {
            mkdir($storagePath);
        }
        $storagePath .= '/' . $urlId;
        $url = Url::find($urlId);
        if (file_exists($storagePath) && $url !== null) {

            $content = $this->deriveContent($storagePath);
            CompressionHelper::compressFile($storagePath);
            $storagePath .= '.gz';
            $s3Path = "{$url->domain_id}/{$url->id}_" . time();
            $response = S3StorageService::instance()->putObject($s3Path, $storagePath);
            $s3Url = env('AWS_S3_ENDPOINT') . '/' . env('AWS_BUCKET') . '/' . $s3Path;

            UrlDownload::create([
                'url_id' => $urlId,
                'content_url' => $s3Url,
                'content' => json_encode($content)
            ]);

        }
        if (file_exists($storagePath)) {
            unlink($storagePath);
        }
        RedisListService::instance()->clearFromAllLists($urlId);

    }

    public function deriveContent($path)
    {

        $html = $content = file_get_contents($path);
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $html = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $html);
        $html = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $html);
        $html = preg_replace('#<svg(.*?)>(.*?)</svg>#is', '', $html);
        $html = strip_tags($html);
        $body = $html;
        $dom = new Dom();
        $dom->load($content);

        /** @var HtmlNode $title */
        $title = head($dom->find('zztitle'));
        if (head($title)) {
            $title = head($title)->innerHtml;
        } else {
            $title = 'Untitled';
        }

        $data = [
            'keywords' => '',
            'title' => $title,
            'description' => '',
            'content' => $body,
        ];


        $keys = ['title', 'description', 'keywords'];
        /** @var HtmlNode $meta */
        foreach ($dom->find('meta') as $meta) {
            $name = $meta->getAttribute('name');
            if ($name !== null) {
                foreach ($keys as $key) {
                    if (str_contains(strtolower($name), $key)) {
                        $data[$key] = $meta->getAttribute('content');
                    }
                }
            }
        }
        return $data;
    }
}
