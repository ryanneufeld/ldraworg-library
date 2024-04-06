<?php

class SetPbg
{
    protected array $errors = [];
    protected array $warnings = [];

    public function __construct(
        public string $set_number,
    )
    {}

    public function pbg(): string
    {
        return '';
    }
}