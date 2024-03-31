<?php

test('open routes', function () {
    $response = $this->get('/');
    $response->assertStatus(200);

    $response = $this->get('/categories.txt');
    $response->assertStatus(200);
});
