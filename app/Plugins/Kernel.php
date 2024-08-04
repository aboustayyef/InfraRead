<?php

namespace App\Plugins;

/**
 *  Plugins are classes that Modify Post objects depending on Source.
 */
class Kernel
{
    public function get()
    {
        // Remember, the order of the plugins matters. Start from left to right.
        return
        [
            'httpskottkeorg' => ['FixRelativeLinks'],
            'httpsslashdotorg' => ['MakeTextLegible', 'ReplaceArticleLink'],
            'httpswwwmacstoriesnet' => ['MarkPostAsRead'],
            'httpswwwcaseylisscom' => ['MarkPostAsRead'],
            'httpswwwslowboringcom' => ['MarkPostAsRead'],
        ];
    }
}
