<?php

namespace App\LDraw;

class PartsUpdateService
{
    public function __construct(
        public \App\LDraw\Check\PartChecker $checker
    ) {}
}