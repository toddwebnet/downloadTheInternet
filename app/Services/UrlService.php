<?php

namespace App\Services;

use App\Models\Blacklist;
use App\Models\Domain;
use App\Models\Url;
use App\Models\UrlLink;
use App\Traits\Singleton;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use function PHPUnit\Framework\directoryExists;

class UrlService
{
    use Singleton;

    public function addUrl($url, $parentUrlId = null, $label = null): void
    {
        $urlInfo = UrlParserService::instance()->parse($url);
        $domain = Domain::firstOrCreate(['domain' => $urlInfo['domain']]);
        $url = Url::firstOrCreate([
            'domain_id' => $domain->id,
            'url' => $url
        ]);

        if (Blacklist::where('domain', $domain->domain)->first() !== null) {
            $url->is_skipped = true;
            $url->save();
        }

        if ($parentUrlId !== null) {
            $urlLink = UrlLink::firstOrCreate([
                'url_id' => $url->id,
                'parent_link_id' => $parentUrlId
            ]);
            if ($label !== null) {
                $urlLink->addLabel($label);
            }
        }

    }

    public function getAndSaveUrl($urlId)
    {
        $url = Url::find($urlId);
        if ($url !== null) {
            $stream = $this->getUrlContent($url);
            if ($stream !== null) {
                $this->saveContents($stream, $urlId);
                $url->last_refreshed = Carbon::now();
                $url->saved();
            }
        }
    }

    private function saveContents(Stream $stream, $urlId)
    {
        $storagePath = storage_path() . '/urls';
        if(!directoryExists($storagePath)){
            mkdir($storagePath);
        }
        $storagePath .= '/' . $urlId;
        file_put_contents($storagePath, $stream);
    }

    public function getUrlContent(Url $url): null|Stream
    {
        $client = app()->make(Client::class);
        $res = $client->request('GET', $url->url);
        if (!$this->isValidHtml($res)) {
            $url->invalidate();
            return null;
            // throw new \Exception("Invalid Html in Url");
        }
        $bodyStream = $res->getBody();
        $size = $bodyStream->getSize();
        if ($size > 1024 * 1024) {
            $url->invalidate();
            return null;
            // throw new \Exception("Data Too Big, skipping");
        }
        return $bodyStream;
    }

    private function isValidHtml(Response $res): bool
    {
        $headers = $res->getHeaders();
        if (is_array($headers) && array_key_exists('Content-Type', $headers)) {
            if (is_array($headers['Content-Type'])) {
                foreach ($headers['Content-Type'] as $type) {
                    if (strpos($type, 'text/html') !== false) {
                        return true;
                    }

                }
            }
        }
        return false;
    }
}
