<?php

namespace App\Services;

use App\Traits\Singleton;

class UrlParserService
{

    use Singleton;

    /**
     * @return array|false|int|string|null
     */
    public function parse($url): bool|array|int|string|null
    {
        $url = $this->cleanUrl($url);
        $parsed = parse_url($url);
        if (!isset($parsed['path'])) {
            $parsed['path'] = '/';
        } else {
            $parsed['path'] = rtrim($parsed['path'], '/');
        }
        if (isset($parsed['host'])) {
            $parsed['domain'] = $this->getDomain($parsed['host']);
        }
        return $parsed;
    }

    /**
     * @param $url
     * @return string
     */
    public function cleanUrl($url)
    {
        $lcUrl = strtolower($url);
        $slashPos = strpos($lcUrl, '//');
        $httpPos = strpos($lcUrl, 'http');
        if (($slashPos === false || $slashPos > 0) && $httpPos === false) {
            $url = "http://{$url}";
        } else if ($slashPos == 0 || $httpPos > 0) {
            $url = "http:{$url}";
        }
        return $url;
    }

    /**
     * @param $url
     * @return string
     */
    public function buildFullLinkOnPage($url, $parentUrl = null)
    {
        $parsedUrl = $this->parse($url);

        if (strpos($url, '#') !== false) {
            $url = substr($url, 0, strpos($url, '#'));
        }
        if (strpos($url, 'http') === 0) {
            return $url;
        }
        if (strpos($url, '//') === 0) {
            return "http:{$url}";
        }
        if (strpos($url, '/') === 0 || strpos($url, '?') === 0) {
            $parsedParent = $this->parse($parentUrl);
            return "{$parsedParent['scheme']}://{$parsedParent['host']}{$url}";
        }
        if (strpos($url, '.') === 0) {
            return $this->buildRelativeUrl($url);
        }
        return "{$parsedUrl['scheme']}://{$parsedUrl['host']}{$parsedUrl['path']}/{$url}";

    }

    /**
     * @param $link
     * @return string
     */
    public function buildRelativeUrl($link)
    {
        $parsed = $this->parse();
        $baseUrl = $this->chopLastSlash($parsed['path']);
        while (strpos($link, '.') === 0) {
            if (strpos($link, '../') === 0) {
                $baseUrl = $this->chopLastSlash($baseUrl);
                $link = $this->addPreSlash(substr($link, 3));
            } elseif (strpos($link, './') === 0) {
                $link = $this->addPreSlash(substr($link, 2));
            }
        }
        return "{$parsed['scheme']}://{$parsed['host']}{$baseUrl}{$link}";
    }

    /**
     * @param $url
     * @return false|string
     */
    private function chopLastSlash($url)
    {
        $slashPos = strrpos(
            str_replace('://', ':::', $url)
            , '/');
        if ($slashPos === false) {
            return $url;
        } else {
            return (substr($url, 0, $slashPos));
        }
    }

    /**
     * @param $link
     * @return string
     */
    private function addPreSlash($link)
    {
        if ($link == '') {
            return $link;
        }
        $slashPos = strrpos($link, '/');
        if ($slashPos !== 0) {
            $link = '/' . $link;
        }
        return $link;
    }


    function getDomain($domain, $debug = false)
    {
        $original = $domain = strtolower($domain);

        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return $domain;
        }

        $debug ? print('<strong style="color:green">&raquo;</strong> Parsing: ' . $original) : false;

        $arr = array_slice(array_filter(explode('.', $domain, 4), function ($value) {
            return $value !== 'www';
        }), 0); //rebuild array indexes

        if (count($arr) > 2) {
            $count = count($arr);
            $_sub = explode('.', $count === 4 ? $arr[3] : $arr[2]);

            $debug ? print(" (parts count: {$count})") : false;

            if (count($_sub) === 2) // two level TLD
            {
                $removed = array_shift($arr);
                if ($count === 4) // got a subdomain acting as a domain
                {
                    $removed = array_shift($arr);
                }
                $debug ? print("<br>\n" . '[*] Two level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
            } elseif (count($_sub) === 1) // one level TLD
            {
                $removed = array_shift($arr); //remove the subdomain

                if (strlen($_sub[0]) === 2 && $count === 3) // TLD domain must be 2 letters
                {
                    array_unshift($arr, $removed);
                } else {
                    // non country TLD according to IANA
                    $tlds = array(
                        'aero',
                        'arpa',
                        'asia',
                        'biz',
                        'cat',
                        'com',
                        'coop',
                        'edu',
                        'gov',
                        'info',
                        'jobs',
                        'mil',
                        'mobi',
                        'museum',
                        'name',
                        'net',
                        'org',
                        'post',
                        'pro',
                        'tel',
                        'travel',
                        'xxx',
                    );

                    if (count($arr) > 2 && in_array($_sub[0], $tlds) !== false) //special TLD don't have a country
                    {
                        array_shift($arr);
                    }
                }
                $debug ? print("<br>\n" . '[*] One level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
            } else // more than 3 levels, something is wrong
            {
                for ($i = count($_sub); $i > 1; $i--) {
                    $removed = array_shift($arr);
                }
                $debug ? print("<br>\n" . '[*] Three level TLD: <strong>' . join('.', $_sub) . '</strong> ') : false;
            }
        } elseif (count($arr) === 2) {
            $arr0 = array_shift($arr);

            if (strpos(join('.', $arr), '.') === false
                && in_array($arr[0], array('localhost', 'test', 'invalid')) === false) // not a reserved domain
            {
                $debug ? print("<br>\n" . 'Seems invalid domain: <strong>' . join('.', $arr) . '</strong> re-adding: <strong>' . $arr0 . '</strong> ') : false;
                // seems invalid domain, restore it
                array_unshift($arr, $arr0);
            }
        }

        $debug ? print("<br>\n" . '<strong style="color:gray">&laquo;</strong> Done parsing: <span style="color:red">' . $original . '</span> as <span style="color:blue">' . join('.', $arr) . "</span><br>\n") : false;

        return join('.', $arr);
    }


}
