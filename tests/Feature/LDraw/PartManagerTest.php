<?php

it('has ldraw/partmanager page', function () {
    $response = $this->get('/ldraw/partmanager');

    $response->assertStatus(200);
});
