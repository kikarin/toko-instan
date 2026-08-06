<?php

test('landing page has a successful response', function () {
    $this->get('/')->assertOk();
});

test('guest browsing is redirected away from the buyer marketplace', function () {
    $this->get('/marketplace')->assertRedirect('/login');
});
