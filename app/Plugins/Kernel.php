<?php
namespace App\Plugins;
/**
 *  Plugins are classes that Modify Post objects depending on Source
 */

class Kernel
{

    public function get()
    {
        return 
        [
            'httpsslashdotorg'  =>  ['MakeTextLegible'],
        ];
    }    

}
