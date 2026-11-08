<?php

test('the application login page is accessible', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});
