<?php

namespace App\Utilities;

use App\Models\Post;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class ReadLater
{
    private $url;
    private const NARRATOR_CACHE_TTL = 86400; // 24 hours

    public function __construct($url)
    {
        // Check if Instapaper or Pocket are set up
        if (
            !env('OMNIVORE_API_KEY') &&
            !env('POCKET_ACCESS_TOKEN') &&
            !env('INSTAPAPER_USERNAME') &&
            !env('NARRATOR_API_TOKEN') &&
            !env('PREFERRED_READLATER_SERVICE')
        ) {
            throw new Exception('You have to setup either Omnivore, Instapaper, Narrator or Pocket and choose which one you prefer');
        }
        // Validate URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $this->url = filter_var($url, FILTER_VALIDATE_URL);
        } else {
            throw new Exception('Url is not valid', 1);
        }
    }

    public function save()
    {
        if (env('PREFERRED_READLATER_SERVICE') == 'pocket') {
            $response = json_decode((string) $this->saveToPocket());
            // Check if the `status` is `1` for success
            if (isset($response->status) && $response->status == 1) {
                return true;
            }
        } elseif (env('PREFERRED_READLATER_SERVICE') == 'omnivore') {
            return $this->saveToOmnivore();
        } elseif (env('PREFERRED_READLATER_SERVICE') == 'narrator') {
            return $this->saveToNarrator();
        } else {
            $response = json_decode((string) $this->saveToInstapaper());
            if (isset($response->bookmark_id)) {
                return true;
            }
        }
        \Log::info('$response->saveUrl: ' . $response->saveUrl);
        throw new Exception('Couldnt save url', 1);
    }

    public function saveToOmnivore()
    {

        $client = new Client();

        // Replace '<your api key>' with your actual API key.
        $apiKey = env('OMNIVORE_API_KEY');
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $apiKey
        ];

        $body = json_encode([
            'query' => 'mutation SaveUrl($input: SaveUrlInput!) { saveUrl(input: $input) { ... on SaveSuccess { url clientRequestId } ... on SaveError { errorCodes message } } }',
            'variables' => [
                'input' => [
                    'clientRequestId' => Str::uuid()->toString(),
                    'source' => 'api',
                    'url' => urldecode($this->url)
                ]
            ]
        ]);

        try {
            $response = $client->request('POST', 'https://api-prod.omnivore.app/api/graphql', [
                'headers' => $headers,
                'body' => $body
            ]);
            $json = $response->getBody()->getContents();
            $data = json_decode($json, true);
            if (is_array($data) && array_key_exists('data', $data)) {
                return true; // Save was succesful
            } else {
                return false; // was was not succesful
            }
        } catch (GuzzleException $e) {
            \Log::error($e->getMessage());
            return false;
        }
    }
    public function saveToNarrator()
    {
        $token = trim((string) env('NARRATOR_API_TOKEN'));
        if (!$token) {
            throw new Exception('Narrator token is not configured');
        }

        $post = Post::where('url', $this->url)->first();
        $title = $post->title ?? $this->url;
        $htmlBody = $post->content ?? '';
        $markdownBody = $post ? $this->getCachedNarratorMarkdown($post) : $this->buildNarratorMarkdown($htmlBody);

        $bodyPayload = [
            'title' => $title,
            'url' => $this->url,
            'body' => $markdownBody,
        ];

        $bodyJson = json_encode($bodyPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        try {
            $client = new Client();
            $response = $client->request('POST', 'https://narrator.beirutspring.com/api/save', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Narrator-Token' => $token,
                ],
                'json' => $bodyPayload,
            ]);

            $rawBody = (string) $response->getBody();

            return [
                'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
                'status_code' => $response->getStatusCode(),
                'payload' => json_decode($rawBody, true),
                'raw_body' => $rawBody,
                'token_preview' => $this->maskToken($token),
                'curl_example' => "curl -X POST https://narrator.beirutspring.com/api/save \\\n  -H 'Content-Type: application/json' \\\n  -H 'X-Narrator-Token: {$token}' \\\n  -d '{$bodyJson}'",
            ];
        } catch (GuzzleException $e) {
            $errorResponse = method_exists($e, 'getResponse') ? $e->getResponse() : null;
            $errorBody = $errorResponse ? (string) $errorResponse->getBody() : null;

            return [
                'success' => false,
                'status_code' => $errorResponse ? $errorResponse->getStatusCode() : null,
                'payload' => $errorBody ? json_decode($errorBody, true) : null,
                'raw_body' => $errorBody,
                'error' => $e->getMessage(),
                'token_preview' => $this->maskToken($token),
                'curl_example' => "curl -X POST https://narrator.beirutspring.com/api/save \\\n  -H 'Content-Type: application/json' \\\n  -H 'X-Narrator-Token: {$token}' \\\n  -d '{$bodyJson}'",
            ];
        }
    }
    public function warmNarratorCache(Post $post): string
    {
        return $this->getCachedNarratorMarkdown($post);
    }
    public function saveToPocket()
    {
        $client = new Client();
        $res = $client->request('POST', 'https://getpocket.com/v3/add', [
            'form_params' => [
                'url' => urldecode($this->url),
                'consumer_key' => env('POCKET_CONSUMER_KEY'),
                'access_token' => env('POCKET_ACCESS_TOKEN'),
            ],
        ]);

        return $res->getBody();
    }

    public function saveToInstapaper()
    {
        $saving_string = 'https://www.instapaper.com/api/add?' .
            'username=' . urlencode(env('INSTAPAPER_USERNAME')) .
            '&password=' . urlencode(env('INSTAPAPER_PASSWORD')) .
            '&url=' . $this->url;

        $client = new Client();
        $res = $client->request('GET', $saving_string, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
            ],
        ]);

        return $res->getBody();
    }

    private function buildNarratorMarkdown(?string $html): string
    {
        $markdownBody = $this->convertHtmlToMarkdown($html);

        // Fallback to plain text if markdown conversion produced nothing
        if (trim($markdownBody) === '') {
            $markdownBody = trim(strip_tags($html ?? '')) ?: $this->url;
        }

        return $markdownBody;
    }

    private function getCachedNarratorMarkdown(Post $post): string
    {
        $cacheKey = $this->getNarratorCacheKey($post);

        return Cache::remember($cacheKey, now()->addSeconds(self::NARRATOR_CACHE_TTL), function () use ($post) {
            return $this->buildNarratorMarkdown($post->content ?? '');
        });
    }

    private function convertHtmlToMarkdown(?string $html): string
    {
        if (!$html) {
            return '';
        }

        $document = new \DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();

        $body = $document->getElementsByTagName('body')->item(0);
        if (!$body) {
            return '';
        }

        $markdown = $this->walkNodes($body);
        $markdown = preg_replace("/\n{3,}/", "\n\n", $markdown);

        return trim($markdown);
    }

    private function walkNodes(\DOMNode $node): string
    {
        $markdown = '';

        /** @var \DOMNode $child */
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $value = htmlspecialchars_decode($child->nodeValue, ENT_QUOTES);
                if (trim($value) === '') {
                    continue;
                }
                $markdown .= $value;
                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $content = trim($this->walkNodes($child));

            switch (strtolower($child->nodeName)) {
                case 'img':
                    // Skip images entirely for Narrator markdown
                    break;
                case 'p':
                    if ($content !== '') {
                        $markdown .= $content . "\n\n";
                    }
                    break;
                case 'br':
                    $markdown .= "  \n";
                    break;
                case 'blockquote':
                    if ($content !== '') {
                        $lines = explode("\n", $content);
                        $quoted = implode("\n", array_map(function ($line) {
                            return '> ' . ltrim($line);
                        }, $lines));
                        $markdown .= $quoted . "\n\n";
                    }
                    break;
                case 'strong':
                case 'b':
                    $markdown .= '**' . $content . '**';
                    break;
                case 'em':
                case 'i':
                    $markdown .= '*' . $content . '*';
                    break;
                case 'a':
                    $hrefAttribute = $child->attributes->getNamedItem('href');
                    $href = $hrefAttribute ? $hrefAttribute->nodeValue : null;
                    $markdown .= $href ? '[' . ($content ?: $href) . '](' . $href . ')' : $content;
                    break;
                case 'ul':
                    $items = [];
                    foreach ($child->childNodes as $li) {
                        if ($li->nodeName === 'li') {
                            $itemContent = trim($this->walkNodes($li));
                            if ($itemContent !== '') {
                                $items[] = '- ' . $itemContent;
                            }
                        }
                    }
                    $markdown .= implode("\n", $items) . "\n\n";
                    break;
                case 'ol':
                $items = [];
                $index = 1;
                foreach ($child->childNodes as $li) {
                    if ($li->nodeName === 'li') {
                        $itemContent = trim($this->walkNodes($li));
                        if ($itemContent !== '') {
                            $items[] = $index . '. ' . $itemContent;
                            $index++;
                        }
                    }
                }
                $markdown .= implode("\n", $items) . "\n\n";
                break;
                case 'h1':
                case 'h2':
                case 'h3':
                case 'h4':
            case 'h5':
            case 'h6':
                $level = (int) substr($child->nodeName, 1);
                $markdown .= str_repeat('#', $level) . ' ' . $content . "\n\n";
                break;
            case 'pre':
                $markdown .= "\n```\n" . $child->textContent . "\n```\n\n";
                break;
            default:
                $markdown .= $content;
                break;
        }
    }

    return $markdown;
}

    private function getNarratorCacheKey(Post $post): string
    {
        return 'narrator:markdown:post:' . $post->id;
    }

    private function maskToken(string $token): string
    {
        $len = strlen($token);
        if ($len <= 6) {
            return str_repeat('*', max(0, $len - 2)) . substr($token, -2);
        }

        return substr($token, 0, 4) . '...' . substr($token, -2) . " (len:$len)";
    }
}
