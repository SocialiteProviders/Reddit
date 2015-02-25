<?php
namespace SocialiteProviders\Reddit;

use SocialiteProviders\Manager\SocialiteWasCalled;

class RedditExtendSocialite
{
    /**
     * Execute the provider.
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite(
            'reddit', __NAMESPACE__.'\Provider'
        );
    }
}
