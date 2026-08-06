<?php

test('marketplace page has a successful response', function () {
    $response = $this->get('/marketplace');

    $response->assertOk();
});
