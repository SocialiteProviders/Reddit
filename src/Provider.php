<?php

namespace SocialiteProviders\Reddit;

use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider implements ProviderInterface
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'REDDIT';

    /**
     * {@inheritdoc}
     */
    protected $scopes = ['identity'];

    /**
     * @var string
     */
    protected $userAgent = '';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            'https://ssl.reddit.com/api/v1/authorize', $state
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://ssl.reddit.com/api/v1/access_token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $headers = [
            'Authorization' => 'Bearer '.$token,
        ];

        if ($this->userAgent != '')
        {
            $headers['User-Agent'] = $this->userAgent;
        }

        $response = $this->getHttpClient()->get(
            'https://oauth.reddit.com/api/v1/me', [
            'headers' => $headers,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['id'], 'nickname' => $user['name'],
            'name' => null, 'email' => null, 'avatar' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($code)
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        if ($this->userAgent)
        {
            $headers['User-Agent'] = $this->userAgent;
        }

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => $headers,
            'auth' => [$this->clientId, $this->clientSecret],
            'form_params' => $this->getTokenFields($code),
        ]);

        $this->credentialsResponseBody = json_decode($response->getBody(), true);

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return [
            'grant_type' => 'authorization_code', 'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];
    }

    /**
     * Sets a custom user-agent header
     *
     * @param $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }
}
