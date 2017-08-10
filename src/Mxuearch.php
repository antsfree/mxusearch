<?php
namespace Antsfree\Mxusearch;

use Illuminate\Support\Facades\Facade;

class Mxusearch extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Mxusearch';
    }
}